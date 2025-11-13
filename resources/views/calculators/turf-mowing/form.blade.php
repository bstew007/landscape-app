@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    @include('calculators.partials.form_header', [
        'title' => $editMode ? '✏️ Edit Turf Maintenance Estimate' : '🌱 Turf Mowing Calculator',
        'subtitle' => 'Enter the square footage and linear footage covered by each task. Mowing, trimming, edging, and blowing quantities feed directly into labor hours using your production rates.',
    ])

    @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])

    <form method="POST" action="{{ route('calculators.turf_mowing.calculate') }}">
        @csrf

        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        <div class="mb-6">
            @include('calculators.partials.section_heading', ['title' => 'Crew & Logistics'])
            @include('calculators.partials.overhead_inputs')
        </div>

        <div class="mb-6">
            @include('calculators.partials.section_heading', ['title' => 'Turf Tasks'])
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

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
            <button type="submit" class="btn btn-secondary">
                {{ $editMode ? '🔄 Recalculate Turf Maintenance' : '🧮 Calculate Turf Maintenance' }}
            </button>

            <a href="{{ route('clients.show', $siteVisit->client->id ?? $siteVisitId) }}" class="btn btn-muted">
                🔙 Back to Client
            </a>
        </div>
    </form>
</div>
@endsection
