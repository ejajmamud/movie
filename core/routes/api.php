<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::namespace('Api')->name('api.')->group(function () {

    Route::controller('AppController')->group(function () {
        Route::get('general-setting', 'generalSetting');
        Route::get('get-countries', 'getCountries');
        Route::get('language/{key?}','getLanguage');
        Route::get('policies', 'policies');
        Route::get('policy/{slug}', 'policyContent');
        Route::get('faq', 'faq');
        Route::get('seo', 'seo');
        Route::get('get-extension/{act}','getExtension');
        Route::post('contact', 'submitContact');
        Route::get('cookie', 'cookie');
        Route::post('cookie/accept', 'cookieAccept');
        Route::get('custom-pages', 'customPages');
        Route::get('custom-page/{slug}', 'customPageData');
        Route::get('sections/{key?}', 'allSections');
        Route::get('ticket/{ticket}', 'viewTicket');
        Route::post('ticket/ticket-reply/{id}', 'replyTicket');
    });

    Route::namespace('Auth')->group(function () {
        Route::controller('LoginController')->group(function () {
            Route::post('login', 'login');
            Route::post('check-token', 'checkToken');
            Route::post('social-login', 'socialLogin');
        });
        Route::post('register', 'RegisterController@register');

        Route::controller('ForgotPasswordController')->group(function () {
            Route::post('password/email', 'sendResetCodeEmail');
            Route::post('password/verify-code', 'verifyCode');
            Route::post('password/reset', 'reset');
        });
    });

    Route::get('dashboard', 'UserController@dashboard');

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('user-data-submit', 'UserController@userDataSubmit');

        //authorization
        Route::middleware('registration.complete')->controller('AuthorizationController')->group(function () {
            Route::get('authorization', 'authorization');
            Route::get('resend-verify/{type}', 'sendVerifyCode');
            Route::post('verify-email', 'emailVerification');
            Route::post('verify-mobile', 'mobileVerification');
        });

        Route::get('user-info', 'UserController@userInfo');

        Route::middleware(['check.status'])->group(function () {


            Route::middleware('registration.complete')->group(function () {

                Route::controller('RequestItemController')->prefix('request-items')->group(function () {
                    Route::get('/', 'index')->name('api.request.items.index');
                    Route::get('/my-requests', 'myRequests')->name('api.request.items.my');
                    Route::get('/live-search', 'liveSearch')->name('api.request.items.live.search');
                    Route::get('/recent-items', 'recentItems')->name('api.request.items.recent');
                    Route::get('/search', 'search')->name('api.request.items.search');
                    Route::post('/', 'store')->name('api.request.items.store');
                    Route::post('/vote', 'vote')->name('api.request.items.vote');
                    Route::post('/subscribe', 'subscribe')->name('api.request.items.subscribe');
                });

                Route::controller('UserController')->group(function () {
                    Route::get('download-attachments/{file_hash}', 'downloadAttachment')->name('download.attachment');
                    Route::get('user/subscription', 'subscription');
                    Route::get('subscribe', 'subscribe');
                    Route::get('plans/{type?}', 'plans');

                    Route::post('subscribe-plan', 'subscribePlan');
                    Route::post('purchase-plan', 'purchasePlan');
                    Route::post('purchase-app', 'purchaseFromApp');

                    Route::post('add-wishlist', 'addWishlist');
                    Route::post('remove-wishlist', 'removeWishlist');
                    Route::get('check-wishlist', 'checkWishlist');
                    Route::get('wishlists', 'wishlists');

                    Route::get('history', 'history');
                    Route::get('watch', 'watchVideo');
                    Route::get('play', 'playVideo');
                    Route::post('status', 'status');

                    Route::post('profile-setting', 'submitProfile');
                    Route::post('change-password', 'submitPassword');

                    //KYC
                    Route::get('kyc-form', 'kycForm');
                    Route::get('kyc-data','kycData');
                    Route::post('kyc-submit', 'kycSubmit');

                    //Report
                    Route::any('deposit/history', 'depositHistory');
                    Route::get('rented/items', 'rentedItem');

                    Route::get('transactions', 'transactions');

                    Route::post('add-device-token', 'addDeviceToken');
                    Route::get('push-notifications', 'pushNotifications');
                    Route::post('push-notifications/read/{id}', 'pushNotificationsRead');

                    //2FA
                    Route::get('twofactor', 'show2faForm');
                    Route::post('twofactor/enable', 'create2fa');
                    Route::post('twofactor/disable', 'disable2fa');

                    Route::post('delete-account', 'deleteAccount');

                    Route::get('user/live-television/{scope?}', 'liveTelevision');
                    Route::get('live-tv/{id?}', 'watchTelevision');
                    Route::post('subscribe/channel/{id}', 'subscribeChannel');
                    Route::get('user/short/videos/{id?}/{route?}', 'shortVideos');

                    Route::get('user/tournament/{id}', 'tournamentDetail');
                    Route::get('user/game/{id}', 'gameDetail');
                    Route::get('user/watch/game/{id}', 'watchGame')->name('watch.game');
                });

                Route::controller('PusherController')->group(function () {
                    Route::post('authenticationApp', 'authenticationApp');
                });

                Route::controller('WatchPartyController')->prefix('party')->group(function () {
                    Route::post('create', 'create');
                    Route::get('room/{code}/{guestId?}', 'room');
                    Route::post('join/request', 'joinRequest');
                    Route::post('request/accept/{id?}', 'requestAccept');
                    Route::post('request/reject/{id?}', 'requestReject');
                    Route::post('send/message', 'sendMessage');
                    Route::post('player/setting', 'playerSetting');
                    Route::post('status/{id}', 'status');
                    Route::post('cancel/{id}', 'cancel');
                    Route::post('leave/{id}/{user_id}', 'leave');
                    Route::post('disabled/{id}', 'disabled');
                    Route::get('history', 'history');
                    Route::post('reload', 'reload');
                });

                // Payment
                Route::controller('PaymentController')->group(function () {
                    Route::get('deposit/methods', 'methods');
                    Route::post('deposit/insert', 'depositInsert');
                    Route::post('app/payment/confirm', 'appPaymentConfirm');
                    Route::post('manual/confirm', 'manualDepositConfirm');
                });

                Route::controller('TicketController')->prefix('ticket')->group(function () {
                    Route::get('/', 'supportTicket');
                    Route::post('create', 'storeSupportTicket');
                    Route::get('view/{ticket}', 'viewTicket');
                    Route::post('reply/{id}', 'replyTicket');
                    Route::post('close/{id}', 'closeTicket');
                    Route::get('download/{attachment_id}', 'ticketDownload');
                });

                Route::controller('ReelController')->group(function () {
                    Route::post('like', 'like')->name('like');
                    Route::post('reels/list', 'list')->name('list');
                });

                Route::controller('FrontendController')->prefix('live-game')->name('live-game.')->group(function () {
                    Route::post('comments', 'storeLiveTournamentComment')->name('comments.store');
                    Route::get('comments/{liveTvId}', 'getLiveTournamentComments')->name('comments.get');
                });

                Route::controller('FrontendController')->prefix('live-tv')->name('live-tv.')->group(function () {
                    Route::post('comments', 'storeLiveComment')->name('comments.store');
                    Route::get('comments/{liveTvId}', 'getLiveComments')->name('comments.get');
                });

            });
        });

        Route::get('logout', 'Auth\LoginController@logout');
    });

    Route::controller('FrontendController')->group(function () {
        Route::get('logo', 'logo');
        Route::get('welcome-info', 'welcomeInfo');
        Route::get('sliders', 'sliders');
        Route::get('live-television/{scope?}', 'liveTelevision');

        Route::get('section/featured', 'featured');
        Route::get('section/recent', 'recentlyAdded');
        Route::get('section/latest', 'latestSeries');
        Route::get('section/single', 'single');
        Route::get('section/trailer', 'trailer');
        Route::get('section/free-zone', 'freeZone');
        Route::get('section/rent', 'rent');

        Route::get('movies', 'movies');
        Route::get('episodes', 'episodes');

        Route::get('categories', 'categories');
        Route::get('subcategories', 'subcategories');
        Route::get('sub-category/{id}', 'subCategory');

        Route::get('search', 'search');

        Route::get('watch-video', 'watchVideo');
        Route::get('play-video', 'playVideo');
        Route::get('policy-pages', 'policyPages');
        Route::get('language/{code?}', 'language');
        Route::get('pop-up/ads', 'popUpAds');

        Route::get('short/videos/{id?}/{route?}', 'shortVideos');
        Route::get('live/tournaments', 'liveTournaments');
        Route::get('tournament/{id}', 'tournamentDetail');
        Route::get('tournament/games/{id}', 'tournamentGames');
        Route::get('game/{id}', 'gameDetail');
        Route::get('watch/game/{id}', 'watchGame');
        Route::get('genres', 'genre');

    });
});
