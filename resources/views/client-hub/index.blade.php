@extends('layouts.sidebar')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm uppercase tracking-wide text-gray-500">Client Hub</p>
            <h1 class="text-3xl font-bold text-gray-900">Pipeline Overview</h1>
            <p class="text-gray-600">Manage clients, visits, and estimates from one place.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('clients.create') }}" class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Add Client</a>
            <a href="{{ route('clients.index') }}" class="rounded border px-4 py-2 text-sm hover:bg-gray-50">Manage Clients</a>
            <a href="{{ route('clients.index') }}" class="rounded border px-4 py-2 text-sm hover:bg-gray-50">Create Site Visit</a>
            <a href="{{ route('estimates.create') }}" class="rounded border px-4 py-2 text-sm hover:bg-gray-50">Create Estimate</a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg bg-white shadow p-4">
            <p class="text-xs uppercase text-gray-500">Clients</p>
            <p class="text-2xl font-bold text-gray-900">{{ $metrics['clients'] }}</p>
        </div>
        <div class="rounded-lg bg-white shadow p-4">
            <p class="text-xs uppercase text-gray-500">Site Visits</p>
            <p class="text-2xl font-bold text-gray-900">{{ $metrics['site_visits'] }}</p>
        </div>
        <div class="rounded-lg bg-white shadow p-4">
            <p class="text-xs uppercase text-gray-500">Upcoming Visits</p>
            <p class="text-2xl font-bold text-blue-700">{{ $metrics['upcoming_visits'] }}</p>
        </div>
        <div class="rounded-lg bg-white shadow p-4">
            <p class="text-xs uppercase text-gray-500">Draft / Pending Estimates</p>
            <p class="text-2xl font-bold text-emerald-700">{{ $metrics['pending_estimates'] }}</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="bg-white rounded-lg shadow p-4 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Recent Clients</h2>
                <a href="{{ route('clients.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View all</a>
            </div>
            <div class="space-y-3">
                @foreach ($recentClients as $client)
                    <div class="border rounded px-3 py-2 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $client->name }}</p>
                            <p class="text-xs text-gray-500">{{ $client->company_name ?: 'Residential' }}</p>
                        </div>
                        <div class="text-right text-xs text-gray-500">
                            <p>{{ $client->site_visits_count }} visits</p>
                            <a href="{{ route('clients.show', $client) }}" class="text-blue-600">Open</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="bg-white rounded-lg shadow p-4 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Upcoming Site Visits</h2>
                <a href="{{ route('clients.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Schedule</a>
            </div>
            <div class="space-y-3">
                @forelse ($upcomingVisits as $visit)
                    <div class="border rounded px-3 py-2 flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-gray-900">{{ optional($visit->client)->name }}</p>
                            <p class="text-xs text-gray-500">{{ optional($visit->property)->name ?? 'No property' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">{{ optional($visit->visit_date)->format('M j, g:ia') }}</p>
                            <a href="{{ route('clients.site-visits.show', [$visit->client_id, $visit->id]) }}" class="text-xs text-blue-600">Open</a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No upcoming visits scheduled.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="bg-white rounded-lg shadow p-4 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Recent Estimates</h2>
            <a href="{{ route('estimates.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View estimates</a>
        </div>
        <div class="space-y-3">
            @forelse ($recentEstimates as $estimate)
                <div class="border rounded px-3 py-2 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $estimate->title }}</p>
                        <p class="text-xs text-gray-500">{{ $estimate->client->name }}</p>
                    </div>
                    <div class="text-right text-sm">
                        <p class="font-semibold">{{ $estimate->total ? '$' . number_format($estimate->total, 2) : 'Pending' }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst($estimate->status) }}</p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No estimates yet.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
