@extends('layouts.sidebar')

@section('content')
@php
    $pageEstimates = collect($estimates->items());
    $pageCount = $pageEstimates->count();
    $pageValue = $pageEstimates->sum(function ($estimate) {
        return (float) ($estimate->grand_total > 0 ? $estimate->grand_total : $estimate->total);
    });
    $approvedValue = $pageEstimates->where('status', 'approved')->sum(function ($estimate) {
        return (float) ($estimate->grand_total > 0 ? $estimate->grand_total : $estimate->total);
    });
    $outstandingCount = $pageEstimates->whereIn('status', ['pending', 'sent'])->count();
    $statusParam = request('status');
    $clientIdParam = request('client_id');
    $clientNameParam = optional(($clients ?? collect())->firstWhere('id', $clientIdParam))->name;
@endphp

<div class="space-y-8">
    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-4 sm:p-6 lg:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-2 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Estimates</p>
                <h1 class="text-2xl sm:text-3xl font-semibold">Pipeline + Pricing Workspace</h1>
                <p class="text-sm text-brand-100/90">Track drafts, nurture approvals, and move proposals from follow-up to signature without leaving the hub.</p>
            </div>
            <div class="flex flex-wrap gap-3 ml-auto">
                <x-secondary-button as="a" href="{{ route('calculator.templates.gallery') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20">
                    Template Gallery
                </x-secondary-button>
                <x-brand-button href="{{ route('estimates.create') }}" variant="muted">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    New Estimate
                </x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">On This Page</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($pageCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Page Volume</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ $pageValue ? '$' . number_format($pageValue, 0) : 'N/A' }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Approved Value</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ $approvedValue ? '$' . number_format($approvedValue, 0) : 'N/A' }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Awaiting Decision</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($outstandingCount) }}</dd>
            </div>
        </dl>
    </section>

    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-4 sm:p-5 lg:p-7 space-y-4 sm:space-y-5">
            {{-- Status changed to individual dropdowns on each row --}}

            {{-- Quick filters --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:pb-0">
                <span class="text-xs uppercase tracking-wide text-brand-400 flex-shrink-0">Quick Filter</span>
                @foreach (\App\Models\Estimate::STATUSES as $option)
                    @php $isActive = $statusParam === $option; @endphp
                    <a href="{{ request()->fullUrlWithQuery(['status' => $isActive ? null : $option, 'page' => null]) }}"
                       class="px-3 py-1.5 rounded-full text-xs font-semibold border transition {{ $isActive ? 'bg-brand-700 text-white border-brand-600 shadow-lg shadow-brand-700/30' : 'bg-white text-brand-700 border-brand-200 hover:border-brand-400 hover:bg-brand-50' }}">
                        {{ ucfirst($option) }}
                    </a>
                @endforeach
                <a href="{{ route('estimates.index') }}" class="text-xs text-brand-500 hover:text-brand-700 ml-auto">Reset filters</a>
            </div>

            @if($statusParam || $clientIdParam)
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-brand-400 uppercase tracking-wide">Active Filters</span>
                    @if($statusParam)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-brand-50 text-brand-900 border border-brand-200">
                            Status: {{ ucfirst($statusParam) }}
                            <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}" class="text-brand-600 hover:text-brand-900" aria-label="Remove status filter">&times;</a>
                        </span>
                    @endif
                    @if($clientIdParam)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-brand-50 text-brand-900 border border-brand-200">
                            Client: {{ $clientNameParam ?? $clientIdParam }}
                            <a href="{{ request()->fullUrlWithQuery(['client_id' => null, 'page' => null]) }}" class="text-brand-600 hover:text-brand-900" aria-label="Remove client filter">&times;</a>
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <div class="border-t border-brand-100/60">
            {{-- Mobile card view --}}
            <div class="md:hidden divide-y divide-brand-100">
                @foreach ($estimates as $estimate)
                    <div class="p-4 hover:bg-brand-50/50 transition">
                        <div class="flex items-start gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-brand-900 truncate">{{ $estimate->title }}</p>
                                <p class="text-sm text-brand-600 mt-1">{{ optional($estimate->client)->name ?? 'Unknown' }}</p>
                                @php
                                    $statusClass = match($estimate->status) {
                                        'draft' => 'bg-gray-100 text-gray-700 border-gray-200',
                                        'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                        'sent' => 'bg-brand-50 text-brand-700 border-brand-200',
                                        'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                        'rejected' => 'bg-red-100 text-red-700 border-red-200',
                                        default => 'bg-gray-100 text-gray-700 border-gray-200',
                                    };
                                    $displayTotal = $estimate->grand_total > 0 ? $estimate->grand_total : $estimate->total;
                                @endphp
                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                    <form method="POST" action="{{ route('estimates.update', $estimate) }}" class="inline-block" x-data="{ updating: false }">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" 
                                                @change="updating = true; $el.closest('form').submit()"
                                                :disabled="updating"
                                                class="rounded-full px-2 py-0.5 text-xs font-semibold border focus:ring-2 focus:ring-brand-500 focus:outline-none {{ $statusClass }}">
                                            @foreach(['draft', 'pending', 'sent', 'approved', 'rejected'] as $statusOption)
                                                <option value="{{ $statusOption }}" {{ $estimate->status === $statusOption ? 'selected' : '' }}>
                                                    {{ ucfirst($statusOption) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                    <span class="text-sm font-semibold text-brand-900">
                                        {{ $displayTotal !== null ? '$' . number_format($displayTotal, 2) : 'N/A' }}
                                    </span>
                                </div>
                                <x-brand-button href="{{ route('estimates.show', $estimate) }}" variant="outline" size="sm" class="mt-3 w-full justify-center">
                                    Open
                                </x-brand-button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Desktop table view --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-brand-50/80 text-left text-[11px] uppercase tracking-wide text-brand-500">
                    <tr>
                        <th class="px-4 py-3">Estimate</th>
                        <th class="px-4 py-3">Client / Property</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3">Expires</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                    @foreach ($estimates as $estimate)
                        <tr class="transition hover:bg-brand-50/70">
                            <td class="px-4 py-3 align-top">
                                <p class="font-semibold text-brand-900">{{ $estimate->title }}</p>
                                <p class="text-xs text-brand-400">Created {{ $estimate->created_at->format('M j, Y') }}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <p class="text-sm text-brand-700 hover:text-brand-900 hover:underline cursor-pointer" data-filter-key="client_id" data-filter-value="{{ $estimate->client_id }}">{{ optional($estimate->client)->name ?? 'Unknown client' }}</p>
                                <p class="text-xs text-brand-400">{{ optional($estimate->property)->name ?? 'No property' }}</p>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <form method="POST" action="{{ route('estimates.update', $estimate) }}" class="inline-block" x-data="{ updating: false }">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" 
                                            @change="updating = true; $el.closest('form').submit()"
                                            :disabled="updating"
                                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold border focus:ring-2 focus:ring-brand-500 focus:outline-none
                                                   {{ match($estimate->status) {
                                                       'draft' => 'bg-gray-100 text-gray-700 border-gray-200',
                                                       'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                       'sent' => 'bg-brand-50 text-brand-700 border-brand-200',
                                                       'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                       'rejected' => 'bg-red-100 text-red-700 border-red-200',
                                                       default => 'bg-gray-100 text-gray-700 border-gray-200',
                                                   } }}">
                                        @foreach(['draft', 'pending', 'sent', 'approved', 'rejected'] as $statusOption)
                                            <option value="{{ $statusOption }}" {{ $estimate->status === $statusOption ? 'selected' : '' }}>
                                                {{ ucfirst($statusOption) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-3 align-top">
                                @if ($estimate->email_last_sent_at)
                                    <div class="text-xs font-semibold text-emerald-700">
                                        Sent {{ $estimate->email_last_sent_at->format('M j, Y') }}
                                    </div>
                                    <div class="text-[11px] text-brand-400">
                                        {{ $estimate->email_send_count }} {{ \Illuminate\Support\Str::plural('time', $estimate->email_send_count) }}
                                    </div>
                                @else
                                    <span class="text-xs text-brand-300">Not sent</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top text-right font-semibold text-brand-900">
                                @php $displayTotal = $estimate->grand_total > 0 ? $estimate->grand_total : $estimate->total; @endphp
                                {{ $displayTotal !== null ? '$' . number_format($displayTotal, 2) : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 align-top text-sm text-brand-600">
                                {{ optional($estimate->expires_at)->format('M j, Y') ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 align-top text-right">
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
        </div>

        <div class="px-5 py-4 border-t border-brand-100/60">
            {{ $estimates->links() }}
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function(){
  function initFilter(){
    var tbody = document.getElementById('estimateTbody');
    if (!tbody) return;
    tbody.addEventListener('click', function(e){
      var el = e.target;
      while (el && el !== tbody && (el.nodeType !== 1 || !el.hasAttribute('data-filter-key'))) {
        el = el.parentNode;
      }
      if (!el || el === tbody) return;
      var key = el.getAttribute('data-filter-key');
      var val = el.getAttribute('data-filter-value') || '';
      if (!key) return;
      try {
        var url = new URL(window.location.href);
        url.searchParams.set(key, val);
        url.searchParams.delete('page');
        window.location.href = url.toString();
      } catch (err) {
        var qs = window.location.search.replace(/^\?/, '');
        var params = qs ? qs.split('&') : [];
        var found = false;
        for (var i=0; i<params.length; i++){
          var pair = params[i].split('=');
          var k = pair[0];
          if (k === 'page') { params.splice(i,1); i--; continue; }
          if (k === key) { params[i] = key + '=' + encodeURIComponent(val); found = true; }
        }
        if (!found) params.push(key + '=' + encodeURIComponent(val));
        var newQs = params.length ? ('?' + params.join('&')) : '';
        window.location.href = window.location.pathname + newQs;
      }
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFilter);
  } else {
    initFilter();
  }
})();
</script>
@endpush
