@php($data = $calculation->data)
<section class="bg-white p-6 rounded shadow space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-semibold">🍂 Pine Needles</h2>
        <span class="text-lg font-bold text-green-700">${{ number_format($data['final_price'], 2) }}</span>
    </div>

    @if (!empty($data['materials']))
        @include('calculators.partials.materials_table', [
            'materials' => $data['materials'],
            'material_total' => $data['material_total'] ?? 0,
        ])
    @endif

    <div>
        <h3 class="text-xl font-semibold mb-2">Labor Breakdown</h3>
        <ul class="space-y-1">
            @foreach ($data['labor_by_task'] as $task => $hours)
                <li class="flex justify-between capitalize">
                    <span>{{ str_replace('_', ' ', $task) }}</span>
                    <span>{{ number_format($hours, 2) }} hrs</span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-gray-50 p-4 rounded">
            <h4 class="font-semibold mb-2">Totals</h4>
            <p>Labor Hours: {{ number_format($data['labor_hours'], 2) }}</p>
            <p>Total Hours: {{ number_format($data['total_hours'], 2) }}</p>
            <p>Labor Cost: ${{ number_format($data['labor_cost'], 2) }}</p>
        </div>
        <div class="bg-gray-50 p-4 rounded">
            <h4 class="font-semibold mb-2">Pricing</h4>
            <p>Markup: {{ $data['markup'] ?? 0 }}% ( ${{ number_format($data['markup_amount'] ?? 0, 2) }} )</p>
            <p class="font-bold">Final Price: ${{ number_format($data['final_price'], 2) }}</p>
        </div>
    </div>

    @if (!empty($data['job_notes']))
        <div class="bg-yellow-50 p-4 rounded border border-yellow-200">
            <h4 class="font-semibold mb-1">Notes</h4>
            <p class="whitespace-pre-line">{{ $data['job_notes'] }}</p>
        </div>
    @endif
</section>
