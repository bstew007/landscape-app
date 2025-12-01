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
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('job_id')->constrained('project_jobs')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Employee who worked
            $table->foreignId('job_work_area_id')->nullable()->constrained()->onDelete('set null'); // Specific work area
            
            // Time tracking
            $table->date('work_date');
            $table->dateTime('clock_in');
            $table->dateTime('clock_out')->nullable();
            $table->integer('break_minutes')->default(0); // Break time in minutes
            $table->decimal('total_hours', 5, 2)->nullable(); // Computed field
            
            // Workflow
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->text('notes')->nullable(); // Work performed, issues, etc.
            $table->text('rejection_reason')->nullable(); // Why rejected
            
            // Approval tracking
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['job_id', 'work_date']);
            $table->index(['user_id', 'work_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
