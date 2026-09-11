<?php

namespace App\Services;

use App\Models\AdSlot;
use Illuminate\Support\HtmlString;

class AdService
{
    /**
     * Render an ad slot for a given position and optional category ID.
     */
    public static function render(string $position, ?int $categoryId = null): HtmlString
    {
        $slot = AdSlot::forPosition($position)->first();
        if (!$slot || !$slot->isLive()) {
            return new HtmlString('');
        }

        if (!$slot->appliesToCategory($categoryId)) {
            return new HtmlString('');
        }

        // Country Targeting (PRD-ADDNEW 5.2)
        $visitorCountry = \App\Services\GeoIpService::getCountryCode();
        if (!$slot->appliesToCountry($visitorCountry)) {
            // Check if fallback ad network code exists for non-targeted country
            if (!empty($slot->fallback_code)) {
                $slot->increment('impressions');
                return new HtmlString($slot->fallback_code);
            }
            return new HtmlString('');
        }

        // Increment impression count
        $slot->increment('impressions');

        $deviceClass = '';
        if ($slot->device === 'desktop') {
            $deviceClass = 'hidden md:block';
        } elseif ($slot->device === 'mobile') {
            $deviceClass = 'block md:hidden';
        }

        // Sticky Footer Placement
        if ($position === 'sticky_footer') {
            $inner = '';
            if ($slot->is_banner && !empty($slot->banner_image)) {
                $targetUrl = !empty($slot->target_url) ? route('ad.click', $slot->id) : '#';
                $inner = '<a href="'.e($targetUrl).'" target="_blank" rel="sponsored noopener" class="inline-block max-h-16">
                    <img src="'.e($slot->banner_image).'" alt="'.e($slot->name).'" class="max-h-16 max-w-full mx-auto object-contain" />
                </a>';
            } elseif (!empty($slot->code)) {
                $inner = $slot->code;
            } else {
                return new HtmlString('');
            }

            return new HtmlString('
            <div id="sticky-ad-footer" class="fixed bottom-0 inset-x-0 z-40 bg-[#111111]/95 text-white border-t border-white/20 p-2.5 shadow-2xl backdrop-blur-md '.$deviceClass.'">
                <div class="max-w-4xl mx-auto flex items-center justify-between gap-4">
                    <div class="flex-1 text-center overflow-hidden">
                        <span class="text-[9px] font-mono text-neutral-400 uppercase tracking-widest block mb-0.5">Advertisement</span>
                        '.$inner.'
                    </div>
                    <button onclick="document.getElementById(\'sticky-ad-footer\').remove()" class="text-neutral-400 hover:text-white px-2.5 py-1 text-base font-bold transition-colors" title="Dismiss">&times;</button>
                </div>
            </div>');
        }

        // Standard Banner Ad
        if ($slot->is_banner && !empty($slot->banner_image)) {
            $targetUrl = !empty($slot->target_url) ? route('ad.click', $slot->id) : '#';
            $html = '
            <div class="ad-slot ad-slot-'.$position.' my-6 text-center '.$deviceClass.'">
                <span class="text-[10px] font-mono text-neutral uppercase tracking-widest block mb-1">Advertisement</span>
                <a href="'.e($targetUrl).'" target="_blank" rel="sponsored noopener" class="inline-block border border-neutral/20 overflow-hidden hover:opacity-95 transition-opacity">
                    <img src="'.e($slot->banner_image).'" alt="'.e($slot->name).'" class="max-w-full h-auto mx-auto" loading="lazy" />
                </a>
            </div>';
            return new HtmlString($html);
        }

        // Custom Embed Code Ad (Google Ads, Carbon, Scripts)
        if (!empty($slot->code)) {
            return new HtmlString('
            <div class="ad-slot ad-slot-'.$position.' my-6 text-center '.$deviceClass.'">
                <span class="text-[10px] font-mono text-neutral uppercase tracking-widest block mb-1">Advertisement</span>
                '.$slot->code.'
            </div>');
        }

        return new HtmlString('');
    }
}
