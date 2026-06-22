<?php

namespace App\Traits;

use App\Constants\Status;
use App\Models\Category;
use App\Models\Item;
use App\Models\SubCategory;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

trait ItemUpload {
    public function create() {
        $pageTitle  = "Add New Item";
        $categories = Category::active()->with([
            'subcategories' => function ($subcategory) {
                $subcategory->active()->orderBy('name');
            },
        ])->orderBy('name')->get();
        return view('admin.item.create', compact('pageTitle', 'categories'));
    }

    public function edit($id) {
        $item       = Item::findOrFail($id);
        $pageTitle  = "Edit : " . $item->title;
        $categories = Category::active()->with([
            'subcategories' => function ($subcategory) {
                $subcategory->active();
            },
        ])->orderBy('id', 'desc')->get();
        $subcategories = SubCategory::active()->where('category_id', $item->category_id)->orderBy('id', 'desc')->get();
        return view('admin.item.edit', compact('pageTitle', 'item', 'categories', 'subcategories'));
    }

    public function store(Request $request) {
        $this->itemValidation($request, 'create');
        $item  = new Item();
        $image = $this->imageUpload($request, $item, 'update');

        $item->item_type = $request->item_type;
        $item->slug      = slug($request->title . '-' . time() . getTrx(5));
        $item->featured  = 0;

        $this->saveItem($request, $image, $item);

        if ($request->notify_to_users) {
            $this->notifyToUsers($item);
        } else if ($request->notify_to_subscriber) {
            $this->notifyToSubscribers($item);
        }

        $notify[] = ['success', 'Item added successfully'];
        if ($request->item_type == Status::EPISODE_ITEM) {
            return redirect()->route('admin.item.episodes', $item->id)->withNotify($notify);
        } else {
            return redirect()->route('admin.item.uploadVideo', $item->id)->withNotify($notify);
        }
    }

    public function update(Request $request, $id) {
        $this->itemValidation($request, 'update');
        $item             = Item::findOrFail($id);
        $item->single     = $request->single ? Status::ENABLE : Status::DISABLE;
        $item->status     = $request->status ? Status::ENABLE : Status::DISABLE;
        $item->trending   = $request->trending ? Status::ENABLE : Status::DISABLE;
        $item->featured   = $request->featured ? Status::ENABLE : Status::DISABLE;
        $item->is_trailer = $request->is_trailer ? Status::ENABLE : Status::DISABLE;
        $image            = $this->imageUpload($request, $item, 'update');

        $this->saveItem($request, $image, $item);

        $notify[] = ['success', 'Item updated successfully'];
        return back()->withNotify($notify);
    }

    private function saveItem($request, $image, $item) {
        $item->category_id     = $request->category;
        $item->sub_category_id = $request->sub_category_id;
        $item->title           = $request->title;
        $item->preview_text    = $request->preview_text;
        $item->description     = $request->description;

        $idField = trim($request->id);
        if (preg_match('/^tt\d+/i', $idField)) {
            $item->imdb_id = $idField;
        } else if (empty($item->imdb_id) && !empty($request->title)) {
            $type = $request->item_type == 1 ? 'movie' : 'tv';
            $item->imdb_id = \App\Services\AiService::resolveImdbId($request->title, $type);
        }
        $genresItem            = [];
        foreach ($request->genres as $genre) {
            $genresItem[] = ucfirst($genre);
        };

        $item->team = [
            'director' => implode(',', $request->director),
            'producer' => implode(',', $request->producer),
            'casts'    => implode(',', $request->casts),
            'genres'   => implode(',', $genresItem),
            'language' => implode(',', $request->language),
        ];
        $item->tags    = implode(',', $request->tags);
        $item->image   = $image;
        $item->version = @$request->version ?? 0;
        $item->ratings = $request->ratings;

        if ($request->version == Status::RENT_VERSION || $request->rent == Status::RENT_VERSION) {
            $item->rent_price    = $request->rent_price ?? 0;
            $item->rental_period = $request->rental_period ?? 0;
            $item->exclude_plan  = $request->exclude_plan ?? 1;
        } else {
            $item->rent_price    = 0;
            $item->rental_period = 0;
            $item->exclude_plan  = 1;
        }

        $item->save();
    }

    private function itemValidation($request, $type) {
        $validation = $type == 'create' ? 'required' : 'nullable';
        $versions   = implode(',', [Status::FREE_VERSION, Status::PAID_VERSION, Status::RENT_VERSION]);
        $request->validate([
            'title'           => 'required|string|max:255',
            'category'        => ['required', 'integer', Rule::exists('categories', 'id')->where(function ($query) {
                $query->where('status', Status::ENABLE);
            })],
            'sub_category_id' => ['nullable', 'integer', Rule::exists('sub_categories', 'id')->where(function ($query) use ($request) {
                $query->where('category_id', $request->input('category'));
            })],
            'preview_text'    => 'required|string|max:255',
            'description'     => 'required|string',
            'director'        => 'required',
            'producer'        => 'required',
            'casts'           => 'required',
            'tags'            => 'required',
            'item_type'       => "$validation|in:1,2",
            'version'         => "nullable|required_if:item_type,==,1|in:$versions",
            'ratings'         => 'required|numeric',
            'rent_price'      => 'required_if:version,==,2|nullable|numeric|gte:0',
            'rental_period'   => 'required_if:version,==,2|nullable|integer|gte:0',
            'exclude_plan'    => 'required_if:version,==,2|nullable|in:0,1',
        ]);
    }

    private function imageUpload($request, $item, $type) {
        $image = [
            'landscape' => $item?->image?->landscape,
            'portrait'  => $item?->image?->portrait,
        ];

        $image = $this->processImage($request, $type, 'landscape', $image);
        $image = $this->processImage($request, $type, 'portrait', $image);
        return $image;
    }

    private function processImage($request, $type, $orientation, $image) {
        $maxFileSize = 3000000;
        $basePath    = getFilePath("item_" . $orientation);
        $imageUrl    = $orientation . "_url";
        if ($request->$imageUrl) {
            try {
                $image[$orientation] = $this->uploadFromUrl($request->input($imageUrl), $basePath);
            } catch (\Exception $e) {
                throw ValidationException::withMessages([$orientation => ucfirst($orientation) . ' image could not be uploaded from URL']);
            }
        }

        if ($request->hasFile($orientation)) {
            $file = $request->file($orientation);
            if ($file->getSize() > $maxFileSize) {
                throw ValidationException::withMessages([$orientation => ucfirst($orientation) . ' image size cannot exceed 3MB']);
            }

            try {
                $datePath = date('Y/m/d');
                if ($type === 'update' && !empty($image[$orientation])) {
                    fileManager()->removeFile($basePath . $image[$orientation]);
                }
                $image[$orientation] = $datePath . '/' . fileUploader($file, $basePath . $datePath);
            } catch (\Exception $e) {
                throw ValidationException::withMessages([$orientation => ucfirst($orientation) . ' image could not be uploaded']);
            }
        }
        return $image;
    }

    private function uploadFromUrl($url, $basePath) {
        $contents = false;
        if ($url) {
            $contents = file_get_contents($url);
        }
        if ($contents === false) {
            throw new \Exception('Failed to fetch image from URL');
        }

        $name = substr($url, strrpos($url, '/') + 1);
        fileManager()->makeDirectory($basePath);
        $path = $basePath . $name;
        Storage::put($name, $contents);
        File::move(storage_path('app/' . $name), $path);

        return $name;
    }

    protected function notifyToUsers($item) {
        $clickUrl = route('watch', $item->slug);
        $users    = User::active()->cursor();

        $shortCode = [
            'title' => $item->title,
        ];
        foreach ($users as $user) {
            notify($user, 'SEND_ITEM_NOTIFICATION', $shortCode, redirectUrl: $clickUrl);
        }
    }

    protected function notifyToSubscribers($item) {
        $subscribers = Subscriber::orderBy('id', 'desc')->cursor();
        $clickUrl    = route('watch', $item->slug);
        $shortCode   = [
            'title' => $item->title,
        ];

        foreach ($subscribers as $subscriber) {
            $receiverName = explode('@', $subscriber->email)[0];
            $user         = [
                'username' => $subscriber->email,
                'email'    => $subscriber->email,
                'fullname' => $receiverName,
            ];
            notify($user, 'SEND_ITEM_NOTIFICATION_SUBSCRIBER', $shortCode, redirectUrl: $clickUrl);
        }
    }
}
