@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10 space-y-6">
    @include('calculators.partials.form_header', [
        'title' => $editMode ? '✏️ Edit Planting Data' : '🌱 Planting Calculator',
        'subtitle' => 'Estimate labor and materials for planting annuals, containers, and trees.',
    ])

    @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])

    <form method="POST" action="{{ route('calculators.planting.calculate') }}">
        @csrf

        @if ($editMode ?? false)
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        <div class="mb-6">
            @include('calculators.partials.section_heading', ['title' => 'Crew & Logistics'])
            @include('calculators.partials.overhead_inputs')
        </div>

        <div class="mb-6">
            @include('calculators.partials.section_heading', [
                'title' => 'Planting Quantities & Pricing',
                'hint' => 'Labor rates already include facing + watering',
            ])

            @php
                $rates = \App\Models\ProductionRate::where('calculator', 'planting')
                    ->orderBy('task')
                    ->get();
                $savedQty = $formData['task_inputs'] ?? [];
                $unitCosts = $formData['unit_costs'] ?? [];
            @endphp

            @if ($rates->isEmpty())
                <div class="p-4 rounded bg-yellow-100 border border-yellow-300 text-sm text-yellow-900">
                    No planting production rates found. Please run
                    <code>php artisan db:seed --class=ProductionRateSeeder</code>
                    on this environment.
                </div>
            @endif

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

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
            <button type="submit" class="btn btn-secondary">
                {{ $editMode ? '🔄 Recalculate Planting' : '🌱 Calculate Planting' }}
            </button>

            <a href="{{ route('clients.show', $siteVisit->client->id ?? $clientId) }}" class="btn btn-muted">
                ⬅️ Back to Client
            </a>
        </div>
    </form>
</div>
@endsection
