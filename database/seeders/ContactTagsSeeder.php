<?php

namespace Database\Seeders;

use App\Models\ContactTag;
use Illuminate\Database\Seeder;

class ContactTagsSeeder extends Seeder
{
    /**
     * Seed default contact tags for categorizing vendors and other contacts.
     */
    public function run(): void
    {
        $tags = [
            [
                'name' => 'Vendor',
                'slug' => 'vendor',
                'color' => 'blue',
                'description' => 'Material and supply vendors',
            ],
            [
                'name' => 'Rental',
                'slug' => 'rental',
                'color' => 'purple',
                'description' => 'Equipment rental companies',
            ],
            [
                'name' => 'Preferred',
                'slug' => 'preferred',
                'color' => 'green',
                'description' => 'Preferred vendors with negotiated rates',
            ],
            [
                'name' => 'Local',
                'slug' => 'local',
                'color' => 'amber',
                'description' => 'Local area vendors',
            ],
            [
                'name' => 'Wholesale',
                'slug' => 'wholesale',
                'color' => 'indigo',
                'description' => 'Wholesale suppliers',
            ],
        ];

        foreach ($tags as $tagData) {
            ContactTag::firstOrCreate(
                ['slug' => $tagData['slug']],
                $tagData
            );
        }

        $this->command->info('Seeded ' . count($tags) . ' contact tags.');
    }
}
