<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Reel;
use App\Models\ReelHistory;
use Illuminate\Http\Request;

class ReelController extends Controller {
    public function like(Request $request) {
        $request->validate([
            'id'   => 'required|integer',
            'type' => 'required|string|in:likes,unlikes',
        ]);

        $reel = Reel::where('id', $request->id)->first();
        if (!$reel) {
            return response()->json(['error' => 'Reel not found.']);
        }

        $reelHistory = ReelHistory::where('reel_id', $reel->id)->where('user_id', auth()->id())->first();
        if ($request->type == 'likes') {
            if ($reelHistory && $reelHistory->likes) {
                return response()->json(['error' => 'You have already liked this reel.']);
            }
        } else {
            if ($reelHistory && $reelHistory->unlikes) {
                return response()->json(['error' => 'You have already unliked this reel.']);
            }
        }

        if (!$reelHistory) {
            $reelHistory          = new ReelHistory();
            $reelHistory->reel_id = $reel->id;
            $reelHistory->user_id = auth()->id();
        }

        $reelHistory->likes   = $request->type == 'likes' ? Status::YES : Status::NO;
        $reelHistory->unlikes = $request->type == 'unlikes' ? Status::YES : Status::NO;
        $reelHistory->save();
        if ($request->type == 'unlikes') {
            $reel->increment('unlikes');
            if ($reel->likes) {
                $reel->decrement('likes');
            }
        } else {
            $reel->increment('likes');
            if ($reel->unlikes) {
                $reel->decrement('unlikes');
            }
        }
        return response()->json(['success' => 'Reel liked successfully.']);
    }

    public function list(Request $request) {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $reel = Reel::where('id', $request->id)->first();
        if (!$reel) {
            return response()->json(['error' => 'Reel not found.']);
        }

        $list = ReelHistory::where('reel_id', $reel->id)->where('user_id', auth()->id())->first();
        if (!$list) {
            $list          = new ReelHistory();
            $list->reel_id = $reel->id;
            $list->user_id = auth()->id();
        }
        if ($list && $list->list == Status::YES) {
            $list->list   = Status::NO;
            $notification = 'Reel remove to your list.';
            $type         = 'remove';
        } else {
            $list->list   = Status::YES;
            $notification = 'Reel added to your list successfully.';
            $type         = 'add';
        }
        $list->save();
        return response()->json(['success' => $notification, 'type' => $type]);
    }
}
