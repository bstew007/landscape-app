{{-- Job Info & Settings Tab --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Info --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Basic Job Details --}}
        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Job Details</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Job Number</dt>
                    <dd class="text-sm text-gray-900 mt-1 font-semibold">{{ $job->job_number }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="text-sm text-gray-900 mt-1">
                        @include('jobs.partials.status-badge', ['status' => $job->status])
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Client</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $job->client->company_name ?? $job->client->full_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Property</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $job->property->address ?? 'No property assigned' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                    <dd class="text-sm text-gray-900 mt-1">
                        {{ $job->start_date ? $job->start_date->format('M j, Y') : 'Not set' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Expected Completion</dt>
                    <dd class="text-sm text-gray-900 mt-1">
                        {{ $job->expected_completion_date ? $job->expected_completion_date->format('M j, Y') : 'Not set' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Foreman</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $job->foreman->name ?? 'Not assigned' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Source Estimate</dt>
                    <dd class="text-sm text-gray-900 mt-1">
                        <a href="{{ route('estimates.show', $job->estimate) }}" 
                           class="text-brand-700 hover:text-brand-900 font-medium">
                            Estimate #{{ $job->estimate->id }}
                        </a>
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Notes --}}
        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
            
            @if($job->notes || $job->crew_notes)
                <div class="space-y-4">
                    @if($job->notes)
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Job Notes</h4>
                            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $job->notes }}</p>
                        </div>
                    @endif
                    
                    @if($job->crew_notes)
                        <div class="pt-4 border-t border-brand-200">
                            <h4 class="text-sm font-medium text-gray-700 mb-1">Crew Notes</h4>
                            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ $job->crew_notes }}</p>
                        </div>
                    @endif
                </div>
            @else
                <p class="text-sm text-gray-500">No notes added yet</p>
            @endif
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Job Info Summary Card --}}
        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Job Info</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $job->created_at->format('M j, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $job->updated_at->format('M j, Y g:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Work Areas</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $job->workAreas->count() }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Crew Size</dt>
                    <dd class="text-sm text-gray-900 mt-1">{{ $job->crew_size ?? 'Not set' }}</dd>
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
                <div class="text-xs text-gray-500 mt-2">
                    {{ number_format($job->actual_labor_hours, 1) }} of {{ number_format($job->estimated_labor_hours, 1) }} hours
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
