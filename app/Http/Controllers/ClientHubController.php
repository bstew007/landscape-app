<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Estimate;
use App\Models\SiteVisit;
use Illuminate\Http\Request;

class ClientHubController extends Controller
{
    public function __invoke(Request $request)
    {
        $recentClients = Client::withCount('siteVisits')->latest()->limit(5)->get();
        $upcomingVisits = SiteVisit::with('client', 'property')
            ->whereDate('visit_date', '>=', now()->subDay())
            ->orderBy('visit_date')
            ->limit(5)
            ->get();
        $recentEstimates = Estimate::with('client')->latest()->limit(5)->get();

        $metrics = [
            'clients' => Client::count(),
            'site_visits' => SiteVisit::count(),
            'pending_estimates' => Estimate::whereIn('status', ['draft', 'pending'])->count(),
            'upcoming_visits' => SiteVisit::whereDate('visit_date', '>=', now())->count(),
        ];

        return view('client-hub.index', compact(
            'recentClients',
            'upcomingVisits',
            'recentEstimates',
            'metrics'
        ));
    }
}
