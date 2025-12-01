@extends('layouts.sidebar')

@section('title', 'New Timesheet')

@section('content')
<div class="space-y-6 max-w-4xl">
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
                <p class="text-xs uppercase tracking-[0.3em] text-gray-300">New Entry</p>
                <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">Create Timesheet</h1>
                <p class="text-sm text-gray-200 mt-1">Record work hours for a job</p>
            </div>
        </div>
    </section>

    {{-- Form --}}
    <form method="POST" action="{{ route('timesheets.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Timesheet Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Job Selection --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Job *</label>
                    <select name="job_id" id="job_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                            x-data="{ selected: '{{ old('job_id', $selectedJobId) }}' }"
                            x-model="selected">
                        <option value="">-- Select a Job --</option>
                        @foreach($jobs as $job)
                            <option value="{{ $job->id }}" {{ old('job_id', $selectedJobId) == $job->id ? 'selected' : '' }}>
                                {{ $job->job_number }} - {{ $job->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('job_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Work Area Selection --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Work Area (Optional)</label>
                    <select name="job_work_area_id" id="work_area_id"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- None / General --</option>
                        {{-- Will be populated via Alpine.js based on job selection --}}
                    </select>
                    @error('job_work_area_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Employee Selection --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Employee *</label>
                    <select name="user_id" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Select Employee --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', auth()->id()) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Work Date --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Work Date *</label>
                    <input type="date" name="work_date" required
                           value="{{ old('work_date', today()->format('Y-m-d')) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('work_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Clock In Time --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Clock In *</label>
                    <input type="time" name="clock_in" required
                           value="{{ old('clock_in', '08:00') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('clock_in')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Clock Out Time --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Clock Out *</label>
                    <input type="time" name="clock_out" required
                           value="{{ old('clock_out', '17:00') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('clock_out')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Break Minutes --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Break Time (minutes)</label>
                    <input type="number" name="break_minutes" min="0" max="480" step="15"
                           value="{{ old('break_minutes', 30) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="e.g., 30 for lunch break">
                    @error('break_minutes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Standard lunch break is 30 minutes</p>
                </div>

                {{-- Notes --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea name="notes" rows="4"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Work performed, issues encountered, etc.">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-4">
            <button type="submit"
                    class="flex-1 px-6 py-3 bg-brand-800 hover:bg-brand-700 text-white font-medium rounded-lg transition">
                Create Timesheet
            </button>
            <a href="{{ route('timesheets.index') }}"
               class="px-6 py-3 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Populate work areas based on selected job
    document.getElementById('job_id').addEventListener('change', function() {
        const jobId = this.value;
        const workAreaSelect = document.getElementById('work_area_id');
        
        // Clear existing options
        workAreaSelect.innerHTML = '<option value="">-- None / General --</option>';
        
        if (!jobId) return;
        
        // Fetch work areas for this job
        const jobs = @json($jobs);
        const selectedJob = jobs.find(j => j.id == jobId);
        
        if (selectedJob && selectedJob.work_areas) {
            selectedJob.work_areas.forEach(area => {
                const option = document.createElement('option');
                option.value = area.id;
                option.textContent = area.name;
                workAreaSelect.appendChild(option);
            });
        }
    });
    
    // Trigger on page load if job is pre-selected
    if (document.getElementById('job_id').value) {
        document.getElementById('job_id').dispatchEvent(new Event('change'));
    }
</script>
@endpush
@endsection
