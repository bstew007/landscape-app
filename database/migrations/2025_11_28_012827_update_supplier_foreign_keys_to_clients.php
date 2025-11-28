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
        // Drop existing foreign key constraints on materials table
        Schema::table('materials', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        // Drop existing foreign key constraints on estimate_purchase_orders table
        Schema::table('estimate_purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        // Add new foreign key constraints pointing to clients table
        Schema::table('materials', function (Blueprint $table) {
            $table->foreign('supplier_id')
                ->references('id')
                ->on('clients')
                ->nullOnDelete();
        });

        Schema::table('estimate_purchase_orders', function (Blueprint $table) {
            $table->foreign('supplier_id')
                ->references('id')
                ->on('clients')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop clients foreign keys
        Schema::table('materials', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        Schema::table('estimate_purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        // Restore suppliers foreign keys
        Schema::table('materials', function (Blueprint $table) {
            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->nullOnDelete();
        });

        Schema::table('estimate_purchase_orders', function (Blueprint $table) {
            $table->foreign('supplier_id')
                ->references('id')
                ->on('suppliers')
                ->nullOnDelete();
        });
    }
};
