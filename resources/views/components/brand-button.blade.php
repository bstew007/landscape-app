@props(['variant' => 'solid', 'href' => null, 'type' => 'button', 'size' => 'md'])
@php
    $base = 'inline-flex items-center rounded font-medium whitespace-nowrap transition-colors gap-2';
    $sizeClass = [
        'sm' => 'h-9 px-3 text-sm',
        'md' => 'h-10 px-4 text-sm',
    ][$size] ?? 'h-10 px-4 text-sm';
    $classes = [
        // Primary action (Save) -> Green
        'solid' => 'bg-green-600 text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500',
        // Secondary/outline -> Amber for Cancel
        'outline' => 'border border-amber-700 text-amber-800 hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-500',
        // Ghost stays neutral brand for links
        'ghost' => 'text-brand-700 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500',
    ][$variant] ?? 'bg-green-600 text-white hover:bg-green-700';
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base.' '.$sizeClass.' '.$classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.$sizeClass.' '.$classes]) }}>
        {{ $slot }}
    </button>
@endif
