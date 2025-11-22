<div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-gray-100 px-4 py-3">
        <h2 class="text-base font-semibold text-gray-900">Client Information</h2>
    </div>
    <div class="px-4 py-4 text-sm text-gray-700 space-y-2">
        <div>
            <span class="font-medium">Client:</span> {{ $estimate->client->name ?? '—' }}
        </div>
        <div>
            <span class="font-medium">Billing Address:</span>
            @php
                $billing = trim(implode(' ', array_filter([
                    $estimate->client->address ?? null,
                    $estimate->client->city ?? null,
                    $estimate->client->state ?? null,
                    $estimate->client->postal_code ?? null,
                ])));
            @endphp
            {{ $billing !== '' ? $billing : '—' }}
        </div>
        <div>
            <span class="font-medium">Contact:</span>
            {{ trim(($estimate->client->first_name ?? '') . ' ' . ($estimate->client->last_name ?? '')) ?: ($estimate->client->company_name ?? '—') }}
        </div>
        <div>
            <span class="font-medium">Phone:</span> {{ $estimate->client->phone ?? '—' }}
        </div>
        <div>
            <span class="font-medium">Email:</span> {{ $estimate->client->email ?? '—' }}
        </div>
        <div>
            <span class="font-medium">Property:</span> {{ $estimate->property->name ?? '—' }}
        </div>
        <div>
            <span class="font-medium">Property Address:</span>
            @php
                $paddr = trim(implode(' ', array_filter([
                    optional($estimate->property)->address_line1,
                    optional($estimate->property)->city,
                    optional($estimate->property)->state,
                    optional($estimate->property)->postal_code,
                ])));
            @endphp
            {{ $paddr !== '' ? $paddr : '—' }}
        </div>
    </div>
</div>
