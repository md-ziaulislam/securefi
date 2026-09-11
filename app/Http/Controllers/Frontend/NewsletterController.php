<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Services\CaptchaService;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request, CaptchaService $captcha)
    {
        // Captcha validation (if enabled for newsletter form)
        if ($captcha->isEnabledForForm('newsletter')) {
            $token = $request->input($captcha->tokenFieldName());
            if (!$captcha->verify($token, $request->ip())) {
                return back()
                    ->withInput()
                    ->withErrors(['captcha' => 'Security verification failed. Please complete the CAPTCHA and try again.']);
            }
        }

        $request->validate([
            'email' => 'required|email|max:150',
        ]);

        NewsletterSubscriber::firstOrCreate(
            ['email' => strtolower(trim($request->email))],
            [
                'status'        => 'subscribed',
                'subscribed_at' => now(),
            ]
        );

        return back()->with('newsletter_success', 'You have been subscribed to SecuroFi.Tech briefing dispatch.');
    }
}
