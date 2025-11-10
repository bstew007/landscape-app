<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('site_visit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->enum('status', ['draft', 'pending', 'sent', 'approved', 'rejected'])->default('draft');
            $table->decimal('total', 12, 2)->nullable();
            $table->date('expires_at')->nullable();
            $table->json('line_items')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estimates');
    }
};
