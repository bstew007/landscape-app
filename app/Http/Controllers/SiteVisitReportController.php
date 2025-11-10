<?php

namespace App\Http\Controllers;

use App\Models\SiteVisit;
use Barryvdh\DomPDF\Facade\Pdf;

class SiteVisitReportController extends Controller
{
    public function show(SiteVisit $siteVisit)
    {
        $siteVisit->load(['client', 'photos', 'calculations' => fn ($query) => $query->latest()]);

        $calculationsByType = $siteVisit->calculations->groupBy('calculation_type');

        return view('site-visits.report', [
            'siteVisit' => $siteVisit,
            'calculationsByType' => $calculationsByType,
        ]);
    }

    public function downloadPdf(SiteVisit $siteVisit)
    {
        $siteVisit->load(['client', 'photos', 'calculations' => fn ($query) => $query->latest()]);

        $calculationsByType = $siteVisit->calculations->groupBy('calculation_type');

        $pdf = Pdf::loadView('site-visits.report-pdf', [
            'siteVisit' => $siteVisit,
            'calculationsByType' => $calculationsByType,
        ]);

        return $pdf->download('site-visit-report-' . $siteVisit->id . '.pdf');
    }
}
