<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleBasedAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $admin;
    protected User $editor;
    protected User $author;
    protected User $reader;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);

        $this->superAdmin = User::factory()->create(['name' => 'Super Admin User', 'email' => 'super@securofi.tech']);
        $this->superAdmin->assignRole('Super Admin');

        $this->admin = User::factory()->create(['name' => 'Platform Admin', 'email' => 'admin@securofi.tech']);
        $this->admin->assignRole('Admin');

        $this->editor = User::factory()->create(['name' => 'Senior Editor', 'email' => 'editor@securofi.tech']);
        $this->editor->assignRole('Editor');

        $this->author = User::factory()->create(['name' => 'Tech Author', 'email' => 'author@securofi.tech']);
        $this->author->assignRole('Author');

        $this->reader = User::factory()->create(['name' => 'Regular Reader', 'email' => 'reader@securofi.tech']);
        // Reader has no staff roles

        $this->category = Category::create([
            'name' => 'Cybersecurity Analysis',
            'slug' => 'cybersecurity-analysis',
            'description' => 'Security protocols and research',
        ]);
    }

    public function test_unauthenticated_guest_is_redirected_to_login_from_admin(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/articles');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_without_staff_role_is_denied_admin_access_with_403(): void
    {
        $response = $this->actingAs($this->reader)->get('/admin');
        $response->assertStatus(403);
        $response->assertSee('Unauthorized Request');
        $response->assertSee('403 Access Denied');

        $response = $this->actingAs($this->reader)->get('/admin/articles');
        $response->assertStatus(403);

        $response = $this->actingAs($this->reader)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_all_admin_modules(): void
    {
        $routes = [
            '/admin',
            '/admin/articles',
            '/admin/categories',
            '/admin/tags',
            '/admin/media',
            '/admin/comments',
            '/admin/ads',
            '/admin/affiliates',
            '/admin/seo',
            '/admin/pages',
            '/admin/theme',
            '/admin/analytics',
            '/admin/users',
            '/admin/security',
            '/admin/settings',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->superAdmin)->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_admin_cannot_access_users_security_settings_theme(): void
    {
        // Permitted routes for Admin
        $allowedRoutes = [
            '/admin',
            '/admin/articles',
            '/admin/categories',
            '/admin/tags',
            '/admin/media',
            '/admin/comments',
            '/admin/ads',
            '/admin/affiliates',
            '/admin/seo',
            '/admin/pages',
            '/admin/analytics',
        ];

        foreach ($allowedRoutes as $route) {
            $response = $this->actingAs($this->admin)->get($route);
            $response->assertStatus(200);
        }

        // Forbidden routes for Admin (Super Admin only)
        $forbiddenRoutes = [
            '/admin/users',
            '/admin/security',
            '/admin/settings',
            '/admin/theme',
        ];

        foreach ($forbiddenRoutes as $route) {
            $response = $this->actingAs($this->admin)->get($route);
            $response->assertStatus(403);
        }
    }

    public function test_editor_can_access_editorial_modules_but_blocked_from_monetization_and_administration(): void
    {
        // Permitted routes for Editor
        $allowedRoutes = [
            '/admin',
            '/admin/articles',
            '/admin/categories',
            '/admin/tags',
            '/admin/media',
            '/admin/comments',
            '/admin/pages',
        ];

        foreach ($allowedRoutes as $route) {
            $response = $this->actingAs($this->editor)->get($route);
            $response->assertStatus(200);
        }

        // Forbidden routes for Editor
        $forbiddenRoutes = [
            '/admin/ads',
            '/admin/affiliates',
            '/admin/seo',
            '/admin/analytics',
            '/admin/users',
            '/admin/security',
            '/admin/settings',
            '/admin/theme',
        ];

        foreach ($forbiddenRoutes as $route) {
            $response = $this->actingAs($this->editor)->get($route);
            $response->assertStatus(403);
        }
    }

    public function test_author_can_only_access_articles_tags_media_and_is_blocked_from_other_modules(): void
    {
        // Permitted routes for Author
        $allowedRoutes = [
            '/admin',
            '/admin/articles',
            '/admin/tags',
            '/admin/media',
        ];

        foreach ($allowedRoutes as $route) {
            $response = $this->actingAs($this->author)->get($route);
            $response->assertStatus(200);
        }

        // Forbidden routes for Author
        $forbiddenRoutes = [
            '/admin/comments',
            '/admin/categories',
            '/admin/pages',
            '/admin/ads',
            '/admin/affiliates',
            '/admin/seo',
            '/admin/analytics',
            '/admin/users',
            '/admin/security',
            '/admin/settings',
            '/admin/theme',
        ];

        foreach ($forbiddenRoutes as $route) {
            $response = $this->actingAs($this->author)->get($route);
            $response->assertStatus(403);
        }
    }

    public function test_author_cannot_edit_or_delete_other_authors_articles(): void
    {
        $otherAuthor = User::factory()->create();
        $otherAuthor->assignRole('Author');

        $otherArticle = Article::create([
            'title' => 'Other Author Article',
            'slug' => 'other-author-article',
            'category_id' => $this->category->id,
            'author_id' => $otherAuthor->id,
            'status' => 'published',
            'content' => 'Article content by another author.',
        ]);

        $ownArticle = Article::create([
            'title' => 'My Own Article',
            'slug' => 'my-own-article',
            'category_id' => $this->category->id,
            'author_id' => $this->author->id,
            'status' => 'draft',
            'content' => 'Article content by this author.',
        ]);

        // Attempting to edit other author's article -> 403 Forbidden
        $response = $this->actingAs($this->author)->get("/admin/articles/{$otherArticle->id}/edit");
        $response->assertStatus(403);

        // Attempting to update other author's article -> 403 Forbidden
        $response = $this->actingAs($this->author)->put("/admin/articles/{$otherArticle->id}", [
            'title' => 'Hacked Title',
            'slug' => 'hacked-title',
            'category_id' => $this->category->id,
            'content' => 'Tampered content',
            'status' => 'draft',
        ]);
        $response->assertStatus(403);

        // Attempting to delete other author's article -> 403 Forbidden
        $response = $this->actingAs($this->author)->delete("/admin/articles/{$otherArticle->id}");
        $response->assertStatus(403);

        // Can edit own article -> 200 OK
        $response = $this->actingAs($this->author)->get("/admin/articles/{$ownArticle->id}/edit");
        $response->assertStatus(200);

        // Can delete own article -> Redirects successfully
        $response = $this->actingAs($this->author)->delete("/admin/articles/{$ownArticle->id}");
        $response->assertRedirect('/admin/articles');
        $this->assertDatabaseMissing('articles', ['id' => $ownArticle->id]);
    }

    public function test_author_cannot_directly_publish_articles(): void
    {
        // Storing with status 'published' gets forced to 'draft' for non-publishers
        $response = $this->actingAs($this->author)->post('/admin/articles', [
            'title' => 'Direct Publish Attempt',
            'category_id' => $this->category->id,
            'content' => 'Testing publish permission restriction.',
            'status' => 'published',
        ]);

        $response->assertRedirect('/admin/articles');
        $this->assertDatabaseHas('articles', [
            'title' => 'Direct Publish Attempt',
            'status' => 'draft', // Forced to draft
        ]);

        $article = Article::where('title', 'Direct Publish Attempt')->first();

        // Updating with status 'published' also gets forced to 'draft'
        $response = $this->actingAs($this->author)->put("/admin/articles/{$article->id}", [
            'title' => 'Direct Publish Attempt Modified',
            'slug' => 'direct-publish-attempt',
            'category_id' => $this->category->id,
            'content' => 'Still trying to publish directly.',
            'status' => 'published',
        ]);

        $response->assertRedirect('/admin/articles');
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'status' => 'draft', // Maintained as draft
        ]);

        // Bulk publish is completely forbidden for authors -> 403
        $response = $this->actingAs($this->author)->post('/admin/articles/bulk-action', [
            'action' => 'publish',
            'selected_ids' => [$article->id],
        ]);
        $response->assertStatus(403);
    }

    public function test_sidebar_displays_only_permitted_navigation_options_per_role(): void
    {
        // Author sidebar
        $response = $this->actingAs($this->author)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Articles');
        $response->assertSee('Tags');
        $response->assertSee('Media Library');
        $response->assertDontSee('Comments');
        $response->assertDontSee('Ads Management');
        $response->assertDontSee('SEO Suite');
        $response->assertDontSee('Users & RBAC');
        $response->assertDontSee('Security & Backups');
        $response->assertSee('Author'); // Role badge

        // Editor sidebar
        $response = $this->actingAs($this->editor)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Articles');
        $response->assertSee('Comments');
        $response->assertSee('Pages (CMS)');
        $response->assertDontSee('Ads Management');
        $response->assertDontSee('SEO Suite');
        $response->assertDontSee('Users & RBAC');
        $response->assertDontSee('Security & Backups');
        $response->assertSee('Editor'); // Role badge

        // Super Admin sidebar
        $response = $this->actingAs($this->superAdmin)->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('Articles');
        $response->assertSee('Comments');
        $response->assertSee('Ads Management');
        $response->assertSee('SEO Suite');
        $response->assertSee('Theme Customizer');
        $response->assertSee('Users & RBAC', false);
        $response->assertSee('Security & Backups', false);
        $response->assertSee('Super Admin'); // Role badge
    }
}
