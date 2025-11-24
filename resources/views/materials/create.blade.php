@extends('layouts.sidebar')

@section('content')
@php
    $categories = \App\Models\MaterialCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
@endphp

<div class="space-y-8">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-2 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Catalogs</p>
                <h1 class="text-2xl sm:text-3xl font-semibold">Add Material</h1>
                <p class="text-sm text-brand-100/90">These values become the defaults when building estimates.</p>
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

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden" x-data="{ tab: 'details' }">
        <!-- Tabs -->
        <div class="border-b border-brand-100/60">
            <nav class="flex p-4 gap-2">
                <button type="button" @click="tab = 'details'" 
                        :class="tab === 'details' ? 'bg-brand-700 text-white' : 'bg-brand-50 text-brand-700 hover:bg-brand-100'"
                        class="px-4 py-2 rounded-lg font-semibold text-sm transition">
                    Material Details
                </button>
                <button type="button" @click="tab = 'categories'" 
                        :class="tab === 'categories' ? 'bg-brand-700 text-white' : 'bg-brand-50 text-brand-700 hover:bg-brand-100'"
                        class="px-4 py-2 rounded-lg font-semibold text-sm transition">
                    Categories
                </button>
            </nav>
        </div>

        <form method="POST" action="{{ route('materials.store') }}" class="p-6 sm:p-8">
            @csrf
            
            <!-- Details Tab -->
            <div x-show="tab === 'details'" class="space-y-6">
                @include('materials._form')
            </div>

            <!-- Categories Tab -->
            <div x-show="tab === 'categories'" x-cloak class="space-y-4">
                <p class="text-sm text-gray-600 mb-4">Select categories for this material to help with filtering and organization.</p>
                
                @if($categories->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <p>No categories available.</p>
                        <a href="{{ route('admin.material-categories.create') }}" class="text-brand-600 hover:underline">Create a category first</a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($categories as $category)
                            <label class="flex items-start gap-3 p-3 rounded-lg border border-brand-200 hover:bg-brand-50 cursor-pointer transition">
                                <input type="checkbox" name="categories[]" value="{{ $category->id }}" 
                                       class="mt-0.5 form-checkbox rounded border-brand-300 text-brand-600 focus:ring-brand-500">
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900">{{ $category->name }}</div>
                                    @if($category->description)
                                        <div class="text-xs text-gray-500 mt-1">{{ $category->description }}</div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-6 mt-6 border-t border-gray-200">
                <x-brand-button type="submit">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Create Material
                </x-brand-button>
                <x-secondary-button as="a" href="{{ route('materials.index') }}">
                    Cancel
                </x-secondary-button>
            </div>
        </form>
    </section>
</div>
@endsection
