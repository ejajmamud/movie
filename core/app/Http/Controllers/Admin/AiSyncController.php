<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\Item;
use App\Services\AiService;

class AiSyncController extends Controller
{
    public function index()
    {
        $pageTitle = 'AI IMDb Sync Manager';

        // Fetch statistics
        $stats = [
            'total_items' => Item::count(),
            'total_movies' => Item::where('item_type', 1)->count(),
            'total_tv' => Item::where('item_type', 2)->count(),
            'missing_posters' => Item::all()->filter(function ($item) {
                $portrait = @$item->image->portrait;
                $landscape = @$item->image->landscape;
                
                $portraitPath = base_path('../assets/images/item/portrait/' . $portrait);
                $landscapePath = base_path('../assets/images/item/landscape/' . $landscape);

                return !$portrait || $portrait === 'default.jpg' || !file_exists($portraitPath) || filesize($portraitPath) == 0 ||
                       !$landscape || $landscape === 'default.jpg' || !file_exists($landscapePath) || filesize($landscapePath) == 0;
            })->count(),
        ];

        // Fetch logs
        $logPath = storage_path('logs/ai_sync.log');
        $logs = '';
        if (file_exists($logPath)) {
            $lines = file($logPath);
            $lines = array_slice($lines, -150); // Get last 150 lines
            $logs = implode("", $lines);
        } else {
            $logs = "No sync logs generated yet. Trigger a sync or diagnostics to start tracking events.";
        }

        return view('admin.ai_sync.index', compact('pageTitle', 'stats', 'logs'));
    }

    public function progress()
    {
        $syncProgress = cache()->get('imdb_sync_progress', ['status' => 'idle']);
        $repairProgress = cache()->get('poster_repair_progress', ['status' => 'idle']);
        
        $logPath = storage_path('logs/ai_sync.log');
        $logs = '';
        if (file_exists($logPath)) {
            $lines = file($logPath);
            $lines = array_slice($lines, -150); // Get last 150 lines
            $logs = implode("", $lines);
        } else {
            $logs = "No sync logs generated yet. Trigger a sync or diagnostics to start tracking events.";
        }
        
        return response()->json([
            'sync' => $syncProgress,
            'repair' => $repairProgress,
            'logs' => $logs
        ]);
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'openrouter_key' => 'nullable|string',
            'ai_model' => 'required|string',
            'ai_enrich' => 'required|in:0,1',
            'auto_repair_posters' => 'required|in:0,1',
            'auto_sync' => 'required|in:0,1',
        ]);

        $general = gs();
        $general->openrouter_key = $request->openrouter_key;
        $general->ai_model = $request->ai_model;
        $general->ai_enrich = $request->ai_enrich;
        $general->auto_repair_posters = $request->auto_repair_posters;
        $general->auto_sync = $request->auto_sync;
        $general->save();

        $notify[] = ['success', 'AI Sync Settings updated successfully!'];
        return back()->withNotify($notify);
    }

    public function triggerSync(Request $request)
    {
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        session()->writeClose();

        $limit = $request->input('limit', 10);
        $type = $request->input('type', 'all');
        $library = $request->has('library');

        try {
            $params = [
                '--limit' => (int) $limit,
                '--type' => $type,
            ];
            if ($library) {
                $params['--library'] = true;
            }

            Artisan::call('movies:sync', $params);
            $output = Artisan::output();

            $notify[] = ['success', 'IMDb Sync completed successfully!'];
            return back()->withNotify($notify)->with('sync_output', $output);
        } catch (\Exception $e) {
            $notify[] = ['error', 'Sync failed: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function triggerRepair()
    {
        ini_set('max_execution_time', 0);
        set_time_limit(0);
        session()->writeClose();

        try {
            Artisan::call('movies:repair-posters');
            $output = Artisan::output();

            $notify[] = ['success', 'Poster diagnostics and repair completed successfully!'];
            return back()->withNotify($notify)->with('repair_output', $output);
        } catch (\Exception $e) {
            $notify[] = ['error', 'Repair failed: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function clearLogs()
    {
        $logPath = storage_path('logs/ai_sync.log');
        if (file_exists($logPath)) {
            @unlink($logPath);
        }
        $notify[] = ['success', 'Sync tracker logs cleared successfully!'];
        return back()->withNotify($notify);
    }
}
