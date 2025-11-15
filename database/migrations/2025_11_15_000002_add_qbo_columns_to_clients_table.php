<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('qbo_customer_id')->nullable()->index();
            $table->string('qbo_sync_token')->nullable();
            $table->timestamp('qbo_last_synced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['qbo_customer_id', 'qbo_sync_token', 'qbo_last_synced_at']);
        });
    }
};
