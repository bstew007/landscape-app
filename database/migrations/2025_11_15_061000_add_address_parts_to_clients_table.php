<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Add city/state/postal_code to support split address fields on contacts
            $table->string('city', 120)->nullable()->after('address');
            $table->string('state', 80)->nullable()->after('city');
            $table->string('postal_code', 20)->nullable()->after('state');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['city', 'state', 'postal_code']);
        });
    }
};
