<?php

namespace App\Http\Controllers;

use App\Models\SiteVisit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class SiteVisitReportController extends Controller
{
    public function show(SiteVisit $siteVisit)
    {
        $siteVisit->load([
            'client',
            'property',
            'photos',
            'calculations' => fn ($query) => $query->latest(),
        ]);

        $calculationsByType = $siteVisit->calculations->groupBy('calculation_type');
        [$summaryRows, $summaryTotals] = $this->buildSummary($calculationsByType);

        return view('site-visits.report', [
            'siteVisit' => $siteVisit,
            'calculationsByType' => $calculationsByType,
            'reportSummary' => $summaryRows,
            'reportTotals' => $summaryTotals,
        ]);
    }

    public function downloadPdf(SiteVisit $siteVisit)
    {
        $siteVisit->load([
            'client',
            'property',
            'photos',
            'calculations' => fn ($query) => $query->latest(),
        ]);

        $calculationsByType = $siteVisit->calculations->groupBy('calculation_type');
        [$summaryRows, $summaryTotals] = $this->buildSummary($calculationsByType);
        $photoSources = $siteVisit->photos->map(function ($photo) {
            $absolutePath = public_path('storage/'.$photo->path);
            if (!file_exists($absolutePath)) {
                $absolutePath = Storage::disk('public')->path($photo->path);
            }

            return [
                'path' => $absolutePath,
                'caption' => $photo->caption,
            ];
        })->filter(fn ($photo) => file_exists($photo['path']))->values();

        $pdf = Pdf::loadView('site-visits.report-pdf', [
            'siteVisit' => $siteVisit,
            'calculationsByType' => $calculationsByType,
            'reportSummary' => $summaryRows,
            'reportTotals' => $summaryTotals,
            'photoSources' => $photoSources,
        ]);

        return $pdf->download('site-visit-report-' . $siteVisit->id . '.pdf');
    }

    protected function buildSummary($calculationsByType): array
    {
        $rows = $calculationsByType->map(function ($calculations, $type) {
            $calculation = $calculations->first();
            $data = $calculation->data ?? [];
            $labor = (float) ($data['labor_cost'] ?? 0);
            $materials = (float) ($data['material_total'] ?? 0);
            $cost = $labor + $materials;
            $price = (float) ($data['final_price'] ?? $cost);

            return [
                'type' => $type,
                'label' => ucwords(str_replace('_', ' ', $type)),
                'labor' => $labor,
                'materials' => $materials,
                'cost' => $cost,
                'price' => $price,
            ];
        })->values();

        $totals = [
            'labor' => $rows->sum('labor'),
            'materials' => $rows->sum('materials'),
            'cost' => $rows->sum('cost'),
            'price' => $rows->sum('price'),
        ];

        return [$rows, $totals];
    }
}
