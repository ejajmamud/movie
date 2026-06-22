<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\ApiQuery;
use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    use HasFactory, GlobalStatus, ApiQuery;
    
    protected $casts = [
        'seo_content' => 'object',
    ];

    public function games()
    {
        return $this->hasMany(Game::class, 'tournament_id');
    }

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
}
