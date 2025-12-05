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
        Schema::create('asset_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('child_asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('relationship_type')->nullable(); // e.g., 'contains', 'attached_to', 'towing'
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Prevent duplicate links
            $table->unique(['parent_asset_id', 'child_asset_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_links');
    }
};
