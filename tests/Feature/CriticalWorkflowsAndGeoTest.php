<?php

namespace Tests\Feature;

use App\Models\AffiliateProduct;
use App\Models\Article;
use App\Models\Category;
use App\Models\Page;
use App\Models\SeoMeta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CriticalWorkflowsAndGeoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $this->admin = User::factory()->create([
            'email' => 'editor@securofi.tech',
            'name'  => 'Editorial Director',
        ]);
        $this->admin->assignRole($role);

        $this->category = Category::create([
            'name' => 'Cloud VPS',
            'slug' => 'cloud-vps',
            'is_active' => true,
        ]);
    }

    /**
     * 1. Test Authentication & Protected Admin Routes
     */
    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');
        $response->assertStatus(200);
    }

    /**
     * 2. Test Affiliate Link Switching & Redirection by Country
     */
    public function test_affiliate_redirect_switches_destination_based_on_visitor_country(): void
    {
        $product = AffiliateProduct::create([
            'name'                   => 'Vultr High Frequency VPS',
            'slug'                   => 'vultr-vps',
            'affiliate_url'          => 'https://vultr.com/?ref=global',
            'country_links'          => [
                'US' => 'https://vultr.com/?ref=usa-promo',
                'GB' => 'https://vultr.co.uk/?ref=uk-promo',
            ],
            'fallback_affiliate_url' => 'https://vultr.com/?ref=fallback',
            'status'                 => true,
        ]);

        // Test with US visitor override
        $resUS = $this->get('/go/' . $product->slug . '?country=US');
        $resUS->assertRedirect('https://vultr.com/?ref=usa-promo');

        // Test with GB visitor override
        $resGB = $this->get('/go/' . $product->slug . '?country=GB');
        $resGB->assertRedirect('https://vultr.co.uk/?ref=uk-promo');

        // Test with unlisted country (e.g. BD) -> falls back
        $resBD = $this->get('/go/' . $product->slug . '?country=BD');
        $resBD->assertRedirect('https://vultr.com/?ref=fallback');

        // Verify click count increments
        $this->assertEquals(3, $product->fresh()->click_count);
    }

    /**
     * 3. Test SEO Meta Tags & OpenGraph Rendering
     */
    public function test_article_renders_seo_meta_and_canonical_tags(): void
    {
        $article = Article::create([
            'title'        => 'Securing Linux Kernel Network Stack in 2026',
            'slug'         => 'securing-linux-kernel-network-stack',
            'category_id'  => $this->category->id,
            'author_id'    => $this->admin->id,
            'content'      => '<p>Linux sysctl hardening best practices.</p>',
            'status'       => 'published',
            'published_at' => now(),
        ]);

        SeoMeta::create([
            'seoable_type'     => Article::class,
            'seoable_id'       => $article->id,
            'meta_title'       => 'Securing Linux Kernel Network Stack | SecuroFi',
            'meta_description' => 'Comprehensive sysctl network security guide for enterprise Linux servers.',
            'focus_keyword'    => 'linux kernel security',
            'canonical_url'    => 'https://securofi.tech/article/securing-linux-kernel-network-stack',
            'robots'           => 'index,follow',
        ]);

        $response = $this->get('/article/' . $article->slug);
        $response->assertStatus(200);
        $response->assertSee('Securing Linux Kernel Network Stack | SecuroFi');
        $response->assertSee('Comprehensive sysctl network security guide for enterprise Linux servers.');
    }

    /**
     * 4. Test Country-Based Article Geo-Restriction & Fallback
     */
    public function test_article_geo_restriction_blocks_disallowed_country_with_fallback_screen(): void
    {
        $article = Article::create([
            'title'                        => 'United States IRS 2026 Crypto Reporting Framework',
            'slug'                         => 'us-irs-2026-crypto-reporting',
            'category_id'                  => $this->category->id,
            'author_id'                    => $this->admin->id,
            'content'                      => '<p>Detailed analysis of IRS form 1099-DA compliance.</p>',
            'status'                       => 'published',
            'published_at'                 => now(),
            'country_rule'                 => 'include',
            'countries'                    => ['US'],
            'restriction_fallback_message' => 'This publication is restricted to US taxpayers.',
        ]);

        // Allowed visitor from US
        $resUS = $this->get('/article/' . $article->slug . '?country=US');
        $resUS->assertStatus(200);
        $resUS->assertSee('Detailed analysis of IRS form 1099-DA compliance.');

        // Restricted visitor from BD -> Sees Geo-notice fallback page (not a 404)
        $resBD = $this->get('/article/' . $article->slug . '?country=BD');
        $resBD->assertStatus(200);
        $resBD->assertSee('Content Not Available in Your Region');
        $resBD->assertSee('This publication is restricted to US taxpayers.');
        $resBD->assertDontSee('Detailed analysis of IRS form 1099-DA compliance.');
    }

    /**
     * 5. Test Contact Submission & Notification
     */
    public function test_user_can_submit_contact_form(): void
    {
        // Ensure Contact Page exists
        Page::create([
            'title'    => 'Contact Us',
            'slug'     => 'contact',
            'content'  => '<p>Get in touch with our security research team.</p>',
            'template' => 'contact',
            'status'   => 'published',
        ]);

        $response = $this->post('/contact', [
            'name'    => 'Sarah Connor',
            'email'   => 'sarah@skynet-defense.org',
            'subject' => 'Vulnerability Disclosure Partnership',
            'message' => 'We detected anomalous latency patterns and would like to coordinate disclosures.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('contact_submissions', [
            'email'   => 'sarah@skynet-defense.org',
            'subject' => 'Vulnerability Disclosure Partnership',
        ]);
    }
}
