@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-gradient-to-br from-green-600 to-green-800 p-3 rounded-xl shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900">
                {{ $editMode ? 'Edit Weeding Data' : 'Weeding Calculator' }}
            </h1>
        </div>
        <p class="text-gray-600">Calculate labor requirements for various weeding tasks.</p>
    </div>

    @if(($mode ?? null) !== 'template' && $siteVisit)
        @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
    @else
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 p-4 rounded-lg mb-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="font-semibold text-blue-900">Template Mode</p>
                    <p class="text-sm text-blue-700">Build a weeding estimate without a site visit.</p>
                    @if(!empty($estimateId))
                        <p class="text-sm text-blue-600 mt-1">Target Estimate: #{{ $estimateId }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('calculators.weeding.calculate') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="mode" value="{{ $mode ?? '' }}">
        @if(!empty($estimateId))
            <input type="hidden" name="estimate_id" value="{{ $estimateId }}">
        @endif

        {{-- Edit Mode: Calculation ID --}}
        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        {{-- Required --}}
        @if(($mode ?? null) !== 'template' && $siteVisitId)
            <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">
        @endif

        {{-- 1. Crew & Logistics --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-bold mr-3">1</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Crew & Logistics
                </h2>
            </div>
            <div class="p-6">
                @include('calculators.partials.overhead_inputs')
            </div>
        </div>

        {{-- 2. Weeding Tasks --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-bold mr-3">2</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Labor Tasks
                </h2>
            </div>
            <div class="p-6">
                @php
                    $savedTasks = $formData['tasks'] ?? [];
                    $savedQuantities = [];

                    foreach ($savedTasks as $taskRow) {
                        $key = str_replace(' ', '_', strtolower($taskRow['task']));
                        $savedQuantities[$key] = $taskRow['qty'] ?? null;
                    }

                    $rates = \App\Models\ProductionRate::where('calculator', 'weeding')
                        ->orderBy('task')
                        ->get();
                @endphp

                @if ($rates->isEmpty())
                    <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border-l-4 border-yellow-400 p-4 rounded-lg mb-4">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-yellow-900">No weeding production rates found</p>
                                <p class="text-sm text-yellow-700 mt-1">
                                    Please add weeding rates in 
                                    <a href="{{ route('production-rates.index', ['calculator' => 'weeding']) }}" class="underline font-medium hover:text-yellow-900">Production Rates</a>
                                    or run <code class="bg-yellow-200 px-1 rounded">php artisan db:seed --class=ProductionRateSeeder</code>.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($rates as $rate)
                        @php
                            $key = $rate->task;
                            $label = ucwords(str_replace('_', ' ', $key));
                            $value = old("tasks.$key.qty", $savedQuantities[$key] ?? '');
                            $isAdvanced = str_contains($key, 'overgrown') || str_contains($key, 'palm');
                        @endphp

                        <div class="border border-gray-200 p-5 rounded-lg bg-gradient-to-br from-white to-gray-50 hover:shadow-md transition {{ $isAdvanced ? 'advanced-task hidden' : '' }}">
                            <label class="block font-semibold text-gray-900 mb-2">{{ $label }}</label>
                            <input type="number"
                                   name="tasks[{{ $key }}][qty]"
                                   step="any"
                                   min="0"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                   placeholder="Enter {{ $rate->unit }}"
                                   value="{{ $value }}">
                            <p class="text-sm text-gray-500 mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Rate: {{ number_format($rate->rate, 4) }} hrs/{{ $rate->unit }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 3. Job Notes --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-bold mr-3">3</span>
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Job Notes
                </h2>
            </div>
            <div class="p-6">
                <textarea name="job_notes"
                          rows="4"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                          placeholder="Enter any special instructions, site conditions, or notes...">{{ old('job_notes', $formData['job_notes'] ?? '') }}</textarea>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
                @if(($mode ?? null) === 'template')
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full">
                        <input type="text" 
                               name="template_name" 
                               class="w-full sm:flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
                               placeholder="Template name (e.g., Basic weeding pass)" 
                               value="{{ old('template_name') }}">
                        <select name="template_scope" class="w-full sm:w-48 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            <option value="global" {{ old('template_scope')==='global' ? 'selected' : '' }}>Global</option>
                            <option value="client" {{ old('template_scope')==='client' ? 'selected' : '' }}>This Client</option>
                            <option value="property" {{ old('template_scope')==='property' ? 'selected' : '' }}>This Property</option>
                        </select>
                        <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-md transition duration-200 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Save Template
                        </button>
                    </div>
                @else
                    <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-md transition duration-200 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        {{ $editMode ? 'Recalculate Weeding' : 'Calculate Weeding' }}
                    </button>

                    @if($siteVisit && $siteVisit->client)
                        <a href="{{ route('clients.show', $siteVisit->client->id) }}" class="w-full sm:w-auto px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-md transition duration-200 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to Client
                        </a>
                    @endif
                @endif
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('toggleAdvancedTasks');
        const advanced = document.querySelectorAll('.advanced-task');

        if (!toggle) {
            return;
        }

        function updateVisibility() {
            advanced.forEach(el => {
                el.classList.toggle('hidden', !toggle.checked);
            });
        }

        toggle.addEventListener('change', updateVisibility);
        updateVisibility(); // on load
    });
</script>
@endpush
