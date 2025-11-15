@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <x-page-header
        title="Edit Contact"
        eyebrow="Contact"
        subtitle="{{ trim(($client->email ?? '—') . ($client->phone ? ' · ' . $client->phone : '')) }}"
        variant="compact">
        <x-slot:leading>
            @php
                $initials = collect(explode(' ', trim($client->name ?? ($client->first_name.' '.$client->last_name))))->map(fn($p)=>strtoupper(mb_substr($p,0,1)))->take(2)->implode('');
            @endphp
            <div class="h-12 w-12 rounded-full bg-brand-600 text-white flex items-center justify-center text-lg font-semibold shadow-sm">
                {{ $initials ?: 'C' }}
            </div>
        </x-slot:leading>
    </x-page-header>

    @include('clients._form', [
        'route' => route('contacts.update', $client),
        'method' => 'PUT',
        'client' => $client,
        'types' => $types ?? ['lead','client','vendor','owner']
    ])
</div>
@endsection
