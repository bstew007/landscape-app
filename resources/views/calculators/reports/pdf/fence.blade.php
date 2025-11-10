@php($data = $calculation->data)
<div class="section">
    <h2>Fence Estimate</h2>
    <p><strong>Final Price:</strong> ${{ number_format($data['final_price'], 2) }}</p>

    <h3>Labor Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th>Task</th>
                <th style="text-align: right;">Hours</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['labor_by_task'] as $task => $hours)
                <tr>
                    <td>{{ ucwords(str_replace('_', ' ', $task)) }}</td>
                    <td style="text-align: right;">{{ number_format($hours, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Pricing</h3>
    <p>Labor Cost: ${{ number_format($data['labor_cost'], 2) }}</p>
    <p>Materials: ${{ number_format($data['material_total'], 2) }}</p>
    <p>Markup: {{ $data['markup'] }}% ( ${{ number_format($data['markup_amount'], 2) }} )</p>
    <p><strong>Final Price:</strong> ${{ number_format($data['final_price'], 2) }}</p>
</div>
