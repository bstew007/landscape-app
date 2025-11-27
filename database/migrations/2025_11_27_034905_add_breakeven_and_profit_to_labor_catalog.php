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
        Schema::table('labor_catalog', function (Blueprint $table) {
            $table->decimal('breakeven', 12, 2)->nullable()->after('base_rate');
            $table->decimal('profit_percent', 5, 2)->nullable()->after('breakeven');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('labor_catalog', function (Blueprint $table) {
            $table->dropColumn(['breakeven', 'profit_percent']);
        });
    }
};
