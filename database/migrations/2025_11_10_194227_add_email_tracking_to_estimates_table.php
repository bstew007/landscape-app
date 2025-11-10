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
        Schema::table('estimates', function (Blueprint $table) {
            $table->timestamp('email_sent_at')->nullable()->after('notes');
            $table->timestamp('email_last_sent_at')->nullable()->after('email_sent_at');
            $table->unsignedInteger('email_send_count')->default(0)->after('email_last_sent_at');
            $table->foreignId('email_last_sent_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('email_send_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropForeign(['email_last_sent_by']);
            $table->dropColumn([
                'email_sent_at',
                'email_last_sent_at',
                'email_send_count',
                'email_last_sent_by',
            ]);
        });
    }
};
