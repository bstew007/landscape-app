@extends('layouts.sidebar')

@section('title', 'Edit Timesheet')

@section('content')
<div class="space-y-6 max-w-4xl">
    {{-- Modern Header --}}
    <section class="rounded-2xl bg-gradient-to-r from-gray-800 to-gray-700 text-white p-6 sm:p-8 shadow-lg border border-brand-700/40">
        <div class="flex items-center gap-4">
            <div class="h-16 w-16 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-white">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-xs uppercase tracking-[0.3em] text-gray-300">Edit Entry</p>
                <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">Update Timesheet</h1>
                <p class="text-sm text-gray-200 mt-1">{{ $timesheet->work_date->format('F d, Y') }}</p>
            </div>
        </div>
    </section>

    {{-- Form --}}
    <form method="POST" action="{{ route('timesheets.update', $timesheet) }}" class="space-y-6">
        @csrf
        @method('PATCH')

        <div class="bg-white rounded-2xl border border-brand-100 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Timesheet Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Job Selection --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Job *</label>
                    <select name="job_id" id="job_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="">-- Select a Job --</option>
                        @foreach($jobs as $job)
                            <option value="{{ $job->id }}" {{ old('job_id', $timesheet->job_id) == $job->id ? 'selected' : '' }}>
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
                    <label class="block text-sm font-bold text-gray-700 mb-2">Work Area (Optional)</label>
                    <select name="job_work_area_id" id="work_area_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="">-- None / General --</option>
                        @if($timesheet->job && $timesheet->job->workAreas)
                            @foreach($timesheet->job->workAreas as $area)
                                <option value="{{ $area->id }}" {{ old('job_work_area_id', $timesheet->job_work_area_id) == $area->id ? 'selected' : '' }}>
                                    {{ $area->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('job_work_area_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Employee Selection --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Employee *</label>
                    <select name="user_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                        <option value="">-- Select Employee --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $timesheet->user_id) == $user->id ? 'selected' : '' }}>
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
                    <label class="block text-sm font-bold text-gray-700 mb-2">Work Date *</label>
                    <input type="date" name="work_date" required
                           value="{{ old('work_date', $timesheet->work_date->format('Y-m-d')) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    @error('work_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Clock In Time --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Clock In *</label>
                    <input type="time" name="clock_in" required
                           value="{{ old('clock_in', $timesheet->clock_in?->format('H:i')) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    @error('clock_in')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Clock Out Time --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Clock Out *</label>
                    <input type="time" name="clock_out" required
                           value="{{ old('clock_out', $timesheet->clock_out?->format('H:i')) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                    @error('clock_out')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Break Minutes --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Break Time (minutes)</label>
                    <input type="number" name="break_minutes" min="0" max="480" step="15"
                           value="{{ old('break_minutes', $timesheet->break_minutes) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                           placeholder="e.g., 30 for lunch break">
                    @error('break_minutes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Standard lunch break is 30 minutes</p>
                </div>

                {{-- Notes --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea name="notes" rows="4"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                              placeholder="Work performed, issues encountered, etc.">{{ old('notes', $timesheet->notes) }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex gap-4">
            <button type="submit"
                    class="flex-1 px-6 py-3 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-sm transition">
                Update Timesheet
            </button>
            <a href="{{ route('timesheets.show', $timesheet) }}"
               class="px-6 py-3 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
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
</script>
@endpush
@endsection
