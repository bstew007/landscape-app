<?php

namespace App\Console\Commands;

use App\Models\Estimate;
use App\Services\EstimateItemService;
use Illuminate\Console\Command;

class RecalculateEstimateTotals extends Command
{
    protected $signature = 'estimates:recalculate {--estimate-id= : Recalculate a specific estimate by ID}';
    protected $description = 'Recalculate totals for all estimates based on their line items';

    public function handle(EstimateItemService $service): int
    {
        $estimateId = $this->option('estimate-id');

        if ($estimateId) {
            $estimate = Estimate::find($estimateId);
            if (!$estimate) {
                $this->error("Estimate {$estimateId} not found.");
                return 1;
            }
            $estimates = collect([$estimate]);
            $this->info("Recalculating estimate #{$estimateId}...");
        } else {
            $estimates = Estimate::with('items')->get();
            $this->info("Recalculating " . $estimates->count() . " estimates...");
        }

        $bar = $this->output->createProgressBar($estimates->count());
        $bar->start();

        foreach ($estimates as $estimate) {
            $service->recalculateTotals($estimate);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done! All estimate totals have been recalculated.');

        return 0;
    }
}
