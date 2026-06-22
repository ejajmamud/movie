<?php

namespace App\Models;

use App\Traits\ApiQuery;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class ChannelCategory extends Model {
    use GlobalStatus, ApiQuery;
    public function channels() {
        return $this->hasMany(LiveTelevision::class, 'channel_category_id', 'id');
    }
}
