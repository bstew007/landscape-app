<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculations', function (Blueprint $table) {
            $table->enum('template_scope', ['global', 'client', 'property'])->nullable()->after('template_name');
            $table->foreignId('client_id')->nullable()->after('template_scope')->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('property_id');
        });
    }

    public function down(): void
    {
        Schema::table('calculations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('client_id');
            $table->dropConstrainedForeignId('property_id');
            $table->dropColumn(['template_scope', 'is_active']);
        });
    }
};
