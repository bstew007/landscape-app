<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Materials catalog
        Schema::table('materials', function (Blueprint $table) {
            $table->foreignId('cost_code_id')->nullable()->after('category')->constrained('cost_codes')->nullOnDelete();
        });

        // Labor catalog
        Schema::table('labor_catalog', function (Blueprint $table) {
            $table->foreignId('cost_code_id')->nullable()->after('type')->constrained('cost_codes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cost_code_id');
        });
        Schema::table('labor_catalog', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cost_code_id');
        });
    }
};
