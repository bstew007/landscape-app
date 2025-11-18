@props(['size' => 'md', 'type' => 'submit', 'href' => null])
@php
  $sizeClass = [
    'sm' => 'h-9 px-3 text-sm',
    'md' => 'h-10 px-4 text-sm',
  ][$size] ?? 'h-10 px-4 text-sm';
  $baseClass = 'inline-flex items-center '.$sizeClass.' rounded font-medium text-white transition-colors focus:outline-none focus:ring-2 focus:ring-amber-500';
  $btnClass = $baseClass.' bg-amber-800 hover:bg-amber-900';
@endphp
@if ($href)
  <a href="{{ $href }}" {{ $attributes->merge(['class' => $btnClass]) }}>
      {{ $slot }}
  </a>
@else
  <button {{ $attributes->merge(['type' => $type, 'class' => $btnClass]) }}>
      {{ $slot }}
  </button>
@endif
