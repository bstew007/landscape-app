@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Modern Header with Icon --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-6">
            <div class="flex-shrink-0">
                <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-gray-700 to-gray-900 flex items-center justify-center shadow-lg">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <circle cx="12" cy="6" r="1.5" fill="currentColor"/>
                        <circle cx="12" cy="12" r="1.5" fill="currentColor"/>
                        <circle cx="12" cy="18" r="1.5" fill="currentColor"/>
                    </svg>
                </div>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Fence Estimate Results</h1>
                <p class="text-gray-600 mt-1">{{ ucfirst($data['fence_type'] ?? 'Fence') }} installation - {{ $data['height'] ?? '' }}' height</p>
            </div>
        </div>

        {{-- Client Info --}}
        @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
    </div>

    {{-- Price Summary Card --}}
    <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-green-800 mb-1">Final Price</p>
                <p class="text-4xl font-bold text-green-900">${{ number_format($data['final_price'], 2) }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-green-700">{{ number_format($data['total_hours'] ?? 0, 1) }} total hours</p>
                <p class="text-sm text-green-700">{{ $data['length'] ?? 0 }} ft fence</p>
            </div>
        </div>
    </div>

    {{-- Materials Summary --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <h2 class="text-xl font-bold text-gray-900">Materials</h2>
        </div>
        @include('calculators.partials.materials_table', [
            'materials' => $data['materials'],
            'material_total' => $data['material_total']
        ])
    </div>

    {{-- Fence Specifications --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <h2 class="text-xl font-bold text-gray-900">Fence Specifications</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Total Length</p>
                <p class="text-lg font-bold text-gray-900">{{ number_format($data['length'], 0) }} ft</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Fence Type</p>
                <p class="text-lg font-bold text-gray-900">{{ ucfirst($data['fence_type'] ?? 'N/A') }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Height</p>
                <p class="text-lg font-bold text-gray-900">{{ $data['height'] ?? 'N/A' }} feet</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Adjusted Length</p>
                <p class="text-lg font-bold text-gray-900">{{ number_format($data['adjusted_length'], 0) }} ft</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Total Gates</p>
                <p class="text-lg font-bold text-gray-900">{{ ($data['gate_4ft'] ?? 0) + ($data['gate_5ft'] ?? 0) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">Dig Method</p>
                <p class="text-lg font-bold text-gray-900">{{ ucfirst($data['dig_method'] ?? 'N/A') }}</p>
            </div>
        </div>
    </div>

    {{-- Labor Breakdown --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h2 class="text-xl font-bold text-gray-900">Labor Breakdown</h2>
        </div>
        <div class="space-y-2 mb-4">
            @foreach ($data['labor_by_task'] as $task => $hours)
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-700 capitalize">{{ str_replace('_', ' ', $task) }}</span>
                    <span class="font-semibold text-gray-900">{{ number_format($hours, 2) }} hrs</span>
                </div>
            @endforeach
        </div>
        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Base Labor Hours:</span>
                <span class="font-semibold text-gray-900">{{ number_format($data['base_hours'] ?? 0, 2) }} hrs</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Drive Time:</span>
                <span class="font-semibold text-gray-900">{{ number_format($data['drive_time_hours'] ?? 0, 2) }} hrs</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Overhead Hours:</span>
                <span class="font-semibold text-gray-900">{{ number_format($data['overhead_hours'] ?? 0, 2) }} hrs</span>
            </div>
            <div class="flex justify-between pt-2 border-t border-gray-200 font-bold">
                <span class="text-gray-900">Total Labor Hours:</span>
                <span class="text-gray-900">{{ number_format($data['total_hours'] ?? 0, 2) }} hrs</span>
            </div>
            <div class="flex justify-between pt-2 border-t border-gray-200 font-bold text-lg">
                <span class="text-gray-900">Labor Cost:</span>
                <span class="text-green-600">${{ number_format($data['labor_cost'] ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Pricing Breakdown --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h2 class="text-xl font-bold text-gray-900">Pricing Summary</h2>
        </div>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-700">Labor Cost</span>
                <span class="text-lg font-semibold text-gray-900">${{ number_format($data['labor_cost'] ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-700">Material Cost</span>
                <span class="text-lg font-semibold text-gray-900">${{ number_format($data['material_total'] ?? 0, 2) }}</span>
            </div>
            <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                <span class="font-semibold text-gray-700">Subtotal</span>
                <span class="text-lg font-semibold text-gray-900">${{ number_format(($data['labor_cost'] ?? 0) + ($data['material_total'] ?? 0), 2) }}</span>
            </div>
            <div class="flex justify-between items-center pt-3 border-t-2 border-gray-300">
                <span class="text-xl font-bold text-gray-900">Final Price</span>
                <span class="text-2xl font-bold text-green-600">${{ number_format($data['final_price'] ?? 0, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Job Notes --}}
    @if (!empty($data['job_notes']))
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <h3 class="font-semibold text-amber-900 mb-2">Job Notes</h3>
                <p class="text-amber-800 whitespace-pre-line">{{ $data['job_notes'] }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Enhanced Import to Estimate Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-2 mb-6">
            <svg class="w-6 h-6 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h2 class="text-xl font-bold text-gray-900">Import to Estimate</h2>
        </div>

        <form action="{{ route('calculators.import-to-estimate') }}" method="POST">
            @csrf
            <input type="hidden" name="calculation_id" value="{{ $calculation->id ?? '' }}">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Estimate Selection --}}
                <div>
                    <label for="estimate_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Select Estimate
                    </label>
                    <div class="flex gap-2">
                        <select name="estimate_id" id="estimate_id" required 
                                class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent">
                            <option value="">-- Choose Estimate --</option>
                            @if(isset($siteVisit->client->estimates))
                                @foreach($siteVisit->client->estimates as $estimate)
                                    <option value="{{ $estimate->id }}">
                                        Estimate #{{ $estimate->id }} - {{ $estimate->name ?? 'Unnamed' }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        <button type="button" 
                                onclick="window.location.href='{{ route('estimates.create', ['client_id' => $siteVisit->client->id ?? '']) }}'" 
                                class="px-4 py-2.5 bg-brand-800 hover:bg-brand-700 text-white font-medium rounded-lg transition-colors whitespace-nowrap">
                            + New
                        </button>
                    </div>
                </div>

                {{-- Work Area Name --}}
                <div>
                    <label for="work_area_name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Work Area Name
                    </label>
                    <input type="text" name="work_area_name" id="work_area_name" required
                           value="Fence Installation - {{ ucfirst($data['fence_type'] ?? 'Fence') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                           placeholder="e.g., Backyard Fence - Wood 6'">
                </div>
            </div>

            {{-- Import Mode Selection --}}
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-700 mb-3">Import Mode</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="relative flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-brand-500 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 transition-colors">
                        <input type="radio" name="import_mode" value="granular" checked class="mt-1 h-4 w-4 text-brand-600 border-gray-300 focus:ring-brand-500">
                        <div class="ml-3">
                            <span class="block text-sm font-semibold text-gray-900">Granular (Detailed)</span>
                            <span class="block text-sm text-gray-600 mt-1">Create individual line items for each labor task and material</span>
                        </div>
                    </label>
                    <label class="relative flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-brand-500 has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 transition-colors">
                        <input type="radio" name="import_mode" value="collapsed" class="mt-1 h-4 w-4 text-brand-600 border-gray-300 focus:ring-brand-500">
                        <div class="ml-3">
                            <span class="block text-sm font-semibold text-gray-900">Collapsed (Summary)</span>
                            <span class="block text-sm text-gray-600 mt-1">Single line item with total cost (legacy mode)</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap gap-3">
                <button type="submit" name="action" value="import" 
                        class="px-6 py-3 bg-brand-800 hover:bg-brand-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                    📋 Import to Estimate
                </button>
                <button type="submit" name="action" value="save_only" 
                        class="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                    💾 Save Only
                </button>
            </div>
        </form>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-3">
        @if(isset($calculation))
            <a href="{{ route('calculators.fence.downloadPdf', $calculation->id) }}" 
               class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                📄 Download PDF
            </a>
            <a href="{{ route('calculators.fence.edit', $calculation->id) }}" 
               class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm">
                ✏️ Edit Calculation
            </a>
        @endif
        <a href="{{ route('site-visits.show', $siteVisit->id) }}" 
           class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
            ← Back to Site Visit
        </a>
    </div>
</div>

@endsection

