<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->text('crew_notes')->nullable()->after('terms');
            $table->text('terms_header')->nullable()->after('terms');
            $table->text('terms_footer')->nullable()->after('terms_header');
        });
    }

    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn(['crew_notes', 'terms_header', 'terms_footer']);
        });
    }
};
