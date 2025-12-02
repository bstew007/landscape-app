{{-- Schedule Tab --}}
<div class="space-y-6">
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Job Schedule</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label class="text-sm font-medium text-gray-700">Start Date</label>
                <p class="text-lg text-gray-900 mt-1">
                    {{ $job->start_date ? $job->start_date->format('F j, Y') : 'Not scheduled' }}
                </p>
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Expected Completion</label>
                <p class="text-lg text-gray-900 mt-1">
                    {{ $job->expected_completion_date ? $job->expected_completion_date->format('F j, Y') : 'Not scheduled' }}
                </p>
            </div>
        </div>
        
        @if($job->start_date && $job->expected_completion_date)
            @php
                $duration = $job->start_date->diffInDays($job->expected_completion_date);
                $elapsed = $job->start_date->isPast() ? $job->start_date->diffInDays(now()) : 0;
                $percentComplete = $duration > 0 ? min(100, ($elapsed / $duration) * 100) : 0;
            @endphp
            
            <div class="mt-4">
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">Schedule Progress</span>
                    <span class="font-medium text-gray-900">{{ round($percentComplete) }}%</span>
                </div>
                <div class="w-full bg-brand-200 rounded-full h-3">
                    <div class="bg-brand-600 h-3 rounded-full transition-all" style="width: {{ $percentComplete }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    {{ $duration }} days total, {{ $elapsed }} days elapsed
                </p>
            </div>
        @endif
    </div>
    
    <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-12">
        <div class="text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2"/>
                <path d="M16 2v4M8 2v4M3 10h18" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Calendar View</h3>
            <p class="mt-2 text-sm text-gray-500">Interactive calendar and milestone tracking coming soon.</p>
        </div>
    </div>
</div>
