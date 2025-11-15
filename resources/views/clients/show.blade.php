@extends('layouts.sidebar')

@section('content')
@php
    $active = $tab ?? request('tab','info');
    function tabUrl($key){ return request()->fullUrlWithQuery(['tab'=>$key]); }
@endphp
<div class="space-y-6">
    <!-- Header -->
    <x-page-header title="{{ $contact->name }}" eyebrow="Contact" subtitle="{{ $contact->email ?? '—' }} @if($contact->phone) · {{ $contact->phone }} @endif" variant="compact">
        <x-slot:leading>
            @php
                $initials = collect(explode(' ', trim($contact->name)))->map(fn($p)=>strtoupper(mb_substr($p,0,1)))->take(2)->implode('');
            @endphp
            <div class="h-12 w-12 rounded-full bg-brand-600 text-white flex items-center justify-center text-lg font-semibold shadow-sm">
                {{ $initials ?: 'C' }}
            </div>
        </x-slot:leading>
        <x-slot:actions>
            <x-brand-button href="{{ route('contacts.edit', $contact) }}" variant="outline">Edit Contact</x-brand-button>
            <x-brand-button href="{{ route('estimates.create', ['client_id' => $contact->id, 'property_id' => optional($contact->primaryProperty)->id]) }}">+ New Estimate</x-brand-button>
            <x-brand-button href="{{ route('todos.create', ['client_id' => $contact->id]) }}" variant="outline">+ New To‑Do</x-brand-button>
            <x-brand-button href="{{ route('contacts.site-visits.index', $contact) }}" variant="outline">Site Visits</x-brand-button>
            <x-brand-button href="{{ route('contacts.site-visits.create', ['client' => $contact->id, 'property_id' => optional($contact->primaryProperty)->id]) }}">+ New Site Visit</x-brand-button>
            <details class="relative">
                <summary class="list-none inline-flex items-center h-10 px-4 rounded font-medium text-sm whitespace-nowrap border border-brand-600 text-brand-700 cursor-pointer select-none hover:bg-brand-50">
                    + Add via Calculator
                </summary>
                <div class="absolute right-0 mt-2 w-64 bg-white border rounded shadow z-10 p-1">
                    <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.mulching.form')]) }}">Mulching</a>
                    <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.syn_turf.form')]) }}">Synthetic Turf</a>
                    <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.patio.form')]) }}">Paver Patio</a>
                    <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.wall.form')]) }}">Retaining Wall</a>
                    <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.weeding.form')]) }}">Weeding</a>
                    <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.mulching.form')]) }}">Mulching</a>
                    <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.turf_mowing.form')]) }}">Turf Mowing</a>
                    <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.planting.form')]) }}">Planting</a>
                    <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.fence.form')]) }}">Fence</a>
                    <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.pruning.form')]) }}">Pruning</a>
                    <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.pine_needles.form')]) }}">Pine Needles</a>
                    <div class="border-t my-1"></div>
                    <a class="block px-3 py-2 hover:bg-gray-50" href="{{ route('calculators.index') }}">All Calculators…</a>
                </div>
            </details>
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
    });
</script>
@endpush




