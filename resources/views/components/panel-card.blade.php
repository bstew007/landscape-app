@props(['title' => null])
<div {{ $attributes->merge(['class' => 'rounded border p-3 relative']) }}>
    @if ($title)
        <div class="text-xs uppercase tracking-wide text-gray-500 mb-2">{{ $title }}</div>
    @endif
    @isset($icon)
        <div class="absolute top-2 right-2 text-gray-600">{{ $icon }}</div>
    @endisset
    {{ $slot }}
</div>
