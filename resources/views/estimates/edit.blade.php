@extends('layouts.sidebar')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">
        <div>
            <h1 class="text-3xl font-bold">Edit Estimate</h1>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('estimates._form', [
            'estimate' => $estimate,
            'route' => route('estimates.update', $estimate),
            'method' => 'PUT',
        ])
    </div>
@endsection
