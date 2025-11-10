<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->boolean('reminder_enabled')->default(true)->after('next_service_date');
            $table->integer('reminder_days_before')->default(7)->after('reminder_enabled');
            $table->timestamp('last_reminder_sent_at')->nullable()->after('reminder_days_before');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['reminder_enabled', 'reminder_days_before', 'last_reminder_sent_at']);
        });
    }
};
