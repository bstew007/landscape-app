<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseAccountMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'category_label',
        'qbo_account_id',
        'qbo_account_name',
        'qbo_account_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Check if this mapping is synced to QBO.
     */
    public function isMapped(): bool
    {
        return !empty($this->qbo_account_id);
    }

    /**
     * Get the default expense categories.
     */
    public static function getDefaultCategories(): array
    {
        return [
            'fuel' => 'Fuel',
            'repairs' => 'Repairs & Maintenance',
            'general' => 'General Expenses',
        ];
    }
}
