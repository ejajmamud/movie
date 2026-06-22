<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Rules\FileTypeValidate;
use Illuminate\Http\Request;

class TournamentController extends Controller {
    public function index() {
        $pageTitle   = 'All Tournaments';
        $tournaments = Tournament::searchable(['name', 'short_name', 'season'])->latest()->paginate(getPaginate());
        return view('admin.tournaments.index', compact('pageTitle', 'tournaments'));
    }

    public function add($id = 0) {
        $tournament = null;
        if ($id) {
            $tournament = Tournament::find($id);
            $pageTitle  = 'Edit Tournament: ' . $tournament->name;
        } else {
            $pageTitle = 'Add Tournament';
        }
        return view('admin.tournaments.add', compact('pageTitle', 'tournament'));
    }

    public function store(Request $request, $id = 0) {
        $imageValidate = $id ? 'nullable' : 'required';
        $request->validate([
            'name'        => 'required|string|max:40',
            'short_name'  => 'required|string|max:40',
            'season'      => 'required|string|max:40',
            'description' => 'required|string|max:255',
            'price'       => 'required|numeric|gte:0',
            'image'       => [$imageValidate, 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);

        if ($id) {
            $tournament   = Tournament::findOrFail($id);
            $notification = 'Tournament updated successfully';
        } else {
            $tournament   = new Tournament();
            $notification = 'Tournament added successfully';
        }

        if ($request->hasFile('image')) {
            try {
                $tournament->image = fileUploader($request->image, getFilePath('tournament'), getFileSize('tournament'), @$tournament->image, getFileThumb('tournament'));
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }
        $tournament->name        = $request->name;
        $tournament->short_name  = $request->short_name;
        $tournament->season      = $request->season;
        $tournament->description = $request->description;
        $tournament->price       = $request->price;
        $tournament->version     = $request->version ? Status::PAID_VERSION : Status::FREE_VERSION;
        $tournament->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function status($id) {
        return Tournament::changeStatus($id);
    }


    
    public function seo($id)
    {
        $key = 'tournament';
        $data = Tournament::findOrFail($id);
        $pageTitle = 'SEO Configuration: ' .  $data->name;
        return view('admin.tournaments.seo', compact('pageTitle', 'key', 'data'));
    }

    public function seoStore(Request $request, $id)
    {
        $request->validate([
            'image' => ['nullable', new FileTypeValidate(['jpeg', 'jpg', 'png'])]
        ]);

        $data = Tournament::findOrFail($id);
        $image = @$data->seo_content->image;
        if ($request->hasFile('image')) {
            try {
                $path  = getFilePath('tournamentSeo');
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
        return to_route('admin.tournament.seo', $data->id)->withNotify($notify);
    }
}
