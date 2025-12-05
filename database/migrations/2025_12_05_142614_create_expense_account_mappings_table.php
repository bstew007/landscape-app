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
        Schema::create('expense_account_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('category')->unique(); // fuel, repairs, general
            $table->string('category_label');
            $table->string('qbo_account_id')->nullable();
            $table->string('qbo_account_name')->nullable();
            $table->string('qbo_account_type')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_account_mappings');
    }
};
