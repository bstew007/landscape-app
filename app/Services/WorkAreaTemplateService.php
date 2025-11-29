<?php

namespace App\Services;

use App\Models\Calculation;
use App\Models\Estimate;
use App\Models\EstimateArea;
use Illuminate\Support\Str;

/**
 * Creates and configures work areas from calculator templates
 */
class WorkAreaTemplateService
{
    public function __construct(
        protected CalculatorOutputFormatter $formatter
    ) {}
    
    /**
     * Create or get work area for calculator import
     * 
     * @param Estimate $estimate
     * @param Calculation $calculation
     * @param int|null $areaId Existing area to use, or null to create new
     * @param array $options Additional options (name, description, etc.)
     * @return EstimateArea
     */
    public function getOrCreateArea(
        Estimate $estimate,
        Calculation $calculation,
        ?int $areaId = null,
        array $options = []
    ): EstimateArea {
        
        // Use existing area if specified
        if ($areaId) {
            $area = $estimate->areas()->findOrFail($areaId);
            $this->updateAreaMetadata($area, $calculation);
            return $area;
        }
        
        // Create new area
        $data = $calculation->data ?? [];
        $metadata = $this->formatter->extractAreaMetadata($data);
        
        $areaName = $options['name'] ?? $this->generateAreaName($calculation);
        $description = $options['description'] ?? $this->generateAreaDescription($calculation, $data);
        
        $area = $estimate->areas()->create([
            'name' => $areaName,
            'description' => $description,
            'calculation_id' => $calculation->id,
            'site_visit_id' => $calculation->site_visit_id,
            'planned_hours' => $metadata['planned_hours'],
            'crew_size' => $metadata['crew_size'],
            'drive_time_hours' => $metadata['drive_time_hours'],
            'overhead_percent' => $metadata['overhead_percent'],
            'calculator_metadata' => $metadata['calculator_metadata'],
            'sort_order' => $estimate->areas()->max('sort_order') + 1,
        ]);
        
        return $area;
    }
    
    /**
     * Update existing area with calculator metadata
     */
    public function updateAreaMetadata(EstimateArea $area, Calculation $calculation): void
    {
        $data = $calculation->data ?? [];
        $metadata = $this->formatter->extractAreaMetadata($data);
        
        $area->update([
            'calculation_id' => $calculation->id,
            'site_visit_id' => $calculation->site_visit_id,
            'planned_hours' => $metadata['planned_hours'],
            'crew_size' => $metadata['crew_size'],
            'drive_time_hours' => $metadata['drive_time_hours'],
            'overhead_percent' => $metadata['overhead_percent'],
            'calculator_metadata' => array_merge(
                $area->calculator_metadata ?? [],
                $metadata['calculator_metadata']
            ),
        ]);
    }
    
    /**
     * Generate user-friendly area name from calculator
     */
    protected function generateAreaName(Calculation $calculation): string
    {
        $baseName = Str::headline($calculation->calculation_type);
        
        // Add descriptor if available
        $data = $calculation->data ?? [];
        if (!empty($data['job_notes'])) {
            $notes = Str::limit($data['job_notes'], 30);
            return "{$baseName} - {$notes}";
        }
        
        // Add site visit date if available
        if ($calculation->siteVisit && $calculation->siteVisit->visit_date) {
            $visitDate = $calculation->siteVisit->visit_date;
            if ($visitDate instanceof \Illuminate\Support\Carbon) {
                return "{$baseName} ({$visitDate->format('M d')})";
            }
        }
        
        return $baseName;
    }
    
    /**
     * Generate area description from calculator data
     */
    protected function generateAreaDescription(Calculation $calculation, array $data): ?string
    {
        $parts = [];
        
        // Add job notes if available
        if (!empty($data['job_notes'])) {
            $parts[] = $data['job_notes'];
        }
        
        // Add key measurements
        $measurements = $this->formatter->extractAreaMetadata($data)['calculator_metadata']['measurements'] ?? [];
        if (!empty($measurements)) {
            $measText = [];
            foreach ($measurements as $key => $value) {
                $measText[] = Str::headline($key) . ': ' . $value;
            }
            if (!empty($measText)) {
                $parts[] = 'Measurements: ' . implode(', ', $measText);
            }
        }
        
        // Add crew/hours summary
        if (!empty($data['total_hours']) && !empty($data['crew_size'])) {
            $parts[] = "Estimated {$data['total_hours']} hours with crew of {$data['crew_size']}";
        }
        
        return !empty($parts) ? implode("\n\n", $parts) : null;
    }
}
