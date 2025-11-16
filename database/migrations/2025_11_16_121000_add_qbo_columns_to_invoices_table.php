<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('qbo_invoice_id')->nullable()->after('pdf_path');
            $table->string('qbo_sync_token')->nullable()->after('qbo_invoice_id');
            $table->timestamp('qbo_last_synced_at')->nullable()->after('qbo_sync_token');
            $table->string('qbo_doc_number')->nullable()->after('qbo_last_synced_at');
            $table->decimal('qbo_total', 12, 2)->nullable()->after('qbo_doc_number');
            $table->decimal('qbo_balance', 12, 2)->nullable()->after('qbo_total');
            $table->string('qbo_status')->nullable()->after('qbo_balance');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['qbo_invoice_id','qbo_sync_token','qbo_last_synced_at','qbo_doc_number','qbo_total','qbo_balance','qbo_status']);
        });
    }
};
