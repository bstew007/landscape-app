<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculations', function (Blueprint $table) {
            if (!Schema::hasColumn('calculations', 'is_template')) {
                // column is created in earlier migration; just guard
                return;
            }
            try {
                $table->index(['is_template', 'calculation_type'], 'calc_is_template_type_idx');
            } catch (\Throwable $e) {
                // ignore if exists
            }
        });
    }

    public function down(): void
    {
        Schema::table('calculations', function (Blueprint $table) {
            try { $table->dropIndex('calc_is_template_type_idx'); } catch (\Throwable $e) { /* ignore */ }
        });
    }
};
