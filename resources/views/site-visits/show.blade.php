@extends('layouts.sidebar')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
        <h1 class="text-3xl font-bold">🗓️ Site Visit Details</h1>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('site-visits.report', $siteVisit) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                📋 View Report
            </a>
            <a href="{{ route('site-visits.report.pdf', $siteVisit) }}"
               class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-900">
                📄 Download Report PDF
            </a>
        </div>
    </div>

    {{-- Site Visit Info --}}
    <div class="bg-white p-6 rounded-lg shadow text-gray-800 space-y-2 mb-6">
        <p><strong>Client:</strong> {{ $client->first_name }} {{ $client->last_name }}</p>
        <p><strong>Visit Date:</strong> {{ $siteVisit->visit_date->format('F j, Y') }}</p>
        <p><strong>Notes:</strong> {{ $siteVisit->notes ?? '—' }}</p>
    </div>

    {{-- ✏️ Edit and ❌ Delete Site Visit --}}
    <div class="flex gap-4 mb-8">
        <a href="{{ route('clients.site-visits.edit', [$client, $siteVisit]) }}"
           class="px-5 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-semibold">
            ✏️ Edit Site Visit
        </a>

        <form method="POST" action="{{ route('clients.site-visits.destroy', [$client, $siteVisit]) }}"
              onsubmit="return confirm('Are you sure you want to delete this site visit?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="px-5 py-3 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold">
                🗑️ Delete Site Visit
            </button>
        </form>
    </div>

    {{-- 🧮 Calculations Section --}}
    <h2 class="text-2xl font-semibold mb-4">🧮 Calculations</h2>

    @if ($calculations->count())
        <div class="space-y-4">
            @foreach ($calculations as $calc)
                <div class="bg-white p-4 rounded shadow flex justify-between items-center">
                    <div>
                        <p><strong>Type:</strong> {{ ucfirst(str_replace('_', ' ', $calc->calculation_type)) }}</p>
                        <p class="text-sm text-gray-500">Created: {{ $calc->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="flex gap-2">
                        {{-- 🔍 View/Edit --}}
                        @if ($calc->calculation_type === 'retaining_wall')
                            <a href="{{ route('calculators.wall.edit', $calc->id) }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                Edit
                            </a>
                            <a href="{{ route('calculations.wall.downloadPdf', $calc->id) }}"
                               class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">
                                PDF
                            </a>
                        @elseif ($calc->calculation_type === 'paver_patio')
                            <a href="{{ route('calculators.patio.edit', $calc->id) }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                Edit
                            </a>
                            <a href="{{ route('calculations.patio.downloadPdf', $calc->id) }}"
                               class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">
                                PDF
                            </a>
                        @elseif ($calc->calculation_type === 'fence')
                             <a href="{{ route('calculators.fence.edit', $calc->id) }}"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                Edit
                            </a>
                            <a href="{{ route('calculators.fence.downloadPdf', $calc->id) }}"
                               class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">
                                PDF
                            </a>

                            @elseif ($calc->calculation_type === 'pruning')
                            <a href="{{ route('calculators.pruning.edit', $calc->id) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                Edit
                            </a>
                            <a href="{{ route('calculators.pruning.downloadPdf', $calc->id) }}"
                            class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">
                                PDF
                            </a>

                             @elseif ($calc->calculation_type === 'weeding')
                            <a href="{{ route('calculators.weeding.edit', $calc->id) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                Edit
                            </a>
                            <a href="{{ route('calculators.weeding.downloadPdf', $calc->id) }}"
                            class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">
                                PDF
                            </a>

                            @elseif ($calc->calculation_type === 'mulching')
                            <a href="{{ route('calculators.mulching.edit', $calc->id) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                Edit
                            </a>
                            <a href="{{ route('calculators.mulching.downloadPdf', $calc->id) }}"
                            class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">
                                PDF
                            </a>

                            @elseif ($calc->calculation_type === 'pine_needles')
                            <a href="{{ route('calculators.pine_needles.edit', $calc->id) }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                Edit
                            </a>
                            <a href="{{ route('calculators.pine_needles.downloadPdf', $calc->id) }}"
                            class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">
                                PDF
                            </a>

                            @elseif ($calc->calculation_type === 'syn_turf')
                            <a href="{{ route('calculators.syn_turf.edit', $calc->id) }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                Edit
                            </a>
                            <a href="{{ route('calculators.syn_turf.downloadPdf', $calc->id) }}"
                               class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">
                                PDF
                            </a>

                            @elseif ($calc->calculation_type === 'turf_mowing')
                            <a href="{{ route('calculators.turf_mowing.edit', $calc->id) }}"
                               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                Edit
                            </a>
                            <a href="{{ route('calculators.turf_mowing.downloadPdf', $calc->id) }}"
                               class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded text-sm">
                                PDF
                            </a>

                        @endif  

                        {{-- ❌ Delete --}}
                        <form method="POST" action="{{ route('site-visits.deleteCalculation', $calc->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm"
                                    onclick="return confirm('Are you sure you want to delete this calculation?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
@else
        <p class="text-gray-600 mb-6">No calculations saved for this visit.</p>
@endif

    {{-- 📷 Photos --}}
    <div class="mt-10">
        <h2 class="text-2xl font-semibold mb-4">📷 Site Visit Photos</h2>

        <form method="POST"
              action="{{ route('clients.site-visits.photos.store', [$client, $siteVisit]) }}"
              enctype="multipart/form-data"
              class="mb-6 bg-white p-4 rounded shadow">
            @csrf
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold mb-1">Upload Photo</label>
                    <input type="file"
                           name="photo"
                           accept="image/*"
                           capture="environment"
                           class="form-input w-full"
                           required>
                    @error('photo')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block font-semibold mb-1">Caption (optional)</label>
                    <input type="text"
                           name="caption"
                           class="form-input w-full"
                           value="{{ old('caption') }}"
                           placeholder="e.g. Front lawn before service">
                </div>
            </div>
            <button type="submit"
                    class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                ⬆️ Upload Photo
            </button>
        </form>

        @if ($siteVisit->photos->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($siteVisit->photos as $photo)
                    <div class="bg-white rounded shadow overflow-hidden">
                        <img src="{{ Storage::disk('public')->url($photo->path) }}"
                             alt="{{ $photo->caption ?? 'Site photo' }}"
                             class="w-full h-48 object-cover">
                        <div class="p-3">
                            <p class="text-sm text-gray-700 mb-2">{{ $photo->caption ?? '—' }}</p>
                            <form method="POST"
                                  action="{{ route('clients.site-visits.photos.destroy', [$client, $siteVisit, $photo]) }}"
                                  onsubmit="return confirm('Delete this photo?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-red-600 text-sm hover:underline">
                                    🗑️ Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600">No photos uploaded yet.</p>
        @endif
    </div>

    {{-- 🔙 Back to Client --}}
    <div class="mt-8">
        <a href="{{ route('clients.show', $client) }}"
           class="inline-block px-5 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-md text-lg">
            🔙 Back to Client
        </a>
    </div>
</div>
@endsection
