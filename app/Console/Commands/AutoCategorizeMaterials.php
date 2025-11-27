<?php

namespace App\Console\Commands;

use App\Models\Material;
use Illuminate\Console\Command;

class AutoCategorizeMaterials extends Command
{
    protected $signature = 'materials:auto-categorize {--overwrite : Overwrite existing categories too}';

    protected $description = 'Auto-assign material categories based on keyword mapping.';

    public function handle(): int
    {
        $map = [
            'Soils & Mulch' => ['mulch','soil','topsoil','compost','loam','top soil'],
            'Turf & Seed' => ['turf','sod','seed','grass seed','rye','fescue','bermuda'],
            'Fertilizer' => ['fert','fertilizer','lime','gypsum','urea','ammonium','nitrate','potash'],
            'Chemicals' => ['herbicide','pre-emergent','pre emergent','post-emergent','post emergent','insecticide','pesticide','fungicide','surfactant','adjuvant'],
            'Hardscape' => ['paver','block','brick','stone','boulder','gravel','sand','base','crusher','crush','screenings','flagstone','stepping'],
            'Irrigation' => ['pvc','poly pipe','pipe','fitting','coupling','elbow','tee','valve','nozzle','rotor','spray head','sprinkler','drip','emitter','filter','regulator','swing joint'],
            'Lighting' => ['light','fixture','transformer','lamp','bulb'],
            'Drainage' => ['drain','catch basin','basin','grate','culvert','french drain','n12','corrugated'],
            'Erosion Control' => ['straw','blanket','mat','wattle','silt fence','erosion'],
            'Edging & Borders' => ['edging','edge restraint','snap edge','paver edge'],
            'Concrete & Mortar' => ['mortar','concrete','portland','cement','ready mix','sakrete','quikrete'],
            'Aggregate' => ['aggregate','rip rap','riprap','57','89','crusher run','crush and run','stone dust'],
        ];

        $overwrite = $this->option('overwrite');
        $updated = 0; $skipped = 0; $total = 0;

        Material::chunk(500, function ($chunk) use ($map, $overwrite, &$updated, &$skipped, &$total) {
            foreach ($chunk as $m) {
                $total++;
                if (!$overwrite && !empty($m->category)) { $skipped++; continue; }

                $text = strtolower(($m->name ?? '') . ' ' . ($m->description ?? '') . ' ' . ($m->vendor_name ?? '') . ' ' . ($m->sku ?? ''));
                $matchedCat = null;
                foreach ($map as $category => $keywords) {
                    foreach ($keywords as $kw) {
                        if (str_contains($text, strtolower($kw))) {
                            $matchedCat = $category;
                            break 2;
                        }
                    }
                }

                if ($matchedCat && $m->category !== $matchedCat) {
                    $m->category = $matchedCat;
                    $m->save();
                    $updated++;
                } else {
                    $skipped++;
                }
            }
        });

        $this->info("Processed {$total} materials. Updated: {$updated}. Skipped: {$skipped}.");
        $this->info('Use --overwrite to recategorize existing ones.');
        return Command::SUCCESS;
    }
}
