@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto py-10 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold">Materials Catalog</h1>
            <p class="text-sm text-gray-600">Centralized pricing + SKU data for estimates and calculators.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('materials.importForm') }}"
               class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                ⬆ Import (JSON/CSV)
            </a>
            <a href="{{ route('materials.export') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800">
                ⬇ Export CSV
            </a>
            <a href="{{ route('materials.create') }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                + Add Material
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif

    <form method="GET" class="flex flex-col sm:flex-row gap-3 bg-white p-4 rounded shadow">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, SKU, category"
               class="form-input flex-1">
        <button class="px-4 py-2 bg-brand-700 text-white rounded hover:bg-brand-800">Search</button>
    </form>

    <form method="POST" action="{{ request()->getSchemeAndHttpHost() . request()->getBaseUrl() . '/catalog/materials/bulk' }}">
        @csrf
        <div class="bg-white rounded shadow overflow-x-auto">
            <div id="bulkBar" class="hidden px-4 py-3 border-b flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="text-sm text-gray-700">
                    <span id="selectedCount">0</span> selected
                </div>
                <div class="flex items-center gap-2">
                    <select name="action" id="bulkAction" class="form-select text-sm">
                        <option value="">Bulk action…</option>
                        <option value="delete">Delete</option>
                        <option value="set_active">Make Active</option>
                        <option value="set_inactive">Make Inactive</option>
                        <option value="set_category">Change Category…</option>
                    </select>
                    <input type="text" name="category" id="bulkCategory" class="form-input text-sm hidden" placeholder="New category">
                    <button type="submit" class="px-3 py-1.5 bg-red-600 text-white rounded text-sm hover:bg-red-700" id="applyBulkBtn" disabled>Apply</button>
                </div>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3"><input type="checkbox" id="checkAll"></th>
                    <th class="text-left px-4 py-3">Name</th>
                    <th class="text-left px-4 py-3">SKU</th>
                    <th class="text-left px-4 py-3">Category</th>
                    <th class="text-right px-4 py-3">Unit Cost</th>
                    <th class="text-center px-4 py-3">Active</th>
                    <th class="px-4 py-3"></th>
                </tr>
                </thead>
                <tbody>
                @forelse ($materials as $material)
                    <tr class="border-t">
                        <td class="px-4 py-3 align-top"><input type="checkbox" name="ids[]" value="{{ $material->id }}" class="rowCheck"></td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-900">{{ $material->name }}</div>
                            <div class="text-xs text-gray-500">{{ $material->unit }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $material->sku ?: '—' }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $material->category ?: '—' }}</td>
                        <td class="px-4 py-3 text-right text-gray-900">${{ number_format($material->unit_cost, 2) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($material->is_active)
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-gray-200 text-gray-700">Hidden</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('materials.edit', $material) }}" class="text-blue-600 hover:underline mr-3">Edit</a>
                            <form action="{{ route('materials.destroy', $material) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Delete this material?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">No materials yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    {{ $materials->links() }}
    </form>

    @push('scripts')
    <script>
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
            updateBar();
        });
    </script>
    @endpush
</div>
@endsection
