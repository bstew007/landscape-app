@extends('layouts.sidebar')

@section('title', $job->job_number . ' - ' . $job->title)

@section('content')
<div class="space-y-6">
    {{-- Modern Header --}}
    <section class="rounded-2xl bg-gradient-to-r from-gray-800 to-gray-700 text-white p-6 sm:p-8 shadow-lg border border-brand-700/40">
        <div class="flex flex-wrap items-start gap-6">
            <div class="flex items-center gap-4 flex-1 min-w-0">
                <div class="h-16 w-16 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-white">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        <path d="M9 10h6M9 14h6"/>
                    </svg>
                </div>
                <div class="space-y-1 flex-1 min-w-0">
                    <div class="flex items-center gap-3">
                        <p class="text-xs uppercase tracking-[0.3em] text-gray-300">{{ $job->job_number }}</p>
                        @include('jobs.partials.status-badge', ['status' => $job->status])
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white">{{ $job->title }}</h1>
                    <p class="text-sm text-gray-200">
                        {{ $job->client->company_name ?? $job->client->full_name }} · {{ $job->property->address ?? 'No property' }}
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('estimates.show', $job->estimate) }}" 
                   class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border text-sm bg-white/10 text-white border-white/40 hover:bg-white/20 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M7 2h7l5 5v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
                        <path d="M14 2v5h5"/>
                    </svg>
                    View Estimate
                </a>
            </div>
        </div>
    </section>

    {{-- Job Details Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Info --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Financial Summary --}}
            @include('jobs.partials.financial-summary', ['job' => $job])

            {{-- Work Areas --}}
            <div class="bg-white rounded-2xl border border-brand-100 shadow-sm">
                <div class="px-6 py-4 border-b border-brand-200">
                    <h3 class="text-lg font-semibold text-gray-900">Work Areas</h3>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($job->workAreas as $area)
                        @include('jobs.partials.work-area-card', ['area' => $area])
                    @empty
                        <p class="text-gray-500 text-center py-4">No work areas defined</p>
                    @endforelse
                </div>
            </div>

            {{-- Notes --}}
            @if($job->notes || $job->crew_notes)
            <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
                @if($job->notes)
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-1">Job Notes</h4>
                        <p class="text-sm text-gray-600">{{ $job->notes }}</p>
                    </div>
                @endif
                @if($job->crew_notes)
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-1">Crew Notes</h4>
                        <p class="text-sm text-gray-600">{{ $job->crew_notes }}</p>
                    </div>
                @endif
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Job Info Card --}}
            <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Job Information</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Foreman</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $job->foreman->name ?? 'Unassigned' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Crew Size</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $job->crew_size ?? 'Not set' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Division</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $job->division->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Cost Code</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $job->costCode->code ?? 'N/A' }} - {{ $job->costCode->description ?? '' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Schedule Card --}}
            <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Schedule</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Scheduled Start</dt>
                        <dd class="text-sm text-gray-900 mt-1">
                            {{ $job->scheduled_start_date ? $job->scheduled_start_date->format('M j, Y') : 'Not scheduled' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Scheduled End</dt>
                        <dd class="text-sm text-gray-900 mt-1">
                            {{ $job->scheduled_end_date ? $job->scheduled_end_date->format('M j, Y') : 'Not scheduled' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Actual Start</dt>
                        <dd class="text-sm text-gray-900 mt-1">
                            {{ $job->actual_start_date ? $job->actual_start_date->format('M j, Y') : 'Not started' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Actual End</dt>
                        <dd class="text-sm text-gray-900 mt-1">
                            {{ $job->actual_end_date ? $job->actual_end_date->format('M j, Y') : 'Not completed' }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Progress Card --}}
            <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Progress</h3>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Overall Progress</span>
                        <span class="font-medium text-gray-900">{{ round($job->progress_percent) }}%</span>
                    </div>
                    <div class="w-full bg-brand-200 rounded-full h-3">
                        <div class="bg-brand-800 h-3 rounded-full transition-all" style="width: {{ $job->progress_percent }}%"></div>
                    </div>
                </div>
            </div>

            {{-- QuickBooks Sync --}}
            @if($job->qbo_job_id)
            <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">QuickBooks</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">QBO Job ID</dt>
                        <dd class="text-sm text-gray-900 mt-1">{{ $job->qbo_job_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Synced</dt>
                        <dd class="text-sm text-gray-900 mt-1">
                            {{ $job->qbo_synced_at ? $job->qbo_synced_at->format('M j, Y g:i A') : 'Never' }}
                        </dd>
                    </div>
                </dl>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
