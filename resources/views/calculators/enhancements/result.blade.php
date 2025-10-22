@if(isset($pruning['tasks']) && count($pruning['tasks']))
    <h2 class="text-xl font-semibold mt-6 mb-2">Pruning Summary</h2>

    <table class="w-full table-auto border">
        <thead>
            <tr>
                <th class="border px-2 py-1 text-left">Task</th>
                <th class="border px-2 py-1">Units</th>
                <th class="border px-2 py-1">Rate</th>
                <th class="border px-2 py-1">Hours</th>
                <th class="border px-2 py-1">Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pruning['tasks'] as $task)
                <tr>
                    <td class="border px-2 py-1">{{ $task['task'] }}</td>
                    <td class="border px-2 py-1 text-center">{{ $task['units'] }}</td>
                    <td class="border px-2 py-1 text-center">{{ $task['prod_rate'] }}</td>
                    <td class="border px-2 py-1 text-center">{{ number_format($task['hours'], 2) }}</td>
                    <td class="border px-2 py-1 text-right">${{ number_format($task['cost'], 2) }}</td>
                </tr>
            @endforeach
            <tr class="font-bold bg-gray-100">
                <td colspan="3" class="border px-2 py-1 text-right">Total</td>
                <td class="border px-2 py-1 text-center">{{ number_format($pruning['total_hours'], 2) }}</td>
                <td class="border px-2 py-1 text-right">${{ number_format($pruning['total_cost'], 2) }}</td>
            </tr>
        </tbody>
    </table>
@endif

@if(isset($mulching))
    <h2 class="text-xl font-semibold mt-8 mb-2">Mulching Summary</h2>

    <table class="w-full table-auto border mb-4">
        <tr>
            <td class="border px-2 py-1">Square Footage</td>
            <td class="border px-2 py-1 text-right">{{ $mulching['sqft'] }}</td>
        </tr>
        <tr>
            <td class="border px-2 py-1">Depth (inches)</td>
            <td class="border px-2 py-1 text-right">{{ $mulching['depth'] }}"</td>
        </tr>
        <tr>
            <td class="border px-2 py-1">Cubic Yards Needed</td>
            <td class="border px-2 py-1 text-right">{{ number_format($mulching['cubic_yards'], 2) }}</td>
        </tr>
        <tr>
            <td class="border px-2 py-1">Mulch Type</td>
            <td class="border px-2 py-1 text-right">{{ $mulching['mulch_type'] }}</td>
        </tr>
        <tr>
            <td class="border px-2 py-1">Cost per CY</td>
            <td class="border px-2 py-1 text-right">${{ number_format($mulching['cost_per_cy'], 2) }}</td>
        </tr>
        <tr>
            <td class="border px-2 py-1">Material Cost</td>
            <td class="border px-2 py-1 text-right">${{ number_format($mulching['material_cost'], 2) }}</td>
        </tr>
        <tr>
            <td class="border px-2 py-1">Delivery Method</td>
            <td class="border px-2 py-1 text-right">{{ ucfirst(str_replace('_', ' ', $mulching['delivery_method'])) }}</td>
        </tr>
        <tr>
            <td class="border px-2 py-1">Production Rate (CY/hr/person)</td>
            <td class="border px-2 py-1 text-right">{{ $mulching['rate_per_hour_per_person'] }}</td>
        </tr>
        <tr>
            <td class="border px-2 py-1">Labor Hours</td>
            <td class="border px-2 py-1 text-right">{{ number_format($mulching['labor_hours'], 2) }}</td>
        </tr>
        <tr>
            <td class="border px-2 py-1">Labor Cost</td>
            <td class="border px-2 py-1 text-right">${{ number_format($mulching['labor_cost'], 2) }}</td>
        </tr>
        <tr class="font-bold bg-gray-100">
            <td class="border px-2 py-1">Total Cost</td>
            <td class="border px-2 py-1 text-right">${{ number_format($mulching['total_cost'], 2) }}</td>
        </tr>
    </table>
@endif
