@extends('layouts.sidebar')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-sm text-gray-500 uppercase tracking-wide">Estimate</p>
            <h1 class="text-3xl font-bold">{{ $estimate->title }}</h1>
            <p class="text-gray-600">{{ $estimate->client->name }} · {{ $estimate->property->name ?? 'No property' }}</p>
        </div>
            <div class="flex flex-wrap gap-2">
            <a href="{{ route('estimates.edit', $estimate) }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Edit</a>
            <form action="{{ route('estimates.destroy', $estimate) }}" method="POST" onsubmit="return confirm('Delete this estimate?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded border border-red-300 px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
            </form>
            <a href="{{ route('estimates.preview-email', $estimate) }}" class="rounded border border-blue-300 px-4 py-2 text-sm text-blue-700 hover:bg-blue-50">Preview Email</a>
            <form action="{{ route('estimates.invoice', $estimate) }}" method="POST">
                @csrf
                <button type="submit" class="rounded border border-green-300 px-4 py-2 text-sm text-green-700 hover:bg-green-50">Create Invoice</button>
            </form>
            <a href="{{ route('estimates.print', $estimate) }}" target="_blank" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Print</a>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-xs font-semibold uppercase text-gray-500">Status</h2>
            <p class="text-xl font-bold text-gray-900">{{ ucfirst($estimate->status) }}</p>
            <p class="text-sm text-gray-600 mt-2">Created {{ $estimate->created_at->format('M j, Y') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-xs font-semibold uppercase text-gray-500">Total</h2>
            <p class="text-xl font-bold text-gray-900">{{ $estimate->total ? '$' . number_format($estimate->total, 2) : 'Pending' }}</p>
            <p class="text-sm text-gray-600 mt-2">Expires {{ optional($estimate->expires_at)->format('M j, Y') ?? 'N/A' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-xs font-semibold uppercase text-gray-500">Linked Site Visit</h2>
            @if ($estimate->siteVisit)
                <a
                    href="{{ route('clients.site-visits.show', [$estimate->client, $estimate->siteVisit]) }}"
                    class="text-sm text-blue-600 hover:text-blue-800"
                >
                    {{ $estimate->siteVisit->visit_date?->format('M j, Y') ?? 'View site visit' }}
                </a>
            @else
                <p class="text-sm text-gray-800">Not linked</p>
            @endif
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-xs font-semibold uppercase text-gray-500">Email Status</h2>
            @if ($estimate->email_send_count)
                <p class="text-sm font-semibold text-gray-900">
                    Last sent {{ $estimate->email_last_sent_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') }}
                </p>
                <p class="text-xs text-gray-600 mt-1">
                    Sent {{ $estimate->email_send_count }} {{ Str::plural('time', $estimate->email_send_count) }}
                    @if ($estimate->emailSender)
                        by {{ $estimate->emailSender->name ?? $estimate->emailSender->email }}
                    @endif
                </p>
            @else
                <p class="text-sm text-gray-700 mt-2">Not emailed yet.</p>
            @endif
        </div>
    </div>

    <section class="bg-white rounded-lg shadow p-6 space-y-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Scope & Notes</h2>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $estimate->notes ?: 'No additional notes yet.' }}</p>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Terms & Conditions</h2>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $estimate->terms ?: 'Add project terms to finalize this estimate.' }}</p>
        </div>
    </section>

    <section class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Line Items</h2>
        </div>
        @if (!empty($estimate->line_items))
            <table class="w-full text-sm">
                <thead>
                <tr class="text-xs uppercase text-gray-500 border-b">
                    <th class="text-left py-2">Description</th>
                    <th class="text-right py-2">Qty</th>
                    <th class="text-right py-2">Rate</th>
                    <th class="text-right py-2">Total</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($estimate->line_items as $item)
                    <tr class="border-b last:border-b-0">
                        <td class="py-2 text-gray-800">{{ $item['label'] ?? 'Line item' }}</td>
                        <td class="py-2 text-right text-gray-600">{{ $item['qty'] ?? '—' }}</td>
                        <td class="py-2 text-right text-gray-600">
                            {{ isset($item['rate']) ? '$' . number_format($item['rate'], 2) : '—' }}
                        </td>
                        <td class="py-2 text-right font-semibold">
                            {{ isset($item['total']) ? '$' . number_format($item['total'], 2) : '—' }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p class="text-sm text-gray-500">No line items captured yet. Pull data from calculators or add manually.</p>
        @endif
    </section>

    <section class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Invoice</h2>
            <p class="text-sm text-gray-500">Auto-generated from estimate</p>
        </div>
        @if ($estimate->invoice)
            <p class="text-sm text-gray-700"><strong>Status:</strong> {{ ucfirst($estimate->invoice->status) }}</p>
            <p class="text-sm text-gray-700"><strong>Amount:</strong> ${{ number_format($estimate->invoice->amount ?? 0, 2) }}</p>
            <p class="text-sm text-gray-700"><strong>Due:</strong> {{ optional($estimate->invoice->due_date)->format('M j, Y') ?? 'N/A' }}</p>
            @if ($estimate->invoice->pdf_path)
                <a href="{{ Storage::disk('public')->url($estimate->invoice->pdf_path) }}" class="text-blue-600 hover:text-blue-800 text-sm">Download Invoice</a>
            @endif
        @else
            <p class="text-sm text-gray-500">No invoice generated yet. Use the button above to create one.</p>
        @endif
    </section>
</div>
@endsection
