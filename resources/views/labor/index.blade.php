@extends('layouts.sidebar')

@section('content')
@php
    $budgetName = $budgetName ?? null;
    $overheadRate = $overheadRate ?? 0;
    $overheadHours = $overheadHours ?? 0;
    $profitMarginPct = $profitMarginPct ?? null;
    $isPublicCatalog = $isPublicCatalog ?? false;
@endphp

<div class="space-y-8 w-full p-4">
    <!-- Hero Header -->
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-3 max-w-3xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Catalogs</p>
                <h1 class="text-3xl sm:text-4xl font-semibold">Labor Catalog</h1>
                <p class="text-sm text-brand-100/85">Manage your labor items with wage calculations, overhead recovery, and profit margins based on your active budget.</p>
            </div>
            <div class="ml-auto">
                <a href="{{ route('labor.create') }}" class="inline-flex items-center h-10 px-5 rounded-lg bg-white text-brand-900 text-sm font-semibold hover:bg-brand-50 shadow-lg">
                    <svg class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    New Labor Item
                </a>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Active Budget</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ $budgetName ?? 'No active budget' }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Overhead Rate</dt>
                <dd class="text-2xl font-semibold text-white mt-2">${{ number_format($overheadRate ?? 0, 2) }}/hr</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Profit Margin</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ isset($profitMarginPct) ? number_format($profitMarginPct, 1) . '%' : '—' }}</dd>
            </div>
        </dl>
    </section>

    <!-- Main Content Card -->
    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-5 sm:p-7 border-b border-brand-100/60">
            <form method="GET" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or type" class="form-input w-full sm:max-w-xs rounded-xl border-brand-200 focus:ring-brand-500 focus:border-brand-500">
                <div class="flex items-center gap-3">
                    <button type="submit" class="inline-flex items-center h-9 px-4 rounded-lg bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700">Search</button>
                    @if($search)
                        <a href="{{ route('labor.index') }}" class="inline-flex items-center h-9 px-4 rounded-lg border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
        @php $overheadRate = $overheadRate ?? 0; @endphp
        <table class="w-full text-sm">
            <thead class="bg-brand-50/80 text-xs uppercase text-brand-500">
            <tr>
                <th class="text-left px-4 py-3">Name</th>
                <th class="text-right px-4 py-3 whitespace-nowrap">Wage/Hr</th>
                <th class="text-right px-4 py-3 whitespace-nowrap">Cost/Hr</th>
                <th class="text-right px-4 py-3">Breakeven</th>
                <th class="text-right px-4 py-3 whitespace-nowrap">Rate/Hr</th>
                <th class="text-right px-4 py-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($labor as $entry)
                @php
                    $wage = (float) ($entry->average_wage ?? 0);
                    $otMult = max(1, (float) ($entry->overtime_factor ?? 1));
                    $burdenPct = max(0, (float) ($entry->labor_burden_percentage ?? 0));
                    $unbillPct = min(99.9, max(0, (float) ($entry->unbillable_percentage ?? 0)));
                    $effectiveWage = $wage * $otMult;
                    $costPerHour = $effectiveWage * (1 + ($burdenPct / 100));
                    $billableFraction = max(0.01, 1 - ($unbillPct / 100));
                    $breakeven = ($costPerHour / $billableFraction) + $overheadRate;
                @endphp
                <tr class="border-t">
                    <td class="px-4 py-3">
                        <div class="font-semibold text-gray-900">{{ $entry->name }}</div>
                        <div class="text-xs text-gray-500">{{ ucfirst($entry->type) }} · {{ $entry->unit }}</div>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-900">${{ number_format($wage, 2) }}</td>
                    <td class="px-4 py-3 text-right text-gray-900">${{ number_format($costPerHour, 2) }}</td>
                    <td class="px-4 py-3 text-right text-gray-900">${{ number_format($breakeven, 2) }}</td>
                    <td class="px-4 py-3 text-right text-gray-900">${{ number_format($entry->base_rate, 2) }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center gap-3">
                            @if(request()->has('estimate_id'))
                                <button
                                    type="button"
                                    class="text-green-600 hover:underline"
                                    title="Insert into estimate"
                                    data-insert-labor="{{ $entry->id }}"
                                >
                                    Insert
                                </button>
                            @endif
                            <a href="{{ route('labor.edit', $entry) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('labor.destroy', $entry) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Delete this labor entry?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-brand-400">No labor entries yet.</td>
            </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-brand-100/60">
            {{ $labor->links() }}
        </div>
    </section>
</div>

@push('scripts')
<script>
(function(){
    function getQueryParam(name){
        const url = new URL(window.location.href);
        return url.searchParams.get(name);
    }
    const estimateId = getQueryParam('estimate_id');
    if (!estimateId) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    async function insertLabor(laborId, areaId = null){
        try {
            const url = `/estimates/${encodeURIComponent(estimateId)}/items`;
            const fd = new FormData();
            fd.append('item_type', 'labor');
            fd.append('catalog_type', 'labor');
            fd.append('catalog_id', String(laborId));
            fd.append('quantity', '1'); // default
            if (areaId) fd.append('area_id', String(areaId)); // optional

            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: fd
            });

            if (!res.ok) {
                const err = await res.json().catch(()=>({message:'Insert failed'}));
                throw new Error(err.message || 'Insert failed');
            }

            const data = await res.json().catch(()=>null);

            if (window.parent && window.parent !== window) {
                window.parent.postMessage({ type: 'estimate:item:inserted', payload: data }, '*');
            }

            if (typeof window.showToast === 'function') {
                window.showToast('Labor item inserted', 'success');
            } else {
                alert('Labor item inserted');
            }

        } catch (e) {
            if (typeof window.showToast === 'function') {
                window.showToast(e.message || 'Insert failed', 'error');
            } else {
                alert(e.message || 'Insert failed');
            }
        }
    }

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-insert-labor]');
        if (!btn) return;
        const laborId = btn.getAttribute('data-insert-labor');
        if (!laborId) return;
        const areaId = getQueryParam('area_id'); // optional passthrough
        insertLabor(laborId, areaId);
    });
})();
</script>
@endpush
@endsection
