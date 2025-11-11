@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10 space-y-6">
    <div>
        <h1 class="text-3xl font-bold">
            {{ $editMode ? '✏️ Edit Planting Data' : '🌱 Planting Calculator' }}
        </h1>
        <p class="text-gray-600">Estimate labor and materials for planting annuals, containers, and trees.</p>
    </div>

    <form method="POST" action="{{ route('calculators.planting.calculate') }}">
        @csrf

        @if ($editMode ?? false)
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Crew & Logistics</h2>
            @include('calculators.partials.overhead_inputs')
        </div>

        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-xl font-semibold">Planting Quantities & Pricing</h2>
                <span class="text-sm text-gray-500">Labor rates already include facing + watering</span>
            </div>

            @php
                $rates = \App\Models\ProductionRate::where('calculator', 'planting')
                    ->orderBy('task')
                    ->get();
                $savedQty = $formData['task_inputs'] ?? [];
                $unitCosts = $formData['unit_costs'] ?? [];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($rates as $rate)
                    @php
                        $key = $rate->task;
                        $label = \Illuminate\Support\Str::headline(str_replace('_', ' ', $key));
                        $qtyValue = old("tasks.$key.qty", $savedQty[$key] ?? '');
                        $costValue = old("tasks.$key.unit_cost", $unitCosts[$key] ?? '');
                    @endphp
                    <div class="border rounded-lg p-4 bg-white shadow-sm">
                        <label class="block font-semibold mb-2">{{ $label }} ({{ $rate->unit }})</label>
                        <div class="space-y-2">
                            <input
                                type="number"
                                name="tasks[{{ $key }}][qty]"
                                min="0"
                                step="any"
                                value="{{ $qtyValue }}"
                                class="form-input w-full"
                                placeholder="Quantity"
                            >
                            <input
                                type="number"
                                name="tasks[{{ $key }}][unit_cost]"
                                min="0"
                                step="0.01"
                                value="{{ $costValue }}"
                                class="form-input w-full"
                                placeholder="Unit material cost (optional)"
                            >
                            <p class="text-xs text-gray-500">
                                Labor production: {{ number_format($rate->rate, 4) }} hrs/{{ $rate->unit }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mb-6">
            <label class="block font-semibold mb-1">Job Notes</label>
            <textarea
                name="job_notes"
                rows="4"
                class="form-textarea w-full"
                placeholder="Irrigation, soil conditions, staging notes..."
            >{{ old('job_notes', $formData['job_notes'] ?? '') }}</textarea>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                {{ $editMode ? '🔄 Recalculate Planting' : '🌱 Calculate Planting' }}
            </button>

            <a href="{{ route('clients.show', $siteVisit->client->id ?? $clientId) }}"
               class="inline-flex items-center px-5 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-semibold">
                ⬅️ Back to Client
            </a>
        </div>
    </form>
</div>
@endsection
