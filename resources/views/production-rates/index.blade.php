@extends('layouts.sidebar')

@section('content')

<x-page-header title="Manage Production Rates" eyebrow="Admin" subtitle="Tune task speeds used by calculators.">
    <x-slot:actions>
        <x-brand-button href="{{ route('materials.index') }}" variant="outline">Materials</x-brand-button>
        <x-brand-button href="{{ route('admin.budgets.index') }}" variant="outline">Company Budget</x-brand-button>
    </x-slot:actions>
</x-page-header>

<form method="GET" action="{{ route('production-rates.index') }}" class="mt-6 mb-6 flex flex-wrap gap-4 items-end">
    {{-- Calculator Filter --}}
    <div class="flex-1 min-w-[200px]">
        <label for="calculator" class="block text-sm font-medium text-gray-700">Calculator</label>
        <select name="calculator" id="calculator" class="w-full border rounded px-3 py-2">
            <option value="">All</option>
            @foreach ($calculators as $calc)
                <option value="{{ $calc }}" @if(request('calculator') === $calc) selected @endif>{{ ucfirst($calc) }}</option>
            @endforeach
        </select>
    </div>

    {{-- Task Search --}}
    <div class="flex-1 min-w-[200px]">
        <label for="task" class="block text-sm font-medium text-gray-700">Task</label>
        <input type="text" name="task" id="task" value="{{ request('task') }}" class="w-full border rounded px-3 py-2">
    </div>

    {{-- Submit --}}
    <div>
        <x-brand-button type="submit">🔍 Filter</x-brand-button>
    </div>
</form>

<div class="max-w-6xl mx-auto py-10">


    {{-- Success message --}}
    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Add New Rate --}}
    <div class="mb-6 p-4 border rounded bg-gray-50">
        <h2 class="font-semibold mb-2">➕ Add New Rate</h2>
        <form method="POST" action="{{ route('production-rates.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-sm">Task</label>
                <input type="text" name="task" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-sm">Unit</label>
                <input type="text" name="unit" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-sm">Rate (hrs/unit)</label>
                <input type="number" name="rate" step="0.0001" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-sm">Calculator</label>
                <input type="text" name="calculator" class="form-input w-full">
            </div>
            <div>
                <label class="block text-sm">Note</label>
                <input type="text" name="note" class="form-input w-full">
            </div>
            <div class="col-span-5 text-right mt-4">
                <x-brand-button type="submit">Save New Rate</x-brand-button>
            </div>
        </form>
    </div>

    {{-- Existing Rates Table --}}
    <h2 class="text-xl font-semibold mb-2">📋 Existing Rates</h2>

    <div class="overflow-x-auto">
        <form id="bulkRatesForm" class="relative">
            <table class="min-w-full bg-white border border-gray-200 rounded text-sm">
                <thead class="bg-gray-100 text-left text-sm text-gray-600">
                    <tr>
                        <th class="py-2 px-3 w-8">&nbsp;</th>
                        <th class="py-2 px-3 w-1/4">Task</th>
                        <th class="py-2 px-3 w-20">Unit</th>
                        <th class="py-2 px-3 w-24">Rate</th>
                        <th class="py-2 px-3 w-32">Calculator</th>
                        <th class="py-2 px-3 w-1/3">Note</th>
                        <th class="py-2 px-3 w-24 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($productionRates as $rate)
                        <tr class="border-t" data-rate-id="{{ $rate->id }}">
                            <td class="px-3 py-2 align-middle">
                                <span class="inline-block h-2 w-2 rounded-full bg-amber-500 opacity-0" data-role="dirty-dot"></span>
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="rates[{{ $rate->id }}][task]" value="{{ $rate->task }}" class="form-input w-full text-xs px-2 py-1 truncate">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="rates[{{ $rate->id }}][unit]" value="{{ $rate->unit }}" class="form-input w-full text-xs px-2 py-1">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.0001" name="rates[{{ $rate->id }}][rate]" value="{{ $rate->rate }}" class="form-input w-full text-xs px-2 py-1">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="rates[{{ $rate->id }}][calculator]" value="{{ $rate->calculator }}" class="form-input w-full text-xs px-2 py-1">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="rates[{{ $rate->id }}][note]" value="{{ $rate->note }}" class="form-input w-full text-xs px-2 py-1 whitespace-normal break-words">
                            </td>
                            <td class="px-3 py-2 text-right space-x-2">
                                <button type="button" class="text-red-600 hover:underline text-xs" data-action="delete-rate" data-id="{{ $rate->id }}">Delete</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="sticky bottom-2 left-0 right-0 mt-3 flex items-center justify-between gap-2">
                <div class="text-sm text-gray-600" id="unsavedCounter" aria-live="polite"></div>
                <div class="flex items-center gap-2">
                    <x-brand-button type="button" variant="outline" id="discardChangesBtn">Discard Changes</x-brand-button>
                    <x-brand-button type="button" id="saveAllBtn" disabled>Save All</x-brand-button>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('bulkRatesForm');
        const saveBtn = document.getElementById('saveAllBtn');
        const discardBtn = document.getElementById('discardChangesBtn');
        const counter = document.getElementById('unsavedCounter');

        const initial = new Map();
        form.querySelectorAll('tr[data-rate-id]').forEach(row => {
            const id = row.dataset.rateId;
            initial.set(id, serializeRow(row));
        });

        function serializeRow(row){
            const rateId = row.dataset.rateId;
            const get = (sel) => row.querySelector(sel)?.value ?? '';
            return {
                id: Number(rateId),
                task: get(`input[name="rates[${rateId}][task]"]`),
                unit: get(`input[name="rates[${rateId}][unit]"]`),
                rate: parseFloat(get(`input[name="rates[${rateId}][rate]"]`) || '0'),
                calculator: get(`input[name="rates[${rateId}][calculator]"]`),
                note: get(`input[name="rates[${rateId}][note]"]`),
            };
        }

        function collectChanges(){
            const changes = [];
            form.querySelectorAll('tr[data-rate-id]').forEach(row => {
                const id = row.dataset.rateId;
                const now = serializeRow(row);
                const orig = initial.get(id);
                if (!orig) return;
                const dirty = JSON.stringify(now) !== JSON.stringify(orig);
                const dot = row.querySelector('[data-role="dirty-dot"]');
                if (dot) dot.style.opacity = dirty ? '1' : '0';
                if (dirty) changes.push(now);
            });
            // Update UI
            const count = changes.length;
            saveBtn.disabled = count === 0;
            counter.textContent = count ? `${count} unsaved change${count>1?'s':''}` : '';
            return changes;
        }

        function highlightSaved(ids){
            ids.forEach(id => {
                const row = form.querySelector(`tr[data-rate-id="${id}"]`);
                if (!row) return;
                row.classList.add('bg-green-50');
                const dot = row.querySelector('[data-role="dirty-dot"]');
                if (dot) dot.style.opacity = '0';
                setTimeout(()=> row.classList.remove('bg-green-50'), 1200);
            });
        }

        async function saveAll(){
            const changes = collectChanges();
            if (!changes.length) { showToast && showToast('No changes to save'); return; }
            saveBtn.disabled = true; saveBtn.textContent = 'Saving...';
            try{
                const res = await fetch("{{ route('production-rates.bulkUpdate') }}", {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ rates: changes }),
                });
                if (!res.ok) throw await res.json().catch(()=>({message:'Save failed'}));
                const json = await res.json();
                // Update initial snapshot and highlight changed rows
                const changedIds = [];
                form.querySelectorAll('tr[data-rate-id]').forEach(row => {
                    const id = row.dataset.rateId; const now = serializeRow(row); const orig = initial.get(id);
                    if (JSON.stringify(now) !== JSON.stringify(orig)) { changedIds.push(Number(id)); }
                    initial.set(id, now);
                });
                highlightSaved(changedIds);
                collectChanges();
                showToast && showToast(`Saved ${json.updated} rate(s)`, 'success');
            } catch (e) {
                showToast && showToast('Failed to save changes', 'error');
            } finally {
                saveBtn.disabled = false; saveBtn.textContent = 'Save All';
            }
        }

        function discardChanges(){
            form.querySelectorAll('tr[data-rate-id]').forEach(row => {
                const id = row.dataset.rateId; const orig = initial.get(id); if (!orig) return;
                row.querySelector(`input[name="rates[${id}][task]"]`).value = orig.task;
                row.querySelector(`input[name="rates[${id}][unit]"]`).value = orig.unit;
                row.querySelector(`input[name="rates[${id}][rate]"]`).value = orig.rate;
                row.querySelector(`input[name="rates[${id}][calculator]"]`).value = orig.calculator;
                row.querySelector(`input[name="rates[${id}][note]"]`).value = orig.note;
            });
            showToast && showToast('Changes discarded');
        }

        async function deleteRate(id){
            if (!confirm('Delete this rate?')) return;
            try{
                const res = await fetch(`{{ url('production-rates') }}/${id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                    body: new URLSearchParams({ _method: 'DELETE' }).toString(),
                });
                if (!res.ok) throw new Error('Delete failed');
                const row = form.querySelector(`tr[data-rate-id="${id}"]`);
                if (row) row.remove();
                initial.delete(String(id));
                showToast && showToast('Rate deleted', 'success');
            } catch (e) {
                showToast && showToast('Failed to delete', 'error');
            }
        }

        // Detect changes as user types
        form.addEventListener('input', () => collectChanges());
        form.addEventListener('change', () => collectChanges());

        // Warn before leaving if there are unsaved changes
        window.addEventListener('beforeunload', (e) => {
            const count = collectChanges().length;
            if (count > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        form.addEventListener('click', (e) => {
            const del = e.target.closest('[data-action="delete-rate"]');
            if (del) { e.preventDefault(); deleteRate(del.dataset.id); }
        });
        saveBtn.addEventListener('click', saveAll);
        discardBtn.addEventListener('click', () => { discardChanges(); collectChanges(); });

        // Initialize state
        collectChanges();
    });
    </script>
    @endpush
</div>
@endsection

