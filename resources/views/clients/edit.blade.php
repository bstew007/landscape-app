@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-semibold text-gray-800 mb-6">✏️ Edit Client</h1>

    @include('clients._form', [
        'route' => route('clients.update', $client),
        'method' => 'PUT',
        'client' => $client
    ])
</div>
@endsection
