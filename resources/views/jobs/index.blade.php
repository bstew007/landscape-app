@extends('layouts.sidebar')

@section('title', 'Jobs')

@section('content')
<div class="space-y-6">
    {{-- Modern Header --}}
    <section class="rounded-2xl bg-gradient-to-r from-gray-800 to-gray-700 text-white p-6 sm:p-8 shadow-lg border border-brand-700/40">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-white">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    <path d="M9 10h6M9 14h6"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-xs uppercase tracking-[0.3em] text-gray-300">Job Management</p>
                <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">Active Jobs</h1>
                <p class="text-sm text-gray-200 mt-1">Track and manage landscaping projects</p>
            </div>
        </div>
    </section>

    {{-- Stats Cards --}}
    @include('jobs.partials.stats-cards', ['stats' => $stats])

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-4">
        <form method="GET" action="{{ route('jobs.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Statuses</option>
                    <option value="scheduled" {{ $status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="on_hold" {{ $status === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="flex-1 min-w-[200px]">
                <label for="foreman_id" class="block text-sm font-medium text-gray-700 mb-1">Foreman</label>
                <select name="foreman_id" id="foreman_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Foremen</option>
                    @foreach($foremen as $foreman)
                        <option value="{{ $foreman->id }}" {{ $foremanId == $foreman->id ? 'selected' : '' }}>
                            {{ $foreman->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-brand-800 text-white rounded-lg hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                    Filter
                </button>
                <a href="{{ route('jobs.index') }}" class="px-4 py-2 bg-white border-2 border-gray-300 text-gray-700 rounded-lg hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 transition">
                    Clear
                </a>
            </div>
        </form>
    </div>

    {{-- Jobs List --}}
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm overflow-hidden">
        @if($jobs->isEmpty())
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No jobs found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by converting an approved estimate to a job.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-brand-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foreman</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-brand-200">
                        @foreach($jobs as $job)
                            <tr class="hover:bg-brand-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $job->job_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $job->title }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $job->client->company_name ?? $job->client->full_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @include('jobs.partials.status-badge', ['status' => $job->status])
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $job->foreman->name ?? 'Unassigned' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($job->scheduled_start_date)
                                        {{ $job->scheduled_start_date->format('M j, Y') }}
                                    @else
                                        <span class="text-gray-400">Not scheduled</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $job->progress_percent }}%"></div>
                                        </div>
                                        <span class="ml-2 text-sm text-gray-600">{{ round($job->progress_percent) }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${{ number_format($job->estimated_revenue, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('jobs.show', $job) }}" class="text-brand-800 hover:text-brand-900 font-semibold">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $jobs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
