@extends('layouts.sidebar')

@section('title', 'Approve Timesheets')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <section class="rounded-2xl bg-gradient-to-r from-gray-800 to-gray-700 text-white p-6 sm:p-8 shadow-lg border border-brand-700/40">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-semibold">Approve Timesheets</h1>
                <p class="text-sm text-gray-200 mt-1">Review and approve submitted timesheets</p>
            </div>
        </div>
    </section>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Pending Approval</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $pendingCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Hours</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalHours, 1) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Estimated Cost</p>
                    <p class="text-2xl font-semibold text-gray-900">${{ number_format($estimatedCost, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
        <form method="GET" action="{{ route('timesheets.approve') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Employee</label>
                <select name="user_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Employees</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ request('user_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Job</label>
                <select name="job_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">All Jobs</option>
                    @foreach($jobs as $job)
                        <option value="{{ $job->id }}" {{ request('job_id') == $job->id ? 'selected' : '' }}>
                            {{ $job->job_number }} - {{ $job->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="md:col-span-4 flex gap-3">
                <button type="submit" class="px-4 py-2 bg-brand-800 hover:bg-brand-700 text-white font-medium rounded-lg transition">
                    Apply Filters
                </button>
                <a href="{{ route('timesheets.approve') }}" class="px-4 py-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                    Clear
                </a>
            </div>
        </form>
    </div>

    {{-- Timesheets Table --}}
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm">
        <div class="px-6 py-4 border-b border-brand-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Submitted Timesheets</h3>
            @if($timesheets->total() > 0)
                <form method="POST" action="{{ route('timesheets.bulk-approve') }}" 
                      onsubmit="return confirm('Approve all {{ $timesheets->total() }} visible timesheets?');">
                    @csrf
                    @foreach(request()->all() as $key => $value)
                        @if($key !== 'page')
                            <input type="hidden" name="filters[{{ $key }}]" value="{{ $value }}">
                        @endif
                    @endforeach
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                        Approve All Visible
                    </button>
                </form>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-brand-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Work Area</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Est. Cost</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-brand-200">
                    @forelse($timesheets as $timesheet)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $timesheet->work_date->format('M j, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $timesheet->user->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <a href="{{ route('jobs.show', $timesheet->job) }}" class="text-brand-800 hover:underline">
                                    {{ $timesheet->job->job_number }}
                                </a>
                                <br>
                                <span class="text-xs text-gray-500">{{ Str::limit($timesheet->job->title, 30) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $timesheet->workArea->name ?? 'General' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ number_format($timesheet->total_hours, 2) }} hrs</span>
                                    <span class="text-xs text-gray-500">
                                        {{ $timesheet->clock_in->format('g:i A') }} - {{ $timesheet->clock_out->format('g:i A') }}
                                    </span>
                                    @if($timesheet->break_minutes > 0)
                                        <span class="text-xs text-gray-500">Break: {{ $timesheet->break_minutes }} min</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @php
                                    $rate = $timesheet->workArea?->labor_rate ?? 25.00;
                                    $cost = $timesheet->total_hours * $rate;
                                @endphp
                                ${{ number_format($cost, 2) }}
                                <br>
                                <span class="text-xs text-gray-500">@ ${{ number_format($rate, 2) }}/hr</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('timesheets.show', $timesheet) }}" 
                                       class="text-brand-800 hover:text-brand-900">
                                        View
                                    </a>
                                    <form method="POST" action="{{ route('timesheets.approve', $timesheet) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900">
                                            Approve
                                        </button>
                                    </form>
                                    <button type="button" 
                                            onclick="showRejectModal({{ $timesheet->id }})"
                                            class="text-red-600 hover:text-red-900">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No submitted timesheets found
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

{{-- Reject Modal --}}
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-2xl bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Timesheet</h3>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason for rejection</label>
                    <textarea name="rejection_reason" rows="4" required
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Please explain why this timesheet is being rejected..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                        Reject
                    </button>
                    <button type="button" onclick="hideRejectModal()"
                            class="flex-1 px-4 py-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showRejectModal(timesheetId) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    form.action = `/timesheets/${timesheetId}/reject`;
    modal.classList.remove('hidden');
}

function hideRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.classList.add('hidden');
}

// Close modal on outside click
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideRejectModal();
    }
});
</script>
@endpush
@endsection
