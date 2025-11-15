@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <x-page-header title="Assets & Equipment" eyebrow="Operations" subtitle="Track vehicles, trailers, and landscape equipment.">
        <x-slot:actions>
            <x-brand-button href="{{ route('assets.create') }}">+ Add Asset</x-brand-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg bg-white shadow p-4">
            <p class="text-xs uppercase text-gray-500">Total Assets</p>
            <p class="text-2xl font-bold text-gray-900">{{ $summary['total'] }}</p>
        </div>
        <div class="rounded-lg bg-white shadow p-4">
            <p class="text-xs uppercase text-gray-500">Active Fleet</p>
            <p class="text-2xl font-bold text-gray-900">{{ $summary['active'] }}</p>
        </div>
        <div class="rounded-lg bg-white shadow p-4">
            <p class="text-xs uppercase text-gray-500">Service Due (14d)</p>
            <p class="text-2xl font-bold text-amber-600">{{ $summary['maintenance_due'] }}</p>
        </div>
        <div class="rounded-lg bg-white shadow p-4">
            <p class="text-xs uppercase text-gray-500">Open Issues</p>
            <p class="text-2xl font-bold text-red-600">{{ $summary['open_issues'] }}</p>
        </div>
    </div>

    <form method="GET" class="bg-white rounded-lg shadow p-4 grid md:grid-cols-4 gap-4 mt-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Search</label>
            <input type="text" name="search" placeholder="Asset name, VIN, assignment"
                   value="{{ $search }}" class="form-input w-full mt-1">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="form-select w-full mt-1">
                <option value="">All</option>
                @foreach (\App\Models\Asset::STATUSES as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ ucwords(str_replace('_', ' ', $option)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Type</label>
            <select name="type" class="form-select w-full mt-1">
                <option value="">All</option>
                @foreach (\App\Models\Asset::TYPES as $option)
                    <option value="{{ $option }}" @selected($type === $option)>{{ ucwords(str_replace('_', ' ', $option)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Assigned To</label>
            <select name="assigned_to" class="form-select w-full mt-1">
                <option value="">All</option>
                @foreach ($assignedOptions as $person)
                    <option value="{{ $person }}" @selected($assignedTo === $person)>{{ $person }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Service Window</label>
            <select name="service_window" class="form-select w-full mt-1">
                <option value="">Any</option>
                <option value="upcoming" @selected($serviceWindow === 'upcoming')>Upcoming (30d)</option>
                <option value="overdue" @selected($serviceWindow === 'overdue')>Overdue</option>
            </select>
        </div>
        <div class="flex items-end md:col-span-2">
            <x-brand-button type="submit" class="w-full justify-center">Apply Filters</x-brand-button>
        </div>
    </form>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($assets as $asset)
            <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">{{ $asset->name }}</h2>
                    <span class="text-xs font-semibold rounded-full px-2 py-0.5
                        @class([
                            'bg-green-100 text-green-800' => $asset->status === 'active',
                            'bg-yellow-100 text-yellow-800' => $asset->status === 'in_maintenance',
                            'bg-gray-200 text-gray-700' => $asset->status === 'retired',
                        ])">
                        {{ ucwords(str_replace('_', ' ', $asset->status)) }}
                    </span>
                </div>
                <p class="text-sm text-gray-600">{{ ucwords(str_replace('_', ' ', $asset->type)) }}</p>
                @if($asset->identifier)
                    <p class="text-xs text-gray-400">#{{ $asset->identifier }}</p>
                @endif
                <div class="mt-3 text-sm text-gray-700 space-y-1">
                    <p><strong>Assigned:</strong> {{ $asset->assigned_to ?: 'Unassigned' }}</p>
                    <p><strong>Mileage / Hours:</strong> {{ $asset->mileage_hours ?: 'N/A' }}</p>
                    <p><strong>Open Issues:</strong> {{ $asset->issues_count ?? 0 }}</p>
                    <p><strong>Next Service:</strong> {{ optional($asset->next_service_date)->format('M j, Y') ?? 'N/A' }}</p>
                </div>
                <div class="mt-4 flex gap-2">
                    <x-brand-button href="{{ route('assets.show', $asset) }}" variant="outline" class="flex-1 justify-center">View</x-brand-button>
                    <x-brand-button href="{{ route('assets.edit', $asset) }}" variant="ghost" class="flex-1 justify-center">Edit</x-brand-button>
                </div>
            </div>
        @empty
            <p class="text-gray-500">No assets found.</p>
        @endforelse
    </div>

    <div>
        {{ $assets->links() }}
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold">Upcoming Services</h2>
                <span class="text-sm text-gray-500">Next 8</span>
            </div>
            <div class="space-y-3">
                @forelse ($upcomingServices as $serviceAsset)
                    <div class="flex items-center justify-between border rounded px-3 py-2">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $serviceAsset->name }}</p>
                            <p class="text-xs text-gray-500">{{ $serviceAsset->assigned_to ?: 'Unassigned' }}</p>
                        </div>
                        <p class="text-sm font-semibold text-blue-700">{{ $serviceAsset->next_service_date->format('M j, Y') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No upcoming services scheduled.</p>
                @endforelse
            </div>
        </section>

        <section class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold">Overdue Services</h2>
                <span class="text-sm text-gray-500">Most Recent</span>
            </div>
            <div class="space-y-3">
                @forelse ($overdueServices as $overdue)
                    <div class="flex items-center justify-between border rounded px-3 py-2 bg-red-50">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $overdue->name }}</p>
                            <p class="text-xs text-gray-500">{{ $overdue->assigned_to ?: 'Unassigned' }}</p>
                        </div>
                        <p class="text-sm font-semibold text-red-700">{{ $overdue->next_service_date->format('M j, Y') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No overdue services. Great job!</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold">Reminder Queue</h2>
                <span class="text-sm text-gray-500">Next {{ $reminderCandidates->count() }} assets</span>
            </div>
            <div class="space-y-3">
                @forelse ($reminderCandidates as $reminderAsset)
                    <div class="border rounded px-3 py-2 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $reminderAsset->name }}</p>
                            <p class="text-xs text-gray-500">
                                Service {{ optional($reminderAsset->next_service_date)->format('M j, Y') }}
                                · Reminder {{ $reminderAsset->reminder_days_before }}d prior
                            </p>
                        </div>
                        <span class="text-xs text-gray-600">
                            Due in {{ now()->diffInDays($reminderAsset->next_service_date, false) }}d
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No reminders due within configured windows.</p>
                @endforelse
            </div>
        </section>

        <section class="bg-white rounded-lg shadow p-4">
            <h2 class="text-lg font-semibold mb-3">Fleet Breakdown</h2>
            <table class="w-full text-sm">
                <thead>
                <tr class="text-left text-xs uppercase text-gray-500 border-b">
                    <th class="py-2">Type</th>
                    <th class="py-2 text-right">Count</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($typeBreakdown as $row)
                    <tr class="border-b last:border-b-0">
                        <td class="py-2 text-gray-700">{{ ucwords(str_replace('_', ' ', $row->type)) }}</td>
                        <td class="py-2 text-right font-semibold">{{ $row->total }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </section>
    </div>
</div>
@endsection
