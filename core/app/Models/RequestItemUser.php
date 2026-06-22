<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestItemUser extends Model
{
    public function requestItem()
    {
        return $this->belongsTo(RequestItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
