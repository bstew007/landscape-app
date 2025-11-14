<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('calculations', function (Blueprint $table) {
            if (!Schema::hasColumn('calculations', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('property_id');
            }
            if (!Schema::hasColumn('calculations', 'is_global')) {
                $table->boolean('is_global')->default(false)->after('template_scope');
            }
            // Helpful composite index for gallery / templates listing
            $table->index(['is_template', 'calculation_type'], 'calc_template_type_idx');
        });
    }

    public function down(): void
    {
        Schema::table('calculations', function (Blueprint $table) {
            if (Schema::hasColumn('calculations', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('calculations', 'is_global')) {
                $table->dropColumn('is_global');
            }
            $table->dropIndex('calc_template_type_idx');
        });
    }
};
