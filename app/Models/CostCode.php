<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int|null $division_id
 * @property string|null $qbo_item_id
 * @property string|null $qbo_item_name
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Division|null $division
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCode query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCode whereDivisionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCode whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCode whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCode whereQboItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCode whereQboItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostCode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CostCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'division_id', 'qbo_item_id', 'qbo_item_name', 'is_active'
    ];

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
