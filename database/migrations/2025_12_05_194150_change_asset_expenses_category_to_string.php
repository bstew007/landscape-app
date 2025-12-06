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
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, so we need to recreate the table
            // This is the recommended approach for SQLite
            
            // Create a new table with the correct schema
            Schema::create('asset_expenses_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('asset_id')->constrained()->onDelete('cascade');
                $table->foreignId('asset_issue_id')->nullable()->constrained()->onDelete('set null');
                $table->string('category', 100); // Changed from enum to string
                $table->string('subcategory')->nullable();
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
                $table->string('qbo_account_id', 100)->nullable();
                $table->timestamp('qbo_synced_at')->nullable();
                $table->timestamps();
            });
            
            // Copy data from old table to new table
            \DB::statement('INSERT INTO asset_expenses_new SELECT * FROM asset_expenses');
            
            // Drop old table
            Schema::dropIfExists('asset_expenses');
            
            // Rename new table to original name
            Schema::rename('asset_expenses_new', 'asset_expenses');
            
        } else {
            // MySQL/PostgreSQL can modify the column type directly
            Schema::table('asset_expenses', function (Blueprint $table) {
                $table->string('category', 100)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // For SQLite, recreate with enum (as string with check constraint)
            Schema::create('asset_expenses_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('asset_id')->constrained()->onDelete('cascade');
                $table->foreignId('asset_issue_id')->nullable()->constrained()->onDelete('set null');
                $table->enum('category', ['fuel', 'repairs', 'general']);
                $table->string('subcategory')->nullable();
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
                $table->string('qbo_account_id', 100)->nullable();
                $table->timestamp('qbo_synced_at')->nullable();
                $table->timestamps();
            });
            
            \DB::statement('INSERT INTO asset_expenses_new SELECT * FROM asset_expenses WHERE category IN ("fuel", "repairs", "general")');
            Schema::dropIfExists('asset_expenses');
            Schema::rename('asset_expenses_new', 'asset_expenses');
            
        } else {
            // MySQL/PostgreSQL
            Schema::table('asset_expenses', function (Blueprint $table) {
                $table->enum('category', ['fuel', 'repairs', 'general'])->change();
            });
        }
    }
};
