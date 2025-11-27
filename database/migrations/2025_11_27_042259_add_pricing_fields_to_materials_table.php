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
        Schema::table('materials', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->nullable()->after('unit_cost');
            $table->decimal('breakeven', 10, 2)->nullable()->after('unit_price');
            $table->decimal('profit_percent', 8, 2)->nullable()->after('breakeven');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'breakeven', 'profit_percent']);
        });
    }
};
