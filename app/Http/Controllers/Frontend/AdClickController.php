<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\AdSlot;
use Illuminate\Http\Request;

class AdClickController extends Controller
{
    public function click(AdSlot $ad)
    {
        $ad->increment('clicks');

        if (!empty($ad->target_url)) {
            return redirect()->away($ad->target_url, 302, [
                'Referrer-Policy' => 'no-referrer',
            ]);
        }

        return redirect()->route('home');
    }
}
