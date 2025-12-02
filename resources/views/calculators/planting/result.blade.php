@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="bg-gradient-to-br from-green-600 to-green-800 p-3 rounded-xl shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
            </div>
            <h1 class="text-4xl font-bold text-gray-900">Planting Calculation Results</h1>
        </div>
        <p class="text-gray-600">Review your planting calculation summary and import to an estimate.</p>
    </div>

    @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])

    {{-- Total Cost Highlight --}}
    <div class="bg-gradient-to-r from-gray-800 to-gray-700 text-white p-8 rounded-xl shadow-lg mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-300 text-sm font-medium uppercase tracking-wide mb-1">Total Project Cost</p>
                <p class="text-5xl font-bold">${{ number_format($data['labor_cost'] + $data['material_total'], 2) }}</p>
                <p class="text-gray-300 text-xs mt-2">Labor includes facing and watering each plant</p>
            </div>
            <div class="bg-white/20 rounded-full p-4">
                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Materials Summary --}}
    @if (!empty($data['materials']))
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-lg font-semibold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Plant Materials Required
                </h2>
            </div>
            <div class="p-6">
                @include('calculators.partials.materials_table', [
                    'materials' => $data['materials'],
                    'material_total' => $data['material_total'] ?? 0
                ])
            </div>
        </div>
    @endif

    {{-- Labor Breakdown --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
            <h2 class="text-lg font-semibold text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Labor Breakdown
            </h2>
        </div>
        <div class="p-6">
            <div class="space-y-3 mb-6">
                @foreach ($data['labor_by_task'] as $task => $hours)
                    <div class="flex justify-between items-center py-3 border-b border-gray-100 last:border-0">
                        <span class="text-gray-700 font-medium">{{ $task }}</span>
                        <span class="text-gray-900 font-semibold">{{ number_format($hours, 2) }} hrs</span>
                    </div>
                @endforeach
            </div>
            
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Base Labor:</span>
                    <span class="text-gray-900 font-semibold">{{ number_format($data['labor_hours'], 2) }} hrs</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Job Site Visits:</span>
                    <span class="text-gray-900 font-semibold">{{ $data['visits'] ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Overhead:</span>
                    <span class="text-gray-900 font-semibold">{{ number_format($data['overhead_hours'] ?? 0, 2) }} hrs</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Drive Time:</span>
                    <span class="text-gray-900 font-semibold">{{ number_format($data['drive_time_hours'] ?? 0, 2) }} hrs</span>
                </div>
                <div class="border-t border-gray-300 mt-3 pt-3 flex justify-between">
                    <span class="text-gray-900 font-bold">Total Labor Hours:</span>
                    <span class="text-gray-900 font-bold text-lg">{{ number_format($data['total_hours'], 2) }} hrs</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-brand-800 font-bold">Labor Cost:</span>
                    <span class="text-brand-800 font-bold text-lg">${{ number_format($data['labor_cost'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Pricing Breakdown --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
            <h2 class="text-lg font-semibold text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Pricing Breakdown
            </h2>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                <div class="flex justify-between py-2">
                    <span class="text-gray-700">Labor Cost:</span>
                    <span class="text-gray-900 font-semibold">${{ number_format($data['labor_cost'], 2) }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-700">Material Cost:</span>
                    <span class="text-gray-900 font-semibold">${{ number_format($data['material_total'], 2) }}</span>
                </div>
                <div class="flex justify-between py-3 border-t-2 border-brand-200 bg-gradient-to-r from-brand-50 to-brand-100 -mx-6 px-6 rounded-b-lg">
                    <span class="text-brand-900 font-bold text-lg">Total Cost:</span>
                    <span class="text-brand-900 font-bold text-2xl">${{ number_format($data['labor_cost'] + $data['material_total'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Job Notes --}}
    @if (!empty($data['job_notes']))
        <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border-l-4 border-yellow-400 p-6 rounded-xl shadow-sm mb-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-yellow-900 mb-2">Job Notes</h3>
                    <p class="text-gray-800 whitespace-pre-line">{{ $data['job_notes'] }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Import to Estimate Section --}}
    @if(isset($calculation) && $siteVisit)
        <div class="bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden mb-6" 
             x-data="{ 
                estimateMode: 'existing',
                newEstimateTitle: 'Planting - {{ date('M d, Y') }}',
                areaName: 'Planting - {{ date('M d, Y') }}'
             }">
            <div class="bg-gradient-to-r from-gray-800 to-gray-700 px-6 py-4 border-b border-gray-600">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Import to Estimate
                </h2>
            </div>
            
            <form method="POST" action="{{ route('calculators.import-to-estimate') }}" class="p-6">
                @csrf
                <input type="hidden" name="calculation_id" value="{{ $calculation->id }}">
                
                {{-- Estimate Selection Toggle --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Estimate:</label>
                    <div class="flex gap-2 mb-3">
                        <button type="button" @click="estimateMode = 'existing'" 
                                :class="estimateMode === 'existing' ? 'bg-brand-800 text-white' : 'bg-gray-200 text-gray-700'"
                                class="px-4 py-2 rounded-lg font-semibold transition">
                            Select Existing
                        </button>
                        <button type="button" @click="estimateMode = 'new'" 
                                :class="estimateMode === 'new' ? 'bg-brand-800 text-white' : 'bg-gray-200 text-gray-700'"
                                class="px-4 py-2 rounded-lg font-semibold transition">
                            Create New
                        </button>
                    </div>
                    
                    {{-- Existing Estimate Selector --}}
                    <div x-show="estimateMode === 'existing'" x-cloak>
                        <select :name="estimateMode === 'existing' ? 'estimate_id' : ''" 
                                :required="estimateMode === 'existing'"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 focus:border-transparent transition">
                            <option value="">-- Choose an Estimate --</option>
                            @php
                                $estimates = $siteVisit->estimates ?? collect();
                            @endphp
                            @foreach($estimates as $est)
                                <option value="{{ $est->id }}">
                                    #{{ $est->id }} - {{ $est->title }} ({{ ucfirst($est->status) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- New Estimate Input --}}
                    <div x-show="estimateMode === 'new'" x-cloak>
                        <input type="text" 
                               x-model="newEstimateTitle"
                               :name="estimateMode === 'new' ? 'new_estimate_title' : ''"
                               :required="estimateMode === 'new'"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 focus:border-transparent transition"
                               placeholder="e.g., Planting - Perennial Beds">
                        <input type="hidden" :name="estimateMode === 'new' ? 'estimate_id' : ''" value="new">
                    </div>
                </div>
                
                {{-- Work Area Name --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Work Area Name:</label>
                    <input type="text" 
                           name="area_name" 
                           x-model="areaName"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent-500 focus:border-transparent transition"
                           placeholder="e.g., Planting - Perennial Beds">
                    <p class="mt-2 text-sm text-gray-500">This will organize line items in your estimate</p>
                </div>
                
                {{-- Import Type --}}
                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-3">Import Type:</label>
                    <div class="space-y-3">
                        <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg hover:border-brand-400 hover:bg-brand-50 cursor-pointer transition group">
                            <input type="radio" name="import_type" value="granular" checked 
                                   class="mt-1 text-brand-700 focus:ring-brand-500">
                            <div class="ml-4 flex-1">
                                <div class="flex items-center">
                                    <p class="font-bold text-gray-900 group-hover:text-brand-900">Granular Line Items</p>
                                    <span class="ml-2 px-2 py-1 bg-brand-100 text-brand-800 text-xs font-semibold rounded-full">Recommended</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">Creates separate line items for each plant type with full detail (installation, drive time, overhead)</p>
                            </div>
                        </label>
                        <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50 cursor-pointer transition">
                            <input type="radio" name="import_type" value="collapsed" 
                                   class="mt-1 text-gray-600 focus:ring-gray-500">
                            <div class="ml-4 flex-1">
                                <p class="font-bold text-gray-900">Collapsed (Legacy)</p>
                                <p class="text-sm text-gray-600 mt-1">Creates single labor and material line items (old format)</p>
                            </div>
                        </label>
                    </div>
                </div>
                
                {{-- Action Buttons --}}
                <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                    <button type="submit" name="action" value="import" 
                            class="flex-1 px-8 py-4 bg-brand-800 hover:bg-brand-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Import to Estimate
                    </button>
                    <button type="submit" name="action" value="save_only" 
                            class="px-8 py-4 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-bold rounded-lg hover:bg-gray-50 transition-all duration-200 flex items-center justify-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Save Only
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Additional Actions --}}
    <div class="flex flex-col sm:flex-row gap-4">
        @if(isset($calculation))
            @php $downloadUrl = route('calculators.planting.downloadPdf', $calculation->id); @endphp
            <a href="{{ $downloadUrl }}" 
               class="flex-1 px-6 py-3 bg-white border-2 border-brand-300 hover:border-brand-400 text-brand-800 font-semibold rounded-lg hover:bg-brand-50 transition-all duration-200 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Download PDF
            </a>
        @endif
        
        @if($siteVisit)
            <a href="{{ route('clients.show', $siteVisit->client->id) }}" 
               class="flex-1 px-6 py-3 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all duration-200 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Client
            </a>
        @endif
    </div>
</div>
@endsection
