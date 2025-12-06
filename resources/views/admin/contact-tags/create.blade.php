@extends('layouts.sidebar')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto p-4">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-2 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Admin / Settings</p>
                <h1 class="text-2xl sm:text-3xl font-semibold">Add Contact Tag</h1>
                <p class="text-sm text-brand-100/90">Create a new tag to categorize and organize your contacts.</p>
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
            <form method="POST" action="{{ route('admin.contact-tags.store') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">Tag Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="form-input w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring-brand-500"
                           placeholder="e.g., Vendor, Rental, Preferred">
                    <p class="text-xs text-gray-500 mt-1">A unique name for this tag. A URL-friendly slug will be generated automatically.</p>
                </div>

                <div>
                    <label for="color" class="block text-sm font-semibold text-gray-900 mb-2">Color <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="color" name="color" value="{{ old('color', '#3b82f6') }}" required
                               class="h-10 w-20 rounded border-brand-200 cursor-pointer">
                        <span class="text-sm text-gray-600">Choose a color to visually identify this tag</span>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-3">
                        <p class="text-xs text-gray-500 w-full mb-1">Quick colors:</p>
                        <button type="button" onclick="document.getElementById('color').value='#3b82f6'" class="h-8 w-8 rounded-full border-2 border-white shadow-sm" style="background-color: #3b82f6" title="Blue"></button>
                        <button type="button" onclick="document.getElementById('color').value='#8b5cf6'" class="h-8 w-8 rounded-full border-2 border-white shadow-sm" style="background-color: #8b5cf6" title="Purple"></button>
                        <button type="button" onclick="document.getElementById('color').value='#10b981'" class="h-8 w-8 rounded-full border-2 border-white shadow-sm" style="background-color: #10b981" title="Green"></button>
                        <button type="button" onclick="document.getElementById('color').value='#f59e0b'" class="h-8 w-8 rounded-full border-2 border-white shadow-sm" style="background-color: #f59e0b" title="Amber"></button>
                        <button type="button" onclick="document.getElementById('color').value='#ef4444'" class="h-8 w-8 rounded-full border-2 border-white shadow-sm" style="background-color: #ef4444" title="Red"></button>
                        <button type="button" onclick="document.getElementById('color').value='#6366f1'" class="h-8 w-8 rounded-full border-2 border-white shadow-sm" style="background-color: #6366f1" title="Indigo"></button>
                        <button type="button" onclick="document.getElementById('color').value='#ec4899'" class="h-8 w-8 rounded-full border-2 border-white shadow-sm" style="background-color: #ec4899" title="Pink"></button>
                        <button type="button" onclick="document.getElementById('color').value='#14b8a6'" class="h-8 w-8 rounded-full border-2 border-white shadow-sm" style="background-color: #14b8a6" title="Teal"></button>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="form-textarea w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring-brand-500"
                              placeholder="Optional description for this tag">{{ old('description') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Provide additional context about when to use this tag.</p>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                    <x-brand-button type="submit">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Create Tag
                    </x-brand-button>
                    <x-secondary-button as="a" href="{{ route('admin.contact-tags.index') }}">
                        Cancel
                    </x-secondary-button>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
