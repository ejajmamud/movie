<?php

namespace App\Lib;

use App\Constants\Status;
use App\Models\ChannelCategory;
use App\Models\Game;
use App\Models\Item;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tournament;
use Illuminate\Validation\ValidationException;

class Subscriptions {

    private $key;
    private $id;
    private $modelClass;

    public function __construct($key, $id) {
        $className = [
            'tournament'       => Tournament::class,
            'game'             => Game::class,
            'plan'             => Plan::class,
            'item'             => Item::class,
            'channel_category' => ChannelCategory::class,
        ];
        $this->key        = $key;
        $this->id         = $id;
        $this->modelClass = $className[$this->key];
    }

    public function getSubscription() {
        $data = $this->modelClass::active()->where('id', $this->id)->first();
        if (!$data) {
            throw ValidationException::withMessages(['error' => ucfirst($this->key) . ' not found ']);
        }

        $hasSubscribed     = false;
        $existSubscription = Subscription::where('user_id', auth()->id())->where($this->key . '_id', $this->id)->with('deposit')->orderBy('id', 'desc');

        if (in_array($this->key, ['game', 'tournament'])) {
            $existSubscription = $existSubscription->first();
            if ($existSubscription) {
                $hasSubscribed = $existSubscription->status == Status::PAYMENT_SUCCESS;
                if (!$hasSubscribed && $existSubscription->deposit?->status == Status::PAYMENT_PENDING) {
                    throw ValidationException::withMessages(['error' => 'Already one payment in pending. Please Wait']);
                }
            }
        }

        $duration = null;
        if ($this->key == 'item') {
            $hasSubscribed = $existSubscription->active()->whereDate('expired_date', '>', now())->exists();
            $duration      = $data->rental_period;
        }

        if ($this->key == 'plan') {
            $user           = auth()->user();
            $pendingPayment = $user->deposits()->where('status', Status::PAYMENT_PENDING)->count();
            if ($pendingPayment > 0) {
                throw ValidationException::withMessages(['error' => 'Already one payment in pending. Please Wait']);
            }
            $hasSubscribed = $user->exp > now();
            $duration      = $data->duration;
        }

        if ($this->key == 'channel_category') {
            $hasSubscribed = $existSubscription->active()->whereDate('expired_date', '>', now())->exists();
            $duration      = 30;
        }

        if ($hasSubscribed) {
            throw ValidationException::withMessages(['error' => 'Already subscribed to this ' . $this->key]);
        }

        $subscription = $this->insertSubscription($duration);
        return $subscription;
    }

    private function insertSubscription($duration = null) {
        $subscription = Subscription::active()->where('user_id', auth()->id())->where($this->key . '_id', $this->id)->first();
        if (!$subscription) {
            $column                = $this->key . '_id';
            $subscription          = new Subscription();
            $subscription->user_id = auth()->id();
            $subscription->$column = $this->id;
        }
        $subscription->expired_date = $duration ? now()->addDays((int) $duration) : null;
        $subscription->status       = Status::DISABLE;
        $subscription->save();

        return $subscription;
    }
}
