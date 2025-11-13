<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculations', function (Blueprint $table) {
            // Drop existing foreign key to change column nullability
            if (Schema::hasColumn('calculations', 'site_visit_id')) {
                try { $table->dropForeign(['site_visit_id']); } catch (\Throwable $e) { /* ignore if not exists */ }
                // Make nullable
                $table->unsignedBigInteger('site_visit_id')->nullable()->change();
                // Re-add foreign key with cascade
                $table->foreign('site_visit_id')->references('id')->on('site_visits')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calculations', function (Blueprint $table) {
            // Revert to not-null (may fail if rows with null exist)
            try { $table->dropForeign(['site_visit_id']); } catch (\Throwable $e) { /* ignore */ }
            $table->unsignedBigInteger('site_visit_id')->nullable(false)->change();
            $table->foreign('site_visit_id')->references('id')->on('site_visits')->onDelete('cascade');
        });
    }
};
