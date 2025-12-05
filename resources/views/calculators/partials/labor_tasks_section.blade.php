{{--
    Labor Tasks Section with Production Rate Inputs
    
    Props needed:
    - $calculator: calculator name (e.g., 'syn_turf', 'paver_patio')
    - $formData: form data array for old/saved values
    - $color: 'blue', 'amber', 'green', etc. (for ring color on focus)
    - $includeExcavation: boolean - whether to include universal excavation rates (default: true)
    
    Example usage:
    @include('calculators.partials.labor_tasks_section', [
        'calculator' => 'paver_patio',
        'formData' => $formData,
        'color' => 'amber',
        'includeExcavation' => true
    ])
--}}

@php
    $color = $color ?? 'green';
    $includeExcavation = $includeExcavation ?? true;
    
    $savedTasks = $formData['tasks'] ?? [];
    $savedQuantities = [];
    foreach ($savedTasks as $taskRow) {
        $key = str_replace(' ', '_', strtolower($taskRow['task'] ?? $taskRow['task_name'] ?? ''));
        $savedQuantities[$key] = $taskRow['qty'] ?? null;
    }

    $rates = \App\Models\ProductionRate::where('calculator', $calculator)->orderBy('task')->get();
    $excavationRates = $includeExcavation 
        ? \App\Models\ProductionRate::where('calculator', 'excavation')->orderBy('task')->get()
        : collect([]);
    
    $ringColors = [
        'blue' => 'focus:ring-blue-500',
        'amber' => 'focus:ring-amber-500',
        'green' => 'focus:ring-green-500',
    ];
    $ringClass = $ringColors[$color] ?? $ringColors['green'];
@endphp

@if ($rates->isEmpty() && $excavationRates->isEmpty())
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
        <p class="font-semibold text-yellow-900">No production rates found</p>
        <p class="text-sm text-yellow-700 mt-1">
            Please add {{ $calculator }} rates in 
            <a href="{{ route('production-rates.index', ['calculator' => $calculator]) }}" class="underline font-medium">Production Rates</a>
        </p>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- Excavation Tasks (from universal excavation rates) --}}
    @if($includeExcavation)
        @foreach ($excavationRates as $rate)
            @php
                $key = $rate->task;
                $label = ucwords(str_replace('_', ' ', $key));
                $value = old("tasks.$key.qty", $savedQuantities[$key] ?? '');
            @endphp
            <div class="border border-gray-200 p-4 rounded-lg bg-gradient-to-br from-white to-gray-50 hover:shadow-md transition">
                <label class="block font-semibold text-gray-900 mb-2">{{ $label }}</label>
                <input type="number"
                       name="tasks[{{ $key }}][qty]"
                       step="any"
                       min="0"
                       x-model="laborQuantities.{{ $key }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 {{ $ringClass }} focus:border-transparent"
                       placeholder="Enter {{ $rate->unit }}"
                       value="{{ $value }}">
                <p class="text-sm text-gray-500 mt-2">
                    ⏱ Rate: {{ number_format($rate->rate, 4) }} hrs/{{ $rate->unit }}
                </p>
            </div>
        @endforeach
    @endif

    {{-- Calculator-specific Tasks --}}
    @foreach ($rates as $rate)
        @php
            $key = $rate->task;
            $label = ucwords(str_replace('_', ' ', $key));
            $value = old("tasks.$key.qty", $savedQuantities[$key] ?? '');
        @endphp
        <div class="border border-gray-200 p-4 rounded-lg bg-gradient-to-br from-white to-gray-50 hover:shadow-md transition">
            <label class="block font-semibold text-gray-900 mb-2">{{ $label }}</label>
            <input type="number"
                   name="tasks[{{ $key }}][qty]"
                   step="any"
                   min="0"
                   x-model="laborQuantities.{{ $key }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 {{ $ringClass }} focus:border-transparent"
                   placeholder="Enter {{ $rate->unit }}"
                   value="{{ $value }}">
            <p class="text-sm text-gray-500 mt-2">
                ⏱ Rate: {{ number_format($rate->rate, 4) }} hrs/{{ $rate->unit }}
            </p>
        </div>
    @endforeach
</div>
