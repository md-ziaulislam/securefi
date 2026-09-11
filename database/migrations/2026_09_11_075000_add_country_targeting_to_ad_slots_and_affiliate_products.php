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
        Schema::table('ad_slots', function (Blueprint $table) {
            $table->string('country_rule', 20)->default('all')->after('device'); // 'all', 'include', 'exclude'
            $table->json('countries')->nullable()->after('country_rule');
            $table->text('fallback_code')->nullable()->after('code');
        });

        Schema::table('affiliate_products', function (Blueprint $table) {
            $table->json('country_links')->nullable()->after('affiliate_url');
            $table->text('fallback_affiliate_url')->nullable()->after('country_links');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_slots', function (Blueprint $table) {
            $table->dropColumn(['country_rule', 'countries', 'fallback_code']);
        });

        Schema::table('affiliate_products', function (Blueprint $table) {
            $table->dropColumn(['country_links', 'fallback_affiliate_url']);
        });
    }
};
