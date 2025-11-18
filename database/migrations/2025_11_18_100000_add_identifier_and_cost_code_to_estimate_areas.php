<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimate_areas', function (Blueprint $table) {
            $table->string('identifier')->nullable()->after('name');
            $table->foreignId('cost_code_id')->nullable()->after('identifier')->constrained('cost_codes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('estimate_areas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cost_code_id');
            $table->dropColumn('identifier');
        });
    }
};
