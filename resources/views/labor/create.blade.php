@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto py-10 space-y-6">
    <div>
        <h1 class="text-3xl font-bold">Add Labor Entry</h1>
        <p class="text-sm text-gray-600">Define reusable labor/equipment rates for estimates.</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-100 text-red-800 px-4 py-3 rounded">
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white p-6 rounded shadow space-y-6">
        <form method="POST" action="{{ route('labor.store') }}" class="space-y-6">
            @include('labor._form')
        </form>
    </div>
</div>
@endsection
