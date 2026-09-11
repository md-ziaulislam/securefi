<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class GeneralSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'SecuroFi.Tech', 'group' => 'branding'],
            ['key' => 'site_tagline', 'value' => 'Engineering Modern Software, Cybersecurity & Finance', 'group' => 'branding'],
            ['key' => 'site_description', 'value' => 'SecuroFi.Tech provides objective, structured analysis of SaaS tools, cloud infrastructure, web security protocols, and digital finance.', 'group' => 'seo'],
            ['key' => 'site_logo_light', 'value' => null, 'group' => 'branding'],
            ['key' => 'site_logo_dark', 'value' => null, 'group' => 'branding'],
            ['key' => 'site_favicon', 'value' => null, 'group' => 'branding'],
            ['key' => 'default_og_image', 'value' => null, 'group' => 'seo'],
            ['key' => 'contact_email', 'value' => 'contact@securofi.tech', 'group' => 'contact'],
            ['key' => 'social_twitter', 'value' => 'https://twitter.com/securofitech', 'group' => 'social'],
            ['key' => 'social_github', 'value' => 'https://github.com/securofitech', 'group' => 'social'],
            ['key' => 'social_linkedin', 'value' => 'https://linkedin.com/company/securofitech', 'group' => 'social'],
            ['key' => 'header_scripts', 'value' => '', 'group' => 'scripts'],
            ['key' => 'footer_scripts', 'value' => '', 'group' => 'scripts'],
            ['key' => 'google_analytics_id', 'value' => '', 'group' => 'analytics'],
            ['key' => 'robots_txt', 'value' => "User-agent: *\nAllow: /\n\nSitemap: http://localhost:8000/sitemap.xml", 'group' => 'seo'],
            ['key' => 'affiliate_disclosure_text', 'value' => 'SecuroFi.Tech is reader-supported. When you buy through links on our site, we may earn an affiliate commission at no extra cost to you.', 'group' => 'affiliate'],
            ['key' => 'affiliate_auto_inject_disclosure', 'value' => '1', 'group' => 'affiliate'],
            ['key' => 'cookie_consent_enabled', 'value' => '1', 'group' => 'privacy'],
            ['key' => 'cookie_consent_text', 'value' => 'We use cookies and privacy-respecting telemetry to ensure high-performance browsing and relevant recommendations.', 'group' => 'privacy'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
