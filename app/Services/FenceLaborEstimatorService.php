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
        $digTask = $inputs['dig_method'] === 'hand'
            ? 'post_install_manual'
            : 'post_install_auger';

        $breakdown = [];

        if ($inputs['fence_type'] === 'wood') {
            $breakdown['Post Installation'] = $inputs['total_posts'] * $this->rate('post_install_' . $inputs['dig_method']);
            $breakdown['Concrete, Set, Level'] = $inputs['total_posts'] * $this->rate('concrete_mix');
            $breakdown['Rail Installation'] = $inputs['adjusted_length'] * $this->rate('rail_install');
            $breakdown['Picket Installation'] = $inputs['length'] * $this->rate('picket_install');
            $breakdown['Gate Installation'] = $inputs['gate_count'] * $this->rate('gate_install');
        } else {
            $breakdown['Post Installation'] = $inputs['post_total'] * $this->rate('post_install_' . $inputs['dig_method']);
            $breakdown['Concrete, Set, Level'] = $inputs['post_total'] * $this->rate('concrete_mix');
            $breakdown['Panel Installation'] = $inputs['adjusted_length'] * $this->rate('panel_install');
            $breakdown['Gate Installation'] = $inputs['gate_count'] * $this->rate('gate_install');
        }

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
