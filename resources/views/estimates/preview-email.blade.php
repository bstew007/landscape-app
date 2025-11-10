@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <p class="text-sm uppercase tracking-wide text-gray-500">Email Preview</p>
        <h1 class="text-3xl font-bold text-gray-900">Estimate Email</h1>
        <p class="text-gray-600">Preview the email before sending it to the client.</p>
    </div>

    @if (session('success'))
        <div class="p-4 rounded bg-green-100 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <section class="bg-white rounded shadow p-4">
        <div class="prose max-w-none">
            {!! $html !!}
        </div>
    </section>

    <div class="flex flex-wrap gap-2">
        <form action="{{ route('estimates.email', $estimate) }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Send to {{ $estimate->client->email ?? 'client' }}
            </button>
        </form>
        <a href="{{ route('estimates.index') }}" class="inline-flex items-center px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Done</a>
    </div>
</div>
@endsection
