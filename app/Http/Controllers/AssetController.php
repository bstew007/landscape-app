<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAttachment;
use App\Models\AssetIssue;
use App\Models\AssetMaintenance;
use App\Models\AssetUsageLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $type = $request->get('type');
        $search = $request->get('search');
        $assignedTo = $request->get('assigned_to');
        $serviceWindow = $request->get('service_window');

        $assets = Asset::query()
            ->when($status && in_array($status, Asset::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($type && in_array($type, Asset::TYPES, true), fn ($q) => $q->where('type', $type))
            ->when($search, function ($q, $term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('identifier', 'like', "%{$term}%")
                        ->orWhere('assigned_to', 'like', "%{$term}%");
                });
            })
            ->when($assignedTo, fn ($q) => $q->where('assigned_to', $assignedTo))
            ->when($serviceWindow === 'overdue', fn ($q) => $q->whereNotNull('next_service_date')->where('next_service_date', '<', now()))
            ->when($serviceWindow === 'upcoming', fn ($q) => $q->whereNotNull('next_service_date')->whereBetween('next_service_date', [now(), now()->addDays(30)]))
            ->with(['usageLogs' => fn ($q) => $q->where('status', 'checked_out')->latest()->limit(1)])
            ->withCount([
                'issues' => fn ($q) => $q->where('status', '!=', 'resolved'),
                'linkedAssets',
                'parentAssets',
            ])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $summary = [
            'total' => Asset::count(),
            'active' => Asset::where('status', 'active')->count(),
            'maintenance_due' => Asset::whereNotNull('next_service_date')
                ->where('next_service_date', '<=', now()->addDays(14))
                ->count(),
            'open_issues' => AssetIssue::where('status', '!=', 'resolved')->count(),
        ];

        $typeBreakdown = Asset::select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        $upcomingServices = Asset::whereNotNull('next_service_date')
            ->where('next_service_date', '>=', now())
            ->orderBy('next_service_date')
            ->limit(8)
            ->get();

        $overdueServices = Asset::whereNotNull('next_service_date')
            ->where('next_service_date', '<', now())
            ->orderBy('next_service_date')
            ->limit(8)
            ->get();

        $reminderCandidates = Asset::where('reminder_enabled', true)
            ->whereNotNull('next_service_date')
            ->get()
            ->filter(function (Asset $asset) {
                if (! $asset->next_service_date) {
                    return false;
                }
                $diff = now()->diffInDays($asset->next_service_date, false);
                return $diff <= $asset->reminder_days_before && $diff >= -1;
            })
            ->sortBy('next_service_date')
            ->values();

        $assignedOptions = Asset::whereNotNull('assigned_to')
            ->select('assigned_to')
            ->distinct()
            ->orderBy('assigned_to')
            ->pluck('assigned_to');

        return view('assets.index', compact(
            'assets',
            'status',
            'type',
            'search',
            'assignedTo',
            'serviceWindow',
            'summary',
            'typeBreakdown',
            'upcomingServices',
            'overdueServices',
            'reminderCandidates',
            'assignedOptions'
        ));
    }

    public function create()
    {
        $drivers = User::drivers()->orderBy('name')->get();
        return view('assets.create', ['asset' => new Asset(), 'drivers' => $drivers]);
    }

    public function store(Request $request)
    {
        $asset = Asset::create($this->validateAsset($request));

        return redirect()->route('assets.show', $asset)->with('success', 'Asset added.');
    }

    public function show(Asset $asset)
    {
        $asset->load([
            'maintenances' => fn ($q) => $q->latest(),
            'issues' => fn ($q) => $q->latest(),
            'attachments',
            'linkedAssets',
            'parentAssets',
            'usageLogs' => fn ($q) => $q->with('user')->latest()->limit(10),
        ]);

        // Get all other assets for linking dropdown
        $availableAssets = Asset::where('id', '!=', $asset->id)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('assets.show', [
            'asset' => $asset,
            'availableAssets' => $availableAssets,
            'maintenanceTypes' => ['Inspection', 'Oil Change', 'Service', 'Repair'],
            'issueSeverities' => AssetIssue::SEVERITIES,
            'issueStatuses' => AssetIssue::STATUSES,
        ]);
    }

    public function edit(Asset $asset)
    {
        $drivers = User::drivers()->orderBy('name')->get();
        return view('assets.edit', compact('asset', 'drivers'));
    }

    public function update(Request $request, Asset $asset)
    {
        $asset->update($this->validateAsset($request));

        return redirect()->route('assets.show', $asset)->with('success', 'Asset updated.');
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('assets.index')->with('success', 'Asset removed.');
    }

    public function storeMaintenance(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'type' => 'nullable|string|max:255',
            'scheduled_at' => 'nullable|date',
            'completed_at' => 'nullable|date',
            'mileage_hours' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $asset->maintenances()->create($data);

        if (! empty($data['completed_at'])) {
            $asset->update(['next_service_date' => null]);
        }

        return back()->with('success', 'Maintenance log saved.');
    }

    public function storeIssue(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:' . implode(',', AssetIssue::STATUSES),
            'severity' => 'required|in:' . implode(',', AssetIssue::SEVERITIES),
            'reported_on' => 'nullable|date',
            'resolved_on' => 'nullable|date',
        ]);

        $asset->issues()->create($data);

        return back()->with('success', 'Issue logged.');
    }

    public function createIssue()
    {
        return view('assets.quick-issue', [
            'assets' => Asset::orderBy('name')->get(),
            'issueSeverities' => AssetIssue::SEVERITIES,
            'issueStatuses' => AssetIssue::STATUSES,
        ]);
    }

    public function storeIssueQuick(Request $request)
    {
        $data = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'severity' => 'required|in:' . implode(',', AssetIssue::SEVERITIES),
            'status' => 'required|in:' . implode(',', AssetIssue::STATUSES),
            'reported_on' => 'nullable|date',
        ]);

        $asset = Asset::findOrFail($data['asset_id']);
        $asset->issues()->create($data);

        return redirect()->route('assets.issues.create')->with('success', 'Issue logged.');
    }

    public function createReminder()
    {
        return view('assets.reminder', [
            'assets' => Asset::orderBy('name')->get(),
        ]);
    }

    public function storeReminder(Request $request)
    {
        $data = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'next_service_date' => 'required|date',
            'reminder_days_before' => 'required|integer|min:1|max:60',
            'reminder_enabled' => 'nullable|boolean',
        ]);

        $asset = Asset::findOrFail($data['asset_id']);
        $asset->update([
            'next_service_date' => $data['next_service_date'],
            'reminder_days_before' => $data['reminder_days_before'],
            'reminder_enabled' => $request->boolean('reminder_enabled', true),
        ]);

        return redirect()->route('assets.reminders.create')->with('success', 'Reminder scheduled.');
    }

    public function storeAttachment(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'label' => 'nullable|string|max:255',
            'file' => 'required|file|max:102400', // 100MB max
        ]);

        $path = $request->file('file')->store('assets/' . $asset->id, 'public');

        $asset->attachments()->create([
            'label' => $data['label'] ?? $request->file('file')->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $request->file('file')->getClientMimeType(),
            'size' => $request->file('file')->getSize(),
        ]);

        return back()->with('success', 'File uploaded.');
    }

    public function destroyAttachment(Asset $asset, AssetAttachment $attachment)
    {
        abort_unless($attachment->asset_id === $asset->id, 404);

        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }

    public function linkAsset(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'linked_asset_id' => 'required|exists:assets,id',
            'relationship_type' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        // Prevent linking to self
        if ($asset->id == $data['linked_asset_id']) {
            return back()->with('error', 'Cannot link an asset to itself.');
        }

        // Check if already linked
        if ($asset->linkedAssets()->where('child_asset_id', $data['linked_asset_id'])->exists()) {
            return back()->with('error', 'Assets are already linked.');
        }

        $asset->linkedAssets()->attach($data['linked_asset_id'], [
            'relationship_type' => $data['relationship_type'] ?? 'linked',
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Asset linked successfully.');
    }

    public function unlinkAsset(Asset $asset, Asset $linkedAsset)
    {
        $asset->linkedAssets()->detach($linkedAsset->id);
        
        return back()->with('success', 'Asset unlinked successfully.');
    }

    // Usage Log Methods
    public function showCheckout(Asset $asset)
    {
        $users = User::orderBy('name')->get();
        
        return view('assets.checkout', [
            'asset' => $asset,
            'users' => $users,
        ]);
    }

    public function storeCheckout(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'mileage_out' => 'nullable|integer',
            'notes' => 'nullable|string',
            'inspection_data' => 'nullable|array',
        ]);

        // Check if asset is already checked out
        $activeLog = $asset->usageLogs()->where('status', 'checked_out')->latest()->first();
        if ($activeLog) {
            return back()->with('error', 'Asset is already checked out.');
        }

        $asset->usageLogs()->create([
            'user_id' => $data['user_id'],
            'checked_out_at' => now(),
            'mileage_out' => $data['mileage_out'] ?? null,
            'notes' => $data['notes'] ?? null,
            'inspection_data' => $data['inspection_data'] ?? [],
            'status' => 'checked_out',
        ]);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset checked out successfully.');
    }

    public function showCheckin(Asset $asset)
    {
        $activeLog = $asset->usageLogs()->where('status', 'checked_out')->latest()->first();
        
        if (!$activeLog) {
            return redirect()->route('assets.show', $asset)->with('error', 'Asset is not checked out.');
        }

        return view('assets.checkin', [
            'asset' => $asset,
            'usageLog' => $activeLog,
        ]);
    }

    public function storeCheckin(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'usage_log_id' => 'required|exists:asset_usage_logs,id',
            'mileage_in' => 'nullable|integer',
            'notes' => 'nullable|string',
            'inspection_data' => 'nullable|array',
        ]);

        $usageLog = AssetUsageLog::findOrFail($data['usage_log_id']);
        
        if ($usageLog->asset_id !== $asset->id) {
            abort(404);
        }

        if ($usageLog->status !== 'checked_out') {
            return back()->with('error', 'This usage log is already checked in.');
        }

        $usageLog->update([
            'checked_in_at' => now(),
            'mileage_in' => $data['mileage_in'] ?? null,
            'notes' => ($usageLog->notes ? $usageLog->notes . "\n\n" : '') . ($data['notes'] ?? ''),
            'inspection_data' => array_merge($usageLog->inspection_data ?? [], $data['inspection_data'] ?? []),
            'status' => 'checked_in',
        ]);

        // Update asset mileage if provided
        if (!empty($data['mileage_in'])) {
            $asset->update(['mileage_hours' => $data['mileage_in']]);
        }

        return redirect()->route('assets.show', $asset)->with('success', 'Asset checked in successfully.');
    }

    public function editUsageLog(Asset $asset, AssetUsageLog $usageLog)
    {
        if ($usageLog->asset_id !== $asset->id) {
            abort(404);
        }

        $users = User::orderBy('name')->get();
        
        return view('assets.edit-usage-log', [
            'asset' => $asset,
            'usageLog' => $usageLog,
            'users' => $users,
        ]);
    }

    public function updateUsageLog(Request $request, Asset $asset, AssetUsageLog $usageLog)
    {
        if ($usageLog->asset_id !== $asset->id) {
            abort(404);
        }

        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'checked_out_at' => 'required|date',
            'checked_in_at' => 'nullable|date|after:checked_out_at',
            'mileage_out' => 'nullable|integer',
            'mileage_in' => 'nullable|integer',
            'notes' => 'nullable|string',
            'inspection_data' => 'nullable|array',
            'status' => 'required|in:checked_out,checked_in',
        ]);

        $usageLog->update($data);

        return redirect()->route('assets.show', $asset)->with('success', 'Usage log updated successfully.');
    }

    public function destroyUsageLog(Asset $asset, AssetUsageLog $usageLog)
    {
        if ($usageLog->asset_id !== $asset->id) {
            abort(404);
        }

        $usageLog->delete();

        return redirect()->route('assets.show', $asset)->with('success', 'Usage log deleted.');
    }

    protected function validateAsset(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'type' => 'required|string|max:50',
            'identifier' => 'nullable|string|max:255',
            'status' => 'required|string|max:50',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'assigned_to' => 'nullable|string|max:255',
            'mileage_hours' => 'nullable|integer',
            'next_service_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
    }
}
