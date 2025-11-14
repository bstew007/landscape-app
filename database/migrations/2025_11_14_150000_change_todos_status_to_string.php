<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            // Switch from enum to string to allow new statuses like 'future'
            $table->string('status')->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('todos', function (Blueprint $table) {
            // Best effort: revert to enum including 'future' to avoid data loss on rollback
            try {
                $table->enum('status', ['future','pending','in_progress','completed'])->default('pending')->change();
            } catch (\Throwable $e) {
                // Some drivers (sqlite) may not support changing back cleanly; ignore
            }
        });
    }
};
