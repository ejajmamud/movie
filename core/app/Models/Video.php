<?php

namespace App\Models;

use App\Constants\Status;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Video extends Model {

     protected $fillable = [
        'item_id'
    ];
    protected $casts = [
        'seconds'  => 'object',
        'ads_time' => 'object',
    ];

    public function episode() {
        return $this->belongsTo(Episode::class);
    }

    public function item() {
        return $this->belongsTo(Item::class);
    }

    public function subtitles() {
        return $this->hasMany(Subtitle::class, 'video_id');
    }

    public function advertiseViews() {
        return $this->hasMany(AdvertiseView::class);
    }

    public function getAds() {
        $adsTime = [];
        $user    = auth()->user();

        // Early return if user's plan blocks ads
        if ($user?->plan?->show_ads === false) {
            return $adsTime;
        }

        if (!$this->seconds) {
            return $adsTime;
        }

        $videoAds = $this->getEligibleAds($user);

        // Add time-based scheduling check
        $currentTime = Carbon::now()->format('H:i:s');
        $videoAds->when(
            $videoAds->getQuery()->columns && in_array('ad_schedule_from', $videoAds->getQuery()->columns),
            fn($query) => $query->whereTime('ad_schedule_from', '<=', $currentTime)
                ->whereTime('ad_schedule_to', '>=', $currentTime)
        );

        // Cache the ads query result to avoid multiple database hits
        $availableAds = $videoAds->get();
        if ($availableAds->isEmpty()) {
            return $adsTime;
        }

        // Create a collection to track used ads
        $remainingAds = $availableAds->shuffle();
        $adTimeSlots  = collect($this->seconds)->shuffle();

        // Distribute ads evenly across time slots
        foreach ($adTimeSlots as $time) {
            if ($remainingAds->isEmpty()) {
                $remainingAds = $availableAds->shuffle();
            }

            // Get next available ad
            $videoAd = $remainingAds->shift();

            if ($videoAd) {
                $adsTime[$time] = [
                    'id'        => $videoAd->id,
                    'url'       => $this->generateAdUrl($videoAd),
                    'ad_format' => $videoAd->ad_format,
                ];
            }
        }

        ksort($adsTime);

        return $adsTime;
    }

    protected function getEligibleAds($user) {
        $geoCountry = $user?->country_code ?? 'GLOBAL';

        return VideoAdvertise::query()->where(
            fn($query) => $query
                ->where('is_global', Status::YES)
                ->orWhere(
                    fn($q) => $q->where('is_global', Status::NO)
                        ->whereJsonContains('geo_targets', $geoCountry)
                )
        )
            ->when($user, function ($query) use ($user) {
                $today = now()->toDateString();

                return $query->where(
                    fn($q) => $q
                        ->where('frequency_cap', 0)
                        ->orWhereRaw('
            (frequency_cap > 0) AND (
                CASE
                    WHEN is_daily = 1 THEN (
                        SELECT COALESCE(SUM(views), 0)
                        FROM advertise_views
                        WHERE video_advertise_id = video_advertises.id
                            AND user_id = ?
                            AND viewed_date = ?
                    )
                    ELSE (
                        SELECT COALESCE(SUM(views), 0)
                        FROM advertise_views
                        WHERE video_advertise_id = video_advertises.id
                            AND user_id = ?
                    )
                END < frequency_cap
            )
        ', [$user->id, $today, $user->id])
                );
            });
    }

    private function generateAdUrl($videoAd) {

        if ($videoAd->content->link) {
            return $videoAd->content->link;
        }

        $general = gs();
        // Handle FTP Server
        if ($videoAd->server == Status::FTP_SERVER) {
            $domainRoot = $general->ftp->root;
            $mainRoot   = str_replace("public_html/", "", $domainRoot);
            return $general->ftp->domain . '/' . $mainRoot . '/' . $videoAd->content->video;
        }

        // Handle Wasabi Server
        if ($videoAd->server == Status::WASABI_SERVER) {
            $s3 = new S3Client([
                'endpoint'    => $general->wasabi->endpoint,
                'region'      => $general->wasabi->region,
                'version'     => 'latest',
                'credentials' => [
                    'key'    => $general->wasabi->key,
                    'secret' => $general->wasabi->secret,
                ],
            ]);

            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => $general->wasabi->bucket,
                'Key'    => $videoAd->content->video,
                'ACL'    => 'public-read',
            ]);

            $request = $s3->createPresignedRequest($cmd, '+20 minutes');
            return (string) $request->getUri(); //Wasabi
        }

        // Handle Digital Ocean Server
        if ($videoAd->server == Status::DIGITAL_OCEAN_SERVER) {
            $s3 = new S3Client([
                'endpoint'    => $general->digital_ocean->endpoint,
                'region'      => $general->digital_ocean->region,
                'version'     => 'latest',
                'credentials' => [
                    'key'    => $general->digital_ocean->key,
                    'secret' => $general->digital_ocean->secret,
                ],
            ]);

            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => $general->digital_ocean->bucket,
                'Key'    => $videoAd->content->video,
                'ACL'    => 'public-read',
            ]);

            $request = $s3->createPresignedRequest($cmd, '+20 minutes');
            return (string) $request->getUri(); // Digital Ocean
        }

        // Default to Local Asset
        return asset('assets/videos/' . $videoAd->content->video);
    }

}
