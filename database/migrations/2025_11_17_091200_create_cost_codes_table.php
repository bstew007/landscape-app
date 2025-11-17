<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cost_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->string('qbo_item_id')->nullable();
            $table->string('qbo_item_name')->nullable(); // FullyQualifiedName for display
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_codes');
    }
};
