<?php

namespace App\Http\Controllers\Api;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\FormProcessor;
use App\Lib\GoogleAuthenticator;
use App\Models\AdminNotification;
use App\Models\Advertise;
use App\Models\ChannelCategory;
use App\Models\Deposit;
use App\Models\DeviceToken;
use App\Models\Episode;
use App\Models\Form;
use App\Models\Game;
use App\Models\History;
use App\Models\Item;
use App\Models\LiveTelevision;
use App\Models\NotificationLog;
use App\Models\Plan;
use App\Models\Reel;
use App\Models\ReelHistory;
use App\Models\Slider;
use App\Models\Subscription;
use App\Models\Tournament;
use App\Models\Transaction;
use App\Models\User;
use App\Models\VideoReport;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller {
    public function dashboard() {
        $data['sliders']        = Slider::with('item', 'item.category', 'item.sub_category')->latest()->limit(4)->get();
        $data['televisions']    = LiveTelevision::active()->limit(6)->apiQuery();
        $items                  = Item::active()->hasVideo();
        $data['featured']       = (clone $items)->where('featured', Status::ENABLE)->latest()->limit(10)->get();
        $data['recently_added'] = (clone $items)->orderBy('id', 'desc')->where('item_type', Status::SINGLE_ITEM)->latest()->limit(10)->get();
        $data['latest_series']  = (clone $items)->orderBy('id', 'desc')->where('item_type', Status::EPISODE_ITEM)->latest()->limit(10)->get();
        $data['single']         = (clone $items)->orderBy('id', 'desc')->where('single', 1)->with('category')->latest()->limit(10)->get();
        $data['trailer']        = (clone $items)->where('item_type', Status::SINGLE_ITEM)->where('is_trailer', Status::TRAILER)->latest()->limit(10)->get();
        $data['rent']           = (clone $items)->where('item_type', Status::SINGLE_ITEM)->where('version', Status::RENT_VERSION)->latest()->limit(10)->get();
        $data['free_zone']      = (clone $items)->free()->latest()->limit(10)->get();
        $data['advertise']      = Advertise::where('device', 2)->where('ads_show', 1)->where('ads_type', 'banner')->inRandomOrder()->first();
        $data['tournaments']    = Tournament::active()->get();

        $imagePath['tournament'] = getFilePath('tournament');
        $imagePath['portrait']   = getFilePath('item_portrait');
        $imagePath['landscape']  = getFilePath('item_landscape');
        $imagePath['television'] = getFilePath('television');
        $imagePath['ads']        = getFilePath('ads');

        $notify[] = 'Dashboard Data';
        return responseSuccess('dashboard', $notify, [
            'data' => $data,
            'path' => $imagePath,
        ]);
    }
    public function subscription() {
        $user                   = auth()->user();
        $subscribedTournamentId = $user->subscribedTournamentId();
        $subscribedMatchId      = $user->subscribedMatchId();
        $subscribedChannelId    = $user->subscribedChannelId();
        return response()->json([
            'remark'  => 'subscription_data',
            'status'  => 'success',
            'message' => ['success' => 'Subscription Data'],
            'data'    => [
                'subscribedChannelId'    => $subscribedChannelId,
                'subscribedTournamentId' => $subscribedTournamentId,
                'subscribedMatchId'      => $subscribedMatchId,
            ],
        ]);
    }

    public function userDataSubmit(Request $request) {
        $user = auth()->user();
        if ($user->profile_complete == Status::YES) {
            $notify[] = 'You\'ve already completed your profile';
            return responseError('already_completed', $notify);
        }

        $countryData  = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));

        $validator = Validator::make($request->all(), [
            'country_code' => 'required|in:' . $countryCodes,
            'country'      => 'required|in:' . $countries,
            'mobile_code'  => 'required|in:' . $mobileCodes,
            'username'     => 'required|unique:users|min:6',
            'mobile'       => ['required', 'regex:/^([0-9]*)$/', Rule::unique('users')->where('dial_code', $request->mobile_code)],
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        if (preg_match("/[^a-z0-9_]/", trim($request->username))) {
            $notify[] = 'No special character, space or capital letters in username';
            return responseError('validation_error', $notify);
        }

        $user->country_code = $request->country_code;
        $user->mobile       = $request->mobile;
        $user->username     = $request->username;

        $user->address      = $request->address;
        $user->city         = $request->city;
        $user->state        = $request->state;
        $user->zip          = $request->zip;
        $user->country_name = $request->country;
        $user->dial_code    = $request->mobile_code;

        $user->profile_complete = Status::YES;
        $user->save();

        $notify[] = 'Profile completed successfully';
        return responseSuccess('profile_completed', $notify, ['user' => $user]);
    }

    public function kycForm() {
        if (auth()->user()->kv == Status::KYC_PENDING) {
            $notify[] = 'Your KYC is under review';
            return responseError('under_review', $notify);
        }
        if (auth()->user()->kv == Status::KYC_VERIFIED) {
            $notify[] = 'You are already KYC verified';
            return responseError('already_verified', $notify);
        }
        $form     = Form::where('act', 'kyc')->first();
        $notify[] = 'KYC field is below';
        return responseSuccess('kyc_form', $notify, ['form' => $form->form_data]);
    }

    public function kycSubmit(Request $request) {
        $form = Form::where('act', 'kyc')->first();
        if (!$form) {
            $notify[] = 'Invalid KYC request';
            return responseError('invalid_request', $notify);
        }
        $formData       = $form->form_data;
        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);

        $validator = Validator::make($request->all(), $validationRule);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }
        $user = auth()->user();
        foreach (isset($user->kyc_data) ? $user->kyc_data : [] as $kycData) {
            if ($kycData->type == 'file') {
                fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
            }
        }
        $userData = $formProcessor->processFormData($request, $formData);

        $user->kyc_data             = $userData;
        $user->kyc_rejection_reason = null;
        $user->kv                   = Status::KYC_PENDING;
        $user->save();

        $notify[] = 'KYC data submitted successfully';
        return responseSuccess('kyc_submitted', $notify, ['kyc_data' => $user->kyc_data]);
    }
    public function kycData() {
        $user      = auth()->user();
        $kycData   = $user->kyc_data ?? [];
        $kycValues = [];
        foreach ($kycData as $kycInfo) {
            if (!$kycInfo->value) {
                continue;
            }
            if ($kycInfo->type == 'checkbox') {
                $value = implode(', ', $kycInfo->value);
            } else if ($kycInfo->type == 'file') {
                $value = encrypt(getFilePath('verify') . '/' . $kycInfo->value);
            } else {
                $value = $kycInfo->value;
            }

            $kycValues[] = [
                'name'  => $kycInfo->name,
                'type'  => $kycInfo->type,
                'value' => $value,
            ];
        }
        $notify[] = 'KYC data';
        return responseSuccess('kyc_data', $notify, ['kyc_data' => $kycValues]);
    }

    public function depositHistory(Request $request) {
        $deposits = auth()->user()->deposits()->selectRaw("*, DATE_FORMAT(created_at, '%Y-%m-%d %h:%m') as date");
        if ($request->search) {
            $deposits = $deposits->where('trx', $request->search);
        }
        $deposits = $deposits->with(['gateway', 'subscription' => function ($query) {
            $query->with('plan', 'item', 'tournament', 'game', 'channelCategory');
        }])->orderBy('id', 'desc')->paginate(getPaginate());

        $notify[] = 'Deposit data';
        return responseSuccess('deposits', $notify, ['deposits' => $deposits]);
    }

    public function transactions(Request $request) {
        $remarks      = Transaction::distinct('remark')->get('remark');
        $transactions = Transaction::where('user_id', auth()->id());

        if ($request->search) {
            $transactions = $transactions->where('trx', $request->search);
        }

        if ($request->type) {
            $type         = $request->type == 'plus' ? '+' : '-';
            $transactions = $transactions->where('trx_type', $type);
        }

        if ($request->remark) {
            $transactions = $transactions->where('remark', $request->remark);
        }

        $transactions = $transactions->orderBy('id', 'desc')->paginate(getPaginate());
        $notify[]     = 'Transactions data';
        return responseSuccess('transactions', $notify, [
            'transactions' => $transactions,
            'remarks'      => $remarks,
        ]);
    }

    public function submitProfile(Request $request) {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required',
            'lastname'  => 'required',
        ], [
            'firstname.required' => 'The first name field is required',
            'lastname.required'  => 'The last name field is required',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $user = auth()->user();

        $user->firstname = $request->firstname;
        $user->lastname  = $request->lastname;

        if ($request->hasFile('image')) {
            try {
                $user->image = fileUploader($request->image, getFilePath('userProfile'), getFileSize('userProfile'), @$user->image);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $user->address = $request->address;
        $user->city    = $request->city;
        $user->state   = $request->state;
        $user->zip     = $request->zip;

        $user->save();

        $notify[] = 'Profile updated successfully';
        return responseSuccess('profile_updated', $notify);
    }

    public function submitPassword(Request $request) {
        $passwordValidation = Password::min(6);
        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password'         => ['required', 'confirmed', $passwordValidation],
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $user = auth()->user();
        if (Hash::check($request->current_password, $user->password)) {
            $password       = Hash::make($request->password);
            $user->password = $password;
            $user->save();
            $notify[] = 'Password changed successfully';
            return responseSuccess('password_changed', $notify);
        } else {
            $notify[] = 'The password doesn\'t match!';
            return responseError('validation_error', $notify);
        }
    }

    public function plans($type = null) {
        $plans = Plan::active();
        if ($type == 'app') {
            $plans->whereNotNull('app_code');
        }
        $plans   = $plans->get();
        $appCode = $plans->pluck('app_code')->toArray();

        $notify[]  = 'Plan';
        $imagePath = getFilePath('plan');

        return responseSuccess('plan', $notify, [
            'plans'      => $plans,
            'image_path' => $imagePath,
            'appCode'    => $appCode,
        ]);
    }

    public function subscribe() {
        $user = auth()->user();
        if ($user->exp > now()) {
            $notify[] = 'You are already subscribed!';
            return responseError('subscribed', $notify);
        }

        $notify[] = 'You have to subscribe plan.';
        return responseSuccess('subscribed', $notify);
    }

    public function subscribePlan(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'   => 'required|integer',
            'type' => 'required|string|in:plan,item,channel_category,tournament,game',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $className = [
            'tournament'       => Tournament::class,
            'game'             => Game::class,
            'plan'             => Plan::class,
            'item'             => Item::class,
            'channel_category' => ChannelCategory::class,
        ];

        $modelClass = $className[$request->type];

        $channelCategory = $request->type;
        $formatCategory  = str_replace('_', ' ', $channelCategory);

        $data = $modelClass::active()->where('id', $request->id)->first();
        if (!$data) {
            $notify[] = ucfirst($formatCategory) . ' not found ';
            return responseError('not_found', $notify);
        }

        $hasSubscribed     = false;
        $user              = auth()->user();
        $existSubscription = Subscription::where('user_id', $user->id)->where($request->type . '_id', $request->id)->with('deposit')->orderBy('id', 'desc');

        if (in_array($request->type, ['game', 'tournament'])) {
            $existSubscription = $existSubscription->first();
            if ($existSubscription) {
                $hasSubscribed = $existSubscription->status == Status::PAYMENT_SUCCESS;
                if (!$hasSubscribed && $existSubscription->deposit?->status == Status::PAYMENT_PENDING) {
                    $notify[] = 'Already one payment in pending. Please Wait';
                    return responseError('payment_pending', $notify);
                }
            }
        }

        $duration = null;
        if ($request->type == 'item') {
            $hasSubscribed = $existSubscription->active()->whereDate('expired_date', '>', now())->exists();
            $duration      = $data->rental_period;
        }

        if ($request->type == 'plan') {
            $pendingPayment = $user->deposits()->where('status', Status::PAYMENT_PENDING)->count();
            if ($pendingPayment > 0) {
                $notify[] = 'Already one payment in pending. Please Wait';
                return responseError('payment_pending', $notify);
            }
            $hasSubscribed = $user->exp > now();
            $duration      = $data->duration;
        }

        if ($request->type == 'channel_category') {
            $hasSubscribed = $existSubscription->active()->whereDate('expired_date', '>', now())->exists();
            $duration      = 30;
        }

        if ($hasSubscribed) {
            $notify[] = 'Already subscribed to this ' . $formatCategory;
            return responseError('already_subscribed', $notify);
        }

        $subscription = Subscription::active()->where('user_id', $user->id)->where($request->type . '_id', $request->id)->orderBy('id', 'desc')->first();
        if (!$subscription) {
            $column                = $request->type . '_id';
            $subscription          = new Subscription();
            $subscription->user_id = $user->id;
            $subscription->$column = $request->id;
        }
        $subscription->expired_date = $duration ? now()->addDays((int)$duration) : null;
        $subscription->status       = Status::DISABLE;
        $subscription->save();

        $notify[] = $formatCategory . ' Purchase';
        return responseSuccess('subscribe_payment', $notify, [
            'type'            => $request->type,
            'subscription_id' => $subscription->id,
            'redirect_url'    => route('user.deposit.index'),
        ]);
    }

    public function purchasePlan(Request $request) {
        $validator = Validator::make($request->all(), [
            'username'    => 'required',
            'token'       => 'required',
            'plan_id'     => 'required|integer',
            'amount'      => 'required',
            'method_code' => 'required|in:-1,-2',
            'type'        => 'required|string|in:plan,item',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $notify);
        }

        $plan = Plan::active()->where('id', $request->plan_id)->first();
        if (!$plan) {
            $notify[] = ['error', 'Plan not found'];
            return responseError('invalid_plan', $notify);
        }

        $subscription               = new Subscription();
        $subscription->user_id      = auth()->id();
        $subscription->plan_id      = $plan->id;
        $subscription->expired_date = now()->addDays((int)$plan->duration);
        $subscription->save();

        $general = gs();

        $detail = [
            'username' => $request->username,
            'plan'     => $plan->name,
            'amount'   => $request->amount,
        ];

        $data                  = new Deposit();
        $data->user_id         = auth()->id();
        $data->subscription_id = $subscription->id;
        $data->method_code     = $request->method_code;
        $data->method_currency = strtoupper($general->cur_text);
        $data->amount          = $request->amount;
        $data->charge          = 0;
        $data->rate            = gs()->cur_text;
        $data->final_amount    = $request->amount;
        $data->btc_amount      = 0;
        $data->btc_wallet      = "";
        $data->detail          = $detail;
        $data->trx             = getTrx();
        $data->status          = Status::PAYMENT_PENDING;
        $data->save();

        $user          = User::find($data->user_id);
        $user->plan_id = $subscription->plan_id;
        $user->exp     = $subscription->expired_date;
        $user->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $user->id;
        $adminNotification->title     = $user->username . ' subscribed to ' . $plan->name;
        $adminNotification->click_url = urlPath('admin.deposit.pending');
        $adminNotification->save();

        notify($user, 'SUBSCRIBE_PLAN', [
            'plan'     => $plan->name,
            'price'    => showAmount($plan->pricing),
            'duration' => $plan->duration,
        ]);

        $notify[] = 'You have deposit request has been taken';
        return responseSuccess('payment_pending', $notify);
    }

    public function purchaseFromApp(Request $request) {
        $validator = Validator::make($request->all(), [
            'plan_id'       => 'required|integer',
            'user_id'       => 'required|integer',
            'method_code'   => 'required|in:-1,-2',
            'amount'        => 'required|numeric|gt:0',
            'currency'      => 'required|string',
            'purchaseToken' => 'required',
            'packageName'   => 'required',
            'productId'     => 'required',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $user = User::active()->where('id', $request->user_id)->first();
        if (!$user) {
            $notify[] = 'User not found';
            return responseError('invalid_user', $notify);
        }

        $plan = Plan::active()->where('id', $request->plan_id)->first();
        if (!$plan) {
            $notify[] = 'Plan not found';
            return responseError('invalid_plan', $notify);
        }

        if ($request->method_code == -1) {
            $general         = gs();
            $jsonKeyFilePath = getFilePath('appPurchase') . '/' . $general->app_purchase_credentials->google->file;
            $client          = new \Google_Client();
            $client->setAuthConfig($jsonKeyFilePath);
            $client->setScopes([\Google_Service_AndroidPublisher::ANDROIDPUBLISHER]);
            $service = new \Google_Service_AndroidPublisher($client);

            $packageName   = $request->packageName;
            $productId     = $request->productId;
            $purchaseToken = $request->token;

            $response = $service->purchases_products->get($packageName, $productId, $purchaseToken);

            if ($response->getPurchaseState() != 0) {
                $notify[] = 'Invalid purchase';
                return responseError('invalid_purchase', $notify);
            }
        }

        $subscription               = new Subscription();
        $subscription->user_id      = $user->id;
        $subscription->plan_id      = $plan->id;
        $subscription->item_id      = 0;
        $subscription->expired_date = now()->addDays((int)$plan->duration);
        $subscription->save();

        $data                  = new Deposit();
        $data->user_id         = $user->id;
        $data->subscription_id = $subscription->id;
        $data->method_code     = $request->method_code;
        $data->method_currency = strtoupper($request->currency);
        $data->amount          = $plan->pricing;
        $data->charge          = 0;
        $data->rate            = showAmount($request->amount / $plan->pricing);
        $data->final_amount    = $request->amount;
        $data->btc_amount      = 0;
        $data->btc_wallet      = "";
        $data->trx             = getTrx();
        $data->status          = Status::PAYMENT_SUCCESS;
        $data->save();

        $notify[] = 'Plan purchase successfully';
        return responseSuccess('plan_purchase', $notify);
    }

    public function wishlists() {
        $wishlists = Wishlist::with('item.category', 'episode')->where('user_id', auth()->id())->paginate(getPaginate());

        $notify[] = 'Wishlist';
        return responseSuccess('wishlist', $notify, [
            'wishlists' => $wishlists,
        ]);
    }

    public function addWishList(Request $request) {
        $validator = Validator::make($request->all(), [
            'item_id'    => 'required_without:episode_id',
            'episode_id' => 'required_without:item_id',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $wishlist = new Wishlist();

        if ($request->item_id) {
            $item = Item::find($request->item_id);
            if (!$item) {
                $notify[] = 'Video item not found';
                return responseError('not_found', $notify);
            }
            $exits             = Wishlist::where('item_id', $item->id)->where('user_id', auth()->id())->first();
            $wishlist->item_id = $item->id;
        } else if ($request->episode_id) {
            $episode = Episode::find($request->episode_id);
            if (!$episode) {
                $notify[] = 'Episode item not found';
                return responseError('not_found', $notify);
            }
            $exits                = Wishlist::where('episode_id', $episode->id)->where('user_id', auth()->id())->first();
            $wishlist->episode_id = $episode->id;
        }

        if (!$exits) {
            $wishlist->user_id = auth()->id();
            $wishlist->save();

            $notify[] = 'Video added to wishlist successfully';
            return responseSuccess('added_successfully', $notify);
        }

        $notify[] = 'Already in wishlist';
        return responseError('already_exits', $notify);
    }

    public function checkWishlist(Request $request) {

        $validator = Validator::make($request->all(), [
            'item_id'    => 'required_without:episode_id',
            'episode_id' => 'required_without:item_id',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }
        $wishlist = 0;
        if ($request->item_id) {
            $item = Item::find($request->item_id);
            if (!$item) {
                $notify[] = 'Video item not found';
                return responseError('not_found', $notify);
            }
            $wishlist = Wishlist::where('item_id', $item->id)->where('user_id', auth()->id())->count();
        } else if ($request->episode_id) {
            $episode = Episode::find($request->episode_id);
            if (!$episode) {
                $notify[] = 'Episode item not found';
                return responseError('not_found', $notify);
            }
            $wishlist = Wishlist::where('episode_id', $episode->id)->where('user_id', auth()->id())->count();
        }
        if ($wishlist) {
            $notify[] = 'Already in wishlist';
            return responseError('true', $notify);
        } else {
            $notify[] = 'Data not found';
            return responseError('false', $notify);
        }
    }

    public function removeWishlist(Request $request) {
        $validator = Validator::make($request->all(), [
            'item_id'    => 'required_without:episode_id',
            'episode_id' => 'required_without:item_id',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        if ($request->item_id) {
            $wishlist = Wishlist::where('item_id', $request->item_id)->where('user_id', auth()->id());
        }

        if ($request->episode_id) {
            $wishlist = Wishlist::where('episode_id', $request->episode_id)->where('user_id', auth()->id());
        }

        $wishlist = $wishlist->first();

        if ($wishlist) {
            $wishlist->delete();
            $notify[] = 'Video removed from wishlist successfully';
            return responseSuccess('remove_successfully', $notify);
        }

        $notify[] = 'Something wrong';

        return responseError('something_wrong', $notify);
    }

    public function history() {
        $histories = History::with('item', 'episode')->where('user_id', auth()->id())->paginate(getPaginate());

        $notify[] = 'History';
        return responseSuccess('history', $notify, [
            'histories' => $histories,
        ]);
    }

    public function watchVideo(Request $request) {
        $item = Item::hasVideo()->where('status', 1)->where('id', $request->item_id)->with('category', 'sub_category')->first();

        if (!$item) {
            $notify[] = 'Video Item Not Found';
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
            $notify[] = 'Unauthorized user';
            return responseError('unauthorized_' . $watchEligible[1], $notify, [
                'item'           => $item,
                'portrait_path'  => $imagePath,
                'landscape_path' => $landscapePath,
                'related_items'  => $relatedItems,
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

    protected function checkWatchEligableItem($item, $userHasSubscribed) {
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

    protected function checkWatchEligibleEpisode($episode, $userHasSubscribed) {
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

    public function playVideo(Request $request) {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $item = Item::hasVideo()->where('status', 1)->where('id', $request->item_id)->first();
        if (!$item) {
            $notify[] = 'Video item not found';
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
                $notify[] = 'Episode not found';
                return responseError('no_episode', $notify);
            }
            $watchEligible = $this->checkWatchEligibleEpisode($episode, $userHasSubscribed);

            if (!$watchEligible[0]) {
                $notify[] = 'Unauthorized user';
                return responseError('unauthorized_' . $watchEligible[1], $notify, [
                    'item' => $item,
                ]);
            }

            $video    = $episode->video;
            $remark   = 'episode_video';
            $notify[] = 'Episode Video';
        } else {

            $watchEligible = $this->checkWatchEligableItem($item, $userHasSubscribed);
            if (!$watchEligible[0]) {
                $notify[] = 'Unauthorized user';
                return responseError('unauthorized_' . $watchEligible[1], $notify, [
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

    private function videoList($video) {
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

    protected function storeHistory($itemId = null, $episodeId = null) {
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

    protected function storeVideoReport($itemId = null, $episodeId = null) {
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

    public function addDeviceToken(Request $request) {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $deviceToken = DeviceToken::where('token', $request->token)->first();

        if ($deviceToken) {
            $notify[] = 'Token already exists';
            return responseError('token_exists', $notify);
        }

        $deviceToken          = new DeviceToken();
        $deviceToken->user_id = auth()->user()->id;
        $deviceToken->token   = $request->token;
        $deviceToken->is_app  = Status::YES;
        $deviceToken->save();

        $notify[] = 'Token saved successfully';
        return responseError('token_saved', $notify);
    }

    public function show2faForm() {
        $ga        = new GoogleAuthenticator();
        $user      = auth()->user();
        $secret    = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . gs('site_name'), $secret);

        $notify[] = '2FA QR Code';
        return responseSuccess('2fa_qr', $notify, [
            'secret'      => $secret,
            'qr_code_url' => $qrCodeUrl,
        ]);
    }

    public function create2fa(Request $request) {
        $validator = Validator::make($request->all(), [
            'secret' => 'required',
            'code'   => 'required',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $user     = auth()->user();
        $response = verifyG2fa($user, $request->code, $request->secret);
        if ($response) {
            $user->tsc = $request->secret;
            $user->ts  = Status::ENABLE;
            $user->save();

            $notify[] = 'Google authenticator activated successfully';
            return responseSuccess('2fa_qr', $notify);
        } else {
            $notify[] = 'Wrong verification code';
            return responseError('wrong_verification', $notify);
        }
    }

    public function disable2fa(Request $request) {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            return responseError('validation_error', $validator->errors());
        }

        $user     = auth()->user();
        $response = verifyG2fa($user, $request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts  = Status::DISABLE;
            $user->save();
            $notify[] = 'Two factor authenticator deactivated successfully';
            return responseSuccess('2fa_qr', $notify);
        } else {
            $notify[] = 'Wrong verification code';
            return responseError('wrong_verification', $notify);
        }
    }

    public function pushNotifications() {
        $notifications = NotificationLog::where('user_id', auth()->id())->where('sender', 'firebase')->orderBy('id', 'desc')->paginate(getPaginate());
        $notify[]      = 'Push notifications';
        return responseSuccess('notifications', $notify, [
            'notifications' => $notifications,
        ]);
    }

    public function pushNotificationsRead($id) {
        $notification = NotificationLog::where('user_id', auth()->id())->where('sender', 'firebase')->find($id);
        if (!$notification) {
            $notify[] = 'Notification not found';
            return responseError('notification_not_found', $notify);
        }
        $notify[]                = 'Notification marked as read successfully';
        $notification->user_read = 1;
        $notification->save();

        return responseSuccess('notification_read', $notify);
    }

    public function userInfo() {
        $notify[] = 'User information';
        return responseSuccess('user_info', $notify, [
            'user' => auth()->user(),
        ]);
    }

    public function deleteAccount() {
        $user              = auth()->user();
        $user->username    = 'deleted_' . $user->username;
        $user->email       = 'deleted_' . $user->email;
        $user->provider_id = 'deleted_' . $user->provider_id;
        $user->save();

        $user->tokens()->delete();

        $notify[] = 'Account deleted successfully';
        return responseSuccess('account_deleted', $notify);
    }

    public function downloadAttachment($fileHash) {
        try {
            $filePath = decrypt($fileHash);
        } catch (\Exception $e) {
            $notify[] = 'Invalid file';
            return responseError('invalid_failed', $notify);
        }
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title     = slug(gs('site_name')) . '-attachments.' . $extension;
        try {
            $mimetype = mime_content_type($filePath);
        } catch (\Exception $e) {
            $notify[] = 'File downloaded failed';
            return responseError('download_failed', $notify);
        }
        if (!headers_sent()) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET,');
            header('Access-Control-Allow-Headers: Content-Type');
        }
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

    public function rentedItem() {
        $rentedItems  = Subscription::active()->where('item_id', '!=', 0)->where('user_id', auth()->id())->with('item')->apiQuery();
        $imagePath    = getFilePath('item_landscape');
        $portraitPath = getFilePath('item_portrait');
        $notify[]     = 'Rented Items';
        return responseSuccess('rented_items', $notify, [
            'rentedItems'    => $rentedItems,
            'landscape_path' => $imagePath,
            'portrait_path'  => $portraitPath,
        ]);
    }

    public function watchTelevision($id = 0) {
        $tv = LiveTelevision::with('category')->active()->where('id', $id)->first();
        if (!$tv) {
            $notify[] = 'Television not found';
            return responseError('not_found', $notify);
        }

        $user = auth()->user();

        $hasSubscribed = Subscription::where('user_id', $user->id)->where('channel_category_id', $tv->channel_category_id)->where('expired_date', '>=', now())->active()->first();
        if (!$hasSubscribed) {
            $notify[] = 'You must subscribe to watch this live TV';
            return responseError('subscription_not_found', $notify);
        }

        $notify[]  = $tv->title;
        $relatedTv = LiveTelevision::whereHas('category', function ($query) {
            $query->active();
        })->active()->where('id', '!=', $id)->get();

        $subscribedChannelId = $user->subscribedChannelId();
        $imagePath           = getFilePath('television');

        return responseSuccess('tv_details', $notify, [
            'tv'                  => $tv,
            'related_tv'          => $relatedTv,
            'image_path'          => $imagePath,
            'subscribedChannelId' => $subscribedChannelId,
        ]);
    }

    public function shortVideos($id = 0, $route = null) {
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

        $userReact     = ReelHistory::where('user_id', auth()->id())->get();
        $userLikesId   = $userReact->where('likes', Status::YES)->pluck('reel_id')->toArray();
        $userUnLikesId = $userReact->where('unlikes', Status::YES)->pluck('reel_id')->toArray();
        $userListId    = $userReact->where('list', Status::YES)->pluck('reel_id')->toArray();
        $lastId        = @$reels->last()->id;
        $videoPath     = getFilePath('reels');
        $notify[]      = 'Reels Data';
        if (request()->lastId) {
            if ($reels->count()) {
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

        return responseSuccess('reels_data', $notify, [
            'reels'         => $reels,
            'lastId'        => $lastId,
            'userLikesId'   => $userLikesId,
            'userUnLikesId' => $userUnLikesId,
            'userListId'    => $userListId,
            'videoPath'     => $videoPath,
        ]);
    }

    public function tournamentDetail($id) {
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
            $watchEligible = $this->checkWatchEligableTournament($tournament);
        }
        $imagePath              = getFilePath('tournament');
        $subscribedTournamentId = auth()->user()->subscribedTournamentId();
        $subscribedMatchId      = auth()->user()->subscribedMatchId();
        $notify[]               = $tournament->name;
        return responseSuccess('tournament_detail', $notify, [
            'tournament'             => $tournament,
            'imagePath'              => $imagePath,
            'games'                  => $games,
            'watchEligible'          => $watchEligible,
            'subscribedTournamentId' => $subscribedTournamentId,
            'subscribedMatchId'      => $subscribedMatchId,
        ]);
    }

    protected function checkWatchEligableTournament($tournament) {
        $watchEligible = true;
        if ($tournament->version == Status::PAID_VERSION) {
            $watchEligible = Subscription::active()->where('user_id', auth()->id())->where('tournament_id', $tournament->id)->exists();
        }
        return $watchEligible;
    }

    public function gameDetail($id) {
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

    protected function checkWatchEligableGame($game) {
        $watchEligible = true;
        if ($game->tournament->version == Status::FREE_VERSION) {
            return $watchEligible;
        }
        if ($game->version == Status::PAID_VERSION) {
            $tournamentId  = $game->tournament_id;
            $gameId        = $game->id;
            $watchEligible = Subscription::active()->where('user_id', auth()->id())->where(function ($query) use ($gameId, $tournamentId) {
                $query->where('game_id', $gameId)->orWhere('tournament_id', $tournamentId);
            })->exists();
        }
        return $watchEligible;
    }

    public function watchGame($id) {
        $game = Game::active()->with('tournament', 'teamOne', 'teamTwo')->where('id', $id)->first();
        if (!$game) {
            $notify[] = 'Game not found';
            return responseError('not_found', $notify);
        }
        $watchEligible = $this->checkWatchEligableGame($game);
        $imagePath     = getFilePath('game');
        if (!$watchEligible) {
            $notify[] = 'Please purchase a subscription for this game';
            return responseError('purchase_subscription', $notify, [
                'game'          => $game,
                'watchEligible' => $watchEligible,
                'imagePath'     => $imagePath,
            ]);
        }
        $notify[] = 'Game not found';
        return responseSuccess('watch_game', $notify, [
            'game'          => $game,
            'watchEligible' => $watchEligible,
            'imagePath'     => $imagePath,
        ]);
    }

    public function liveTelevision($scope = null) {
        $notify[] = 'Live Television';
        if ($scope == 'show_all') {
            $televisions = ChannelCategory::active()->withWhereHas('channels', function ($query) {
                $query->active();
            })->apiQuery();
        } else {
            $televisions = LiveTelevision::active()->apiQuery();
        }
        $imagePath = getFilePath('television');

        $subscribedChannelId = auth()->user()->subscribedChannelId();

        return responseSuccess('live_television', $notify, [
            'televisions'         => $televisions,
            'image_path'          => $imagePath,
            'subscribedChannelId' => $subscribedChannelId,
        ]);
    }
}
