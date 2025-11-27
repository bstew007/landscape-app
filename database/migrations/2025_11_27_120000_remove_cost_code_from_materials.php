<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('materials', 'cost_code_id')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->dropConstrainedForeignId('cost_code_id');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('materials', 'cost_code_id')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->foreignId('cost_code_id')->nullable()->after('category')->constrained('cost_codes')->nullOnDelete();
            });
        }
    }
};
