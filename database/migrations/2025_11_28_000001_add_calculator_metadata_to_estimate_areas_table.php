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
        Schema::table('estimate_areas', function (Blueprint $table) {
            // Link to source calculator
            $table->foreignId('calculation_id')
                ->nullable()
                ->after('cost_code_id')
                ->constrained('calculations')
                ->nullOnDelete()
                ->comment('Source calculator if created from import');
            
            $table->foreignId('site_visit_id')
                ->nullable()
                ->after('calculation_id')
                ->constrained('site_visits')
                ->nullOnDelete()
                ->comment('Source site visit if applicable');
            
            // Planning metadata
            $table->decimal('planned_hours', 10, 2)
                ->nullable()
                ->after('site_visit_id')
                ->comment('Total planned labor hours from calculator');
            
            $table->integer('crew_size')
                ->nullable()
                ->after('planned_hours')
                ->comment('Recommended crew size from calculator');
            
            $table->decimal('drive_time_hours', 10, 2)
                ->nullable()
                ->after('crew_size')
                ->comment('Calculated drive time');
            
            $table->decimal('overhead_percent', 5, 2)
                ->nullable()
                ->after('drive_time_hours')
                ->comment('Total overhead percentage applied');
            
            // Flexible metadata storage
            $table->json('calculator_metadata')
                ->nullable()
                ->after('overhead_percent')
                ->comment('Additional calculator parameters and settings');
            
            // Indexes for performance
            $table->index('calculation_id', 'idx_areas_calculation');
            $table->index('site_visit_id', 'idx_areas_site_visit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimate_areas', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_areas_calculation');
            $table->dropIndex('idx_areas_site_visit');
            
            // Drop foreign keys
            $table->dropForeign(['calculation_id']);
            $table->dropForeign(['site_visit_id']);
            
            // Drop columns
            $table->dropColumn([
                'calculation_id',
                'site_visit_id',
                'planned_hours',
                'crew_size',
                'drive_time_hours',
                'overhead_percent',
                'calculator_metadata',
            ]);
        });
    }
};
