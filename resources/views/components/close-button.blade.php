@props(['size' => 'md'])
@php
    $sizes = [
        'sm' => 'h-5 w-5',
        'md' => 'h-6 w-6',
        'lg' => 'h-8 w-8',
    ];
    $dim = $sizes[$size] ?? $sizes['md'];
@endphp
<button type="button" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-md bg-rose-700 hover:bg-rose-800 text-white focus:outline-none focus:ring-2 focus:ring-rose-500 '.$dim]) }} aria-label="Close">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $dim }}">
        <line x1="18" y1="6" x2="6" y2="18" />
        <line x1="6" y1="6" x2="18" y2="18" />
    </svg>
</button>
