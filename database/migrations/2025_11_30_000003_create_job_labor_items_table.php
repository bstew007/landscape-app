<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_labor_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_work_area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('estimate_item_id')->nullable()->constrained('estimate_items')->nullOnDelete();
            $table->foreignId('labor_item_id')->nullable()->constrained('labor_catalog')->nullOnDelete();
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 50)->nullable();
            
            // Estimated
            $table->decimal('estimated_quantity', 10, 2)->default(0);
            $table->decimal('estimated_hours', 10, 2)->default(0);
            $table->decimal('estimated_rate', 10, 2)->default(0);
            $table->decimal('estimated_cost', 12, 2)->default(0);
            
            // Actual (computed from timesheets)
            $table->decimal('actual_hours', 10, 2)->default(0);
            $table->decimal('actual_cost', 12, 2)->default(0);
            
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_labor_items');
    }
};
