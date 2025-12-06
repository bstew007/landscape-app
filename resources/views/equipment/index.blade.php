@extends('layouts.sidebar')

@section('content')
@php
    $pageCount = $equipment->count();
    $companyCount = $equipment->where('ownership_type', 'company')->count();
    $rentalCount = $equipment->where('ownership_type', 'rental')->count();
@endphp

<div class="space-y-8">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-2 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Catalogs</p>
                <h1 class="text-2xl sm:text-3xl font-semibold">Equipment Catalog</h1>
                <p class="text-sm text-brand-100/90">Centralized equipment pricing for estimates - company-owned and rental equipment.</p>
            </div>
            <div class="flex flex-wrap gap-3 ml-auto">
                <x-secondary-button as="a" href="{{ route('equipment.importForm') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20">
                    ⬆ Import
                </x-secondary-button>
                <x-secondary-button as="a" href="{{ route('equipment.export') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20">
                    ⬇ Export
                </x-secondary-button>
                <x-brand-button href="{{ route('equipment.create') }}" variant="muted">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Equipment
                </x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">On This Page</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($pageCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Company-Owned</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($companyCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Rental</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($rentalCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Total Catalog</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($equipment->total()) }}</dd>
            </div>
        </dl>
    </section>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3">{{ session('success') }}</div>
    @endif

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-5 sm:p-7 space-y-5">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, SKU, category, model"
                       class="flex-1 min-w-[200px] rounded-full border-brand-200 bg-white text-sm px-4 py-2 focus:ring-brand-500 focus:border-brand-500">
                
                <div class="relative">
                    <button type="button"
                            class="inline-flex items-center gap-2 h-9 px-3 rounded-full border border-brand-200 bg-white text-brand-700 text-sm font-semibold hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1"
                            @click="const menu = document.getElementById('ownershipMenu'); menu?.classList.toggle('hidden');">
                        <svg class="h-4 w-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        {{ $ownership === 'company' ? 'Company' : ($ownership === 'rental' ? 'Rental' : 'All Types') }}
                        <svg class="h-3.5 w-3.5 text-brand-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
                    </button>
                    <div id="ownershipMenu" class="hidden absolute right-0 mt-2 w-48 rounded-2xl border border-brand-100 bg-white shadow-lg z-10 overflow-hidden">
                        <a href="{{ route('equipment.index', array_filter(['search' => $search ?: null, 'category' => $category ?: null])) }}" class="block px-4 py-2 text-sm text-brand-700 hover:bg-brand-50">All Types</a>
                        <a href="{{ route('equipment.index', array_filter(['search' => $search ?: null, 'category' => $category ?: null, 'ownership' => 'company'])) }}" class="block px-4 py-2 text-sm hover:bg-brand-50 {{ $ownership === 'company' ? 'bg-brand-50 text-brand-900 font-semibold' : 'text-brand-700' }}">🏢 Company-Owned</a>
                        <a href="{{ route('equipment.index', array_filter(['search' => $search ?: null, 'category' => $category ?: null, 'ownership' => 'rental'])) }}" class="block px-4 py-2 text-sm hover:bg-brand-50 {{ $ownership === 'rental' ? 'bg-brand-50 text-brand-900 font-semibold' : 'text-brand-700' }}">🔑 Rental</a>
                    </div>
                </div>

                <div class="relative">
                    <button type="button"
                            class="inline-flex items-center gap-2 h-9 px-3 rounded-full border border-brand-200 bg-white text-brand-700 text-sm font-semibold hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1"
                            @click="const menu = document.getElementById('categoryMenu'); menu?.classList.toggle('hidden');">
                        <svg class="h-4 w-4 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        {{ $category ? Str::limit($category, 20) : 'Category' }}
                        <svg class="h-3.5 w-3.5 text-brand-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
                    </button>
                    <div id="categoryMenu" class="hidden absolute right-0 mt-2 w-56 rounded-2xl border border-brand-100 bg-white shadow-lg z-10 overflow-hidden">
                        <a href="{{ route('equipment.index', array_filter(['search' => $search ?: null, 'ownership' => $ownership ?: null])) }}" class="block px-4 py-2 text-sm text-brand-700 hover:bg-brand-50">All categories</a>
                        <div class="max-h-64 overflow-y-auto divide-y divide-brand-50">
                            @forelse($categories as $cat)
                                <a href="{{ route('equipment.index', array_filter(['search' => $search ?: null, 'ownership' => $ownership ?: null, 'category' => $cat])) }}"
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
                @if($search || $category || $ownership)
                    <a href="{{ route('equipment.index') }}" class="text-xs text-brand-500 hover:text-brand-700">Clear</a>
                @endif
            </form>
        </div>

        <form method="POST" action="{{ route('equipment.bulk') }}">
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
                        <th class="px-4 py-3">Equipment</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3 text-right">Rate</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                    @forelse ($equipment as $item)
                        <tr class="transition hover:bg-brand-50/70">
                            <td class="px-4 py-3 align-top"><input type="checkbox" name="ids[]" value="{{ $item->id }}" class="rowCheck"></td>
                            <td class="px-4 py-3 align-top">
                                <a href="{{ route('equipment.edit', $item) }}" class="font-semibold text-brand-900 hover:underline">
                                    {{ $item->name }}
                                </a>
                                @if($item->model)
                                    <p class="text-xs text-brand-500">{{ $item->model }}</p>
                                @endif
                                @if($item->sku)
                                    <p class="text-xs text-brand-400">SKU: {{ $item->sku }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $item->ownership_type === 'company' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                                    {{ $item->ownership_type === 'company' ? '🏢 Company' : '🔑 Rental' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-top">
                                @if($item->category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                                        {{ $item->category }}
                                    </span>
                                @else
                                    <span class="text-brand-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top text-right">
                                @php
                                    $rate = $item->unit === 'day' ? $item->daily_rate : $item->hourly_rate;
                                @endphp
                                @if($rate)
                                    <span class="font-semibold text-brand-900">${{ number_format($rate, 2) }}</span>
                                    <span class="text-xs text-brand-500">/{{ $item->unit }}</span>
                                @else
                                    <span class="text-brand-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {{ $item->is_active ? 'bg-emerald-100 text-emerald-700 border border-emerald-200' : 'bg-brand-50 text-brand-400 border border-brand-200' }}">
                                    {{ $item->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-top text-right space-x-2">
                                <a href="{{ route('equipment.edit', $item) }}" class="text-brand-600 hover:text-brand-800 font-medium">Edit</a>
                                <form method="POST" action="{{ route('equipment.destroy', $item) }}" class="inline" onsubmit="return confirm('Delete this equipment?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-medium">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-brand-400">
                                No equipment found. <a href="{{ route('equipment.create') }}" class="text-brand-600 hover:underline">Add one now</a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </form>
    </section>

    @if ($equipment->hasPages())
        <div class="flex justify-center">
            {{ $equipment->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
(function(){
    const checkAll = document.getElementById('checkAll');
    const rowChecks = document.querySelectorAll('.rowCheck');
    const bulkBar = document.getElementById('bulkBar');
    const bulkAction = document.getElementById('bulkAction');
    const bulkCategory = document.getElementById('bulkCategory');
    const applyBtn = document.getElementById('applyBulkBtn');
    const selectedCount = document.getElementById('selectedCount');
    const clearBtn = document.getElementById('clearSelection');

    function updateUI() {
        const checked = document.querySelectorAll('.rowCheck:checked');
        selectedCount.textContent = checked.length;
        if (checked.length > 0) {
            bulkBar?.classList.remove('hidden');
        } else {
            bulkBar?.classList.add('hidden');
        }
        applyBtn.disabled = !bulkAction.value || (bulkAction.value === 'set_category' && !bulkCategory.value);
    }

    checkAll?.addEventListener('change', function() {
        rowChecks.forEach(c => c.checked = this.checked);
        updateUI();
    });

    rowChecks.forEach(c => c.addEventListener('change', updateUI));

    bulkAction?.addEventListener('change', function() {
        if (this.value === 'set_category') {
            bulkCategory?.classList.remove('hidden');
        } else {
            bulkCategory?.classList.add('hidden');
        }
        updateUI();
    });

    bulkCategory?.addEventListener('change', updateUI);

    clearBtn?.addEventListener('click', function() {
        rowChecks.forEach(c => c.checked = false);
        if (checkAll) checkAll.checked = false;
        updateUI();
    });
})();
</script>
@endpush
@endsection
