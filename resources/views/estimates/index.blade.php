@extends('layouts.sidebar')

@section('content')
<div class="max-w-6xl mx-auto py-6 space-y-6">
    <x-page-header title="Estimates" eyebrow="Sales" subtitle="Draft, send, and track pricing packages.">
        <x-slot:actions>
            <x-brand-button href="{{ route('estimates.create') }}">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                New Estimate
            </x-brand-button>
        </x-slot:actions>
    </x-page-header>

    <div class="mt-6 bg-white rounded-lg shadow p-3 flex flex-wrap items-center gap-2" data-role="bulk-toolbar">
        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Actions</label>
            <select id="bulkAction" class="form-select text-sm border-brand-300 focus:ring-brand-500 focus:border-brand-500">
                <option value="">Choose...</option>
                <optgroup label="Update status">
                    @foreach (\App\Models\Estimate::STATUSES as $option)
                        <option value="status:{{ $option }}">Set to {{ ucfirst($option) }}</option>
                    @endforeach
                </optgroup>
                <option value="send_reminders">Send reminders</option>
                <option value="lock">Lock estimate</option>
                <option value="archive">Archive</option>
            </select>
            <x-brand-button id="applyBulk" size="sm" disabled>Apply</x-brand-button>
            <span class="mx-2 text-gray-300">|</span>
            <button type="button" class="text-xs text-gray-600 hover:underline" data-action="select-page">Select page</button>
            <button type="button" class="text-xs text-gray-600 hover:underline" data-action="clear-selection">Clear selection</button>
        </div>
        <span class="ml-auto inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-brand-100 text-brand-800 hidden" data-role="selected-count">0 selected</span>
    </div>

    @php
        $statusParam = request('status');
        $clientIdParam = request('client_id');
        $clientNameParam = optional(($clients ?? collect())->firstWhere('id', $clientIdParam))->name;
    @endphp
    @if($statusParam || $clientIdParam)
        <div class="bg-white rounded-lg shadow p-2 flex flex-wrap items-center gap-2">
            <span class="text-xs text-gray-600">Filters:</span>
            @if($statusParam)
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs bg-brand-50 text-brand-800 border border-brand-200">Status: {{ ucfirst($statusParam) }}
                    <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}" class="ml-1 text-brand-700 hover:underline" aria-label="Remove status filter">✕</a>
                </span>
            @endif
            @if($clientIdParam)
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs bg-brand-50 text-brand-800 border border-brand-200">Client: {{ $clientNameParam ?? $clientIdParam }}
                    <a href="{{ request()->fullUrlWithQuery(['client_id' => null, 'page' => null]) }}" class="ml-1 text-brand-700 hover:underline" aria-label="Remove client filter">✕</a>
                </span>
            @endif
            <a href="{{ route('estimates.index') }}" class="ml-auto text-xs text-gray-600 hover:underline">Clear all</a>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3"><input type="checkbox" data-action="toggle-all"></th>
                <th class="px-4 py-3">Estimate</th>
                <th class="px-4 py-3">Client / Property</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3">Expires</th>
                <th class="px-4 py-3"></th>
            </tr>
            </thead>
            <tbody class="divide-y" id="estimateTbody" data-update-base="{{ url('estimates') }}" data-email-suffix="/email">
            @foreach ($estimates as $estimate)
                <tr class="hover:bg-brand-50/50" data-id="{{ $estimate->id }}" data-status="{{ $estimate->status }}" data-client-id="{{ $estimate->client_id }}" data-update-url="{{ route('estimates.update', $estimate) }}" data-email-url="{{ route('estimates.email', $estimate) }}">
                    <td class="px-4 py-3"><input type="checkbox" data-role="row-check" value="{{ $estimate->id }}"></td>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-gray-900">{{ $estimate->title }}</p>
                        <p class="text-xs text-gray-500">Created {{ $estimate->created_at->format('M j, Y') }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm text-brand-700 hover:text-brand-900 hover:underline cursor-pointer" data-filter-key="client_id" data-filter-value="{{ $estimate->client_id }}">{{ optional($estimate->client)->name ?? 'Unknown client' }}</p>
                        <p class="text-xs text-gray-500">{{ optional($estimate->property)->name ?? 'No property' }}</p>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $statusClass = match($estimate->status) {
                                'draft' => 'bg-gray-100 text-gray-700 border-gray-200',
                                'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                'sent' => 'bg-brand-100 text-brand-700 border-brand-200',
                                'approved' => 'bg-green-100 text-green-700 border-green-200',
                                'rejected' => 'bg-red-100 text-red-700 border-red-200',
                                default => 'bg-gray-100 text-gray-700 border-gray-200',
                            };
                        @endphp
                        <button type="button"
                                class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold border hover:ring-2 hover:ring-brand-300 {{ $statusClass }}"
                                data-filter-key="status" data-filter-value="{{ $estimate->status }}">
                            {{ ucfirst($estimate->status) }}
                        </button>
                    </td>
                    <td class="px-4 py-3">
                        @if ($estimate->email_last_sent_at)
                            <div class="text-xs font-semibold text-green-700">
                                Sent {{ $estimate->email_last_sent_at->format('M j, Y') }}
                            </div>
                            <div class="text-[11px] text-gray-500">
                                {{ $estimate->email_send_count }} {{ \Illuminate\Support\Str::plural('time', $estimate->email_send_count) }}
                            </div>
                        @else
                            <span class="text-xs text-gray-400">Not sent</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                        {{ $estimate->total ? '$' . number_format($estimate->total, 2) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ optional($estimate->expires_at)->format('M j, Y') ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <x-brand-button href="{{ route('estimates.show', $estimate) }}" variant="outline" size="sm">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            Open
                        </x-brand-button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @php $totalCount = method_exists($estimates, 'total') ? $estimates->total() : null; $pageCount = $estimates->count(); @endphp
    <div id="selectAllBanner" class="hidden mt-2 bg-brand-50 border border-brand-200 rounded px-3 py-2 text-xs text-brand-900 flex items-center gap-2">
        <span>All {{ $pageCount }} estimates on this page are selected.</span>
        @if($totalCount && $totalCount > $pageCount)
            <span class="text-gray-600">Selection persists across pages. Navigate pages to select more (total {{ $totalCount }}).</span>
        @endif
        <button type="button" class="text-brand-700 hover:underline" data-action="clear-page-selection">Clear</button>
    </div>

    <div>
        {{ $estimates->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const storageKey = 'selected_estimate_ids';
    const selectedIds = new Set(JSON.parse(localStorage.getItem(storageKey) || '[]'));
    const tbody = document.getElementById('estimateTbody');
    const toggleAll = document.querySelector('[data-action="toggle-all"]');
    const applyBtn = document.getElementById('applyBulk');
    const actionSelect = document.getElementById('bulkAction');
    const selectedBadge = document.querySelector('[data-role="selected-count"]');

    function checks() { return Array.from(document.querySelectorAll('[data-role="row-check"]')); }
    function selectedRows() { return checks().filter(c => c.checked).map(c => c.closest('tr')); }
    function syncCheckboxesFromStorage() {
      checks().forEach(cb => {
        const id = cb.value;
        cb.checked = selectedIds.has(id);
      });
      updateToolbar();
      updateSelectAllBanner();
    }
    function persist() { localStorage.setItem(storageKey, JSON.stringify(Array.from(selectedIds))); }

    function updateToolbar() {
      const count = selectedIds.size;
      if (applyBtn) applyBtn.disabled = !(count && actionSelect && actionSelect.value);
      if (selectedBadge) {
        if (count) {
          selectedBadge.classList.remove('hidden');
          selectedBadge.textContent = `${count} selected`;
        } else selectedBadge.classList.add('hidden');
      }
    }

    const banner = document.getElementById('selectAllBanner');
    function updateSelectAllBanner(){
      if (!banner) return;
      const pageIds = checks().map(cb => cb.value);
      const allOnPageSelected = pageIds.length > 0 && pageIds.every(id => selectedIds.has(id));
      banner.classList.toggle('hidden', !allOnPageSelected);
    }

    if (toggleAll) {
      toggleAll.addEventListener('change', () => {
        checks().forEach(c => {
          c.checked = toggleAll.checked;
          if (c.checked) selectedIds.add(c.value); else selectedIds.delete(c.value);
        });
        persist();
        updateToolbar();
        updateSelectAllBanner();
      });
    }
    checks().forEach(c => c.addEventListener('change', () => {
      if (c.checked) selectedIds.add(c.value); else selectedIds.delete(c.value);
      persist();
      updateToolbar();
      updateSelectAllBanner();
    }));
    if (actionSelect) actionSelect.addEventListener('change', updateToolbar);

    // Click-to-filter chips
    if (tbody) {
      tbody.addEventListener('click', (e) => {
        const t = e.target.closest('[data-filter-key]');
        if (!t) return;
        const key = t.getAttribute('data-filter-key');
        const val = t.getAttribute('data-filter-value');
        if (!key) return;
        const url = new URL(window.location.href);
        url.searchParams.set(key, val || '');
        url.searchParams.delete('page');
        window.location.href = url.toString();
      });
    }

    // Quick-select helpers
    document.querySelector('[data-action="select-page"]')?.addEventListener('click', () => {
      checks().forEach(c => { c.checked = true; selectedIds.add(c.value); });
      persist(); updateToolbar(); updateSelectAllBanner();
    });
    document.querySelector('[data-action="clear-selection"]')?.addEventListener('click', () => {
      selectedIds.clear(); persist(); syncCheckboxesFromStorage();
    });
    document.querySelector('[data-action="clear-page-selection"]')?.addEventListener('click', () => {
      checks().forEach(c => { c.checked = false; selectedIds.delete(c.value); });
      persist(); updateToolbar(); updateSelectAllBanner();
    });

    // Shift-click range selection
    let lastClickedIndex = null;
    tbody?.addEventListener('click', (e) => {
      const cb = e.target.closest('input[type="checkbox"][data-role="row-check"]');
      if (!cb) return;
      const list = checks();
      const idx = list.indexOf(cb);
      if (e.shiftKey && lastClickedIndex !== null) {
        const [start, end] = idx > lastClickedIndex ? [lastClickedIndex, idx] : [idx, lastClickedIndex];
        for (let i = start; i <= end; i++) {
          list[i].checked = cb.checked;
          if (list[i].checked) selectedIds.add(list[i].value); else selectedIds.delete(list[i].value);
        }
        persist(); updateToolbar(); updateSelectAllBanner();
      }
      lastClickedIndex = idx;
    });

    async function runBulkAction() {
      const ids = Array.from(selectedIds);
      if (!ids.length) return;
      const val = actionSelect.value || '';
      if (!val) return;
      const [action, arg] = val.split(':');

      try {
        applyBtn.disabled = true;
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        if (action === 'status') {
          // PATCH each estimate to new status
          for (const id of ids) {
            const url = `${document.getElementById('estimateTbody').dataset.updateBase}/${id}`;
            await fetch(url, {
              method: 'PATCH',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
              body: JSON.stringify({ status: arg })
            });
          }
          showToast('Status updated', 'success');
        } else if (action === 'send_reminders') {
          const suffix = document.getElementById('estimateTbody').dataset.emailSuffix;
          for (const id of ids) {
            const url = `${document.getElementById('estimateTbody').dataset.updateBase}/${id}${suffix}`;
            await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf } });
          }
          showToast('Reminders sent', 'success');
        } else if (action === 'lock') {
          for (const id of ids) {
            const url = `${document.getElementById('estimateTbody').dataset.updateBase}/${id}`;
            await fetch(url, {
              method: 'PATCH',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
              body: JSON.stringify({ locked: true })
            });
          }
          showToast('Estimates locked', 'success');
        } else if (action === 'archive') {
          for (const id of ids) {
            const url = `${document.getElementById('estimateTbody').dataset.updateBase}/${id}`;
            await fetch(url, {
              method: 'PATCH',
              headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
              body: JSON.stringify({ archived: true })
            });
          }
          showToast('Estimates archived', 'success');
        }
      } catch (e) {
        showToast('Bulk action failed', 'error');
      } finally {
        window.location.reload();
      }
    }

    if (applyBtn) applyBtn.addEventListener('click', runBulkAction);

    // Initialize checkboxes from storage on page load
    syncCheckboxesFromStorage();
  });
</script>
@endpush
