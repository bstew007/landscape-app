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
        Schema::table('estimates', function (Blueprint $table) {
            $table->decimal('material_subtotal', 12, 2)->default(0)->after('total');
            $table->decimal('labor_subtotal', 12, 2)->default(0)->after('material_subtotal');
            $table->decimal('fee_total', 12, 2)->default(0)->after('labor_subtotal');
            $table->decimal('discount_total', 12, 2)->default(0)->after('fee_total');
            $table->decimal('tax_total', 12, 2)->default(0)->after('discount_total');
            $table->decimal('grand_total', 12, 2)->default(0)->after('tax_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn([
                'material_subtotal',
                'labor_subtotal',
                'fee_total',
                'discount_total',
                'tax_total',
                'grand_total',
            ]);
        });
    }
};
