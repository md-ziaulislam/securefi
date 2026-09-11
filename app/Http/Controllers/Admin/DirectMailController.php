<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CampaignMail;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DirectMailController extends Controller
{
    public function index(Request $request)
    {
        $templates        = EmailTemplate::orderBy('name')->get();
        $subscribersCount = NewsletterSubscriber::active()->count();
        $usersCount       = User::count();

        // Pre-fill parameters if arriving from subscriber table or link
        $prefillEmail = $request->query('to', '');
        $prefillName  = $request->query('name', '');
        $prefillIds   = $request->query('subscriber_ids', '');
        $prefillMode  = 'single';

        if (!empty($prefillIds)) {
            $prefillMode = 'selected_subscribers';
        } elseif (!empty($prefillEmail)) {
            $prefillMode = 'single';
        }

        return view('admin.email.compose', compact(
            'templates',
            'subscribersCount',
            'usersCount',
            'prefillEmail',
            'prefillName',
            'prefillIds',
            'prefillMode'
        ));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'target_type'          => 'required|in:single,custom_list,selected_subscribers,active_subscribers,registered_users',
            'recipient_email'      => 'nullable|required_if:target_type,single|email|max:200',
            'recipient_name'       => 'nullable|string|max:100',
            'custom_emails'        => 'nullable|required_if:target_type,custom_list|string',
            'subscriber_ids'       => 'nullable|string',
            'subject'              => 'required|string|max:250',
            'template_id'          => 'nullable|exists:email_templates,id',
            'body'                 => 'nullable|string',
            'reply_to'             => 'nullable|email|max:150',
        ]);

        if (empty($validated['template_id']) && empty(trim($validated['body'] ?? ''))) {
            return back()->withInput()->withErrors(['body' => 'Please provide an email body or select an email template.']);
        }

        // Apply SMTP Config
        SmtpController::applySmtpConfig();

        // Resolve recipients list: array of ['email' => ..., 'name' => ..., 'subscriber' => ...]
        $recipients = $this->resolveRecipients($validated);

        if (empty($recipients)) {
            return back()->withInput()->withErrors(['recipients' => 'No valid recipient email addresses found for the selected audience.']);
        }

        $template    = !empty($validated['template_id']) ? EmailTemplate::find($validated['template_id']) : null;
        $sentCount   = 0;
        $failedCount = 0;

        foreach ($recipients as $item) {
            $email = $item['email'];
            $name  = $item['name'] ?? explode('@', $email)[0];
            $sub   = $item['subscriber'] ?? null;

            try {
                $vars = [
                    'name'            => $name,
                    'email'           => $email,
                    'site_name'       => Setting::get('site_name', 'SecuroFi.Tech'),
                    'site_url'        => config('app.url'),
                    'unsubscribe_url' => $sub ? $sub->unsubscribeUrl() : config('app.url'),
                ];

                $body = $template ? $template->render($vars) : $this->renderCustomBody($validated['body'] ?? '', $vars);

                Mail::to($email)->send(
                    new CampaignMail(
                        emailSubject: $validated['subject'],
                        renderedBody: $body,
                        subscriber: $sub,
                        replyToEmail: $validated['reply_to'] ?? null,
                        recipientName: $name
                    )
                );

                EmailLog::logDelivery(
                    email: $email,
                    name: $name,
                    subject: $validated['subject'],
                    body: $body,
                    type: 'direct',
                    status: 'sent'
                );

                $sentCount++;
            } catch (\Throwable $e) {
                EmailLog::logDelivery(
                    email: $email,
                    name: $name,
                    subject: $validated['subject'],
                    body: $validated['body'] ?? '',
                    type: 'direct',
                    status: 'failed',
                    errorMessage: $e->getMessage()
                );

                $failedCount++;
            }
        }

        $message = "Email successfully sent to {$sentCount} recipient(s).";
        if ($failedCount > 0) {
            $message .= " ({$failedCount} failed - check Delivery Logs).";
        }

        return redirect()->route('admin.email.logs.index')->with('success', $message);
    }

    public function testSend(Request $request)
    {
        $validated = $request->validate([
            'test_email'  => 'required|email',
            'subject'     => 'required|string|max:250',
            'template_id' => 'nullable|exists:email_templates,id',
            'body'        => 'nullable|string',
            'reply_to'    => 'nullable|email',
        ]);

        SmtpController::applySmtpConfig();

        $template = !empty($validated['template_id']) ? EmailTemplate::find($validated['template_id']) : null;
        $name     = 'Administrator';
        $vars     = [
            'name'            => $name,
            'email'           => $validated['test_email'],
            'site_name'       => Setting::get('site_name', 'SecuroFi.Tech'),
            'site_url'        => config('app.url'),
            'unsubscribe_url' => config('app.url'),
        ];

        $body = $template ? $template->render($vars) : $this->renderCustomBody($validated['body'] ?? '', $vars);

        try {
            Mail::to($validated['test_email'])->send(
                new CampaignMail(
                    emailSubject: '[TEST] ' . $validated['subject'],
                    renderedBody: $body,
                    subscriber: null,
                    replyToEmail: $validated['reply_to'] ?? null,
                    recipientName: $name
                )
            );

            EmailLog::logDelivery(
                email: $validated['test_email'],
                name: $name,
                subject: '[TEST] ' . $validated['subject'],
                body: $body,
                type: 'test',
                status: 'sent'
            );

            return back()->with('success', "Test email sent to {$validated['test_email']} successfully! ✅");
        } catch (\Throwable $e) {
            EmailLog::logDelivery(
                email: $validated['test_email'],
                name: $name,
                subject: '[TEST] ' . $validated['subject'],
                body: $body,
                type: 'test',
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            return back()->withInput()->withErrors(['test_email' => 'Failed to send test email: ' . $e->getMessage()]);
        }
    }

    private function resolveRecipients(array $validated): array
    {
        $recipients = [];

        switch ($validated['target_type']) {
            case 'single':
                $recipients[] = [
                    'email'      => strtolower(trim($validated['recipient_email'])),
                    'name'       => $validated['recipient_name'] ?? null,
                    'subscriber' => NewsletterSubscriber::where('email', strtolower(trim($validated['recipient_email'])))->first(),
                ];
                break;

            case 'custom_list':
                $raw = preg_split('/[\r\n,;]+/', $validated['custom_emails'] ?? '');
                $seen = [];
                foreach ($raw as $str) {
                    $email = strtolower(trim($str));
                    if (filter_var($email, FILTER_VALIDATE_EMAIL) && !isset($seen[$email])) {
                        $seen[$email] = true;
                        $recipients[] = [
                            'email'      => $email,
                            'name'       => explode('@', $email)[0],
                            'subscriber' => NewsletterSubscriber::where('email', $email)->first(),
                        ];
                    }
                }
                break;

            case 'selected_subscribers':
                $ids = array_filter(explode(',', $validated['subscriber_ids'] ?? ''));
                if (!empty($ids)) {
                    $subs = NewsletterSubscriber::whereIn('id', $ids)->get();
                    foreach ($subs as $sub) {
                        $recipients[] = [
                            'email'      => $sub->email,
                            'name'       => $sub->name ?? explode('@', $sub->email)[0],
                            'subscriber' => $sub,
                        ];
                    }
                }
                break;

            case 'active_subscribers':
                $subs = NewsletterSubscriber::active()->get();
                foreach ($subs as $sub) {
                    $recipients[] = [
                        'email'      => $sub->email,
                        'name'       => $sub->name ?? explode('@', $sub->email)[0],
                        'subscriber' => $sub,
                    ];
                }
                break;

            case 'registered_users':
                $users = User::all();
                foreach ($users as $user) {
                    $recipients[] = [
                        'email'      => $user->email,
                        'name'       => $user->name,
                        'subscriber' => NewsletterSubscriber::where('email', $user->email)->first(),
                    ];
                }
                break;
        }

        return $recipients;
    }

    private function renderCustomBody(string $body, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $body = str_replace(['{{' . $key . '}}', '{{ ' . $key . ' }}'], (string)$value, $body);
        }
        return $body;
    }
}
