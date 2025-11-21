@extends('layouts.sidebar')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-3 max-w-3xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Estimates</p>
                <h1 class="text-3xl sm:text-4xl font-semibold">Update Estimate</h1>
                <p class="text-sm text-brand-100/85">Adjust the metadata before returning to the builder to tweak work areas, labor, and templates.</p>
            </div>
            <div class="ml-auto flex flex-col gap-2 text-sm text-brand-100">
                <div class="rounded-2xl bg-white/10 border border-white/20 px-4 py-2">
                    <p class="text-xs uppercase tracking-wide text-brand-200/80">Status</p>
                    <p class="text-xl font-semibold text-white mt-1">{{ ucfirst($estimate->status ?? 'draft') }}</p>
                </div>
                <div class="rounded-2xl bg-white/10 border border-white/20 px-4 py-2">
                    <p class="text-xs uppercase tracking-wide text-brand-200/80">Client</p>
                    <p class="text-base font-semibold text-white mt-1">{{ optional($estimate->client)->name ?? 'Unassigned' }}</p>
                </div>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Work Areas</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ $estimate->areas->count() }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Total</dt>
                <dd class="text-2xl font-semibold text-white mt-2">${{ number_format($estimate->grand_total ?? $estimate->total ?? 0, 2) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Expires</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ optional($estimate->expires_at)->format('M j, Y') ?? '—' }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Last Updated</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ optional($estimate->updated_at)->diffForHumans() ?? 'Just now' }}</dd>
            </div>
        </dl>
    </section>

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 p-6 sm:p-8 space-y-6 max-w-6xl xl:max-w-7xl mx-auto">
        @php $errorBag = session('errors'); @endphp
        @if ($errorBag?->any())
            <div class="p-4 bg-red-50 text-red-900 rounded-2xl border border-red-200 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errorBag->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('estimates._form_simple', [
            'estimate' => $estimate,
            'route' => route('estimates.update', $estimate),
            'method' => 'PUT',
        ])
    </section>
</div>
@endsection
