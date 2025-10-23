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
        Schema::create('production_rates', function (Blueprint $table) {
    $table->id();
    $table->string('task');                 // e.g. base_install
    $table->string('unit');                 // e.g. sqft, lf
    $table->decimal('rate', 8, 4);          // e.g. 0.125
    $table->string('calculator')->nullable(); // e.g. retaining_wall
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_rates');
    }
};
