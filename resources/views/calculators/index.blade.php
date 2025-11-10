@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">🧮 CFL Calculators</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        {{-- Retaining Wall --}}
        <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.wall.form')]) }}"
           class="bg-white p-6 rounded-lg shadow hover:bg-blue-50 transition">
            <h2 class="text-xl font-semibold text-blue-700">Retaining Wall</h2>
            <p class="text-gray-600 mt-2">Estimate materials, labor, and pricing for retaining walls.</p>
        </a>

        {{-- Paver Patio --}}
        <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.patio.form')]) }}"
           class="bg-white p-6 rounded-lg shadow hover:bg-blue-50 transition">
            <h2 class="text-xl font-semibold text-blue-700">Paver Patio</h2>
            <p class="text-gray-600 mt-2">Estimate materials, labor, and pricing for paver patios.</p>
        </a>

        {{-- Landscape Enhancements --}}
        <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.enhancements.form')]) }}"
           class="bg-white p-6 rounded-lg shadow hover:bg-green-50 transition">
            <h2 class="text-xl font-semibold text-green-700">Landscape Enhancements</h2>
            <p class="text-gray-600 mt-2">Estimate pruning, mulching, and more with labor and materials.</p>
        </a>

        {{-- Synthetic Turf --}}
        <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.syn_turf.form')]) }}"
           class="bg-white p-6 rounded-lg shadow hover:bg-green-50 transition">
            <h2 class="text-xl font-semibold text-green-700">Synthetic Turf</h2>
            <p class="text-gray-600 mt-2">Estimate base prep, turf install, and infill labor.</p>
        </a>

        {{-- Turf Mowing --}}
        <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.turf_mowing.form')]) }}"
           class="bg-white p-6 rounded-lg shadow hover:bg-green-50 transition">
            <h2 class="text-xl font-semibold text-green-700">Turf Mowing</h2>
            <p class="text-gray-600 mt-2">Plan mowing, trimming, edging, and blowing labor.</p>
        </a>

         {{-- Fence Construction --}}
        <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('fence.form')]) }}"
           class="bg-white p-6 rounded-lg shadow hover:bg-green-50 transition">
            <h2 class="text-xl font-semibold text-green-700">Fence Construction</h2>
            <p class="text-gray-600 mt-2">Estimate installing wood or vinyl fencing with labor and materials.</p>
        </a>
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
