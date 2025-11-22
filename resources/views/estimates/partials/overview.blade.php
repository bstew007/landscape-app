@php
    $displayTotal = $estimate->grand_total ?? $estimate->total ?? 0;
    $siteVisitDate = optional(optional($estimate->siteVisit)->visit_date)->format('M j, Y');
@endphp
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-gray-100 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Estimate Total</p>
        <p class="text-2xl font-semibold text-gray-900">${{ number_format($displayTotal, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Includes taxes/fees if applicable</p>
    </div>
    <div class="rounded-lg border border-gray-100 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Status</p>
        <div class="mt-1">
            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                  @class([
                      'bg-gray-100 text-gray-700' => $estimate->status === 'draft',
                      'bg-amber-100 text-amber-700' => $estimate->status === 'pending',
                      'bg-brand-100 text-brand-700' => $estimate->status === 'sent',
                      'bg-green-100 text-green-700' => $estimate->status === 'approved',
                      'bg-red-100 text-red-700' => $estimate->status === 'rejected',
                  ])>
                {{ ucfirst($estimate->status) }}
            </span>
        </div>
        @if ($estimate->email_last_sent_at)
            <p class="text-[11px] text-gray-500 mt-2">Last emailed {{ $estimate->email_last_sent_at->format('M j, Y') }} ({{ $estimate->email_send_count }} {{ \Illuminate\Support\Str::plural('time', $estimate->email_send_count) }})</p>
        @endif
    </div>
    <div class="rounded-lg border border-gray-100 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Expires</p>
        <p class="text-lg font-semibold text-gray-900">{{ optional($estimate->expires_at)->format('M j, Y') ?? 'Not set' }}</p>
        <p class="text-[11px] text-gray-500 mt-1">Created {{ optional($estimate->created_at)->format('M j, Y') }}</p>
    </div>
    <div class="rounded-lg border border-gray-100 p-4">
        <p class="text-xs uppercase tracking-wide text-gray-500">Linked Site Visit</p>
        <p class="text-lg font-semibold text-gray-900">{{ $siteVisitDate ?? 'None' }}</p>
        @if (!empty($siteVisitDate) && $estimate->siteVisit)
            <a href="{{ route('clients.site-visits.show', [$estimate->client, $estimate->siteVisit]) }}" class="inline-block mt-2 text-xs text-brand-700 hover:text-brand-900">Open Visit</a>
        @endif
    </div>
</div>
