<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AffiliateProduct;

class AffiliateRedirectController extends Controller
{
    public function redirect(string $identifier)
    {
        $product = AffiliateProduct::where('status', true)
            ->where(function ($q) use ($identifier) {
                if (is_numeric($identifier)) {
                    $q->where('id', (int)$identifier)->orWhere('slug', $identifier);
                } else {
                    $q->where('slug', $identifier);
                }
            })
            ->firstOrFail();

        $product->increment('click_count');

        $targetUrl = $product->getTargetUrlForCountry();

        return redirect()->away($targetUrl, 302, [
            'Referrer-Policy' => 'no-referrer',
            'X-Robots-Tag'    => 'noindex, nofollow',
        ]);
    }
}
