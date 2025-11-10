@extends('layouts.sidebar')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <h1 class="text-3xl font-bold">Add New Asset</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('assets._form', [
            'asset' => $asset,
            'route' => route('assets.store'),
            'method' => 'POST',
        ])
    </div>
@endsection
