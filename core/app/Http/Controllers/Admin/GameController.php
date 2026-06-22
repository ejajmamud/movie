<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Team;
use App\Models\Tournament;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GameController extends Controller {
    public function index() {
        $pageTitle = 'All Games';
        $games     = Game::searchable(['tournament:name', 'teamOne:name', 'teamTwo:name'])->withCount('liveComments')->latest()->paginate(getPaginate());
        return view('admin.games.index', compact('pageTitle', 'games'));
    }

    public function add($id = 0) {
        $game = null;
        if ($id) {
            $game      = Game::find($id);
            $pageTitle = 'Edit Game';
        } else {
            $pageTitle = 'Add Game';
        }
        $tournaments = Tournament::active()->get(['id', 'name']);
        return view('admin.games.add', compact('pageTitle', 'game', 'tournaments'));
    }

    public function team(Request $request) {
        $teamName = trim($request->term);
        $teams    = Team::active();
        if ($teamName) {
            $teams->where('name', 'LIKE', "%$teamName%");
        }
        $teams     = $teams->select('id', 'name as text')->paginate(10);
        $morePages = true;
        if (empty($teams->nextPageUrl())) {
            $morePages = false;
        }
        $results = [
            "results"    => $teams->items(),
            "pagination" => ["more" => $morePages],
        ];
        return response()->json($results);
    }

    public function store(Request $request, $id = 0) {
        $validate = $this->validationRequest($request, $id);
        if ($id) {
            $game         = Game::findOrFail($id);
            $notification = 'Game updated successfully';
        } else {
            $game         = new Game();
            $notification = 'Game added successfully';
        }

        if ($request->hasFile('image')) {
            try {
                $game->image = fileUploader($request->image, getFilePath('game'), getFileSize('game'), @$game->image, getFileThumb('game'));
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $game->tournament_id = $request->tournament_id;
        $game->team_one_id   = $request->team_one_id;
        $game->team_two_id   = $request->team_two_id;
        $game->start_time    = $request->start_time;
        $game->slug          = @$validate['slug'];
        $game->link          = $request->link;
        $game->details       = $request->details;
        $game->price         = $request->price;
        $game->version       = $request->version ? Status::PAID_VERSION : Status::FREE_VERSION;
        $game->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    protected function validationRequest($request, $id = 0) {
        $imageValidate = $id ? 'nullable' : 'required';
        $request->validate([
            'tournament_id' => 'required|integer|exists:tournaments,id',
            'team_one_id'   => 'required|integer|exists:teams,id',
            'team_two_id'   => [
                'required',
                'integer',
                'exists:teams,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value == $request->team_one_id) {
                        $fail('Team One and Team Two cannot be the same');
                    }
                },
            ],
            'start_time'    => 'required|date_format:Y-m-d H:i|after:now',
            'details'       => 'required|string|max:255',
            'link'          => 'required|url',
            'price'         => 'required|numeric|gte:0',
            'image'         => [$imageValidate, 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        $tournament = Tournament::active()->where('id', $request->tournament_id)->first();
        if (!$tournament) {
            throw ValidationException::withMessages(['error' => 'Tournament not found']);
        }
        $teamOne = Team::active()->where('id', $request->team_one_id)->first();
        if (!$teamOne) {
            throw ValidationException::withMessages(['error' => 'Team One not found']);
        }
        $teamTwo = Team::active()->where('id', $request->team_two_id)->first();
        if (!$teamTwo) {
            throw ValidationException::withMessages(['error' => 'Team Two not found']);
        }
        return ['slug' => slug($teamOne->name . '-' . $teamTwo->name)];
    }

    public function status($id) {
        return Game::changeStatus($id);
    }

    public function seo($id) {
        $key       = 'game';
        $data      = Game::findOrFail($id);
        $pageTitle = 'SEO Configuration: ' . keyToTitle($data->slug);
        return view('admin.games.seo', compact('pageTitle', 'key', 'data'));
    }

    public function seoStore(Request $request, $id) {
        $request->validate([
            'image' => ['nullable', new FileTypeValidate(['jpeg', 'jpg', 'png'])],
        ]);

        $data  = Game::findOrFail($id);
        $image = @$data->seo_content->image;
        if ($request->hasFile('image')) {
            try {
                $path  = getFilePath('gameSeo');
                $image = fileUploader($request->image, $path, getFileSize('seo'), @$data->seo_content->image);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the seo image'];
                return back()->withNotify($notify);
            }
        }
        $data->seo_content = [
            'image'              => $image,
            'description'        => $request->description,
            'social_title'       => $request->social_title,
            'social_description' => $request->social_description,
            'keywords'           => $request->keywords,
        ];
        $data->save();

        $notify[] = ['success', 'SEO content has been updated successfully'];
        return to_route('admin.game.seo', $data->id)->withNotify($notify);
    }

    public function liveComment($id) {
        $game      = Game::findOrFail($id);
        $comments  = $game->liveComments()->with('user')->latest()->paginate(getPaginate());
        $pageTitle = 'Live Comments: ' . $game->tournament->name;
        return view('admin.games.live_comment', compact('pageTitle', 'game', 'comments'));
    }

    public function liveCommentDelete($tvId, $id) {
        $game = Game::findOrFail($tvId);
        $game->liveComments()->findOrFail($id)->delete();
        return back()->with('success', "Successfully deleted live comments");
    }

    public function liveCommentBulkDelete(Request $request, $tvId) {
        $game = Game::findOrFail($tvId);
        $request->validate([
            'delete_all' => 'nullable|boolean',
            'date'       => 'required_without:delete_all',
        ]);

        $query = $game->liveComments();
        if ($request->delete_all) {
            $query->delete();
        } else {
            $dates = explode('-', $request->date);
            try {
                $startDate = Carbon::parse(trim($dates[0]))->startOfDay();
                $endDate   = isset($dates[1])
                ? Carbon::parse(trim($dates[1]))->endOfDay()
                : $startDate->copy()->endOfDay();

                $arr = $query->whereBetween('created_at', [
                    $startDate->format('Y-m-d H:i:s'),
                    $endDate->format('Y-m-d H:i:s'),
                ])->delete();
            } catch (\Exception $e) {
                throw ValidationException::withMessages(['error' => 'Invalid date format']);
            }
        }

        return back()->with('success', "Successfully deleted live comments");
    }
}
