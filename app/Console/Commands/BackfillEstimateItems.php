<?php

namespace App\Console\Commands;

use App\Models\Estimate;
use App\Services\EstimateItemService;
use Illuminate\Console\Command;

class BackfillEstimateItems extends Command
{
    public function __construct(protected EstimateItemService $service)
    {
        parent::__construct();
    }

    /**
     * The console command name and signature.
     */
    protected $signature = 'estimates:backfill-items
        {--force : Process estimates that already have structured items}
        {--dry-run : Preview the migration without writing to the database}';

    /**
     * The console command description.
     */
    protected $description = 'Convert legacy JSON estimate line items into structured estimate_items records.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $this->info(sprintf(
            'Starting backfill (force: %s, dry-run: %s)',
            $force ? 'yes' : 'no',
            $dryRun ? 'yes' : 'no'
        ));

        $estimatesProcessed = 0;
        $itemsCreated = 0;

        Estimate::query()
            ->withCount('items')
            ->select(['id', 'line_items'])
            ->chunkById(100, function ($estimates) use (&$estimatesProcessed, &$itemsCreated, $force, $dryRun) {
                foreach ($estimates as $estimate) {
                    $lineItems = $estimate->line_items;

                    if (!is_array($lineItems) || empty($lineItems)) {
                        continue;
                    }

                    if (!$force && $estimate->items_count > 0) {
                        continue;
                    }

                    $estimatesProcessed++;

                    if ($dryRun) {
                        $itemsCreated += count($lineItems);
                        $this->line(sprintf(
                            '[DRY RUN] Would migrate %d items for estimate #%d',
                            count($lineItems),
                            $estimate->id
                        ));
                        continue;
                    }

                    $this->service->syncFromLegacyLineItems($estimate, $lineItems);
                    $itemsCreated += count($lineItems);
                }
            });

        $this->info(sprintf(
            'Backfill complete. Estimates processed: %d, items %s: %d',
            $estimatesProcessed,
            $dryRun ? 'evaluated' : 'created',
            $itemsCreated
        ));

        if ($dryRun) {
            $this->comment('Dry run mode enabled—no database changes were made.');
        }

        return Command::SUCCESS;
    }
}
