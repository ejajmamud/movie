<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\VideoUploader;
use App\Models\Advertise;
use App\Models\VideoAdvertise;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

class AdvertiseController extends Controller
{
    public function index()
    {
        $ads       = Advertise::orderBy('id', 'desc')->paginate(getPaginate());
        $pageTitle = "Advertises";
        return view('admin.advertise.index', compact('ads', 'pageTitle'));
    }

    public function store(Request $request, $id = 0)
    {
        $imageValidate = $id ? 'nullable' : 'required';
        if ($request->ads_type == 'script') {
            $imageValidate = 'nullable';
        }
        $request->validate([
            'type'     => 'required|integer|in:1,2',
            'device'   => 'required|integer|in:1,2',
            'ads_show' => 'required|integer|in:1,2',
            'ads_type' => 'required|string|in:banner,script',
            'image'    => [$imageValidate, new FileTypeValidate(['jpg', 'jpeg', 'png', 'gif'])],
        ]);

        if ($id == 0) {
            $advertise    = new Advertise();
            $notification = 'Advertise added successfully';
            $oldFile      = NULL;
        } else {
            $advertise    = Advertise::findOrFail($id);
            $notification = 'Advertise updated successfully';
            $oldFile      = $advertise->content->image;
            $filename     = $advertise->content->image;
        }

        $advertise->type     = $request->type;
        $advertise->device   = $request->device;
        $advertise->ads_show = $request->ads_show;
        $advertise->ads_type = $request->ads_type;

        if ($request->hasFile('image')) {
            try {
                if ($request->ads_show == 1) {
                    $size = $request->device == 1 ? '1200x700' : '400x500';
                } else {
                    $size = '728x90';
                }
                $filename = fileUploader($request->image, getFilePath('ads'), $size, $oldFile);
            } catch (\Exception $e) {
                $notify[] = ['error', 'Image could not be uploaded'];
                return back()->withNotify($notify);
            }
        }

        $data = [
            'image'  => @$filename,
            'link'   => $request->ads_type == 'banner' ? $request->link : NULL,
            'script' => $request->ads_type == 'script' ? $request->script : NULL,
        ];

        $advertise->content = $data;
        $advertise->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function remove($id)
    {
        $ads = Advertise::findOrFail($id);
        fileManager()->removeFile(getFilePath('ads') . '/' . @$ads->content->image);
        $ads->delete();
        $notify[] = ['success', 'Advertise deleted successfully'];
        return back()->withNotify($notify);
    }

    public function videoAdvertise()
    {
        $pageTitle = "Video Advertises";
        $ads       = VideoAdvertise::latest()->paginate(getPaginate());
        return view('admin.advertise.videos', compact('ads', 'pageTitle'));
    }

    public function videoAdvertiseForm($id = 0)
    {
        $pageTitle     = $id ? 'Edit Video Advertises' : 'Add Video Advertises';
        $advertise     = $id ? VideoAdvertise::findOrFail($id) : [];
        $countriesJson = $this->countryJson();
        return view('admin.advertise.video_form', compact('pageTitle', 'advertise', 'countriesJson'));
    }

    public function videoAdvertiseStore(Request $request, $id = 0)
    {
        $isEdit = isset($id);

        $rules = [
            'type'             => 'required|integer|in:1,2',
            'ad_format'        => 'required|in:1,2',
            'frequency_cap'    => 'nullable|integer|gte:0',
            'ad_schedule_from' => 'nullable|date_format:H:i:s',
            'ad_schedule_to'   => 'nullable|date_format:H:i:s|after:ad_schedule_from',
            'geo_targets'      => 'required_without:is_global|array|min:1',
            'geo_targets.*'    => 'nullable',
        ];

        if (!$isEdit) {
            $rules['link']  = 'nullable|url|required_if:type,1';
            $rules['video'] = [
                'nullable',
                'required_if:type,2',
                new FileTypeValidate(['mp4', 'mkv', '3gp']),
            ];
        } else {
            $rules['link']  = 'nullable|url';
            $rules['video'] = [
                'nullable',
                new FileTypeValidate(['mp4', 'mkv', '3gp']),
            ];
        }

        $request->validate($rules);

        $videoAd      = $id ? VideoAdvertise::findOrFail($id) : new VideoAdvertise();
        $oldFile      = $id ? $videoAd->content->video ?? NULL : NULL;
        $notification = $id ? 'Advertise updated successfully' : 'Advertise created successfully';

        $videoAd->is_global = $request->is_global ? Status::YES : Status::NO;
        if (!$request->is_global) {
            $videoAd->geo_targets = $request->geo_targets;
        } else {
            $videoAd->geo_targets = NULL;
        }

        if ($id && $request->type == 1 && $videoAd->type == 2) {
            $videoUploader            = new VideoUploader();
            $videoUploader->oldFile   = $oldFile;
            $videoUploader->oldServer = $videoAd->server ?? null;
            $videoUploader->removeOldFile();
            $videoAd->content = '';
            $videoAd->save();
        }

        if ($id && $videoAd->type == 1 && $request->type == 2) {
            $videoAd->content = null;
            $videoAd->save();
        }

        $filename = $oldFile;
        if ($request->hasFile('video')) {
            $videoUploader            = new VideoUploader();
            $videoUploader->file      = $request->file('video');
            $videoUploader->oldFile   = $oldFile;
            $videoUploader->oldServer = $videoAd->server ?? NULL;

            $videoUploader->upload();

            if ($videoUploader->error) {
                return back()->withNotify([['error', 'Could not upload the Video']]);
            }

            $filename = $videoUploader->fileName;

            $serverMapping = [
                'current'       => Status::CURRENT_SERVER,
                'current-ftp'   => Status::FTP_SERVER,
                'wasabi'        => Status::WASABI_SERVER,
                'digital-ocean' => Status::DIGITAL_OCEAN_SERVER,
            ];
            $videoAd->server = $serverMapping[gs('server')] ?? Status::CURRENT_SERVER;
        }

        // Update video ad content
        $videoAd->type    = $request->type;
        $videoAd->content = [
            'link'  => $request->type == 1 ? ($request->link ?? NULL) : NULL,
            'video' => $request->type == 2 ? $filename : NULL,
        ];

        $videoAd->ad_format        = $request->ad_format;
        $videoAd->frequency_cap    = $request->frequency_cap ?? 0;
        $videoAd->is_daily         = $request->is_daily ? Status::YES : Status::NO;
        $videoAd->ad_schedule_from = $request->ad_schedule_from;
        $videoAd->ad_schedule_to   = $request->ad_schedule_to;
        $videoAd->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function videoAdvertiseRemove($id)
    {
        $ads = VideoAdvertise::findOrFail($id);
        if (@$ads->content->video) {
            try {
                $videoUploader            = new VideoUploader();
                $videoUploader->oldFile   = $ads->content->video;
                $videoUploader->oldServer = $ads->server ?? null;
                $videoUploader->removeOldFile();
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t remove your advertised video'];
                return back()->withNotify($notify);
            }
        }
        $ads->delete();
        $notify[] = ['success', 'Video advertise deleted successfully'];
        return back()->withNotify($notify);
    }

    private function countryJson()
    {
        return json_decode(file_get_contents(resource_path('views/partials/country.json')));
    }
}
