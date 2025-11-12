<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_budgets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Default Budget');
            $table->unsignedSmallInteger('year')->nullable();
            $table->boolean('is_active')->default(false);
            $table->date('effective_from')->nullable();
            $table->decimal('desired_profit_margin', 6, 4)->default(0.20); // e.g., 0.15 = 15%
            $table->json('inputs')->nullable(); // raw inputs
            $table->json('outputs')->nullable(); // computed outputs
            $table->timestamps();

            $table->index(['is_active', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_budgets');
    }
};
