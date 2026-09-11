<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArticleCommentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Article $article;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $this->admin = User::factory()->create([
            'email' => 'admin@securofi.tech',
            'name' => 'Security Director'
        ]);
        $this->admin->assignRole($role);

        $category = Category::create([
            'name' => 'Cybersecurity',
            'slug' => 'cybersecurity',
            'description' => 'Cybersecurity updates and research'
        ]);

        $this->article = Article::create([
            'title' => 'Zero Trust Network Architecture Guide',
            'slug' => 'zero-trust-network-architecture-guide',
            'category_id' => $category->id,
            'author_id' => $this->admin->id,
            'status' => 'published',
            'content' => '## Analysis\n\nDeep dive into zero-trust concepts.',
            'published_at' => now(),
        ]);
    }

    public function test_guest_can_submit_comment_for_moderation(): void
    {
        $response = $this->post("/article/{$this->article->slug}/comment", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'website' => 'https://example.com',
            'content' => 'Exceptional technical breakdown of perimeter-less microsegmentation.',
        ]);

        $response->assertRedirect("/article/{$this->article->slug}#comments");
        $response->assertSessionHas('comment_success');

        $this->assertDatabaseHas('comments', [
            'article_id' => $this->article->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'content' => 'Exceptional technical breakdown of perimeter-less microsegmentation.',
            'status' => 'pending',
        ]);
    }

    public function test_spam_honeypot_drops_bot_submissions(): void
    {
        $response = $this->post("/article/{$this->article->slug}/comment", [
            'name' => 'Spam Bot',
            'email' => 'bot@spammer.net',
            'company_url' => 'http://buy-cheap-stuff.xyz',
            'content' => 'Check out our cheap links!',
        ]);

        $response->assertRedirect("/article/{$this->article->slug}#comments");

        $this->assertDatabaseMissing('comments', [
            'email' => 'bot@spammer.net',
        ]);
    }

    public function test_reader_can_submit_reply_to_existing_comment(): void
    {
        $parent = Comment::create([
            'article_id' => $this->article->id,
            'name' => 'Original Author',
            'email' => 'author@example.com',
            'content' => 'What about hardware token key rotation?',
            'status' => 'approved',
        ]);

        $response = $this->post("/article/{$this->article->slug}/comment", [
            'parent_id' => $parent->id,
            'name' => 'Respondent',
            'email' => 'reply@example.com',
            'content' => 'FIDO2 WebAuthn keys should rotate credentials per tenant.',
        ]);

        $response->assertRedirect("/article/{$this->article->slug}#comments");

        $this->assertDatabaseHas('comments', [
            'article_id' => $this->article->id,
            'parent_id' => $parent->id,
            'name' => 'Respondent',
            'content' => 'FIDO2 WebAuthn keys should rotate credentials per tenant.',
        ]);
    }

    public function test_admin_can_access_moderation_dashboard(): void
    {
        $comment = Comment::create([
            'article_id' => $this->article->id,
            'name' => 'Pending Reviewer',
            'email' => 'pending@example.com',
            'content' => 'Awaiting moderation review.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/comments');

        $response->assertStatus(200);
        $response->assertSee('Reader Comments & Discussions');
        $response->assertSee('Pending Reviewer');
        $response->assertSee('Awaiting moderation review.');
        $response->assertSee('replyUrlTemplate');
        $response->assertSee('openReply(');
        $response->assertSee('x-cloak');
        $response->assertSee('id="admin-reply-form"', false);
    }

    public function test_admin_can_approve_and_reject_comment(): void
    {
        $comment = Comment::create([
            'article_id' => $this->article->id,
            'name' => 'Review Item',
            'email' => 'review@example.com',
            'content' => 'Content to be reviewed.',
            'status' => 'pending',
        ]);

        // Approve
        $this->actingAs($this->admin)->post("/admin/comments/{$comment->id}/approve")
            ->assertRedirect();

        $this->assertEquals('approved', $comment->fresh()->status);

        // Reject
        $this->actingAs($this->admin)->post("/admin/comments/{$comment->id}/reject")
            ->assertRedirect();

        $this->assertEquals('rejected', $comment->fresh()->status);
    }

    public function test_admin_can_reply_directly_from_moderation_console(): void
    {
        $parent = Comment::create([
            'article_id' => $this->article->id,
            'name' => 'Community Member',
            'email' => 'community@example.com',
            'content' => 'Is this applicable to Kubernetes service meshes?',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/comments/{$parent->id}/reply", [
            'content' => 'Yes, particularly with Istio mutual TLS enabled.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('comments', [
            'article_id' => $this->article->id,
            'parent_id' => $parent->id,
            'user_id' => $this->admin->id,
            'name' => $this->admin->name . ' (Staff)',
            'is_admin_reply' => true,
            'status' => 'approved',
            'content' => 'Yes, particularly with Istio mutual TLS enabled.',
        ]);
    }

    public function test_admin_can_delete_comment(): void
    {
        $comment = Comment::create([
            'article_id' => $this->article->id,
            'name' => 'Unwanted Comment',
            'email' => 'unwanted@example.com',
            'content' => 'To be deleted.',
            'status' => 'spam',
        ]);

        $response = $this->actingAs($this->admin)->delete("/admin/comments/{$comment->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }
}
