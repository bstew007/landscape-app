<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Estimate;
use App\Models\Property;
use App\Models\SiteVisit;
use App\Services\EstimateItemService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstimateSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $clients = [
                [
                    'first_name' => 'Harper',
                    'last_name' => 'King',
                    'company_name' => 'King Landscape',
                    'email' => 'harper.king@example.com',
                    'phone' => '555-2211',
                    'address' => '12 Garden Way',
                ],
                [
                    'first_name' => 'Mia',
                    'last_name' => 'Brooks',
                    'company_name' => 'Brooks HOA',
                    'email' => 'mia.brooks@example.com',
                    'phone' => '555-8832',
                    'address' => '88 Maple Ave',
                ],
            ];

            foreach ($clients as $index => $payload) {
                $client = Client::firstOrCreate(
                    ['email' => $payload['email']],
                    $payload
                );

                $property = Property::firstOrCreate(
                    ['client_id' => $client->id, 'name' => $client->company_name ?: 'Primary Residence'],
                    [
                        'type' => $client->company_name ? 'commercial' : 'residential',
                        'address_line1' => $client->address,
                        'city' => 'Springfield',
                        'state' => 'GA',
                        'postal_code' => '31302',
                        'is_primary' => true,
                    ]
                );

                $visit = SiteVisit::firstOrCreate(
                    [
                        'client_id' => $client->id,
                        'property_id' => $property->id ?? null,
                        'visit_date' => now()->subDays(10 - $index * 3),
                    ],
                    [
                        'notes' => 'Seeded visit for estimate generation.',
                    ]
                );

                $items = [
                    [
                        'label' => 'Hardscape Installation',
                        'qty' => 1,
                        'rate' => 12500,
                        'total' => 12500,
                    ],
                    [
                        'label' => 'Planting & Materials',
                        'qty' => 1,
                        'rate' => 4000,
                        'total' => 4000,
                    ],
                ];

                $estimate = Estimate::updateOrCreate(
                    ['title' => $client->company_name ? "{$client->company_name} Maintenance" : "{$client->first_name} {$client->last_name} Patio"],
                    [
                        'client_id' => $client->id,
                        'property_id' => $property->id,
                        'site_visit_id' => $visit->id,
                        'status' => $index === 0 ? 'pending' : 'draft',
                        'total' => $index === 0 ? 16500 : 7800,
                        'expires_at' => now()->addDays(14 + $index * 7),
                        'line_items' => $items,
                        'notes' => 'Seeded estimate for dashboard metrics.',
                        'terms' => '50% deposit due on acceptance. Remaining balance upon completion.',
                    ]
                );

                app(EstimateItemService::class)->syncFromLegacyLineItems($estimate->fresh(), $items);
            }
        });
    }
}
