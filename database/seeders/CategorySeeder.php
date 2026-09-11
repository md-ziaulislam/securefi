<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'AI Tools & SaaS',
                'slug' => 'ai-tools-saas',
                'description' => 'Comprehensive reviews, comparisons, and workflows for state-of-the-art AI software and SaaS productivity platforms.',
                'meta_title' => 'AI Tools & SaaS Insights | SecuroFi.Tech',
                'meta_description' => 'Explore the latest AI software, machine learning applications, and enterprise SaaS productivity solutions.',
                'order' => 1,
            ],
            [
                'name' => 'Web Hosting & Domains',
                'slug' => 'web-hosting-domains',
                'description' => 'Unbiased benchmarks of cloud infrastructure, VPS, managed WordPress hosting, and website builders.',
                'meta_title' => 'Web Hosting & Domain Guides | SecuroFi.Tech',
                'meta_description' => 'Find the fastest, most reliable web hosts, VPS providers, and domain management strategies.',
                'order' => 2,
            ],
            [
                'name' => 'Cybersecurity & Privacy',
                'slug' => 'cybersecurity-privacy',
                'description' => 'Actionable cybersecurity practices, VPN audits, data encryption guides, and threat mitigation tactics.',
                'meta_title' => 'Cybersecurity & Privacy Analysis | SecuroFi.Tech',
                'meta_description' => 'Protect your digital footprint with cybersecurity tutorials, VPN reviews, and enterprise security frameworks.',
                'order' => 3,
            ],
            [
                'name' => 'Personal Finance',
                'slug' => 'personal-finance',
                'description' => 'Smart fintech tools, wealth-building strategies, budgeting systems, and digital investment security.',
                'meta_title' => 'Personal Finance & Fintech | SecuroFi.Tech',
                'meta_description' => 'Master your money with fintech apps, passive income setups, and safe digital financial platforms.',
                'order' => 4,
            ],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
