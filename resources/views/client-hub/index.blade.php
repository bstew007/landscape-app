@extends('layouts.sidebar')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="space-y-6">
    <x-page-header title="Home Dashboard" eyebrow="Client Hub" subtitle="Manage clients, visits, and estimates from one place.">
        <x-slot:actions>
            <x-brand-button href="{{ route('contacts.create') }}">Add Contact</x-brand-button>
            <x-brand-button href="{{ route('contacts.index') }}" variant="outline">Manage Contacts</x-brand-button>
            <x-brand-button href="{{ route('contacts.index') }}" variant="outline">Create Site Visit</x-brand-button>
            <x-brand-button href="{{ route('estimates.create') }}" variant="outline">Create Estimate</x-brand-button>
        </x-slot:actions>
    </x-page-header>

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
                <a href="{{ route('contacts.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View all</a>
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
                <a href="{{ route('contacts.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Schedule</a>
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
                            <a href="{{ route('contacts.site-visits.show', [$visit->client_id, $visit->id]) }}" class="text-xs text-blue-600">Open</a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No upcoming visits scheduled.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2 mt-6">
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

        <section class="bg-white rounded-lg shadow p-4 space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Your To‑Dos</h2>
                <a href="{{ route('todos.index') }}" class="text-sm text-blue-600 hover:text-blue-800">Open Board</a>
            </div>
            @forelse ($todos as $todo)
                <div class="border rounded px-3 py-2 flex items-start justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $todo->title }}</p>
                        <p class="text-xs text-gray-500">Status: {{ Str::headline($todo->status) }} @if($todo->due_date) • Due {{ $todo->due_date->format('M j') }} @endif</p>
                    </div>
                    @if($todo->priority)
                        <span class="text-xs px-2 py-1 rounded {{ $todo->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($todo->priority === 'high' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-700') }}">{{ Str::headline($todo->priority) }}</span>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">No current to‑dos. Enjoy the day!</p>
            @endforelse
    </section>
    </div>
</div>
@endsection
