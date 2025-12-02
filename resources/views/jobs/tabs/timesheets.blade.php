{{-- Timesheet Tracking Tab --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Timesheet History Table --}}
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm">
            <div class="px-6 py-4 border-b border-brand-200 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Timesheet History</h3>
                <span class="text-sm text-gray-500">{{ $job->timesheets->count() }} entries</span>
            </div>
            
            @if($job->timesheets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-brand-200">
                        <thead class="bg-brand-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Work Area</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-brand-200">
                            @foreach($job->timesheets->sortByDesc('work_date') as $timesheet)
                                <tr class="hover:bg-brand-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $timesheet->work_date->format('M j, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $timesheet->user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $timesheet->workArea?->name ?? 'General' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $timesheet->clock_in?->format('g:i A') }} - {{ $timesheet->clock_out?->format('g:i A') ?? 'Active' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ number_format($timesheet->total_hours ?? 0, 2) }} hrs
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @include('timesheets.partials.status-badge', ['status' => $timesheet->status])
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('timesheets.show', $timesheet) }}" 
                                           class="text-brand-700 hover:text-brand-900 font-medium">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                {{-- Summary Stats --}}
                <div class="px-6 py-4 bg-brand-50 border-t border-brand-200">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-center">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Total Hours</p>
                            <p class="text-lg font-bold text-gray-900 mt-1">{{ number_format($job->timesheets->sum('total_hours'), 1) }} hrs</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Approved</p>
                            <p class="text-lg font-bold text-green-600 mt-1">{{ number_format($job->timesheets->where('status', 'approved')->sum('total_hours'), 1) }} hrs</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Pending</p>
                            <p class="text-lg font-bold text-yellow-600 mt-1">{{ number_format($job->timesheets->where('status', 'submitted')->sum('total_hours'), 1) }} hrs</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider">Draft</p>
                            <p class="text-lg font-bold text-gray-600 mt-1">{{ number_format($job->timesheets->where('status', 'draft')->sum('total_hours'), 1) }} hrs</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="px-6 py-12 text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/>
                    </svg>
                    <p class="text-lg font-medium">No timesheet entries yet</p>
                    <p class="text-sm text-gray-400 mt-1">Time logged on this job will appear here</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Time Clock Sidebar --}}
    <div class="lg:col-span-1">
        @php
            $activeTimesheet = $job->timesheets()
                ->where('user_id', auth()->id())
                ->where('work_date', now()->toDateString())
                ->whereNull('clock_out')
                ->first();
        @endphp
        
        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6" x-data="timeclockWidget">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Time Clock</h3>
            
            @if($activeTimesheet)
                {{-- Currently Clocked In --}}
                <div class="space-y-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="h-3 w-3 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-green-900">Clocked In</span>
                        </div>
                        <p class="text-xs text-green-700">Started: {{ $activeTimesheet->clock_in->format('g:i A') }}</p>
                        <p class="text-xs text-green-700 mt-1">
                            Area: {{ $activeTimesheet->workArea->name ?? 'General' }}
                        </p>
                        <p class="text-lg font-semibold text-green-900 mt-2" x-text="elapsedTime"></p>
                    </div>
                    
                    <button @click="clockOut({{ $activeTimesheet->id }})"
                            :disabled="loading"
                            class="w-full px-4 py-3 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-medium rounded-lg transition">
                        <span x-show="!loading">Clock Out</span>
                        <span x-show="loading">Processing...</span>
                    </button>
                </div>
            @else
                {{-- Clock In Form --}}
                <form @submit.prevent="clockIn">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Work Area</label>
                            <select x-model="workAreaId" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">Select work area...</option>
                                @foreach($job->workAreas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <button type="submit"
                                :disabled="loading || !workAreaId"
                                class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-medium rounded-lg transition">
                            <span x-show="!loading">Clock In</span>
                            <span x-show="loading">Processing...</span>
                        </button>
                    </div>
                </form>
            @endif
            
            {{-- Error Message --}}
            <div x-show="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-700" x-text="error"></p>
            </div>
        </div>
    </div>
</div>
