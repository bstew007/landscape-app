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
            $table->decimal('custom_price_override', 12, 2)->nullable()->after('description');
            $table->decimal('custom_profit_override', 8, 2)->nullable()->after('custom_price_override');
            $table->string('price_distribution_method', 50)->nullable()->after('custom_profit_override')->default('proportional');
            $table->timestamp('override_applied_at')->nullable()->after('price_distribution_method');
            $table->unsignedBigInteger('override_applied_by')->nullable()->after('override_applied_at');
            
            $table->foreign('override_applied_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estimate_areas', function (Blueprint $table) {
            $table->dropForeign(['override_applied_by']);
            $table->dropColumn([
                'custom_price_override',
                'custom_profit_override',
                'price_distribution_method',
                'override_applied_at',
                'override_applied_by'
            ]);
        });
    }
};
