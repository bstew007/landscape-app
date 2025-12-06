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
        Schema::create('equipment_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->string('category')->nullable();
            $table->enum('ownership_type', ['company', 'rental'])->default('company');
            $table->string('unit')->default('hr'); // 'hr' or 'day'
            $table->decimal('hourly_cost', 12, 2)->nullable();
            $table->decimal('daily_cost', 12, 2)->nullable();
            $table->decimal('hourly_rate', 12, 2)->nullable();
            $table->decimal('daily_rate', 12, 2)->nullable();
            $table->decimal('breakeven', 12, 2)->nullable();
            $table->decimal('profit_percent', 5, 2)->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('model')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->timestamps();

            $table->index(['name', 'sku']);
            $table->index('ownership_type');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_catalog');
    }
};
