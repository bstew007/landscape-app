@extends('layouts.sidebar')

@section('content')
@php
    $active = $tab ?? request('tab','info');
    function tabUrl($key){ return request()->fullUrlWithQuery(['tab'=>$key]); }
@endphp
<div class="space-y-6">
    <!-- Header -->
    <x-page-header title="{{ $contact->name }}" eyebrow="Contact" subtitle="{{ trim(($contact->email ?? '—') . ($contact->phone ? ' · ' . $contact->phone : '')) }}" variant="compact">
        <x-slot:leading>
            @php
                $initials = collect(explode(' ', trim($contact->name)))->map(fn($p)=>strtoupper(mb_substr($p,0,1)))->take(2)->implode('');
            @endphp
            <div class="h-12 w-12 rounded-full bg-brand-600 text-white flex items-center justify-center text-lg font-semibold shadow-sm">
                {{ $initials ?: 'C' }}
            </div>
        </x-slot:leading>
        <x-slot:actions>
            <div class="flex items-center gap-2 flex-wrap" id="contactHeaderActions">
                <span data-role="hdr-edit">
                    <x-brand-button href="{{ route('contacts.edit', $contact) }}" variant="outline">Edit Contact</x-brand-button>
                </span>
                <span data-role="hdr-new-estimate">
                    <x-brand-button href="{{ route('estimates.create', ['client_id' => $contact->id, 'property_id' => optional($contact->primaryProperty)->id]) }}">+ New Estimate</x-brand-button>
                </span>
                <span data-role="hdr-new-todo">
                    <x-brand-button href="{{ route('todos.create', ['client_id' => $contact->id]) }}" variant="outline">+ New To‑Do</x-brand-button>
                </span>
                <span data-role="hdr-site-visits">
                    <x-brand-button href="{{ route('contacts.site-visits.index', $contact) }}" variant="outline">Site Visits</x-brand-button>
                </span>
                <span data-role="hdr-new-site-visit">
                    <x-brand-button href="{{ route('contacts.site-visits.create', ['client' => $contact->id, 'property_id' => optional($contact->primaryProperty)->id]) }}">+ New Site Visit</x-brand-button>
                </span>
                <details class="relative" data-role="hdr-calculators">
                    <summary class="list-none inline-flex items-center h-10 px-4 rounded font-medium text-sm whitespace-nowrap border border-brand-600 text-brand-700 cursor-pointer select-none hover:bg-brand-50">
                        + Add via Calculator
                    </summary>
                    <div class="absolute right-0 mt-2 w-64 bg-white border rounded shadow z-10 p-1">
                        <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.mulching.form')]) }}">Mulching</a>
                        <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.syn_turf.form')]) }}">Synthetic Turf</a>
                        <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.patio.form')]) }}">Paver Patio</a>
                        <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.wall.form')]) }}">Retaining Wall</a>
                        <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.weeding.form')]) }}">Weeding</a>
                        <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.turf_mowing.form')]) }}">Turf Mowing</a>
                        <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.planting.form')]) }}">Planting</a>
                        <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.fence.form')]) }}">Fence</a>
                        <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.pruning.form')]) }}">Pruning</a>
                        <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.pine_needles.form')]) }}">Pine Needles</a>
                        <div class="border-t my-1"></div>
                        <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.index') }}">All Calculators…</a>
                    </div>
                </details>
                <details class="relative" data-role="hdr-manage">
                    <summary class="list-none inline-flex items-center h-10 px-3 rounded font-medium text-sm whitespace-nowrap border border-gray-300 text-gray-700 cursor-pointer select-none hover:bg-gray-50">Customize</summary>
                    <div class="absolute right-0 mt-2 w-64 bg-white border rounded shadow z-10 p-2">
                        <p class="text-xs text-gray-500 px-2 pb-1">Show buttons</p>
                        <label class="flex items-center gap-2 text-sm py-1 px-2"><input type="checkbox" data-hdr-pref="hdr-edit" class="rounded"> <span>Edit Contact</span></label>
                        <label class="flex items-center gap-2 text-sm py-1 px-2"><input type="checkbox" data-hdr-pref="hdr-new-estimate" class="rounded"> <span>+ New Estimate</span></label>
                        <label class="flex items-center gap-2 text-sm py-1 px-2"><input type="checkbox" data-hdr-pref="hdr-new-todo" class="rounded"> <span>+ New To‑Do</span></label>
                        <label class="flex items-center gap-2 text-sm py-1 px-2"><input type="checkbox" data-hdr-pref="hdr-site-visits" class="rounded"> <span>Site Visits</span></label>
                        <label class="flex items-center gap-2 text-sm py-1 px-2"><input type="checkbox" data-hdr-pref="hdr-new-site-visit" class="rounded"> <span>+ New Site Visit</span></label>
                    </div>
                </details>
            </div>
        </x-slot:actions>
    </x-page-header>

    <!-- Tabs -->
    <div class="bg-white rounded shadow p-2 flex flex-wrap gap-2">
        <a href="{{ tabUrl('info') }}" class="px-3 py-1 text-sm rounded border {{ $active==='info' ? 'bg-blue-600 text-white' : '' }}">Info</a>
        <a href="{{ tabUrl('properties') }}" class="px-3 py-1 text-sm rounded border {{ $active==='properties' ? 'bg-blue-600 text-white' : '' }}">Properties</a>
        <a href="{{ tabUrl('estimates') }}" class="px-3 py-1 text-sm rounded border {{ $active==='estimates' ? 'bg-blue-600 text-white' : '' }}">Estimates</a>
        <a href="{{ tabUrl('invoices') }}" class="px-3 py-1 text-sm rounded border {{ $active==='invoices' ? 'bg-blue-600 text-white' : '' }}">Invoices</a>
        <a href="{{ tabUrl('comms') }}" class="px-3 py-1 text-sm rounded border {{ $active==='comms' ? 'bg-blue-600 text-white' : '' }}">Communications</a>
    </div>

    <!-- Panels -->
    @if($active==='info')
        @include('clients.tabs.info')
    @elseif($active==='properties')
        @include('clients.tabs.properties')
    @elseif($active==='estimates')
        @include('clients.tabs.estimates')
    @elseif($active==='invoices')
        @include('clients.tabs.invoices')
    @elseif($active==='comms')
        @include('clients.tabs.comms')
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Slide-over wiring for Quick Add Property
        const open = document.getElementById('openPropSlideOver');
        const panel = document.getElementById('propSlideOver');
        const overlay = document.getElementById('propSlideOverOverlay');
        const close = document.getElementById('closePropSlideOver');
        const cancel = document.getElementById('cancelPropSlideOver');
        function show(){ if(panel) panel.classList.remove('hidden'); }
        function hide(){ if(panel) panel.classList.add('hidden'); }
        if (open) open.addEventListener('click', show);
        if (overlay) overlay.addEventListener('click', hide);
        if (close) close.addEventListener('click', hide);
        if (cancel) cancel.addEventListener('click', hide);

        // Quick add To‑Do panel
        const quickBtn = document.getElementById('toggleQuickTodo');
        const quickWrap = document.getElementById('quickTodoWrap');
        const quickCancel = document.getElementById('quickTodoCancel');
        if (quickBtn && quickWrap) quickBtn.addEventListener('click', ()=> quickWrap.classList.toggle('hidden'));
        if (quickCancel && quickWrap) quickCancel.addEventListener('click', ()=> quickWrap.classList.add('hidden'));

        // Header button management (persist which header buttons are visible)
        const hdr = document.getElementById('contactHeaderActions');
        if (hdr) {
            const prefs = {};
            ['hdr-edit','hdr-new-estimate','hdr-new-todo','hdr-site-visits','hdr-new-site-visit'].forEach(k => {
                prefs[k] = localStorage.getItem('contact_hdr_'+k) !== '0';
                const span = hdr.querySelector(`[data-role="${k}"]`);
                if (span) span.classList.toggle('hidden', !prefs[k]);
            });
            document.querySelectorAll('[data-hdr-pref]').forEach(cb => {
                const key = cb.getAttribute('data-hdr-pref');
                cb.checked = localStorage.getItem('contact_hdr_'+key) !== '0';
                cb.addEventListener('change', () => {
                    const on = cb.checked;
                    const span = hdr.querySelector(`[data-role="${key}"]`);
                    if (span) span.classList.toggle('hidden', !on);
                    localStorage.setItem('contact_hdr_'+key, on ? '1' : '0');
                });
            });
        }
    });
</script>
@endpush




