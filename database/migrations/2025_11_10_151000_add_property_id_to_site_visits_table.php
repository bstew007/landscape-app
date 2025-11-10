<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->foreignId('property_id')
                ->nullable()
                ->after('client_id')
                ->constrained('properties')
                ->nullOnDelete();
        });

        // Seed a default property for existing clients so their site visits remain linked.
        $clients = DB::table('clients')->select('id', 'first_name', 'last_name', 'company_name', 'address')->get();

        foreach ($clients as $client) {
            $propertyId = DB::table('properties')->insertGetId([
                'client_id' => $client->id,
                'name' => $client->company_name ?: trim("{$client->first_name} {$client->last_name}") ?: 'Primary Property',
                'type' => $client->company_name ? 'commercial' : 'residential',
                'address_line1' => $client->address,
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('site_visits')
                ->whereNull('property_id')
                ->where('client_id', $client->id)
                ->update(['property_id' => $propertyId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('property_id');
        });
    }
};
