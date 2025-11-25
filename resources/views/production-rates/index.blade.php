@extends('layouts.sidebar')

@section('content')
@php
    $totalRates = $productionRates->count();
    $uniqueTasks = $productionRates->pluck('task')->unique()->count();
    $uniqueCalculators = $calculators->count();
    $avgRate = round(optional($productionRates)->avg('rate') ?? 0, 3);
@endphp

<div class="space-y-8 max-w-7xl mx-auto p-4">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-3 max-w-3xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Admin · Pricing</p>
                <h1 class="text-3xl sm:text-4xl font-semibold">Production Rates</h1>
                <p class="text-sm text-brand-100/85">Tune calculator speeds and labor assumptions so every estimate uses the same playbook.</p>
            </div>
            <div class="flex flex-wrap gap-3 ml-auto">
                <x-secondary-button as="a" href="{{ route('materials.index') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20">Materials Catalog</x-secondary-button>
                <x-brand-button as="a" href="{{ route('admin.budgets.index') }}" variant="muted">Company Budget</x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Rates Loaded</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($totalRates) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Unique Tasks</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($uniqueTasks) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Calculators</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($uniqueCalculators) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Avg Rate (hrs/unit)</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($avgRate, 3) }}</dd>
            </div>
        </dl>
    </section>

    @if (session('success'))
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 text-sm shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-5 sm:p-7">
            <form method="GET" action="{{ route('production-rates.index') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Calculator</label>
                    <select name="calculator" class="w-full rounded-2xl border border-brand-200 bg-white px-3 py-2.5 text-sm focus:ring-brand-500 focus:border-brand-500">
                        <option value="">All</option>
                        @foreach ($calculators as $calc)
                            <option value="{{ $calc }}" @selected(request('calculator') === $calc)>{{ ucfirst($calc) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Task</label>
                    <input type="text" name="task" value="{{ request('task') }}" placeholder="e.g., bed edging" class="w-full rounded-2xl border border-brand-200 bg-white px-3 py-2.5 text-sm focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div class="sm:col-span-2 lg:col-span-3 flex flex-wrap items-center gap-3 pt-1">
                    <button type="submit" class="inline-flex items-center gap-2 h-11 px-6 rounded-2xl bg-brand-900 text-white text-sm font-semibold shadow-[0_10px_30px_rgba(16,37,61,0.25)] hover:bg-brand-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 transition">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        Apply Filters
                    </button>
                    @if(request()->filled('calculator') || request()->filled('task'))
                        <x-secondary-button as="a" href="{{ route('production-rates.index') }}" class="h-12 px-5 text-sm">Reset</x-secondary-button>
                    @endif
                </div>
            </form>
        </div>
    </section>

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-5 sm:p-7 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-brand-900">Add Production Rate</h2>
                <p class="text-sm text-brand-500">Seed new calculator tasks instantly</p>
            </div>
            <form method="POST" action="{{ route('production-rates.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Task</label>
                    <input type="text" name="task" class="form-input w-full rounded-2xl" required>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Unit</label>
                    <input type="text" name="unit" class="form-input w-full rounded-2xl" required>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Rate (hrs/unit)</label>
                    <input type="number" name="rate" step="0.0001" class="form-input w-full rounded-2xl" required>
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Calculator</label>
                    <input type="text" name="calculator" class="form-input w-full rounded-2xl">
                </div>
                <div>
                    <label class="block text-xs uppercase tracking-wide text-brand-400 mb-1.5 font-medium">Note</label>
                    <input type="text" name="note" class="form-input w-full rounded-2xl">
                </div>
                <div class="md:col-span-5 text-right">
                    <button type="submit" class="inline-flex items-center justify-center gap-2 h-11 px-6 rounded-2xl bg-brand-900 text-white text-sm font-semibold shadow-[0_10px_30px_rgba(16,37,61,0.25)] hover:bg-brand-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500 transition">
                        Save Rate
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-5 sm:p-7 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-brand-900">Existing Rates</h2>
                <p class="text-sm text-brand-500">Inline edits auto-track unsaved changes</p>
            </div>
            <div class="overflow-x-auto">
                <form id="bulkRatesForm" class="relative">
                    <table class="min-w-full bg-white border border-brand-100 rounded-2xl text-sm">
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
                                        <button
                                            type="button"
                                            class="inline-flex items-center justify-center h-8 w-8 rounded-full border border-red-200 text-red-600 hover:text-red-800 hover:border-red-400 hover:bg-red-50 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-500"
                                            data-action="delete-rate"
                                            data-id="{{ $rate->id }}"
                                            aria-label="Delete rate">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                        </button>
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
    </section>
</div>
@endsection

