<?php

namespace App\Support;

use App\Models\Calculation;
use App\Models\Estimate;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ScopeSummaryBuilder
{
    protected const MEASUREMENT_FIELDS = [
        'area_sqft' => ['label' => 'Area', 'unit' => 'sq ft', 'precision' => 0],
        'square_feet' => ['label' => 'Area', 'unit' => 'sq ft', 'precision' => 0],
        'square_footage' => ['label' => 'Area', 'unit' => 'sq ft', 'precision' => 0],
        'mulch_yards' => ['label' => 'Volume', 'unit' => 'cu yd', 'precision' => 2],
        'depth_inches' => ['label' => 'Depth', 'unit' => 'in', 'precision' => 1],
        'edging_linear_ft' => ['label' => 'Edging', 'unit' => 'lf', 'precision' => 0],
        'linear_ft' => ['label' => 'Length', 'unit' => 'lf', 'precision' => 0],
        'perimeter_linear_ft' => ['label' => 'Perimeter', 'unit' => 'lf', 'precision' => 0],
        'posts' => ['label' => 'Posts', 'unit' => '', 'precision' => 0],
        'panels' => ['label' => 'Panels', 'unit' => '', 'precision' => 0],
        'steps' => ['label' => 'Steps', 'unit' => '', 'precision' => 0],
    ];

    protected const TEXT_FIELDS = [
        'turf_name' => 'Turf',
        'turf_grade' => 'Grade',
        'mulch_type' => 'Mulch',
        'surface_type' => 'Surface',
        'fence_style' => 'Fence Style',
        'edging_type' => 'Edging',
    ];

    public static function fromEstimate(Estimate $estimate): array
    {
        $estimate->loadMissing('siteVisit.calculations');

        /** @var Collection<int,Calculation>|null $calculations */
        $calculations = optional($estimate->siteVisit)->calculations;

        if (! $calculations || $calculations->isEmpty()) {
            return [];
        }

        return $calculations
            ->map(fn (Calculation $calculation) => self::fromCalculation($calculation))
            ->filter()
            ->values()
            ->all();
    }

    public static function notesFromSummaries(array $summaries): string
    {
        return collect($summaries)
            ->map(function ($summary) {
                $segments = [];

                if (!empty($summary['measurements'])) {
                    $measurementText = collect($summary['measurements'])
                        ->map(fn ($m) => "{$m['label']}: {$m['value']}")
                        ->implode(', ');
                    if ($measurementText) {
                        $segments[] = $measurementText;
                    }
                }

                if (!empty($summary['materials'])) {
                    $materialText = collect($summary['materials'])
                        ->map(function ($material) {
                            $meta = $material['meta'] ?? null;
                            $value = trim($material['value']);
                            $label = $material['label'];

                            $suffix = $meta ? " ({$meta})" : '';

                            return "{$label}: {$value}{$suffix}";
                        })
                        ->implode(', ');

                    if ($materialText) {
                        $segments[] = 'Materials: ' . $materialText;
                    }
                }

                if (empty($segments)) {
                    return null;
                }

                $sentence = $summary['title'] . ': ' . implode('. ', $segments);

                return rtrim($sentence, '.') . '.';
            })
            ->filter()
            ->implode("\n");
    }

    public static function fromCalculation(Calculation $calculation): ?array
    {
        $data = $calculation->data ?? [];

        $measurements = self::extractMeasurements($data);
        $materials = self::extractMaterials($data);

        if (empty($measurements) && empty($materials)) {
            return null;
        }

        return [
            'title' => self::titleFromType($calculation->calculation_type),
            'measurements' => $measurements,
            'materials' => $materials,
        ];
    }

    protected static function titleFromType(?string $type): string
    {
        if (! $type) {
            return 'Scope';
        }

        return Str::headline(str_replace('_', ' ', $type));
    }

    protected static function extractMeasurements(array $data): array
    {
        $entries = [];

        foreach (self::MEASUREMENT_FIELDS as $key => $meta) {
            if (! isset($data[$key]) || ! is_numeric($data[$key])) {
                continue;
            }

            $value = (float) $data[$key];
            if ($value <= 0) {
                continue;
            }

            $formatted = number_format(
                $value,
                $meta['precision'] ?? 0
            );

            $unit = $meta['unit'] ? ' ' . $meta['unit'] : '';

            $entries[] = [
                'label' => $meta['label'],
                'value' => trim("{$formatted}{$unit}"),
            ];
        }

        foreach (self::TEXT_FIELDS as $key => $label) {
            $value = Arr::get($data, $key);

            if (! $value || ! is_string($value)) {
                continue;
            }

            $entries[] = [
                'label' => $label,
                'value' => $key === 'turf_grade' ? ucfirst($value) : $value,
            ];
        }

        $tasks = Arr::get($data, 'tasks');

        if (is_array($tasks)) {
            foreach ($tasks as $task) {
                if (!is_array($task)) {
                    continue;
                }

                $qty = $task['qty'] ?? null;
                if (!is_numeric($qty) || (float) $qty <= 0) {
                    continue;
                }

                $label = $task['task'] ?? $task['label'] ?? null;
                if (! $label) {
                    continue;
                }

                $unit = isset($task['unit'])
                    ? $task['unit']
                    : self::inferUnitFromTask($label);

                $entries[] = [
                    'label' => Str::headline($label),
                    'value' => trim(rtrim(rtrim(number_format((float) $qty, 2), '0'), '.') . ($unit ? ' ' . $unit : '')),
                ];
            }
        }

        return $entries;
    }

    protected static function extractMaterials(array $data): array
    {
        $materials = $data['materials'] ?? [];

        if ($materials instanceof Collection) {
            $materials = $materials->toArray();
        }

        if (! is_array($materials) || empty($materials)) {
            return [];
        }

        $entries = [];

        foreach ($materials as $name => $material) {
            if (! is_array($material)) {
                continue;
            }

            $qty = $material['qty'] ?? null;

            if (! is_numeric($qty) || (float) $qty <= 0) {
                continue;
            }

            $unit = $material['unit'] ?? $material['unit_label'] ?? '';
            $label = is_string($name) ? $name : ($material['name'] ?? 'Material');

            $entries[] = [
                'label' => $label,
                'value' => trim(rtrim(rtrim(number_format((float) $qty, 2), '0'), '.') . ' ' . $unit),
                'meta' => $material['meta'] ?? null,
            ];
        }

        return $entries;
    }

    protected static function inferUnitFromTask(string $label): string
    {
        $lower = Str::of($label)->lower();

        foreach (['square', 'sq ft', 'sqft', 'bed', 'shear', 'hedge'] as $needle) {
            if ($lower->contains($needle)) {
                return 'sq ft';
            }
        }

        foreach (['line', 'lf', 'linear', 'edge'] as $needle) {
            if ($lower->contains($needle)) {
                return 'lf';
            }
        }

        foreach (['tree', 'shrub', 'plant', 'stump', 'prune', 'palm'] as $needle) {
            if ($lower->contains($needle)) {
                return 'ea';
            }
        }

        return '';
    }
}
