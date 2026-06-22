<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertiseView extends Model
{
    protected $fillable = [
        'user_id',
        'video_advertise_id',
        'viewed_date',
        'views'
    ];

    protected $casts = [
        'viewed_date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function videoAdvertise()
    {
        return $this->belongsTo(VideoAdvertise::class);
    }

    public static function updateOrCreateView($userId, $adId)
    {
        return self::updateOrCreate(
            [
                'user_id' => $userId,
                'video_advertise_id' => $adId,
                'viewed_date' => now()->toDateString()
            ],
            [
                'views' => \DB::raw('views + 1')
            ]
        );
    }
}
