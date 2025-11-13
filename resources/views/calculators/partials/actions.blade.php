{{-- Shared Calculator Actions Partial --}}
{{-- Expects: $calculationType (string), $siteVisit (SiteVisit), $data (array), optional $calculation (Calculation), optional $downloadPdfUrl (string) --}}
<div class="mt-8 bg-white p-6 rounded-lg shadow">
    <div class="flex flex-col gap-4">
        {{-- Estimate Picker --}}
        @php
            $estimates = \App\Models\Estimate::where('site_visit_id', $siteVisit->id)
                ->orderByDesc('id')
                ->get(['id','title','status']);
        @endphp
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label for="estimate_id" class="block text-sm font-semibold text-gray-700">Import into Estimate</label>
                <select id="estimate_id" name="estimate_id" form="calc-action-form-{{ $calculationType }}"
                        class="mt-1 block w-72 rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">— Select estimate (optional) —</option>
                    @foreach($estimates as $est)
                        <option value="{{ $est->id }}">#{{ $est->id }} — {{ $est->title ?? 'Untitled' }} ({{ ucfirst($est->status ?? 'draft') }})</option>
                    @endforeach
                </select>
                @if($estimates->isEmpty())
                    <p class="text-xs text-gray-500 mt-1">No estimates for this site visit yet.</p>
                @endif
            </div>
            <div class="pb-1">
                <a href="{{ route('estimates.create', ['client_id' => $siteVisit->client_id, 'site_visit_id' => $siteVisit->id, 'property_id' => $siteVisit->property_id]) }}" class="btn btn-primary">
                    ➕ New Estimate
                </a>
            </div>
        </div>

        {{-- Actions Row --}}
        <div class="flex flex-wrap gap-3 items-center">
            <form id="calc-action-form-{{ $calculationType }}" method="POST" action="{{ route('site-visits.storeCalculation') }}" class="flex flex-wrap gap-3 items-center">
                @csrf
                <input type="hidden" name="calculation_type" value="{{ $calculationType }}">
                <input type="hidden" name="site_visit_id" value="{{ $siteVisit->id }}">
                <input type="hidden" name="data" value='@json($data)'>

                <button type="submit" name="no_import" value="1" class="btn btn-secondary">
                    💾 Save to Site Visit
                </button>
                <button type="submit" name="append" value="1" class="btn btn-primary" data-requires-estimate>
                    ➕ Save & Append to Estimate
                </button>
                <button type="submit" name="replace" value="1" class="btn btn-danger" data-requires-estimate>
                    ♻️ Save & Replace on Estimate
                </button>
            </form>

            @isset($calculation)
                @isset($downloadPdfUrl)
                    <a href="{{ $downloadPdfUrl }}" class="btn btn-info">📄 Download PDF</a>
                @endisset
            @endisset

            <a href="{{ route('clients.show', $siteVisit->client_id) }}" class="btn btn-muted">🔙 Back to Client</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const form = document.getElementById('calc-action-form-{{ $calculationType }}');
        if (!form) return;
        const select = document.getElementById('estimate_id');
        const requires = form.querySelectorAll('[data-requires-estimate]');

        function updateButtons() {
            const hasValue = select && select.value;
            requires.forEach(btn => {
                if (hasValue) {
                    btn.removeAttribute('disabled');
                } else {
                    btn.setAttribute('disabled', 'disabled');
                }
            });
        }

        if (select) {
            updateButtons();
            select.addEventListener('change', updateButtons);
        } else {
            // No select on page; disable buttons that require estimate
            requires.forEach(btn => btn.setAttribute('disabled','disabled'));
        }
    });
</script>
@endpush