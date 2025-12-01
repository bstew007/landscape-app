<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Timesheet extends Model
{
    protected $fillable = [
        'job_id',
        'user_id',
        'job_work_area_id',
        'work_date',
        'clock_in',
        'clock_out',
        'break_minutes',
        'total_hours',
        'status',
        'notes',
        'rejection_reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'break_minutes' => 'integer',
        'total_hours' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workArea(): BelongsTo
    {
        return $this->belongsTo(JobWorkArea::class, 'job_work_area_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Accessors & Mutators
    public function getIsActiveAttribute(): bool
    {
        return $this->clock_in && !$this->clock_out;
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->work_date->format('M d, Y');
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'submitted' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            default => 'gray',
        };
    }

    // Business Logic
    public function calculateTotalHours(): float
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $minutes = $this->clock_in->diffInMinutes($this->clock_out);
        $minutes -= $this->break_minutes;

        return round($minutes / 60, 2);
    }

    public function clockIn(): void
    {
        $this->clock_in = now();
        $this->status = 'draft';
        $this->save();
    }

    public function clockOut(): void
    {
        $this->clock_out = now();
        $this->total_hours = $this->calculateTotalHours();
        $this->save();
    }

    public function submit(): void
    {
        $this->status = 'submitted';
        $this->total_hours = $this->calculateTotalHours();
        $this->save();
    }

    public function approve(User $approver): void
    {
        $this->status = 'approved';
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->save();
    }

    public function reject(User $approver, string $reason): void
    {
        $this->status = 'rejected';
        $this->rejection_reason = $reason;
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->save();
    }

    // Scopes
    public function scopeForJob($query, int $jobId)
    {
        return $query->where('job_id', $jobId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }
}
