@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-semibold text-gray-800 mb-6">➕ Add New Contact</h1>

    @include('clients._form', [
        'route' => route('clients.store'),
        'method' => 'POST',
        'client' => new \App\Models\Client(),
        'types' => $types ?? ['lead','client','vendor','owner']
    ])
</div>
@endsection
