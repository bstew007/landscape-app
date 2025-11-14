@extends('layouts.sidebar')

@section('content')
<div class="max-w-7xl mx-auto" x-data="templateGallery()">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Calculator Templates</h1>
        <a href="{{ route('estimates.index') }}" class="text-sm text-blue-600">Back to Estimates</a>
    </div>

    <form method="GET" class="bg-white border rounded p-4 mb-4 grid grid-cols-1 md:grid-cols-5 gap-3">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Type</label>
            <select name="type" class="form-select w-full">
                <option value="">All</option>
                @foreach (['mulching','syn_turf','paver_patio','retaining_wall','weeding','turf_mowing','fence','planting','pruning','pine_needles'] as $t)
                    <option value="{{ $t }}" @selected(request('type')===$t)>{{ Str::headline($t) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Scope</label>
            <select name="scope" class="form-select w-full">
                <option value="">All</option>
                <option value="global" @selected(request('scope')==='global')>Global</option>
                <option value="client" @selected(request('scope')==='client')>Client</option>
                <option value="property" @selected(request('scope')==='property')>Property</option>
                <option value="mine" @selected(request('scope')==='mine')>Mine</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Search</label>
            <input type="text" name="q" class="form-input w-full" value="{{ request('q') }}" placeholder="Template name">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" class="form-input w-full" value="{{ request('from') }}">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" class="form-input w-full" value="{{ request('to') }}">
        </div>
        <div class="md:col-span-5 text-right">
            <button class="px-3 py-2 bg-emerald-700 text-white rounded">Filter</button>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($templates as $tpl)
        <div class="bg-white border rounded p-4 flex flex-col gap-3">
            <div class="flex items-center justify-between gap-2">
                <form method="POST" action="{{ route('calculator.templates.update', $tpl) }}" class="flex-1 flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="text" name="template_name" class="form-input w-full" value="{{ $tpl->template_name ?? '(Untitled)' }}">
                    <button class="text-sm px-3 py-2 rounded bg-emerald-700 text-white hover:bg-emerald-800">Save</button>
                </form>
                <form method="POST" action="{{ route('calculator.templates.destroy', $tpl) }}" onsubmit="return confirm('Delete this template?');" class="flex items-center">
                    @csrf
                    @method('DELETE')
                    <button class="text-sm px-3 py-2 rounded bg-red-600 text-white hover:bg-red-700">Delete</button>
                </form>
            </div>
            <div class="text-xs text-gray-500">Type: {{ Str::headline($tpl->calculation_type) }} · Scope: {{ ucfirst($tpl->template_scope) }}</div>
            <div class="text-xs text-gray-500">Created: {{ optional($tpl->created_at)->format('M j, Y') }}</div>
            <div class="mt-1 border-t pt-2 text-sm space-y-1">
                @php
                    $d = $tpl->data ?? [];
                    $material = $d['material_total'] ?? null;
                    $laborCost = $d['labor_cost'] ?? null;
                    $hours = $d['total_hours'] ?? ($d['labor_hours'] ?? null);
                    $final = $d['final_price'] ?? null;
                @endphp
                <div>Materials: {{ $material !== null ? '$'.number_format($material,2) : '—' }}</div>
                <div>Labor: {{ $laborCost !== null ? '$'.number_format($laborCost,2) : '—' }} ({{ $hours !== null ? number_format($hours,2).' hrs' : '—' }})</div>
                <div>Final: {{ $final !== null ? '$'.number_format($final,2) : '—' }}</div>
            </div>
            <div class="mt-auto pt-2 border-t">
                <div class="flex flex-wrap items-center gap-2">
                    <a class="text-sm px-3 py-2 rounded border border-gray-300 hover:bg-gray-50" href="{{ url('/calculators/'.str_replace('_','-',$tpl->calculation_type).'/'.$tpl->id.'/edit') }}">Open in Calculator</a>
                    <form method="POST" action="{{ route('calculator.templates.duplicate', $tpl) }}">
                        @csrf
                        <button class="text-sm px-3 py-2 rounded border border-gray-300 hover:bg-gray-50">Duplicate</button>
                    </form>
                    <button type="button" class="text-sm px-3 py-2 rounded bg-emerald-700 text-white hover:bg-emerald-800 ml-auto" @click="openImport({ id: {{ $tpl->id }}, name: '{{ addslashes($tpl->template_name) }}' })">Import</button>
                </div>
            </div>
        </div>
        @empty
            <p class="text-sm text-gray-500">No templates found.</p>
        @endforelse
    <div class="mt-4">{{ $templates->links() }}</div>
</div>

<!-- Import Modal -->
<div x-show="modalOpen" class="fixed inset-0 z-50" style="display:none;">
    <div class="absolute inset-0 bg-black/40" @click="closeImport()"></div>
    <div class="absolute inset-x-0 top-16 mx-auto max-w-lg bg-white rounded shadow p-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-lg font-semibold">Import Template</h3>
            <button class="text-gray-500" @click="closeImport()">Close</button>
        </div>
        <div class="text-sm text-gray-600 mb-3" x-text="currentTpl?.name"></div>
        <div class="space-y-3">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Search Estimate (# or title)</label>
                <input type="text" class="form-input w-full" placeholder="e.g. 123 or Front Yard Cleanup" x-model="search" @focus="if(!search){fetchEstimates()}" @input="debouncedSearch()" @keydown.enter.prevent="tryAutoSelectAndLoadAreas()">
                <div class="mt-2 max-h-40 overflow-y-auto border rounded" x-show="results.length">
                    <template x-for="r in results" :key="r.id">
                        <button type="button" class="w-full text-left px-3 py-2 hover:bg-gray-50 text-sm" @click="selectEstimate(r)">
                            <span class="font-medium" x-text="'#'+r.id+' · '+r.title"></span>
                            <span class="text-xs text-gray-500" x-text="r.client ? (' · '+r.client) : ''"></span>
                            <span class="text-xs text-gray-500" x-text="r.property ? (' · '+r.property) : ''"></span>
                        </button>
                    </template>
                </div>
                <p class="text-xs text-gray-500 mt-1" x-show="selected">Selected: <span x-text="'#'+selected?.id+' · '+selected?.title"></span></p>
            </div>
            <div x-show="areas.length">
                <label class="block text-xs text-gray-500 mb-1">Work Area (optional)</label>
                <select class="form-select w-full" x-model.number="areaId">
                    <option value="">Unassigned</option>
                    <template x-for="a in areas" :key="a.id">
                        <option :value="a.id" x-text="a.name"></option>
                    </template>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input id="replaceFlag" type="checkbox" x-model="replace" class="form-checkbox">
                <label for="replaceFlag" class="text-sm">Replace existing items for this template</label>
            </div>
            <div class="text-right">
                <button class="px-3 py-2 rounded bg-emerald-700 text-white hover:bg-emerald-800 disabled:opacity-50" :disabled="( !selected && !parseIdFromSearch() ) || loading" @click="submitImport()">
                    <span x-show="!loading">Import</span>
                    <span x-show="loading">Importing...</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function templateGallery(){
    return {
        modalOpen: false,
        currentTpl: null,
        search: '',
        results: [],
        selected: null,
        areas: [],
        areaId: '',
        replace: false,
        loading: false,
        openImport(tpl){ this.currentTpl = tpl; this.modalOpen = true; this.search = ''; this.results = []; this.selected = null; this.areas = []; this.areaId=''; this.replace=false; },
        closeImport(){ this.modalOpen = false; },
        parseIdFromSearch(){ const m = (this.search || '').match(/\d+/); return m ? parseInt(m[0], 10) : null; },
        async tryAutoSelectAndLoadAreas(){ if (this.selected) return; const id = this.parseIdFromSearch(); if (!id) return; this.selected = { id: id, title: '' }; try { const res = await fetch(`{{ url('calculator/templates/estimates') }}/${id}/areas`, { headers: { 'Accept': 'application/json' } }); const json = await res.json(); this.areas = json.areas || []; } catch(_) { this.areas = []; } },
        async fetchEstimates(){
            const url = `{{ route('calculator.templates.estimates.search') }}?q=${encodeURIComponent(this.search)}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            this.results = json.results || [];
        },
        debouncedSearch: debounce(function(){ this.fetchEstimates(); }, 250),
        async selectEstimate(e){
            this.selected = e; this.results = []; this.search = `${e.id} · ${e.title}`;
            // Load areas
            try {
                const res = await fetch(`{{ url('calculator/templates/estimates') }}/${e.id}/areas`, { headers: { 'Accept': 'application/json' } });
                const json = await res.json();
                this.areas = json.areas || [];
            } catch(_) { this.areas = []; }
        },
        async submitImport(){
            if (!this.currentTpl) return;
            if (!this.selected) {
                const id = this.parseIdFromSearch();
                if (!id) return; // need a valid estimate id
                this.selected = { id: id, title: '' };
                await this.tryAutoSelectAndLoadAreas();
            }
            this.loading = true;
            try {
                const form = new FormData();
                form.append('estimate_id', this.selected.id);
                if (this.areaId) form.append('area_id', this.areaId);
                if (this.replace) form.append('replace', '1');
                form.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                const res = await fetch(`{{ url('calculator/templates') }}/${this.currentTpl.id}/import`, { method: 'POST', body: form });
                if (!res.ok) throw new Error('Import failed');
                window.location = `{{ url('estimates') }}/${this.selected.id}`;
            } catch (e) {
                alert('Import failed.');
            } finally {
                this.loading = false;
            }
        },
    }
}

function debounce(fn, delay){ let t; return function(){ clearTimeout(t); const args = arguments, ctx = this; t = setTimeout(()=>fn.apply(ctx,args), delay); } }
</script>
@endpush
@endsection
