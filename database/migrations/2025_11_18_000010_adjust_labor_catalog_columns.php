<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('labor_catalog', function (Blueprint $table) {
            // Descriptive fields
            $table->text('description')->nullable()->after('name');
            $table->text('internal_notes')->nullable()->after('notes');

            // Wage & overtime factor
            $table->decimal('average_wage', 12, 2)->nullable()->after('unit');
            $table->decimal('overtime_factor', 5, 2)->nullable()->after('overtime_rate');

            // Percentages
            $table->decimal('unbillable_percentage', 5, 2)->default(0)->after('is_billable');
            $table->decimal('labor_burden_percentage', 5, 2)->default(0)->after('burden_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('labor_catalog', function (Blueprint $table) {
            $table->dropColumn([
                'description',
                'internal_notes',
                'average_wage',
                'overtime_factor',
                'unbillable_percentage',
                'labor_burden_percentage',
            ]);
        });
    }
};
