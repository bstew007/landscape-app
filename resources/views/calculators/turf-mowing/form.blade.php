@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">
        {{ $editMode ? '✏️ Edit Turf Maintenance Estimate' : '🌱 Turf Mowing Calculator' }}
    </h1>

    <p class="text-gray-600 mb-6">
        Enter the square footage and linear footage covered by each task. Mowing, trimming, edging, and blowing
        quantities feed directly into labor hours using your production rates.
    </p>

    <form method="POST" action="{{ route('calculators.turf_mowing.calculate') }}">
        @csrf

        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Crew & Logistics</h2>
            @include('calculators.partials.overhead_inputs')
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Turf Tasks</h2>
            @php
                $savedTasks = $formData['tasks'] ?? [];
                $savedQuantities = [];

                foreach ($savedTasks as $taskRow) {
                    $key = str_replace(' ', '_', strtolower($taskRow['task']));
                    $savedQuantities[$key] = $taskRow['qty'] ?? null;
                }

                $rates = \App\Models\ProductionRate::where('calculator', 'turf_mowing')
                    ->orderBy('task')
                    ->get();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($rates as $rate)
                    @php
                        $key = $rate->task;
                        $label = ucwords(str_replace('_', ' ', $key));
                        $value = old("tasks.$key.qty", $savedQuantities[$key] ?? '');
                        $unitLabel = $rate->unit === 'linear ft' ? 'Linear Feet' : 'Square Feet';
                    @endphp
                    <div class="border p-4 rounded bg-gray-50">
                        <label class="block font-semibold mb-1">{{ $label }} ({{ $unitLabel }})</label>
                        <input type="number"
                               name="tasks[{{ $key }}][qty]"
                               step="any"
                               min="0"
                               class="form-input w-full"
                               placeholder="Enter {{ strtolower($unitLabel) }}"
                               value="{{ $value }}">
                        <p class="text-sm text-gray-500">Rate: {{ number_format($rate->rate, 4) }} hrs/{{ $rate->unit }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-6">
            <label class="block font-semibold" for="job_notes">Job Notes (optional)</label>
            <textarea name="job_notes" id="job_notes" rows="4"
                      class="form-textarea w-full"
                      placeholder="Add mowing patterns, obstacles, dump locations, etc.">{{ old('job_notes', $formData['job_notes'] ?? '') }}</textarea>
        </div>

        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
            {{ $editMode ? '🔄 Recalculate Turf Maintenance' : '🧮 Calculate Turf Maintenance' }}
        </button>

        <div class="mt-6">
            <a href="{{ route('clients.show', $siteVisit->client->id ?? $siteVisitId) }}"
               class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded-lg font-semibold">
                🔙 Back to Client
            </a>
        </div>
    </form>
</div>
@endsection
