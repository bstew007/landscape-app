<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculations', function (Blueprint $table) {
            $table->boolean('is_template')->default(false)->after('calculation_type');
            $table->string('template_name')->nullable()->after('is_template');
            $table->foreignId('estimate_id')->nullable()->constrained()->nullOnDelete()->after('site_visit_id');
        });
    }

    public function down(): void
    {
        Schema::table('calculations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('estimate_id');
            $table->dropColumn(['is_template', 'template_name']);
        });
    }
};
