<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\RequestItem;
use Illuminate\Http\Request;

class ManageRequestItemController extends Controller
{

    public function index()
    {
        $pageTitle = 'All Request Items';
        $items     = RequestItem::searchable(['item', 'track_id'])
            ->withCount([
                'votes as upvotes'   => function ($q) {
                    $q->where('vote', Status::VOTE_UP);
                },
                'votes as downvotes' => function ($q) {
                    $q->where('vote', Status::VOTE_DOWN);
                },
                'itemSubscribes',
            ])->with('users.user')
            ->latest()->paginate(getPaginate());
        return view('admin.request_item.index', compact('pageTitle', 'items'));
    }

    public function details($id)
    {
        $pageTitle = 'Request Items Details';
        $item     = RequestItem::with('users.user')->findOrFail($id);
        $items = $item->users;
        return view('admin.request_item.details', compact('pageTitle', 'items', 'item'));
    }

    public function status($id, $status)
    {
        $item         = RequestItem::with('users.user')->findOrFail($id);
        $item->status = $status;
        $item->save();
        foreach ($item->users as $requestUser) {
            $user = $requestUser->user;
            if (!$user) continue;

            if ($item->status == Status::REQUEST_ITEM_ACCEPTED) {
                notify($user, 'REQUEST_ITEM_ACCEPTED', [
                    'item_name' => $item->item,
                ]);
            }
            if ($item->status == Status::REQUEST_ITEM_REJECTED) {
                notify($user, 'REQUEST_ITEM_REJECTED', [
                    'item_name' => $item->item,
                ]);
            }
        }

        $notify[] = ['success', 'Request Item status updated successfully.'];
        return back()->withNotify($notify);
    }

    public function publish(Request $request)
    {
        $request->validate([
            'item_track_id' => 'required|string|exists:request_items,track_id',
            'item_link'     => 'required_if:is_notify,on|nullable|url',
        ]);

        $item = RequestItem::where('status', Status::REQUEST_ITEM_ACCEPTED)->where('track_id', $request->item_track_id)->with('users.user', function ($q) {
            $q->active();
        }, 'itemSubscribes')->firstOrFail();

        $item->is_publish = Status::YES;
        $item->save();

        if ($request->is_notify) {
            $subscribes = $item->itemSubscribes;
            if ($subscribes) {
                foreach ($subscribes as $subscribe) {
                    notify($subscribe->user, 'INTEREST_ITEM_PUBLISHED', [
                        'item_name'  => $item->item,
                        'quick_link' => $request->item_link,
                    ]);
                }
            }
            foreach ($item->users as $requestUser) {
                $user = $requestUser->user;
                if ($user) {
                    notify($user, 'INTEREST_ITEM_PUBLISHED', [
                        'item_name'  => $item->item,
                        'quick_link' => $request->item_link,
                    ]);
                }
            }
        }

        $notify[] = ['success', 'The requested item status has been published successfully.'];
        return back()->withNotify($notify);
    }
}