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
        // Drop foreign key constraints temporarily
        Schema::table('materials', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        Schema::table('estimate_purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
        });

        // Create a mapping table to track old supplier_id -> new client_id
        DB::statement('CREATE TEMPORARY TABLE supplier_to_client_mapping (
            old_supplier_id INT PRIMARY KEY,
            new_client_id INT NOT NULL
        )');

        // Copy all suppliers into clients table as vendors
        $suppliers = DB::table('suppliers')->get();
        
        foreach ($suppliers as $supplier) {
            // Check if a contact with this name already exists
            $existingClient = DB::table('clients')
                ->whereRaw('LOWER(company_name) = ?', [strtolower($supplier->name)])
                ->orWhereRaw('LOWER(CONCAT(first_name, " ", last_name)) = ?', [strtolower($supplier->name)])
                ->first();

            if ($existingClient) {
                // Update existing contact to be a vendor and merge supplier data
                DB::table('clients')
                    ->where('id', $existingClient->id)
                    ->update([
                        'contact_type' => 'vendor',
                        'company_name' => $supplier->company_name ?: $existingClient->company_name,
                        'email' => $supplier->email ?: $existingClient->email,
                        'phone' => $supplier->phone ?: $existingClient->phone,
                        'address' => $supplier->address ?: $existingClient->address,
                        'city' => $supplier->city ?: $existingClient->city,
                        'state' => $supplier->state ?: $existingClient->state,
                        'postal_code' => $supplier->zip ?: $existingClient->postal_code,
                        'qbo_vendor_id' => $supplier->qbo_vendor_id,
                        'updated_at' => now(),
                    ]);
                
                $newClientId = $existingClient->id;
            } else {
                // Create new contact from supplier
                $newClientId = DB::table('clients')->insertGetId([
                    'first_name' => '',
                    'last_name' => $supplier->contact_person ?: '',
                    'company_name' => $supplier->company_name ?: $supplier->name,
                    'contact_type' => 'vendor',
                    'email' => $supplier->email,
                    'phone' => $supplier->phone,
                    'address' => $supplier->address,
                    'city' => $supplier->city,
                    'state' => $supplier->state,
                    'postal_code' => $supplier->zip,
                    'qbo_vendor_id' => $supplier->qbo_vendor_id,
                    'created_at' => $supplier->created_at ?? now(),
                    'updated_at' => $supplier->updated_at ?? now(),
                ]);
            }

            // Record the mapping
            DB::table('supplier_to_client_mapping')->insert([
                'old_supplier_id' => $supplier->id,
                'new_client_id' => $newClientId,
            ]);
        }

        // Update materials.supplier_id to point to the new client_id
        $mappings = DB::table('supplier_to_client_mapping')->get();
        foreach ($mappings as $mapping) {
            DB::table('materials')
                ->where('supplier_id', $mapping->old_supplier_id)
                ->update(['supplier_id' => $mapping->new_client_id]);
        }

        // Update estimate_purchase_orders.supplier_id to point to the new client_id
        foreach ($mappings as $mapping) {
            DB::table('estimate_purchase_orders')
                ->where('supplier_id', $mapping->old_supplier_id)
                ->update(['supplier_id' => $mapping->new_client_id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible
        // Would need to recreate suppliers table and copy data back
        throw new \Exception('This migration cannot be reversed. Restore from backup if needed.');
    }
};
