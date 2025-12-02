@extends('layouts.sidebar')

@section('title', $job->job_number . ' - ' . $job->title)

@section('content')
<div class="space-y-6" x-data="{ tab: '{{ request('tab', 'info') }}' }">
    {{-- Modern Header --}}
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
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
                        <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">{{ $job->job_number }}</p>
                        @include('jobs.partials.status-badge', ['status' => $job->status])
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white">{{ $job->title }}</h1>
                    <p class="text-sm text-brand-100/85">
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

    {{-- Tabs Bar --}}
    <nav class="flex flex-wrap border-b border-gray-200 text-sm font-medium text-gray-600">
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='info', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='info' }"
            @click="tab='info'">
            Job Info
        </button>
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='costing', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='costing' }"
            @click="tab='costing'">
            Job Costing
        </button>
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='timesheets', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='timesheets' }"
            @click="tab='timesheets'">
            Timesheet Tracking
        </button>
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='materials', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='materials' }"
            @click="tab='materials'">
            Materials & Equipment
        </button>
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='bills', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='bills' }"
            @click="tab='bills'">
            Vendor Bills
        </button>
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='schedule', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='schedule' }"
            @click="tab='schedule'">
            Schedule
        </button>
        <button class="px-4 py-2 -mb-px border-b-2 transition-colors"
            :class="{ 'border-brand-500 text-brand-700 font-semibold' : tab==='invoicing', 'border-transparent hover:text-gray-900 hover:border-gray-300' : tab!=='invoicing' }"
            @click="tab='invoicing'">
            Invoicing
        </button>
    </nav>

    {{-- Tab Content --}}
    <div x-show="tab==='info'">
        @include('jobs.tabs.info', ['job' => $job])
    </div>

    <div x-show="tab==='costing'">
        @include('jobs.tabs.costing', ['job' => $job])
    </div>

    <div x-show="tab==='timesheets'">
        @include('jobs.tabs.timesheets', ['job' => $job])
    </div>

    <div x-show="tab==='materials'">
        @include('jobs.tabs.materials', ['job' => $job])
    </div>

    <div x-show="tab==='bills'">
        @include('jobs.tabs.bills', ['job' => $job])
    </div>

    <div x-show="tab==='schedule'">
        @include('jobs.tabs.schedule', ['job' => $job])
    </div>

    <div x-show="tab==='invoicing'">
        @include('jobs.tabs.invoicing', ['job' => $job])
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('timeclockWidget', () => ({
        loading: false,
        error: null,
        workAreaId: '',
        elapsedTime: '00:00:00',
        intervalId: null,
        clockInTime: @json($activeTimesheet?->clock_in?->timestamp ?? null),
        
        init() {
            if (this.clockInTime) {
                this.startTimer();
            }
        },
        
        startTimer() {
            this.intervalId = setInterval(() => {
                const now = Math.floor(Date.now() / 1000);
                const elapsed = now - this.clockInTime;
                
                const hours = Math.floor(elapsed / 3600);
                const minutes = Math.floor((elapsed % 3600) / 60);
                const seconds = elapsed % 60;
                
                this.elapsedTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }, 1000);
        },
        
        async clockIn() {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await fetch('{{ route('timesheets.clock-in') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        job_id: {{ $job->id }},
                        job_work_area_id: this.workAreaId,
                        work_date: new Date().toISOString().split('T')[0]
                    })
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to clock in');
                }
                
                // Reload page to show active timesheet
                window.location.reload();
            } catch (err) {
                this.error = err.message;
                this.loading = false;
            }
        },
        
        async clockOut(timesheetId) {
            this.loading = true;
            this.error = null;
            
            try {
                const response = await fetch(`{{ route('timesheets.clock-out', ':id') }}`.replace(':id', timesheetId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to clock out');
                }
                
                clearInterval(this.intervalId);
                
                // Reload page to show clock in form
                window.location.reload();
            } catch (err) {
                this.error = err.message;
                this.loading = false;
            }
        }
    }));
});
</script>
@endpush
@endsection
