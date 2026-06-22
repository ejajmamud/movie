<?php

namespace App\Http\Controllers\Api;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Reel;
use App\Models\ReelHistory;
use Illuminate\Http\Request;

class ReelController extends Controller
{

    public function like(Request $request)
    {
        $request->validate([
            'id'   => 'required|integer',
            'type' => 'required|string|in:likes,unlikes',
        ]);

        $reel = Reel::where('id', $request->id)->first();
        if (!$reel) {
            $notify[] = 'Reel not found';
            return responseError('not_found', $notify);
        }

        $reelHistory = ReelHistory::where('reel_id', $reel->id)->where('user_id', auth()->id())->first();
        if ($request->type == 'likes') {
            if ($reelHistory && $reelHistory->likes) {
                $notify[] = 'You have already liked this reel';
                return responseError('already_liked', $notify);
            }
        } else {
            if ($reelHistory && $reelHistory->unlikes) {
                $notify[] = 'You have already unliked this reel';
                return responseError('already_unliked', $notify);
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
        $type     = $request->type == 'likes' ? 'liked' : 'unliked';
        $notify[] = 'Reel ' . $type . ' successfully';
        $remark = 'reel_' . $type;
        return responseSuccess($remark, $notify);
    }

    public function list(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $reel = Reel::where('id', $request->id)->first();
        if (!$reel) {
            $notify[] = 'Reel not found';
            return responseError('not_found', $notify);
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
            $remark       = 'removed';
        } else {
            $list->list   = Status::YES;
            $notification = 'Reel added to your list successfully.';
            $type         = 'add';
            $remark       = 'added';
        }
        $list->save();
        $notify[] = $notification;
        $remark = $remark . '_list';
        return responseSuccess('remark', $notify, [
            'list' => $list,
            'type' => $type,
        ]);
    }
}
