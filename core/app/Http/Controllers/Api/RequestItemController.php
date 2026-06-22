<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Constants\Status;
use App\Lib\CurlRequest;
use App\Models\AdminNotification;
use App\Models\Item;
use App\Models\ItemSubscribe;
use App\Models\RequestItem;
use App\Models\RequestItemUser;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RequestItemController extends Controller
{
    private $apiKey;
    private $baseUrl = 'https://api.themoviedb.org/3';
    private $baseImg = 'https://image.tmdb.org/t/p/w200';

    public function __construct()
    {
        if (!gs('request_item')) {
            $notify[] = 'Request item feature is disabled';
            return responseError('feature_disabled', $notify);
        }
        $this->apiKey = gs('tmdb_api');
    }

    public function index(Request $request)
    {
        try {
            $items = RequestItem::accepted()->unpublished();
            return $this->fetchItems($items);
        } catch (\Exception $e) {
            $notify[] = 'Failed to fetch requested items';
            return responseError('fetch_failed', $notify);
        }
    }

    public function myRequests(Request $request)
    {
        try {

            $items = new RequestItem();
            return $this->fetchItems($items, true, true);
        } catch (\Exception $e) {
            $notify[] = 'Failed to fetch your requested items';
            return responseError('fetch_failed', $notify);
        }
    }

    public function liveSearch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|min:1'
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        try {
            $items = new RequestItem();
            return $this->fetchItems($items, true);
        } catch (\Exception $e) {
            $notify[] = 'Search failed';
            return responseError('search_failed', $notify);
        }
    }

    private function fetchItems($query, $liveSearch = false, $forceMyItem = false)
    {
        $userId = auth()->id();

        if ($forceMyItem || request('myItem') == 'true' || request()->routeIs('user.request.item.mine')) {
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
                $notify[] = 'Requested items fetched successfully';
                return responseSuccess('items_fetched', $notify, [
                    'image_path' => $this->baseImg,
                    'data'    => $items,
                    'hasMore' => $items->hasMorePages(),
                ]);
            }
        }

        $notify[] = 'Requested items fetched successfully';
        return responseSuccess('items_fetched', $notify, [
            'items' => $items->items(),
            'image_path' => $this->baseImg,
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'has_more_pages' => $items->hasMorePages(),
                'next_page_url' => $items->nextPageUrl(),
                'prev_page_url' => $items->previousPageUrl(),
            ]
        ]);
    }

    public function recentItems()
    {
        try {
            $recentItems = Item::hasVideo()
                ->where('item_type', Status::SINGLE_ITEM)
                ->orderBy('id', 'desc')
                ->limit(18)
                ->get();

            $notify[] = 'Recent items fetched successfully';
            return responseSuccess('recent_items', $notify, [
                'items' => $recentItems
            ]);
        } catch (\Exception $e) {
            $notify[] = 'Failed to fetch recent items';
            return responseError('fetch_failed', $notify);
        }
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:3'
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        try {
            $query = $request->get('query');
            $movies = $this->searchTmdb('movie', $query);
            $series = $this->searchTmdb('tv', $query);
            $items = array_merge($movies, $series);

            $notify[] = 'Search completed successfully';
            return responseSuccess('search_completed', $notify, [
                'movies' => $movies,
                'series' => $series,
                'all_items' => $items
            ]);
        } catch (\Exception $e) {
            $notify[] = 'Search failed';
            return responseError('search_failed', $notify);
        }
    }

    private function searchTmdb($type, $query)
    {
        $url = "{$this->baseUrl}/search/{$type}?" . http_build_query([
            'api_key' => $this->apiKey,
            'language' => 'en-US',
            'query' => $query,
            'page' => 1,
        ]);

        $headers = ['Accept: application/json'];
        $response = CurlRequest::curlContent($url, $headers);

        return json_decode($response, true)['results'] ?? [];
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item' => 'required|string',
            'track_id' => 'required|integer',
            'recommend' => 'required|string',
            'image_path' => 'required|string',
            'overview' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        try {

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
                return responseError('already_requested', $notify);
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

            $adminNotification = new AdminNotification();
            $adminNotification->user_id = auth()->user()->id;
            $adminNotification->title = 'A new request item has been submitted';
            $adminNotification->click_url = urlPath('admin.request.item.index');
            $adminNotification->save();

            $notify[] = 'Item requested successfully';
            return responseSuccess('item_requested', $notify, [
                'request_item' => $requestItem,
                'base_image' => $this->baseImg
            ]);
        } catch (\Exception $e) {
            $notify[] = 'Failed to create request item';
            return responseError('request_failed', $notify);
        }
    }

    public function vote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'track_id' => 'required|integer|exists:request_items,id',
            'vote_type' => 'required|string|in:upvote,downvote',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        try {
            $userId = auth()->id();
            $voteType = $request->vote_type;
            $reqItemId = $request->track_id;
            $voteDelete = false;

            $vote = Vote::where('user_id', $userId)
                ->where('request_item_id', $reqItemId)
                ->first();

            if ($vote) {
                if (($voteType === 'upvote' && $vote->vote === Status::VOTE_UP) ||
                    ($voteType === 'downvote' && $vote->vote === Status::VOTE_DOWN)
                ) {
                    $voteDelete = $voteType === 'upvote' ? 'upDelete' : 'downDelete';
                    $vote->delete();
                } else {
                    $vote->vote = $voteType === 'upvote' ? Status::VOTE_UP : Status::VOTE_DOWN;
                    $vote->save();
                }
            } else {
                $vote = new Vote();
                $vote->user_id = $userId;
                $vote->request_item_id = $reqItemId;
                $vote->vote = $voteType === 'upvote' ? Status::VOTE_UP : Status::VOTE_DOWN;
                $vote->save();
            }

            $upVotes = Vote::where('request_item_id', $reqItemId)
                ->where('vote', Status::VOTE_UP)
                ->count();
            $downVotes = Vote::where('request_item_id', $reqItemId)
                ->where('vote', Status::VOTE_DOWN)
                ->count();

            $notify[] = ucfirst($voteType) . ' successfully recorded!';
            return responseSuccess('vote_recorded', $notify, [
                'vote_type' => $voteType,
                'upvotes' => $upVotes,
                'downvotes' => $downVotes,
                'vote_delete' => $voteDelete,
                'user_vote' => $voteDelete ? null : ($voteType === 'upvote' ? Status::VOTE_UP : Status::VOTE_DOWN)
            ]);
        } catch (\Exception $e) {
            $notify[] = 'Failed to record vote';
            return responseError('vote_failed', $notify);
        }
    }

    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|integer|exists:request_items,id',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        try {
            $userId = auth()->id();
            $itemId = $request->item_id;

            $alreadySubscribed = ItemSubscribe::where('user_id', $userId)
                ->where('request_item_id', $itemId)
                ->first();

            if ($alreadySubscribed) {
                $alreadySubscribed->delete();
                $notify[] = 'Subscribe item removed successfully!';
                return responseSuccess('subscription_removed', $notify, [
                    'subscribed' => false
                ]);
            }

            $subscribe = new ItemSubscribe();
            $subscribe->user_id = $userId;
            $subscribe->request_item_id = $itemId;
            $subscribe->save();

            $notify[] = 'Successfully subscribed to item notifications!';
            return responseSuccess('subscription_added', $notify, [
                'subscribed' => true
            ]);
        } catch (\Exception $e) {
            $notify[] = 'Failed to update subscription';
            return responseError('subscription_failed', $notify);
        }
    }

    public function show($id)
    {
        try {
            $item = RequestItem::with(['votes', 'itemSubscribes'])
                ->withCount([
                    'votes as upvotes' => fn($q) => $q->where('vote', Status::VOTE_UP),
                    'votes as downvotes' => fn($q) => $q->where('vote', Status::VOTE_DOWN),
                ])
                ->find($id);

            if (!$item) {
                $notify[] = 'Request item not found';
                return responseError('item_not_found', $notify);
            }

            $userId = auth()->id();
            if ($userId) {
                $userVote = $item->userVote();
                $item->user_vote = $userVote?->vote;
                $item->user_subscribe = $item->userItemSubscribes()->exists() ?? false;
            }

            $notify[] = 'Request item fetched successfully';
            return responseSuccess('item_fetched', $notify, [
                'item' => $item
            ]);
        } catch (\Exception $e) {
            $notify[] = 'Failed to fetch request item';
            return responseError('fetch_failed', $notify);
        }
    }

    public function destroy($id)
    {
        try {
            $item = RequestItem::where('id', $id)
                ->whereHas('users', function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->first();

            if (!$item) {
                $notify[] = 'Request item not found or you are not authorized to delete it';
                return responseError('item_not_found', $notify);
            }

            $item->delete();

            $notify[] = 'Request item deleted successfully';
            return responseSuccess('item_deleted', $notify);
        } catch (\Exception $e) {
            $notify[] = 'Failed to delete request item';
            return responseError('delete_failed', $notify);
        }
    }
}
