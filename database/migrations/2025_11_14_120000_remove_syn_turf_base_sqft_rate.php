<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('production_rates')
            ->where('calculator', 'syn_turf')
            ->where('task', 'base')
            ->delete();
    }

    public function down(): void
    {
        // Intentionally no-op: legacy 'base' (sqft) should not be restored.
    }
};
