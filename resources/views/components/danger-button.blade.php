@props(['size' => 'md', 'type' => 'submit'])
@php
  $sizeClass = [
    'sm' => 'h-9 px-3 text-sm',
    'md' => 'h-10 px-4 text-sm',
  ][$size] ?? 'h-10 px-4 text-sm';
@endphp
<button {{ $attributes->merge(['type' => $type, 'class' => 'inline-flex items-center '.$sizeClass.' bg-red-600 border border-transparent rounded font-medium text-white hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors']) }}>
    {{ $slot }}
</button>
