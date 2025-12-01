<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_work_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('estimate_area_id')->nullable()->constrained('estimate_areas')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Estimated from Estimate
            $table->decimal('estimated_labor_hours', 10, 2)->default(0);
            $table->decimal('estimated_labor_cost', 12, 2)->default(0);
            $table->decimal('estimated_material_cost', 12, 2)->default(0);
            
            // Actual Tracking (computed from timesheets/expenses)
            $table->decimal('actual_labor_hours', 10, 2)->default(0);
            $table->decimal('actual_labor_cost', 12, 2)->default(0);
            $table->decimal('actual_material_cost', 12, 2)->default(0);
            
            // Status
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamp('completed_at')->nullable();
            
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index('job_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_work_areas');
    }
};
