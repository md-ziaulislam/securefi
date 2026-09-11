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
        Schema::table('articles', function (Blueprint $table) {
            $table->string('country_rule')->default('all')->after('status'); // all, include, exclude
            $table->json('countries')->nullable()->after('country_rule');
            $table->text('restriction_fallback_message')->nullable()->after('countries');
            $table->foreignId('master_article_id')->nullable()->after('restriction_fallback_message')->constrained('articles')->nullOnDelete();
            $table->string('variant_country', 10)->nullable()->after('master_article_id');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->string('country_rule')->default('all')->after('status'); // all, include, exclude
            $table->json('countries')->nullable()->after('country_rule');
            $table->text('restriction_fallback_message')->nullable()->after('countries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['master_article_id']);
            $table->dropColumn([
                'country_rule',
                'countries',
                'restriction_fallback_message',
                'master_article_id',
                'variant_country',
            ]);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'country_rule',
                'countries',
                'restriction_fallback_message',
            ]);
        });
    }
};
