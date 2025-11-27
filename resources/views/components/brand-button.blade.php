@props(['variant' => 'solid', 'href' => null, 'type' => 'button', 'size' => 'md'])
@php
    $base = 'inline-flex items-center rounded font-medium whitespace-nowrap transition-colors gap-2';
    $sizeClass = [
        'sm' => 'h-9 px-3 text-sm',
        'md' => 'h-10 px-4 text-sm',
    ][$size] ?? 'h-10 px-4 text-sm';
    $classes = [
        'solid' => 'bg-brand-600 text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-brand-500',
        'outline' => 'border border-brand-600 text-brand-700 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-brand-400',
        'ghost' => 'text-brand-700 hover:bg-brand-100 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-brand-300',
        'muted' => 'bg-brand-50 text-brand-900 border border-brand-200 hover:bg-brand-100 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-brand-200',
    ][$variant] ?? 'bg-brand-600 text-white hover:bg-brand-700';
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
