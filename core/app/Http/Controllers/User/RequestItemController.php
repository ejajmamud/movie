<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\CurlRequest;
use App\Models\AdminNotification;
use App\Models\Item;
use App\Models\ItemSubscribe;
use App\Models\RequestItem;
use App\Models\RequestItemUser;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RequestItemController extends Controller {
    private $apiKey;
    private $baseUrl = 'https://api.themoviedb.org/3';

    public function __construct() {
        if (!gs('request_item')) {
            abort(404);
        }
        $this->apiKey = gs('tmdb_api');
    }

    public function index() {
        $items = RequestItem::accepted()->unpublished();
        return $this->fetchItems("All Requested Items", $items);
    }

    public function myRequests() {
        $items = new RequestItem();
        return $this->fetchItems("My Requested Items", $items);
    }

    public function liveSearch(Request $request) {
        $items     = new RequestItem();
        $pageTitle = "Request Item for: " . $request->search;
        return $this->fetchItems($pageTitle, $items, true);
    }

    private function fetchItems($pageTitle, $query, $liveSearch = false) {

        $userId = auth()->id();

        if (request('myItem') == 'true' || request()->routeIs('user.request.item.mine')) {
            $items = $query->whereHas('users', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        } else {
            $items = $query->accepted()->unpublished();
        }

        $items = $items->select('request_items.*')
            ->groupBy('track_id')
            ->searchable(['item', 'track_id'])
            ->withCount([
                'votes as upvotes'   => fn($q)   => $q->where('vote', Status::VOTE_UP),
                'votes as downvotes' => fn($q) => $q->where('vote', Status::VOTE_DOWN),
                'users as total_requests'
            ])->with(['itemSubscribes'])->orderBy('id', 'desc')->paginate(getPaginate());

        if ($userId) {
            $items->each(function ($item) {
                $userVote             = $item->userVote();
                $item->user_vote      = $userVote?->vote;
                $item->user_subscribe = $item->userItemSubscribes()->exists ?? null;
            });
        }

        if (request('load') || $liveSearch) {
            if ($items->isNotEmpty()) {
                return response()->json([
                    'data'    => view('Template::user.request_item.fetch', compact('items'))->render(),
                    'hasMore' => $items->hasMorePages(),
                ]);
            } else {
                return response()->json(['error' => 'No more requested item!']);
            };
        }
        return view('Template::user.request_item.index', compact('pageTitle', 'items'));
    }

    public function create() {
        $pageTitle   = "Movie or Series Request";
        $recentItems = Item::hasVideo()->where('item_type', Status::SINGLE_ITEM)->orderBy('id', 'desc')->limit(18)->get();
        return view('Template::user.request_item.create', compact('pageTitle', 'recentItems'));
    }

    public function search(Request $request) {
        $query = $request->get('query');

        if (strlen($query) < 3) {
            return response()->json(['error' => 'Search query too short.'], 400);
        }
        $movies = $this->searchTmdb('movie', $query);
        $series = $this->searchTmdb('tv', $query);
        $items  = array_merge($movies, $series);


        $html = view('Template::user.request_item.item', compact('items'))->render();

        return response()->json(['html' => $html]);
    }

    private function searchTmdb($type, $query) {
        $url = "{$this->baseUrl}/search/{$type}?" . http_build_query([
            'api_key'  => $this->apiKey,
            'language' => 'en-US',
            'query'    => $query,
            'page'     => 1,
        ]);

        $headers  = ['Accept: application/json'];
        $response = CurlRequest::curlContent($url, $headers);

        return json_decode($response, true)['results'] ?? [];
    }

    public function store(Request $request) {
        $request->validate([
            'item'       => 'required',
            'track_id'   => 'required|integer',
            'recommend'  => 'required|string',
            'image_path' => 'required|string',
            'overview'   => 'nullable|string',
        ]);

        $requestItem = RequestItem::where('track_id', $request->track_id)->first();

        if (!$requestItem) {
            $requestItem = new RequestItem();
            $requestItem->track_id = $request->track_id;
            $requestItem->item = $request->item;
            $requestItem->image_path = $request->image_path;
            $requestItem->overview = $request->overview ?? 'No overview available';
            $requestItem->save();
        }

        $existingUserRequest = RequestItemUser::where('user_id', auth()->id())
            ->where('request_item_id', $requestItem->id)
            ->first();

        if ($existingUserRequest) {
            $notify[] = 'You have already requested this item';
            return back()->withNotify($notify);
        }

        if ($requestItem->status == Status::REQUEST_ITEM_REJECTED) {
            $requestItem->status = Status::REQUEST_ITEM_PENDING;
            $requestItem->save();
        }

        $userRequest = new RequestItemUser();
        $userRequest->request_item_id = $requestItem->id;
        $userRequest->user_id = auth()->id();
        $userRequest->recommend = $request->recommend;
        $userRequest->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = auth()->user()->id;
        $adminNotification->title     = 'A new request item has been submitted';
        $adminNotification->click_url = urlPath('admin.request.item.index');
        $adminNotification->save();

        $notify[] = ["success", "Item requested successfully"];
        return to_route('user.request.item.mine')->withNotify($notify);
    }

    public function vote(Request $request) {
        $validator = Validator::make($request->all(), [
            'track_id'  => 'required|integer|exists:request_items,id',
            'vote_type' => 'required|string|in:upvote,downvote',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors(),
            ]);
        }

        $userId     = auth()->id();
        $voteType   = $request->vote_type;
        $reqItemId  = $request->track_id;
        $voteDelete = false;

        $vote = Vote::where('user_id', $userId)->where('request_item_id', $reqItemId)->first();

        if ($vote) {
            if (($voteType === 'upvote' && $vote->vote === Status::VOTE_UP) || ($voteType === 'downvote' && $vote->vote === Status::VOTE_DOWN)) {
                $voteDelete = $voteType === 'upvote' ? 'upDelete' : 'downDelete';
                $vote->delete();
            } else {
                $vote->vote = $voteType === 'upvote' ? Status::VOTE_UP : Status::VOTE_DOWN;
                $vote->save();
            }
        } else {
            $vote                  = new Vote();
            $vote->user_id         = $userId;
            $vote->request_item_id = $reqItemId;
            $vote->vote            = $voteType === 'upvote' ? Status::VOTE_UP : Status::VOTE_DOWN;
            $vote->save();
        }

        $upVotes   = Vote::where('request_item_id', $reqItemId)->where('vote', Status::VOTE_UP)->count();
        $downVotes = Vote::where('request_item_id', $reqItemId)->where('vote', Status::VOTE_DOWN)->count();

        return response()->json([
            'status'     => 'success',
            'voteType'   => $voteType,
            'upVotes'    => $upVotes,
            'downVotes'  => $downVotes,
            'voteDelete' => $voteDelete,
            'message'    => ucfirst($voteType) . ' successfully recorded!',
        ]);
    }

    public function subscribe(Request $request) {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|integer|exists:request_items,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => $validator->errors(),
            ]);
        }

        $userId = auth()->id();
        $itemId = $request->item_id;

        $alreadySubscribed = ItemSubscribe::where('user_id', $userId)->where('request_item_id', $itemId)->first();
        if ($alreadySubscribed) {
            $alreadySubscribed->delete();
            return response()->json([
                'status'  => 'success',
                'enable'  => false,
                'message' => 'Subscribe item removed successfully!',
            ]);
        }

        $subscribe                  = new ItemSubscribe();
        $subscribe->user_id         = $userId;
        $subscribe->request_item_id = $itemId;
        $subscribe->save();

        return response()->json([
            'status'  => 'success',
            'enable'  => true,
            'message' => 'Successfully subscribed to item notifications!',
        ]);
    }
}
