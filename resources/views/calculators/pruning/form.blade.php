@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">
        {{ $editMode ? '✏️ Edit Pruning Data' : '🌿 Pruning Calculator' }}
    </h1>

    @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])

    <form method="POST" action="{{ route('calculators.pruning.calculate') }}">
        @csrf

        {{-- Edit Mode: Calculation ID --}}
        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        {{-- Required --}}
        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        {{-- Crew & Logistics --}}
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Crew & Logistics</h2>
            @include('calculators.partials.overhead_inputs')
        </div>

        {{-- Toggle for Advanced Tasks --}}
        <div class="mb-4">
            <label class="inline-flex items-center">
                <input type="checkbox" id="toggleAdvancedTasks" class="form-checkbox h-5 w-5 text-blue-600">
                <span class="ml-2 text-sm font-medium">Show Palm Pruning & Overgrown Tasks</span>
            </label>
        </div>

        {{-- Task Inputs from DB --}}
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Pruning Tasks</h2>

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

                    <div class="border p-4 rounded bg-gray-50 {{ $isAdvanced ? 'advanced-task hidden' : '' }}">
                        <label class="block font-semibold mb-1">{{ $label }} ({{ $rate->unit }})</label>
                        <input type="number"
                               name="tasks[{{ $key }}][qty]"
                               step="any"
                               min="0"
                               class="form-input w-full"
                               placeholder="Enter quantity"
                               value="{{ $value }}">
                        <p class="text-sm text-gray-500">Rate: {{ number_format($rate->rate, 4) }} hrs/{{ $rate->unit }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:gap-6">
            <button type="submit" class="btn btn-secondary">
                {{ $editMode ? '🔄 Recalculate Pruning' : '🧮 Calculate Pruning Estimate' }}
            </button>

            <a href="{{ route('clients.show', $siteVisitId) }}" class="btn btn-muted">
                🔙 Back to Client
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('toggleAdvancedTasks');
        const advanced = document.querySelectorAll('.advanced-task');

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
