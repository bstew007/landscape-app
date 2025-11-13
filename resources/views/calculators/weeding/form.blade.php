@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    @include('calculators.partials.form_header', [
        'title' => $editMode ? '✏️ Edit Weeding Data' : '🌿 Weeding Calculator',
    ])

    @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])

    <form method="POST" action="{{ route('calculators.weeding.calculate') }}">
        @csrf

        {{-- Edit Mode: Calculation ID --}}
        @if ($editMode && isset($calculation))
            <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
        @endif

        {{-- Required --}}
        <input type="hidden" name="site_visit_id" value="{{ $siteVisitId }}">

        {{-- Crew & Logistics --}}
        <div class="mb-6">
            @include('calculators.partials.section_heading', ['title' => 'Crew & Logistics'])
            @include('calculators.partials.overhead_inputs')
        </div>

        {{-- Task Inputs from DB --}}
        <div class="mb-6">
            @include('calculators.partials.section_heading', ['title' => 'Weeding Tasks'])

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
                {{ $editMode ? '🔄 Recalculate Weeding' : '🧮 Calculate Weeding Data' }}
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
