<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\Subscriptions;
use App\Models\DeviceToken;
use App\Models\History;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function home()
    {
        return to_route('home');
    }

    public function depositHistory(Request $request)
    {
        $pageTitle = 'Payment History';
        $deposits  = auth()->user()->deposits()->searchable(['trx'])->with(['gateway', 'subscription' => function ($query) {
            $query->with('plan', 'item');
        }])->orderBy('id', 'desc')->paginate(getPaginate());

        return view('Template::user.deposit_history', compact('pageTitle', 'deposits'));
    }

    public function watchHistory()
    {
        $pageTitle = 'Watch History';
        $histories = History::where('user_id', auth()->id());
        $total     = $histories->count();
        if (request()->lastId) {
            $histories = $histories->where('id', '<', request()->lastId);
        }
        $histories = $histories->with('item', 'episode.item')->orderBy('id', 'desc')->take(20)->get();
        $lastId    = @$histories->last()->id;

        if (request()->lastId) {
            if ($histories->count()) {
                $data = view('Template::user.watch.fetch_history', compact('histories'))->render();
                return response()->json([
                    'data'   => $data,
                    'lastId' => $lastId,
                ]);
            }
            return response()->json([
                'error' => 'History not more yet',
            ]);
        }
        return view('Template::user.watch.history', compact('pageTitle', 'histories', 'lastId', 'total'));
    }

    public function removeHistory(Request $request, $id)
    {
        History::where('id', $id)->where('user_id', auth()->id())->delete();
        $notify[] = ['success', 'Item removed from history list.'];
        return back()->withNotify($notify);
    }

    public function userData()
    {
        $user = auth()->user();

        if ($user->profile_complete == Status::YES) {
            return to_route('user.home');
        }

        $pageTitle  = 'User Data';
        $info       = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = isset($info['code']) ? implode(',', $info['code']) : '';
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        return view('Template::user.user_data', compact('pageTitle', 'user', 'countries', 'mobileCode'));
    }

    public function userDataSubmit(Request $request)
    {

        $user = auth()->user();

        if ($user->profile_complete == Status::YES) {
            return to_route('user.home');
        }

        $countryData  = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));

        $request->validate([
            'country_code' => 'required|in:' . $countryCodes,
            'country'      => 'required|in:' . $countries,
            'mobile_code'  => 'required|in:' . $mobileCodes,
            'username'     => 'required|unique:users|min:6',
            'mobile'       => ['required', 'regex:/^([0-9]*)$/', Rule::unique('users')->where('dial_code', $request->mobile_code)],
        ]);

        if (preg_match("/[^a-z0-9_]/", trim($request->username))) {
            $notify[] = ['info', 'Username can contain only small letters, numbers and underscore.'];
            $notify[] = ['error', 'No special character, space or capital letters in username.'];
            return back()->withNotify($notify)->withInput($request->all());
        }

        $user->country_code = $request->country_code;
        $user->mobile       = $request->mobile;
        $user->username     = $request->username;

        $user->address      = $request->address;
        $user->city         = $request->city;
        $user->state        = $request->state;
        $user->zip          = $request->zip;
        $user->country_name = isset($request->country) ? $request->country : '';
        $user->dial_code    = $request->mobile_code;

        $user->profile_complete = Status::YES;
        $user->save();

        return to_route('user.home');
    }

    public function addDeviceToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'errors' => $validator->errors()->all()];
        }

        $deviceToken = DeviceToken::where('token', $request->token)->first();

        if ($deviceToken) {
            return ['success' => true, 'message' => 'Already exists'];
        }

        $deviceToken          = new DeviceToken();
        $deviceToken->user_id = auth()->user()->id;
        $deviceToken->token   = $request->token;
        $deviceToken->is_app  = Status::NO;
        $deviceToken->save();

        return ['success' => true, 'message' => 'Token saved successfully'];
    }

    public function downloadAttachment($fileHash)
    {
        $filePath  = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $title     = slug(gs('site_name')) . '- attachments.' . $extension;
        try {
            $mimetype = mime_content_type($filePath);
        } catch (\Exception $e) {
            $notify[] = ['error', 'File does not exists'];
            return back()->withNotify($notify);
        }
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

    public function subscribePlan($id)
    {
        $subscription = new Subscriptions('plan', $id);
        $data         = $subscription->getSubscription();
        session()->put('subscription_id', $data->id);
        return redirect()->route('user.deposit.index');
    }

    public function subscribeVideo($id)
    {
        $subscription = new Subscriptions('item', $id);
        $data         = $subscription->getSubscription();
        session()->put('subscription_id', $data->id);
        return redirect()->route('user.deposit.index');
    }

    public function subscribeGame($id)
    {
        $subscription = new Subscriptions('game', $id);
        $data         = $subscription->getSubscription();
        session()->put('subscription_id', $data->id);
        return redirect()->route('user.deposit.index');
    }

    public function subscribeTournament($id)
    {
        $subscription = new Subscriptions('tournament', $id);
        $data         = $subscription->getSubscription();
        session()->put('subscription_id', $data->id);
        return redirect()->route('user.deposit.index');
    }
    public function subscribeChannel($id)
    {
        if (!gs('live_tv')) {
            abort(404);
        };
        $subscription = new Subscriptions('channel_category', $id);
        $data         = $subscription->getSubscription();
        session()->put('subscription_id', $data->id);
        return redirect()->route('user.deposit.index');
    }

    public function rentedItem()
    {
        $pageTitle   = 'Rented Items';
        $rentedItems = Subscription::active()->where('item_id', '!=', 0)->where('user_id', auth()->id());
        $total       = (clone $rentedItems)->count();
        if (request()->lastId) {
            $rentedItems = $rentedItems->where('id', '<', request()->lastId);
        }

        $rentedItems = $rentedItems->with('item')->orderBy('id', 'desc')->take(20)->get();
        $lastId      = @$rentedItems->last()->id;
        if (request()->lastId) {
            if ($rentedItems->count()) {
                $data = view('Template::user.rent.fetch_item', compact('rentedItems'))->render();
                return response()->json([
                    'data'   => $data,
                    'lastId' => $lastId,
                ]);
            }
            return response()->json([
                'error' => 'Item not more yet',
            ]);
        }
        return view('Template::user.rent.items', compact('pageTitle', 'rentedItems', 'total', 'lastId'));
    }
}
