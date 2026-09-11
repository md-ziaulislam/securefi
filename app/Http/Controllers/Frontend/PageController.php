<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();
        $seoModel = $page;

        // Geo-fencing & Country Restriction Check (PRD-ADDNEW 5.1)
        $visitorCountry = \App\Services\GeoIpService::getCountryCode();
        if (!$page->isAccessibleInCountry($visitorCountry)) {
            return response()->view('frontend.restricted', [
                'title'            => $page->title,
                'visitorCountry'   => $visitorCountry,
                'fallbackMessage'  => $page->restriction_fallback_message,
                'localizedVariant' => null,
                'allVariants'      => null,
                'seoModel'         => $page,
            ], 200);
        }

        if ($page->template === 'contact') {
            return view('frontend.contact', compact('page', 'seoModel'));
        }

        if ($page->template === 'dev-info' || $slug === 'dev-info') {
            return view('frontend.dev-info', compact('page', 'seoModel'));
        }

        return view('frontend.page', compact('page', 'seoModel'));
    }
}
