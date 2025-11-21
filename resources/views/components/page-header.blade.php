@props([
    'title' => '',
    'subtitle' => null,
    'eyebrow' => null,
    'variant' => 'primary', // primary | compact
])
@php
    $isCompact = $variant === 'compact' || $variant === 'secondary';
    $wrapPad = $isCompact ? 'p-3' : 'p-4';
    $titleCls = $isCompact ? 'text-2xl font-semibold' : 'text-3xl font-bold';
    $subtitleCls = $isCompact ? 'text-sm' : '';
@endphp
<div {{ $attributes->merge(['class' => "rounded-2xl border border-brand-100 bg-white shadow-sm $wrapPad"]) }}>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-start gap-3">
            @isset($leading)
                <div class="shrink-0">
                    {{ $leading }}
                </div>
            @endisset
            <div>
                @if($eyebrow)
                    <p class="text-sm uppercase tracking-wide text-brand-700">{{ $eyebrow }}</p>
                @endif
                <h1 class="{{ $titleCls }} text-gray-900 tracking-wide uppercase">{{ $title }}</h1>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            {{ $actions ?? '' }}
        </div>
    </div>
</div>
