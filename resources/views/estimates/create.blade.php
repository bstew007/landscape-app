@extends('layouts.sidebar')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-3 max-w-3xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Estimates</p>
                <h1 class="text-3xl sm:text-4xl font-semibold">Create a New Estimate</h1>
                <p class="text-sm text-brand-100/85">Link pricing to a client + property, pull in site visits, and kick off calculators from the same workspace.</p>
            </div>
            <x-secondary-button as="a" href="{{ route('estimates.index') }}" class="ml-auto bg-white/10 text-white border-white/40 hover:bg-white/20">
                Back to Estimates
            </x-secondary-button>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Steps</dt>
                <dd class="text-2xl font-semibold text-white mt-2">Client → Property → Totals</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Site Visits</dt>
                <dd class="text-2xl font-semibold text-white mt-2">Optional import</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Status</dt>
                <dd class="text-2xl font-semibold text-white mt-2">Draft by default</dd>
            </div>
        </dl>
    </section>

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 p-6 sm:p-8 space-y-6 max-w-6xl xl:max-w-7xl mx-auto">
        @if ($errors->any())
            <div class="p-4 bg-red-50 text-red-900 rounded-2xl border border-red-200 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('estimates._form_simple', [
            'estimate' => $estimate,
            'route' => route('estimates.store'),
            'method' => 'POST',
        ])
    </section>
</div>
@endsection
