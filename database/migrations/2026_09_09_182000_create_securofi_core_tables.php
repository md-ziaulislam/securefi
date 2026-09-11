<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tags
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // 3. Articles
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->string('featured_image')->nullable();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['draft', 'scheduled', 'published'])->default('draft');
            $table->integer('reading_time')->default(1);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });

        // 4. Article-Tag Pivot
        Schema::create('article_tag', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->primary(['article_id', 'tag_id']);
        });

        // 5. Pages
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content');
            $table->string('template')->default('default');
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('show_in_footer')->default(true);
            $table->integer('menu_order')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();
        });

        // 6. Polymorphic SEO Meta
        Schema::create('seo_meta', function (Blueprint $table) {
            $table->id();
            $table->morphs('seoable');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->json('schema_json')->nullable();
            $table->string('focus_keyword')->nullable();
            $table->string('robots')->default('index,follow');
            $table->timestamps();
        });

        // 7. Ad Slots
        Schema::create('ad_slots', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('position', ['header', 'sidebar', 'in_article_top', 'in_article_middle', 'in_article_bottom', 'footer']);
            $table->longText('code')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('target_url')->nullable();
            $table->boolean('is_banner')->default(false);
            $table->json('category_ids')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 8. Affiliate Products
        Schema::create('affiliate_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('price')->nullable();
            $table->text('description')->nullable();
            $table->text('affiliate_url');
            $table->string('source_site')->nullable();
            $table->string('rel_type')->default('sponsored nofollow');
            $table->unsignedBigInteger('click_count')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 9. Affiliate Shortcodes
        Schema::create('affiliate_shortcodes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['single', 'list', 'comparison'])->default('single');
            $table->json('product_ids');
            $table->string('shortcode')->unique();
            $table->timestamps();
        });

        // 10. Redirects
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_url')->index();
            $table->string('to_url');
            $table->smallInteger('type')->default(301);
            $table->boolean('status')->default(true);
            $table->unsignedBigInteger('hit_count')->default(0);
            $table->timestamps();
        });

        // 11. Settings (Global Key-Value)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('group')->default('general');
            $table->timestamps();
        });

        // 12. Theme Settings (Design Tokens: Swiss Minimalism)
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('color');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 13. Media Library
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('disk')->default('public');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('alt_text')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 14. Visitor Logs (Real-Time & Historical)
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->nullable();
            $table->string('session_id')->nullable()->index();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('device_type')->nullable();
            $table->string('os')->nullable();
            $table->string('browser')->nullable();
            $table->string('referrer_source')->nullable();
            $table->text('current_page')->nullable();
            $table->boolean('is_bot')->default(false);
            $table->string('bot_name')->nullable();
            $table->boolean('is_returning')->default(false);
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();
        });

        // 15. Login History
        Schema::create('login_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('device')->nullable();
            $table->string('browser')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('success');
            $table->timestamp('created_at')->useCurrent();
        });

        // 16. Newsletter Subscribers
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('status')->default('subscribed');
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamps();
        });

        // 17. Contact Submissions
        Schema::create('contact_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_submissions');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('login_history');
        Schema::dropIfExists('visitor_logs');
        Schema::dropIfExists('media');
        Schema::dropIfExists('theme_settings');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('affiliate_shortcodes');
        Schema::dropIfExists('affiliate_products');
        Schema::dropIfExists('ad_slots');
        Schema::dropIfExists('seo_meta');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('article_tag');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
    }
};
