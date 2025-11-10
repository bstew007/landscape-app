@extends('layouts.sidebar')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <h1 class="text-3xl font-bold">New To-Do</h1>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('todos._form', [
            'route' => route('todos.store'),
            'method' => 'POST',
            'todo' => new \App\Models\Todo(),
        ])
    </div>
@endsection
