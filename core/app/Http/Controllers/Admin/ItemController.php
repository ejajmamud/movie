<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\CurlRequest;
use App\Lib\MultiVideoUploader;
use App\Lib\VideoUploader;
use App\Models\Episode;
use App\Models\Item;
use App\Models\Reel;
use App\Models\Subtitle;
use App\Models\Video;
use App\Models\VideoReport;
use App\Models\VideoResolution;
use App\Rules\FileTypeValidate;
use App\Traits\ItemUpload;
use Carbon\Carbon;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller {

    use ItemUpload;

    public function items() {
        return $this->renderItemsView("Play Video Items");
    }

    public function singleItems() {
        return $this->renderItemsView("Single Video Items", 'singleItems');
    }

    public function episodeItems() {
        return $this->renderItemsView("Episode Video Items", 'episodeItems');
    }

    public function trailerItems() {
        return $this->renderItemsView("Trailer Video Items", 'trailerItems');
    }

    public function rentItems() {
        return $this->renderItemsView("Rent Video Items", 'rentItems');
    }

    private function renderItemsView($pageTitle, $scope = null) {
        $items = $this->itemsData($scope);
        return view('admin.item.index', compact('pageTitle', 'items'));
    }

    private function itemsData($scope = null) {
        $query = Item::with('category', 'sub_category', 'video');
        if ($scope) {
            $query = $query->$scope();
        }
        return $query->searchable(['title', 'category:name'])->filter(['version', 'item_type', 'featured', 'category_id'])->orderBy('id', 'desc')->paginate(getPaginate());
    }

    public function uploadVideo($id) {
        $item  = Item::findOrFail($id);
        $video = $item->video;

        if ($video) {
            $notify[] = ['error', 'Already video exist'];
            return back()->withNotify($notify);
        }

        $pageTitle = "Upload video to: " . $item->title;
        $prevUrl   = route('admin.item.index');
        $route     = route('admin.item.upload.video', $item->id);
        $episodeId = 0;
        return view('admin.item.video.update', compact('item', 'pageTitle', 'video', 'prevUrl', 'route', 'episodeId'));
    }

    public function upload(Request $request, $id) {
        $item = Item::where('id', $id)->first();
        if (!$item) {
            return response()->json(['error' => 'Item not found']);
        }

        $video = $item->video;

        if ($video) {
            $sevenTwentyLink  = 'nullable';
            $sevenTwentyVideo = 'nullable';
        } else {
            $sevenTwentyLink  = 'required_if:video_type_seven_twenty,0';
            $sevenTwentyVideo = 'required_if:video_type_seven_twenty,1';
        }

        ini_set('memory_limit', '-1');
        $validator = Validator::make($request->all(), [
            'video_type_three_sixty'     => 'required',
            'three_sixty_link'           => 'nullable',
            'three_sixty_video'          => ['nullable', new FileTypeValidate(['mp4', 'mkv', '3gp'])],

            'video_type_four_eighty'     => 'required',
            'four_eighty_link'           => 'nullable',
            'four_eighty_video'          => ['nullable', new FileTypeValidate(['mp4', 'mkv', '3gp'])],

            'video_type_seven_twenty'    => 'required',
            'seven_twenty_link'          => "$sevenTwentyLink",
            'seven_twenty_video'         => ["$sevenTwentyVideo", new FileTypeValidate(['mp4', 'mkv', '3gp'])],

            'video_type_thousand_eighty' => ' ',
            'thousand_eighty_link'       => 'nullable',
            'thousand_eighty_video'      => ['nullable', new FileTypeValidate(['mp4', 'mkv', '3gp'])],
        ], [
            'video_type_three_sixty'     => 'Video file 360P type is required',
            'three_sixty_link'           => 'Video file 360P link is required',
            'three_sixty_video'          => 'Video file 360P video is required',
            'video_type_four_eighty'     => 'Video file 480P type is required',
            'four_eighty_link'           => 'Video file 480P link is required',
            'four_eighty_video'          => 'Video file 480P video is required',
            'video_type_seven_twenty'    => 'Video file 720P type is required',
            'seven_twenty_link'          => 'Video file 720P link is required',
            'seven_twenty_video'         => 'Video file 720P video is required',
            'video_type_thousand_eighty' => 'Video file 1080P type is required',
            'thousand_eighty_link'       => 'Video file 1080P link is required',
            'thousand_eighty_video'      => 'Video file 1080P video is required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $sizeValidation = MultiVideoUploader::checkSizeValidation();
        if ($sizeValidation['error']) {
            return response()->json(['error' => $sizeValidation['message']]);
        }

        $uploadThreeSixty = MultiVideoUploader::multiQualityVideoUpload($video, 'three_sixty', $request);
        if ($uploadThreeSixty['error']) {
            return response()->json(['error' => $uploadThreeSixty['message']]);
        }

        $uploadFourEighty = MultiVideoUploader::multiQualityVideoUpload($video, 'four_eighty', $request);
        if ($uploadFourEighty['error']) {
            return response()->json(['error' => $uploadFourEighty['message']]);
        }

        $uploadSevenTwenty = MultiVideoUploader::multiQualityVideoUpload($video, 'seven_twenty', $request);
        if ($uploadSevenTwenty['error']) {
            return response()->json(['error' => $uploadSevenTwenty['message']]);
        }

        $uploadThousandEighty = MultiVideoUploader::multiQualityVideoUpload($video, 'thousand_eighty', $request);
        if ($uploadThousandEighty['error']) {
            return response()->json(['error' => $uploadThousandEighty['message']]);
        }

        if (!$video) {
            $video          = new Video();
            $video->item_id = $item->id;
        }

        $video->video_type_three_sixty     = @$request->video_type_three_sixty;
        $video->video_type_four_eighty     = @$request->video_type_four_eighty;
        $video->video_type_seven_twenty    = @$request->video_type_seven_twenty;
        $video->video_type_thousand_eighty = @$request->video_type_thousand_eighty;

        $video->three_sixty_video     = @$uploadThreeSixty['three_sixty_video'];
        $video->four_eighty_video     = @$uploadFourEighty['four_eighty_video'];
        $video->seven_twenty_video    = @$uploadSevenTwenty['seven_twenty_video'];
        $video->thousand_eighty_video = @$uploadThousandEighty['thousand_eighty_video'];

        $video->server_three_sixty     = @$uploadThreeSixty['server'] ?? 0;
        $video->server_four_eighty     = @$uploadFourEighty['server'] ?? 0;
        $video->server_seven_twenty    = @$uploadSevenTwenty['server'] ?? 0;
        $video->server_thousand_eighty = @$uploadThousandEighty['server'] ?? 0;

        $video->save();
        return response()->json(['success' => 'Video uploaded successfully']);
    }

    public function updateVideo(Request $request, $id) {
        $item  = Item::findOrFail($id);
        $video = $item->video;

        if (!$video) {
            $notify[] = ['error', 'Video not found'];
            return back()->withNotify($notify);
        }

        $pageTitle   = "Update video of: " . $item->title;
        $posterImage = getImage(getFilePath('item_landscape') . @$item->image->landscape);
        $general     = gs();

        $videoFile['three_sixty']     = getVideoFile($video, 'three_sixty');
        $videoFile['four_eighty']     = getVideoFile($video, 'four_eighty');
        $videoFile['seven_twenty']    = getVideoFile($video, 'seven_twenty');
        $videoFile['thousand_eighty'] = getVideoFile($video, 'thousand_eighty');

        $route     = route('admin.item.upload.video', @$item->id);
        $prevUrl   = route('admin.item.index');
        $episodeId = 0;
        return view('admin.item.video.update', compact('item', 'pageTitle', 'video', 'videoFile', 'posterImage', 'prevUrl', 'route', 'episodeId'));
    }

    public function uploadChunk(Request $request) {
        $validator = Validator::make($request->all(), [
            'chunk'        => 'required|file',
            'chunk_index'  => 'required|integer',
            'total_chunks' => 'required|integer',
            'file_name'    => 'required|string',
            'upload_id'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        if (!gs('ffmpeg')) {
            return response()->json(['error' => 'Upload with FFMPEG disabled']);
        }

        $chunkDir = storage_path("app/chunks/{$request->upload_id}");
        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }

        $chunkPath = $chunkDir . '/' . $request->chunk_index;
        file_put_contents($chunkPath, file_get_contents($request->file('chunk')));
        return response()->json(['success' => 'chunk received']);
    }

    public function chunkToVideo(Request $request) {
        $uploadId      = $request->upload_id;
        $fileName      = $request->file_name;
        $finalDir      = storage_path("app/videos");
        $chunkDir      = storage_path("app/chunks/{$uploadId}");
        $finalFilePath = storage_path("app/videos/{$fileName}");

        if (!is_dir($finalDir)) {
            mkdir($finalDir, 0755, true);
        }

        $chunkFiles = array_diff(scandir($chunkDir), ['.', '..']);

        // Sort files based on chunk index
        usort($chunkFiles, function ($a, $b) {
            $aIndex = (int) filter_var($a, FILTER_SANITIZE_NUMBER_INT);
            $bIndex = (int) filter_var($b, FILTER_SANITIZE_NUMBER_INT);
            return $aIndex - $bIndex;
        });

        $out = fopen($finalFilePath, 'ab');
        foreach ($chunkFiles as $chunkFile) {
            fwrite($out, file_get_contents($chunkDir . '/' . $chunkFile));
        }
        fclose($out);
        File::deleteDirectory($chunkDir);

        return response()->json([
            'video_path' => $finalFilePath,
        ]);
    }

    public function getVideoResolutions(Request $request) {
        $fullPath = $request->video_path;
        try {
            $ffprobe = FFProbe::create();
        } catch (\Exception $e) {
            return response()->json(['error' => 'FFmpeg init failed']);
        }

        $videoStream = $ffprobe->streams($fullPath)->videos()->first();
        $width       = $videoStream->get('width');
        $height      = $videoStream->get('height');
        $resolution  = $width . 'x' . $height;
        $resolutions = $this->getResolutions($resolution);
        if (!$resolutions) {
            return response()->json(['error' => 'Unavailable resolutions']);
        }
        return response()->json([
            'resolutions' => $resolutions,
        ]);
    }

    public function multiResolution(Request $request) {
        $resolution    = $request->resolution;
        $key           = $request->key;
        $fullPath      = $request->video_path;
        $itemId        = $request->item_id;
        $episodeId     = $request->episode_id;
        $lastKey       = $request->last_key;
        $allResolution = $request->all_resolution;

        try {
            $ffmpeg = FFMpeg::create([
                'timeout'        => 3600,
                'ffmpeg.threads' => 12,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'FFmpeg init failed']);
        }

        try {
            $outputDir = storage_path('app/multi-video');
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            [$newWidth, $newHeight] = explode('x', $resolution);
            $uuid                   = uniqid();
            $rdName                 = "{$uuid}.mp4";
            $outputFilePath         = $outputDir . '/' . $rdName;
            $video                  = $ffmpeg->open($fullPath);
            $video->filters()->resize(new Dimension($newWidth, $newHeight));
            $format = new X264();
            $format->setAudioCodec('aac');
            $video->save($format, $outputFilePath);

            $quality = match ($key) {
                '360p'  => 'three_sixty_video',
                '480p'  => 'four_eighty_video',
                '720p'  => 'seven_twenty_video',
                default => 'thousand_eighty_video',
            };

            $qualityPartExplode = explode('_', $quality);
            $qualityPartMain    = $qualityPartExplode[0] . '_' . $qualityPartExplode[1];
            $serverName         = 'server_' . $qualityPartMain;
            $videoTypeName      = 'video_type_' . $qualityPartMain;

            if ($episodeId) {
                $itemVideo = Video::where('episode_id', $episodeId)->first();
            } else {
                $itemVideo = Video::where('item_id', $itemId)->first();
            }

            if (!$itemVideo) {
                $itemVideo = new Video();
            }

            $uploadedFile = new UploadedFile(
                $outputFilePath,
                basename($outputFilePath),
                mime_content_type($outputFilePath),
                null,
                true
            );

            $request = Request::create('/', 'POST', [], [], [
                $quality => $uploadedFile,
            ]);

            $uploadResult = MultiVideoUploader::multiQualityVideoUpload($itemVideo, $qualityPartMain, $request);
            if ($uploadResult['error']) {
                return response()->json(['error' => $uploadResult['error']]);
            }

            $setServerName = gs('server');
            $currentServer = 0;

            if ($setServerName == 'current') {
                $currentServer = 0;
            } else if ($setServerName == 'custom-ftp') {
                $currentServer = 1;
            } else if ($setServerName == 'wasabi') {
                $currentServer = 2;
            } else {
                $currentServer = 3;
            }

            if ($episodeId) {
                $itemVideo->episode_id = $episodeId;
                $redirect              = route('admin.item.episode.updateVideo', $episodeId);
            } else {
                $itemVideo->item_id = $itemId;
                $redirect           = route('admin.item.updateVideo', $itemId);
            }

            $itemVideo->$quality       = $uploadResult[$quality];
            $itemVideo->$serverName    = $currentServer;
            $itemVideo->$videoTypeName = 1;
            $itemVideo->save();

            File::delete($outputFilePath);
            if ($key == $lastKey) {
                $allQualities = [
                    '360p'    => 'three_sixty',
                    '480p'    => 'four_eighty',
                    '720p'    => 'seven_twenty',
                    'default' => 'thousand_eighty',
                ];
                $missingKeys = array_diff(array_keys($allQualities), $allResolution);

                $missingQualities = [];
                foreach ($missingKeys as $key => $missKey) {
                    $missingQualities[] = $allQualities[$missKey];
                }

                foreach ($missingQualities ?? [] as $missQuality) {
                    $missingVideoType = 'video_type_' . $missQuality;
                    $missingVideo     = $missQuality . '_video';

                    $removeFile          = new VideoUploader();
                    $removeFile->oldFile = $itemVideo->$missingVideo;
                    $removeFile->removeOldFile();

                    $itemVideo->$missingVideoType = 1;
                    $itemVideo->$missingVideo     = null;
                    $itemVideo->save();
                }

                File::delete($fullPath);
                return response()->json(['redirect' => $redirect]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'The request time has expired']);
        }
    }

    private function getResolutions($resolution) {
        [$inputWidth, $inputHeight] = explode('x', $resolution);

        $resolutions = VideoResolution::where('width', '<=', $inputWidth)->where('height', '<=', $inputHeight)->orderBy('width', 'desc')->orderBy('height', 'desc')->get();

        $availableResolutions = [];
        foreach ($resolutions as $res) {
            $key                        = $res->resolution_label;
            $availableResolutions[$key] = "{$res->width}x{$res->height}";
        }
        return $availableResolutions;
    }

    public function itemList(Request $request) {
        $items = Item::hasVideo();

        if (request()->search) {
            $items = $items->where('title', 'like', "%$request->search%");
        }
        $items = $items->latest()->paginate(getPaginate());

        foreach ($items as $item) {
            $response[] = [
                'id'   => $item->id,
                'text' => $item->title,
            ];
        }

        return $response ?? [];
    }

    public function itemFetch(Request $request) {
        $validate = Validator::make($request->all(), [
            'id'        => 'required|string',
            'item_type' => 'required|integer|in:1,2',
        ]);
        if ($validate->fails()) {
            return response()->json(['error' => $validate->errors()]);
        }
        $general  = gs();
        $itemType = $request->item_type == 1 ? 'movie' : 'tv';
        $id       = trim($request->id);

        if (preg_match('/^tt\d+/i', $id)) {
            $imdbUrl = 'https://api.imdbapi.dev/titles/' . $id;
            $imdbResponse = CurlRequest::curlContent($imdbUrl);
            $imdbData = json_decode($imdbResponse);

            if (isset($imdbData->id)) {
                $genreList = [];
                if (isset($imdbData->genres)) {
                    foreach ($imdbData->genres as $genre) {
                        $genreList[] = (object)['name' => $genre];
                    }
                }

                $languageList = [];
                if (isset($imdbData->spokenLanguages)) {
                    foreach ($imdbData->spokenLanguages as $lang) {
                        $languageList[] = (object)['name' => $lang->name];
                    }
                }

                $data = (object)[
                    'title' => $imdbData->primaryTitle,
                    'overview' => $imdbData->plot ?? '',
                    'vote_average' => @$imdbData->rating->aggregateRating ?? 0,
                    'tagline' => $imdbData->plot ? explode('.', $imdbData->plot)[0] . '.' : '',
                    'poster_path' => @$imdbData->primaryImage->url ?? '',
                    'backdrop_path' => @$imdbData->primaryImage->url ?? '',
                    'genres' => $genreList,
                    'spoken_languages' => $languageList,
                    'created_by' => [],
                ];

                $castList = [];
                if (isset($imdbData->stars)) {
                    foreach ($imdbData->stars as $star) {
                        $castList[] = (object)['name' => $star->displayName];
                    }
                }
                $casts = (object)['cast' => $castList, 'crew' => []];

                if (isset($imdbData->directors)) {
                    foreach ($imdbData->directors as $dir) {
                        $casts->crew[] = (object)[
                            'name' => $dir->displayName,
                            'job' => 'Director',
                            'known_for_department' => 'Directing'
                        ];
                    }
                }
                if (isset($imdbData->writers)) {
                    foreach ($imdbData->writers as $writer) {
                        $casts->crew[] = (object)[
                            'name' => $writer->displayName,
                            'job' => 'Screenplay',
                            'known_for_department' => 'Directing'
                        ];
                    }
                }

                $tags = (object)['results' => $genreList];

                return response()->json([
                    'success' => true,
                    'data'    => $data,
                    'casts'   => $casts,
                    'tags'    => $tags,
                ]);
            }

            $findUrl = 'https://api.themoviedb.org/3/find/' . $id . '?api_key=' . $general->tmdb_api . '&external_source=imdb_id';
            $findResponse = CurlRequest::curlContent($findUrl);
            $findData = json_decode($findResponse);

            if ($request->item_type == 1) {
                $resolvedId = @$findData->movie_results[0]->id;
            } else {
                $resolvedId = @$findData->tv_results[0]->id;
            }

            if (!$resolvedId) {
                $resolvedId = @$findData->movie_results[0]->id ?? @$findData->tv_results[0]->id;
            }

            if (!$resolvedId) {
                return response()->json(['error' => 'The resource you requested could not be found on TMDB or IMDb API.']);
            }
            $id = $resolvedId;
        }

        $tmDbUrl  = 'https://api.themoviedb.org/3/' . $itemType . '/' . $id;
        $url      = $tmDbUrl . '?api_key=' . $general->tmdb_api;
        $castUrl  = $tmDbUrl . '/credits?api_key=' . $general->tmdb_api;
        $tags     = $tmDbUrl . '/keywords?api_key=' . $general->tmdb_api;

        $movieResponse = CurlRequest::curlContent($url);
        $castResponse  = CurlRequest::curlContent($castUrl);
        $tagsResponse  = CurlRequest::curlContent($tags);

        $data  = json_decode($movieResponse);
        $casts = json_decode($castResponse);
        $tags  = json_decode($tagsResponse);

        if (isset($data->success) && $data->success == false) {
            return response()->json(['error' => 'The resource you requested could not be found.']);
        }

        return response()->json([
            'success' => true,
            'data'    => $data,
            'casts'   => $casts,
            'tags'    => $tags,
        ]);
    }

    public function sendNotification($id) {
        $item = Item::where('status', Status::ENABLE)->findOrFail($id);
        $this->notifyToUsers($item);
        $notify[] = ['success', 'Notification send successfully'];
        return back()->withNotify($notify);
    }

    public function adsDuration($id, $episodeId = 0) {
        $pageTitle = 'Ads Configuration';
        $item      = Item::findOrFail($id);

        if ($item->item_type == 1 || $item->item_type == 3) {
            $episodeId = null;
            $video     = $item->video;
        } else {
            $episode   = $item->episodes()->where('id', $episodeId)->with('video')->first();
            $video     = $episode->video;
            $episodeId = $episode->id;
        }
        $general   = gs();
        $videoFile = getVideoFile($video, 'seven_twenty');
        return view('admin.item.video.ads', compact('pageTitle', 'item', 'video', 'videoFile', 'episodeId'));
    }

    public function adsDurationStore(Request $request, $id = 0, $episodeId = 0) {
        $request->validate([
            'ads_time'   => 'required|array',
            'ads_time.*' => 'required',
        ]);
        $item = Item::findOrFail($id);
        if ($item->item_type == 1 || $item->item_type == 3) {
            $video = $item->video;
        } else {
            $episode = $item->episodes()->with('video')->findOrFail($episodeId);
            $video   = $episode->video;
        }
        for ($i = 0; $i < count($request->ads_time); $i++) {
            $arr = explode(':', $request->ads_time[$i]);
            if (count($arr) === 3) {
                $second[] = $arr[0] * 3600 + $arr[1] * 60 + $arr[2];
            } else {
                $second[] = $arr[0] * 60 + $arr[1];
            }
        }
        $video->seconds  = $second;
        $video->ads_time = $request->ads_time;
        $video->save();

        $notify[] = ['success', 'Ads time added successfully'];
        return back()->withNotify($notify);
    }

    public function subtitles($id, $videoId = 0) {
        $itemId    = 0;
        $episodeId = 0;
        if ($videoId == 0) {
            $item       = Item::with('video')->findOrFail($id);
            $videoId    = $item->video->id;
            $columnName = 'item_id';
            $itemId     = $item->id;
        } else {
            $item       = Episode::with('video')->findOrFail($id);
            $columnName = 'episode_id';
            $episodeId  = $item->id;
        }
        $subtitles = Subtitle::where($columnName, $id)->where('video_id', $videoId)->paginate(getPaginate());
        $pageTitle = 'Subtitles for - ' . $item->title;
        return view('admin.item.video.subtitles', compact('pageTitle', 'item', 'subtitles', 'videoId', 'episodeId', 'itemId'));
    }

    public function subtitleStore(Request $request, $itemId, $episodeId, $videoId, $id = 0) {
        $validate = $id ? 'nullable' : 'required';
        $request->validate([
            'language' => 'required|string|max:40',
            'code'     => 'required|string|max:40',
            'file'     => [$validate, new FileTypeValidate(['vtt'])],
        ]);

        if ($id) {
            $subtitle     = Subtitle::findOrFail($id);
            $oldFile      = $subtitle->file;
            $notification = 'Subtitle updated successfully';
        } else {
            $subtitle     = new Subtitle();
            $notification = 'Subtitle created successfully';
            $oldFile      = null;
        }

        $subtitle->item_id    = $itemId;
        $subtitle->episode_id = $episodeId;
        $subtitle->video_id   = $videoId;
        $subtitle->language   = $request->language;
        $subtitle->code       = strtolower($request->code);
        if ($request->file) {
            $subtitle->file = fileUploader($request->file, getFilePath('subtitle'), null, $oldFile);
        }
        $subtitle->save();
        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function subtitleDelete($id) {
        $subtitle = Subtitle::where('id', $id)->firstOrFail();
        fileManager()->removeFile(getFilePath('subtitle') . '/' . $subtitle->file);
        $subtitle->delete();
        $notify[] = ['success', 'Subtitle deleted successfully'];
        return back()->withNotify($notify);
    }

    public function report($id, $videoId = 0) {
        $startDate = now()->subMonth()->startOfDay()->toDateString();
        $endDate   = now()->endOfDay()->toDateString();

        if ($videoId == 0) {
            $item         = Item::with('videoReport')->findOrFail($id);
            $videoReports = $item->videoReport()->whereBetween('created_at', [$startDate, $endDate])->get();
            $title        = $item->title;
        } else {
            $episode      = Episode::with('item', 'videoReport')->findOrFail($videoId);
            $item         = $episode->item;
            $videoReports = $episode->videoReport()->whereBetween('created_at', [$startDate, $endDate])->get();
            $title        = $episode->title;
        }

        $reports = $videoReports->groupBy(function ($data) {
            return substr($data['created_at'], 0, 10);
        })->map(function ($group) {
            return count($group);
        });

        $totalViews = $videoReports->count();

        $allRegions = VideoReport::distinct('region_name')->pluck('region_name');
        $pageTitle  = 'Report - ' . $item->title;

        return view('admin.item.report', compact('pageTitle', 'reports', 'item', 'totalViews', 'title', 'allRegions', 'videoId'));
    }

    public function fetchViewReportData(Request $request) {
        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate   = Carbon::parse($request->end_date)->endOfDay();

        $dates = $this->getAllDates($startDate, $endDate);

        $mainQuery = VideoReport::where('created_at', '>=', $startDate)->where('created_at', '<=', $endDate);
        if ($request->episode_id == 0) {
            $videoReports = $mainQuery->where('item_id', $request->item_id);
            if ($request->search) {
                $videoReports = $videoReports->where('region_name', $request->search);
            }
        } else {
            $videoReports = $mainQuery->where('episode_id', $request->episode_id);
            if ($request->search) {
                $videoReports = $videoReports->where('region_name', $request->search);
            }
        }

        $videoReports = $videoReports->get();
        $totalViews   = $videoReports->count();

        $groupedReports = $videoReports->groupBy(function ($data) {
            return $data->created_at->format('Y-m-d');
        })->map(function ($group) {
            return $group->count();
        });

        $data = [];
        foreach ($dates as $date) {
            $data[] = [
                'created_on' => $date,
                'views'      => $groupedReports[$date] ?? 0,
            ];
        }

        return response()->json([
            'created_on' => collect($data)->pluck('created_on'),
            'data'       => [
                [
                    'name' => 'Total Views',
                    'data' => collect($data)->pluck('views'),
                ],
                'fetch_total_view' => $totalViews,
            ],
        ]);
    }

    private function getAllDates($startDate, $endDate) {
        $dates       = [];
        $currentDate = new \DateTime($startDate);
        $endDate     = new \DateTime($endDate);

        while ($currentDate <= $endDate) {
            $dates[] = $currentDate->format('Y-m-d');
            $currentDate->modify('+1 day');
        }

        return $dates;
    }

    public function import(Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:csv',
        ]);
        $file = $request->file('file');
        if (($handle = fopen($file, 'r')) !== false) {
            $headings = fgetcsv($handle);

            if (isset($headings[0])) {
                $headings[0] = preg_replace('/^\x{FEFF}/u', '', $headings[0]);
            }

            $versions = implode(',', [Status::FREE_VERSION, Status::PAID_VERSION, Status::RENT_VERSION]);
            $items    = [];
            $errors   = [];

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $row       = array_combine($headings, $data);
                $validator = Validator::make($row, [
                    'title'           => 'required|string|max:255',
                    'category_id'     => 'required|integer|exists:categories,id',
                    'sub_category_id' => 'nullable|integer|exists:sub_categories,id',
                    'preview_text'    => 'required|string|max:255',
                    'description'     => 'required|string',
                    'item_type'       => "required|in:1,2",
                    'view'            => 'nullable|integer',
                    'version'         => "nullable|required_if:item_type,==,1|in:$versions",
                    'ratings'         => 'required|numeric',
                    'rent_price'      => 'required_if:version,==,2|nullable|numeric|gte:0',
                    'rental_period'   => 'required_if:version,==,2|nullable|integer|gte:0',
                    'exclude_plan'    => 'required_if:version,==,2|nullable|in:0,1',
                ]);

                if ($validator->fails()) {
                    $errors[] = $validator->errors()->all();
                    continue;
                }

                $items[] = [
                    'category_id'     => @$row['category_id'],
                    'sub_category_id' => @$row['sub_category_id'],
                    'slug'            => @$row['slug'],
                    'title'           => @$row['title'],
                    'preview_text'    => @$row['preview_text'],
                    'description'     => @$row['description'],
                    'team'            => @$row['team'],
                    'image'           => @$row['image'],
                    'item_type'       => @$row['item_type'],
                    'status'          => @$row['status'],
                    'single'          => @$row['single'],
                    'trending'        => @$row['trending'],
                    'featured'        => @$row['featured'],
                    'version'         => @$row['version'],
                    'tags'            => @$row['tags'],
                    'ratings'         => @$row['ratings'],
                    'view'            => @$row['view'],
                    'is_trailer'      => @$row['is_trailer'],
                    'rent_price'      => @$row['rent_price'],
                    'rental_period'   => @$row['rental_period'],
                    'exclude_plan'    => @$row['exclude_plan'],
                    'created_at'      => @$row['created_at'],
                    'updated_at'      => @$row['updated_at'],
                ];
            }
            fclose($handle);
        }
        if (!blank($errors)) {
            foreach ($errors as $error) {
                $notify[] = ['error', implode(', ', $error)];
            }
            return back()->withNotify($notify);
        }
        if (!blank($items)) {
            Item::insert($items);
        }

        $notify[] = ['success', 'CSV data imported successfully!'];
        return back()->withNotify($notify);
    }

    public function reels() {
        $pageTitle = 'Reels Item';
        $reels     = Reel::searchable(['title'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.item.reels', compact('pageTitle', 'reels'));
    }

    public function reelStore(Request $request, $id = 0) {
        $videoValidate = $id ? 'nullable' : 'required';
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'video'       => [$videoValidate, new FileTypeValidate(['mp4'])],
        ]);

        if ($id) {
            $reel         = Reel::findOrFail($id);
            $notification = 'Reel updated successfully';
        } else {
            $reel         = new Reel();
            $notification = 'Reel created successfully';
        }
        $reel->title       = $request->title;
        $reel->description = $request->description;
        if ($request->hasFile('video')) {
            try {
                $reel->video = fileUploader($request->video, getFilePath('reels'), null, @$reel->video);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }
        $reel->save();
        $notify[] = ['success', $notification];
        return back()->withNotify($notify);
    }

    public function reelRemove($id) {
        $reel = Reel::findOrFail($id);
        fileManager()->removeFile(getFilePath('reels') . '/' . @$reel->video);
        $reel->delete();
        $notify[] = ['success', 'Reel deleted successfully'];
        return back()->withNotify($notify);
    }

    public function seo($id) {
        $key       = 'item';
        $data      = Item::findOrFail($id);
        $pageTitle = 'SEO Configuration: ' . $data->title;
        return view('admin.item.seo', compact('pageTitle', 'key', 'data'));
    }

    public function seoStore(Request $request, $id) {
        $request->validate([
            'image' => ['nullable', new FileTypeValidate(['jpeg', 'jpg', 'png'])],
        ]);

        $data  = Item::findOrFail($id);
        $image = @$data->seo_content->image;
        if ($request->hasFile('image')) {
            try {
                $path  = getFilePath('itemSeo');
                $image = fileUploader($request->image, $path, getFileSize('seo'), @$data->seo_content->image);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload the seo image'];
                return back()->withNotify($notify);
            }
        }
        $data->seo_content = [
            'image'              => $image,
            'description'        => $request->description,
            'social_title'       => $request->social_title,
            'social_description' => $request->social_description,
            'keywords'           => $request->keywords,
        ];
        $data->save();

        $notify[] = ['success', 'SEO content has been updated successfully'];
        return to_route('admin.item.seo', $data->id)->withNotify($notify);
    }
};
