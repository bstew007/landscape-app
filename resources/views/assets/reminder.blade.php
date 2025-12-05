@extends('layouts.sidebar')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        {{-- Branded Header --}}
        <section class="rounded-[20px] sm:rounded-[28px] bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 text-white p-6 sm:p-8 shadow-2xl border border-blue-800/40">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7 text-white">
                        <circle cx="12" cy="13" r="7"/><path d="M12 10v4l3 2M7 3h3M14 3h3"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-blue-200/80">Assets</p>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">Schedule Service Reminder</h1>
                    <p class="text-sm text-blue-100/85 mt-1">Pick an asset, set the next service date, and configure the reminder window.</p>
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

        <form action="{{ route('assets.reminders.store') }}" method="POST" class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6 space-y-4">
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
                <label class="block text-sm font-medium text-brand-800 mb-1">Next Service Date</label>
                <input type="date" name="next_service_date" value="{{ old('next_service_date', now()->addWeek()->format('Y-m-d')) }}"
                       class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-brand-800 mb-1">Reminder Window (days before)</label>
                <input type="number" name="reminder_days_before" min="1" max="60" value="{{ old('reminder_days_before', 7) }}"
                       class="form-input w-full" required>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="reminder_enabled" id="reminder_enabled" value="1"
                       class="h-4 w-4 text-brand-600 border-gray-300 rounded"
                       @checked(old('reminder_enabled', true))>
                <label for="reminder_enabled" class="text-sm text-brand-700">Enable reminder notifications</label>
            </div>
            <div class="flex justify-end gap-2 pt-4 border-t border-brand-100">
                <x-brand-button href="{{ route('assets.index') }}" variant="outline">
                    <svg class="h-4 w-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Cancel
                </x-brand-button>
                <x-brand-button type="submit">
                    <svg class="h-4 w-4 mr-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="13" r="7"/><path d="M12 10v4l3 2M7 3h3M14 3h3"/></svg>
                    Save Reminder
                </x-brand-button>
            </div>
        </form>
    </div>
@endsection
