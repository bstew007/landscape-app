<?php

namespace App\Observers;

use App\Models\Timesheet;
use App\Models\Job;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TimesheetApproved;
use App\Notifications\TimesheetRejected;

class TimesheetObserver
{
    /**
     * Handle the Timesheet "approved" event.
     */
    public function updated(Timesheet $timesheet): void
    {
        if ($timesheet->isDirty('status')) {
            $originalStatus = $timesheet->getOriginal('status');
            $newStatus = $timesheet->status;

            // Timesheet was approved
            if ($originalStatus === 'submitted' && $newStatus === 'approved') {
                $this->handleApproval($timesheet);
            }

            // Timesheet was rejected
            if ($originalStatus === 'submitted' && $newStatus === 'rejected') {
                $this->handleRejection($timesheet);
            }
        }
    }

    /**
     * Handle timesheet approval
     */
    protected function handleApproval(Timesheet $timesheet): void
    {
        // Update job actual labor costs
        $job = $timesheet->job;
        $laborRate = $timesheet->workArea?->labor_rate ?? 25.00;
        $laborCost = $timesheet->total_hours * $laborRate;

        // Increment actual labor cost
        $job->increment('actual_labor_cost', $laborCost);

        // Log activity (optional - could integrate with activity log package)
        \Log::info("Timesheet #{$timesheet->id} approved", [
            'job_id' => $job->id,
            'user_id' => $timesheet->user_id,
            'hours' => $timesheet->total_hours,
            'cost' => $laborCost,
        ]);

        // Send notification to employee (optional)
        // Uncomment if you create the notification class
        // $timesheet->user->notify(new TimesheetApproved($timesheet));
    }

    /**
     * Handle timesheet rejection
     */
    protected function handleRejection(Timesheet $timesheet): void
    {
        // Log activity
        \Log::info("Timesheet #{$timesheet->id} rejected", [
            'job_id' => $timesheet->job_id,
            'user_id' => $timesheet->user_id,
            'reason' => $timesheet->rejection_reason,
        ]);

        // Send notification to employee (optional)
        // Uncomment if you create the notification class
        // $timesheet->user->notify(new TimesheetRejected($timesheet));
    }
}
