@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">🧮 CFL Calculators</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <a href="{{ route('calculators.wall.form') }}"
           class="bg-white p-6 rounded-lg shadow hover:bg-blue-50 transition">
            <h2 class="text-xl font-semibold text-blue-700">Retaining Wall</h2>
            <p class="text-gray-600 mt-2">Estimate materials, labor, and pricing for retaining walls.</p>
        </a>

        {{-- Add more calculators here in the future --}}
    </div>
    {{-- 🔙 Back to Client if returning from site visit --}}
    @if (session('last_client_id'))
        <div class="mt-6">
            <a href="{{ route('clients.show', session('last_client_id')) }}"
               class="inline-block bg-gray-600 text-white px-5 py-3 rounded hover:bg-gray-700">
                🔙 Back to Client Hub
            </a>
        </div>
    @endif
</div>
@endsection
