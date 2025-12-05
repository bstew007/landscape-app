<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, set any NULL cost_code_id to the first active cost code
        $defaultCostCodeId = DB::table('cost_codes')
            ->where('is_active', true)
            ->whereNotNull('qbo_item_id')
            ->value('id');
        
        if (!$defaultCostCodeId) {
            throw new \Exception('No active cost code with QBO mapping found. Please create one before running this migration.');
        }
        
        DB::table('estimates')
            ->whereNull('cost_code_id')
            ->update(['cost_code_id' => $defaultCostCodeId]);
        
        // Drop the existing foreign key with SET NULL
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropForeign(['cost_code_id']);
        });
        
        // Make the column NOT NULL (works with both MySQL and SQLite)
        Schema::table('estimates', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_code_id')->nullable(false)->change();
        });
        
        // Re-add foreign key with RESTRICT (prevents deletion of cost codes in use)
        Schema::table('estimates', function (Blueprint $table) {
            $table->foreign('cost_code_id')
                ->references('id')
                ->on('cost_codes')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the RESTRICT foreign key
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropForeign(['cost_code_id']);
        });
        
        // Make column nullable (works with both MySQL and SQLite)
        Schema::table('estimates', function (Blueprint $table) {
            $table->unsignedBigInteger('cost_code_id')->nullable()->change();
        });
        
        // Restore the original SET NULL foreign key
        Schema::table('estimates', function (Blueprint $table) {
            $table->foreign('cost_code_id')
                ->references('id')
                ->on('cost_codes')
                ->onDelete('set null');
        });
    }
};
