<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * FeedController (Admin)
 *
 * Manages RSS/Atom feed settings: enable/disable, item count,
 * content mode, metadata customization, and live endpoint verification.
 */
class FeedController extends Controller
{
    public function index()
    {
        $settings = Setting::whereIn('group', ['feed', 'general', 'seo'])->pluck('value', 'key');

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $publishedCount = Article::published()->count();
        $lastPublished = Article::published()->latest('published_at')->first();
        $recentArticles = Article::with(['category', 'author'])
            ->published()
            ->latest('published_at')
            ->limit(5)
            ->get();

        return view('admin.feed.index', compact(
            'settings',
            'categories',
            'publishedCount',
            'lastPublished',
            'recentArticles'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'feed_enabled'       => 'nullable|in:0,1',
            'feed_items_count'   => 'required|integer|min:5|max:100',
            'feed_show_excerpt'  => 'nullable|in:0,1',
            'feed_show_fulltext' => 'nullable|in:0,1',
            'feed_copyright'     => 'nullable|string|max:255',
            'feed_title'         => 'nullable|string|max:150',
            'feed_description'   => 'nullable|string|max:500',
            'feed_language'      => 'nullable|string|max:20',
        ]);

        Setting::set('feed_enabled',       $request->has('feed_enabled') ? '1' : '0', 'feed');
        Setting::set('feed_items_count',   (string) $validated['feed_items_count'], 'feed');
        Setting::set('feed_show_excerpt',  $request->has('feed_show_excerpt') ? '1' : '0', 'feed');
        Setting::set('feed_show_fulltext', $request->has('feed_show_fulltext') ? '1' : '0', 'feed');
        Setting::set('feed_copyright',     $validated['feed_copyright'] ?? '', 'feed');
        Setting::set('feed_title',         $validated['feed_title'] ?? '', 'feed');
        Setting::set('feed_description',   $validated['feed_description'] ?? '', 'feed');
        Setting::set('feed_language',      $validated['feed_language'] ?? 'en-us', 'feed');

        if (function_exists('activity') && auth()->check()) {
            activity()
                ->causedBy(auth()->user())
                ->log('Updated RSS/Atom Feed settings.');
        }

        return back()->with('success', 'RSS & Atom Feed configuration successfully updated.');
    }
}
