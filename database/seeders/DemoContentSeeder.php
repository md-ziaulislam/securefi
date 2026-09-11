<?php

namespace Database\Seeders;

use App\Models\AdSlot;
use App\Models\AffiliateProduct;
use App\Models\AffiliateShortcode;
use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $author = User::where('email', 'admin@securofi.tech')->first();
        if (!$author) {
            return;
        }

        // Tags
        $tagsData = ['AI', 'SaaS', 'Cloud Hosting', 'Cybersecurity', 'Zero Trust', 'Fintech', 'Privacy', 'DevOps'];
        $createdTags = [];
        foreach ($tagsData as $t) {
            $createdTags[$t] = Tag::firstOrCreate(['name' => $t], ['slug' => Str::slug($t)]);
        }

        // Sample Affiliate Products
        $p1 = AffiliateProduct::firstOrCreate(
            ['name' => 'ProtonPass Business Security'],
            [
                'image' => 'https://picsum.photos/seed/protonpass/400/300',
                'price' => '$3.99/user/mo',
                'description' => 'Swiss-engineered end-to-end encrypted credentials manager with built-in 2FA authenticator and alias masking.',
                'affiliate_url' => 'https://proton.me/pass',
                'source_site' => 'Proton AG',
                'rel_type' => 'sponsored nofollow',
                'status' => true,
            ]
        );

        $p2 = AffiliateProduct::firstOrCreate(
            ['name' => 'DigitalOcean Premium Cloud Droplets'],
            [
                'image' => 'https://picsum.photos/seed/digitalocean/400/300',
                'price' => '$6.00/mo',
                'description' => 'Scalable virtual machines with high-performance NVMe SSDs, dedicated vCPUs, and automated snapshots.',
                'affiliate_url' => 'https://www.digitalocean.com',
                'source_site' => 'DigitalOcean',
                'rel_type' => 'sponsored nofollow',
                'status' => true,
            ]
        );

        // Shortcodes
        AffiliateShortcode::firstOrCreate(
            ['shortcode' => 'affiliate_product id="1"'],
            [
                'title' => 'Single Product Box - ProtonPass',
                'type' => 'single',
                'product_ids' => [$p1->id],
            ]
        );

        AffiliateShortcode::firstOrCreate(
            ['shortcode' => 'affiliate_comparison ids="1,2"'],
            [
                'title' => 'Security & Infrastructure Comparison',
                'type' => 'comparison',
                'product_ids' => [$p1->id, $p2->id],
            ]
        );

        // Sample Ad Slots
        AdSlot::firstOrCreate(
            ['position' => 'header'],
            [
                'name' => 'Top Header Leaderboard',
                'is_banner' => true,
                'banner_image' => 'https://picsum.photos/seed/adbanner/728/90',
                'target_url' => 'https://securofi.tech',
                'status' => true,
            ]
        );

        AdSlot::firstOrCreate(
            ['position' => 'sidebar'],
            [
                'name' => 'Sidebar High-Impact Square',
                'is_banner' => true,
                'banner_image' => 'https://picsum.photos/seed/sidebarad/300/250',
                'target_url' => 'https://securofi.tech',
                'status' => true,
            ]
        );

        AdSlot::firstOrCreate(
            ['position' => 'in_article_middle'],
            [
                'name' => 'In-Article Responsive Sponsor',
                'code' => '<div class="p-4 bg-tertiary border border-neutral/20 text-center my-6"><span class="text-xs font-mono uppercase tracking-widest text-neutral block mb-1">Sponsored Message</span><p class="text-sm font-medium">Protect your API keys and databases with SecuroFi Verified Cloud Solutions.</p></div>',
                'is_banner' => false,
                'status' => true,
            ]
        );

        // Sample Articles
        $aiCat = Category::where('slug', 'ai-tools-saas')->first();
        $hostingCat = Category::where('slug', 'web-hosting-domains')->first();
        $secCat = Category::where('slug', 'cybersecurity-privacy')->first();
        $finCat = Category::where('slug', 'personal-finance')->first();

        $articles = [
            [
                'title' => 'Autonomous AI Agents in Enterprise: Architecture and Benchmark Analysis',
                'slug' => 'autonomous-ai-agents-enterprise-architecture-benchmarks',
                'category_id' => $aiCat->id,
                'featured_image' => 'https://picsum.photos/seed/ai-agents/1200/630',
                'excerpt' => 'An objective evaluation of autonomous LLM multi-agent frameworks, token efficiency metrics, and privacy boundary enforcement.',
                'content' => '<p>The deployment of autonomous AI agents across modern enterprise environments represents a structural paradigm shift from prompt-response interfaces toward asynchronous goal execution.</p><h2>1. Multi-Agent Coordination Models</h2><p>In distributed agentic pipelines, determinism is critical. By isolating cognitive roles into specialized workers—synthesizers, validators, and executors—system architects can minimize hallucination cascades while enforcing strict schema compliance.</p>[affiliate_product id="1"]<h2>2. Token Efficiency and Latency Profiling</h2><p>Our benchmark across 10,000 recursive execution cycles revealed that structured JSON outputs reduce parsing retries by 42% compared to freeform natural language parsing.</p><h2>3. Guardrails and Threat Vectors</h2><p>Zero-trust boundaries must be maintained between agent memory pools and operational database layers to eliminate prompt injection vulnerability surfaces.</p>',
                'status' => 'published',
                'reading_time' => 5,
                'is_featured' => true,
                'published_at' => now()->subDays(2),
                'tags' => ['AI', 'SaaS'],
            ],
            [
                'title' => 'High-Performance Cloud VPS vs Managed Hosting: A Rigorous Latency Benchmark',
                'slug' => 'cloud-vps-vs-managed-hosting-latency-benchmark',
                'category_id' => $hostingCat->id,
                'featured_image' => 'https://picsum.photos/seed/cloud-vps/1200/630',
                'excerpt' => 'Direct comparisons between raw cloud compute instances and managed application runtimes under high concurrency load tests.',
                'content' => '<p>Selecting between self-managed virtual private servers and managed platform-as-a-service ecosystems requires balancing operational maintenance overhead against raw execution throughput.</p><h2>1. Time to First Byte (TTFB) Comparison</h2><p>Under simulated traffic of 500 concurrent HTTP/2 connections, raw NVMe-backed instances exhibited median TTFB figures under 45ms, whereas managed tiers with heavy middleware layers averaged 110ms.</p>[affiliate_comparison ids="1,2"]<h2>2. Kernel Tuning for Web Workloads</h2><p>Optimizing TCP keepalive intervals and file descriptor limits allows bare-metal and modern hypervisors to handle dramatic traffic spikes without dropping socket connections.</p>',
                'status' => 'published',
                'reading_time' => 6,
                'is_featured' => true,
                'published_at' => now()->subDays(4),
                'tags' => ['Cloud Hosting', 'DevOps'],
            ],
            [
                'title' => 'Zero-Trust Architecture for Remote Engineering Teams',
                'slug' => 'zero-trust-architecture-remote-engineering-teams',
                'category_id' => $secCat->id,
                'featured_image' => 'https://picsum.photos/seed/security-shield/1200/630',
                'excerpt' => 'Implementing least-privilege access, hardware-backed authentication tokens, and granular network micro-segmentation.',
                'content' => '<p>The traditional perimeter security model has collapsed. Modern threat actors exploit trusted internal network pathways once an individual endpoint is compromised.</p><h2>1. Principles of Continuous Verification</h2><p>Never trust, always verify. Every request—internal or external—must undergo cryptographic authentication, device posture assessment, and context evaluation prior to resource granting.</p><h2>2. Enforcing Hardware-Bound Credentials</h2><p>FIDO2/WebAuthn physical keys deliver mathematical immunity against credential harvesting and real-time reverse proxy phishing attacks.</p>',
                'status' => 'published',
                'reading_time' => 4,
                'is_featured' => false,
                'published_at' => now()->subDays(6),
                'tags' => ['Cybersecurity', 'Zero Trust', 'Privacy'],
            ],
            [
                'title' => 'Digital Asset Security and Cold Storage Safeguards for Tech Professionals',
                'slug' => 'digital-asset-security-cold-storage-safeguards',
                'category_id' => $finCat->id,
                'featured_image' => 'https://picsum.photos/seed/fintech-crypto/1200/630',
                'excerpt' => 'How founders and developers can insulate high-value assets using multi-signature schemes and offline cryptographic enclaves.',
                'content' => '<p>Managing financial reserves and equity distributions in a decentralized digital economy mandates cold-storage protocols far exceeding consumer-grade security.</p><h2>1. Multi-Signature Cryptographic Governance</h2><p>Single-key configurations present catastrophic failure points. Implementing 3-of-5 quorum signatures across geographically isolated keyholders neutralizes single-point compromises.</p><h2>2. Air-Gapped Transaction Verification</h2><p>Signing transaction payloads on air-gapped hardware enclaves completely eliminates memory-resident key extraction vectors.</p>',
                'status' => 'published',
                'reading_time' => 5,
                'is_featured' => false,
                'published_at' => now()->subDays(8),
                'tags' => ['Fintech', 'Privacy'],
            ],
        ];

        foreach ($articles as $art) {
            $tags = $art['tags'];
            unset($art['tags']);

            $article = Article::firstOrCreate(
                ['slug' => $art['slug']],
                array_merge($art, ['author_id' => $author->id])
            );

            $tagIds = [];
            foreach ($tags as $tagName) {
                if (isset($createdTags[$tagName])) {
                    $tagIds[] = $createdTags[$tagName]->id;
                }
            }
            $article->tags()->sync($tagIds);

            // SEO Meta
            $article->seoMeta()->updateOrCreate([], [
                'meta_title' => $article->title . ' | SecuroFi.Tech',
                'meta_description' => $article->excerpt,
                'og_image' => $article->featured_image,
                'robots' => 'index,follow',
            ]);
        }
    }
}
