<?php

namespace App\Models;

use App\Traits\ApiQuery;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model {
    use GlobalStatus, ApiQuery;
    protected $guarded = ['id'];

    protected $casts = ['expired_date' => 'datetime'];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function deposit() {
        return $this->hasOne(Deposit::class, 'subscription_id');
    }

    public function plan() {
        return $this->belongsTo(Plan::class, 'plan_id');
    }
    public function item() {
        return $this->belongsTo(Item::class, 'item_id');
    }
    public function tournament() {
        return $this->belongsTo(Tournament::class, 'tournament_id');
    }
    public function game() {
        return $this->belongsTo(Game::class, 'game_id');
    }
    public function channelCategory() {
        return $this->belongsTo(ChannelCategory::class, 'channel_category_id');
    }

    public function getSubscriptionPriceAttribute() {
        $amount = 0;
        if ($this->plan_id) {
            $amount = @$this->plan->pricing;
        } else if ($this->item_id) {
            $amount = @$this->item->rent_price;
        } else if ($this->tournament_id) {
            $amount = @$this->tournament->price;
        } else if ($this->game_id) {
            $amount = @$this->game->price;
        } else if ($this->channel_category_id) {
            $amount = @$this->channelCategory->price;
        }
        return $amount;
    }

    public function getRentedDurationAttribute() {
        $duration = '';
        if ($this->expired_date > now()) {
            $duration = diffForHumans($this->expired_date);
        } else {
            $duration = 'Expired';
        }
        return $duration;
    }
}
