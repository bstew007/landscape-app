@extends('layouts.sidebar')

@section('content')
    <div class="max-w-5xl mx-auto py-6 space-y-6">
        <x-page-header title="Edit Property" eyebrow="Properties" subtitle="{{ $property->name }}" variant="compact">
            <x-slot:leading>
                @php
                    $initials = collect(explode(' ', trim($property->name ?? 'Property')))
                        ->map(fn($p)=>strtoupper(mb_substr($p,0,1)))
                        ->take(2)
                        ->implode('');
                @endphp
                <div class="h-12 w-12 rounded-full bg-brand-600 text-white flex items-center justify-center text-lg font-semibold shadow-sm">
                    {{ $initials ?: 'P' }}
                </div>
            </x-slot:leading>
        </x-page-header>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-100 text-red-800 border border-red-300 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('properties._form', [
            'route' => route('contacts.properties.update', [$client, $property]),
            'method' => 'PUT',
            'property' => $property,
        ])
    </div>
@endsection
