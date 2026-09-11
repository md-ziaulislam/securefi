<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VisitorLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', '7d');

        $query = VisitorLog::query();

        if ($period === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($period === '7d') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7));
        } elseif ($period === '30d') {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        // Live Real-Time Pulse (Always computed on last 5 minutes)
        $activeVisitorsNow = VisitorLog::activeNow(5)->count();
        $activeHumansNow = VisitorLog::activeNow(5)->humans()->count();
        $activeBotsNow = $activeVisitorsNow - $activeHumansNow;

        // Period Totals
        $totalPageviews = (clone $query)->count();
        $uniqueSessions = (clone $query)->distinct('session_id')->count('session_id');
        $humanVisits = (clone $query)->where('is_bot', false)->count();
        $botVisits = (clone $query)->where('is_bot', true)->count();
        $returningCount = (clone $query)->where('is_returning', true)->count();

        // Breakdowns
        $topCountries = (clone $query)
            ->select('country', DB::raw('count(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('count')
            ->take(8)
            ->get();

        $topDevices = (clone $query)
            ->select('device_type', DB::raw('count(*) as count'))
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();

        $topBrowsers = (clone $query)
            ->select('browser', DB::raw('count(*) as count'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->take(6)
            ->get();

        $topOperatingSystems = (clone $query)
            ->select('os', DB::raw('count(*) as count'))
            ->whereNotNull('os')
            ->groupBy('os')
            ->orderByDesc('count')
            ->take(6)
            ->get();

        $topPages = (clone $query)
            ->select('current_page', DB::raw('count(*) as count'))
            ->whereNotNull('current_page')
            ->groupBy('current_page')
            ->orderByDesc('count')
            ->take(10)
            ->get();

        $topReferrers = (clone $query)
            ->select('referrer_source', DB::raw('count(*) as count'))
            ->whereNotNull('referrer_source')
            ->groupBy('referrer_source')
            ->orderByDesc('count')
            ->take(8)
            ->get();

        $recentBots = VisitorLog::where('is_bot', true)
            ->latest('id')
            ->take(10)
            ->get();

        // 7-day trend array
        $dailyTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $label = Carbon::now()->subDays($i)->format('M d');
            $count = VisitorLog::whereDate('created_at', $date)->count();
            $humans = VisitorLog::whereDate('created_at', $date)->where('is_bot', false)->count();
            $bots = $count - $humans;

            $dailyTrend[] = [
                'date' => $date,
                'label' => $label,
                'total' => $count,
                'humans' => $humans,
                'bots' => $bots,
            ];
        }

        // Live Feed Table (Latest 25)
        $recentStream = VisitorLog::latest('id')->paginate(25, ['*'], 'feed_page');

        return view('admin.analytics.index', compact(
            'period',
            'activeVisitorsNow',
            'activeHumansNow',
            'activeBotsNow',
            'totalPageviews',
            'uniqueSessions',
            'humanVisits',
            'botVisits',
            'returningCount',
            'topCountries',
            'topDevices',
            'topBrowsers',
            'topOperatingSystems',
            'topPages',
            'topReferrers',
            'recentBots',
            'dailyTrend',
            'recentStream'
        ));
    }

    /**
     * Live stats endpoint for AJAX polling ticker.
     */
    public function liveStats()
    {
        $activeNow = VisitorLog::activeNow(5)->count();
        $activeHumans = VisitorLog::activeNow(5)->humans()->count();
        $activeBots = $activeNow - $activeHumans;

        $recent = VisitorLog::latest('id')
            ->take(6)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'ip' => $log->ip_address,
                    'location' => ($log->city !== 'Unknown' && $log->city ? $log->city . ', ' : '') . $log->country,
                    'device' => $log->device_type,
                    'browser' => $log->browser,
                    'page' => $log->current_page,
                    'is_bot' => $log->is_bot,
                    'bot_name' => $log->bot_name,
                    'time_ago' => $log->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'active_now' => $activeNow,
            'active_humans' => $activeHumans,
            'active_bots' => $activeBots,
            'recent' => $recent,
        ]);
    }

    /**
     * Export Visitor Logs to CSV.
     */
    public function exportCsv(Request $request)
    {
        $filename = 'visitor_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $logs = VisitorLog::latest('id')->take(2000)->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID',
                'IP Address',
                'Session ID',
                'Country',
                'City',
                'Device Type',
                'OS',
                'Browser',
                'Referrer Source',
                'Current Page',
                'Is Bot',
                'Bot Name',
                'Is Returning',
                'Created At',
            ]);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->id,
                    $log->ip_address,
                    $log->session_id,
                    $log->country,
                    $log->city,
                    $log->device_type,
                    $log->os,
                    $log->browser,
                    $log->referrer_source,
                    $log->current_page,
                    $log->is_bot ? 'Yes' : 'No',
                    $log->bot_name,
                    $log->is_returning ? 'Yes' : 'No',
                    $log->created_at->toIso8601String(),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
