@php($data = $calculation->data)
<div class="section">
    <h2>Planting</h2>
    <p><strong>Final Price:</strong> ${{ number_format($data['final_price'], 2) }}</p>

    @if (!empty($data['materials']))
        <h3>Materials</h3>
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th style="text-align:right;">Qty</th>
                    <th style="text-align:right;">Unit Cost</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['materials'] as $label => $item)
                    <tr>
                        <td>{{ $label }}</td>
                        <td style="text-align:right;">{{ $item['qty'] }}</td>
                        <td style="text-align:right;">${{ number_format($item['unit_cost'], 2) }}</td>
                        <td style="text-align:right;">${{ number_format($item['total'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h3>Labor Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th>Task</th>
                <th style="text-align:right;">Hours</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['labor_by_task'] as $task => $hours)
                <tr>
                    <td>{{ $task }}</td>
                    <td style="text-align:right;">{{ number_format($hours, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
