<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

