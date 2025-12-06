@extends('layouts.sidebar')

@section('content')
@php
    $pageCount = $tags->count();
@endphp

<div class="space-y-8">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-2 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Admin / Settings</p>
                <h1 class="text-2xl sm:text-3xl font-semibold">Contact Tags</h1>
                <p class="text-sm text-brand-100/90">Organize and categorize your contacts with tags for better filtering and management.</p>
            </div>
            <div class="flex flex-wrap gap-3 ml-auto">
                <x-brand-button href="{{ route('admin.contact-tags.create') }}" variant="muted">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Tag
                </x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">On This Page</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($pageCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Total Tags</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($tags->total()) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Tagged Contacts</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($tags->sum('contacts_count')) }}</dd>
            </div>
        </dl>
    </section>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3">{{ session('error') }}</div>
    @endif

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-5 sm:p-7 space-y-5">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Search tags..."
                       class="flex-1 min-w-[200px] rounded-full border-brand-200 bg-white text-sm px-4 py-2 focus:ring-brand-500 focus:border-brand-500">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 h-9 px-4 rounded-full bg-brand-600 text-white text-sm font-semibold shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1 transition disabled:opacity-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    Search
                </button>
                @if($search ?? false)
                    <a href="{{ route('admin.contact-tags.index') }}" class="text-xs text-brand-500 hover:text-brand-700">Clear</a>
                @endif
            </form>
        </div>

        <div class="border-t border-brand-100/60">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-brand-50/80 text-left text-[11px] uppercase tracking-wide text-brand-500">
                    <tr>
                        <th class="px-4 py-3">Tag</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3 text-center">Contacts</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                    @forelse ($tags as $tag)
                        <tr class="transition hover:bg-brand-50/70">
                            <td class="px-4 py-3 align-top">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border"
                                          style="background-color: {{ $tag->color }}20; border-color: {{ $tag->color }}; color: {{ $tag->color }};">
                                        {{ $tag->name }}
                                    </span>
                                </div>
                                <p class="text-xs text-brand-400 mt-1">Created {{ $tag->created_at->format('M j, Y') }}</p>
                            </td>
                            <td class="px-4 py-3 align-top text-brand-700">
                                {{ $tag->description ? \Illuminate\Support\Str::limit($tag->description, 60) : '—' }}
                            </td>
                            <td class="px-4 py-3 align-top text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                                    {{ $tag->contacts_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-top text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.contact-tags.edit', $tag) }}"
                                       class="inline-flex items-center justify-center h-9 w-9 rounded-full border border-brand-200 text-brand-600 hover:text-brand-900 hover:border-brand-400 hover:bg-brand-50 transition focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1"
                                       aria-label="Edit tag">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.contact-tags.destroy', $tag) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Delete this tag? This will remove it from all contacts.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                {{ $tag->contacts_count > 0 ? 'disabled' : '' }}
                                                class="inline-flex items-center justify-center h-9 w-9 rounded-full border border-red-200 text-red-600 hover:text-red-800 hover:border-red-400 hover:bg-red-50 transition focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed"
                                                aria-label="Delete tag"
                                                title="{{ $tag->contacts_count > 0 ? 'Cannot delete tag in use' : 'Delete tag' }}">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-brand-400">
                                <svg class="h-12 w-12 mx-auto text-brand-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                                    <line x1="7" y1="7" x2="7.01" y2="7"/>
                                </svg>
                                <p class="text-sm font-medium">No tags found.</p>
                                <p class="text-xs mt-1">Create your first tag to organize contacts.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-5 py-4 border-t border-brand-100/60">
            {{ $tags->links() }}
        </div>
    </section>
</div>
@endsection
