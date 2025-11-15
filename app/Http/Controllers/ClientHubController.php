<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Estimate;
use App\Models\SiteVisit;
use App\Models\Todo;
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
        $todos = Todo::whereIn('status', ['pending','in_progress'])
            ->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC")
            ->limit(8)
            ->get();

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
            'metrics',
            'todos'
        ));
    }
}
