<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_slots', function (Blueprint $table) {
            $table->string('position', 50)->change();
            $table->unsignedBigInteger('impressions')->default(0)->after('status');
            $table->unsignedBigInteger('clicks')->default(0)->after('impressions');
            $table->string('device', 20)->default('all')->after('clicks');
            $table->dateTime('start_date')->nullable()->after('device');
            $table->dateTime('end_date')->nullable()->after('start_date');
        });

        Schema::table('affiliate_products', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->decimal('rating', 2, 1)->default(5.0)->after('price');
            $table->string('badge_text')->nullable()->after('rating');
            $table->string('button_text')->nullable()->default('Claim Deal & Check Price')->after('badge_text');
            $table->foreignId('category_id')->nullable()->after('source_site')->constrained('categories')->nullOnDelete();
            $table->boolean('is_featured')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('ad_slots', function (Blueprint $table) {
            $table->dropColumn(['impressions', 'clicks', 'device', 'start_date', 'end_date']);
        });

        Schema::table('affiliate_products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['slug', 'rating', 'badge_text', 'button_text', 'category_id', 'is_featured']);
        });
    }
};
