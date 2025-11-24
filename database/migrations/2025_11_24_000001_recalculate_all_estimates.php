<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Estimate;
use App\Services\EstimateItemService;

return new class extends Migration
{
    public function up(): void
    {
        $service = new EstimateItemService();
        
        // Process in chunks to avoid memory issues
        // This will recalculate totals for every estimate based on its current line items
        Estimate::chunk(100, function ($estimates) use ($service) {
            foreach ($estimates as $estimate) {
                $service->recalculateTotals($estimate);
            }
        });
    }

    public function down(): void
    {
        // No rollback needed
    }
};
