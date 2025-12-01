<?php

namespace App\Services;

use App\Models\Estimate;
use App\Models\EstimateArea;
use App\Models\Job;
use App\Models\JobWorkArea;
use App\Models\JobLaborItem;
use App\Models\JobMaterialItem;
use Illuminate\Support\Facades\DB;

class JobCreationService
{
    /**
     * Convert an approved estimate into a trackable job
     */
    public function createFromEstimate(Estimate $estimate): Job
    {
        $this->validateEstimateForConversion($estimate);
        
        return DB::transaction(function () use ($estimate) {
            // Load relationships
            $estimate->load(['areas.items', 'client', 'property']);
            
            $job = $this->createJobRecord($estimate);
            $this->createWorkAreasFromEstimate($job, $estimate);
            
            return $job->fresh(['workAreas.laborItems', 'workAreas.materialItems']);
        });
    }

    /**
     * Validate that estimate can be converted to a job
     */
    protected function validateEstimateForConversion(Estimate $estimate): void
    {
        if ($estimate->status !== 'approved') {
            throw new \Exception('Only approved estimates can be converted to jobs. Current status: ' . $estimate->status);
        }
        
        if (Job::where('estimate_id', $estimate->id)->exists()) {
            throw new \Exception('A job already exists for this estimate');
        }
        
        // Load areas to check if estimate has work areas
        if (!$estimate->relationLoaded('areas')) {
            $estimate->load('areas');
        }
        
        if ($estimate->areas->isEmpty()) {
            throw new \Exception('Cannot create job: estimate has no work areas');
        }
    }

    /**
     * Create the main job record
     */
    protected function createJobRecord(Estimate $estimate): Job
    {
        return Job::create([
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
    }

    /**
     * Create work areas from estimate areas
     */
    protected function createWorkAreasFromEstimate(Job $job, Estimate $estimate): void
    {
        foreach ($estimate->areas as $estimateArea) {
            $this->createWorkAreaFromEstimateArea($job, $estimateArea);
        }
    }

    /**
     * Create a single work area with its labor and material items
     */
    protected function createWorkAreaFromEstimateArea(Job $job, EstimateArea $estimateArea): JobWorkArea
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
        
        $this->createLaborItems($workArea, $laborItems);
        $this->createMaterialItems($workArea, $materialItems);
        
        return $workArea;
    }

    /**
     * Create labor items for a work area
     */
    protected function createLaborItems(JobWorkArea $workArea, $laborItems): void
    {
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
    }

    /**
     * Create material items for a work area
     */
    protected function createMaterialItems(JobWorkArea $workArea, $materialItems): void
    {
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

    /**
     * Generate unique job number
     */
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
