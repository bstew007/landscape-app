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
        Schema::create('labor_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('crew');
            $table->string('unit')->default('hr');
            $table->decimal('base_rate', 12, 2)->default(0);
            $table->decimal('overtime_rate', 12, 2)->nullable();
            $table->decimal('burden_percentage', 5, 2)->default(0);
            $table->boolean('is_billable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['name', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labor_catalog');
    }
};
