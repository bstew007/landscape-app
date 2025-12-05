<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AssetExpenseAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_expense_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'uploaded_by',
    ];

    /**
     * Get the expense that owns the attachment.
     */
    public function expense()
    {
        return $this->belongsTo(AssetExpense::class, 'asset_expense_id');
    }

    /**
     * Get the user who uploaded the attachment.
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full URL for the file.
     */
    public function getUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get human-readable file size.
     */
    public function getFileSizeHumanAttribute()
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Delete the file when the model is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            if (Storage::exists($attachment->file_path)) {
                Storage::delete($attachment->file_path);
            }
        });
    }
}
