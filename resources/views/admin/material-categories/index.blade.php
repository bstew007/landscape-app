@extends('layouts.sidebar')

@section('content')
@php
    $pageCount = $categories->count();
    $activeCount = $categories->where('is_active', true)->count();
@endphp

<div class="space-y-8">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-2 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Admin / Settings</p>
                <h1 class="text-2xl sm:text-3xl font-semibold">Material Categories</h1>
                <p class="text-sm text-brand-100/90">Organize your materials catalog with categories for better filtering and organization.</p>
            </div>
            <div class="flex flex-wrap gap-3 ml-auto">
                <x-brand-button href="{{ route('admin.material-categories.create') }}" variant="muted">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Category
                </x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">On This Page</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($pageCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Active</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($activeCount) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Total Categories</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($categories->total()) }}</dd>
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
                <input type="text" name="search" value="{{ $search }}" placeholder="Search categories..."
                       class="flex-1 min-w-[200px] rounded-full border-brand-200 bg-white text-sm px-4 py-2 focus:ring-brand-500 focus:border-brand-500">
                <button type="submit"
                        class="inline-flex items-center gap-1.5 h-9 px-4 rounded-full bg-brand-600 text-white text-sm font-semibold shadow-sm hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1 transition disabled:opacity-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    Search
                </button>
                @if($search)
                    <a href="{{ route('admin.material-categories.index') }}" class="text-xs text-brand-500 hover:text-brand-700">Clear</a>
                @endif
            </form>
        </div>

        <div class="border-t border-brand-100/60">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-brand-50/80 text-left text-[11px] uppercase tracking-wide text-brand-500">
                    <tr>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Description</th>
                        <th class="px-4 py-3 text-center">Materials</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                    @forelse ($categories as $category)
                        <tr class="transition hover:bg-brand-50/70">
                            <td class="px-4 py-3 align-top">
                                <p class="font-semibold text-brand-900">{{ $category->name }}</p>
                                <p class="text-xs text-brand-400">Created {{ $category->created_at->format('M j, Y') }}</p>
                            </td>
                            <td class="px-4 py-3 align-top text-brand-700">
                                {{ $category->description ? \Illuminate\Support\Str::limit($category->description, 60) : '—' }}
                            </td>
                            <td class="px-4 py-3 align-top text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                                    {{ $category->materials_count ?? $category->materials()->count() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 align-top text-center">
                                @if ($category->is_active)
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">Active</span>
                                @else
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.material-categories.edit', $category) }}"
                                       class="inline-flex items-center justify-center h-9 w-9 rounded-full border border-brand-200 text-brand-600 hover:text-brand-900 hover:border-brand-400 hover:bg-brand-50 transition focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1"
                                       aria-label="Edit category">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.material-categories.destroy', $category) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Delete this category? This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center h-9 w-9 rounded-full border border-red-200 text-red-600 hover:text-red-800 hover:border-red-400 hover:bg-red-50 transition focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1"
                                                aria-label="Delete category">
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
                            <td colspan="5" class="px-4 py-8 text-center text-brand-400">
                                <svg class="h-12 w-12 mx-auto text-brand-300 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h18v18H3zM3 9h18M9 21V9"/></svg>
                                <p class="text-sm font-medium">No categories found.</p>
                                <p class="text-xs mt-1">Create your first category to organize materials.</p>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-5 py-4 border-t border-brand-100/60">
            {{ $categories->links() }}
        </div>
    </section>
</div>
@endsection
