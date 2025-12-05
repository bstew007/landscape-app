@extends('layouts.sidebar')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Branded Header --}}
        <section class="rounded-[20px] sm:rounded-[28px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7 text-white">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Asset Sign In</p>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">{{ $asset->name }}</h1>
                    <p class="text-sm text-brand-100/85 mt-1">Check in asset with post-use inspection.</p>
                </div>
            </div>
        </section>

        {{-- Checkout Info --}}
        <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-brand-50 border-2 border-brand-200 p-5">
            <div class="flex items-start gap-3">
                <svg class="h-5 w-5 text-brand-600 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
                </svg>
                <div class="flex-1 text-sm">
                    <p class="font-semibold text-brand-900">Currently checked out to: <span class="text-brand-700">{{ $usageLog->user->name ?? 'Unknown' }}</span></p>
                    <p class="text-brand-700 mt-1">Checked out: <strong>{{ $usageLog->checked_out_at->format('M j, Y g:i A') }}</strong></p>
                    @if($usageLog->mileage_out)
                        <p class="text-brand-700 mt-0.5">Starting mileage/hours: <strong>{{ number_format($usageLog->mileage_out) }}</strong></p>
                    @endif
                </div>
            </div>
        </div>

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

        <form action="{{ route('assets.checkin.store', $asset) }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="usage_log_id" value="{{ $usageLog->id }}">

            <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6 space-y-6">
                <h2 class="text-lg font-bold text-brand-900 border-b-2 border-brand-100 pb-3">Sign In Information</h2>

                {{-- Mileage/Hours In --}}
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Mileage / Hours (Return Reading)</label>
                    <input type="number" name="mileage_in" value="{{ old('mileage_in') }}" 
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all"
                        placeholder="Enter current reading">
                    @if($usageLog->mileage_out)
                        <p class="text-xs text-brand-600 mt-1">Starting reading was {{ number_format($usageLog->mileage_out) }}</p>
                    @endif
                </div>
            </div>

            {{-- Post-Use Inspection Checklist --}}
            <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6 space-y-6">
                <h2 class="text-lg font-bold text-brand-900 border-b-2 border-brand-100 pb-3">Post-Use Inspection</h2>
                
                <div class="space-y-4">
                    {{-- Exterior Condition --}}
                    <div class="bg-brand-50/50 rounded-xl p-4 border border-brand-200/50">
                        <h3 class="font-semibold text-brand-900 mb-3">Exterior Condition</h3>
                        <div class="space-y-2.5">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_exterior_clean]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Exterior cleaned after use</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_no_new_damage]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">No new damage occurred</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_lights_off]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">All lights turned off</span>
                            </label>
                        </div>
                    </div>

                    {{-- Fluid Checks --}}
                    <div class="bg-brand-50/50 rounded-xl p-4 border border-brand-200/50">
                        <h3 class="font-semibold text-brand-900 mb-3">Fluid Levels</h3>
                        <div class="space-y-2.5">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_no_leaks]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">No fluid leaks detected</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_oil_ok]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Oil level checked and adequate</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_fuel_noted]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Fuel level noted (refuel if needed)</span>
                            </label>
                        </div>
                    </div>

                    {{-- Operational Issues --}}
                    <div class="bg-brand-50/50 rounded-xl p-4 border border-brand-200/50">
                        <h3 class="font-semibold text-brand-900 mb-3">Operational Performance</h3>
                        <div class="space-y-2.5">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_no_issues]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">No operational issues during use</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_brakes_ok]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Brakes performed properly</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_controls_ok]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">All controls functioned properly</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_unusual_sounds]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">No unusual sounds or vibrations</span>
                            </label>
                        </div>
                    </div>

                    {{-- Cleanliness & Storage --}}
                    <div class="bg-brand-50/50 rounded-xl p-4 border border-brand-200/50">
                        <h3 class="font-semibold text-brand-900 mb-3">Cleanliness & Storage</h3>
                        <div class="space-y-2.5">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_interior_clean]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Interior cleaned and organized</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_trash_removed]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Trash removed from cabin</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_parked_properly]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Parked in designated area</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_keys_returned]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900">Keys returned to proper location</span>
                            </label>
                        </div>
                    </div>

                    {{-- Service Needs --}}
                    <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200">
                        <h3 class="font-semibold text-brand-900 mb-3 flex items-center gap-2">
                            <svg class="h-5 w-5 text-yellow-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Service Needs
                        </h3>
                        <div class="space-y-2.5">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_needs_service]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-yellow-400 text-yellow-600 focus:ring-2 focus:ring-yellow-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900 font-medium">Requires maintenance or service</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="inspection_data[return_needs_repair]" value="1" 
                                    class="h-5 w-5 rounded border-2 border-yellow-400 text-yellow-600 focus:ring-2 focus:ring-yellow-500/20 transition-all">
                                <span class="text-sm text-brand-800 group-hover:text-brand-900 font-medium">Requires immediate repair</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-semibold text-brand-800 mb-2">Notes / Issues or Damage Reported</label>
                    <textarea name="notes" rows="4" 
                        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all resize-none"
                        placeholder="Document any issues, damage, or maintenance needs...">{{ old('notes') }}</textarea>
                    <p class="text-xs text-brand-600 mt-1">Please report any damage, issues, or unusual behavior experienced during use.</p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-3 justify-end">
                <x-brand-button href="{{ route('assets.show', $asset) }}" variant="secondary">
                    Cancel
                </x-brand-button>
                <x-brand-button type="submit" variant="primary">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Check In Asset
                </x-brand-button>
            </div>
        </form>
    </div>
@endsection
