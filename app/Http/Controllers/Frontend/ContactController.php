<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use App\Services\CaptchaService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function submit(Request $request, CaptchaService $captcha)
    {
        // Spam honeypot
        if ($request->filled('website')) {
            return back()->with('success', 'Thank you. Your message has been received.');
        }

        // Captcha validation (if enabled for contact form)
        if ($captcha->isEnabledForForm('contact')) {
            $token = $request->input($captcha->tokenFieldName());
            if (!$captcha->verify($token, $request->ip())) {
                return back()
                    ->withInput()
                    ->withErrors(['captcha' => 'Security verification failed. Please complete the CAPTCHA and try again.']);
            }
        }

        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:150',
            'subject' => 'nullable|string|max:200',
            'message' => 'required|string|min:10|max:5000',
        ]);

        ContactSubmission::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'subject'      => $validated['subject'] ?? 'Website Inquiry',
            'message'      => $validated['message'],
            'ip_address'   => $request->ip(),
            'submitted_at' => now(),
        ]);

        return back()->with('success', 'Your message has been securely submitted. Our engineering team will review it shortly.');
    }
}
