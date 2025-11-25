@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    @include('calculators.partials.form_header', [
        'title' => $editMode ? '✏️ Edit Weeding Data' : '🌿 Weeding Calculator',
    ])

    @if(($mode ?? null) !== 'template' && $siteVisit)
        @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
    @elseif(($mode ?? null) === 'template')
        <div class="bg-white p-4 rounded border mb-6">
            <p class="text-sm text-gray-700">Template Mode — build a Weeding template without a site visit.</p>
            @if(!empty($estimateId))
                <p class="text-sm text-gray-500">Target Estimate: #{{ $estimateId }}</p>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('calculators.weeding.calculate') }}">
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

            @if ($rates->isEmpty())
                <div class="p-4 rounded bg-yellow-100 border border-yellow-300 text-sm text-yellow-900 mb-4">
                    <strong>No weeding production rates found.</strong> Please add weeding rates in 
                    <a href="{{ route('production-rates.index', ['calculator' => 'weeding']) }}" class="underline font-medium">Production Rates</a>
                    or run <code class="bg-yellow-200 px-1 rounded">php artisan db:seed --class=ProductionRateSeeder</code>.
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
            @if(($mode ?? null) === 'template')
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full">
                    <input type="text" name="template_name" class="form-input w-full sm:w-72" placeholder="Template name (e.g., Basic weeding pass)" value="{{ old('template_name') }}">
                    <select name="template_scope" class="form-select w-full sm:w-48">
                        <option value="global" {{ old('template_scope')==='global' ? 'selected' : '' }}>Global</option>
                        <option value="client" {{ old('template_scope')==='client' ? 'selected' : '' }}>This Client</option>
                        <option value="property" {{ old('template_scope')==='property' ? 'selected' : '' }}>This Property</option>
                    </select>
                    <button type="submit" class="btn btn-secondary">💾 Save Template</button>
                </div>
            @else
                <button type="submit" class="btn btn-secondary">
                    {{ $editMode ? '🔄 Recalculate Weeding' : '🧮 Calculate Weeding Data' }}
                </button>

                @if($siteVisit && $siteVisit->client)
                    <a href="{{ route('clients.show', $siteVisit->client->id) }}" class="btn btn-muted">🔙 Back to Client</a>
                @endif
            @endif
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
