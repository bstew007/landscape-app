@extends('layouts.sidebar')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-4 mb-4">
            <div class="flex-shrink-0 w-16 h-16 rounded-xl bg-gradient-to-br from-amber-700 to-orange-800 flex items-center justify-center shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Paver Patio Estimate</h1>
                <p class="text-gray-600 mt-1">Installation calculation summary</p>
            </div>
        </div>

        @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])
    </div>

    {{-- Final Price Card --}}
    <div class="bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-amber-200 rounded-xl shadow-md p-8 mb-8">
        <div class="text-center">
            <p class="text-lg font-semibold text-gray-700 mb-2">Final Price</p>
            <p class="text-5xl font-bold bg-gradient-to-r from-amber-700 to-orange-700 bg-clip-text text-transparent">
                ${{ number_format($data['final_price'], 2) }}
            </p>
        </div>
    </div>

    {{-- Project Specifications --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-blue-600 to-blue-700 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Project Specifications</h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Patio Area</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($data['area_sqft'], 0) }}</div>
                <div class="text-xs text-gray-500">square feet</div>
            </div>

            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Pavers</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($data['paver_count']) }}</div>
                <div class="text-xs text-gray-500">stones</div>
            </div>

            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Base Gravel</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($data['base_tons']) }}</div>
                <div class="text-xs text-gray-500">tons</div>
            </div>

            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-4">
                <div class="text-sm text-gray-600 mb-1">Edge Restraint</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($data['edge_lf'], 1) }}</div>
                <div class="text-xs text-gray-500">linear feet</div>
            </div>
        </div>
    </div>

    {{-- Materials --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-amber-600 to-orange-700 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Materials</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="text-left py-3 px-4 font-semibold text-gray-700">Material</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Quantity</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Unit Cost</th>
                        <th class="text-right py-3 px-4 font-semibold text-gray-700">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['materials'] as $name => $material)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4 font-medium text-gray-900">{{ $name }}</td>
                            <td class="py-3 px-4 text-right text-gray-700">{{ number_format($material['qty'], 2) }}</td>
                            <td class="py-3 px-4 text-right text-gray-700">${{ number_format($material['unit_cost'], 2) }}</td>
                            <td class="py-3 px-4 text-right font-semibold text-gray-900">${{ number_format($material['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-gray-300 bg-gray-50">
                        <td colspan="3" class="py-4 px-4 text-right font-bold text-gray-900">Material Total:</td>
                        <td class="py-4 px-4 text-right font-bold text-amber-700 text-lg">${{ number_format($data['material_total'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Labor Breakdown --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-purple-600 to-purple-700 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Labor Breakdown</h2>
        </div>

        <div class="space-y-2 mb-4">
            @foreach ($data['labor_by_task'] as $task => $hours)
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-gray-700 capitalize">{{ str_replace('_', ' ', $task) }}</span>
                    <span class="font-semibold text-gray-900">{{ number_format($hours, 2) }} hrs</span>
                </div>
            @endforeach
        </div>

        <div class="border-t-2 border-gray-200 pt-4 space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-700">Base Labor Hours</span>
                <span class="font-semibold text-gray-900">{{ number_format($data['labor_hours'], 2) }} hrs</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-700">Overhead Hours</span>
                <span class="font-semibold text-gray-900">{{ number_format($data['overhead_hours'], 2) }} hrs</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-700">Drive Time Hours</span>
                <span class="font-semibold text-gray-900">{{ number_format($data['drive_time_hours'], 2) }} hrs</span>
            </div>
            <div class="flex justify-between pt-3 border-t border-gray-200">
                <span class="font-bold text-gray-900 text-lg">Total Labor Hours</span>
                <span class="font-bold text-purple-700 text-lg">{{ number_format($data['total_hours'], 2) }} hrs</span>
            </div>
            <div class="flex justify-between pt-2">
                <span class="font-bold text-gray-900 text-lg">Labor Cost</span>
                <span class="font-bold text-purple-700 text-lg">${{ number_format($data['labor_cost'], 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Pricing Summary --}}\n    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-green-600 to-green-700 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Pricing Summary</h2>
        </div>

        <div class="space-y-3">
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-700 text-lg">Labor Cost</span>
                <span class="font-semibold text-gray-900 text-lg">${{ number_format($data['labor_cost'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-2">
                <span class="text-gray-700 text-lg">Material Cost</span>
                <span class="font-semibold text-gray-900 text-lg">${{ number_format($data['material_total'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-3 border-t-2 border-gray-200">
                <span class="font-semibold text-gray-700 text-lg">Total Cost</span>
                <span class="font-bold text-gray-900 text-xl">${{ number_format($data['labor_cost'] + $data['material_total'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center py-4 border-t-2 border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 -mx-6 px-6 rounded-lg">
                <span class="font-bold text-gray-900 text-xl">Final Price</span>
                <span class="font-bold text-amber-700 text-2xl">${{ number_format($data['final_price'], 2) }}</span>
            </div>
        </div>
    </div>

    @if (!empty($data['job_notes']))
        <div class="bg-gradient-to-br from-amber-50 to-yellow-50 border-l-4 border-amber-400 rounded-lg shadow-sm p-6 mb-6">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-400 flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Job Notes</h3>
                    <p class="text-gray-800 whitespace-pre-line">{{ $data['job_notes'] }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Import to Estimate --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex-shrink-0 w-10 h-10 rounded-lg bg-gradient-to-br from-brand-600 to-brand-700 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Import to Estimate</h2>
                <p class="text-sm text-gray-600">Add this calculation to a project estimate</p>
            </div>
        </div>

        <form method="POST" action="{{ route('calculators.import_to_estimate') }}">
            @csrf
            <input type="hidden" name="calculation_id" value="{{ $calculation->id ?? '' }}">
            <input type="hidden" name="calculator_type" value="paver_patio">

            {{-- Target Estimate Selection --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Select Target Estimate:</label>
                <div class="flex gap-3">
                    <select name="estimate_id" required 
                            class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent transition">
                        <option value="">-- Choose an Estimate --</option>
                        @php
                            $estimates = $siteVisit->estimates ?? collect();
                        @endphp
                        @foreach($estimates as $est)
                            <option value="{{ $est->id }}">
                                #{{ $est->id }} - {{ $est->name }} ({{ ucfirst($est->status) }})
                            </option>
                        @endforeach
                    </select>
                    <a href="{{ route('estimates.create', ['site_visit_id' => $siteVisit->id]) }}" 
                       class="px-6 py-3 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-sm transition whitespace-nowrap flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Estimate
                    </a>
                </div>
                <p class="mt-2 text-sm text-gray-500">Choose an existing estimate or create a new one</p>
            </div>
            
            {{-- Work Area Name --}}
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Work Area Name:</label>
                <input type="text" 
                       name="area_name" 
                       value="Paver Patio - {{ date('M d, Y') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-transparent transition"
                       placeholder="e.g., Backyard Patio Installation">
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
                            <p class="text-sm text-gray-600 mt-1">Creates separate line items for each task with full detail (excavation, base, laying pavers, materials, overhead)</p>
                        </div>
                    </label>
                    <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50 cursor-pointer transition">
                        <input type="radio" name="import_type" value="collapsed" 
                               class="mt-1 text-gray-600 focus:ring-gray-500">
                        <div class="ml-4 flex-1">
                            <p class="font-bold text-gray-900">Collapsed (Legacy)</p>
                            <p class="text-sm text-gray-600 mt-1">Single line item with total price only</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-600">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Line items will be added to the selected estimate
                </p>
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-700 hover:to-brand-800 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import to Estimate
                </button>
            </div>
        </form>
    </div>

    {{-- Actions --}}
    @php $downloadUrl = isset($calculation) ? route('calculations.patio.downloadPdf', $calculation->id) : null; @endphp
    <div class="flex flex-col sm:flex-row gap-4">
        @if($downloadUrl)
            <a href="{{ $downloadUrl }}" 
               class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download PDF
            </a>
        @endif
        
        <a href="{{ route('site_visits.show', $siteVisit->id) }}" 
           class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Site Visit
        </a>
    </div>
</div>
@endsection
