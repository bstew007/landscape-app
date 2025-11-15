@props(['variant' => 'solid', 'href' => null, 'type' => 'button', 'size' => 'md'])
@php
    $base = 'inline-flex items-center rounded font-medium whitespace-nowrap transition-colors gap-2';
    $sizeClass = [
        'sm' => 'h-9 px-3 text-sm',
        'md' => 'h-10 px-4 text-sm',
    ][$size] ?? 'h-10 px-4 text-sm';
    $classes = [
        'solid' => 'bg-brand-700 text-white hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500',
        'outline' => 'border border-brand-600 text-brand-700 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500',
        'ghost' => 'text-brand-700 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500',
    ][$variant] ?? 'bg-brand-700 text-white hover:bg-brand-800';
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
