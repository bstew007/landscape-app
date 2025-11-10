@php($data = $calculation->data)
<div class="section">
    <h2>Weeding</h2>
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
</div>
