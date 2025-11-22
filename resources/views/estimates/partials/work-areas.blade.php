@php $allItems = $estimate->items; @endphp

<div id="areasContainer" class="space-y-6">
    @foreach ($estimate->areas as $area)
        @include('estimates.partials.area', [
            'estimate' => $estimate,
            'area' => $area,
            'allItems' => $allItems,
            'costCodes' => $costCodes ?? []
        ])
    @endforeach
</div>
