@extends('layouts.sidebar')

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="space-y-6">
    <x-page-header title="Site Visit" eyebrow="Client">
        <x-slot:leading>
            <div class="h-12 w-12 rounded-full bg-brand-600 text-white flex items-center justify-center text-lg font-semibold shadow-sm">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            </div>
        </x-slot:leading>
        <x-slot:actions>
            <x-brand-button href="{{ route('site-visits.report', $siteVisit) }}">View Report</x-brand-button>
            <x-brand-button href="{{ route('site-visits.report.pdf', $siteVisit) }}" variant="outline">Download PDF</x-brand-button>
        </x-slot:actions>
    </x-page-header>

    {{-- Info + Actions --}}
    <div class="rounded-lg bg-white shadow-sm border border-brand-100 p-6 space-y-6">
        <div class="grid md:grid-cols-2 gap-6 text-brand-900">
            <div class="space-y-2">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-brand-600">Details</h2>
                <p><span class="font-semibold">Client:</span> {{ $client->first_name }} {{ $client->last_name }}</p>
                <p><span class="font-semibold">Property:</span> {{ optional($siteVisit->property)->name ?? 'Unassigned' }}</p>
                @if($siteVisit->property)
                    <p><span class="font-semibold">Location:</span> {{ $siteVisit->property->display_address ?? 'No address on file' }}</p>
                    <p>
                        <span class="font-semibold">Property Contact:</span>
                        {{ $siteVisit->property->contact_name ?? 'N/A' }}
                        @if($siteVisit->property->contact_phone)
                            · {{ $siteVisit->property->contact_phone }}
                        @endif
                        @if($siteVisit->property->contact_email)
                            ({{ $siteVisit->property->contact_email }})
                        @endif
                    </p>
                @endif
                <p><span class="font-semibold">Visit Date:</span> {{ optional($siteVisit->visit_date)->format('F j, Y') ?? 'Not scheduled' }}</p>
                <p><span class="font-semibold">Notes:</span> {{ $siteVisit->notes ?? 'No notes recorded' }}</p>
            </div>
            <div class="space-y-3">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-brand-600">Actions</h2>
                <div class="flex flex-wrap gap-3">
                    <x-brand-button href="{{ route('contacts.site-visits.edit', [$client, $siteVisit]) }}">Edit</x-brand-button>
                    <form method="POST" action="{{ route('contacts.site-visits.destroy', [$client, $siteVisit]) }}" onsubmit="return confirm('Delete this site visit?');">
                        @csrf
                        @method('DELETE')
                        <x-secondary-button type="submit" class="text-red-700 border-red-300 hover:bg-red-50">Delete</x-secondary-button>
                    </form>
                    <x-secondary-button href="{{ route('clients.show', $client) }}">Back to Contact</x-secondary-button>
                </div>
            </div>
        </div>
    </div>

    {{-- Calculators --}}
    <div class="rounded-lg bg-white shadow-sm border border-brand-100 p-6 space-y-4">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h2 class="text-xl font-semibold text-brand-900 uppercase tracking-wide">Add Calculator</h2>
            @if(($siteVisitOptions ?? collect())->count() > 1)
                <div class="w-full md:w-auto">
                    <label class="block text-sm font-semibold text-brand-700 mb-1" for="calculator-site-visit">Apply calculator to site visit</label>
                    <select id="calculator-site-visit" class="form-select w-full md:w-64">
                        @foreach ($siteVisitOptions as $option)
                            <option value="{{ $option->id }}" @selected($option->id === $siteVisit->id)>
                                {{ optional($option->visit_date)->format('M j, Y') ?? 'Visit #' . $option->id }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
        @php
            $calculatorLinks = [
                ['label' => 'Retaining Wall', 'route' => route('calculators.wall.form', ['site_visit_id' => $siteVisit->id]), 'template' => route('calculators.wall.form', ['site_visit_id' => '__SITE_ID__'])],
                ['label' => 'Paver Patio', 'route' => route('calculators.patio.form', ['site_visit_id' => $siteVisit->id]), 'template' => route('calculators.patio.form', ['site_visit_id' => '__SITE_ID__'])],
                ['label' => 'Fence', 'route' => route('calculators.fence.form', ['site_visit_id' => $siteVisit->id]), 'template' => route('calculators.fence.form', ['site_visit_id' => '__SITE_ID__'])],
                ['label' => 'Pruning', 'route' => route('calculators.pruning.form', ['site_visit_id' => $siteVisit->id]), 'template' => route('calculators.pruning.form', ['site_visit_id' => '__SITE_ID__'])],
                ['label' => 'Weeding', 'route' => route('calculators.weeding.form', ['site_visit_id' => $siteVisit->id]), 'template' => route('calculators.weeding.form', ['site_visit_id' => '__SITE_ID__'])],
                ['label' => 'Mulching', 'route' => route('calculators.mulching.form', ['site_visit_id' => $siteVisit->id]), 'template' => route('calculators.mulching.form', ['site_visit_id' => '__SITE_ID__'])],
                ['label' => 'Planting', 'route' => route('calculators.planting.form', ['site_visit_id' => $siteVisit->id]), 'template' => route('calculators.planting.form', ['site_visit_id' => '__SITE_ID__'])],
                ['label' => 'Pine Needles', 'route' => route('calculators.pine_needles.form', ['site_visit_id' => $siteVisit->id]), 'template' => route('calculators.pine_needles.form', ['site_visit_id' => '__SITE_ID__'])],
                ['label' => 'Synthetic Turf', 'route' => route('calculators.syn_turf.form', ['site_visit_id' => $siteVisit->id]), 'template' => route('calculators.syn_turf.form', ['site_visit_id' => '__SITE_ID__'])],
                ['label' => 'Turf Mowing', 'route' => route('calculators.turf_mowing.form', ['site_visit_id' => $siteVisit->id]), 'template' => route('calculators.turf_mowing.form', ['site_visit_id' => '__SITE_ID__'])],
            ];
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($calculatorLinks as $calcLink)
                <a href="{{ $calcLink['route'] }}"
                   data-route-template="{{ $calcLink['template'] }}"
                   class="calc-link px-4 py-3 rounded-lg border border-brand-200 text-brand-800 hover:bg-brand-50 flex items-center justify-between">
                    <span>{{ $calcLink['label'] }} Calculator</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Calculations --}}
    <div class="rounded-lg bg-white shadow-sm border border-brand-100 p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-brand-900 uppercase tracking-wide">Calculations</h2>
        </div>
        @if ($calculations->count())
            <div class="overflow-x-auto rounded-lg border border-brand-200">
                <table class="min-w-full border-collapse text-base">
                    <thead class="bg-brand-50 text-brand-600 uppercase tracking-wide">
                        <tr>
                            <th class="px-4 py-3 border border-brand-200 bg-brand-100 text-left">Type</th>
                            <th class="px-4 py-3 border border-brand-200 bg-brand-100 text-left">Created</th>
                            <th class="px-4 py-3 border border-brand-200 bg-brand-100 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-brand-900 text-lg">
                        @foreach ($calculations as $calc)
                            @php
                                $label = ucfirst(str_replace('_', ' ', $calc->calculation_type));
                                $rowShade = $loop->even ? 'bg-brand-100/70' : 'bg-white';
                                $editRoutes = [
                                    'retaining_wall' => 'calculators.wall.edit',
                                    'paver_patio' => 'calculators.patio.edit',
                                    'fence' => 'calculators.fence.edit',
                                    'pruning' => 'calculators.pruning.edit',
                                    'weeding' => 'calculators.weeding.edit',
                                    'mulching' => 'calculators.mulching.edit',
                                    'pine_needles' => 'calculators.pine_needles.edit',
                                    'syn_turf' => 'calculators.syn_turf.edit',
                                    'turf_mowing' => 'calculators.turf_mowing.edit',
                                ];
                                $pdfRoutes = [
                                    'retaining_wall' => 'calculations.wall.downloadPdf',
                                    'paver_patio' => 'calculations.patio.downloadPdf',
                                    'fence' => 'calculators.fence.downloadPdf',
                                    'pruning' => 'calculators.pruning.downloadPdf',
                                    'weeding' => 'calculators.weeding.downloadPdf',
                                    'mulching' => 'calculators.mulching.downloadPdf',
                                    'pine_needles' => 'calculators.pine_needles.downloadPdf',
                                    'syn_turf' => 'calculators.syn_turf.downloadPdf',
                                    'turf_mowing' => 'calculators.turf_mowing.downloadPdf',
                                ];
                                $editRoute = $editRoutes[$calc->calculation_type] ?? null;
                                $pdfRoute = $pdfRoutes[$calc->calculation_type] ?? null;
                            @endphp
                            <tr class="{{ $rowShade }}">
                                <td class="px-4 py-3 border border-brand-200">{{ $label }}</td>
                                <td class="px-4 py-3 border border-brand-200">{{ $calc->created_at->format('M d, Y H:i') }}</td>
                                <td class="px-4 py-3 border border-brand-200">
                                    <div class="flex flex-wrap gap-2">
                                        @if($editRoute)
                                            <x-brand-button href="{{ route($editRoute, $calc->id) }}" size="sm">Edit</x-brand-button>
                                        @endif
                                        @if($pdfRoute)
                                            <x-secondary-button href="{{ route($pdfRoute, $calc->id) }}" size="sm">PDF</x-secondary-button>
                                        @endif
                                        <form method="POST" action="{{ route('site-visits.deleteCalculation', $calc->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <x-danger-button size="sm" onclick="return confirm('Delete this calculation?')">Delete</x-danger-button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-brand-600 text-lg">No calculations saved for this visit.</p>
        @endif
    </div>

    {{-- Photos --}}
    <div class="rounded-lg bg-white shadow-sm border border-brand-100 p-6 space-y-4">
        <h2 class="text-xl font-semibold text-brand-900 uppercase tracking-wide">Site Visit Photos</h2>
        <form method="POST"
              action="{{ route('contacts.site-visits.photos.store', [$client, $siteVisit]) }}"
              enctype="multipart/form-data"
              class="border border-brand-100 rounded-lg p-4 space-y-4">
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
            <x-brand-button type="submit">Upload Photo</x-brand-button>
        </form>

        @if ($siteVisit->photos->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($siteVisit->photos as $photo)
                    <div class="bg-white rounded-lg shadow border border-brand-100 overflow-hidden">
                        <img src="{{ Storage::disk('public')->url($photo->path) }}"
                             alt="{{ $photo->caption ?? 'Site photo' }}"
                             class="w-full h-48 object-cover">
                        <div class="p-3 space-y-2">
                            <p class="text-sm text-brand-700">{{ $photo->caption ?? '' }}</p>
                            <form method="POST"
                                  action="{{ route('contacts.site-visits.photos.destroy', [$client, $siteVisit, $photo]) }}"
                                  onsubmit="return confirm('Delete this photo?');">
                                @csrf
                                @method('DELETE')
                                <x-danger-button size="sm">Delete</x-danger-button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-brand-600">No photos uploaded yet.</p>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('calculator-site-visit');
        const links = document.querySelectorAll('.calc-link');
        if (select && links.length) {
            const updateLinks = () => {
                links.forEach(link => {
                    const template = link.dataset.routeTemplate;
                    if (template) {
                        link.href = template.replace('__SITE_ID__', select.value);
                    }
                });
            };
            select.addEventListener('change', updateLinks);
            updateLinks();
        }
    });
</script>
@endpush
