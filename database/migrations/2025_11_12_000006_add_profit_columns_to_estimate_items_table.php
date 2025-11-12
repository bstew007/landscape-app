<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->decimal('unit_price', 12, 2)->default(0)->after('unit_cost');
            $table->decimal('margin_rate', 6, 4)->default(0)->after('unit_price');
            $table->decimal('cost_total', 12, 2)->default(0)->after('line_total');
            $table->decimal('margin_total', 12, 2)->default(0)->after('cost_total');
        });

        DB::statement('UPDATE estimate_items SET unit_price = unit_cost, margin_rate = 0, cost_total = quantity * unit_cost, margin_total = line_total - (quantity * unit_cost)');
    }

    public function down(): void
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'margin_rate', 'cost_total', 'margin_total']);
        });
    }
};
