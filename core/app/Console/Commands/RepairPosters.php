<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Item;
use App\Lib\CurlRequest;

class RepairPosters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:repair-posters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan database for items with missing physical poster files and repair them from IMDb API.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting poster files diagnostics and repair...');

        $items = Item::all();
        $total = $items->count();
        $current = 0;
        $repairedCount = 0;
        $missingCount = 0;
        $skippedCount = 0;

        $this->updateProgress(0, $total, 0, 0, 'Initializing diagnostics...', 'repairing');

        $portraitPath = base_path('../assets/images/item/portrait/');
        $landscapePath = base_path('../assets/images/item/landscape/');

        try {
            foreach ($items as $item) {
                $current++;
                $this->updateProgress($current, $total, $repairedCount, $skippedCount, "Checking: {$item->title}", 'repairing');

                $portraitName = @$item->image->portrait;
                $landscapeName = @$item->image->landscape;

                $portraitFile = $portraitPath . $portraitName;
                $landscapeFile = $landscapePath . $landscapeName;

                $p_missing = !$portraitName || !file_exists($portraitFile) || filesize($portraitFile) == 0;
                $l_missing = !$landscapeName || !file_exists($landscapeFile) || filesize($landscapeFile) == 0;

                if ($p_missing || $l_missing) {
                    $missingCount++;
                    $this->warn("Poster missing for item ID {$item->id}: \"{$item->title}\". Attempting to repair...");

                    if ($this->repairItemPoster($item, $portraitPath, $landscapePath)) {
                        $repairedCount++;
                    } else {
                        $skippedCount++;
                    }
                    
                    sleep(2);
                } else {
                    $skippedCount++;
                }
                $this->updateProgress($current, $total, $repairedCount, $skippedCount, "Processed: {$item->title}", 'repairing');
            }

            $this->info("Diagnostics complete. Total missing found: {$missingCount}. Successfully repaired: {$repairedCount}.");
            $this->updateProgress($current, $total, $repairedCount, $skippedCount, "Diagnostics complete. Repaired: {$repairedCount}, Scanned: {$total}", 'completed');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->updateProgress($current, $total, $repairedCount, $skippedCount, 'Repair failed: ' . $e->getMessage(), 'failed', $e->getMessage());
            $this->error('Repair failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function repairItemPoster($item, $portraitPath, $landscapePath)
    {
        $query = urlencode($item->title);
        $searchUrl = "https://api.imdbapi.dev/search/titles?query={$query}&limit=1";
        $searchResponse = CurlRequest::curlContent($searchUrl);
        $searchData = json_decode($searchResponse);

        $imdbId = null;
        if (isset($searchData->titles[0]->id)) {
            $imdbId = $searchData->titles[0]->id;
        } else {
            // AI Fallback to resolve IMDb ID directly
            $type = $item->item_type == 1 ? 'movie' : 'tv';
            $this->info("IMDb search failed or rate-limited. Querying AI to resolve IMDb ID for \"{$item->title}\"...");
            $imdbId = \App\Services\AiService::resolveImdbId($item->title, $type);
        }

        if (!$imdbId) {
            $this->error("Could not resolve \"{$item->title}\" to an IMDb ID.");
            return false;
        }
        $titleUrl = "https://api.imdbapi.dev/titles/{$imdbId}";
        $titleResponse = CurlRequest::curlContent($titleUrl);
        $titleData = json_decode($titleResponse);

        if (!isset($titleData->primaryImage->url)) {
            $this->error("IMDb details for \"{$item->title}\" ({$imdbId}) has no image URL.");
            return false;
        }

        $imageUrl = $titleData->primaryImage->url;
        $newPortraitName = uniqid() . '.jpg';
        $newLandscapeName = uniqid() . '.jpg';

        $p_downloaded = $this->downloadImage($imageUrl, $portraitPath . $newPortraitName);
        $l_downloaded = $this->downloadImage($imageUrl, $landscapePath . $newLandscapeName);

        if ($p_downloaded && $l_downloaded) {
            $item->image = (object)[
                'portrait' => $newPortraitName,
                'landscape' => $newLandscapeName,
            ];
            $item->imdb_id = $imdbId;

            // AI Fallback for missing/weak description or metadata
            if (empty($item->description) || empty($item->team->casts) || $item->team->casts == 'Unknown') {
                $type = $item->item_type == 1 ? 'movie' : 'tv';
                $aiData = \App\Services\AiService::generateMetadata($item->title, $type);
                if ($aiData) {
                    $item->description = $aiData['description'] ?? $item->description;
                    $item->preview_text = $aiData['tagline'] ?? $item->preview_text;
                    
                    $team = $item->team;
                    if (is_string($team)) {
                        $team = json_decode($team);
                    }
                    $team->casts = $aiData['casts'] ?? $team->casts;
                    $team->director = $aiData['director'] ?? $team->director;
                    $item->team = (object)$team;
                    $this->info("AI enriched metadata for \"{$item->title}\".");
                }
            }

            $item->save();
            $this->info("Successfully repaired poster for \"{$item->title}\" (IMDb ID: {$imdbId}).");
            return true;
        }

        $this->error("Failed to download poster image for \"{$item->title}\".");
        return false;
    }

    private function downloadImage($url, $path)
    {
        $content = @file_get_contents($url);
        if ($content !== false) {
            @file_put_contents($path, $content);
            return basename($path);
        }
        return null;
    }

    public function info($string, $verbosity = null)
    {
        parent::info($string, $verbosity);
        \App\Services\AiService::log("[INFO] " . $string);
    }

    public function warn($string, $verbosity = null)
    {
        parent::warn($string, $verbosity);
        \App\Services\AiService::log("[WARN] " . $string);
    }

    public function error($string, $verbosity = null)
    {
        parent::error($string, $verbosity);
        \App\Services\AiService::log("[ERROR] " . $string);
    }

    private function updateProgress($current, $total, $success, $skipped, $itemTitle, $status = 'repairing', $error = null)
    {
        cache()->put('poster_repair_progress', [
            'status' => $status,
            'total' => $total,
            'current' => $current,
            'success_count' => $success,
            'skipped_count' => $skipped,
            'current_item' => $itemTitle,
            'error_message' => $error,
            'last_updated' => now()->toDateTimeString()
        ], 600);
    }
}
