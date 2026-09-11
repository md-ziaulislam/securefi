<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('source')->nullable()->after('status'); // web, import, api
            $table->string('ip_address')->nullable()->after('source');
            $table->timestamp('confirmed_at')->nullable()->after('ip_address');
            $table->timestamp('unsubscribed_at')->nullable()->after('confirmed_at');
            $table->string('unsubscribe_token')->nullable()->unique()->after('unsubscribed_at');
        });
    }

    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropColumn(['name', 'source', 'ip_address', 'confirmed_at', 'unsubscribed_at', 'unsubscribe_token']);
        });
    }
};
