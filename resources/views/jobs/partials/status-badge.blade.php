@php
    $statusClasses = [
        'scheduled' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
        'in_progress' => 'bg-green-50 text-green-700 border border-green-200',
        'on_hold' => 'bg-orange-50 text-orange-700 border border-orange-200',
        'completed' => 'bg-brand-100 text-brand-800 border border-brand-200',
        'cancelled' => 'bg-red-50 text-red-700 border border-red-200',
    ];
    
    $statusLabels = [
        'scheduled' => 'Scheduled',
        'in_progress' => 'In Progress',
        'on_hold' => 'On Hold',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];
    
    $class = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
    $label = $statusLabels[$status] ?? ucfirst($status);
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $class }}">
    {{ $label }}
</span>
