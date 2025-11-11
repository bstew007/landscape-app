<?php

namespace App\Support;

use App\Models\Estimate;
use App\Models\SiteVisit;
use Illuminate\Support\Collection;

class ScopeDescriptionResolver
{
    public static function descriptionsForEstimate(Estimate $estimate): array
    {
        $estimate->loadMissing('siteVisit.calculations');

        return self::descriptionsFromCalculations(optional($estimate->siteVisit)->calculations);
    }

    public static function descriptionsForSiteVisit(SiteVisit $siteVisit): array
    {
        $siteVisit->loadMissing('calculations');

        return self::descriptionsFromCalculations($siteVisit->calculations);
    }

    public static function templateFromDescriptions(array $descriptions): string
    {
        return collect($descriptions)
            ->map(fn ($text) => trim($text))
            ->filter()
            ->implode("\n\n");
    }

    protected static function descriptionsFromCalculations(?Collection $calculations): array
    {
        if (! $calculations || $calculations->isEmpty()) {
            return [];
        }

        $descriptions = config('calculator_scopes', []);

        return $calculations->pluck('calculation_type')
            ->unique()
            ->map(fn ($type) => $descriptions[$type] ?? null)
            ->filter()
            ->values()
            ->all();
    }
}
