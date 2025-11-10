@php($data = $calculation->data)
<div class="section">
    <h2>Mulching Estimate</h2>
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

    <h3>Coverage</h3>
    <p>Area: {{ number_format($data['area_sqft'] ?? 0, 2) }} sq ft — Depth: {{ number_format($data['depth_inches'] ?? 0, 2) }} in</p>
    <p>Labor Hours: {{ number_format($data['labor_hours'], 2) }}</p>
</div>
