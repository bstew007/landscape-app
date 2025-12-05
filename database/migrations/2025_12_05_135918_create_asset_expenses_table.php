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
        Schema::create('asset_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_issue_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('category', ['fuel', 'repairs', 'general']);
            $table->string('subcategory')->nullable(); // gas/diesel/oil, insurance/registration/permit
            $table->string('vendor')->nullable();
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->integer('odometer_hours')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('receipt_number')->nullable();
            $table->boolean('is_reimbursable')->default(false);
            $table->foreignId('submitted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('qbo_expense_id')->nullable();
            $table->timestamp('qbo_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_expenses');
    }
};
