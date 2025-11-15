@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto py-10 space-y-6">
    <x-page-header title="CFL Calculators" eyebrow="Tools" subtitle="Choose a calculator to start or attach to a site visit.">
        <x-slot:actions>
            <x-brand-button href="{{ route('calculator.templates.gallery') }}" variant="outline">Open Template Gallery</x-brand-button>
        </x-slot:actions>
    </x-page-header>

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

        {{-- Planting --}}
        <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.planting.form')]) }}"
           class="bg-white p-6 rounded-lg shadow hover:bg-green-50 transition">
            <h2 class="text-xl font-semibold text-green-700">Planting</h2>
            <p class="text-gray-600 mt-2">Plan annuals, shrubs, palms, and tree installs.</p>
        </a>

        {{-- Turf Mowing --}}
        <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.turf_mowing.form')]) }}"
           class="bg-white p-6 rounded-lg shadow hover:bg-green-50 transition">
            <h2 class="text-xl font-semibold text-green-700">Turf Mowing</h2>
            <p class="text-gray-600 mt-2">Plan mowing, trimming, edging, and blowing labor.</p>
        </a>

         {{-- Fence Construction --}}
        <a href="{{ route('calculators.selectSiteVisit', ['redirect_to' => route('calculators.fence.form')]) }}"
           class="bg-white p-6 rounded-lg shadow hover:bg-green-50 transition">
            <h2 class="text-xl font-semibold text-green-700">Fence Construction</h2>
            <p class="text-gray-600 mt-2">Estimate installing wood or vinyl fencing with labor and materials.</p>
        </a>
    </div>

    {{-- 🔙 Back to Client if returning from site visit --}}
    @if (session('last_client_id'))
        <div class="mt-6">
            <x-brand-button href="{{ route('clients.show', session('last_client_id')) }}" variant="outline">🔙 Back to Client Hub</x-brand-button>
        </div>
    @endif
</div>
@endsection
