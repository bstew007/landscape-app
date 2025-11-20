@props(['value' => 0, 'class' => 'bg-gray-100 text-gray-800'])
<span {{ $attributes->merge(['class' => 'px-2 py-0.5 rounded-full ' . $class]) }}>{{ $value }}</span>
