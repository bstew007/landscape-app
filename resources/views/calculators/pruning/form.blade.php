@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4" x-data="pruningCalculator()">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-gradient-to-br from-green-700 to-green-900 p-3 rounded-xl shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-4xl font-bold text-gray-900">
                    {{ $editMode ? 'Edit Pruning Calculation' : 'Pruning Calculator' }}
                </h1>
                <p class="text-gray-600 mt-1">Calculate labor hours for tree and shrub pruning tasks</p>
            </div>
        </div>
    </div>

    @if(($mode ?? null) !== 'template' && ($siteVisit ?? null))
        @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
    @else
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg shadow-sm mb-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-blue-900">Template Mode</p>
                    <p class="text-sm text-blue-800">Build a Pruning template without a site visit.</p>
                    @if(!empty($estimateId))
                        <p class="text-sm text-blue-700 mt-1">Target Estimate: #{{ $estimateId }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('calculators.pruning.calculate') }}">
        @csrf
        <input type="hidden" name="mode" value="{{ $mode ?? '' }}">
        @if(!empty($estimateId))
            <input type="hidden" name="estimate_id" value="{{ $estimateId }}">
        @endif

        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        @if(($mode ?? null) !== 'template')
            <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">
        @endif

        {{-- Section 1: Crew & Logistics --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="bg-white/20 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm font-bold">1</span>
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Crew & Logistics
                </h2>
            </div>
            <div class="p-6">
                @include('calculators.partials.overhead_inputs')
            </div>
        </div>

        {{-- Section 2: Pruning Tasks --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="bg-white/20 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm font-bold">2</span>
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    Labor Tasks
                </h2>
            </div>
            <div class="p-6">
                {{-- Toggle for Advanced Tasks --}}
                <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" 
                               id="toggleAdvancedTasks" 
                               class="form-checkbox h-5 w-5 text-brand-700 rounded focus:ring-brand-500 focus:ring-2"
                               x-model="showAdvanced">
                        <span class="ml-3 text-sm font-bold text-gray-900">Show Palm Pruning & Overgrown Tasks</span>
                    </label>
                    <p class="text-xs text-gray-600 mt-1 ml-8">Toggle to display advanced pruning options</p>
                </div>

                @php
                    $savedTasks = $formData['tasks'] ?? [];
                    $savedQuantities = [];

                    foreach ($savedTasks as $taskRow) {
                        $key = str_replace(' ', '_', strtolower($taskRow['task']));
                        $savedQuantities[$key] = $taskRow['qty'] ?? null;
                    }

                    $rates = \App\Models\ProductionRate::where('calculator', 'pruning')
                        ->orderBy('task')
                        ->get();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($rates as $rate)
                        @php
                            $key = $rate->task;
                            $label = ucwords(str_replace('_', ' ', $key));
                            $value = old("tasks.$key.qty", $savedQuantities[$key] ?? '');
                            $isAdvanced = str_contains($key, 'overgrown') || str_contains($key, 'palm');
                        @endphp

                        <div class="bg-gradient-to-br from-gray-50 to-gray-100 border border-gray-200 p-5 rounded-lg hover:shadow-md transition-shadow"
                             @if($isAdvanced) x-show="showAdvanced" x-transition @endif>
                            <label class="block font-bold text-gray-900 mb-2">{{ $label }}</label>
                            <input type="number"
                                   name="tasks[{{ $key }}][qty]"
                                   step="any"
                                   min="0"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 focus:border-transparent transition"
                                   placeholder="Enter quantity"
                                   value="{{ $value }}">
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-sm text-gray-600">
                                    <span class="font-semibold">Rate:</span> {{ number_format($rate->rate, 4) }} hrs/{{ $rate->unit }}
                                </p>
                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">
                                    {{ ucwords($rate->unit) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Section 3: Job Notes --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <span class="bg-white/20 rounded-full w-8 h-8 flex items-center justify-center mr-3 text-sm font-bold">3</span>
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Job Notes
                </h2>
            </div>
            <div class="p-6">
                <textarea name="job_notes" 
                          rows="4" 
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 focus:border-transparent transition"
                          placeholder="Add any additional notes about this pruning job...">{{ old('job_notes', $formData['job_notes'] ?? '') }}</textarea>
                <p class="mt-2 text-sm text-gray-500">Optional: Special instructions, tree species, or other relevant details</p>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row gap-4">
            @if(($mode ?? null) === 'template')
                <div class="flex flex-col sm:flex-row gap-3 w-full">
                    <input type="text" 
                           name="template_name" 
                           class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 focus:border-transparent transition" 
                           placeholder="Template name (e.g., Spring tree pruning)" 
                           value="{{ old('template_name') }}">
                    <select name="template_scope" 
                            class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 focus:border-transparent transition">
                        <option value="global" {{ old('template_scope')==='global' ? 'selected' : '' }}>Global</option>
                        <option value="client" {{ old('template_scope')==='client' ? 'selected' : '' }}>This Client</option>
                        <option value="property" {{ old('template_scope')==='property' ? 'selected' : '' }}>This Property</option>
                    </select>
                    <button type="submit" 
                            class="px-8 py-3 bg-brand-800 hover:bg-brand-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center whitespace-nowrap">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Save Template
                    </button>
                </div>
            @else
                <button type="submit" 
                        class="flex-1 px-8 py-4 bg-brand-800 hover:bg-brand-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    {{ $editMode ? 'Recalculate' : 'Calculate Pruning Estimate' }}
                </button>

                <a href="{{ route('clients.show', $siteVisitId) }}" 
                   class="px-8 py-4 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-all duration-200 flex items-center justify-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Client
                </a>
            @endif
        </div>
    </form>
</div>

<script>
function pruningCalculator() {
    return {
        showAdvanced: false
    };
}
</script>
@endsection
