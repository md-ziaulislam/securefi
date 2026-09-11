<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\ContactSubmission;
use App\Models\NewsletterSubscriber;
use App\Models\VisitorLog;
use App\Services\UptimeService;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(UptimeService $uptimeService)
    {
        $stats = [
            'total_articles' => Article::count(),
            'published_articles' => Article::where('status', 'published')->count(),
            'total_categories' => Category::count(),
            'total_views' => Article::sum('view_count'),
            'total_subscribers' => NewsletterSubscriber::count(),
            'live_visitors' => VisitorLog::activeNow(5)->humans()->count(),
            'unread_contacts' => ContactSubmission::where('is_read', false)->count(),
        ];

        $recentArticles = Article::with(['category', 'author'])
            ->latest()
            ->take(5)
            ->get();

        $recentActivities = Activity::with('causer')
            ->latest()
            ->take(6)
            ->get();

        $uptimeSummary = null;
        if ($uptimeService->isWidgetEnabled()) {
            $uptimeData = $uptimeService->getUptimeData();
            $uptimeSummary = $uptimeData['summary'] ?? null;
        }

        return view('admin.dashboard', compact('stats', 'recentArticles', 'recentActivities', 'uptimeSummary'));
    }
}
