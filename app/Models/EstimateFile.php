<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $estimate_id
 * @property string $disk
 * @property string $path
 * @property string $filename
 * @property string|null $mime
 * @property int $size
 * @property int|null $uploaded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Estimate $estimate
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateFile query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateFile whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateFile whereEstimateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateFile whereMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateFile wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateFile whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EstimateFile whereUploadedBy($value)
 * @mixin \Eloquent
 */
class EstimateFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'estimate_id', 'disk', 'path', 'filename', 'mime', 'size', 'uploaded_by',
    ];

    public function estimate()
    {
        return $this->belongsTo(Estimate::class);
    }
}
