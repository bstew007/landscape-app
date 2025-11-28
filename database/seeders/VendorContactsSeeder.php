<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorContactsSeeder extends Seeder
{
    /**
     * Seed vendor contacts for materials catalog.
     * These vendors are used for purchase order generation.
     */
    public function run(): void
    {
        $vendors = [
            ['company_name' => '421 Sand'],
            ['company_name' => 'Adams Supply'],
            ['company_name' => 'American Properties'],
            ['company_name' => 'Farmers Supply'],
            ['company_name' => 'Hoffman Eco Works'],
            ['company_name' => 'Home Depot Inc.', 'email' => 'commercial@homedepot.com'],
            ['company_name' => 'Japanese Maples of Wilmington'],
            ['company_name' => 'Lowes'],
            ['company_name' => 'Martin Marietta'],
            ['company_name' => 'Oakland Plantation'],
            ['company_name' => 'Outdoor Living Supply'],
            ['company_name' => 'Seaside Mulch'],
            ['company_name' => 'SiteOne'],
            ['company_name' => 'The Plant Place'],
            ['company_name' => 'Tinga Nursery'],
            ['company_name' => 'Vinyl Fence Fittings'],
            ['company_name' => 'Witherspoon Rose'],
        ];

        foreach ($vendors as $vendorData) {
            Contact::firstOrCreate(
                [
                    'company_name' => $vendorData['company_name'],
                    'contact_type' => 'vendor',
                ],
                array_merge([
                    'first_name' => '',
                    'last_name' => '',
                    'email' => $vendorData['email'] ?? null,
                    'phone' => null,
                    'address' => null,
                    'city' => null,
                    'state' => null,
                    'postal_code' => null,
                ], $vendorData)
            );
        }

        $this->command->info('Seeded ' . count($vendors) . ' vendor contacts.');
    }
}
