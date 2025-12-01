<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Material> $materials
 * @property-read int|null $materials_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCategory whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCategory whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MaterialCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MaterialCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

        public function materials()
    {
        return $this->belongsToMany(Material::class, 'material_material_category');
    }
}
