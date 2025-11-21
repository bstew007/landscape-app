@props(['size' => 'md', 'href' => null, 'as' => null, 'type' => 'button'])
@php
  $sizeClass = [
    'sm' => 'h-9 px-3 text-sm',
    'md' => 'h-10 px-4 text-sm',
  ][$size] ?? 'h-10 px-4 text-sm';
  $baseClass = 'inline-flex items-center '.$sizeClass.' bg-brand-50 border border-brand-200 rounded font-medium text-brand-800 shadow-sm hover:bg-brand-100 focus:outline-none focus:ring-2 focus:ring-brand-300 focus:ring-offset-1 disabled:opacity-25 transition-colors';
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
