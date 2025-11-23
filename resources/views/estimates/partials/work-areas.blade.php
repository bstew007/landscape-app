@php $allItems = $estimate->items; @endphp

@php
    $recentAreaId = $recentAreaId ?? null;
    $defaultOpenAreaId = $recentAreaId ?? optional($estimate->areas->first())->id;
@endphp

<div id="areasContainer" class="space-y-1">
    @foreach ($estimate->areas as $area)
        @include('estimates.partials.area', [
            'estimate' => $estimate,
            'area' => $area,
            'allItems' => $allItems,
            'costCodes' => $costCodes ?? [],
            'initiallyOpen' => (string) $area->id === (string) $defaultOpenAreaId,
        ])
    @endforeach
</div>
