<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimate_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('job_number', 50)->unique();
            $table->string('title');
            $table->enum('status', ['scheduled', 'in_progress', 'on_hold', 'completed', 'cancelled'])->default('scheduled');
            
            // Client & Location
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            
            // Financial Tracking
            $table->decimal('estimated_revenue', 12, 2)->default(0);
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->decimal('estimated_profit', 12, 2)->default(0);
            $table->decimal('actual_labor_cost', 12, 2)->default(0);
            $table->decimal('actual_material_cost', 12, 2)->default(0);
            $table->decimal('actual_total_cost', 12, 2)->default(0);
            
            // Scheduling
            $table->date('scheduled_start_date')->nullable();
            $table->date('scheduled_end_date')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();
            
            // Assignment
            $table->foreignId('foreman_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('crew_size')->nullable();
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cost_code_id')->nullable()->constrained('cost_codes')->nullOnDelete();
            
            // Metadata
            $table->text('notes')->nullable();
            $table->text('crew_notes')->nullable();
            
            // QuickBooks
            $table->string('qbo_job_id')->nullable();
            $table->timestamp('qbo_synced_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('foreman_id');
            $table->index(['scheduled_start_date', 'scheduled_end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
