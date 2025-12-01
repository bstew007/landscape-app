<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Estimate;
use App\Models\User;
use App\Services\JobCreationService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function __construct(
        protected JobCreationService $jobService
    ) {}

    /**
     * Display list of jobs with filters
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        $foremanId = $request->get('foreman_id');
        
        $jobs = Job::with(['client', 'property', 'foreman', 'workAreas'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($foremanId, fn($q) => $q->where('foreman_id', $foremanId))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        
        $foremen = User::whereHas('jobs', function($q) {
            // Only users assigned as foremen
        })->orWhereNotNull('id')->orderBy('name')->get();
        
        $stats = $this->calculateIndexStats($jobs, $status);
        
        return view('jobs.index', compact('jobs', 'foremen', 'stats', 'status', 'foremanId'));
    }

    /**
     * Display a single job with all details
     */
    public function show(Job $job)
    {
        $job->load([
            'estimate',
            'client',
            'property',
            'foreman',
            'division',
            'costCode',
            'workAreas.laborItems.laborItem',
            'workAreas.materialItems.material',
        ]);
        
        return view('jobs.show', compact('job'));
    }

    /**
     * Update job details
     */
    public function update(Request $request, Job $job)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:scheduled,in_progress,on_hold,completed,cancelled',
            'foreman_id' => 'sometimes|nullable|exists:users,id',
            'scheduled_start_date' => 'sometimes|nullable|date',
            'scheduled_end_date' => 'sometimes|nullable|date|after_or_equal:scheduled_start_date',
            'crew_size' => 'sometimes|nullable|integer|min:1',
            'notes' => 'sometimes|nullable|string',
            'crew_notes' => 'sometimes|nullable|string',
        ]);
        
        // Auto-set actual dates based on status
        if (isset($validated['status'])) {
            if ($validated['status'] === 'in_progress' && !$job->actual_start_date) {
                $validated['actual_start_date'] = now()->toDateString();
            }
            
            if ($validated['status'] === 'completed' && !$job->actual_end_date) {
                $validated['actual_end_date'] = now()->toDateString();
            }
        }
        
        $job->update($validated);
        
        if ($request->wantsJson()) {
            return response()->json(['job' => $job]);
        }
        
        return back()->with('success', 'Job updated successfully');
    }

    /**
     * Create job from approved estimate
     */
    public function createFromEstimate(Estimate $estimate)
    {
        try {
            $job = $this->jobService->createFromEstimate($estimate);
            
            // If this is an AJAX request, return JSON
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Job #{$job->job_number} created successfully",
                    'redirect' => route('jobs.show', $job),
                    'job' => $job
                ]);
            }
            
            return redirect()
                ->route('jobs.show', $job)
                ->with('success', "Job #{$job->job_number} created successfully");
        } catch (\Exception $e) {
            \Log::error('Job creation failed', [
                'estimate_id' => $estimate->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // If this is an AJAX request, return JSON error
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Calculate stats for index page
     */
    protected function calculateIndexStats($jobs, $currentStatus): array
    {
        // Get all jobs for stats (not just current page)
        $allJobs = Job::all();
        
        return [
            'total_count' => $allJobs->count(),
            'in_progress_count' => $allJobs->where('status', 'in_progress')->count(),
            'scheduled_count' => $allJobs->where('status', 'scheduled')->count(),
            'completed_count' => $allJobs->where('status', 'completed')->count(),
            'total_value' => $allJobs->sum('estimated_revenue'),
            'page_count' => $jobs->count(),
        ];
    }
}
