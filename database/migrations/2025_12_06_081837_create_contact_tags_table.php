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
        // Tags table
        Schema::create('contact_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('color')->default('gray'); // For UI display
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Pivot table for contact-tag relationship
        Schema::create('contact_tag_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('contact_tags')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['contact_id', 'tag_id']);
            $table->index('contact_id');
            $table->index('tag_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_tag_pivot');
        Schema::dropIfExists('contact_tags');
    }
};
