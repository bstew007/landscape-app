@php
    $colors = [
        'draft' => 'bg-gray-100 text-gray-800',
        'submitted' => 'bg-yellow-100 text-yellow-800',
        'approved' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
    ];
    
    $color = $colors[$status] ?? 'bg-gray-100 text-gray-800';
@endphp

<span class="px-3 py-1 rounded-full text-xs font-semibold {{ $color }}">
    {{ ucfirst($status) }}
</span>
