@extends('layouts.sidebar')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Branded Header --}}
        <section class="rounded-[20px] sm:rounded-[28px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7 text-white">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Edit Usage Log</p>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">{{ $asset->name }}</h1>
                    <p class="text-sm text-brand-100/85 mt-1">Modify usage log entry.</p>
                </div>
            </div>
        </section>

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

        <form action="{{ route('assets.usage-logs.update', [$asset, $usageLog]) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6 space-y-6">
                <h2 class="text-lg font-bold text-brand-900 border-b-2 border-brand-100 pb-3">Usage Information</h2>

                {{-- User Selection --}}
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">User / Operator *</label>
                    <select name="user_id" required class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        <option value="">Select user...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $usageLog->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    {{-- Check Out Time --}}
                    <div>
                        <label class="block text-sm font-semibold text-brand-800 mb-2">Checked Out At *</label>
                        <input type="datetime-local" name="checked_out_at" 
                            value="{{ old('checked_out_at', $usageLog->checked_out_at?->format('Y-m-d\TH:i')) }}" 
                            required
                            class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    </div>

                    {{-- Check In Time --}}
                    <div>
                        <label class="block text-sm font-semibold text-brand-800 mb-2">Checked In At</label>
                        <input type="datetime-local" name="checked_in_at" 
                            value="{{ old('checked_in_at', $usageLog->checked_in_at?->format('Y-m-d\TH:i')) }}" 
                            class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                    </div>

                    {{-- Mileage Out --}}
                    <div>
                        <label class="block text-sm font-semibold text-brand-800 mb-2">Mileage / Hours (Out)</label>
                        <input type="number" name="mileage_out" value="{{ old('mileage_out', $usageLog->mileage_out) }}" 
                            class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all"
                            placeholder="Starting reading">
                    </div>

                    {{-- Mileage In --}}
                    <div>
                        <label class="block text-sm font-semibold text-brand-800 mb-2">Mileage / Hours (In)</label>
                        <input type="number" name="mileage_in" value="{{ old('mileage_in', $usageLog->mileage_in) }}" 
                            class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all"
                            placeholder="Ending reading">
                    </div>

                    {{-- Status --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-brand-800 mb-2">Status *</label>
                        <select name="status" required class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                            <option value="checked_out" {{ old('status', $usageLog->status) == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                            <option value="checked_in" {{ old('status', $usageLog->status) == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                        </select>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Notes</label>
                    <textarea name="notes" rows="4" 
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all resize-none"
                        placeholder="Notes and observations...">{{ old('notes', $usageLog->notes) }}</textarea>
                </div>

                @if($usageLog->inspection_data && count($usageLog->inspection_data) > 0)
                    <div>
                        <label class="block text-sm font-semibold text-brand-800 mb-3">Inspection Data</label>
                        <div class="bg-brand-50 rounded-xl p-4 border border-brand-200">
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach($usageLog->inspection_data as $key => $value)
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="checkbox" name="inspection_data[{{ $key }}]" value="1" 
                                            {{ $value ? 'checked' : '' }}
                                            class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                        <span class="text-sm text-brand-800 group-hover:text-brand-900">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-3 justify-between">
                <form action="{{ route('assets.usage-logs.destroy', [$asset, $usageLog]) }}" method="POST" onsubmit="return confirm('Delete this usage log entry? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <x-brand-button type="submit" variant="outline" class="border-red-300 text-red-600 hover:bg-red-50">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                        </svg>
                        Delete Entry
                    </x-brand-button>
                </form>

                <div class="flex items-center gap-3">
                    <x-brand-button href="{{ route('assets.show', $asset) }}" variant="secondary">
                        Cancel
                    </x-brand-button>
                    <x-brand-button type="submit" variant="primary">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Save Changes
                    </x-brand-button>
                </div>
            </div>
        </form>
    </div>
@endsection
