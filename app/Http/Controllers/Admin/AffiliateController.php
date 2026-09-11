<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    public function index(Request $request)
    {
        $products = AffiliateProduct::with('category')->orderByDesc('click_count')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        $editingProduct = null;
        if ($request->filled('edit')) {
            $editingProduct = AffiliateProduct::find($request->edit);
        }

        $disclosureText = Setting::get('affiliate_disclosure_text', 'SecuroFi.Tech is reader-supported. When you buy through links on our site, we may earn an affiliate commission at no extra cost to you.');
        $autoInject = Setting::get('affiliate_auto_inject_disclosure', '1');

        // Analytics KPIs
        $totalProducts = $products->count();
        $activeCount   = $products->where('status', true)->count();
        $totalClicks   = $products->sum('click_count');

        return view('admin.affiliates.index', compact(
            'products',
            'categories',
            'editingProduct',
            'disclosureText',
            'autoInject',
            'totalProducts',
            'activeCount',
            'totalClicks'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:150',
            'slug'          => 'nullable|string|max:150|unique:affiliate_products,slug',
            'image'         => 'nullable|string|max:500',
            'image_file'    => 'nullable|image|max:4096',
            'price'         => 'nullable|string|max:100',
            'rating'        => 'nullable|numeric|min:1|max:5',
            'badge_text'    => 'nullable|string|max:100',
            'button_text'   => 'nullable|string|max:100',
            'category_id'   => 'nullable|exists:categories,id',
            'description'   => 'nullable|string|max:1000',
            'affiliate_url'          => 'required|url|max:1000',
            'country_links'          => 'nullable|array',
            'country_links.*'        => 'nullable|url|max:1000',
            'fallback_affiliate_url' => 'nullable|url|max:1000',
            'source_site'            => 'nullable|string|max:100',
            'rel_type'               => 'nullable|string|max:100',
            'status'                 => 'nullable|boolean',
            'is_featured'            => 'nullable|boolean',
        ]);

        $image = $validated['image'] ?? null;
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('uploads/affiliates', 'public');
            $image = Storage::url($path);
        }

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        // Clean empty country links
        $countryLinks = [];
        if (!empty($validated['country_links']) && is_array($validated['country_links'])) {
            foreach ($validated['country_links'] as $cc => $url) {
                if (!empty(trim($url))) {
                    $countryLinks[strtoupper(trim($cc))] = trim($url);
                }
            }
        }

        AffiliateProduct::create([
            'name'                   => $validated['name'],
            'slug'                   => $slug,
            'image'                  => $image,
            'price'                  => $validated['price'] ?? null,
            'rating'                 => $validated['rating'] ?? 5.0,
            'badge_text'             => $validated['badge_text'] ?? null,
            'button_text'            => $validated['button_text'] ?? 'Claim Deal & Check Price',
            'category_id'            => $validated['category_id'] ?? null,
            'description'            => $validated['description'] ?? null,
            'affiliate_url'          => $validated['affiliate_url'],
            'country_links'          => !empty($countryLinks) ? $countryLinks : null,
            'fallback_affiliate_url' => !empty($validated['fallback_affiliate_url']) ? trim($validated['fallback_affiliate_url']) : null,
            'source_site'            => $validated['source_site'] ?? 'Direct Partner',
            'rel_type'               => $validated['rel_type'] ?? 'sponsored nofollow',
            'status'                 => $request->has('status'),
            'is_featured'            => $request->has('is_featured'),
            'click_count'            => 0,
        ]);

        return redirect()->route('admin.affiliates.index')->with('success', 'Affiliate product created successfully.');
    }

    public function update(Request $request, AffiliateProduct $affiliate)
    {
        $validated = $request->validate([
            'name'                   => 'required|string|max:150',
            'slug'                   => 'nullable|string|max:150|unique:affiliate_products,slug,' . $affiliate->id,
            'image'                  => 'nullable|string|max:500',
            'image_file'             => 'nullable|image|max:4096',
            'price'                  => 'nullable|string|max:100',
            'rating'                 => 'nullable|numeric|min:1|max:5',
            'badge_text'             => 'nullable|string|max:100',
            'button_text'            => 'nullable|string|max:100',
            'category_id'            => 'nullable|exists:categories,id',
            'description'            => 'nullable|string|max:1000',
            'affiliate_url'          => 'required|url|max:1000',
            'country_links'          => 'nullable|array',
            'country_links.*'        => 'nullable|url|max:1000',
            'fallback_affiliate_url' => 'nullable|url|max:1000',
            'source_site'            => 'nullable|string|max:100',
            'rel_type'               => 'nullable|string|max:100',
            'status'                 => 'nullable|boolean',
            'is_featured'            => 'nullable|boolean',
        ]);

        $image = $affiliate->image;
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('uploads/affiliates', 'public');
            $image = Storage::url($path);
        } elseif (!empty($validated['image'])) {
            $image = $validated['image'];
        }

        $slug = !empty($validated['slug']) ? Str::slug($validated['slug']) : ($affiliate->slug ?: Str::slug($validated['name']));

        // Clean empty country links
        $countryLinks = [];
        if (!empty($validated['country_links']) && is_array($validated['country_links'])) {
            foreach ($validated['country_links'] as $cc => $url) {
                if (!empty(trim($url))) {
                    $countryLinks[strtoupper(trim($cc))] = trim($url);
                }
            }
        }

        $affiliate->update([
            'name'                   => $validated['name'],
            'slug'                   => $slug,
            'image'                  => $image,
            'price'                  => $validated['price'] ?? null,
            'rating'                 => $validated['rating'] ?? 5.0,
            'badge_text'             => $validated['badge_text'] ?? null,
            'button_text'            => $validated['button_text'] ?? 'Claim Deal & Check Price',
            'category_id'            => $validated['category_id'] ?? null,
            'description'            => $validated['description'] ?? null,
            'affiliate_url'          => $validated['affiliate_url'],
            'country_links'          => !empty($countryLinks) ? $countryLinks : null,
            'fallback_affiliate_url' => !empty($validated['fallback_affiliate_url']) ? trim($validated['fallback_affiliate_url']) : null,
            'source_site'            => $validated['source_site'] ?? 'Direct Partner',
            'rel_type'               => $validated['rel_type'] ?? 'sponsored nofollow',
            'status'                 => $request->has('status'),
            'is_featured'            => $request->has('is_featured'),
        ]);

        return redirect()->route('admin.affiliates.index')->with('success', 'Affiliate product updated successfully.');
    }

    public function toggle(AffiliateProduct $affiliate)
    {
        $affiliate->update(['status' => !$affiliate->status]);
        $statusText = $affiliate->status ? 'activated' : 'paused';
        return back()->with('success', "Affiliate product \"{$affiliate->name}\" {$statusText}.");
    }

    public function resetClicks(AffiliateProduct $affiliate)
    {
        $affiliate->update(['click_count' => 0]);
        return back()->with('success', "Click metrics reset for \"{$affiliate->name}\".");
    }

    public function destroy(AffiliateProduct $affiliate)
    {
        $affiliate->delete();
        return redirect()->route('admin.affiliates.index')->with('success', 'Affiliate product deleted.');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'affiliate_disclosure_text'       => 'required|string|max:1000',
            'affiliate_auto_inject_disclosure' => 'nullable|boolean',
        ]);

        Setting::set('affiliate_disclosure_text', $validated['affiliate_disclosure_text'], 'affiliate');
        Setting::set('affiliate_auto_inject_disclosure', $request->has('affiliate_auto_inject_disclosure') ? '1' : '0', 'affiliate');

        return redirect()->route('admin.affiliates.index')->with('success', 'Affiliate compliance settings saved.');
    }
}
