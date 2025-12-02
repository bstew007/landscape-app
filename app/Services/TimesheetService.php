<?php

namespace App\Services;

use App\Models\Timesheet;
use App\Models\Job;
use App\Models\JobWorkArea;
use App\Models\JobLaborItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimesheetService
{
    /**
     * Validate that there are no overlapping timesheets for a user on a given date
     */
    public function validateNoOverlap(int $userId, Carbon $workDate, Carbon $clockIn, Carbon $clockOut, ?int $timesheetId = null): bool
    {
        $query = Timesheet::where('user_id', $userId)
            ->where('work_date', $workDate->format('Y-m-d'))
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out');

        if ($timesheetId) {
            $query->where('id', '!=', $timesheetId);
        }

        $overlapping = $query->get()->filter(function ($timesheet) use ($clockIn, $clockOut) {
            return $clockIn < $timesheet->clock_out && $clockOut > $timesheet->clock_in;
        });

        return $overlapping->isEmpty();
    }

    /**
     * Calculate total hours from clock in/out and break time
     */
    public function calculateHours(Carbon $clockIn, Carbon $clockOut, int $breakMinutes = 0): float
    {
        $totalMinutes = $clockIn->diffInMinutes($clockOut);
        $workMinutes = $totalMinutes - $breakMinutes;

        return round($workMinutes / 60, 2);
    }

    /**
     * Update job costs when a timesheet is approved
     */
    public function updateJobCostsFromApproval(Timesheet $timesheet): void
    {
        DB::transaction(function () use ($timesheet) {
            $job = $timesheet->job;
            
            // Recalculate actual labor cost from all approved timesheets
            $totalApprovedHours = Timesheet::forJob($job->id)
                ->approved()
                ->sum('total_hours');

            // Get average labor rate from job labor items
            $avgLaborRate = $this->calculateAverageLaborRate($job);
            
            // Update job actual costs
            $job->actual_labor_cost = $totalApprovedHours * $avgLaborRate;
            $job->actual_total_cost = $job->actual_labor_cost + ($job->actual_material_cost ?? 0);
            $job->save();

            // Update work area if specified
            if ($timesheet->job_work_area_id) {
                $this->updateWorkAreaCosts($timesheet->workArea);
            }
        });
    }

    /**
     * Calculate average labor rate for a job
     */
    protected function calculateAverageLaborRate(Job $job): float
    {
        $laborItems = JobLaborItem::where('job_id', $job->id)->get();
        
        if ($laborItems->isEmpty()) {
            return 25.00; // Default fallback rate
        }

        $totalCost = $laborItems->sum('total_cost');
        $totalHours = $laborItems->sum('estimated_hours');

        if ($totalHours == 0) {
            return 25.00;
        }

        return $totalCost / $totalHours;
    }

    /**
     * Update work area actual costs from approved timesheets
     */
    protected function updateWorkAreaCosts(JobWorkArea $workArea): void
    {
        $totalApprovedHours = Timesheet::where('job_work_area_id', $workArea->id)
            ->approved()
            ->sum('total_hours');

        $avgLaborRate = $this->calculateAverageLaborRate($workArea->job);
        
        // Use DB query to avoid computed attribute issues
        DB::table('job_work_areas')
            ->where('id', $workArea->id)
            ->update([
                'actual_labor_hours' => $totalApprovedHours,
                'actual_labor_cost' => $totalApprovedHours * $avgLaborRate,
                'updated_at' => now(),
            ]);
    }

    /**
     * Get timesheet statistics for a job
     */
    public function getJobTimesheetStats(Job $job): array
    {
        $timesheets = $job->timesheets;

        return [
            'total_entries' => $timesheets->count(),
            'pending_approval' => $timesheets->where('status', 'submitted')->count(),
            'approved_hours' => $timesheets->where('status', 'approved')->sum('total_hours'),
            'total_hours' => $timesheets->sum('total_hours'),
            'estimated_hours' => $this->getEstimatedHours($job),
            'variance_hours' => $this->getEstimatedHours($job) - $timesheets->where('status', 'approved')->sum('total_hours'),
        ];
    }

    /**
     * Get estimated hours for a job from labor items
     */
    protected function getEstimatedHours(Job $job): float
    {
        return JobLaborItem::where('job_id', $job->id)->sum('estimated_hours');
    }

    /**
     * Get timesheet statistics for a user
     */
    public function getUserTimesheetStats(int $userId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = Timesheet::forUser($userId);

        if ($startDate) {
            $query->where('work_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('work_date', '<=', $endDate);
        }

        $timesheets = $query->get();

        return [
            'total_entries' => $timesheets->count(),
            'total_hours' => $timesheets->sum('total_hours'),
            'approved_hours' => $timesheets->where('status', 'approved')->sum('total_hours'),
            'pending_hours' => $timesheets->where('status', 'submitted')->sum('total_hours'),
            'rejected_entries' => $timesheets->where('status', 'rejected')->count(),
        ];
    }

    /**
     * Bulk approve timesheets
     */
    public function bulkApprove(array $timesheetIds, int $approverId): int
    {
        $approved = 0;
        
        foreach ($timesheetIds as $id) {
            $timesheet = Timesheet::find($id);
            
            if ($timesheet && $timesheet->status === 'submitted') {
                $timesheet->approve(\App\Models\User::find($approverId));
                // Observer handles cost updates automatically
                $approved++;
            }
        }

        return $approved;
    }
}
