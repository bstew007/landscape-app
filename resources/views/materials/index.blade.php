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
                <div class="relative">
                    <button type="button"
                            class="inline-flex items-center gap-2 h-9 px-3 rounded-full border border-brand-200 bg-white text-brand-700 text-sm font-semibold hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1"
                            @click="const menu = document.getElementById('categoryMenu'); menu?.classList.toggle('hidden');">
                        <svg class="h-4 w-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        {{ $category ? Str::limit($category, 24) : 'Category' }}
                        <svg class="h-3.5 w-3.5 text-brand-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
                    </button>
                    <div id="categoryMenu" class="hidden absolute right-0 mt-2 w-56 rounded-2xl border border-brand-100 bg-white shadow-lg z-10 overflow-hidden">
                        <a href="{{ route('materials.index', array_filter(['search' => $search ?: null])) }}" class="block px-4 py-2 text-sm text-brand-700 hover:bg-brand-50">All categories</a>
                        <a href="{{ route('materials.index', array_filter(['search' => $search ?: null, 'category' => '_none'])) }}" class="block px-4 py-2 text-sm hover:bg-brand-50 {{ $category === '_none' ? 'bg-brand-50 text-brand-900 font-semibold' : 'text-brand-700' }}">Uncategorized</a>
                        <div class="max-h-64 overflow-y-auto divide-y divide-brand-50">
                            @forelse($categories as $cat)
                                <a href="{{ route('materials.index', array_filter(['search' => $search ?: null, 'category' => $cat])) }}"
                                   class="block px-4 py-2 text-sm hover:bg-brand-50 {{ $category === $cat ? 'bg-brand-50 text-brand-900 font-semibold' : 'text-brand-700' }}">
                                    {{ $cat }}
                                </a>
                            @empty
                                <div class="px-4 py-3 text-sm text-brand-400">No categories yet</div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-1.5 h-9 px-4 rounded-full bg-brand-600 text-white text-sm font-semibold shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1 transition disabled:opacity-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    Search
                </button>
                @if($search || $category)
                    <a href="{{ route('materials.index') }}" class="text-xs text-brand-500 hover:text-brand-700">Clear</a>
                @endif
            </form>
        </div>

        <form method="POST" action="{{ request()->getSchemeAndHttpHost() . request()->getBaseUrl() . '/catalog/materials/bulk' }}">
            @csrf
            <div class="px-4 sm:px-6 lg:px-8 py-4">
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
                    <select name="category" id="bulkCategory" class="hidden rounded-full border-brand-200 bg-white text-sm px-3 py-1.5 focus:ring-brand-500 focus:border-brand-500 min-w-[180px]">
                        <option value="">Select category…</option>
                        <option value="_none">Uncategorized</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
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
                                <a href="{{ route('materials.edit', $material) }}" class="font-semibold text-brand-900 hover:underline">
                                    {{ $material->name }}
                                </a>
                                <p class="text-xs text-brand-400">{{ $material->unit }}</p>
                            </td>
                            <td class="px-4 py-3 align-top text-brand-700">{{ $material->sku ?: '—' }}</td>
                            <td class="px-4 py-3 align-top">
                                @if($material->categories->count() > 0)
                                    @foreach($material->categories as $cat)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200 mr-1 mb-1">
                                            {{ $cat->name }}
                                        </span>
                                    @endforeach
                                @elseif($material->category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                                        {{ $material->category }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-50 text-gray-500 border border-gray-200">
                                        Uncategorized
                                    </span>
                                @endif
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
                                <form method="POST" action="{{ route('materials.destroy', $material) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Delete this material? This cannot be undone.');"
                                            class="inline-flex items-center justify-center h-9 w-9 rounded-full border border-red-200 bg-red-50 text-red-700 hover:bg-red-100"
                                            aria-label="Delete material">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                            <path d="M14 10v8" />
                                            <path d="M10 10v8" />
                                            <path d="M5 6l1-3h12l1 3" />
                                        </svg>
                                    </button>
                                </form>
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
                applyBtn.disabled = selected === 0 || !actionSel.value || (actionSel.value === 'set_category' && !catInput.value);
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
            catInput.addEventListener('change', updateBar);
            
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
