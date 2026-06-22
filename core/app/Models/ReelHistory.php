<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReelHistory extends Model {
    use HasFactory;

    public function reel() {
        return $this->belongsTo(Reel::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function scopeLikes() {
        return $this->where('likes', Status::YES);
    }
    public function scopeUnLikes() {
        return $this->where('unlikes', Status::YES);
    }
}
