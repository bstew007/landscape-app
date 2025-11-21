@props(['title' => null, 'titleClass' => 'text-xs uppercase tracking-wide text-brand-500 mb-2'])
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-brand-100/70 bg-white shadow-sm p-4 relative']) }}>
    @if ($title)
        <div class="{{ $titleClass }}">{{ $title }}</div>
    @endif
    @isset($icon)
        <div class="absolute top-3 right-3 text-brand-400">{{ $icon }}</div>
    @endisset
    {{ $slot }}
</div>
