<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Material;
use Illuminate\Support\Facades\File;

class ImportMaterials extends Command
{
    protected $signature = 'materials:import {file=materials_export.json}';
    protected $description = 'Import materials from JSON file';

    public function handle()
    {
        $filePath = base_path($this->argument('file'));
        
        if (!File::exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("Reading materials from: {$filePath}");
        $materials = json_decode(File::get($filePath), true);
        
        if (!is_array($materials)) {
            $this->error("Invalid JSON format");
            return 1;
        }

        $this->info("Found " . count($materials) . " materials to import");
        
        $bar = $this->output->createProgressBar(count($materials));
        $bar->start();
        
        $imported = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($materials as $data) {
            try {
                // Remove id, created_at, updated_at from import data
                unset($data['id'], $data['created_at'], $data['updated_at']);
                
                // Find by SKU or create new
                $material = Material::updateOrCreate(
                    ['sku' => $data['sku']],
                    $data
                );

                if ($material->wasRecentlyCreated) {
                    $imported++;
                } else {
                    $updated++;
                }
            } catch (\Exception $e) {
                $this->newLine();
                $this->warn("Skipped: {$data['name']} - " . $e->getMessage());
                $skipped++;
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        $this->info("Import complete!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Imported (new)', $imported],
                ['Updated (existing)', $updated],
                ['Skipped (errors)', $skipped],
            ]
        );

        return 0;
    }
}
