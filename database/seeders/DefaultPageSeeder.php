<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class DefaultPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about',
                'content' => '<h2>About SecuroFi.Tech</h2><p>SecuroFi.Tech was engineered with a clear mandate: to eliminate arbitrariness and superficiality from tech recommendations. Inspired by the clarity and functional precision of Swiss typography and architecture, we produce rigorously verified analysis across Artificial Intelligence, web hosting architectures, digital security, and personal finance systems.</p><p>Every guide, benchmark, and product comparison is designed to help engineers, founders, and security-conscious individuals make informed software choices without marketing noise.</p>',
                'template' => 'about',
                'status' => 'published',
                'show_in_menu' => true,
                'show_in_footer' => true,
                'menu_order' => 1,
                'meta_title' => 'About SecuroFi.Tech | Objective Tech & Security Insights',
                'meta_description' => 'Learn about SecuroFi.Tech mission to deliver objective, Swiss-standard engineering analysis for AI tools, web hosting, cybersecurity, and personal finance.',
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h2>Privacy Policy</h2><p>Last updated: September 2026</p><p>SecuroFi.Tech ("we", "our", or "us") is dedicated to protecting user privacy. This document explains what information is collected when you visit our website, how that data is protected, and your rights under global privacy regulations including GDPR and CCPA.</p><h3>1. Data We Collect</h3><p>We do not sell personal data. We collect anonymized telemetry such as browser type, operating system, and geographic region to analyze aggregate traffic patterns and prevent fraudulent requests.</p><h3>2. Cookies and Tracking</h3><p>We utilize standard functional cookies to maintain session states and user preferences (such as light/dark mode). You may manage your cookie preferences at any time.</p>',
                'template' => 'default',
                'status' => 'published',
                'show_in_menu' => false,
                'show_in_footer' => true,
                'menu_order' => 2,
                'meta_title' => 'Privacy Policy | SecuroFi.Tech',
                'meta_description' => 'Our commitment to data privacy, GDPR compliance, and transparent telemetry handling.',
            ],
            [
                'title' => 'Terms & Conditions',
                'slug' => 'terms-conditions',
                'content' => '<h2>Terms and Conditions</h2><p>Last updated: September 2026</p><p>By accessing SecuroFi.Tech, you agree to comply with these terms of service. All content published on this platform is provided for educational, analytical, and informational purposes only.</p><h3>Intellectual Property</h3><p>All trademarks, logos, and original written analyses are the property of SecuroFi.Tech and respective authors.</p>',
                'template' => 'default',
                'status' => 'published',
                'show_in_menu' => false,
                'show_in_footer' => true,
                'menu_order' => 3,
                'meta_title' => 'Terms & Conditions | SecuroFi.Tech',
                'meta_description' => 'Terms of service, intellectual property guidelines, and user usage agreements for SecuroFi.Tech.',
            ],
            [
                'title' => 'Affiliate Disclosure',
                'slug' => 'affiliate-disclosure',
                'content' => '<h2>Affiliate Disclosure</h2><p>In accordance with FTC guidelines, SecuroFi.Tech maintains complete transparency regarding monetization. Some links on this site contain affiliate tracking tags. If you click on an affiliate link and make a purchase, we may receive a commission at no additional cost to you.</p><p>Our editorial integrity is paramount. Affiliate partnerships never influence our benchmark scores or critical evaluations.</p>',
                'template' => 'default',
                'status' => 'published',
                'show_in_menu' => false,
                'show_in_footer' => true,
                'menu_order' => 4,
                'meta_title' => 'Affiliate Disclosure | SecuroFi.Tech',
                'meta_description' => 'Full transparency regarding our affiliate partnerships, reader-supported model, and strict editorial independence.',
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact',
                'content' => '<p>Have questions, security audit requests, or partnership inquiries? Send us a message below and our engineering team will respond within 24–48 business hours.</p>',
                'template' => 'contact',
                'status' => 'published',
                'show_in_menu' => true,
                'show_in_footer' => true,
                'menu_order' => 5,
                'meta_title' => 'Contact SecuroFi.Tech Team',
                'meta_description' => 'Reach out to Ziaul Islam and the SecuroFi.Tech editorial and engineering staff.',
            ],
            [
                'title' => 'Developer Information',
                'slug' => 'dev-info',
                'content' => '<h2>Developer & Architecture Specification</h2><p><strong>Lead Architect:</strong> Ziaul Islam</p><p><strong>Stack:</strong> Laravel 12 LTS + MySQL 8 + Tailwind CSS (Swiss Minimalist Grid) + Blade + Spatie Suite.</p><p>SecuroFi.Tech is engineered for maximum performance, minimal bundle overhead, and complete no-code editorial flexibility.</p>',
                'template' => 'dev-info',
                'status' => 'published',
                'show_in_menu' => false,
                'show_in_footer' => true,
                'menu_order' => 6,
                'meta_title' => 'Developer & System Info | SecuroFi.Tech',
                'meta_description' => 'Architecture specifications, developer credentials, and internal telemetry hubs for SecuroFi.Tech.',
            ],
        ];

        foreach ($pages as $p) {
            Page::firstOrCreate(['slug' => $p['slug']], $p);
        }
    }
}
