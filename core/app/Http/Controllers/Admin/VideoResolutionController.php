<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoResolution;
use Illuminate\Http\Request;

class VideoResolutionController extends Controller
{
    public function index()
    {
        $pageTitle = 'Video Resolution';
        $resolutions = VideoResolution::get();
        return view('admin.video_resolution.index', compact('pageTitle', 'resolutions'));
    }

    public function save(Request $request, $id)
    {
        $request->validate([
            'width' => 'required|numeric|unique:video_resolutions,width,' . $id,
            'height' => 'required|numeric|unique:video_resolutions,height,' . $id,
        ]);

        $resolution = VideoResolution::findOrFail($id);
        $resolution->width = $request->width;
        $resolution->height = $request->height;
        $resolution->save();

        $notify[] = ['success', 'Video resolution update successfully'];
        return back()->withNotify($notify);
    }
}
