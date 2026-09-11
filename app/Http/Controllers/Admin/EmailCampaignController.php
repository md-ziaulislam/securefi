<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CampaignMail;
use App\Models\EmailCampaign;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailCampaignController extends Controller
{
    public function index()
    {
        $campaigns = EmailCampaign::with('template')->latest()->get();
        return view('admin.email.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $templates        = EmailTemplate::orderBy('name')->get();
        $subscribers      = NewsletterSubscriber::active()->count();
        $totalSubscribers = NewsletterSubscriber::count();
        $usersCount       = User::count();

        return view('admin.email.campaigns.create', compact('templates', 'subscribers', 'totalSubscribers', 'usersCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:200',
            'subject'       => 'required|string|max:250',
            'template_id'   => 'nullable|exists:email_templates,id',
            'body'          => 'nullable|string',
            'audience'      => 'required|in:all,active,users,custom',
            'custom_emails' => 'nullable|required_if:audience,custom|string',
        ]);

        if (empty($validated['template_id']) && empty(trim($validated['body'] ?? ''))) {
            return back()->withInput()->withErrors(['body' => 'Please provide campaign HTML content or select a template.']);
        }

        // If custom audience, we can store custom emails in the body meta or an extra field if needed
        $campaignData = [
            'name'        => $validated['name'],
            'subject'     => $validated['subject'],
            'template_id' => $validated['template_id'],
            'body'        => $validated['body'],
            'audience'    => $validated['audience'],
            'status'      => 'draft',
        ];

        // If custom emails, save into campaign body with metadata comment if body is null
        if ($validated['audience'] === 'custom' && !empty($validated['custom_emails'])) {
            $campaignData['custom_recipients'] = $validated['custom_emails'];
        }

        $campaign = EmailCampaign::create($campaignData);

        return redirect()->route('admin.email.campaigns.index')
            ->with('success', "Campaign '{$campaign->name}' created and saved as draft.");
    }

    public function send(EmailCampaign $campaign)
    {
        if ($campaign->status === 'sent') {
            return back()->withErrors(['campaign' => 'This campaign has already been sent.']);
        }

        // Resolve recipients list: array of ['email' => ..., 'name' => ..., 'subscriber' => ...]
        $recipients = $this->resolveCampaignRecipients($campaign);

        if (empty($recipients)) {
            return back()->withErrors(['campaign' => 'No recipients found for this audience.']);
        }

        $campaign->update([
            'status'           => 'sending',
            'total_recipients' => count($recipients),
        ]);

        SmtpController::applySmtpConfig();

        $sentCount   = 0;
        $failedCount = 0;
        $template    = $campaign->template;

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

                $body = $template
                    ? $template->render($vars)
                    : $this->renderCustomBody($campaign->body ?? '', $vars);

                Mail::to($email)->send(
                    new CampaignMail(
                        emailSubject: $campaign->subject,
                        renderedBody: $body,
                        subscriber: $sub,
                        recipientName: $name
                    )
                );

                EmailLog::logDelivery(
                    email: $email,
                    name: $name,
                    subject: $campaign->subject,
                    body: $body,
                    type: 'campaign',
                    status: 'sent',
                    campaignId: $campaign->id
                );

                $sentCount++;
            } catch (\Throwable $e) {
                EmailLog::logDelivery(
                    email: $email,
                    name: $name,
                    subject: $campaign->subject,
                    body: $campaign->body ?? '',
                    type: 'campaign',
                    status: 'failed',
                    errorMessage: $e->getMessage(),
                    campaignId: $campaign->id
                );

                $failedCount++;
            }
        }

        $campaign->update([
            'status'       => $failedCount === count($recipients) ? 'failed' : 'sent',
            'sent_count'   => $sentCount,
            'failed_count' => $failedCount,
            'sent_at'      => now(),
        ]);

        return redirect()->route('admin.email.campaigns.index')
            ->with('success', "Campaign broadcast sent to {$sentCount} recipient(s). ({$failedCount} failed)");
    }

    public function testSend(Request $request, EmailCampaign $campaign)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        SmtpController::applySmtpConfig();

        $template = $campaign->template;
        $name     = 'Administrator';
        $vars     = [
            'name'            => $name,
            'email'           => $request->test_email,
            'site_name'       => Setting::get('site_name', 'SecuroFi.Tech'),
            'site_url'        => config('app.url'),
            'unsubscribe_url' => config('app.url'),
        ];

        $body = $template
            ? $template->render($vars)
            : $this->renderCustomBody($campaign->body ?? '', $vars);

        try {
            Mail::to($request->test_email)->send(
                new CampaignMail(
                    emailSubject: '[CAMPAIGN PREVIEW] ' . $campaign->subject,
                    renderedBody: $body,
                    subscriber: null,
                    recipientName: $name
                )
            );

            EmailLog::logDelivery(
                email: $request->test_email,
                name: $name,
                subject: '[CAMPAIGN PREVIEW] ' . $campaign->subject,
                body: $body,
                type: 'test',
                status: 'sent',
                campaignId: $campaign->id
            );

            return back()->with('success', "Campaign preview email sent to {$request->test_email} successfully! ✅");
        } catch (\Throwable $e) {
            return back()->withErrors(['test_email' => 'Error sending test email: ' . $e->getMessage()]);
        }
    }

    public function destroy(EmailCampaign $campaign)
    {
        $name = $campaign->name;
        $campaign->delete();
        return back()->with('success', "Campaign '{$name}' deleted.");
    }

    private function resolveCampaignRecipients(EmailCampaign $campaign): array
    {
        $recipients = [];

        if ($campaign->audience === 'users') {
            $users = User::all();
            foreach ($users as $u) {
                $recipients[] = [
                    'email'      => $u->email,
                    'name'       => $u->name,
                    'subscriber' => NewsletterSubscriber::where('email', $u->email)->first(),
                ];
            }
            return $recipients;
        }

        $query = NewsletterSubscriber::query();
        if ($campaign->audience === 'active') {
            $query->active();
        }

        $subscribers = $query->get();
        foreach ($subscribers as $sub) {
            $recipients[] = [
                'email'      => $sub->email,
                'name'       => $sub->name ?? explode('@', $sub->email)[0],
                'subscriber' => $sub,
            ];
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
