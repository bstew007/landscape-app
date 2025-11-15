@props(['variant' => 'solid', 'href' => null, 'type' => 'button'])
@php
    $base = 'inline-flex items-center h-10 px-4 rounded font-medium text-sm whitespace-nowrap transition-colors';
    $classes = [
        'solid' => 'bg-brand-700 text-white hover:bg-brand-800 focus:outline-none focus:ring-2 focus:ring-brand-500',
        'outline' => 'border border-brand-600 text-brand-700 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500',
        'ghost' => 'text-brand-700 hover:bg-brand-50',
    ][$variant] ?? 'bg-brand-700 text-white hover:bg-brand-800';
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $base.' '.$classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.$classes]) }}>
        {{ $slot }}
    </button>
@endif
