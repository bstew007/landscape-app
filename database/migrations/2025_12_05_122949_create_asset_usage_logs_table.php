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
        Schema::create('asset_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->integer('mileage_out')->nullable();
            $table->integer('mileage_in')->nullable();
            $table->json('inspection_data')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('checked_out'); // checked_out, checked_in
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_usage_logs');
    }
};
