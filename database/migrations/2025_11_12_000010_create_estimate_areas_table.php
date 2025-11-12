<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimate_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimate_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('estimate_items', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->after('calculation_id')->constrained('estimate_areas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('estimate_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('area_id');
        });
        Schema::dropIfExists('estimate_areas');
    }
};
