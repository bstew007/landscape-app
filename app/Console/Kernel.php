<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<int, class-string<\Illuminate\Console\Command>>
     */
    protected $commands = [
        \App\Console\Commands\AuditCatalogLinks::class,
        \App\Console\Commands\AutoCategorizeMaterials::class,
        \App\Console\Commands\BackfillEstimateItems::class,
        \App\Console\Commands\ClearOrphanedCatalogLinks::class,
        \App\Console\Commands\DiagnoseCatalogItem::class,
        \App\Console\Commands\FixBrokenCatalogLinks::class,
        \App\Console\Commands\PollQboCdc::class,
        \App\Console\Commands\RecalculateEstimateTotals::class,
        \App\Console\Commands\RecalculateEstimates::class,
        \App\Console\Commands\SyncMaterialCategories::class,
        \App\Console\Commands\MergeMaterialCategories::class,
        \App\Console\Commands\CategorizeShrubsByUnit::class,
        \App\Console\Commands\MergeFenceCategories::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
