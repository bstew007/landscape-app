<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $rows = [
            ['name' => 'Hardscaping', 'sort_order' => 1, 'is_active' => true],
            ['name' => 'Planting', 'sort_order' => 2, 'is_active' => true],
            ['name' => 'Irrigation', 'sort_order' => 3, 'is_active' => true],
            ['name' => 'Lighting', 'sort_order' => 4, 'is_active' => true],
            ['name' => 'Mowing', 'sort_order' => 5, 'is_active' => true],
            ['name' => 'Pruning', 'sort_order' => 6, 'is_active' => true],
            ['name' => 'Fertilization & Turf Apps', 'sort_order' => 7, 'is_active' => true],
            ['name' => 'Cleanups', 'sort_order' => 8, 'is_active' => true],
            ['name' => 'Enhancements', 'sort_order' => 9, 'is_active' => true],
        ];
        foreach ($rows as $r) {
            DB::table('divisions')->insert(array_merge($r, ['created_at' => $now, 'updated_at' => $now]));
        }
    }

    public function down(): void
    {
        DB::table('divisions')->truncate();
    }
};
