<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * CaptchaService
 *
 * Supports Google reCAPTCHA v2 (checkbox), reCAPTCHA v3 (invisible/score-based),
 * and Cloudflare Turnstile. Provider and keys are stored in the settings table
 * and can be managed from the Admin Panel without code changes.
 */
class CaptchaService
{
    protected string $provider;
    protected string $siteKey;
    protected string $secretKey;
    protected bool   $enabled;

    // Per-form toggles
    protected bool $contactEnabled;
    protected bool $newsletterEnabled;
    protected bool $commentEnabled;

    // reCAPTCHA v3 score threshold (default 0.5)
    protected float $v3Threshold;

    public function __construct()
    {
        $this->provider          = Setting::get('captcha_provider', 'recaptcha_v2');
        $this->siteKey           = Setting::get('captcha_site_key', '');
        $this->secretKey         = Setting::get('captcha_secret_key', '');
        $this->enabled           = Setting::get('captcha_enabled', '0') === '1';
        $this->contactEnabled    = Setting::get('captcha_contact_enabled', '1') === '1';
        $this->newsletterEnabled = Setting::get('captcha_newsletter_enabled', '1') === '1';
        $this->commentEnabled    = Setting::get('captcha_comment_enabled', '1') === '1';
        $this->v3Threshold       = (float) Setting::get('captcha_v3_threshold', '0.5');
    }

    /**
     * Is captcha globally enabled AND have valid keys configured?
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->siteKey) && !empty($this->secretKey);
    }

    public function isEnabledForForm(string $form): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return match ($form) {
            'contact'    => $this->contactEnabled,
            'newsletter' => $this->newsletterEnabled,
            'comment'    => $this->commentEnabled,
            default      => false,
        };
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * Returns the HTML script tag to load the captcha JS library.
     */
    public function scriptTag(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return match ($this->provider) {
            'recaptcha_v2' => '<script src="https://www.google.com/recaptcha/api.js" async defer></script>',
            'recaptcha_v3' => '<script src="https://www.google.com/recaptcha/api.js?render=' . e($this->siteKey) . '" async defer></script>',
            'turnstile'    => '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>',
            default        => '',
        };
    }

    /**
     * Returns the HTML widget markup for a given form.
     * For reCAPTCHA v3 this is a hidden input + JS callback.
     */
    public function widgetHtml(string $form = 'contact', string $action = 'submit'): string
    {
        if (!$this->isEnabledForForm($form)) {
            return '';
        }

        $siteKey = e($this->siteKey);

        return match ($this->provider) {
            'recaptcha_v2' => <<<HTML
                <div class="g-recaptcha" data-sitekey="{$siteKey}" data-theme="dark"></div>
HTML,
            'recaptcha_v3' => <<<HTML
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response-{$form}">
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        if (typeof grecaptcha !== 'undefined') {
                            grecaptcha.ready(function () {
                                grecaptcha.execute('{$siteKey}', {action: '{$action}'}).then(function (token) {
                                    var el = document.getElementById('g-recaptcha-response-{$form}');
                                    if (el) el.value = token;
                                });
                            });
                        }
                    });
                </script>
HTML,
            'turnstile' => <<<HTML
                <div class="cf-turnstile" data-sitekey="{$siteKey}" data-theme="dark"></div>
HTML,
            default => '',
        };
    }

    /**
     * Verify the captcha token submitted with the request.
     *
     * @param  string|null $token   The captcha response token from the request
     * @param  string      $remoteIp The user's IP address
     * @return bool
     */
    public function verify(?string $token, string $remoteIp = ''): bool
    {
        if (!$this->isEnabled()) {
            return true; // Captcha disabled — always pass
        }

        if (empty($token)) {
            return false;
        }

        try {
            $verifyUrl = match ($this->provider) {
                'recaptcha_v2', 'recaptcha_v3' => 'https://www.google.com/recaptcha/api/siteverify',
                'turnstile'                     => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                default                         => '',
            };

            if (empty($verifyUrl)) {
                return true;
            }

            $response = Http::asForm()->post($verifyUrl, [
                'secret'   => $this->secretKey,
                'response' => $token,
                'remoteip' => $remoteIp,
            ]);

            $data = $response->json();

            if (!($data['success'] ?? false)) {
                Log::warning('Captcha verification failed', [
                    'provider'    => $this->provider,
                    'error-codes' => $data['error-codes'] ?? [],
                ]);
                return false;
            }

            // reCAPTCHA v3 extra score check
            if ($this->provider === 'recaptcha_v3') {
                $score = (float) ($data['score'] ?? 0);
                if ($score < $this->v3Threshold) {
                    Log::warning('reCAPTCHA v3 score below threshold', [
                        'score'     => $score,
                        'threshold' => $this->v3Threshold,
                    ]);
                    return false;
                }
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Captcha verification exception: ' . $e->getMessage());
            // On network error, fail-open (pass) to not block legitimate users
            return true;
        }
    }

    /**
     * Get the request field name for the captcha token, based on provider.
     */
    public function tokenFieldName(): string
    {
        return match ($this->provider) {
            'turnstile'    => 'cf-turnstile-response',
            default        => 'g-recaptcha-response',
        };
    }
}
