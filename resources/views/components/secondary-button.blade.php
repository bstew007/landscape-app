@props(['size' => 'md', 'href' => null, 'as' => null, 'type' => 'button'])
@php
  $sizeClass = [
    'sm' => 'h-9 px-3 text-sm',
    'md' => 'h-10 px-4 text-sm',
  ][$size] ?? 'h-10 px-4 text-sm';
  $baseClass = 'inline-flex items-center '.$sizeClass.' bg-white border border-brand-300 rounded font-medium text-brand-700 shadow-sm hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500 disabled:opacity-25 transition-colors';
@endphp
@if ($href || $as === 'a')
  <a href="{{ $href ?? '#' }}" {{ $attributes->merge(['class' => $baseClass]) }}>
      {{ $slot }}
  </a>
@else
  <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClass]) }}>
      {{ $slot }}
  </button>
@endif
