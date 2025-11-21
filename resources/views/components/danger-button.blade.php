@props(['size' => 'md', 'type' => 'submit', 'href' => null])
@php
  $sizeClass = [
    'sm' => 'h-9 px-3 text-sm',
    'md' => 'h-10 px-4 text-sm',
  ][$size] ?? 'h-10 px-4 text-sm';
  $baseClass = 'inline-flex items-center '.$sizeClass.' rounded font-medium text-white transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1';
  $btnClass = $baseClass.' bg-red-600 hover:bg-red-500 focus:ring-red-400';
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
