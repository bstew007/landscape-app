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
    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-4 sm:p-6 lg:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-4 sm:gap-6">
            <div class="space-y-2 sm:space-y-3 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">CRM</p>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-semibold">Contacts Command Center</h1>
                <p class="text-xs sm:text-sm text-brand-100/85">Search, tag, and action every relationship&mdash;from lead intake to vendor coordination&mdash;without leaving the CRM hub.</p>
            </div>
            <div class="flex flex-wrap gap-2 sm:gap-3 ml-auto w-full sm:w-auto">
                <x-secondary-button as="a" href="{{ route('contacts.qbo.search') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs sm:text-sm flex-1 sm:flex-none justify-center">
                    Import Customers
                </x-secondary-button>
                <x-secondary-button as="a" href="{{ route('contacts.qbo.customer.link-page') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs sm:text-sm flex-1 sm:flex-none justify-center">
                    Link Customers
                </x-secondary-button>
                <x-secondary-button as="a" href="{{ route('contacts.qbo.vendor.link-page') }}" class="bg-white/10 text-white border-white/40 hover:bg-white/20 text-xs sm:text-sm flex-1 sm:flex-none justify-center">
                    Link Vendors
                </x-secondary-button>
                <x-brand-button href="{{ route('contacts.create') }}" variant="muted" class="flex-1 sm:flex-none justify-center">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    New Contact
                </x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4 mt-6 sm:mt-8 text-sm text-brand-100">
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

    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-4 sm:p-5 lg:p-7 space-y-4 sm:space-y-6">
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
                    <x-brand-button type="submit" size="sm" variant="outline">
                        <svg class="h-4 w-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        Find
                    </x-brand-button>
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

            <div class="rounded-2xl border border-brand-100/80 bg-brand-50/40 p-3 flex flex-wrap items-center gap-2" 
                 data-role="bulk-toolbar" 
                 x-data="{ 
                    selectedCount: 0, 
                    selectedIds: [],
                    showTagsModal: false,
                    showArchiveConfirm: false,
                    showDeleteConfirm: false,
                    updateSelection(count, ids) {
                        this.selectedCount = count;
                        this.selectedIds = ids;
                    },
                    clearSelection() {
                        this.selectedCount = 0;
                        this.selectedIds = [];
                        window.clearAllCheckboxes();
                    }
                 }"
                 @selection-changed.window="updateSelection($event.detail.count, $event.detail.ids)">
                <span class="text-xs text-brand-600 font-medium" x-text="selectedCount === 0 ? 'Select contacts below' : selectedCount + ' selected'"></span>
                
                <template x-if="selectedCount > 0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" @click="showTagsModal = true" class="inline-flex items-center gap-1.5 h-8 px-3 rounded-full bg-white border border-brand-300 text-brand-700 text-xs font-medium hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                                <line x1="7" y1="7" x2="7.01" y2="7"/>
                            </svg>
                            Manage Tags
                        </button>
                        
                        <button type="button" @click="showArchiveConfirm = true" class="inline-flex items-center gap-1.5 h-8 px-3 rounded-full bg-white border border-amber-300 text-amber-700 text-xs font-medium hover:bg-amber-50 focus:outline-none focus:ring-2 focus:ring-amber-500">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="21 8 21 21 3 21 3 8"/>
                                <rect x="1" y="3" width="22" height="5"/>
                                <line x1="10" y1="12" x2="14" y2="12"/>
                            </svg>
                            Archive
                        </button>
                        
                        <button type="button" @click="showDeleteConfirm = true" class="inline-flex items-center gap-1.5 h-8 px-3 rounded-full bg-white border border-red-300 text-red-700 text-xs font-medium hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>
                            </svg>
                            Delete
                        </button>
                        
                        <button type="button" @click="clearSelection()" class="text-xs text-brand-500 hover:text-brand-700 ml-2">
                            Clear Selection
                        </button>
                    </div>
                </template>
                
                <div class="ml-auto flex items-center gap-2">
                    <x-brand-button href="{{ route('contacts.create') }}" size="sm">New Contact</x-brand-button>
                    <a href="{{ route('contacts.qbo.search') }}" class="inline-flex items-center rounded-full border border-brand-600 px-4 py-1.5 text-sm font-medium text-brand-700 hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        Import from QBO
                    </a>
                </div>
                
                <!-- Tags Modal -->
                <div x-show="showTagsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showTagsModal = false">
                    <div class="flex min-h-screen items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50 transition-opacity"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6" @click.stop>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Manage Tags for <span x-text="selectedCount"></span> Contact(s)</h3>
                            <form method="POST" action="{{ route('contacts.bulk.tags') }}">
                                @csrf
                                <input type="hidden" name="contact_ids" :value="selectedIds.join(',')">
                                
                                @php $allTags = \App\Models\ContactTag::orderBy('name')->get(); @endphp
                                @if($allTags->count() > 0)
                                    <div class="space-y-3 mb-6">
                                        @foreach($allTags as $tag)
                                            <label class="flex items-center gap-3 p-3 rounded-lg border-2 border-gray-200 hover:border-brand-300 cursor-pointer transition">
                                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="form-checkbox rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border"
                                                      style="background-color: {{ $tag->color }}20; border-color: {{ $tag->color }}; color: {{ $tag->color }};">
                                                    {{ $tag->name }}
                                                </span>
                                                @if($tag->description)
                                                    <span class="text-xs text-gray-500">{{ $tag->description }}</span>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                    
                                    <div class="flex items-center gap-3">
                                        <button type="submit" class="inline-flex items-center justify-center h-10 px-6 rounded-full bg-brand-600 text-white text-sm font-semibold hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                            Apply Tags
                                        </button>
                                        <button type="button" @click="showTagsModal = false" class="inline-flex items-center justify-center h-10 px-6 rounded-full border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                            Cancel
                                        </button>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 mb-4">No tags available. <a href="{{ route('admin.contact-tags.create') }}" class="text-brand-600 hover:text-brand-700 underline">Create tags first</a>.</p>
                                    <button type="button" @click="showTagsModal = false" class="inline-flex items-center justify-center h-10 px-6 rounded-full border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50">
                                        Close
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Archive Confirmation Modal -->
                <div x-show="showArchiveConfirm" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showArchiveConfirm = false">
                    <div class="flex min-h-screen items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50 transition-opacity"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" @click.stop>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Archive Contacts?</h3>
                            <p class="text-sm text-gray-600 mb-6">Archive <span x-text="selectedCount"></span> selected contact(s)? Archived contacts won't appear in normal lists but can be restored later.</p>
                            <form method="POST" action="{{ route('contacts.bulk.archive') }}">
                                @csrf
                                <input type="hidden" name="contact_ids" :value="selectedIds.join(',')">
                                <div class="flex items-center gap-3">
                                    <button type="submit" class="inline-flex items-center justify-center h-10 px-6 rounded-full bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500">
                                        Archive Contacts
                                    </button>
                                    <button type="button" @click="showArchiveConfirm = false" class="inline-flex items-center justify-center h-10 px-6 rounded-full border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Delete Confirmation Modal -->
                <div x-show="showDeleteConfirm" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @click.self="showDeleteConfirm = false">
                    <div class="flex min-h-screen items-center justify-center p-4">
                        <div class="fixed inset-0 bg-black/50 transition-opacity"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" @click.stop>
                            <div class="flex items-start gap-3 mb-4">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Delete Contacts?</h3>
                                    <p class="text-sm text-gray-600 mt-1">Permanently delete <span x-text="selectedCount"></span> selected contact(s)? This action cannot be undone.</p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('contacts.bulk.delete') }}">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="contact_ids" :value="selectedIds.join(',')">
                                <div class="flex items-center gap-3">
                                    <button type="submit" class="inline-flex items-center justify-center h-10 px-6 rounded-full bg-red-600 text-white text-sm font-semibold hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                        Delete Permanently
                                    </button>
                                    <button type="button" @click="showDeleteConfirm = false" class="inline-flex items-center justify-center h-10 px-6 rounded-full border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-t border-brand-100/60">
            @if ($contacts->count())
                {{-- Mobile: Card view (visible on phones) --}}
                <div class="sm:hidden divide-y divide-brand-100">
                    @foreach ($contacts as $contact)
                        @php
                            $contactType = strtolower($contact->contact_type ?? 'client');
                            $typeColors = [
                                'client' => 'bg-accent-50 text-accent-800 border border-accent-200',
                                'lead' => 'bg-amber-50 text-amber-800 border border-amber-200',
                                'vendor' => 'bg-blue-50 text-blue-800 border border-blue-200',
                                'owner' => 'bg-yellow-50 text-yellow-800 border border-yellow-200',
                            ];
                            $pillClass = $typeColors[$contactType] ?? 'bg-brand-50 text-brand-700 border border-brand-200';
                        @endphp
                        <div class="p-4 hover:bg-brand-50/50 transition">
                            <div class="flex items-start gap-3">
                                <input type="checkbox" value="{{ $contact->id }}" data-role="row-check" class="mt-1">
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('contacts.show', $contact) }}" class="font-semibold text-brand-900 hover:text-brand-700 hover:underline block truncate">
                                        {{ collect([$contact->last_name, $contact->first_name])->filter()->join(', ') ?: $contact->company_name ?: 'Unnamed' }}
                                    </a>
                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $pillClass }}">
                                            {{ ucfirst($contactType) }}
                                        </span>
                                        @if($contact->company_name)
                                            <span class="text-xs text-brand-500">{{ $contact->company_name }}</span>
                                        @endif
                                    </div>
                                    @if($contact->email)
                                        <a href="mailto:{{ $contact->email }}" class="text-sm text-brand-700 hover:text-brand-900 hover:underline block mt-2 truncate">{{ $contact->email }}</a>
                                    @endif
                                    @if($contact->mobile || $contact->phone)
                                        <div class="text-sm text-brand-600 mt-1">{{ $contact->mobile ?? $contact->phone }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Tablet/Desktop: Table view (horizontal scroll on tablet) --}}
                <div class="hidden sm:block overflow-x-auto">
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
                                    @if($contact->qbo_customer_id || $contact->qbo_vendor_id)
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

        <div class="px-4 sm:px-5 py-4 border-t border-brand-100/60">
            {{ $contacts->links() }}
        </div>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggleAll = document.querySelector('[data-action="toggle-all"]');
    const rowChecks = document.querySelectorAll('[data-role="row-check"]');
    
    function updateSelection() {
        const checked = Array.from(rowChecks).filter(cb => cb.checked);
        const ids = checked.map(cb => cb.value);
        
        // Dispatch custom event for Alpine to listen to
        window.dispatchEvent(new CustomEvent('selection-changed', {
            detail: {
                count: checked.length,
                ids: ids
            }
        }));
        
        // Update toggle-all checkbox
        if (toggleAll) {
            toggleAll.checked = checked.length === rowChecks.length && checked.length > 0;
            toggleAll.indeterminate = checked.length > 0 && checked.length < rowChecks.length;
        }
        
        // Update row highlighting
        rowChecks.forEach(cb => {
            const row = cb.closest('tr');
            if (row) {
                row.dataset.selected = cb.checked;
            }
        });
    }
    
    // Make clearAllCheckboxes globally available for Alpine
    window.clearAllCheckboxes = function() {
        rowChecks.forEach(cb => cb.checked = false);
        updateSelection();
    };
    
    if (toggleAll) {
        toggleAll.addEventListener('change', (e) => {
            rowChecks.forEach(cb => cb.checked = e.target.checked);
            updateSelection();
        });
    }
    
    rowChecks.forEach(cb => {
        cb.addEventListener('change', updateSelection);
    });
    
    // Initial update
    updateSelection();
});
</script>
@endpush
@endsection
