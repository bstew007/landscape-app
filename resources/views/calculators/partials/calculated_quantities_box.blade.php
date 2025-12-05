{{--
    Calculated Material Quantities Display Box
    
    Props needed:
    - $color: 'blue', 'amber', 'green', etc. (for styling)
    - $quantities: array of ['label' => 'Display Label', 'value' => 'x-text binding or static value', 'suffix' => 'unit']
    
    Example usage:
    @include('calculators.partials.calculated_quantities_box', [
        'color' => 'blue',
        'quantities' => [
            ['label' => 'Excavation', 'value' => 'excavationCY.toFixed(2)', 'suffix' => 'cy'],
            ['label' => 'Area', 'value' => 'area.toFixed(2)', 'suffix' => 'sqft'],
        ]
    ])
--}}

@php
    $color = $color ?? 'blue';
    $colorClasses = [
        'blue' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-500',
            'text' => 'text-blue-700',
            'textBold' => 'text-blue-900',
        ],
        'amber' => [
            'bg' => 'bg-amber-50',
            'border' => 'border-amber-500',
            'text' => 'text-amber-700',
            'textBold' => 'text-amber-900',
        ],
        'green' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-500',
            'text' => 'text-green-700',
            'textBold' => 'text-green-900',
        ],
    ];
    $classes = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<div class="{{ $classes['bg'] }} border-l-4 {{ $classes['border'] }} rounded-lg p-4">
    <h3 class="font-semibold {{ $classes['textBold'] }} mb-2">Calculated Material Quantities</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        @foreach($quantities as $item)
            <div>
                <p class="{{ $classes['text'] }}">{{ $item['label'] }}</p>
                @if(isset($item['alpine']) && $item['alpine'])
                    <p class="font-bold {{ $classes['textBold'] }}" x-text="{{ $item['value'] }} + ' {{ $item['suffix'] ?? '' }}'"></p>
                @else
                    <p class="font-bold {{ $classes['textBold'] }}">{{ $item['value'] }} {{ $item['suffix'] ?? '' }}</p>
                @endif
            </div>
        @endforeach
    </div>
</div>
