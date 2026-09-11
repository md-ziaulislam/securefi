<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SmtpController extends Controller
{
    public function index()
    {
        $smtp = [
            'smtp_host'       => Setting::get('smtp_host', ''),
            'smtp_port'       => Setting::get('smtp_port', '587'),
            'smtp_username'   => Setting::get('smtp_username', ''),
            'smtp_password'   => Setting::get('smtp_password', ''),
            'smtp_encryption' => Setting::get('smtp_encryption', 'tls'),
            'smtp_from_name'  => Setting::get('smtp_from_name', Setting::get('site_name', 'SecuroFi.Tech')),
            'smtp_from_email' => Setting::get('smtp_from_email', ''),
        ];

        return view('admin.email.smtp', compact('smtp'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'smtp_host'       => 'required|string|max:250',
            'smtp_port'       => 'required|integer|min:1|max:65535',
            'smtp_username'   => 'nullable|string|max:250',
            'smtp_password'   => 'nullable|string|max:500',
            'smtp_encryption' => 'required|in:tls,ssl,none',
            'smtp_from_name'  => 'required|string|max:100',
            'smtp_from_email' => 'required|email|max:150',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'smtp');
        }

        return back()->with('success', 'SMTP settings saved successfully.');
    }

    public function test(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        // Apply SMTP settings at runtime
        $this->applySmtpConfig();

        try {
            Mail::raw(
                'This is a test email from ' . Setting::get('site_name', 'SecuroFi.Tech') . ' to confirm SMTP is working correctly.',
                function ($message) use ($request) {
                    $message->to($request->test_email)
                            ->subject('SMTP Test — ' . Setting::get('site_name', 'SecuroFi.Tech'));
                }
            );
            return back()->with('success', "Test email sent to {$request->test_email} successfully! ✅");
        } catch (\Throwable $e) {
            return back()->withErrors(['smtp' => 'SMTP Error: ' . $e->getMessage()]);
        }
    }

    public static function applySmtpConfig(): void
    {
        $host       = Setting::get('smtp_host');
        $port       = Setting::get('smtp_port', '587');
        $username   = Setting::get('smtp_username');
        $password   = Setting::get('smtp_password');
        $encryption = Setting::get('smtp_encryption', 'tls');
        $fromEmail  = Setting::get('smtp_from_email');
        $fromName   = Setting::get('smtp_from_name', Setting::get('site_name', 'SecuroFi.Tech'));

        if ($host && $fromEmail) {
            Config::set('mail.mailer', 'smtp');
            Config::set('mail.mailers.smtp.host', $host);
            Config::set('mail.mailers.smtp.port', (int) $port);
            Config::set('mail.mailers.smtp.username', $username);
            Config::set('mail.mailers.smtp.password', $password);
            Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
            Config::set('mail.from.address', $fromEmail);
            Config::set('mail.from.name', $fromName);
        }
    }
}
