<?php

namespace App\Constants;

class FileInfo
{

    /*
    |--------------------------------------------------------------------------
    | File Information
    |--------------------------------------------------------------------------
    |
    | This class basically contain the path of files and size of images.
    | All information are stored as an array. Developer will be able to access
    | this info as method and property using FileManager class.
    |
     */

    public function fileInfo()
    {
        $data['verify'] = [
            'path' => 'assets/verify',
        ];
        $data['default'] = [
            'path' => 'assets/images/default.png',
        ];
        $data['ticket'] = [
            'path' => 'assets/support',
        ];
        $data['logoIcon'] = [
            'path' => 'assets/images/logo_icon',
        ];
        $data['favicon'] = [
            'size' => '128x128',
        ];
        $data['extensions'] = [
            'path' => 'assets/images/extensions',
            'size' => '36x36',
        ];
        $data['seo'] = [
            'path' => 'assets/images/seo',
            'size' => '1180x600',
        ];
        $data['userProfile'] = [
            'path' => 'assets/images/user/profile',
            'size' => '100x100',
        ];
        $data['adminProfile'] = [
            'path' => 'assets/admin/images/profile',
            'size' => '400x400',
        ];
        $data['push'] = [
            'path' => 'assets/images/push_notification',
        ];
        $data['appPurchase'] = [
            'path' => 'assets/in_app_purchase_config',
        ];
        $data['maintenance'] = [
            'path' => 'assets/images/maintenance',
            'size' => '660x325',
        ];
        $data['language'] = [
            'path' => 'assets/images/language',
            'size' => '50x50',
        ];
        $data['gateway'] = [
            'path' => 'assets/images/gateway',
            'size' => '',
        ];
        $data['subtitle'] = [
            'path' => 'assets/subtitles',
        ];
        $data['thumbnail'] = [
            'path' => 'assets/thumbnail',
        ];
        $data['slider'] = [
            'path' => 'assets/images/slider',
            'size' => '240x300',
        ];
        $data['labflixSlider'] = [
            'path' => 'assets/images/slider',
            'size' => '1920x700',
        ];
        $data['item_landscape'] = [
            'path' => 'assets/images/item/landscape/',
        ];
        $data['item_portrait'] = [
            'path' => 'assets/images/item/portrait/',
        ];
        $data['itemSeo'] = [
            'path' => 'assets/images/item/seo',
        ];
        $data['episode'] = [
            'path' => 'assets/images/item/episode/',
        ];
        $data['ads'] = [
            'path' => 'assets/images/ads',
        ];
        $data['reels'] = [
            'path' => 'assets/reels',
        ];
        $data['plan'] = [
            'path' => 'assets/images/plan',
            'size' => '250x250',
        ];
        $data['television'] = [
            'path' => 'assets/images/television',
            'size' => '400x400',
        ];
        $data['televisionSeo'] = [
            'path' => 'assets/images/television/seo',
        ];
        $data['pushConfig'] = [
            'path' => 'assets/admin',
        ];
        $data['tournament'] = [
            'path'  => 'assets/images/tournament',
            'size'  => '1920x800',
            'thumb' => '480x200',
        ];
        $data['tournamentSeo'] = [
            'path' => 'assets/images/tournament/seo',
        ];
        $data['game'] = [
            'path'  => 'assets/images/game',
            'size'  => '1920x800',
            'thumb' => '480x200',
        ];
        $data['gameSeo'] = [
            'path' => 'assets/images/game/seo',
        ];
        return $data;
    }
}
