<?php

namespace App\Http\Controllers\Api;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Advertise;
use App\Models\Category;
use App\Models\ChannelCategory;
use App\Models\Episode;
use App\Models\Frontend;
use App\Models\Game;
use App\Models\History;
use App\Models\Item;
use App\Models\Language;
use App\Models\LiveComment;
use App\Models\LiveTelevision;
use App\Models\Reel;
use App\Models\ReelHistory;
use App\Models\Slider;
use App\Models\SubCategory;
use App\Models\Subscription;
use App\Models\Tournament;
use App\Models\VideoReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FrontendController extends Controller
{

    public function logo()
    {
        $notify[] = 'Logo Information';
        $logo     = getFilePath('logoIcon') . '/logo.png';
        $favicon  = getFilePath('logoIcon') . '/favicon.png';

        return responseSuccess('logo_info', $notify, [
            'logo'    => $logo,
            'favicon' => $favicon,
        ]);
    }

    public function welcomeInfo()
    {
        $notify[] = 'Welcome Info';
        $welcome  = Frontend::where('tempname', activeTemplateName())->where('data_keys', 'app_welcome.content')->first();
        $path     = 'assets/images/frontend/app_welcome';

        return responseSuccess('welcome_info', $notify, [
            'welcome' => $welcome->data_values,
            'path'    => $path,
        ]);
    }

    public function sliders()
    {
        $sliders  = Slider::with('item', 'item.category', 'item.sub_category')->get();
        $notify[] = 'All Sliders';
        $path     = getFilePath('item_landscape');

        return responseSuccess('all_sliders', $notify, [
            'sliders'        => $sliders,
            'landscape_path' => $path,
        ]);
    }

    public function liveTelevision($scope = null)
    {
        $notify[] = 'Live Television';
        if ($scope == 'show_all') {
            $televisions = ChannelCategory::active()->withWhereHas('channels', function ($query) {
                $query->active();
            })->apiQuery();
        } else {
            $televisions = LiveTelevision::active()->apiQuery();
        }
        $imagePath = getFilePath('television');

        return responseSuccess('live_television', $notify, [
            'televisions' => $televisions,
            'image_path'  => $imagePath,
        ]);
    }

    public function featured()
    {
        $notify[]     = 'Featured';
        $featured     = Item::active()->hasVideo()->where('featured', Status::ENABLE)->apiQuery();
        $imagePath    = getFilePath('item_landscape');
        $portraitPath = getFilePath('item_portrait');

        return responseSuccess('featured', $notify, [
            'featured'       => $featured,
            'landscape_path' => $imagePath,
            'portrait_path'  => $portraitPath,
        ]);
    }

    public function recentlyAdded()
    {
        $notify[]      = 'Recently Added';
        $recentlyAdded = Item::active()->hasVideo()->where('item_type', Status::SINGLE_ITEM)->apiQuery();
        $imagePath     = getFilePath('item_portrait');
        $landscapePath = getFilePath('item_landscape');

        return responseSuccess('recently_added', $notify, [
            'recent'         => $recentlyAdded,
            'portrait_path'  => $imagePath,
            'landscape_path' => $landscapePath,
        ]);
    }

    public function latestSeries()
    {
        $notify[]      = 'Latest Series';
        $latestSeries  = Item::active()->hasVideo()->where('item_type', Status::EPISODE_ITEM)->apiQuery();
        $imagePath     = getFilePath('item_portrait');
        $landscapePath = getFilePath('item_landscape');

        return responseSuccess('latest-series', $notify, [
            'latest'         => $latestSeries,
            'portrait_path'  => $imagePath,
            'landscape_path' => $landscapePath,
        ]);
    }

    public function single()
    {
        $notify[] = 'Single Item';

        $single = Item::active()->hasVideo()->where('single', 1)->with('category')->apiQuery();

        $imagePath     = getFilePath('item_portrait');
        $landscapePath = getFilePath('item_landscape');

        return responseSuccess('single', $notify, [
            'single'         => $single,
            'portrait_path'  => $imagePath,
            'landscape_path' => $landscapePath,
        ]);
    }

    public function trailer()
    {
        $notify[] = 'Trailer';
        $trailer  = Item::active()->hasVideo()->where('item_type', Status::SINGLE_ITEM)->where('is_trailer', Status::TRAILER)->apiQuery();

        $imagePath     = getFilePath('item_portrait');
        $landscapePath = getFilePath('item_landscape');

        return responseSuccess('trailer', $notify, [
            'trailer'        => $trailer,
            'portrait_path'  => $imagePath,
            'landscape_path' => $landscapePath,
        ]);
    }

    public function rent()
    {
        $notify[] = 'Rent';
        $rent     = Item::active()->hasVideo()->where('item_type', Status::SINGLE_ITEM)->where('version', Status::RENT_VERSION)->apiQuery();

        $imagePath     = getFilePath('item_portrait');
        $landscapePath = getFilePath('item_landscape');

        return responseSuccess('rent', $notify, [
            'rent'           => $rent,
            'portrait_path'  => $imagePath,
            'landscape_path' => $landscapePath,
        ]);
    }

    public function freeZone()
    {
        $notify[]      = 'Free Zone';
        $freeZone      = Item::active()->hasVideo()->free()->orderBy('id', 'desc')->apiQuery();
        $imagePath     = getFilePath('item_portrait');
        $landscapePath = getFilePath('item_landscape');

        return responseSuccess('free_zone', $notify, [
            'free_zone'      => $freeZone,
            'portrait_path'  => $imagePath,
            'landscape_path' => $landscapePath,
        ]);
    }

    public function categories()
    {
        $notify[]   = 'All Categories';
        $categories = Category::where('status', Status::ENABLE)->apiQuery();

        return responseSuccess('all-categories', $notify, [
            'categories' => $categories,
        ]);
    }

    public function subcategories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $notify[]      = 'SubCategories';
        $subcategories = SubCategory::where('category_id', $request->category_id)->where('status', Status::ENABLE)->apiQuery();

        return responseSuccess('sub-categories', $notify, [
            'subcategories' => $subcategories,
        ]);
    }

    public function search(Request $request)
    {
        $notify[] = 'Search';
        $search   = $request->search;

        $items = Item::search($search)->where('status', 1)->where(function ($query) {
            $query->orWhereHas('video')->orWhereHas('episodes', function ($video) {
                $video->where('status', 1)->whereHas('video');
            });
        });

        if ($request->category_id) {
            $items = $items->where('category_id', $request->category_id);
        }

        if ($request->subcategory_id) {
            $items = $items->where('sub_category_id', $request->subcategory_id);
        }

        $items = $items->orderBy('id', 'desc')->paginate(getPaginate());

        $imagePath     = getFilePath('item_portrait');
        $landscapePath = getFilePath('item_landscape');

        return responseSuccess('search', $notify, [
            'items'          => $items,
            'portrait_path'  => $imagePath,
            'landscape_path' => $landscapePath,
        ]);
    }

    public function watchVideo(Request $request)
    {
        $item = Item::hasVideo()->where('status', 1)->where('id', $request->item_id)->with('category', 'sub_category')->first();
        $notify[] = 'Item not found';
        if (!$item) {
            return responseError('not_found', $notify);
        }

        $item->increment('view');

        $relatedItems = Item::hasVideo()->orderBy('id', 'desc')->where('category_id', $item->category_id)->where('id', '!=', $request->item_id)->limit(6)->get();

        $imagePath     = getFilePath('item_portrait');
        $landscapePath = getFilePath('item_landscape');
        $episodePath   = getFilePath('episode');

        $userHasSubscribed = (auth()->check() && auth()->user()->exp > now()) ? Status::ENABLE : Status::DISABLE;
        if ($item->item_type == Status::EPISODE_ITEM) {
            $episodes = Episode::hasVideo()->where('item_id', $request->item_id)->get();

            if ($episodes->count()) {
                $this->storeHistory(0, $episodes[0]->id);
                $this->storeVideoReport(0, $episodes[0]->id);
            }

            $notify[] = 'Episode Video';
            return responseSuccess('episode_video', $notify, [
                'item'           => $item,
                'episodes'       => $episodes,
                'related_items'  => $relatedItems,
                'portrait_path'  => $imagePath,
                'landscape_path' => $landscapePath,
                'episode_path'   => $episodePath,
            ]);
        }

        $watchEligible = $this->checkWatchEligableItem($item, $userHasSubscribed);

        if (!$watchEligible[0]) {
            $remark = 'unauthorized_' . $watchEligible[1];
            return responseError($remark, $notify, [
                'remark'  => 'unauthorized_' . $watchEligible[1],
                'status'  => 'error',
                'message' => ['error' => 'Unauthorized user'],
                'data'    => [
                    'item'           => $item,
                    'portrait_path'  => $imagePath,
                    'landscape_path' => $landscapePath,
                    'related_items'  => $relatedItems,
                ],
            ]);
        }

        $this->storeHistory($item->id, 0);
        $this->storeVideoReport($item->id, 0);

        $notify[] = 'Item Video';

        return responseSuccess('item_video', $notify, [
            'item'           => $item,
            'related_items'  => $relatedItems,
            'portrait_path'  => $imagePath,
            'landscape_path' => $landscapePath,
            'episode_path'   => $episodePath,
            'watchEligible'  => $watchEligible[0],
            'type'           => $watchEligible[1],
        ]);
    }

    protected function checkWatchEligableItem($item, $userHasSubscribed)
    {
        if ($item->version == Status::PAID_VERSION) {
            $watchEligible = $userHasSubscribed ? true : false;
            $type          = 'paid';
        } else if ($item->version == Status::RENT_VERSION) {
            $hasSubscribedItem = Subscription::active()->where('user_id', auth()->id())->where('item_id', $item->id)->whereDate('expired_date', '>', now())->exists();
            if ($item->exclude_plan) {
                $watchEligible = $hasSubscribedItem ? true : false;
            } else {
                $watchEligible = ($userHasSubscribed || $hasSubscribedItem) ? true : false;
            }
            $type = 'rent';
        } else {
            $watchEligible = true;
            $type          = 'free';
        }
        return [$watchEligible, $type];
    }

    protected function checkWatchEligibleEpisode($episode, $userHasSubscribed)
    {
        if ($episode->version == Status::PAID_VERSION) {
            $watchEligible = $userHasSubscribed ? true : false;
            $type          = 'paid';
        } else if ($episode->version == Status::RENT_VERSION) {
            $hasSubscribedItem = Subscription::active()->where('user_id', auth()->id())->where('item_id', $episode->item_id)->whereDate('expired_date', '>', now())->exists();
            if (@$episode->item->exclude_plan) {
                $watchEligible = $hasSubscribedItem ? true : false;
            } else {
                $watchEligible = ($userHasSubscribed || $hasSubscribedItem) ? true : false;
            }
            $type = 'rent';
        } else {
            $watchEligible = true;
            $type          = 'free';
        }
        return [$watchEligible, $type];
    }

    public function playVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $item = Item::hasVideo()->where('status', 1)->where('id', $request->item_id)->first();
        if (!$item) {
            $notify[] = 'Item not found';
            return responseError('not_found', $notify);
        }

        if ($item->item_type == Status::EPISODE_ITEM && !$request->episode_id) {
            $notify[] = 'Episode id field is required';
            return responseError('not_found', $notify);
        }

        $userHasSubscribed = (auth()->check() && auth()->user()->exp > now()) ? Status::ENABLE : Status::DISABLE;

        if ($item->item_type == Status::EPISODE_ITEM) {
            $episode = Episode::hasVideo()->where('item_id', $request->item_id)->find($request->episode_id);

            if (!$episode) {
                $notify[] = 'No episode found';
                return responseError('no_episode', $notify);
            }
            $watchEligible = $this->checkWatchEligibleEpisode($episode, $userHasSubscribed);

            if (!$watchEligible[0]) {
                $notify[] = 'Unauthorized user';
                $remark = 'unauthorized_' . $watchEligible[1];
                return responseError($remark, $notify, [
                    'item' => $item,
                ]);
            }

            $video    = $episode->video;
            $remark   = 'episode_video';
            $notify[] = 'Episode Video';
        } else {

            $watchEligible = $this->checkWatchEligableItem($item, $userHasSubscribed);
            if (!$watchEligible[0]) {
                $remark  = 'unauthorized_' . $watchEligible[1];
                $notify[] = 'Unauthorized user';
                return responseError($remark, $notify, [
                    'item' => $item,
                ]);
            }

            $video    = $item->video;
            $remark   = 'item_video';
            $notify[] = 'Item Video';
        }

        $videoFile    = $this->videoList($video);
        $subtitles    = $video->subtitles()->get();
        $adsTime      = $video->getAds();
        $subtitlePath = getFilePath('subtitle');

        return responseSuccess($remark, $notify, [
            'video'         => $videoFile,
            'subtitles'     => !blank($subtitles) ? $subtitles : null,
            'adsTime'       => !blank($adsTime) ? $adsTime : null,
            'subtitlePath'  => $subtitlePath,
            'watchEligible' => $watchEligible[0],
            'type'          => $watchEligible[1],
        ]);
    }

    private function videoList($video)
    {
        $videoFile = [];
        if ($video->three_sixty_video) {
            $videoFile[] = [
                'content' => getVideoFile($video, 'three_sixty'),
                'size'    => 360,
            ];
        }
        if ($video->four_eighty_video) {
            $videoFile[] = [
                'content' => getVideoFile($video, 'four_eighty'),
                'size'    => 480,
            ];
        }
        if ($video->seven_twenty_video) {
            $videoFile[] = [
                'content' => getVideoFile($video, 'seven_twenty'),
                'size'    => 720,
            ];
        }
        if ($video->thousand_eighty_video) {
            $videoFile[] = [
                'content' => getVideoFile($video, 'thousand_eighty'),
                'size'    => 1080,
            ];
        }

        return json_decode(json_encode($videoFile, true));
    }

    protected function storeHistory($itemId = null, $episodeId = null)
    {
        if (auth()->check()) {
            if ($itemId) {
                $history = History::where('user_id', auth()->id())->orderBy('id', 'desc')->limit(1)->first();
                if (!$history || ($history && $history->item_id != $itemId)) {
                    $history          = new History();
                    $history->user_id = auth()->id();
                    $history->item_id = $itemId;
                    $history->save();
                }
            }
            if ($episodeId) {
                $history = History::where('user_id', auth()->id())->orderBy('id', 'desc')->limit(1)->first();
                if (!$history || ($history && $history->episode_id != $episodeId)) {
                    $history             = new History();
                    $history->user_id    = auth()->id();
                    $history->episode_id = $episodeId;
                    $history->save();
                }
            }
        }
    }

    protected function storeVideoReport($itemId = null, $episodeId = null)
    {
        $deviceId = md5($_SERVER['HTTP_USER_AGENT']);

        if ($itemId) {
            $report = VideoReport::whereDate('created_at', now())->where('device_id', $deviceId)->where('item_id', $itemId)->exists();
        }

        if ($episodeId) {
            $report = VideoReport::whereDate('created_at', now())->where('device_id', $deviceId)->where('episode_id', $episodeId)->exists();
        }
        if (!$report) {
            $videoReport             = new VideoReport();
            $videoReport->device_id  = $deviceId;
            $videoReport->item_id    = $itemId ?? 0;
            $videoReport->episode_id = $episodeId ?? 0;
            $videoReport->save();
        }
    }

    public function policyPages()
    {
        $notify[]    = 'Policy Page';
        $policyPages = Frontend::where('tempname', gs('active_template'))->where('data_keys', 'policy_pages.element')->get();

        return responseSuccess('policy', $notify, [
            'policy_pages' => $policyPages,
        ]);
    }

    public function movies()
    {
        $notify[]      = 'All Movies';
        $movies        = Item::active()->hasVideo()->where('item_type', Status::SINGLE_ITEM)->apiQuery();
        $imagePath     = getFilePath('item_portrait');
        $landscapePath = getFilePath('item_landscape');

        return responseSuccess('all_movies', $notify, [
            'movies'         => $movies,
            'portrait_path'  => $imagePath,
            'landscape_path' => $landscapePath,
        ]);
    }

    public function episodes()
    {
        $notify[]      = 'All Episodes';
        $episodes      = Item::active()->hasVideo()->where('item_type', Status::EPISODE_ITEM)->apiQuery();
        $imagePath     = getFilePath('item_portrait');
        $landscapePath = getFilePath('item_landscape');

        return responseSuccess('all_episodes', $notify, [
            'episodes'       => $episodes,
            'portrait_path'  => $imagePath,
            'landscape_path' => $landscapePath,
        ]);
    }

    public function language($code = 'en')
    {
        $language = Language::where('code', $code)->first();
        if (!$language) {
            $code = 'en';
        }
        $languageData = json_decode(file_get_contents(resource_path('lang/' . $code . '.json')));
        $languages    = Language::get();
        $notify[]     = 'Language Data';
        return responseSuccess('language_data', $notify, [
            'language_data' => $languageData,
            'languages'     => $languages,
            'image_path'    => getFilePath('language'),
        ]);
    }
    public function popUpAds()
    {
        $advertise = Advertise::where('device', 2)->where('ads_show', 1)->where('ads_type', 'banner')->inRandomOrder()->first();
        if (!$advertise) {
            $notify[] = 'Advertise not found';
            return responseError('advertise_not_found', $notify);
        }
        $imagePath = getFilePath('ads');
        $notify[] = 'Popup Ads';

        return responseSuccess('pop_up_ad', $notify, [
            'advertise' => $advertise,
            'imagePath' => $imagePath,
        ]);
    }
    public function shortVideos($id = 0, $route = null)
    {
        $reels = Reel::query();
        if (request()->lastId) {
            $reels = $reels->where('id', '<', request()->lastId);
        }
        if ($id) {
            $firstReel = clone $reels;
            $firstReel = $firstReel->where('id', $id)->firstOrFail();
            $reels     = clone $reels;
            $reels     = $reels->where('id', '!=', $firstReel->id)->inRandomOrder()->take(9)->get();
            $reels->prepend($firstReel);
        } else {
            if ($route == 'favorite') {
                $reelId = ReelHistory::where('user_id', auth()->id())->where('list', Status::YES)->pluck('reel_id')->toArray();
                $reels  = $reels->whereIn('id', $reelId);
            }
            $reels = $reels->inRandomOrder()->take(10)->get();
        }

        $userLikesId   = [];
        $userUnLikesId = [];
        $userListId    = [];
        if (auth()->check()) {
            $userReact     = ReelHistory::where('user_id', auth()->id())->get();
            $userLikesId   = $userReact->where('likes', Status::YES)->pluck('reel_id')->toArray();
            $userUnLikesId = $userReact->where('unlikes', Status::YES)->pluck('reel_id')->toArray();
            $userListId    = $userReact->where('list', Status::YES)->pluck('reel_id')->toArray();
        }
        $lastId    = @$reels->last()->id;
        $videoPath = getFilePath('reels');
        if (request()->lastId) {
            if ($reels->count()) {
                $notify[] = 'Reels';
                return responseSuccess('reels_data', $notify, [
                    'reels'         => $reels,
                    'lastId'        => $lastId,
                    'userLikesId'   => $userLikesId,
                    'userUnLikesId' => $userUnLikesId,
                    'userListId'    => $userListId,
                    'videoPath'     => $videoPath,
                ]);
            }
            return response()->json([
                'error' => 'Item not more yet',
            ]);
            $notify[] = 'Item not found yet!';
            return responseError('not_found', $notify);
        }

        $notify[] = 'Reels';
        return responseSuccess('reels_data', $notify, [
            'reels'         => $reels,
            'lastId'        => $lastId,
            'userLikesId'   => $userLikesId,
            'userUnLikesId' => $userUnLikesId,
            'userListId'    => $userListId,
            'videoPath'     => $videoPath,
        ]);
    }

    public function liveTournaments()
    {
        $tournaments = Tournament::active()->apiQuery();
        $notify[]    = 'Live Tournaments';
        $imagePath   = getFilePath('tournament');

        return responseSuccess('live_tournaments', $notify, [
            'tournaments' => $tournaments,
            'imagePath'   => $imagePath,
        ]);
    }

    public function tournamentGames($id)
    {
        $tournament = Tournament::active()->with(['games' => function ($query) {
            $query->active()->orderBy('start_time', 'asc');
        }])->where('id', $id)->first();
        if (!$tournament) {
            $notify[] = 'Tournament not found';
            return responseError('not_found', $notify);
        }
        $games = $tournament->games->groupBy(function ($game) {
            return Carbon::parse($game->start_time)->format('Y-m-d');
        });
        $notify[]  = $tournament->name;
        $imagePath = getFilePath('tournament');
        return responseSuccess('tournament_detail', $notify);
    }

    public function tournamentDetail($id)
    {
        $tournament = Tournament::active()->with(['games' => function ($query) {
            $query->active()->orderBy('start_time', 'asc');
        }])->where('id', $id)->first();

        if (!$tournament) {
            $notify[] = 'Tournament not found';
            return responseError('not_found', $notify);
        }

        $games = $tournament->games->groupBy(function ($game) {
            return Carbon::parse($game->start_time)->format('Y-m-d');
        });

        $watchEligible = true;
        if ($tournament->version == Status::PAID_VERSION) {
            $watchEligible = false;
        }
        $notify[]  = $tournament->name;
        $imagePath = getFilePath('tournament');
        return responseSuccess('tournament_detail', $notify, [
            'tournament'    => $tournament,
            'imagePath'     => $imagePath,
            'games'         => $games,
            'watchEligible' => $watchEligible,
        ]);
    }

    protected function checkWatchEligableTournament($tournament)
    {
        $watchEligible = true;
        if ($tournament->version == Status::PAID_VERSION) {
            $watchEligible = false;
        }
        return $watchEligible;
    }

    public function gameDetail($id)
    {
        $game = Game::active()->with('tournament', 'teamOne', 'teamTwo')->where('id', $id)->first();
        if (!$game) {
            $notify[] = 'Game not found';
            return responseError('not_found', $notify);
        }
        $relatedGames  = Game::active()->where('id', '!=', $game->id)->where('tournament_id', $game->tournament_id)->orderBy('start_time', 'asc')->get();
        $notify[]      = $game->slug;
        $watchEligible = $this->checkWatchEligableGame($game);
        $imagePath     = getFilePath('game');
        return responseSuccess('tournament_detail', $notify, [
            'game'          => $game,
            'imagePath'     => $imagePath,
            'relatedGames'  => $relatedGames,
            'watchEligible' => $watchEligible,
        ]);
    }

    protected function checkWatchEligableGame($game)
    {
        $watchEligible = true;
        if ($game->tournament->version == Status::FREE_VERSION) {
            return $watchEligible;
        }
        if ($game->version == Status::PAID_VERSION) {
            $watchEligible = false;
        }
        return $watchEligible;
    }

    public function watchGame($id)
    {
        $game = Game::active()->with('tournament', 'teamOne', 'teamTwo')->where('id', $id)->first();
        if (!$game) {
            $notify[] = 'Game not found';
            return responseError('not_found', $notify);
        }
        $watchEligible = $this->checkWatchEligableGame($game);
        if (!$watchEligible) {
            $notify[] = 'Please purchase a subscription for this game';
            return responseError('purchase_subscription', $notify);
        }
        $notify[]  = 'Game not found';
        $imagePath = getFilePath('game');
        return responseSuccess('watch_game', $notify, [
            'game'          => $game,
            'watchEligible' => $watchEligible,
            'imagePath'     => $imagePath,
        ]);
    }

    public function genre()
    {
        if (!gs('genre')) {
            $notify[] = 'Genre feature is not available';
            return responseError('genre_disable', $notify);
        }

        $genres  = json_decode(gs('genres'));

        if (!$genres) {
            $notify[] = 'No genres found';
            return responseError('not_found', $notify);
        }

        $genreItems = [];
        foreach ($genres as $genre) {
            $items = Item::active()

                ->hasVideo()
                ->whereRaw("JSON_EXTRACT(team, '$.genres') LIKE ?", ["%$genre%"])
                ->orderBy('view', 'desc')
                ->limit(12)
                ->get(['id', 'item_type', 'item_type', 'image']);
            $genreItems[$genre] = $items;
        }

        $imagePath    = getFilePath('item_landscape');
        $portraitPath = getFilePath('item_portrait');

        $notify[]  = 'Genres';
        return responseSuccess('genre', $notify, [
            'genres' => $genreItems,
            'landscape_path' => $imagePath,
            'portrait_path'  => $portraitPath,
        ]);
    }

    public function storeLiveComment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'live_id' => 'required|integer|exists:live_televisions,id',
                'comment' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return responseError('validation_error', $validator->errors());
            }

            if (!auth()->check()) {
                $notify = 'User not found';
                return responseError('Unauthorized', $notify);
            }

            $comment = new LiveComment();
            $comment->live_television_id = $request->live_id;
            $comment->comment = $request->comment;
            $comment->user_id = auth()->id();
            $comment->save();

            $user = $comment->user;
            $data = [
                'id' => $comment->id,
                'liveTvId' => $comment->live_television_id,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'imageUrl' => $this->generateUserImageUrl($user->image),
                ],
                'comment' => $comment->comment,
                'created_at' => $comment->created_at->toISOString(),
            ];

            $notify = 'Comment posted successfully';

            return responseSuccess('comment_posted', $notify, [
                'data'  => $data,
            ]);

        } catch (\Exception $e) {
            $notify = 'Failed to post comment';
            return responseError('Server error', $notify);
        }
    }


    public function getLiveComments($liveTvId)
    {
        try {
            $comments = LiveComment::where('live_television_id', $liveTvId)
                ->with(['user:id,username,firstname,lastname,image'])
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get()
                ->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'liveTvId' => $comment->live_television_id,
                        'user' => [
                            'id' => $comment->user->id,
                            'username' => $comment->user->username,
                            'firstname' => $comment->user->firstname,
                            'lastname' => $comment->user->lastname,
                            'imageUrl' => $this->generateUserImageUrl($comment->user->image),
                        ],
                        'comment' => $comment->comment,
                        'created_at' => $comment->created_at->toISOString(),
                    ];
                });

            $notify = 'Comments retrieve successfully';

            return responseSuccess('comments_retrieve', $notify, [
                'comments'  => $comments,
            ]);

        } catch (\Exception $e) {
            $notify = 'Failed to retrieve comments';
            return responseError('Server error', $notify);
        }
    }


    public function storeLiveTournamentComment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'live_id' => 'required|integer|exists:games,id',
                'comment' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return responseError('validation_error', $validator->errors());
            }

            if (!auth()->check()) {
                $notify = 'User not found';
                return responseError('Unauthorized', $notify);
            }

            $comment = new LiveComment();
            $comment->game_id = $request->live_id;
            $comment->comment = $request->comment;
            $comment->user_id = auth()->id();
            $comment->save();

            $user = $comment->user;
            $data = [
                'id' => $comment->id,
                'liveGameId' => $comment->game_id,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'imageUrl' => $this->generateUserImageUrl($user->image),
                ],
                'comment' => $comment->comment,
                'created_at' => $comment->created_at->toISOString(),
            ];

            $notify = 'Comment posted successfully';

            return responseSuccess('comment_posted', $notify, [
                'data'  => $data,
            ]);
        } catch (\Exception $e) {
            $notify = 'Failed to post comment';
            return responseError('Server error', $notify);
        }
    }


    public function getLiveTournamentComments($liveTvId)
    {
        try {
            $comments = LiveComment::where('game_id', $liveTvId)
                ->with(['user:id,username,firstname,lastname,image'])
                ->orderBy('created_at', 'desc')
                ->take(50)
                ->get()
                ->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'liveTvId' => $comment->game_id,
                        'user' => [
                            'id' => $comment->user->id,
                            'username' => $comment->user->username,
                            'firstname' => $comment->user->firstname,
                            'lastname' => $comment->user->lastname,
                            'imageUrl' => $this->generateUserImageUrl($comment->user->image),
                        ],
                        'comment' => $comment->comment,
                        'created_at' => $comment->created_at->toISOString(),
                    ];
                });

            $notify = 'Comments retrieve successfully';

            return responseSuccess('comments_retrieve', $notify, [
                'comments'  => $comments,
            ]);

        } catch (\Exception $e) {
            $notify = 'Failed to retrieve comments';
            return responseError('Server error', $notify);
        }
    }


    private function generateUserImageUrl($image)
    {
        return getImage(getFilePath('userProfile') . '/' . $image, getFileSize('userProfile'), true);
    }
}
