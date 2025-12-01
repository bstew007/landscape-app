@extends('layouts.sidebar')

@section('title', 'Timesheet - ' . $timesheet->work_date->format('M d, Y'))

@section('content')
<div class="space-y-6">
    {{-- Modern Header --}}
    <section class="rounded-2xl bg-gradient-to-r from-gray-800 to-gray-700 text-white p-6 sm:p-8 shadow-lg border border-brand-700/40">
        <div class="flex flex-wrap items-start gap-6">
            <div class="flex items-center gap-4 flex-1 min-w-0">
                <div class="h-16 w-16 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-white">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 6v6l4 2"/>
                    </svg>
                </div>
                <div class="space-y-1 flex-1 min-w-0">
                    <div class="flex items-center gap-3">
                        <p class="text-xs uppercase tracking-[0.3em] text-gray-300">{{ $timesheet->work_date->format('l') }}</p>
                        @include('timesheets.partials.status-badge', ['status' => $timesheet->status])
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white">{{ $timesheet->work_date->format('F d, Y') }}</h1>
                    <p class="text-sm text-gray-200">{{ $timesheet->user->name }} · {{ number_format($timesheet->total_hours ?? 0, 2) }} hours</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Info Card --}}
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Timesheet Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Employee Info --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Employee</h3>
                <p class="text-xl font-bold text-gray-900">{{ $timesheet->user->name }}</p>
            </div>

            {{-- Total Hours --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Total Hours</h3>
                <p class="text-xl font-bold text-brand-800">{{ number_format($timesheet->total_hours ?? 0, 2) }} hrs</p>
            </div>

            {{-- Job --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Job</h3>
                <a href="{{ route('jobs.show', $timesheet->job) }}" 
                   class="text-xl font-bold text-brand-700 hover:text-brand-900">
                    {{ $timesheet->job->job_number }}
                </a>
                <p class="text-sm text-gray-600 mt-1">{{ $timesheet->job->title }}</p>
            </div>

            {{-- Work Area --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Work Area</h3>
                <p class="text-lg text-gray-900">{{ $timesheet->workArea?->name ?? 'General' }}</p>
            </div>
        </div>
    </div>

    {{-- Time Details --}}
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Time Details</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-center gap-4">
                <div class="bg-green-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Clock In</p>
                    <p class="text-lg font-bold text-gray-900">{{ $timesheet->clock_in?->format('g:i A') ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="bg-red-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Clock Out</p>
                    <p class="text-lg font-bold text-gray-900">{{ $timesheet->clock_out?->format('g:i A') ?? 'Active' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Break Time</p>
                    <p class="text-lg font-bold text-gray-900">{{ $timesheet->break_minutes }} min</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    @if($timesheet->notes)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-xl mb-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-yellow-900 mb-2">Work Notes</h3>
                    <p class="text-gray-800 whitespace-pre-line">{{ $timesheet->notes }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Rejection Reason --}}
    @if($timesheet->status === 'rejected' && $timesheet->rejection_reason)
        <div class="bg-red-50 border-l-4 border-red-400 p-6 rounded-xl mb-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-red-900 mb-2">Rejection Reason</h3>
                    <p class="text-gray-800">{{ $timesheet->rejection_reason }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Approval Info --}}
    @if($timesheet->status === 'approved' && $timesheet->approvedBy)
        <div class="bg-green-50 border-l-4 border-green-400 p-6 rounded-xl mb-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-green-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-green-900 mb-1">Approved</h3>
                    <p class="text-gray-800">
                        By {{ $timesheet->approvedBy->name }} on {{ $timesheet->approved_at->format('M d, Y g:i A') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-4">
        @if($timesheet->status === 'draft')
            <form method="POST" action="{{ route('timesheets.submit', $timesheet) }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full px-6 py-3 bg-yellow-600 hover:bg-yellow-700 text-white font-semibold rounded-lg shadow-sm transition">
                    Submit for Approval
                </button>
            </form>

            <a href="{{ route('timesheets.edit', $timesheet) }}"
               class="flex-1 px-6 py-3 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-sm transition text-center">
                Edit
            </a>

            <form method="POST" action="{{ route('timesheets.destroy', $timesheet) }}" 
                  onsubmit="return confirm('Are you sure you want to delete this timesheet?')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-sm transition">
                    Delete
                </button>
            </form>
        @endif

        <a href="{{ route('timesheets.index') }}"
           class="px-6 py-3 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition text-center">
            Back to List
        </a>
    </div>
</div>
@endsection
