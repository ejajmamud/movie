<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model {
    use GlobalStatus;

    public function users()
    {
        return $this->hasMany(RequestItemUser::class);
    }

    public function votes() {
        return $this->hasMany(Vote::class);
    }

    public function userVote() {
        return $this->votes()->where('user_id', auth()->id())->first();
    }

    public function itemSubscribes() {
        return $this->hasMany(ItemSubscribe::class);
    }

    public function userItemSubscribes() {
        return $this->itemSubscribes()->where('user_id', auth()->id())->first();
    }

    public function scopePending() {
        return $this->where('status', Status::REQUEST_ITEM_PENDING);
    }
    public function scopeAccepted() {
        return $this->where('status', Status::REQUEST_ITEM_ACCEPTED);
    }
    public function scopeUnpublished() {
        return $this->accepted()->where('is_publish', Status::NO);
    }

    public function statusBadge(): Attribute {
        return new Attribute(function () {
            $html = '';
            if ($this->status == Status::REQUEST_ITEM_ACCEPTED) {
                $html = '<span class="badge badge--success">' . trans("Approved") . '</span>';
            } else if ($this->status == Status::REQUEST_ITEM_PENDING) {
                $html = '<span class="badge badge--warning">' . trans("Pending") . '</span>';
            } else if ($this->status == Status::REQUEST_ITEM_REJECTED) {
                $html = '<span class="badge badge--danger">' . trans("Rejected") . '</span>';
            }
            return $html;
        });
    }
}