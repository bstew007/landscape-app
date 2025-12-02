<div class="bg-white p-6 rounded-lg shadow mb-8">
    <h2 class="text-2xl font-semibold mb-4">🧱 Materials Summary</h2>
    <table class="w-full border-collapse text-sm mb-6">
        <thead>
            <tr class="bg-gray-100 text-left border-b">
                <th class="p-2">Material</th>
                <th class="p-2 text-right">Qty</th>
                <th class="p-2 text-right">Unit Cost</th>
                <th class="p-2 text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($materials as $label => $item)
                @if(is_array($item) && isset($item['qty'], $item['unit_cost'], $item['total']))
                    <tr>
                        <td class="p-2">{{ $label }}</td>
                        <td class="p-2 text-right">{{ is_numeric($item['qty']) ? number_format($item['qty'], 2) : $item['qty'] }}</td>
                        <td class="p-2 text-right">${{ number_format($item['unit_cost'], 2) }}</td>
                        <td class="p-2 text-right">${{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="font-bold bg-gray-100">
                <td colspan="3" class="text-right px-4 py-2">Total Material Cost:</td>
                <td class="text-right px-4 py-2">${{ number_format($material_total ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>
</div>
