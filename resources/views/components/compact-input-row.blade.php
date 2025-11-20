@props(['label' => '', 'for' => null])
<div {{ $attributes->merge(['class' => 'flex items-center justify-between py-1.5']) }}>
    <label @if($for) for="{{ $for }}" @endif class="text-sm font-medium text-gray-800 pr-3">{{ $label }}</label>
    <div class="shrink-0">
        {{ $slot }}
    </div>
</div>
