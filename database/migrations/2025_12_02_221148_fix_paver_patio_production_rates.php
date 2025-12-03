<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove duplicate excavation task from paver_patio (use universal excavation rates instead)
        DB::table('production_rates')
            ->where('calculator', 'paver_patio')
            ->where('task', 'excavation')
            ->delete();

        // Fix install_edging unit from sqft to lf (linear feet)
        DB::table('production_rates')
            ->where('calculator', 'paver_patio')
            ->where('task', 'install_edging')
            ->update(['unit' => 'lf']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore excavation task
        DB::table('production_rates')->insert([
            'calculator' => 'paver_patio',
            'task' => 'excavation',
            'rate' => 0.03,
            'unit' => 'sqft',
            'note' => 'Excavate for patio base'
        ]);

        // Revert install_edging unit back to sqft
        DB::table('production_rates')
            ->where('calculator', 'paver_patio')
            ->where('task', 'install_edging')
            ->update(['unit' => 'sqft']);
    }
};
