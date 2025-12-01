<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use App\Models\Job;
use App\Services\TimesheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TimesheetApiController extends Controller
{
    public function __construct(
        private TimesheetService $timesheetService
    ) {}

    /**
     * Get jobs assigned to authenticated user
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function myJobs(Request $request)
    {
        $user = $request->user();
        
        // Get active jobs where user is foreman or crew member
        $jobs = Job::with(['client', 'property', 'workAreas'])
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where(function ($query) use ($user) {
                $query->where('foreman_id', $user->id)
                    ->orWhereHas('crewMembers', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->orderBy('scheduled_start_date')
            ->get()
            ->map(function ($job) use ($user) {
                // Check if user has active timesheet for this job today
                $activeTimesheet = $job->timesheets()
                    ->where('user_id', $user->id)
                    ->where('work_date', today())
                    ->whereNull('clock_out')
                    ->first();

                return [
                    'id' => $job->id,
                    'job_number' => $job->job_number,
                    'title' => $job->title,
                    'status' => $job->status,
                    'client_name' => $job->client->company_name ?? $job->client->full_name,
                    'address' => $job->property?->full_address ?? 'No address',
                    'scheduled_start' => $job->scheduled_start_date?->toDateString(),
                    'scheduled_end' => $job->scheduled_end_date?->toDateString(),
                    'work_areas' => $job->workAreas->map(fn($area) => [
                        'id' => $area->id,
                        'name' => $area->name,
                        'description' => $area->description,
                    ]),
                    'active_timesheet' => $activeTimesheet ? [
                        'id' => $activeTimesheet->id,
                        'work_area_id' => $activeTimesheet->job_work_area_id,
                        'work_area_name' => $activeTimesheet->workArea?->name ?? 'General',
                        'clock_in' => $activeTimesheet->clock_in->toIso8601String(),
                        'elapsed_seconds' => now()->diffInSeconds($activeTimesheet->clock_in),
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'jobs' => $jobs,
        ]);
    }

    /**
     * Clock in to a job
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function clockIn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:project_jobs,id',
            'work_area_id' => 'nullable|exists:job_work_areas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Check for existing active timesheet
        $existingActive = Timesheet::where('user_id', $user->id)
            ->where('work_date', today())
            ->whereNull('clock_out')
            ->first();

        if ($existingActive) {
            return response()->json([
                'success' => false,
                'message' => 'You are already clocked in to a job',
                'active_timesheet' => [
                    'id' => $existingActive->id,
                    'job_id' => $existingActive->job_id,
                    'job_number' => $existingActive->job->job_number,
                    'clock_in' => $existingActive->clock_in->toIso8601String(),
                ],
            ], 400);
        }

        $timesheet = Timesheet::create([
            'job_id' => $request->job_id,
            'user_id' => $user->id,
            'job_work_area_id' => $request->work_area_id,
            'work_date' => today(),
            'clock_in' => now(),
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clocked in successfully',
            'timesheet' => [
                'id' => $timesheet->id,
                'job_id' => $timesheet->job_id,
                'work_area_id' => $timesheet->job_work_area_id,
                'clock_in' => $timesheet->clock_in->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Clock out from a job
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function clockOut(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timesheet_id' => 'required|exists:timesheets,id',
            'break_minutes' => 'nullable|integer|min:0|max:480',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $timesheet = Timesheet::findOrFail($request->timesheet_id);

        // Verify ownership
        if ($timesheet->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if already clocked out
        if ($timesheet->clock_out) {
            return response()->json([
                'success' => false,
                'message' => 'Already clocked out',
            ], 400);
        }

        $timesheet->clock_out = now();
        $timesheet->break_minutes = $request->break_minutes ?? 0;
        $timesheet->notes = $request->notes;
        $timesheet->total_hours = $timesheet->calculateTotalHours();
        $timesheet->save();

        return response()->json([
            'success' => true,
            'message' => 'Clocked out successfully',
            'timesheet' => [
                'id' => $timesheet->id,
                'clock_in' => $timesheet->clock_in->toIso8601String(),
                'clock_out' => $timesheet->clock_out->toIso8601String(),
                'break_minutes' => $timesheet->break_minutes,
                'total_hours' => $timesheet->total_hours,
                'notes' => $timesheet->notes,
            ],
        ]);
    }

    /**
     * Submit timesheet for approval
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitTimesheet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timesheet_id' => 'required|exists:timesheets,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $timesheet = Timesheet::findOrFail($request->timesheet_id);

        // Verify ownership
        if ($timesheet->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Validate can submit
        if ($timesheet->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft timesheets can be submitted',
            ], 400);
        }

        if (!$timesheet->clock_out) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot submit timesheet without clocking out',
            ], 400);
        }

        $timesheet->submit();

        return response()->json([
            'success' => true,
            'message' => 'Timesheet submitted for approval',
            'timesheet' => [
                'id' => $timesheet->id,
                'status' => $timesheet->status,
                'total_hours' => $timesheet->total_hours,
            ],
        ]);
    }

    /**
     * Get user's timesheets
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function myTimesheets(Request $request)
    {
        $user = $request->user();
        
        $timesheets = Timesheet::with(['job', 'workArea'])
            ->where('user_id', $user->id)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->start_date, fn($q, $date) => $q->where('work_date', '>=', $date))
            ->when($request->end_date, fn($q, $date) => $q->where('work_date', '<=', $date))
            ->orderBy('work_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($ts) {
                return [
                    'id' => $ts->id,
                    'job_number' => $ts->job->job_number,
                    'job_title' => $ts->job->title,
                    'work_area' => $ts->workArea?->name ?? 'General',
                    'work_date' => $ts->work_date->toDateString(),
                    'clock_in' => $ts->clock_in->toIso8601String(),
                    'clock_out' => $ts->clock_out?->toIso8601String(),
                    'break_minutes' => $ts->break_minutes,
                    'total_hours' => $ts->total_hours,
                    'status' => $ts->status,
                    'notes' => $ts->notes,
                    'rejection_reason' => $ts->rejection_reason,
                ];
            });

        return response()->json([
            'success' => true,
            'timesheets' => $timesheets,
        ]);
    }
}
