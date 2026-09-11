<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdSlot;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdSlotController extends Controller
{
    public function index(Request $request)
    {
        $adSlots = AdSlot::orderBy('position')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $editingSlot = null;
        if ($request->filled('edit')) {
            $editingSlot = AdSlot::find($request->edit);
        }

        // Metrics & KPI calculations
        $totalSlots       = $adSlots->count();
        $activeSlots      = $adSlots->where('status', true)->count();
        $totalImpressions = $adSlots->sum('impressions');
        $totalClicks      = $adSlots->sum('clicks');
        $avgCtr           = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0.0;

        $allCountries    = \App\Services\GeoIpService::getAllCountries();
        $tier1Countries  = \App\Services\GeoIpService::getTier1CountryCodes();
        $euCountries     = \App\Services\GeoIpService::getEuCountryCodes();

        return view('admin.ads.index', compact(
            'adSlots',
            'categories',
            'editingSlot',
            'totalSlots',
            'activeSlots',
            'totalImpressions',
            'totalClicks',
            'avgCtr',
            'allCountries',
            'tier1Countries',
            'euCountries'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:100',
            'position'          => 'required|in:header,sidebar,in_article_top,in_article_middle,in_article_bottom,footer,sticky_footer,between_articles',
            'is_banner'         => 'nullable|boolean',
            'code'              => 'nullable|string',
            'fallback_code'     => 'nullable|string',
            'banner_image'      => 'nullable|string|max:500',
            'banner_image_file' => 'nullable|image|max:4096',
            'target_url'        => 'nullable|url|max:500',
            'category_ids'      => 'nullable|array',
            'category_ids.*'    => 'exists:categories,id',
            'device'            => 'required|in:all,desktop,mobile',
            'country_rule'      => 'required|in:all,include,exclude',
            'countries'         => 'nullable|array',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'status'            => 'nullable|boolean',
        ]);

        $bannerImage = $validated['banner_image'] ?? null;
        if ($request->hasFile('banner_image_file')) {
            $path = $request->file('banner_image_file')->store('uploads/ads', 'public');
            $bannerImage = Storage::url($path);
        }

        AdSlot::create([
            'name'          => $validated['name'],
            'position'      => $validated['position'],
            'is_banner'     => $request->has('is_banner'),
            'code'          => $validated['code'] ?? null,
            'fallback_code' => $validated['fallback_code'] ?? null,
            'banner_image'  => $bannerImage,
            'target_url'    => $validated['target_url'] ?? null,
            'category_ids'  => $validated['category_ids'] ?? null,
            'device'        => $validated['device'] ?? 'all',
            'country_rule'  => $validated['country_rule'] ?? 'all',
            'countries'     => $validated['countries'] ?? null,
            'start_date'    => $validated['start_date'] ?? null,
            'end_date'      => $validated['end_date'] ?? null,
            'status'        => $request->has('status'),
            'impressions'   => 0,
            'clicks'        => 0,
        ]);

        return redirect()->route('admin.ads.index')->with('success', 'Ad slot created successfully.');
    }

    public function update(Request $request, AdSlot $ad)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:100',
            'position'          => 'required|in:header,sidebar,in_article_top,in_article_middle,in_article_bottom,footer,sticky_footer,between_articles',
            'is_banner'         => 'nullable|boolean',
            'code'              => 'nullable|string',
            'fallback_code'     => 'nullable|string',
            'banner_image'      => 'nullable|string|max:500',
            'banner_image_file' => 'nullable|image|max:4096',
            'target_url'        => 'nullable|url|max:500',
            'category_ids'      => 'nullable|array',
            'category_ids.*'    => 'exists:categories,id',
            'device'            => 'required|in:all,desktop,mobile',
            'country_rule'      => 'required|in:all,include,exclude',
            'countries'         => 'nullable|array',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'status'            => 'nullable|boolean',
        ]);

        $bannerImage = $ad->banner_image;
        if ($request->hasFile('banner_image_file')) {
            $path = $request->file('banner_image_file')->store('uploads/ads', 'public');
            $bannerImage = Storage::url($path);
        } elseif (!empty($validated['banner_image'])) {
            $bannerImage = $validated['banner_image'];
        }

        $ad->update([
            'name'          => $validated['name'],
            'position'      => $validated['position'],
            'is_banner'     => $request->has('is_banner'),
            'code'          => $validated['code'] ?? null,
            'fallback_code' => $validated['fallback_code'] ?? null,
            'banner_image'  => $bannerImage,
            'target_url'    => $validated['target_url'] ?? null,
            'category_ids'  => $validated['category_ids'] ?? null,
            'device'        => $validated['device'] ?? 'all',
            'country_rule'  => $validated['country_rule'] ?? 'all',
            'countries'     => $validated['countries'] ?? null,
            'start_date'    => $validated['start_date'] ?? null,
            'end_date'      => $validated['end_date'] ?? null,
            'status'        => $request->has('status'),
        ]);

        return redirect()->route('admin.ads.index')->with('success', 'Ad slot updated successfully.');
    }

    public function toggle(AdSlot $ad)
    {
        $ad->update(['status' => !$ad->status]);
        $statusText = $ad->status ? 'activated' : 'paused';
        return back()->with('success', "Ad slot \"{$ad->name}\" {$statusText}.");
    }

    public function resetStats(AdSlot $ad)
    {
        $ad->update(['impressions' => 0, 'clicks' => 0]);
        return back()->with('success', "Impressions and click analytics reset for \"{$ad->name}\".");
    }

    public function destroy(AdSlot $ad)
    {
        $ad->delete();
        return redirect()->route('admin.ads.index')->with('success', 'Ad slot deleted.');
    }
}
