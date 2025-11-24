@extends('layouts.sidebar')

@section('content')
@php
    $pageCount = $materials->count();
@endphp

<div class="space-y-8">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-2 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Catalogs</p>
                <h1 class="text-2xl sm:text-3xl font-semibold">Materials Catalog</h1>
                <p class="text-sm text-brand-100/90">Centralized pricing + SKU data for estimates and calculators.</p>
            </div>
            <div class="flex flex-wrap gap-3 ml-auto">
                <x-secondary-button as="a" href="{{ route('materials.importForm') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20">
                    ⬆ Import
                </x-secondary-button>
                <x-secondary-button as="a" href="{{ route('materials.export') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20">
                    ⬇ Export
                </x-secondary-button>
                <x-brand-button href="{{ route('materials.create') }}" variant="muted">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Material
                </x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">On This Page</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($pageCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Active Items</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($materials->where('is_active', true)->count()) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Total Catalog</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($materials->total()) }}</dd>
            </div>
        </dl>
    </section>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3">{{ session('success') }}</div>
    @endif

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-5 sm:p-7 space-y-5">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, SKU, category"
                       class="flex-1 min-w-[200px] rounded-full border-brand-200 bg-white text-sm px-4 py-2 focus:ring-brand-500 focus:border-brand-500">
                <x-brand-button type="submit" size="sm">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    Search
                </x-brand-button>
                @if($search)
                    <a href="{{ route('materials.index') }}" class="text-xs text-brand-500 hover:text-brand-700">Clear</a>
                @endif
            </form>

            <form method="POST" action="{{ request()->getSchemeAndHttpHost() . request()->getBaseUrl() . '/catalog/materials/bulk' }}">
                @csrf
                <div id="bulkBar" class="hidden flex flex-wrap items-center gap-3 text-sm mb-4">
                    <span class="text-xs uppercase tracking-wide text-brand-400">Bulk Actions</span>
                    <span class="text-brand-700 font-medium"><span id="selectedCount">0</span> selected</span>
                    <select name="action" id="bulkAction" class="min-w-[180px] rounded-full border-brand-200 bg-white text-sm px-3 py-1.5 focus:ring-brand-500 focus:border-brand-500">
                        <option value="">Choose action…</option>
                        <option value="delete">Delete selected</option>
                        <option value="set_active">Make active</option>
                        <option value="set_inactive">Make inactive</option>
                        <option value="set_category">Change category…</option>
                    </select>
                    <input type="text" name="category" id="bulkCategory" class="rounded-full border-brand-200 bg-white text-sm px-3 py-1.5 hidden" placeholder="New category">
                    <x-brand-button type="submit" id="applyBulkBtn" size="sm" disabled>Apply</x-brand-button>
                    <span class="mx-2 text-brand-200">|</span>
                    <button type="button" class="text-xs text-brand-600 hover:text-brand-800" id="clearSelection">Clear selection</button>
                </div>
        </div>

        <div class="border-t border-brand-100/60">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-brand-50/80 text-left text-[11px] uppercase tracking-wide text-brand-500">
                    <tr>
                        <th class="px-4 py-3"><input type="checkbox" id="checkAll"></th>
                        <th class="px-4 py-3">Material</th>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3 text-right">Unit Cost</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                    @forelse ($materials as $material)
                        <tr class="transition hover:bg-brand-50/70">
                            <td class="px-4 py-3 align-top"><input type="checkbox" name="ids[]" value="{{ $material->id }}" class="rowCheck"></td>
                            <td class="px-4 py-3 align-top">
                                <p class="font-semibold text-brand-900">{{ $material->name }}</p>
                                <p class="text-xs text-brand-400">{{ $material->unit }}</p>
                            </td>
                            <td class="px-4 py-3 align-top text-brand-700">{{ $material->sku ?: '—' }}</td>
                            <td class="px-4 py-3 align-top">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                                    {{ $material->category ?: 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-top text-right font-semibold text-brand-900">${{ number_format($material->unit_cost, 2) }}</td>
                            <td class="px-4 py-3 align-top text-center">
                                @if ($material->is_active)
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
                                @else
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top text-right">
                                <x-brand-button href="{{ route('materials.edit', $material) }}" variant="outline" size="sm">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </x-brand-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-brand-400">No materials found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-5 py-4 border-t border-brand-100/60">
            {{ $materials->links() }}
        </div>
            </form>
    </section>
</div>

    @push('scripts')
    <script>
        function deleteMaterial(url){
            try{
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ _method: 'DELETE', _token: token }).toString(),
                }).then(res => {
                    if (res.ok) { window.location.reload(); }
                    else { res.text().then(t => alert('Delete failed: ' + t)); }
                });
            } catch (e) { alert('Delete failed'); }
        }
        document.addEventListener('DOMContentLoaded', function () {
            const checkAll = document.getElementById('checkAll');
            const checks = document.querySelectorAll('.rowCheck');
            const bulkBar = document.getElementById('bulkBar');
            const selectedCount = document.getElementById('selectedCount');
            const applyBtn = document.getElementById('applyBulkBtn');
            const actionSel = document.getElementById('bulkAction');
            const catInput = document.getElementById('bulkCategory');

            function updateBar(){
                const selected = document.querySelectorAll('.rowCheck:checked').length;
                selectedCount.textContent = selected;
                bulkBar.classList.toggle('hidden', selected === 0);
                applyBtn.disabled = selected === 0 || !actionSel.value;
                catInput.classList.toggle('hidden', actionSel.value !== 'set_category');
                if (actionSel.value !== 'set_category') catInput.value = '';
            }

            if (checkAll) {
                checkAll.addEventListener('change', function(){
                    checks.forEach(c => c.checked = checkAll.checked);
                    updateBar();
                });
            }
            checks.forEach(c => c.addEventListener('change', updateBar));
            actionSel.addEventListener('change', updateBar);
            
            const clearBtn = document.getElementById('clearSelection');
            if (clearBtn) {
                clearBtn.addEventListener('click', function(){
                    checks.forEach(c => c.checked = false);
                    if (checkAll) checkAll.checked = false;
                    updateBar();
                });
            }
            
            updateBar();
        });
    </script>
    @endpush
</div>
@endsection
