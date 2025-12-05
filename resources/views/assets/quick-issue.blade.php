@extends('layouts.sidebar')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        {{-- Branded Header --}}
        <section class="rounded-[20px] sm:rounded-[28px] bg-gradient-to-br from-red-900 via-red-800 to-red-700 text-white p-6 sm:p-8 shadow-2xl border border-red-800/40">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7 text-white">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-red-200/80">Assets</p>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">Log Asset Issue</h1>
                    <p class="text-sm text-red-100/85 mt-1">Quickly capture breakdowns, damage, or maintenance requests.</p>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-xl bg-green-50 border-2 border-green-200 p-4">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-xl bg-red-50 border-2 border-red-200 text-red-800 px-5 py-4">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('assets.issues.quickStore') }}" method="POST" class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-brand-800 mb-1">Asset</label>
                <select name="asset_id" class="form-select w-full" required>
                    <option value="">Select asset</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}" @selected(old('asset_id') == $asset->id)>
                            {{ $asset->name }} ({{ ucwords(str_replace('_', ' ', $asset->type)) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-brand-800 mb-1">Title</label>
                <input type="text" name="title" class="form-input w-full" value="{{ old('title') }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-brand-800 mb-1">Description</label>
                <textarea name="description" rows="4" class="form-textarea w-full">{{ old('description') }}</textarea>
            </div>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-brand-800 mb-1">Severity</label>label>
                    <select name="severity" class="form-select w-full">
                        @foreach ($issueSeverities as $severity)
                            <option value="{{ $severity }}" @selected(old('severity') === $severity)>{{ ucfirst($severity) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-brand-800 mb-1">Status</label>
                    <select name="status" class="form-select w-full">
                        @foreach ($issueStatuses as $status)
                            <option value="{{ $status }}" @selected(old('status', 'open') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-brand-800 mb-1">Reported On</label>
                    <input type="date" name="reported_on" class="form-input w-full" value="{{ old('reported_on', now()->format('Y-m-d')) }}">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-4 border-t border-brand-100">
                <x-brand-button href="{{ route('assets.index') }}" variant="outline">
                    <svg class="h-4 w-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Cancel
                </x-brand-button>
                <x-brand-button type="submit">
                    <svg class="h-4 w-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    Log Issue
                </x-brand-button>
            </div>
        </form>
    </div>
@endsection
