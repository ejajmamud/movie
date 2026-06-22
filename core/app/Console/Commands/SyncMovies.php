<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;
use App\Models\Item;
use App\Models\Video;
use App\Models\Episode;
use App\Models\LiveTelevision;
use Illuminate\Support\Str;
use App\Lib\CurlRequest;

class SyncMovies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movies:sync {--limit=10 : Number of items to sync} {--type=all : Types to sync: movie, tv, all} {--library : Import popular library instead of only trending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically fetch trending movies and TV shows from TMDB and import them into the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $type = $this->option('type');
        
        $general = gs(); // Get general settings
        $apiKey = $general->tmdb_api;
        
        $movieCategory = Category::where('name', 'Movie')->first();
        if (!$movieCategory) {
            $movieCategory = new Category();
            $movieCategory->name = 'Movie';
            $movieCategory->status = 1;
            $movieCategory->save();
        }

        $seriesCategory = Category::where('name', 'TV Series')->first();
        if (!$seriesCategory) {
            $seriesCategory = new Category();
            $seriesCategory->name = 'TV Series';
            $seriesCategory->status = 1;
            $seriesCategory->save();
        }

        $portraitPath = base_path('../assets/images/item/portrait/');
        $landscapePath = base_path('../assets/images/item/landscape/');

        @mkdir($portraitPath, 0755, true);
        @mkdir($landscapePath, 0755, true);

        if (!$apiKey || $apiKey == '---------------------') {
            $this->info('TMDB API Key is not configured in General Settings. Using keyless IMDb API (api.imdbapi.dev) for syncing...');
            return $this->handleImdbSync($limit, $type, $movieCategory->id, $seriesCategory->id, $portraitPath, $landscapePath);
        }

        $this->info('Starting TMDB sync...');

        // Sync Movies
        if ($type === 'movie' || $type === 'all') {
            $this->info('Fetching trending movies...');
            $url = "https://api.themoviedb.org/3/trending/movie/week?api_key={$apiKey}";
            $response = CurlRequest::curlContent($url);
            $data = json_decode($response);

            if (isset($data->results)) {
                $count = 0;
                foreach ($data->results as $result) {
                    if ($count >= $limit) break;
                    
                    // Check if item already exists by title
                    $title = $result->title ?? $result->original_title;
                    $exists = Item::where('title', $title)->exists();
                    
                    if (!$exists) {
                        $this->info("Importing movie: {$title}");
                        if ($this->importMovie($result->id, $movieCategory->id, $apiKey, $portraitPath, $landscapePath)) {
                            $count++;
                        }
                    } else {
                        $this->info("Skipped (already exists): {$title}");
                    }
                }
                $this->info("Successfully imported {$count} new movies.");
            } else {
                $this->error('Failed to retrieve movies from TMDB API.');
            }
        }

        // Sync TV Series
        if ($type === 'tv' || $type === 'all') {
            $this->info('Fetching trending TV shows...');
            $url = "https://api.themoviedb.org/3/trending/tv/week?api_key={$apiKey}";
            $response = CurlRequest::curlContent($url);
            $data = json_decode($response);

            if (isset($data->results)) {
                $count = 0;
                foreach ($data->results as $result) {
                    if ($count >= $limit) break;
                    
                    $title = $result->name ?? $result->original_name;
                    $exists = Item::where('title', $title)->exists();
                    
                    if (!$exists) {
                        $this->info("Importing TV series: {$title}");
                        if ($this->importTVShow($result->id, $seriesCategory->id, $apiKey, $portraitPath, $landscapePath)) {
                            $count++;
                        }
                    } else {
                        $this->info("Skipped (already exists): {$title}");
                    }
                }
                $this->info("Successfully imported {$count} new TV shows.");
            } else {
                $this->error('Failed to retrieve TV shows from TMDB API.');
            }
        }

        $this->info('Sync process completed!');
        return Command::SUCCESS;
    }

    private function importMovie($tmdbId, $categoryId, $apiKey, $portraitPath, $landscapePath)
    {
        $movieUrl = "https://api.themoviedb.org/3/movie/{$tmdbId}?api_key={$apiKey}";
        $creditsUrl = "https://api.themoviedb.org/3/movie/{$tmdbId}/credits?api_key={$apiKey}";
        $tagsUrl = "https://api.themoviedb.org/3/movie/{$tmdbId}/keywords?api_key={$apiKey}";

        $movieData = json_decode(CurlRequest::curlContent($movieUrl));
        $creditsData = json_decode(CurlRequest::curlContent($creditsUrl));
        $tagsData = json_decode(CurlRequest::curlContent($tagsUrl));

        if (!isset($movieData->id)) {
            return false;
        }

        // Parse cast, directors, producers, genres
        $casts = [];
        if (isset($creditsData->cast)) {
            foreach (array_slice($creditsData->cast, 0, 5) as $actor) {
                $casts[] = $actor->name;
            }
        }

        $directors = [];
        $producers = [];
        if (isset($creditsData->crew)) {
            foreach ($creditsData->crew as $crew) {
                if ($crew->job === 'Director') {
                    $directors[] = $crew->name;
                }
                if ($crew->job === 'Producer') {
                    $producers[] = $crew->name;
                }
            }
        }

        $genres = [];
        if (isset($movieData->genres)) {
            foreach ($movieData->genres as $genre) {
                $genres[] = $genre->name;
            }
        }

        $languages = [];
        if (isset($movieData->spoken_languages)) {
            foreach ($movieData->spoken_languages as $lang) {
                $languages[] = $lang->name;
            }
        }

        $tags = [];
        if (isset($tagsData->keywords)) {
            foreach (array_slice($tagsData->keywords, 0, 10) as $keyword) {
                $tags[] = $keyword->name;
            }
        }

        $item = new Item();
        $item->category_id = $categoryId;
        $item->slug = Str::slug(($movieData->title ?? $movieData->original_title) . '-' . time() . getTrx(5));
        $item->title = $movieData->title ?? $movieData->original_title;
        $item->preview_text = $movieData->tagline ?? substr($movieData->overview, 0, 100);
        $item->description = $movieData->overview;
        $item->item_type = 1; // Movie
        $item->status = 1;
        $item->single = 1;
        $item->trending = 1;
        $item->featured = 1;
        $item->ratings = $movieData->vote_average ?? 0;
        $item->view = rand(100, 1000);
        $item->tags = implode(',', $tags) ?: 'Action,Adventure';
        
        $item->team = (object)[
            'director' => implode(',', $directors) ?: 'Unknown',
            'producer' => implode(',', $producers) ?: 'Unknown',
            'casts' => implode(',', $casts) ?: 'Unknown',
            'genres' => implode(',', $genres) ?: 'Action',
            'language' => implode(',', $languages) ?: 'English',
        ];

        // Download posters
        $portraitName = uniqid() . '.jpg';
        $landscapeName = uniqid() . '.jpg';
        
        if (isset($movieData->poster_path)) {
            $this->downloadImage("https://image.tmdb.org/t/p/w500{$movieData->poster_path}", $portraitPath . $portraitName);
        }
        if (isset($movieData->backdrop_path)) {
            $this->downloadImage("https://image.tmdb.org/t/p/original{$movieData->backdrop_path}", $landscapePath . $landscapeName);
        }

        $item->image = (object)[
            'portrait' => $portraitName,
            'landscape' => $landscapeName,
        ];
        $item->save();

        // Create related video so it passes scopeHasVideo
        $video = new Video();
        $video->item_id = $item->id;
        $video->video_type_seven_twenty = 1; // Link
        $video->seven_twenty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->video_type_three_sixty = 1;
        $video->three_sixty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->video_type_four_eighty = 1;
        $video->four_eighty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->video_type_thousand_eighty = 1;
        $video->thousand_eighty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->save();

        return true;
    }

    private function importTVShow($tmdbId, $categoryId, $apiKey, $portraitPath, $landscapePath)
    {
        $tvUrl = "https://api.themoviedb.org/3/tv/{$tmdbId}?api_key={$apiKey}";
        $creditsUrl = "https://api.themoviedb.org/3/tv/{$tmdbId}/credits?api_key={$apiKey}";
        $tagsUrl = "https://api.themoviedb.org/3/tv/{$tmdbId}/keywords?api_key={$apiKey}";

        $tvData = json_decode(CurlRequest::curlContent($tvUrl));
        $creditsData = json_decode(CurlRequest::curlContent($creditsUrl));
        $tagsData = json_decode(CurlRequest::curlContent($tagsUrl));

        if (!isset($tvData->id)) {
            return false;
        }

        $casts = [];
        if (isset($creditsData->cast)) {
            foreach (array_slice($creditsData->cast, 0, 5) as $actor) {
                $casts[] = $actor->name;
            }
        }

        $directors = [];
        if (isset($tvData->created_by)) {
            foreach ($tvData->created_by as $creator) {
                $directors[] = $creator->name;
            }
        }

        $producers = [];
        if (isset($creditsData->crew)) {
            foreach ($creditsData->crew as $crew) {
                if ($crew->job === 'Producer') {
                    $producers[] = $crew->name;
                }
            }
        }

        $genres = [];
        if (isset($tvData->genres)) {
            foreach ($tvData->genres as $genre) {
                $genres[] = $genre->name;
            }
        }

        $languages = [];
        if (isset($tvData->spoken_languages)) {
            foreach ($tvData->spoken_languages as $lang) {
                $languages[] = $lang->name;
            }
        }

        $tags = [];
        if (isset($tagsData->results)) {
            foreach (array_slice($tagsData->results, 0, 10) as $keyword) {
                $tags[] = $keyword->name;
            }
        }

        $item = new Item();
        $item->category_id = $categoryId;
        $item->slug = Str::slug(($tvData->name ?? $tvData->original_name) . '-' . time() . getTrx(5));
        $item->title = $tvData->name ?? $tvData->original_name;
        $item->preview_text = $tvData->tagline ?? substr($tvData->overview, 0, 100);
        $item->description = $tvData->overview;
        $item->item_type = 2; // TV Series
        $item->status = 1;
        $item->single = 0;
        $item->trending = 1;
        $item->featured = 1;
        $item->ratings = $tvData->vote_average ?? 0;
        $item->view = rand(100, 1000);
        $item->tags = implode(',', $tags) ?: 'Action,Drama';
        
        $item->team = (object)[
            'director' => implode(',', $directors) ?: 'Unknown',
            'producer' => implode(',', $producers) ?: 'Unknown',
            'casts' => implode(',', $casts) ?: 'Unknown',
            'genres' => implode(',', $genres) ?: 'Drama',
            'language' => implode(',', $languages) ?: 'English',
        ];

        // Download posters
        $portraitName = uniqid() . '.jpg';
        $landscapeName = uniqid() . '.jpg';
        
        if (isset($tvData->poster_path)) {
            $this->downloadImage("https://image.tmdb.org/t/p/w500{$tvData->poster_path}", $portraitPath . $portraitName);
        }
        if (isset($tvData->backdrop_path)) {
            $this->downloadImage("https://image.tmdb.org/t/p/original{$tvData->backdrop_path}", $landscapePath . $landscapeName);
        }

        $item->image = (object)[
            'portrait' => $portraitName,
            'landscape' => $landscapeName,
        ];
        $item->save();

        // Create related Episode
        $episode = new Episode();
        $episode->item_id = $item->id;
        $episode->title = 'Episode 1';
        $episode->image = $portraitName;
        $episode->status = 1;
        $episode->save();

        // Create related video for Episode
        $video = new Video();
        $video->episode_id = $episode->id;
        $video->video_type_seven_twenty = 1; // Link
        $video->seven_twenty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->video_type_three_sixty = 1;
        $video->three_sixty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->video_type_four_eighty = 1;
        $video->four_eighty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->video_type_thousand_eighty = 1;
        $video->thousand_eighty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->save();

        return true;
    }

    private function handleImdbSync($limit, $type, $movieCategoryId, $seriesCategoryId, $portraitPath, $landscapePath)
    {
        // Sync Movies
        if ($type === 'movie' || $type === 'all') {
            $this->info('Fetching movies from IMDb API...');
            $count = 0;
            $pageToken = '';
            
            while ($count < $limit) {
                $url = "https://api.imdbapi.dev/titles?types=MOVIE&sortBy=SORT_BY_POPULARITY&sortOrder=ASC&minVoteCount=5000";
                if ($pageToken) {
                    $url .= "&pageToken=" . urlencode($pageToken);
                }
                
                $response = CurlRequest::curlContent($url);
                $data = json_decode($response);
                
                if (!isset($data->titles) || empty($data->titles)) {
                    $this->warn('No more movies found or API error.');
                    break;
                }
                
                foreach ($data->titles as $result) {
                    if ($count >= $limit) break;
                    
                    $title = $result->primaryTitle ?? $result->originalTitle;
                    $exists = Item::where('title', $title)->exists();
                    
                    if (!$exists) {
                        $this->info("Importing IMDb movie ({$count}/{$limit}): {$title} ({$result->id})");
                        if ($this->importMovieFromImdb($result->id, $movieCategoryId, $portraitPath, $landscapePath)) {
                            $count++;
                        }
                    } else {
                        $this->info("Skipped (already exists): {$title}");
                    }
                }
                
                if (isset($data->nextPageToken) && !empty($data->nextPageToken)) {
                    $pageToken = $data->nextPageToken;
                } else {
                    break;
                }
            }
            $this->info("Successfully imported {$count} new movies from IMDb.");
        }

        // Sync TV Series
        if ($type === 'tv' || $type === 'all') {
            $this->info('Fetching TV shows from IMDb API...');
            $count = 0;
            $pageToken = '';
            
            while ($count < $limit) {
                $url = "https://api.imdbapi.dev/titles?types=TV_SERIES&sortBy=SORT_BY_POPULARITY&sortOrder=ASC&minVoteCount=2000";
                if ($pageToken) {
                    $url .= "&pageToken=" . urlencode($pageToken);
                }
                
                $response = CurlRequest::curlContent($url);
                $data = json_decode($response);
                
                if (!isset($data->titles) || empty($data->titles)) {
                    $this->warn('No more TV shows found or API error.');
                    break;
                }
                
                foreach ($data->titles as $result) {
                    if ($count >= $limit) break;
                    
                    $title = $result->primaryTitle ?? $result->originalTitle;
                    $exists = Item::where('title', $title)->exists();
                    
                    if (!$exists) {
                        $this->info("Importing IMDb TV series ({$count}/{$limit}): {$title} ({$result->id})");
                        if ($this->importTVShowFromImdb($result->id, $seriesCategoryId, $portraitPath, $landscapePath)) {
                            $count++;
                        }
                    } else {
                        $this->info("Skipped (already exists): {$title}");
                    }
                }
                
                if (isset($data->nextPageToken) && !empty($data->nextPageToken)) {
                    $pageToken = $data->nextPageToken;
                } else {
                    break;
                }
            }
            $this->info("Successfully imported {$count} new TV shows from IMDb.");
        }

        $this->info('IMDb Sync process completed!');
        return Command::SUCCESS;
    }


    private function importMovieFromImdb($imdbId, $categoryId, $portraitPath, $landscapePath)
    {
        $movieUrl = "https://api.imdbapi.dev/titles/{$imdbId}";
        $response = CurlRequest::curlContent($movieUrl);
        $imdbData = json_decode($response);

        if (!isset($imdbData->id)) {
            return false;
        }

        $casts = [];
        if (isset($imdbData->stars)) {
            foreach (array_slice($imdbData->stars, 0, 5) as $actor) {
                $casts[] = $actor->displayName;
            }
        }

        $directors = [];
        if (isset($imdbData->directors)) {
            foreach ($imdbData->directors as $dir) {
                $directors[] = $dir->displayName;
            }
        }

        $writers = [];
        if (isset($imdbData->writers)) {
            foreach ($imdbData->writers as $writer) {
                $writers[] = $writer->displayName;
            }
        }

        $genres = $imdbData->genres ?? [];

        $languages = [];
        if (isset($imdbData->spokenLanguages)) {
            foreach ($imdbData->spokenLanguages as $lang) {
                $languages[] = $lang->name;
            }
        }

        $item = new Item();
        $item->category_id = $categoryId;
        $item->imdb_id = $imdbId;
        $item->slug = Str::slug(($imdbData->primaryTitle) . '-' . time() . getTrx(5));
        $item->title = $imdbData->primaryTitle;
        $item->preview_text = $imdbData->plot ? explode('.', $imdbData->plot)[0] . '.' : 'No tagline available.';
        $item->description = $imdbData->plot ?? '';
        $item->item_type = 1; // Movie
        $item->status = 1;
        $item->single = 1;
        $item->trending = 1;
        $item->featured = 1;
        $item->ratings = @$imdbData->rating->aggregateRating ?? 0;
        $item->view = rand(100, 1000);
        $item->tags = implode(',', $genres) ?: 'Action,Adventure';
        
        $item->team = (object)[
            'director' => implode(',', $directors) ?: 'Unknown',
            'producer' => implode(',', $writers) ?: 'Unknown',
            'casts' => implode(',', $casts) ?: 'Unknown',
            'genres' => implode(',', $genres) ?: 'Action',
            'language' => implode(',', $languages) ?: 'English',
        ];

        $portraitName = uniqid() . '.jpg';
        $landscapeName = uniqid() . '.jpg';
        $hasImage = false;
        
        if (isset($imdbData->primaryImage->url)) {
            $imageUrl = $imdbData->primaryImage->url;
            $downloadedPortrait = $this->downloadImage($imageUrl, $portraitPath . $portraitName);
            $downloadedLandscape = $this->downloadImage($imageUrl, $landscapePath . $landscapeName);
            if ($downloadedPortrait && $downloadedLandscape) {
                $hasImage = true;
            }
        }

        if (!$hasImage) {
            $portraitName = 'default.jpg';
            $landscapeName = 'default.jpg';
        }

        // AI Enrichment Fallback during Import
        $general = gs();
        if (@$general->ai_enrich && (empty($item->description) || empty($item->team->casts) || $item->team->casts == 'Unknown')) {
            \App\Services\AiService::log("AI enriching metadata for \"{$item->title}\" during import...");
            $aiData = \App\Services\AiService::generateMetadata($item->title, 'movie');
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
                \App\Services\AiService::log("AI successfully enriched metadata for \"{$item->title}\".");
            } else {
                \App\Services\AiService::log("AI enrichment failed for \"{$item->title}\".");
            }
        }

        $item->image = (object)[
            'portrait' => $portraitName,
            'landscape' => $landscapeName,
        ];
        $item->save();

        $video = new Video();
        $video->item_id = $item->id;
        $video->video_type_seven_twenty = 1;
        $video->seven_twenty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->video_type_three_sixty = 1;
        $video->three_sixty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->video_type_four_eighty = 1;
        $video->four_eighty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->video_type_thousand_eighty = 1;
        $video->thousand_eighty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->save();

        return true;
    }

    private function importTVShowFromImdb($imdbId, $categoryId, $portraitPath, $landscapePath)
    {
        $tvUrl = "https://api.imdbapi.dev/titles/{$imdbId}";
        $response = CurlRequest::curlContent($tvUrl);
        $imdbData = json_decode($response);

        if (!isset($imdbData->id)) {
            return false;
        }

        $casts = [];
        if (isset($imdbData->stars)) {
            foreach (array_slice($imdbData->stars, 0, 5) as $actor) {
                $casts[] = $actor->displayName;
            }
        }

        $directors = [];
        if (isset($imdbData->directors)) {
            foreach ($imdbData->directors as $dir) {
                $directors[] = $dir->displayName;
            }
        }

        $writers = [];
        if (isset($imdbData->writers)) {
            foreach ($imdbData->writers as $writer) {
                $writers[] = $writer->displayName;
            }
        }

        $genres = $imdbData->genres ?? [];

        $languages = [];
        if (isset($imdbData->spokenLanguages)) {
            foreach ($imdbData->spokenLanguages as $lang) {
                $languages[] = $lang->name;
            }
        }

        $item = new Item();
        $item->category_id = $categoryId;
        $item->imdb_id = $imdbId;
        $item->slug = Str::slug(($imdbData->primaryTitle) . '-' . time() . getTrx(5));
        $item->title = $imdbData->primaryTitle;
        $item->preview_text = $imdbData->plot ? explode('.', $imdbData->plot)[0] . '.' : 'No tagline available.';
        $item->description = $imdbData->plot ?? '';
        $item->item_type = 2; // TV Series
        $item->status = 1;
        $item->single = 0;
        $item->trending = 1;
        $item->featured = 1;
        $item->ratings = @$imdbData->rating->aggregateRating ?? 0;
        $item->view = rand(100, 1000);
        $item->tags = implode(',', $genres) ?: 'Action,Drama';
        
        $item->team = (object)[
            'director' => implode(',', $directors) ?: 'Unknown',
            'producer' => implode(',', $writers) ?: 'Unknown',
            'casts' => implode(',', $casts) ?: 'Unknown',
            'genres' => implode(',', $genres) ?: 'Drama',
            'language' => implode(',', $languages) ?: 'English',
        ];

        $portraitName = uniqid() . '.jpg';
        $landscapeName = uniqid() . '.jpg';
        $hasImage = false;
        
        if (isset($imdbData->primaryImage->url)) {
            $imageUrl = $imdbData->primaryImage->url;
            $downloadedPortrait = $this->downloadImage($imageUrl, $portraitPath . $portraitName);
            $downloadedLandscape = $this->downloadImage($imageUrl, $landscapePath . $landscapeName);
            if ($downloadedPortrait && $downloadedLandscape) {
                $hasImage = true;
            }
        }

        if (!$hasImage) {
            $portraitName = 'default.jpg';
            $landscapeName = 'default.jpg';
        }

        // AI Enrichment Fallback during Import
        $general = gs();
        if (@$general->ai_enrich && (empty($item->description) || empty($item->team->casts) || $item->team->casts == 'Unknown')) {
            \App\Services\AiService::log("AI enriching metadata for \"{$item->title}\" during TV show import...");
            $aiData = \App\Services\AiService::generateMetadata($item->title, 'tv');
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
                \App\Services\AiService::log("AI successfully enriched metadata for \"{$item->title}\".");
            } else {
                \App\Services\AiService::log("AI enrichment failed for \"{$item->title}\".");
            }
        }

        $item->image = (object)[
            'portrait' => $portraitName,
            'landscape' => $landscapeName,
        ];
        $item->save();

        $episode = new Episode();
        $episode->item_id = $item->id;
        $episode->title = 'Episode 1';
        $episode->image = $portraitName;
        $episode->status = 1;
        $episode->save();

        $video = new Video();
        $video->episode_id = $episode->id;
        $video->video_type_seven_twenty = 1;
        $video->seven_twenty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->video_type_three_sixty = 1;
        $video->three_sixty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->video_type_four_eighty = 1;
        $video->four_eighty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->video_type_thousand_eighty = 1;
        $video->thousand_eighty_video = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';
        $video->save();

        return true;
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
}
