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
            
            // Timesheet was unapproved (approved → any other status)
            if ($originalStatus === 'approved' && $newStatus !== 'approved') {
                $this->handleUnapproval($timesheet);
            }
        }
    }

    /**
     * Handle the Timesheet "deleting" event.
     */
    public function deleting(Timesheet $timesheet): void
    {
        // If deleting an approved timesheet, reverse the cost updates
        if ($timesheet->status === 'approved') {
            $this->handleUnapproval($timesheet);
        }
    }

    /**
     * Handle timesheet approval
     */
    protected function handleApproval(Timesheet $timesheet): void
    {
        $job = $timesheet->job;
        $workArea = $timesheet->workArea;
        
        // Get labor rate - calculate from work area's labor items or use default
        $laborRate = $this->getLaborRate($workArea);
        $laborCost = $timesheet->total_hours * $laborRate;

        // Update job-level actuals
        $job->increment('actual_labor_cost', $laborCost);
        
        // Recalculate total cost (labor + materials)
        $job->actual_total_cost = $job->actual_labor_cost + $job->actual_material_cost;
        $job->save();

        // Update work area actuals if work area is specified
        if ($workArea) {
            // Use DB query to update only specific columns, avoiding computed attributes
            \DB::table('job_work_areas')
                ->where('id', $workArea->id)
                ->update([
                    'actual_labor_hours' => \DB::raw('actual_labor_hours + ' . $timesheet->total_hours),
                    'actual_labor_cost' => \DB::raw('actual_labor_cost + ' . $laborCost),
                    'updated_at' => now(),
                ]);
        }

        // Log activity
        \Log::info("Timesheet #{$timesheet->id} approved", [
            'job_id' => $job->id,
            'work_area_id' => $workArea?->id,
            'user_id' => $timesheet->user_id,
            'hours' => $timesheet->total_hours,
            'labor_rate' => $laborRate,
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

    /**
     * Handle timesheet unapproval or deletion
     * Reverses the cost updates that were applied during approval
     */
    protected function handleUnapproval(Timesheet $timesheet): void
    {
        $job = $timesheet->job;
        $workArea = $timesheet->workArea;
        
        // Get labor rate - calculate from work area's labor items or use default
        $laborRate = $this->getLaborRate($workArea);
        $laborCost = $timesheet->total_hours * $laborRate;

        // Reverse job-level actuals
        $job->decrement('actual_labor_cost', $laborCost);
        
        // Recalculate total cost (labor + materials)
        $job->actual_total_cost = $job->actual_labor_cost + $job->actual_material_cost;
        $job->save();

        // Reverse work area actuals if work area is specified
        if ($workArea) {
            // Use DB query to update only specific columns, avoiding computed attributes
            \DB::table('job_work_areas')
                ->where('id', $workArea->id)
                ->update([
                    'actual_labor_hours' => \DB::raw('actual_labor_hours - ' . $timesheet->total_hours),
                    'actual_labor_cost' => \DB::raw('actual_labor_cost - ' . $laborCost),
                    'updated_at' => now(),
                ]);
        }

        // Log activity
        \Log::info("Timesheet #{$timesheet->id} unapproved/deleted - costs reversed", [
            'job_id' => $job->id,
            'work_area_id' => $workArea?->id,
            'user_id' => $timesheet->user_id,
            'hours' => $timesheet->total_hours,
            'labor_rate' => $laborRate,
            'cost' => $laborCost,
        ]);
    }

    /**
     * Get the labor rate for a work area
     * Calculates weighted average from labor items or uses default
     */
    protected function getLaborRate($workArea): float
    {
        if (!$workArea) {
            return 25.00; // Default hourly rate
        }

        // Calculate weighted average labor rate from work area's labor items
        $laborItems = $workArea->laborItems()->get();
        
        if ($laborItems->isEmpty()) {
            return 25.00; // Default if no labor items
        }

        $totalHours = $laborItems->sum('estimated_hours');
        $totalCost = $laborItems->sum('estimated_cost');

        if ($totalHours > 0) {
            return $totalCost / $totalHours;
        }

        // Fallback to average of rates
        $avgRate = $laborItems->avg('estimated_rate');
        return $avgRate > 0 ? $avgRate : 25.00;
    }
}
