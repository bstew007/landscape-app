@extends('layouts.sidebar')

@section('content')
@php
    $pageContacts = collect($contacts->items());
    $pageCount = $pageContacts->count();
    $leadCount = $pageContacts->filter(fn($c) => strtolower($c->contact_type ?? '') === 'lead')->count();
    $clientCount = $pageContacts->filter(fn($c) => strtolower($c->contact_type ?? '') === 'client')->count();
    $vendorCount = $pageContacts->filter(fn($c) => strtolower($c->contact_type ?? '') === 'vendor')->count();
    $types = ['all','lead','client','vendor','owner'];
@endphp

<div class="space-y-8">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">CRM</p>
                <h1 class="text-3xl sm:text-4xl font-semibold">Contacts Command Center</h1>
                <p class="text-sm text-brand-100/85">Search, tag, and action every relationship&mdash;from lead intake to vendor coordination&mdash;without leaving the CRM hub.</p>
            </div>
            <div class="flex flex-wrap gap-3 ml-auto">
                <x-secondary-button as="a" href="{{ route('contacts.qbo.search') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20">
                    Import From QBO
                </x-secondary-button>
                <x-brand-button href="{{ route('contacts.create') }}" variant="muted">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    New Contact
                </x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">On This Page</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($pageCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Leads</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($leadCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Clients</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($clientCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Vendors</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($vendorCount) }}</dd>
            </div>
        </dl>
    </section>

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-5 sm:p-7 space-y-6">
            <form method="GET" action="{{ route('contacts.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="type" value="{{ $type ?? 'all' }}">
                <div class="flex items-center gap-3 flex-1 min-w-[240px]">
                    <span class="text-xs uppercase tracking-wide text-brand-400">Search</span>
                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Name, company, phone, or email"
                           class="flex-1 rounded-full border border-brand-200 bg-white px-4 py-2.5 text-sm focus:ring-brand-500 focus:border-brand-500">
                </div>
                <div class="flex items-center gap-2">
                    @if(!empty($search))
                        <x-secondary-button as="a" href="{{ route('contacts.index', array_filter(['type' => ($type ?? 'all') !== 'all' ? $type : null], fn($v) => $v !== null && $v !== '')) }}" size="sm">Clear</x-secondary-button>
                    @endif
                    <x-brand-button type="submit" size="sm">Find</x-brand-button>
                </div>
            </form>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="text-brand-400 uppercase tracking-wide">Type</span>
                @foreach ($types as $t)
                    @php $isActive = ($type ?? 'all') === $t; @endphp
                    <a href="{{ route('contacts.index', array_filter(['type' => $t, 'search' => $search], fn($v) => $v !== null && $v !== '')) }}"
                       class="px-3 py-1.5 rounded-full border text-xs font-semibold transition {{ $isActive ? 'bg-brand-700 text-white border-brand-600 shadow-lg shadow-brand-700/30' : 'bg-white text-brand-700 border-brand-200 hover:border-brand-400 hover:bg-brand-50' }}">
                        {{ ucfirst($t) }}
                    </a>
                @endforeach
                @if(!empty($search) || ($type ?? 'all') !== 'all')
                    <a href="{{ route('contacts.index') }}" class="text-brand-500 hover:text-brand-700 ml-auto">Reset filters</a>
                @endif
            </div>

            @if (session('success'))
                <div class="p-4 bg-accent-50 text-accent-900 rounded-2xl border border-accent-200 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="rounded-2xl border border-brand-100/80 bg-brand-50/40 p-3 flex flex-wrap items-center gap-2" data-role="bulk-toolbar">
                <x-secondary-button data-action="bulk-view" size="sm" disabled>
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    View
                </x-secondary-button>
                <x-brand-button variant="outline" data-action="bulk-edit" size="sm" disabled>
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4Z"/></svg>
                    Edit
                </x-brand-button>
                <x-danger-button data-action="bulk-delete" size="sm" disabled>
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                    Delete
                </x-danger-button>
                <div class="ml-auto flex items-center gap-2">
                    <x-brand-button href="{{ route('contacts.create') }}" size="sm">New Contact</x-brand-button>
                    <a href="{{ route('contacts.qbo.search') }}" class="inline-flex items-center rounded-full border border-brand-600 px-4 py-1.5 text-sm font-medium text-brand-700 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        Import from QBO
                    </a>
                </div>
            </div>
        </div>

        <div class="border-t border-brand-100/60">
            @if ($contacts->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-brand-50/80 text-left text-[11px] uppercase tracking-wide text-brand-500">
                        <tr>
                            <th class="px-4 py-3"><input type="checkbox" data-action="toggle-all"></th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Company</th>
                            <th class="px-4 py-3">Address</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Mobile / Alt</th>
                            <th class="px-4 py-3">Phone</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">QBO</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-brand-50 text-brand-900 text-sm">
                        @foreach ($contacts as $contact)
                            @php
                                $cityState = collect([$contact->city, $contact->state])->filter()->join(', ');
                                $contactType = strtolower($contact->contact_type ?? 'client');
                                $typeColors = [
                                    'client' => 'bg-accent-50 text-accent-800 border border-accent-200',
                                    'lead' => 'bg-amber-50 text-amber-800 border border-amber-200',
                                    'vendor' => 'bg-blue-50 text-blue-800 border border-blue-200',
                                    'owner' => 'bg-yellow-50 text-yellow-800 border border-yellow-200',
                                ];
                                $pillClass = $typeColors[$contactType] ?? 'bg-brand-50 text-brand-700 border border-brand-200';
                            @endphp
                            <tr class="transition hover:bg-brand-50/70 data-[selected=true]:bg-brand-50">
                                <td class="px-4 py-3 align-top"><input type="checkbox" value="{{ $contact->id }}" data-role="row-check"></td>
                                <td class="px-4 py-3 align-top whitespace-nowrap">
                                    <a href="{{ route('contacts.show', $contact) }}" class="font-semibold text-brand-900 hover:text-brand-700 hover:underline">
                                        {{ collect([$contact->last_name, $contact->first_name])->filter()->join(', ') ?: $contact->company_name ?: 'Unnamed' }}
                                    </a>
                                    <p class="text-xs text-brand-400">Updated {{ $contact->updated_at?->format('M j, Y') ?? 'N/A' }}</p>
                                </td>
                                <td class="px-4 py-3 align-top">{{ $contact->company_name ?: 'N/A' }}</td>
                                <td class="px-4 py-3 align-top">
                                    <div>{{ $contact->address ?: 'N/A' }}</div>
                                    @if($cityState)
                                        <div class="text-xs text-brand-400">{{ $cityState }}</div>
                                    @elseif($contact->postal_code)
                                        <div class="text-xs text-brand-400">{{ $contact->postal_code }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-top">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $pillClass }}">
                                        {{ ucfirst($contactType) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 align-top">
                                    {{ $contact->mobile ?? 'N/A' }}
                                    @if($contact->phone2)
                                        <div class="text-xs text-brand-400">Alt: {{ $contact->phone2 }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-top">
                                    {{ $contact->phone ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 align-top">
                                    @if($contact->email)
                                        <a href="mailto:{{ $contact->email }}" class="text-brand-700 hover:text-brand-900 hover:underline">{{ $contact->email }}</a>
                                    @else
                                        <span class="text-brand-300">N/A</span>
                                    @endif
                                    @if($contact->email2)
                                        <div class="text-xs">
                                            <a href="mailto:{{ $contact->email2 }}" class="text-brand-500 hover:text-brand-700 hover:underline">{{ $contact->email2 }}</a>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-top">
                                    @if($contact->qbo_customer_id)
                                        @php $needsSync = $contact->qbo_last_synced_at && $contact->updated_at && $contact->updated_at->gt($contact->qbo_last_synced_at); @endphp
                                        @if($needsSync)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs">Needs Sync</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs">Synced</span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200 text-xs">Not Linked</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-brand-500 text-sm">No contacts yet. Use "New Contact" to get started.</div>
            @endif
        </div>

        <div class="px-5 py-4 border-t border-brand-100/60">
            {{ $contacts->links() }}
        </div>
    </section>
</div>
@endsection
