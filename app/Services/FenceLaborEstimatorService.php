<?php

namespace App\Services;

use App\Models\ProductionRate;

class FenceLaborEstimatorService
{
    protected $rateMap = [];

    public function __construct()
    {
        $this->rateMap = ProductionRate::where('calculator', 'fence')
            ->get()
            ->keyBy('task');
    }

    public function estimate(array $inputs): array
    {
        // ✅ Ensure all expected keys exist to prevent "Undefined array key" errors
        $inputs = array_merge([
            'fence_type' => 'wood',
            'dig_method' => 'hand',
            'total_posts' => 0,
            'post_total' => 0,
            'adjusted_length' => 0,
            'length' => 0,
            'gate_count' => 0,
        ], $inputs);

        // Pick the right dig task
        $digTask = $inputs['dig_method'] === 'hand'
            ? 'post_install_manual'
            : 'post_install_auger';

        $breakdown = [];

        // 🪵 Wood fence labor
        if ($inputs['fence_type'] === 'wood') {
            $breakdown['Post Installation'] = $inputs['total_posts'] * $this->rate($digTask);
            $breakdown['Concrete, Set, Level'] = $inputs['total_posts'] * $this->rate('concrete_mix');
            $breakdown['Rail Installation'] = $inputs['adjusted_length'] * $this->rate('rail_install');
            $breakdown['Picket Installation'] = $inputs['length'] * $this->rate('picket_install');
            $breakdown['Gate Installation'] = $inputs['gate_count'] * $this->rate('gate_install');
        }
        // 🧱 Vinyl fence labor
        else {
            $breakdown['Post Installation'] = $inputs['total_posts'] * $this->rate($digTask);
            $breakdown['Concrete, Set, Level'] = $inputs['total_posts'] * $this->rate('concrete_mix');
            $breakdown['Panel Installation'] = $inputs['adjusted_length'] * $this->rate('panel_install');
            $breakdown['Gate Installation'] = $inputs['gate_count'] * $this->rate('gate_install');
        }

        // ✅ Return both breakdown and total base hours
        return [
            'breakdown' => $breakdown,
            'base_hours' => array_sum($breakdown)
        ];
    }

    protected function rate(string $task): float
    {
        return floatval($this->rateMap[$task]->rate ?? 0);
    }
}
