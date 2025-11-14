@extends('layouts.sidebar')

@section('content')
@php
    $active = $tab ?? request('tab','info');
    function tabUrl($key){ return request()->fullUrlWithQuery(['tab'=>$key]); }
@endphp
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-500 uppercase tracking-wide">Contact</p>
            <h1 class="text-3xl font-bold">{{ $contact->name }}</h1>
            <p class="text-gray-600">{{ $contact->email ?? '—' }} @if($contact->phone) · {{ $contact->phone }} @endif</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('contacts.edit', $contact) }}" class="rounded border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Edit Contact</a>
            <a href="{{ route('estimates.create', ['client_id' => $contact->id, 'property_id' => optional($contact->primaryProperty)->id]) }}" class="rounded bg-brand-700 text-white px-4 py-2 text-sm hover:bg-brand-800">+ New Estimate</a>
            <a href="{{ route('todos.create', ['client_id' => $contact->id]) }}" class="rounded border border-emerald-300 px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50">+ New To‑Do</a>
        </div>
    </div>

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




