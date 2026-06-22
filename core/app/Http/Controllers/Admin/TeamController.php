<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamController extends Controller {
    public function index() {
        $pageTitle = 'All Teams';
        $teams     = Team::searchable(['name'])->latest()->paginate(getPaginate());
        return view('admin.teams.index', compact('pageTitle', 'teams'));
    }

    public function store(Request $request, $id = 0) {
        $request->validate([
            'name' => 'required|string|max:40|unique:teams,name,' . $id,
        ]);

        if ($id) {
            $team         = Team::findOrFail($id);
            $notification = 'Team updated successfully';
        } else {
            $team         = new Team();
            $notification = 'Team added successfully';
        }
        $team->name = $request->name;
        $team->save();

        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function status($id) {
        return Team::changeStatus($id);
    }
}
