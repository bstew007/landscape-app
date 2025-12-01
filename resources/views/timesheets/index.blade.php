@extends('layouts.sidebar')

@section('title', 'Timesheets')

@section('content')
<div class="space-y-6">
    {{-- Modern Header --}}
    <section class="rounded-2xl bg-gradient-to-r from-gray-800 to-gray-700 text-white p-6 sm:p-8 shadow-lg border border-brand-700/40">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-white">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-xs uppercase tracking-[0.3em] text-gray-300">Time Tracking</p>
                <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">Timesheets</h1>
                <p class="text-sm text-gray-200 mt-1">Track and manage work hours</p>
            </div>
            <a href="{{ route('timesheets.create') }}" 
               class="inline-flex items-center gap-1.5 h-9 px-4 rounded-lg text-sm bg-white/10 text-white border border-white/40 hover:bg-white/20 transition">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                New Entry
            </a>
        </div>
    </section>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Entries</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_entries'] }}</p>
                </div>
                <div class="h-12 w-12 bg-brand-50 rounded-xl flex items-center justify-center">
                    <svg class="h-6 w-6 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending Approval</p>
                    <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $stats['pending_approval'] }}</p>
                </div>
                <div class="h-12 w-12 bg-yellow-50 rounded-xl flex items-center justify-center">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Approved Today</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">{{ $stats['approved_today'] }}</p>
                </div>
                <div class="h-12 w-12 bg-green-50 rounded-xl flex items-center justify-center">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Hours Today</p>
                    <p class="text-3xl font-bold text-brand-800 mt-1">{{ number_format($stats['total_hours_today'], 1) }}</p>
                </div>
                <div class="h-12 w-12 bg-brand-50 rounded-xl flex items-center justify-center">
                    <svg class="h-6 w-6 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-4">
        <form method="GET" action="{{ route('timesheets.index') }}" class="flex flex-wrap gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Submitted</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                    <option value="">All Employees</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Job</label>
                <select name="job_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
                    <option value="">All Jobs</option>
                    @foreach($jobs as $job)
                        <option value="{{ $job->id }}" {{ request('job_id') == $job->id ? 'selected' : '' }}>
                            {{ $job->job_number }} - {{ $job->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500">
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg transition">
                    Apply Filters
                </button>
                <a href="{{ route('timesheets.index') }}" 
                   class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition">
                    Clear
                </a>
            </div>
        </form>
    </div>

    {{-- Timesheets Table --}}
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-brand-200">
            <h3 class="text-lg font-semibold text-gray-900">Timesheet Entries</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Work Area</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($timesheets as $timesheet)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $timesheet->work_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $timesheet->user->name }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('jobs.show', $timesheet->job) }}" 
                                   class="text-brand-700 hover:text-brand-900 font-medium">
                                    {{ $timesheet->job->job_number }}
                                </a>
                                <p class="text-gray-600 text-xs">{{ Str::limit($timesheet->job->title, 30) }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $timesheet->workArea?->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                                {{ number_format($timesheet->total_hours ?? 0, 2) }} hrs
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @include('timesheets.partials.status-badge', ['status' => $timesheet->status])
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('timesheets.show', $timesheet) }}" 
                                   class="text-brand-700 hover:text-brand-900 font-medium">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p class="text-lg font-medium">No timesheets found</p>
                                <p class="text-sm mt-1">Create your first timesheet entry to get started.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($timesheets->hasPages())
            <div class="px-6 py-4 border-t border-brand-200">
                {{ $timesheets->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
