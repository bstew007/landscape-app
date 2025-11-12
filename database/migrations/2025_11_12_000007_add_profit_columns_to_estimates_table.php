<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->decimal('material_cost_total', 12, 2)->default(0)->after('material_subtotal');
            $table->decimal('labor_cost_total', 12, 2)->default(0)->after('labor_subtotal');
            $table->decimal('revenue_total', 12, 2)->default(0)->after('grand_total');
            $table->decimal('cost_total', 12, 2)->default(0)->after('revenue_total');
            $table->decimal('profit_total', 12, 2)->default(0)->after('cost_total');
            $table->decimal('net_profit_total', 12, 2)->default(0)->after('profit_total');
            $table->decimal('profit_margin', 5, 2)->default(0)->after('net_profit_total');
            $table->decimal('net_margin', 5, 2)->default(0)->after('profit_margin');
        });

        DB::table('estimates')->update([
            'material_cost_total' => 0,
            'labor_cost_total' => 0,
            'revenue_total' => DB::raw('(material_subtotal + labor_subtotal + fee_total - discount_total)'),
            'cost_total' => 0,
            'profit_total' => 0,
            'net_profit_total' => DB::raw('(material_subtotal + labor_subtotal + fee_total - discount_total)'),
            'profit_margin' => 0,
            'net_margin' => 0,
        ]);
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn([
                'material_cost_total',
                'labor_cost_total',
                'revenue_total',
                'cost_total',
                'profit_total',
                'net_profit_total',
                'profit_margin',
                'net_margin',
            ]);
        });
    }
};
