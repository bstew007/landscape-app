@php
    $client = $siteVisit->client;
    $data = $calculation->data;
@endphp

<p>Hi {{ $client->first_name ?? $client->name ?? 'there' }},</p>

@if (!empty($messageBody))
    <p>{!! nl2br(e($messageBody)) !!}</p>
@else
    <p>Attached is the synthetic turf calculator summary from our recent site visit. Please review the details and let us know if anything needs clarification.</p>
@endif

<p>
    <strong>Project:</strong> {{ $client->address ?? 'N/A' }}<br>
    <strong>Prepared for:</strong> {{ $client->first_name }} {{ $client->last_name }}<br>
    <strong>Prepared by:</strong> {{ config('app.name') }}
</p>

<p>
    <strong>Calculator Total:</strong> ${{ number_format($data['final_price'] ?? 0, 2) }}<br>
    <strong>Labor:</strong> ${{ number_format($data['labor_cost'] ?? 0, 2) }}<br>
    <strong>Materials:</strong> ${{ number_format($data['material_total'] ?? 0, 2) }}
</p>

<p>The full site-visit calculator breakdown is included in the attached PDF.</p>

<p>Thank you,<br>{{ config('app.name') }}</p>
