<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reel extends Model {
    use HasFactory;

    public function getReelVideoAttribute() {
        return getImage(getFilePath('reels') . '/' . $this->video);
    }
}
