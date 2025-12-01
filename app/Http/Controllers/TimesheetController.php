<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use App\Models\Job;
use App\Models\User;
use App\Services\TimesheetService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TimesheetController extends Controller
{
    public function __construct(
        private TimesheetService $timesheetService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Timesheet::with(['job', 'user', 'workArea'])
            ->orderBy('work_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by job
        if ($request->filled('job_id')) {
            $query->where('job_id', $request->job_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('work_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('work_date', '<=', $request->end_date);
        }

        $timesheets = $query->paginate(50);

        // Get filter options
        $jobs = Job::orderBy('job_number', 'desc')->limit(50)->get();
        $users = User::orderBy('name')->get();

        // Calculate stats
        $stats = [
            'total_entries' => Timesheet::count(),
            'pending_approval' => Timesheet::where('status', 'submitted')->count(),
            'approved_today' => Timesheet::where('status', 'approved')
                ->whereDate('approved_at', today())
                ->count(),
            'total_hours_today' => Timesheet::whereDate('work_date', today())
                ->sum('total_hours'),
        ];

        return view('timesheets.index', compact('timesheets', 'jobs', 'users', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $jobs = Job::with('workAreas')
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->orderBy('job_number', 'desc')
            ->get();
        
        $users = User::orderBy('name')->get();
        
        // Pre-select job if provided
        $selectedJobId = $request->input('job_id');

        return view('timesheets.create', compact('jobs', 'users', 'selectedJobId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'required|exists:project_jobs,id',
            'user_id' => 'required|exists:users,id',
            'job_work_area_id' => 'nullable|exists:job_work_areas,id',
            'work_date' => 'required|date',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after:clock_in',
            'break_minutes' => 'nullable|integer|min:0|max:480',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Convert times to Carbon instances
        $workDate = Carbon::parse($validated['work_date']);
        $clockIn = Carbon::parse($validated['work_date'] . ' ' . $validated['clock_in']);
        $clockOut = Carbon::parse($validated['work_date'] . ' ' . $validated['clock_out']);

        // Validate no overlap
        if (!$this->timesheetService->validateNoOverlap($validated['user_id'], $workDate, $clockIn, $clockOut)) {
            return back()->withErrors(['clock_in' => 'This time overlaps with an existing timesheet entry.'])->withInput();
        }

        // Calculate total hours
        $breakMinutes = $validated['break_minutes'] ?? 0;
        $totalHours = $this->timesheetService->calculateHours($clockIn, $clockOut, $breakMinutes);

        // Create timesheet
        $timesheet = Timesheet::create([
            'job_id' => $validated['job_id'],
            'user_id' => $validated['user_id'],
            'job_work_area_id' => $validated['job_work_area_id'],
            'work_date' => $workDate,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'break_minutes' => $breakMinutes,
            'total_hours' => $totalHours,
            'notes' => $validated['notes'],
            'status' => 'draft',
        ]);

        return redirect()
            ->route('timesheets.show', $timesheet)
            ->with('success', '✅ Timesheet entry created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Timesheet $timesheet)
    {
        $timesheet->load(['job', 'user', 'workArea', 'approvedBy']);

        return view('timesheets.show', compact('timesheet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Timesheet $timesheet)
    {
        // Can only edit draft timesheets
        if ($timesheet->status !== 'draft') {
            return redirect()
                ->route('timesheets.show', $timesheet)
                ->with('error', '❌ Only draft timesheets can be edited.');
        }

        $jobs = Job::with('workAreas')
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->orderBy('job_number', 'desc')
            ->get();
        
        $users = User::orderBy('name')->get();

        return view('timesheets.edit', compact('timesheet', 'jobs', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Timesheet $timesheet)
    {
        // Can only update draft timesheets
        if ($timesheet->status !== 'draft') {
            return redirect()
                ->route('timesheets.show', $timesheet)
                ->with('error', '❌ Only draft timesheets can be updated.');
        }

        $validated = $request->validate([
            'job_id' => 'required|exists:project_jobs,id',
            'user_id' => 'required|exists:users,id',
            'job_work_area_id' => 'nullable|exists:job_work_areas,id',
            'work_date' => 'required|date',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i|after:clock_in',
            'break_minutes' => 'nullable|integer|min:0|max:480',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Convert times to Carbon instances
        $workDate = Carbon::parse($validated['work_date']);
        $clockIn = Carbon::parse($validated['work_date'] . ' ' . $validated['clock_in']);
        $clockOut = Carbon::parse($validated['work_date'] . ' ' . $validated['clock_out']);

        // Validate no overlap (excluding current timesheet)
        if (!$this->timesheetService->validateNoOverlap($validated['user_id'], $workDate, $clockIn, $clockOut, $timesheet->id)) {
            return back()->withErrors(['clock_in' => 'This time overlaps with an existing timesheet entry.'])->withInput();
        }

        // Calculate total hours
        $breakMinutes = $validated['break_minutes'] ?? 0;
        $totalHours = $this->timesheetService->calculateHours($clockIn, $clockOut, $breakMinutes);

        // Update timesheet
        $timesheet->update([
            'job_id' => $validated['job_id'],
            'user_id' => $validated['user_id'],
            'job_work_area_id' => $validated['job_work_area_id'],
            'work_date' => $workDate,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'break_minutes' => $breakMinutes,
            'total_hours' => $totalHours,
            'notes' => $validated['notes'],
        ]);

        return redirect()
            ->route('timesheets.show', $timesheet)
            ->with('success', '✅ Timesheet entry updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Timesheet $timesheet)
    {
        // Can only delete draft timesheets
        if ($timesheet->status !== 'draft') {
            return redirect()
                ->route('timesheets.index')
                ->with('error', '❌ Only draft timesheets can be deleted.');
        }

        $timesheet->delete();

        return redirect()
            ->route('timesheets.index')
            ->with('success', '✅ Timesheet entry deleted successfully.');
    }

    /**
     * Submit timesheet for approval
     */
    public function submit(Timesheet $timesheet)
    {
        if ($timesheet->status !== 'draft') {
            return back()->with('error', '❌ Only draft timesheets can be submitted.');
        }

        $timesheet->submit();

        return back()->with('success', '✅ Timesheet submitted for approval.');
    }

    /**
     * Clock in to a job
     */
    public function clockIn(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'required|exists:project_jobs,id',
            'job_work_area_id' => 'nullable|exists:job_work_areas,id',
        ]);

        $timesheet = Timesheet::create([
            'job_id' => $validated['job_id'],
            'user_id' => auth()->id(),
            'job_work_area_id' => $validated['job_work_area_id'] ?? null,
            'work_date' => today(),
            'clock_in' => now(),
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Clocked in successfully',
            'timesheet' => $timesheet,
        ]);
    }

    /**
     * Clock out from a job
     */
    public function clockOut(Timesheet $timesheet, Request $request)
    {
        if ($timesheet->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($timesheet->clock_out) {
            return response()->json(['error' => 'Already clocked out'], 400);
        }

        $validated = $request->validate([
            'break_minutes' => 'nullable|integer|min:0|max:480',
            'notes' => 'nullable|string|max:1000',
        ]);

        $timesheet->clock_out = now();
        $timesheet->break_minutes = $validated['break_minutes'] ?? 0;
        $timesheet->notes = $validated['notes'] ?? null;
        $timesheet->total_hours = $timesheet->calculateTotalHours();
        $timesheet->save();

        return response()->json([
            'success' => true,
            'message' => '✅ Clocked out successfully',
            'timesheet' => $timesheet,
        ]);
    }

    /**
     * Show approval page for foremen
     */
    public function approvalPage(Request $request)
    {
        $query = Timesheet::with(['job', 'user', 'workArea'])
            ->where('status', 'submitted')
            ->orderBy('work_date', 'desc');

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->job_id);
        }

        if ($request->filled('date_from')) {
            $query->where('work_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('work_date', '<=', $request->date_to);
        }

        $timesheets = $query->paginate(20);
        $pendingCount = Timesheet::where('status', 'submitted')->count();
        $totalHours = Timesheet::where('status', 'submitted')->sum('total_hours');
        
        // Estimate cost based on work area labor rates
        $estimatedCost = Timesheet::where('status', 'submitted')
            ->with('workArea')
            ->get()
            ->sum(function ($ts) {
                $rate = $ts->workArea?->labor_rate ?? 25.00;
                return $ts->total_hours * $rate;
            });

        $employees = User::whereHas('timesheets')->orderBy('name')->get();
        $jobs = Job::whereHas('timesheets')->orderBy('job_number')->get();

        return view('timesheets.approve', compact(
            'timesheets',
            'pendingCount',
            'totalHours',
            'estimatedCost',
            'employees',
            'jobs'
        ));
    }

    /**
     * Approve a single timesheet
     */
    public function approve(Timesheet $timesheet)
    {
        if ($timesheet->status !== 'submitted') {
            return back()->with('error', 'Only submitted timesheets can be approved');
        }

        DB::transaction(function () use ($timesheet) {
            $timesheet->approve(auth()->id());
            $this->timesheetService->updateJobCostsFromApproval($timesheet);
        });

        return back()->with('success', 'Timesheet approved successfully');
    }

    /**
     * Reject a timesheet
     */
    public function reject(Timesheet $timesheet, Request $request)
    {
        if ($timesheet->status !== 'submitted') {
            return back()->with('error', 'Only submitted timesheets can be rejected');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $timesheet->reject($validated['rejection_reason']);

        return back()->with('success', 'Timesheet rejected');
    }

    /**
     * Bulk approve timesheets
     */
    public function bulkApprove(Request $request)
    {
        $filters = $request->input('filters', []);
        
        $query = Timesheet::where('status', 'submitted');
        
        // Apply same filters as approval page
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (!empty($filters['job_id'])) {
            $query->where('job_id', $filters['job_id']);
        }
        if (!empty($filters['date_from'])) {
            $query->where('work_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('work_date', '<=', $filters['date_to']);
        }

        $timesheetIds = $query->pluck('id')->toArray();
        
        $count = $this->timesheetService->bulkApprove($timesheetIds, auth()->id());

        return back()->with('success', "Successfully approved {$count} timesheets");
    }
}

