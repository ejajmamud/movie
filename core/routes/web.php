<?php

use Illuminate\Support\Facades\Route;

Route::post('pusher/auth/{socketId}/{channelName}', 'SiteController@pusher')->name('pusher');

Route::get('/clear', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
});

// User Support Ticket
Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group(function () {
    Route::get('/', 'supportTicket')->name('index');
    Route::get('new', 'openSupportTicket')->name('open');
    Route::post('create', 'storeSupportTicket')->name('store');
    Route::get('view/{ticket}', 'viewTicket')->name('view');
    Route::post('reply/{id}', 'replyTicket')->name('reply');
    Route::post('close/{id}', 'closeTicket')->name('close');
    Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
});

Route::get('app/deposit/confirm/{hash}', 'Gateway\PaymentController@appDepositConfirm')->name('deposit.app.confirm');

Route::controller('SiteController')->name('wishlist.')->prefix('wishlist')->group(function () {
    Route::post('add', 'addWishlist')->name('add');
    Route::post('remove', 'removeWishlist')->name('remove');
});

Route::controller('SiteController')->group(function () {
    Route::get('short/videos/{id?}/{route?}', 'shortVideos')->name('short.videos');
    Route::get('contact', 'contact')->name('contact');
    Route::post('contact', 'contactSubmit');
    Route::get('change/{lang?}', 'changeLanguage')->name('lang');

    Route::get('genres', 'genre')->name('genre');
    Route::get('live-tv', 'liveTelevision')->name('live.tv');
    Route::get('live-tv/{id?}', 'watchTelevision')->name('watch.tv');

    //tv-live-chat
    Route::post('live-tv/comments', 'storeLiveComment')->name('live-tv.comments.store');
    Route::get('live-tv/{liveTvId}/comments', 'getLiveComments')->name('live-tv.comments.get');

    Route::get('live/tournaments', 'liveTournaments')->name('live.tournaments');
    Route::get('tournament/{id}/{slug}', 'tournamentDetail')->name('tournament.detail');
    Route::get('tournament/games/{id}/{slug}', 'tournamentGames')->name('tournament.games');
    Route::get('game/{id}/{slug}', 'gameDetail')->name('game.detail');
    Route::get('watch/game/{id}/{slug}', 'watchGame')->name('watch.game');

    //game-live-chat
    Route::post('live-game/comments', 'storeLiveTournamentComment')->name('live-game.comments.store');
    Route::get('live-game/{liveTvId}/comments', 'getLiveTournamentComments')->name('live-game.comments.get');

    Route::get('get/section', 'getSection')->name('get.section');
    Route::get('watch-video/{slug}/{episode_id?}', 'watchVideo')->name('watch');
    Route::post('video-ad/track-view', 'trackAdView')->name('video.ad.track');

    // game
    Route::get('category/{id}', 'category')->name('category');
    Route::get('sub-category/{id}', 'subCategory')->name('subCategory');
    Route::get('search', 'search')->name('search');
    Route::get('load-more', 'loadMore')->name('loadmore.load_data');

    Route::post('add-click', 'addClick')->name('add.click');
    Route::post('subscribe', 'subscribe')->name('subscribe');

    Route::get('subscribe', 'subscription')->name('subscription');

    Route::get('cookie-policy', 'cookiePolicy')->name('cookie.policy');
    Route::get('cookie/accept', 'cookieAccept')->name('cookie.accept');

    Route::get('links/{slug}', 'links')->name('links');
    Route::get('policy/{slug}', 'policyPages')->name('policy.pages');

    Route::get('placeholder-image/{size}', 'placeholderImage')->withoutMiddleware('maintenance')->name('placeholder.image');
    Route::get('maintenance-mode', 'maintenance')->withoutMiddleware('maintenance')->name('maintenance');

    Route::post('device/token', 'storeDeviceToken')->name('store.device.token');
    Route::get('/', 'index')->name('home');
});
