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
        // Create universal excavation rates (shared across all calculators)
        DB::table('production_rates')->updateOrInsert(
            ['calculator' => 'excavation', 'task' => 'excavation_manual'],
            ['rate' => 0.014, 'unit' => 'sqft', 'note' => 'Manual excavation with shovels - general rate']
        );

        DB::table('production_rates')->updateOrInsert(
            ['calculator' => 'excavation', 'task' => 'excavation_mini_skid'],
            ['rate' => 0.14, 'unit' => 'cy', 'note' => 'Mini skid steer excavation ~7-8 cy/hr']
        );

        DB::table('production_rates')->updateOrInsert(
            ['calculator' => 'excavation', 'task' => 'excavation_skid_steer'],
            ['rate' => 0.1, 'unit' => 'cy', 'note' => 'Skid steer excavation ~10 cy/hr']
        );

        // Update syn_turf rates - remove old tasks, keep new ones
        // Remove old tasks that were replaced
        DB::table('production_rates')
            ->where('calculator', 'syn_turf')
            ->whereIn('task', ['base', 'edging', 'excavation', 'infill', 'syn_turf_install'])
            ->delete();

        // Add/update new syn_turf tasks
        DB::table('production_rates')->updateOrInsert(
            ['calculator' => 'syn_turf', 'task' => 'base_install'],
            ['rate' => 0.2, 'unit' => 'cy', 'note' => 'Place, grade, compact base (mini skid + compactor)']
        );

        DB::table('production_rates')->updateOrInsert(
            ['calculator' => 'syn_turf', 'task' => 'turf_install'],
            ['rate' => 0.022, 'unit' => 'sqft', 'note' => 'Roll out, trim, seam, detail edges, and secure']
        );

        DB::table('production_rates')->updateOrInsert(
            ['calculator' => 'syn_turf', 'task' => 'edging_install'],
            ['rate' => 0.06, 'unit' => 'lf', 'note' => 'Install edging with stakes/spikes']
        );

        DB::table('production_rates')->updateOrInsert(
            ['calculator' => 'syn_turf', 'task' => 'infill_application'],
            ['rate' => 0.0025, 'unit' => 'sqft', 'note' => 'Distribute infill material']
        );

        // Also add excavation methods to syn_turf (these reference the universal rates)
        DB::table('production_rates')->updateOrInsert(
            ['calculator' => 'syn_turf', 'task' => 'excavation_manual'],
            ['rate' => 0.014, 'unit' => 'sqft', 'note' => 'Manual excavation for turf removal']
        );

        DB::table('production_rates')->updateOrInsert(
            ['calculator' => 'syn_turf', 'task' => 'excavation_mini_skid'],
            ['rate' => 0.14, 'unit' => 'cy', 'note' => 'Mini skid excavation for turf']
        );

        DB::table('production_rates')->updateOrInsert(
            ['calculator' => 'syn_turf', 'task' => 'excavation_skid_steer'],
            ['rate' => 0.1, 'unit' => 'cy', 'note' => 'Skid steer excavation for turf']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove universal excavation rates
        DB::table('production_rates')
            ->where('calculator', 'excavation')
            ->whereIn('task', ['excavation_manual', 'excavation_mini_skid', 'excavation_skid_steer'])
            ->delete();

        // Restore old syn_turf tasks
        DB::table('production_rates')
            ->where('calculator', 'syn_turf')
            ->whereIn('task', ['base_install', 'turf_install', 'edging_install', 'infill_application', 'excavation_manual', 'excavation_mini_skid', 'excavation_skid_steer'])
            ->delete();

        // Re-add old tasks
        DB::table('production_rates')->insert([
            ['calculator' => 'syn_turf', 'task' => 'base', 'rate' => 0.0180, 'unit' => 'sqft', 'note' => 'Install and compact 3–4 in. aggregate base'],
            ['calculator' => 'syn_turf', 'task' => 'edging', 'rate' => 0.0600, 'unit' => 'linear ft', 'note' => 'Install edging'],
            ['calculator' => 'syn_turf', 'task' => 'excavation', 'rate' => 0.0140, 'unit' => 'sqft', 'note' => 'General turf removal'],
            ['calculator' => 'syn_turf', 'task' => 'infill', 'rate' => 0.0025, 'unit' => 'sqft', 'note' => 'Distribute infill'],
            ['calculator' => 'syn_turf', 'task' => 'syn_turf_install', 'rate' => 0.0220, 'unit' => 'sqft', 'note' => 'Roll out and install turf'],
        ]);
    }
};
