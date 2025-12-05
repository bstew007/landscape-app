<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'asset_issue_id',
        'category',
        'subcategory',
        'vendor',
        'amount',
        'expense_date',
        'odometer_hours',
        'description',
        'notes',
        'receipt_number',
        'is_reimbursable',
        'submitted_by',
        'approved_by',
        'qbo_expense_id',
        'qbo_synced_at',
        'qbo_account_id',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'is_reimbursable' => 'boolean',
        'qbo_synced_at' => 'datetime',
    ];

    /**
     * Get the asset that owns the expense.
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the asset issue associated with the expense (for repairs).
     */
    public function assetIssue()
    {
        return $this->belongsTo(AssetIssue::class);
    }

    /**
     * Get the user who submitted the expense.
     */
    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the user who approved the expense.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the attachments for the expense.
     */
    public function attachments()
    {
        return $this->hasMany(AssetExpenseAttachment::class);
    }

    /**
     * Check if the expense is synced to QBO.
     */
    public function isSyncedToQbo()
    {
        return !is_null($this->qbo_expense_id) && !is_null($this->qbo_synced_at);
    }

    /**
     * Check if the expense is approved.
     */
    public function isApproved()
    {
        return !is_null($this->approved_by);
    }

    /**
     * Scope a query to only include fuel expenses.
     */
    public function scopeFuel($query)
    {
        return $query->where('category', 'fuel');
    }

    /**
     * Scope a query to only include repair expenses.
     */
    public function scopeRepairs($query)
    {
        return $query->where('category', 'repairs');
    }

    /**
     * Scope a query to only include general expenses.
     */
    public function scopeGeneral($query)
    {
        return $query->where('category', 'general');
    }
}
