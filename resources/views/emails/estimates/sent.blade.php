@component('mail::message')
# Estimate Summary

Hello {{ $estimate->client->name }},

Here is your estimate titled **{{ $estimate->title }}** with a total of **${{ number_format($estimate->total ?? 0, 2) }}**.

@if($estimate->line_items)
@component('mail::table')
| Item | Quantity | Rate | Total |
| --- | --- | --- | --- |
@foreach($estimate->line_items as $item)
| {{ $item['label'] ?? 'Item' }} | {{ $item['qty'] ?? '—' }} | {{ isset($item['price']) ? '$' . number_format($item['price'], 2) : '—' }} | {{ isset($item['total']) ? '$' . number_format($item['total'], 2) : '—' }} |
@endforeach
@endcomponent
@endif

{{ $estimate->notes }}

Thanks,<br>
CFL Landscape Team
@endcomponent
