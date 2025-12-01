<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    use HasFactory;
    
    protected $table = 'project_jobs';

    protected $fillable = [
        'estimate_id',
        'job_number',
        'title',
        'status',
        'client_id',
        'property_id',
        'foreman_id',
        'estimated_revenue',
        'estimated_cost',
        'estimated_profit',
        'actual_labor_cost',
        'actual_material_cost',
        'actual_total_cost',
        'scheduled_start_date',
        'scheduled_end_date',
        'actual_start_date',
        'actual_end_date',
        'crew_size',
        'division_id',
        'cost_code_id',
        'notes',
        'crew_notes',
        'qbo_job_id',
        'qbo_synced_at',
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

    // Relationships
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

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function costCode(): BelongsTo
    {
        return $this->belongsTo(CostCode::class);
    }

    public function workAreas(): HasMany
    {
        return $this->hasMany(JobWorkArea::class)->orderBy('sort_order');
    }

    // Computed attributes
    public function getVarianceTotalAttribute(): float
    {
        return $this->estimated_cost - $this->actual_total_cost;
    }

    public function getVariancePercentAttribute(): float
    {
        if ($this->estimated_cost == 0) {
            return 0;
        }
        
        return (($this->estimated_cost - $this->actual_total_cost) / $this->estimated_cost) * 100;
    }

    public function getActualProfitAttribute(): float
    {
        return $this->estimated_revenue - $this->actual_total_cost;
    }

    public function getActualMarginAttribute(): float
    {
        if ($this->estimated_revenue == 0) {
            return 0;
        }
        
        return ($this->actual_profit / $this->estimated_revenue) * 100;
    }

    public function getProgressPercentAttribute(): int
    {
        $total = $this->workAreas->count();
        
        if ($total === 0) {
            return 0;
        }
        
        $completed = $this->workAreas->where('status', 'completed')->count();
        
        return (int) (($completed / $total) * 100);
    }
}
