<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Estimate;

class RecalculateEstimates extends Command
{
    protected $signature = 'estimates:recalculate';
    protected $description = 'Recalculate totals for all estimates based on line items';

    public function handle()
    {
        $this->info('Recalculating estimates...');
        
        $estimates = Estimate::with('items')->get();
        $bar = $this->output->createProgressBar($estimates->count());

        foreach ($estimates as $estimate) {
            $estimate->recalculate();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All estimates have been synchronized.');
    }
}
