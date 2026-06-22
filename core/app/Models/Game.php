<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use GlobalStatus;

    protected $casts = [
        'seo_content' => 'object',
    ];
    public function getVersionNameAttribute(): string
    {
        return $this->version == Status::FREE_VERSION ? 'Free' : 'Paid';
    }

    public function versionBadge(): Attribute
    {
        return new Attribute(
            function () {
                $html = '';
                if ($this->version == Status::FREE_VERSION) {
                    $html = '<span class="badge badge--info">' . trans('Free') . '</span>';
                } else {
                    $html = '<span class="badge badge--danger">' . trans('Paid') . '</span>';
                }
                return $html;
            }
        );
    }
    public function tournament()
    {
        return $this->belongsTo(Tournament::class, 'tournament_id');
    }
    public function teamOne()
    {
        return $this->belongsTo(Team::class, 'team_one_id');
    }
    public function teamTwo()
    {
        return $this->belongsTo(Team::class, 'team_two_id');
    }
    public function getGameNameAttribute(): string
    {
        return $this->teamOne->name . ' vs ' . $this->teamTwo->name;
    }
    public function liveComments()
    {
        return $this->hasMany(LiveComment::class);
    }
}
