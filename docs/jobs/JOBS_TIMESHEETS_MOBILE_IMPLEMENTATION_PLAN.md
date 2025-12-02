# Jobs, Timesheets & Mobile Tracking - Implementation Plan

## Executive Summary

This document outlines the implementation plan for converting approved estimates into trackable jobs with timesheet tracking, material expense tracking, and a mobile interface for field foremen. This is the next phase after the production-level estimate system and QuickBooks integration.

---

## Current System Architecture

### What's Working (Production Level)
1. **Budget System** - Overhead rates, labor costs, profit margins
2. **Material & Labor Catalogs** - Cost items with QBO integration
3. **Site Visit Calculators** - Convert to structured estimates with work areas
4. **Estimates** - Design/install tracking with:
   - Work areas with granular labor/material items
   - Cost tracking (estimated vs budgeted)
   - Profit margin calculations
   - QuickBooks sync ready
5. **QuickBooks Integration** - Customer sync, vendor sync, purchase order export

### Current Workflow
```
Site Visit → Calculator → Estimate → [Status: approved] → ??? (Gap we're filling)
```

### What We're Building
```
Estimate [approved] → Job Creation → Time Tracking → Material Tracking → Actuals vs Estimates → QBO Export
                                         ↓                    ↓
                                   Mobile App          Mobile App
                                   (Foreman)           (Foreman)
```

---

## Phase 1: Jobs System (Weeks 1-2)

### Overview
Convert approved estimates into active jobs that can be tracked, scheduled, and managed.

### 1.1 Database Schema

**Table: `jobs`**
```sql
CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estimate_id BIGINT UNSIGNED NOT NULL UNIQUE,
    job_number VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    status ENUM('scheduled', 'in_progress', 'on_hold', 'completed', 'cancelled') DEFAULT 'scheduled',
    
    -- Client & Location
    client_id BIGINT UNSIGNED NOT NULL,
    property_id BIGINT UNSIGNED NULL,
    
    -- Financial Tracking
    estimated_revenue DECIMAL(12,2) NOT NULL DEFAULT 0,
    estimated_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    estimated_profit DECIMAL(12,2) NOT NULL DEFAULT 0,
    actual_labor_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    actual_material_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    actual_total_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    
    -- Scheduling
    scheduled_start_date DATE NULL,
    scheduled_end_date DATE NULL,
    actual_start_date DATE NULL,
    actual_end_date DATE NULL,
    
    -- Assignment
    foreman_id BIGINT UNSIGNED NULL,
    crew_size INT NULL,
    division_id BIGINT UNSIGNED NULL,
    cost_code_id BIGINT UNSIGNED NULL,
    
    -- Metadata
    notes TEXT NULL,
    crew_notes TEXT NULL,
    
    -- QuickBooks
    qbo_job_id VARCHAR(255) NULL,
    qbo_synced_at TIMESTAMP NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (estimate_id) REFERENCES estimates(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (foreman_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE SET NULL,
    FOREIGN KEY (cost_code_id) REFERENCES cost_codes(id) ON DELETE SET NULL,
    
    INDEX idx_status (status),
    INDEX idx_foreman (foreman_id),
    INDEX idx_dates (scheduled_start_date, scheduled_end_date)
);
```

**Table: `job_work_areas`**
```sql
CREATE TABLE job_work_areas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    estimate_area_id BIGINT UNSIGNED NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    
    -- Estimated from Estimate
    estimated_labor_hours DECIMAL(10,2) NOT NULL DEFAULT 0,
    estimated_labor_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    estimated_material_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    
    -- Actual Tracking (computed from timesheets/expenses)
    actual_labor_hours DECIMAL(10,2) NOT NULL DEFAULT 0,
    actual_labor_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    actual_material_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    
    -- Status
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    completed_at TIMESTAMP NULL,
    
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (estimate_area_id) REFERENCES estimate_areas(id) ON DELETE SET NULL,
    
    INDEX idx_job (job_id),
    INDEX idx_status (status)
);
```

**Table: `job_labor_items`**
```sql
CREATE TABLE job_labor_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_work_area_id BIGINT UNSIGNED NOT NULL,
    estimate_item_id BIGINT UNSIGNED NULL,
    labor_item_id BIGINT UNSIGNED NULL,
    
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    unit VARCHAR(50) NULL,
    
    -- Estimated
    estimated_quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    estimated_hours DECIMAL(10,2) NOT NULL DEFAULT 0,
    estimated_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    estimated_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    
    -- Actual (computed from timesheets)
    actual_hours DECIMAL(10,2) NOT NULL DEFAULT 0,
    actual_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (job_work_area_id) REFERENCES job_work_areas(id) ON DELETE CASCADE,
    FOREIGN KEY (estimate_item_id) REFERENCES estimate_items(id) ON DELETE SET NULL,
    FOREIGN KEY (labor_item_id) REFERENCES labor_items(id) ON DELETE SET NULL
);
```

**Table: `job_material_items`**
```sql
CREATE TABLE job_material_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_work_area_id BIGINT UNSIGNED NOT NULL,
    estimate_item_id BIGINT UNSIGNED NULL,
    material_id BIGINT UNSIGNED NULL,
    
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    unit VARCHAR(50) NULL,
    
    -- Estimated
    estimated_quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    estimated_unit_cost DECIMAL(10,2) NOT NULL DEFAULT 0,
    estimated_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    
    -- Actual (computed from expense entries)
    actual_quantity DECIMAL(10,2) NOT NULL DEFAULT 0,
    actual_unit_cost DECIMAL(10,2) NOT NULL DEFAULT 0,
    actual_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (job_work_area_id) REFERENCES job_work_areas(id) ON DELETE CASCADE,
    FOREIGN KEY (estimate_item_id) REFERENCES estimate_items(id) ON DELETE SET NULL,
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE SET NULL
);
```

### 1.2 Job Creation Service

**File: `app/Services/JobCreationService.php`**

```php
<?php

namespace App\Services;

use App\Models\Estimate;
use App\Models\Job;
use App\Models\JobWorkArea;
use App\Models\JobLaborItem;
use App\Models\JobMaterialItem;
use Illuminate\Support\Facades\DB;

class JobCreationService
{
    /**
     * Convert an approved estimate into a job
     */
    public function createFromEstimate(Estimate $estimate): Job
    {
        if ($estimate->status !== 'approved') {
            throw new \Exception('Only approved estimates can be converted to jobs');
        }
        
        if (Job::where('estimate_id', $estimate->id)->exists()) {
            throw new \Exception('Job already exists for this estimate');
        }
        
        return DB::transaction(function () use ($estimate) {
            // Create job
            $job = Job::create([
                'estimate_id' => $estimate->id,
                'job_number' => $this->generateJobNumber(),
                'title' => $estimate->title,
                'client_id' => $estimate->client_id,
                'property_id' => $estimate->property_id,
                'division_id' => $estimate->division_id,
                'cost_code_id' => $estimate->cost_code_id,
                'estimated_revenue' => $estimate->revenue_total,
                'estimated_cost' => $estimate->cost_total,
                'estimated_profit' => $estimate->profit_total,
                'crew_notes' => $estimate->crew_notes,
                'status' => 'scheduled',
            ]);
            
            // Create work areas from estimate areas
            foreach ($estimate->areas as $area) {
                $this->createWorkAreaFromEstimateArea($job, $area);
            }
            
            return $job->fresh(['workAreas.laborItems', 'workAreas.materialItems']);
        });
    }
    
    protected function createWorkAreaFromEstimateArea(Job $job, $estimateArea)
    {
        $laborItems = $estimateArea->items->where('item_type', 'labor');
        $materialItems = $estimateArea->items->where('item_type', 'material');
        
        $workArea = JobWorkArea::create([
            'job_id' => $job->id,
            'estimate_area_id' => $estimateArea->id,
            'name' => $estimateArea->name,
            'description' => $estimateArea->description,
            'estimated_labor_hours' => $laborItems->sum('quantity'),
            'estimated_labor_cost' => $laborItems->sum('cost_total'),
            'estimated_material_cost' => $materialItems->sum('cost_total'),
            'sort_order' => $estimateArea->sort_order,
        ]);
        
        // Create labor items
        foreach ($laborItems as $item) {
            JobLaborItem::create([
                'job_work_area_id' => $workArea->id,
                'estimate_item_id' => $item->id,
                'labor_item_id' => $item->catalog_id,
                'name' => $item->name,
                'description' => $item->description,
                'unit' => $item->unit,
                'estimated_quantity' => $item->quantity,
                'estimated_hours' => $item->quantity,
                'estimated_rate' => $item->unit_cost,
                'estimated_cost' => $item->cost_total,
                'sort_order' => $item->sort_order,
            ]);
        }
        
        // Create material items
        foreach ($materialItems as $item) {
            JobMaterialItem::create([
                'job_work_area_id' => $workArea->id,
                'estimate_item_id' => $item->id,
                'material_id' => $item->catalog_id,
                'name' => $item->name,
                'description' => $item->description,
                'unit' => $item->unit,
                'estimated_quantity' => $item->quantity,
                'estimated_unit_cost' => $item->unit_cost,
                'estimated_cost' => $item->cost_total,
                'sort_order' => $item->sort_order,
            ]);
        }
    }
    
    protected function generateJobNumber(): string
    {
        $year = date('Y');
        $lastJob = Job::where('job_number', 'LIKE', "JOB-{$year}-%")
            ->orderBy('job_number', 'desc')
            ->first();
        
        if ($lastJob) {
            preg_match('/JOB-\d{4}-(\d+)/', $lastJob->job_number, $matches);
            $sequence = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('JOB-%s-%04d', $year, $sequence);
    }
}
```

### 1.3 Models

**File: `app/Models/Job.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    protected $fillable = [
        'estimate_id', 'job_number', 'title', 'status',
        'client_id', 'property_id', 'foreman_id',
        'estimated_revenue', 'estimated_cost', 'estimated_profit',
        'actual_labor_cost', 'actual_material_cost', 'actual_total_cost',
        'scheduled_start_date', 'scheduled_end_date',
        'actual_start_date', 'actual_end_date',
        'crew_size', 'division_id', 'cost_code_id',
        'notes', 'crew_notes',
        'qbo_job_id', 'qbo_synced_at',
    ];
    
    protected $casts = [
        'scheduled_start_date' => 'date',
        'scheduled_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'qbo_synced_at' => 'datetime',
        'estimated_revenue' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'estimated_profit' => 'decimal:2',
        'actual_labor_cost' => 'decimal:2',
        'actual_material_cost' => 'decimal:2',
        'actual_total_cost' => 'decimal:2',
    ];
    
    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }
    
    public function client(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'client_id');
    }
    
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
    
    public function foreman(): BelongsTo
    {
        return $this->belongsTo(User::class, 'foreman_id');
    }
    
    public function workAreas(): HasMany
    {
        return $this->hasMany(JobWorkArea::class)->orderBy('sort_order');
    }
    
    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }
    
    public function materialExpenses(): HasMany
    {
        return $this->hasMany(JobMaterialExpense::class);
    }
    
    // Computed attributes
    public function getVarianceTotalAttribute(): float
    {
        return $this->estimated_cost - $this->actual_total_cost;
    }
    
    public function getVariancePercentAttribute(): float
    {
        if ($this->estimated_cost == 0) return 0;
        return (($this->estimated_cost - $this->actual_total_cost) / $this->estimated_cost) * 100;
    }
}
```

### 1.4 Controller & Routes

**File: `app/Http/Controllers/JobController.php`**
```php
<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Estimate;
use App\Services\JobCreationService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function __construct(
        protected JobCreationService $jobService
    ) {}
    
    public function index(Request $request)
    {
        $status = $request->get('status');
        $foremanId = $request->get('foreman_id');
        
        $jobs = Job::with(['client', 'property', 'foreman'])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($foremanId, fn($q) => $q->where('foreman_id', $foremanId))
            ->latest()
            ->paginate(20);
        
        return view('jobs.index', compact('jobs'));
    }
    
    public function show(Job $job)
    {
        $job->load([
            'estimate',
            'client',
            'property',
            'foreman',
            'workAreas.laborItems',
            'workAreas.materialItems',
            'timesheets.user',
        ]);
        
        return view('jobs.show', compact('job'));
    }
    
    public function createFromEstimate(Estimate $estimate)
    {
        try {
            $job = $this->jobService->createFromEstimate($estimate);
            
            return redirect()
                ->route('jobs.show', $job)
                ->with('success', "Job #{$job->job_number} created successfully");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    
    public function update(Request $request, Job $job)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:scheduled,in_progress,on_hold,completed,cancelled',
            'foreman_id' => 'sometimes|nullable|exists:users,id',
            'scheduled_start_date' => 'sometimes|nullable|date',
            'scheduled_end_date' => 'sometimes|nullable|date',
            'crew_size' => 'sometimes|nullable|integer|min:1',
            'notes' => 'sometimes|nullable|string',
        ]);
        
        $job->update($validated);
        
        if ($request->wantsJson()) {
            return response()->json(['job' => $job]);
        }
        
        return back()->with('success', 'Job updated');
    }
}
```

**Routes: `routes/web.php`**
```php
Route::middleware(['auth'])->group(function () {
    // Jobs
    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{job}', [JobController::class, 'show'])->name('jobs.show');
    Route::patch('/jobs/{job}', [JobController::class, 'update'])->name('jobs.update');
    Route::post('/estimates/{estimate}/create-job', [JobController::class, 'createFromEstimate'])
        ->name('estimates.create-job');
});
```

### 1.5 UI Integration

Add "Convert to Job" button on estimate show page when status is 'approved':

**File: `resources/views/estimates/show.blade.php`** (add to actions section)
```php
@if($estimate->status === 'approved' && !$estimate->job)
    <form method="POST" action="{{ route('estimates.create-job', $estimate) }}" class="inline">
        @csrf
        <x-brand-button type="submit">
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Convert to Job
        </x-brand-button>
    </form>
@elseif($estimate->job)
    <x-secondary-button href="{{ route('jobs.show', $estimate->job) }}">
        View Job #{{ $estimate->job->job_number }}
    </x-secondary-button>
@endif
```

---

## Phase 2: Timesheet System (Weeks 3-4)

### Overview
Track labor hours against job work areas and labor items. This feeds the "actual vs estimated" comparison.

### 2.1 Database Schema

**Table: `timesheets`**
```sql
CREATE TABLE timesheets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    
    work_date DATE NOT NULL,
    clock_in TIME NULL,
    clock_out TIME NULL,
    
    -- Hours tracking
    regular_hours DECIMAL(5,2) NOT NULL DEFAULT 0,
    overtime_hours DECIMAL(5,2) NOT NULL DEFAULT 0,
    total_hours DECIMAL(5,2) NOT NULL DEFAULT 0,
    
    -- Cost tracking
    hourly_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    labor_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
    
    -- Details
    notes TEXT NULL,
    status ENUM('draft', 'submitted', 'approved', 'rejected') DEFAULT 'draft',
    
    -- Approval
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    
    -- QuickBooks
    qbo_timeactivity_id VARCHAR(255) NULL,
    qbo_synced_at TIMESTAMP NULL,
    
    -- Geolocation (optional for mobile check-in)
    check_in_latitude DECIMAL(10,8) NULL,
    check_in_longitude DECIMAL(11,8) NULL,
    check_out_latitude DECIMAL(10,8) NULL,
    check_out_longitude DECIMAL(11,8) NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_job_date (job_id, work_date),
    INDEX idx_user_date (user_id, work_date),
    INDEX idx_status (status)
);
```

**Table: `timesheet_line_items`**
```sql
CREATE TABLE timesheet_line_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    timesheet_id BIGINT UNSIGNED NOT NULL,
    job_work_area_id BIGINT UNSIGNED NOT NULL,
    job_labor_item_id BIGINT UNSIGNED NULL,
    
    hours DECIMAL(5,2) NOT NULL,
    description TEXT NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (timesheet_id) REFERENCES timesheets(id) ON DELETE CASCADE,
    FOREIGN KEY (job_work_area_id) REFERENCES job_work_areas(id) ON DELETE CASCADE,
    FOREIGN KEY (job_labor_item_id) REFERENCES job_labor_items(id) ON DELETE SET NULL
);
```

### 2.2 Timesheet Service

**File: `app/Services/TimesheetService.php`**

```php
<?php

namespace App\Services;

use App\Models\Timesheet;
use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TimesheetService
{
    public function calculateCost(Timesheet $timesheet): void
    {
        $user = $timesheet->user;
        $rate = $user->hourly_rate ?? 0;
        
        $regularCost = $timesheet->regular_hours * $rate;
        $overtimeCost = $timesheet->overtime_hours * $rate * 1.5;
        
        $timesheet->update([
            'hourly_rate' => $rate,
            'labor_cost' => $regularCost + $overtimeCost,
        ]);
    }
    
    public function updateJobActuals(Job $job): void
    {
        DB::transaction(function () use ($job) {
            // Recalculate actual labor cost from timesheets
            $actualLaborCost = Timesheet::where('job_id', $job->id)
                ->where('status', 'approved')
                ->sum('labor_cost');
            
            // Recalculate actual material cost from expenses
            $actualMaterialCost = $job->materialExpenses()
                ->where('status', 'approved')
                ->sum('total_cost');
            
            $job->update([
                'actual_labor_cost' => $actualLaborCost,
                'actual_material_cost' => $actualMaterialCost,
                'actual_total_cost' => $actualLaborCost + $actualMaterialCost,
            ]);
            
            // Update work area actuals
            foreach ($job->workAreas as $workArea) {
                $this->updateWorkAreaActuals($workArea);
            }
        });
    }
    
    protected function updateWorkAreaActuals($workArea): void
    {
        // Sum hours from timesheet line items
        $actualHours = DB::table('timesheet_line_items as tli')
            ->join('timesheets as t', 't.id', '=', 'tli.timesheet_id')
            ->where('tli.job_work_area_id', $workArea->id)
            ->where('t.status', 'approved')
            ->sum('tli.hours');
        
        // Sum costs from approved timesheets for this area
        $actualLaborCost = DB::table('timesheet_line_items as tli')
            ->join('timesheets as t', 't.id', '=', 'tli.timesheet_id')
            ->where('tli.job_work_area_id', $workArea->id)
            ->where('t.status', 'approved')
            ->selectRaw('SUM(tli.hours * t.hourly_rate) as total')
            ->value('total') ?? 0;
        
        $workArea->update([
            'actual_labor_hours' => $actualHours,
            'actual_labor_cost' => $actualLaborCost,
        ]);
    }
    
    public function approveTimesheet(Timesheet $timesheet, User $approver): void
    {
        $timesheet->update([
            'status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
        
        $this->updateJobActuals($timesheet->job);
    }
}
```

### 2.3 Models

**File: `app/Models/Timesheet.php`**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timesheet extends Model
{
    protected $fillable = [
        'job_id', 'user_id', 'work_date',
        'clock_in', 'clock_out',
        'regular_hours', 'overtime_hours', 'total_hours',
        'hourly_rate', 'labor_cost',
        'notes', 'status',
        'approved_by', 'approved_at',
        'qbo_timeactivity_id', 'qbo_synced_at',
        'check_in_latitude', 'check_in_longitude',
        'check_out_latitude', 'check_out_longitude',
    ];
    
    protected $casts = [
        'work_date' => 'date',
        'approved_at' => 'datetime',
        'qbo_synced_at' => 'datetime',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'labor_cost' => 'decimal:2',
    ];
    
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function lineItems(): HasMany
    {
        return $this->hasMany(TimesheetLineItem::class);
    }
}
```

### 2.4 QuickBooks Timesheet Export

**File: `app/Services/QboTimesheetService.php`**

```php
<?php

namespace App\Services;

use App\Models\Timesheet;
use App\Models\QboToken;
use Illuminate\Support\Facades\Http;

class QboTimesheetService
{
    public function syncTimesheet(Timesheet $timesheet): array
    {
        $token = QboToken::latest('updated_at')->first();
        if (!$token) {
            return ['success' => false, 'message' => 'QuickBooks not connected'];
        }
        
        $employee = $this->ensureEmployee($timesheet->user);
        if (!$employee) {
            return ['success' => false, 'message' => 'Failed to sync employee to QuickBooks'];
        }
        
        $payload = [
            'NameOf' => 'Employee',
            'EmployeeRef' => ['value' => $employee['Id']],
            'TxnDate' => $timesheet->work_date->format('Y-m-d'),
            'Hours' => $timesheet->total_hours,
            'HourlyRate' => $timesheet->hourly_rate,
            'Description' => $timesheet->notes ?? "Job #{$timesheet->job->job_number}",
        ];
        
        $url = config('qbo.environment') === 'production'
            ? 'https://quickbooks.api.intuit.com'
            : 'https://sandbox-quickbooks.api.intuit.com';
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token->access_token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("{$url}/v3/company/{$token->realm_id}/timeactivity", $payload);
        
        if ($response->successful()) {
            $data = $response->json();
            $timesheet->update([
                'qbo_timeactivity_id' => $data['TimeActivity']['Id'] ?? null,
                'qbo_synced_at' => now(),
            ]);
            
            return ['success' => true, 'message' => 'Timesheet synced to QuickBooks'];
        }
        
        return ['success' => false, 'message' => $response->json()['Fault']['Error'][0]['Message'] ?? 'Sync failed'];
    }
    
    protected function ensureEmployee($user)
    {
        // Logic to sync user as Employee in QuickBooks
        // Similar to existing QboCustomerService
        // Return employee data with Id
    }
}
```

---

## Phase 3: Material Expense Tracking (Week 5)

### 3.1 Database Schema

**Table: `job_material_expenses`**
```sql
CREATE TABLE job_material_expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_id BIGINT UNSIGNED NOT NULL,
    job_work_area_id BIGINT UNSIGNED NULL,
    job_material_item_id BIGINT UNSIGNED NULL,
    
    -- Submitted by
    submitted_by BIGINT UNSIGNED NOT NULL,
    
    -- Expense details
    expense_date DATE NOT NULL,
    vendor_name VARCHAR(255) NULL,
    supplier_id BIGINT UNSIGNED NULL,
    
    -- Material info
    material_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    unit VARCHAR(50) NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit_cost DECIMAL(10,2) NOT NULL,
    total_cost DECIMAL(12,2) NOT NULL,
    
    -- Receipt
    receipt_photo_path VARCHAR(255) NULL,
    invoice_number VARCHAR(100) NULL,
    
    -- Status
    status ENUM('draft', 'submitted', 'approved', 'rejected') DEFAULT 'draft',
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    
    notes TEXT NULL,
    
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (job_work_area_id) REFERENCES job_work_areas(id) ON DELETE SET NULL,
    FOREIGN KEY (job_material_item_id) REFERENCES job_material_items(id) ON DELETE SET NULL,
    FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_job_date (job_id, expense_date),
    INDEX idx_status (status)
);
```

### 3.2 Material Expense Service

**File: `app/Services/MaterialExpenseService.php`**

```php
<?php

namespace App\Services;

use App\Models\JobMaterialExpense;
use App\Models\Job;
use Illuminate\Support\Facades\Storage;

class MaterialExpenseService
{
    public function createExpense(array $data): JobMaterialExpense
    {
        // Handle receipt photo upload
        if (isset($data['receipt_photo'])) {
            $path = $data['receipt_photo']->store('receipts', 'public');
            $data['receipt_photo_path'] = $path;
            unset($data['receipt_photo']);
        }
        
        // Calculate total
        $data['total_cost'] = $data['quantity'] * $data['unit_cost'];
        
        return JobMaterialExpense::create($data);
    }
    
    public function approveExpense(JobMaterialExpense $expense, $approverId): void
    {
        $expense->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);
        
        // Update job actuals
        $this->updateJobMaterialActuals($expense->job);
    }
    
    protected function updateJobMaterialActuals(Job $job): void
    {
        $job->load('workAreas');
        
        foreach ($job->workAreas as $workArea) {
            $actualMaterialCost = JobMaterialExpense::where('job_work_area_id', $workArea->id)
                ->where('status', 'approved')
                ->sum('total_cost');
            
            $workArea->update([
                'actual_material_cost' => $actualMaterialCost,
            ]);
        }
        
        $totalMaterialCost = $job->materialExpenses()
            ->where('status', 'approved')
            ->sum('total_cost');
        
        $job->update([
            'actual_material_cost' => $totalMaterialCost,
            'actual_total_cost' => $job->actual_labor_cost + $totalMaterialCost,
        ]);
    }
}
```

---

## Phase 4: Mobile App (Weeks 6-8)

### Overview
Laravel API + React Native mobile app for foremen to track time and materials in the field.

### 4.1 Mobile API Endpoints

**File: `app/Http/Controllers/Api/MobileJobController.php`**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Timesheet;
use Illuminate\Http\Request;

class MobileJobController extends Controller
{
    /**
     * Get jobs assigned to current user (foreman)
     */
    public function index(Request $request)
    {
        $jobs = Job::where('foreman_id', $request->user()->id)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->with(['client', 'property', 'workAreas'])
            ->get();
        
        return response()->json(['jobs' => $jobs]);
    }
    
    /**
     * Get job details with work areas
     */
    public function show(Job $job, Request $request)
    {
        // Ensure user has access
        if ($job->foreman_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $job->load([
            'workAreas.laborItems',
            'workAreas.materialItems',
            'client',
            'property',
        ]);
        
        return response()->json(['job' => $job]);
    }
    
    /**
     * Clock in to job
     */
    public function clockIn(Request $request, Job $job)
    {
        $validated = $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);
        
        $timesheet = Timesheet::create([
            'job_id' => $job->id,
            'user_id' => $request->user()->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->format('H:i:s'),
            'check_in_latitude' => $validated['latitude'] ?? null,
            'check_in_longitude' => $validated['longitude'] ?? null,
            'status' => 'draft',
        ]);
        
        return response()->json([
            'message' => 'Clocked in',
            'timesheet' => $timesheet,
        ]);
    }
    
    /**
     * Clock out from job
     */
    public function clockOut(Request $request, Timesheet $timesheet)
    {
        $validated = $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'line_items' => 'required|array',
            'line_items.*.job_work_area_id' => 'required|exists:job_work_areas,id',
            'line_items.*.hours' => 'required|numeric|min:0',
            'line_items.*.description' => 'nullable|string',
        ]);
        
        $clockOut = now();
        $clockIn = \Carbon\Carbon::parse($timesheet->work_date->format('Y-m-d') . ' ' . $timesheet->clock_in);
        
        $totalHours = $clockOut->diffInMinutes($clockIn) / 60;
        $regularHours = min($totalHours, 8);
        $overtimeHours = max($totalHours - 8, 0);
        
        $timesheet->update([
            'clock_out' => $clockOut->format('H:i:s'),
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'total_hours' => $totalHours,
            'notes' => $validated['notes'] ?? null,
            'check_out_latitude' => $validated['latitude'] ?? null,
            'check_out_longitude' => $validated['longitude'] ?? null,
            'status' => 'submitted',
        ]);
        
        // Create line items
        foreach ($validated['line_items'] as $item) {
            $timesheet->lineItems()->create($item);
        }
        
        // Calculate cost
        app(\App\Services\TimesheetService::class)->calculateCost($timesheet);
        
        return response()->json([
            'message' => 'Clocked out',
            'timesheet' => $timesheet->fresh('lineItems'),
        ]);
    }
}
```

**File: `app/Http/Controllers/Api/MobileMaterialExpenseController.php`**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobMaterialExpense;
use App\Services\MaterialExpenseService;
use Illuminate\Http\Request;

class MobileMaterialExpenseController extends Controller
{
    public function __construct(
        protected MaterialExpenseService $expenseService
    ) {}
    
    /**
     * Submit material expense from mobile
     */
    public function store(Request $request, Job $job)
    {
        $validated = $request->validate([
            'job_work_area_id' => 'nullable|exists:job_work_areas,id',
            'job_material_item_id' => 'nullable|exists:job_material_items,id',
            'expense_date' => 'required|date',
            'vendor_name' => 'nullable|string|max:255',
            'material_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'nullable|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'receipt_photo' => 'nullable|image|max:5120', // 5MB
            'invoice_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        
        $validated['job_id'] = $job->id;
        $validated['submitted_by'] = $request->user()->id;
        $validated['status'] = 'submitted';
        
        $expense = $this->expenseService->createExpense($validated);
        
        return response()->json([
            'message' => 'Material expense submitted',
            'expense' => $expense,
        ], 201);
    }
}
```

### 4.2 Mobile API Routes

**File: `routes/api.php`**

```php
use App\Http\Controllers\Api\MobileJobController;
use App\Http\Controllers\Api\MobileMaterialExpenseController;

Route::middleware(['auth:sanctum'])->prefix('mobile')->group(function () {
    // Jobs
    Route::get('/jobs', [MobileJobController::class, 'index']);
    Route::get('/jobs/{job}', [MobileJobController::class, 'show']);
    Route::post('/jobs/{job}/clock-in', [MobileJobController::class, 'clockIn']);
    Route::post('/timesheets/{timesheet}/clock-out', [MobileJobController::class, 'clockOut']);
    
    // Material Expenses
    Route::post('/jobs/{job}/material-expenses', [MobileMaterialExpenseController::class, 'store']);
});
```

### 4.3 Mobile App Stack

**Technology:**
- **React Native** (cross-platform iOS/Android)
- **Expo** for easier development and deployment
- **React Navigation** for routing
- **Axios** for API calls
- **AsyncStorage** for offline support

**Key Screens:**
1. Login (Sanctum token authentication)
2. My Jobs (list of assigned jobs)
3. Job Detail (work areas, estimated vs actual)
4. Clock In/Out (with GPS location)
5. Time Entry (distribute hours across work areas)
6. Material Expense Entry (with camera for receipts)
7. Daily Summary (today's timesheets and expenses)

**Offline Support:**
- Store pending timesheets locally
- Sync when connection available
- Queue photo uploads

---

## Phase 5: Reports & Analytics (Week 9)

### 5.1 Job Profitability Report

**File: `app/Http/Controllers/Reports/JobProfitabilityController.php`**

```php
public function show(Job $job)
{
    $job->load(['workAreas.laborItems', 'workAreas.materialItems']);
    
    $data = [
        'estimated_revenue' => $job->estimated_revenue,
        'estimated_cost' => $job->estimated_cost,
        'estimated_profit' => $job->estimated_profit,
        'actual_cost' => $job->actual_total_cost,
        'actual_profit' => $job->estimated_revenue - $job->actual_total_cost,
        'variance' => $job->variance_total,
        'variance_percent' => $job->variance_percent,
        'work_areas' => $job->workAreas->map(function ($area) {
            return [
                'name' => $area->name,
                'estimated_hours' => $area->estimated_labor_hours,
                'actual_hours' => $area->actual_labor_hours,
                'estimated_labor_cost' => $area->estimated_labor_cost,
                'actual_labor_cost' => $area->actual_labor_cost,
                'estimated_material_cost' => $area->estimated_material_cost,
                'actual_material_cost' => $area->actual_material_cost,
                'variance' => ($area->estimated_labor_cost + $area->estimated_material_cost) - 
                              ($area->actual_labor_cost + $area->actual_material_cost),
            ];
        }),
    ];
    
    return view('reports.job-profitability', compact('job', 'data'));
}
```

### 5.2 Timesheet Approval Dashboard

**File: `resources/views/timesheets/approval.blade.php`**

Show pending timesheets grouped by:
- Date
- Job
- Employee
- Total hours
- Total cost

Batch approval actions.

---

## Implementation Order Summary

### Week 1-2: Jobs Foundation
1. Database migrations (jobs, job_work_areas, job_labor_items, job_material_items)
2. Models (Job, JobWorkArea, JobLaborItem, JobMaterialItem)
3. JobCreationService
4. JobController
5. Basic job views (index, show)
6. "Convert to Job" button on estimates

### Week 3-4: Timesheets
1. Database migrations (timesheets, timesheet_line_items)
2. Models (Timesheet, TimesheetLineItem)
3. TimesheetService
4. TimesheetController
5. Timesheet views (create, edit, index)
6. Approval workflow
7. QBO timesheet export service

### Week 5: Material Expenses
1. Database migration (job_material_expenses)
2. Model (JobMaterialExpense)
3. MaterialExpenseService
4. Material expense controller & views
5. Receipt photo upload
6. Approval workflow

### Week 6-8: Mobile App
1. Laravel Sanctum setup for mobile auth
2. Mobile API endpoints
3. React Native app scaffolding
4. Authentication flow
5. Job list & detail screens
6. Clock in/out functionality
7. Material expense entry with camera
8. Offline support
9. Testing on iOS/Android

### Week 9: Reports & Polish
1. Job profitability reports
2. Timesheet approval dashboard
3. Material expense approval dashboard
4. Estimated vs Actual analytics
5. Export timesheets to QBO
6. Performance optimization
7. User training materials

---

## QuickBooks Integration Points

### 1. Timesheet Export
- Export approved timesheets as TimeActivity records
- Link to Employee records (users synced as employees)
- Include job reference for reporting

### 2. Material Expense Export
- Create as Bills or Expenses in QuickBooks
- Link to Vendor (supplier)
- Track against Job

### 3. Job Costing in QuickBooks
- Jobs can sync as Classes or Customers in QB
- All time/expenses tagged with job reference
- QB reports show job profitability

---

## Mobile App Architecture

```
Mobile App (React Native)
    ↓
Laravel API (/api/mobile/*)
    ↓
Controllers (MobileJobController, MobileMaterialExpenseController)
    ↓
Services (TimesheetService, MaterialExpenseService)
    ↓
Models (Job, Timesheet, JobMaterialExpense)
    ↓
Database
```

**Authentication:**
- Laravel Sanctum for token-based auth
- Mobile app stores token in AsyncStorage
- Include token in all API requests

**Key Features:**
- GPS location tracking on clock in/out
- Camera integration for receipt photos
- Offline mode with local storage queue
- Real-time sync when online
- Push notifications for job assignments

---

## User Roles & Permissions

### Admin
- Create/manage jobs
- Approve timesheets
- Approve material expenses
- Access all reports
- Manage users

### Foreman (Mobile Access)
- View assigned jobs
- Clock in/out
- Submit timesheets
- Submit material expenses
- View job progress

### Office Staff
- View jobs
- Generate reports
- Export to QuickBooks
- Manage invoices

---

## Success Metrics

1. **Time Tracking Adoption**: % of jobs with timesheets submitted
2. **Estimate Accuracy**: Average variance between estimated and actual costs
3. **Mobile App Usage**: Daily active foremen
4. **Approval Turnaround**: Average time from submission to approval
5. **QB Export Success Rate**: % of timesheets successfully exported

---

## Risk Mitigation

1. **Mobile Connectivity**: Offline mode with sync queue
2. **User Training**: Video tutorials and in-person training sessions
3. **Data Integrity**: Validation rules and automated tests
4. **Performance**: Index optimization, eager loading, caching
5. **Security**: API rate limiting, input validation, HTTPS only

---

## Next Steps

1. Review and approve this plan
2. Set up development environment
3. Create feature branch: `feature/jobs-timesheets-mobile`
4. Start with Phase 1 (Jobs System)
5. Weekly progress reviews
6. User acceptance testing after each phase
7. Deploy to staging for testing
8. Production deployment with training

---

## Questions to Answer Before Starting

1. **User Roles**: Do we need additional roles beyond admin, foreman, office staff?
2. **Mobile Devices**: Will company provide devices or BYOD?
3. **GPS Requirements**: Should we enforce GPS check-in within X meters of job site?
4. **Overtime Rules**: Are overtime rules standard (>8hrs) or customizable?
5. **Approval Workflow**: Single approver or multi-level approval?
6. **QB Integration**: Sync in real-time or batch nightly?
7. **Payroll Export**: Weekly, bi-weekly, or on-demand?

---

**Document Version**: 1.0  
**Created**: November 30, 2025  
**Author**: GitHub Copilot  
**Status**: Draft - Awaiting Review
