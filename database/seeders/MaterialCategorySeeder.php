<?php

namespace Database\Seeders;

use App\Models\MaterialCategory;
use Illuminate\Database\Seeder;

class MaterialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            "Aggregates & Sand",
            "Chemicals",
            "Fences 4' Vinyl",
            "Fences 8' Vinyl",
            "Fertilizer",
            "Hardscapes",
            "Heath & Safety",
            "Holiday Lighting",
            "Irrigation",
            "Jobsite Consumables",
            "Lighting",
            "Lumber",
            "Masonry",
            "Metal Work",
            "Natural Stone Paving",
            "Natural Stone Retaining Walls",
            "Pavers",
            "Plant Material - Annuals",
            "Plant Material - Perennials",
            "Plant Material - Shrubs",
            "Plant Material - Trees",
            "Plants - Grassses",
            "Plants - Palms",
            "Plants - Perennials",
            "Plants - Trees & Shrubs",
        ];

        foreach ($categories as $index => $name) {
            MaterialCategory::updateOrCreate(
                ['name' => $name],
                [
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );
        }
    }
}
