<?php

namespace App\Services;

use App\Models\AffiliateProduct;
use App\Models\Setting;

class ShortcodeParser
{
    /**
     * Parse all supported shortcodes in article content.
     */
    public static function parse(string $content): string
    {
        $hasAffiliate = false;

        // 1. Single Product: [affiliate_product id="123"] or [affiliate_product slug="nordvpn"]
        $content = preg_replace_callback('/\[affiliate_product\s+(?:id=[\'"]?(\d+)[\'"]?|slug=[\'"]?([a-zA-Z0-9\-_]+)[\'"]?)\]/i', function ($matches) use (&$hasAffiliate) {
            $hasAffiliate = true;
            $identifier = !empty($matches[1]) ? $matches[1] : ($matches[2] ?? null);
            if (!$identifier) return '';

            $product = AffiliateProduct::where('status', true)
                ->where(function ($q) use ($identifier) {
                    if (is_numeric($identifier)) {
                        $q->where('id', (int)$identifier)->orWhere('slug', $identifier);
                    } else {
                        $q->where('slug', $identifier);
                    }
                })->first();

            if (!$product) {
                return '';
            }

            return self::renderSingleProductCard($product);
        }, $content);

        // 2. Comparison Table: [affiliate_comparison ids="1,2,3"]
        $content = preg_replace_callback('/\[affiliate_comparison\s+ids=[\'"]?([0-9,\s]+)[\'"]?\]/i', function ($matches) use (&$hasAffiliate) {
            $hasAffiliate = true;
            $ids = array_map('trim', explode(',', $matches[1]));
            $products = AffiliateProduct::whereIn('id', $ids)->where('status', true)->get();
            if ($products->isEmpty()) {
                return '';
            }

            $cards = '';
            foreach ($products as $idx => $p) {
                $badgeText = $p->badge_text ?: (($idx === 0) ? 'Top Choice' : 'Alternative');
                $badge = '<span class="text-[10px] font-mono uppercase bg-primary text-secondary px-2 py-0.5 tracking-wider">'.e($badgeText).'</span>';
                $rating = $p->rating ? '<span class="text-xs font-bold text-amber-600 ml-1.5">★ '.number_format($p->rating, 1).'</span>' : '';
                $price = $p->price ? '<div class="text-lg font-mono font-bold text-primary my-2">'.e($p->price).'</div>' : '';
                $img = $p->image ? '<img src="'.e($p->image).'" alt="'.e($p->name).'" class="w-full h-32 object-cover border-b border-neutral/20 mb-4" loading="lazy" />' : '';
                $btn = $p->button_text ?: 'View Offer';

                $cards .= '
                <div class="swiss-card p-5 flex flex-col justify-between bg-secondary">
                    <div>
                        <div class="mb-3 flex items-center justify-between">'.$badge.$rating.'</div>
                        '.$img.'
                        <h4 class="font-bold text-lg text-primary leading-snug">'.e($p->name).'</h4>
                        '.$price.'
                        <p class="text-xs text-primary/70 my-3 leading-relaxed">'.e($p->description).'</p>
                    </div>
                    <div class="pt-4 border-t border-neutral/20 mt-2">
                        <a href="'.e($p->cloakedUrl()).'" target="_blank" rel="'.e($p->rel_type).'" class="btn-primary w-full text-center text-xs py-2">
                            '.e($btn).'
                        </a>
                    </div>
                </div>';
            }

            return '
            <div class="my-8">
                <div class="text-xs font-mono uppercase tracking-widest text-neutral mb-3">Head-to-Head Verified Comparison</div>
                <div class="grid grid-cols-1 md:grid-cols-'.min(count($products), 3).' gap-4">
                    '.$cards.'
                </div>
            </div>';
        }, $content);

        // 3. Product List: [affiliate_list ids="1,2,3"]
        $content = preg_replace_callback('/\[affiliate_list\s+ids=[\'"]?([0-9,\s]+)[\'"]?\]/i', function ($matches) use (&$hasAffiliate) {
            $hasAffiliate = true;
            $ids = array_map('trim', explode(',', $matches[1]));
            $products = AffiliateProduct::whereIn('id', $ids)->where('status', true)->get();
            if ($products->isEmpty()) {
                return '';
            }

            $items = '';
            foreach ($products as $idx => $p) {
                $img = $p->image ? '<img src="'.e($p->image).'" alt="'.e($p->name).'" class="w-20 h-20 object-cover border border-neutral/20 shrink-0" loading="lazy" />' : '';
                $price = $p->price ? '<span class="text-xs font-mono font-bold text-primary">'.e($p->price).'</span>' : '';
                $rating = $p->rating ? '<span class="text-[11px] font-bold text-amber-600">★ '.number_format($p->rating, 1).'</span>' : '';
                $btn = $p->button_text ?: 'Visit Site →';

                $items .= '
                <div class="p-4 bg-secondary border border-neutral/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        '.$img.'
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] font-mono text-neutral">#0'.($idx + 1).'</span>
                                <h4 class="font-bold text-base text-primary">'.e($p->name).'</h4>
                                '.$rating.'
                            </div>
                            <p class="text-xs text-neutral mt-1">'.e($p->description).'</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 shrink-0 w-full sm:w-auto justify-between sm:justify-end">
                        '.$price.'
                        <a href="'.e($p->cloakedUrl()).'" target="_blank" rel="'.e($p->rel_type).'" class="btn-primary text-xs py-1.5 px-4">
                            '.e($btn).'
                        </a>
                    </div>
                </div>';
            }

            return '
            <div class="my-8 space-y-3">
                <div class="text-xs font-mono uppercase tracking-widest text-neutral mb-2">Recommended Solutions List</div>
                '.$items.'
            </div>';
        }, $content);

        // Auto-inject disclosure if affiliate shortcodes were present and auto-inject is active
        if ($hasAffiliate && Setting::get('affiliate_auto_inject_disclosure', '1') === '1') {
            $disclosureText = Setting::get('affiliate_disclosure_text', 'SecuroFi.Tech is reader-supported. When you buy through links on our site, we may earn an affiliate commission at no extra cost to you.');
            $disclosureHtml = '
            <aside class="mb-6 p-3.5 bg-[#F8F8F6] border-l-2 border-primary text-xs text-neutral leading-relaxed">
                <span class="font-semibold text-primary font-mono uppercase tracking-wider text-[11px]">Affiliate Transparency:</span> '.e($disclosureText).'
            </aside>';
            $content = $disclosureHtml . $content;
        }

        return $content;
    }

    public static function renderSingleProductCard(AffiliateProduct $product): string
    {
        $img = $product->image ? '<img src="'.e($product->image).'" alt="'.e($product->name).'" class="w-full sm:w-48 h-36 object-cover border border-neutral/20 shrink-0" loading="lazy" />' : '';
        $price = $product->price ? '<div class="text-sm font-mono font-semibold uppercase tracking-wider text-surface mt-1">'.e($product->price).'</div>' : '';
        $badge = $product->badge_text ?: 'Verified Recommendation';
        $rating = $product->rating ? '<span class="text-xs font-bold text-amber-600 ml-2">★ '.number_format($product->rating, 1).' / 5.0</span>' : '';
        $btn = $product->button_text ?: 'Check Details & Pricing';

        return '
        <div class="my-8 p-6 bg-[#F8F8F6] border border-neutral/30 rounded-none flex flex-col sm:flex-row gap-6 items-center">
            '.$img.'
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span class="text-[11px] font-mono uppercase tracking-widest px-2 py-0.5 bg-primary text-secondary font-bold">'.e($badge).'</span>
                    <span class="text-xs text-neutral font-mono">via '.e($product->source_site ?? 'SecuroFi Verified').'</span>
                    '.$rating.'
                </div>
                <h3 class="text-xl font-bold text-primary mb-2">'.e($product->name).'</h3>
                '.$price.'
                <p class="text-sm text-primary/80 mt-2 mb-4 leading-relaxed font-sans">'.e($product->description).'</p>
                <a href="'.e($product->cloakedUrl()).'" target="_blank" rel="'.e($product->rel_type).'" class="btn-primary inline-flex items-center gap-2 font-mono text-xs px-5 py-2.5">
                    <span>'.e($btn).'</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>';
    }

    /**
     * Render auto-injected recommendation box for a specific category or featured product.
     */
    public static function renderCategoryAffiliateBox(?int $categoryId = null): string
    {
        $query = AffiliateProduct::where('status', true);
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $product = $query->latest('is_featured')->first();
        if (!$product) {
            $product = AffiliateProduct::where('status', true)->latest('is_featured')->first();
        }

        if (!$product) {
            return '';
        }

        return self::renderSingleProductCard($product);
    }
}
