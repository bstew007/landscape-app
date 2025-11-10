@extends('layouts.sidebar')

@section('content')
    <div class="max-w-4xl mx-auto py-10">
        <h1 class="text-3xl font-semibold mb-6">Add Property for {{ $client->name }}</h1>

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
            'route' => route('clients.properties.store', $client),
            'method' => 'POST',
            'property' => $property,
        ])
    </div>
@endsection
