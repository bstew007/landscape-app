<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetIssue;
use App\Models\AssetMaintenance;
use App\Models\AssetUsageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetReportController extends Controller
{
    public function index()
    {
        return view('asset-reports.index');
    }

    public function usageReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $assetId = $request->get('asset_id');
        $userId = $request->get('user_id');

        $usageLogs = AssetUsageLog::with(['asset', 'user'])
            ->whereDate('checked_out_at', '>=', $startDate)
            ->whereDate('checked_out_at', '<=', $endDate)
            ->when($assetId, fn($q) => $q->where('asset_id', $assetId))
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->orderBy('checked_out_at', 'desc')
            ->get();

        $assets = Asset::orderBy('name')->get();
        $users = \App\Models\User::orderBy('name')->get();

        return view('asset-reports.usage', compact('usageLogs', 'assets', 'users', 'startDate', 'endDate', 'assetId', 'userId'));
    }

    public function maintenanceReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(90)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $assetId = $request->get('asset_id');

        $maintenanceRecords = AssetMaintenance::with('asset')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereDate('completed_at', '>=', $startDate)
                  ->whereDate('completed_at', '<=', $endDate);
            })
            ->when($assetId, fn($q) => $q->where('asset_id', $assetId))
            ->orderBy('completed_at', 'desc')
            ->get();

        $assets = Asset::orderBy('name')->get();

        return view('asset-reports.maintenance', compact('maintenanceRecords', 'assets', 'startDate', 'endDate', 'assetId'));
    }

    public function issuesReport(Request $request)
    {
        $status = $request->get('status');
        $severity = $request->get('severity');
        $assetId = $request->get('asset_id');

        $issues = AssetIssue::with('asset')
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($severity, fn($q) => $q->where('severity', $severity))
            ->when($assetId, fn($q) => $q->where('asset_id', $assetId))
            ->orderBy('reported_on', 'desc')
            ->get();

        $assets = Asset::orderBy('name')->get();

        return view('asset-reports.issues', compact('issues', 'assets', 'status', 'severity', 'assetId'));
    }

    public function utilizationReport(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Get usage statistics per asset
        $assetStats = Asset::select('assets.*')
            ->withCount([
                'usageLogs as total_uses' => function($q) use ($startDate, $endDate) {
                    $q->whereDate('checked_out_at', '>=', $startDate)
                      ->whereDate('checked_out_at', '<=', $endDate);
                },
                'usageLogs as total_hours' => function($q) use ($startDate, $endDate) {
                    $q->whereDate('checked_out_at', '>=', $startDate)
                      ->whereDate('checked_out_at', '<=', $endDate)
                      ->whereNotNull('checked_in_at');
                }
            ])
            ->with(['usageLogs' => function($q) use ($startDate, $endDate) {
                $q->whereDate('checked_out_at', '>=', $startDate)
                  ->whereDate('checked_out_at', '<=', $endDate)
                  ->whereNotNull('checked_in_at')
                  ->select('asset_id', 'checked_out_at', 'checked_in_at', 'mileage_out', 'mileage_in');
            }])
            ->get()
            ->map(function($asset) {
                $totalMinutes = $asset->usageLogs->sum(function($log) {
                    return $log->checked_out_at && $log->checked_in_at 
                        ? $log->checked_out_at->diffInMinutes($log->checked_in_at)
                        : 0;
                });
                
                $totalMileage = $asset->usageLogs->sum(function($log) {
                    return ($log->mileage_in && $log->mileage_out) 
                        ? ($log->mileage_in - $log->mileage_out)
                        : 0;
                });

                $asset->total_usage_hours = round($totalMinutes / 60, 1);
                $asset->total_mileage_used = $totalMileage;
                
                return $asset;
            })
            ->sortByDesc('total_uses');

        return view('asset-reports.utilization', compact('assetStats', 'startDate', 'endDate'));
    }

    public function costsReport(Request $request)
    {
        $assetType = $request->get('asset_type');
        $sortBy = $request->get('sort_by', 'total_cost');

        $assetCosts = Asset::select('assets.*')
            ->when($assetType, fn($q) => $q->where('asset_type', $assetType))
            ->withCount(['maintenances', 'issues'])
            ->with(['expenses'])
            ->get()
            ->map(function($asset) {
                // Calculate expense costs by category
                $asset->total_expense_cost = $asset->expenses->sum('amount');
                $asset->fuel_cost = $asset->expenses->where('category', 'fuel')->sum('amount');
                $asset->repair_cost = $asset->expenses->where('category', 'repairs')->sum('amount');
                $asset->general_cost = $asset->expenses->where('category', 'general')->sum('amount');
                
                // Note: AssetMaintenance table doesn't have a cost field
                $asset->total_maintenance_cost = 0;
                $asset->maintenance_count = $asset->maintenances_count;
                $asset->issue_count = $asset->issues_count;
                
                return $asset;
            });

        // Sort the collection
        if ($sortBy === 'total_cost') {
            $assetCosts = $assetCosts->sortByDesc(function($asset) {
                return ($asset->purchase_cost ?? 0) + $asset->total_expense_cost;
            });
        } elseif ($sortBy === 'purchase_cost') {
            $assetCosts = $assetCosts->sortByDesc('purchase_cost');
        } elseif ($sortBy === 'maintenance_cost') {
            $assetCosts = $assetCosts->sortByDesc('total_expense_cost');
        } elseif ($sortBy === 'name') {
            $assetCosts = $assetCosts->sortBy('name');
        }

        // Calculate totals
        $totalPurchaseCost = $assetCosts->sum(fn($a) => $a->purchase_cost ?? 0);
        $totalMaintenanceCost = $assetCosts->sum('total_expense_cost');
        $totalCostOfOwnership = $totalPurchaseCost + $totalMaintenanceCost;
        
        // Expense breakdowns
        $totalFuelCost = $assetCosts->sum('fuel_cost');
        $totalRepairCost = $assetCosts->sum('repair_cost');
        $totalGeneralCost = $assetCosts->sum('general_cost');

        return view('asset-reports.costs', compact('assetCosts', 'totalPurchaseCost', 'totalMaintenanceCost', 'totalCostOfOwnership', 'totalFuelCost', 'totalRepairCost', 'totalGeneralCost'));
    }
}
