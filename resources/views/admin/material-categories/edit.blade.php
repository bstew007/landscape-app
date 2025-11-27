@extends('layouts.sidebar')

@section('content')
<div class="space-y-8">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-2 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Admin / Settings</p>
                <h1 class="text-2xl sm:text-3xl font-semibold">Edit Material Category</h1>
                <p class="text-sm text-brand-100/90">Update category details and organization settings.</p>
            </div>
        </div>
    </section>

    @if ($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3">
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-6 sm:p-8">
            <form method="POST" action="{{ route('admin.material-categories.update', $materialCategory) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $materialCategory->name) }}" required
                           class="form-input w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring-brand-500"
                           placeholder="e.g., Hardscape Materials, Plants, Irrigation">
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="form-textarea w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring-brand-500"
                              placeholder="Optional description for this category">{{ old('description', $materialCategory->description) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Provide additional context or notes about this category.</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $materialCategory->is_active) ? 'checked' : '' }}
                           class="form-checkbox rounded border-brand-300 text-brand-600 focus:ring-brand-500">
                    <label for="is_active" class="text-sm text-gray-900">Active</label>
                    <p class="text-xs text-gray-500 ml-2">(Inactive categories are hidden from material forms)</p>
                </div>

                @if($materialCategory->materials()->count() > 0)
                    <div class="rounded-lg bg-brand-50 border border-brand-200 px-4 py-3">
                        <div class="flex items-start gap-2">
                            <svg class="h-5 w-5 text-brand-600 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <div class="text-sm text-brand-800">
                                <p class="font-semibold">This category has {{ $materialCategory->materials()->count() }} materials assigned.</p>
                                <p class="text-xs mt-1">If you deactivate or delete this category, reassign those materials first.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                    <x-brand-button type="submit">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Update Category
                    </x-brand-button>
                    <x-secondary-button as="a" href="{{ route('admin.material-categories.index') }}">
                        Cancel
                    </x-secondary-button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
