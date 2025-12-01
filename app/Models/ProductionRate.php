<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $task
 * @property string $unit
 * @property string $rate
 * @property string|null $calculator
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $note
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRate whereCalculator($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRate whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRate whereTask($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRate whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductionRate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductionRate extends Model
{
    protected $fillable = [
        'task',
        'unit',
        'rate',
        'calculator',
        'note'
    ];

    /**
     * Get the production rate for a given calculator and task.
     */
    public static function getRate(string $calculator, string $task): float
{
    $rate = self::where('calculator', $calculator)
                ->where('task', $task)
                ->value('rate');

   // if (is_null($rate)) {
   //     throw new \Exception("No production rate found for [{$calculator}] / [{$task}]");
  //  }

    return (float) $rate;
}

}

