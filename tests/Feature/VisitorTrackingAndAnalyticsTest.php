<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use App\Models\VisitorLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorTrackingAndAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_tracking_middleware_logs_public_visits(): void
    {
        $this->get('/');

        $this->assertDatabaseHas('visitor_logs', [
            'current_page' => '/',
            'is_bot' => false,
        ]);
    }

    public function test_ai_crawler_and_bot_detection(): void
    {
        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (compatible; GPTBot/1.2; +https://openai.com/gptbot)',
        ])->get('/');

        $this->assertDatabaseHas('visitor_logs', [
            'is_bot' => true,
            'bot_name' => 'GPTBot (OpenAI)',
        ]);
    }

    protected function getAdminUser(): User
    {
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'view analytics', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);

        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    public function test_admin_can_access_analytics_console(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get('/admin/analytics');
        $response->assertStatus(200);
        $response->assertSee('Live Traffic & Audience Analytics');
        $response->assertSee('Real-Time Traffic Stream Feed');
    }

    public function test_admin_can_fetch_live_stats_json(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get('/admin/analytics/live-stats');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'active_now',
            'active_humans',
            'active_bots',
            'recent',
        ]);
    }

    public function test_admin_can_export_csv(): void
    {
        $user = $this->getAdminUser();

        $response = $this->actingAs($user)->get('/admin/analytics/export');
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'attachment; filename="visitor_logs_'));
    }

    public function test_rest_api_endpoints(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'AI SaaS',
            'slug' => 'ai-saas',
            'is_active' => true,
        ]);

        $article = Article::create([
            'title' => 'Top AI Tools',
            'slug' => 'top-ai-tools',
            'content' => 'Comprehensive review of AI software tools in 2026.',
            'excerpt' => 'Short overview',
            'category_id' => $category->id,
            'author_id' => $user->id,
            'status' => 'published',
            'reading_time' => 4,
            'published_at' => now(),
        ]);

        // Articles API
        $response = $this->getJson('/api/v1/articles');
        $response->assertStatus(200);
        $response->assertJsonFragment(['slug' => 'top-ai-tools']);

        // Single Article API
        $responseSingle = $this->getJson('/api/v1/articles/top-ai-tools');
        $responseSingle->assertStatus(200);
        $responseSingle->assertJsonFragment(['title' => 'Top AI Tools']);

        // Categories API
        $responseCats = $this->getJson('/api/v1/categories');
        $responseCats->assertStatus(200);
        $responseCats->assertJsonFragment(['slug' => 'ai-saas']);

        // Stats API
        $responseStats = $this->getJson('/api/v1/stats');
        $responseStats->assertStatus(200);
        $responseStats->assertJsonStructure([
            'status',
            'data' => ['published_articles', 'categories', 'total_views'],
        ]);

        // Verify Article Performance Score calculation
        $this->assertGreaterThan(0, $article->performance_score);
        $this->assertIsString($article->performance_label);
    }
}
