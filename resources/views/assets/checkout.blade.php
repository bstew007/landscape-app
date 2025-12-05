@extends('layouts.sidebar')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Branded Header --}}
        <section class="rounded-[20px] sm:rounded-[28px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7 text-white">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Asset Sign Out</p>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">{{ $asset->name }}</h1>
                    <p class="text-sm text-brand-100/85 mt-1">Check out asset with pre-use inspection.</p>
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

        <form action="{{ route('assets.checkout.store', $asset) }}" method="POST" class="space-y-6">
            @csrf

            <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6 space-y-6">
                <h2 class="text-lg font-bold text-brand-900 border-b-2 border-brand-100 pb-3">Sign Out Information</h2>

                {{-- User Selection --}}
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">User / Operator *</label>
                    <select name="user_id" required class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
                        <option value="">Select user...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Mileage/Hours Out --}}
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Mileage / Hours (Current Reading)</label>
                    <input type="number" name="mileage_out" value="{{ old('mileage_out', $asset->mileage_hours) }}" 
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all"
                        placeholder="Enter current reading">
                </div>
            </div>

            {{-- Pre-Use Inspection Checklist --}}
            <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6 space-y-6">
                <h2 class="text-lg font-bold text-brand-900 border-b-2 border-brand-100 pb-3">Pre-Use Inspection</h2>
                
                <div class="space-y-4">
                    {{-- Exterior Condition --}}
                    <div class="bg-brand-50/50 rounded-xl p-4 border border-brand-200/50">
                        <h3 class="font-semibold text-brand-900 mb-3">Exterior Condition</h3>
                        <div class="space-y-2.5">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[exterior_clean]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Exterior clean and free of damage</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[lights_working]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">All lights functional</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[tires_good]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Tires properly inflated and in good condition</span>
                            </label>
                        </div>
                    </div>

                    {{-- Fluid Levels --}}
                    <div class="bg-brand-50/50 rounded-xl p-4 border border-brand-200/50">
                        <h3 class="font-semibold text-brand-900 mb-3">Fluid Levels</h3>
                        <div class="space-y-2.5">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[oil_level]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Oil level adequate</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[coolant_level]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Coolant level adequate</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[fuel_level]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Fuel level checked</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[hydraulic_fluid]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Hydraulic fluid adequate (if applicable)</span>
                            </label>
                        </div>
                    </div>

                    {{-- Safety Equipment --}}
                    <div class="bg-brand-50/50 rounded-xl p-4 border border-brand-200/50">
                        <h3 class="font-semibold text-brand-900 mb-3">Safety Equipment</h3>
                        <div class="space-y-2.5">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[seat_belts]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Seat belts functional</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[fire_extinguisher]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Fire extinguisher present and charged</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[emergency_kit]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Emergency kit/first aid present</span>
                            </label>
                        </div>
                    </div>

                    {{-- Operational Checks --}}
                    <div class="bg-brand-50/50 rounded-xl p-4 border border-brand-200/50">
                        <h3 class="font-semibold text-brand-900 mb-3">Operational Checks</h3>
                        <div class="space-y-2.5">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[brakes_working]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Brakes functioning properly</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[steering_responsive]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Steering responsive</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[horn_working]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Horn/backup alarm working</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[controls_functional]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">All controls functional</span>
                            </label>
                        </div>
                    </div>

                    {{-- Interior/Cabin --}}
                    <div class="bg-brand-50/50 rounded-xl p-4 border border-brand-200/50">
                        <h3 class="font-semibold text-brand-900 mb-3">Interior/Cabin</h3>
                        <div class="space-y-2.5">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[interior_clean]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Interior clean and organized</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[mirrors_adjusted]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Mirrors adjusted and clean</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[gauges_working]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">All gauges/indicators working</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Notes / Issues Found</label>
                    <textarea name="notes" rows="4" 
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all resize-none"
                        placeholder="Document any issues, concerns, or observations before use...">{{ old('notes') }}</textarea>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-3 justify-end">
                <x-brand-button href="{{ route('assets.show', $asset) }}" variant="secondary">
                    Cancel
                </x-brand-button>
                <x-brand-button type="submit" variant="primary">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Check Out Asset
                </x-brand-button>
            </div>
        </form>
    </div>
@endsection
