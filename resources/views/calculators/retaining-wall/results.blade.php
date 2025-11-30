@extends('layouts.sidebar')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    {{-- Header with Gray Gradient Brick Wall Icon --}}
    <div class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 rounded-2xl shadow-2xl p-8">
        <div class="flex items-center gap-6">
            <div class="flex-shrink-0">
                <div class="bg-gradient-to-br from-gray-600 to-gray-800 p-6 rounded-xl shadow-lg">
                    <svg class="w-20 h-20 text-white" fill="none" stroke="currentColor" viewBox="0 0 64 64">
                        <g stroke-width="1.5">
                            {{-- Row 1 --}}
                            <rect x="2" y="2" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="16" y="2" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            <rect x="30" y="2" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="44" y="2" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            {{-- Row 2 (offset) --}}
                            <rect x="8" y="10" width="12" height="6" fill="currentColor" opacity="0.85"/>
                            <rect x="22" y="10" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="36" y="10" width="12" height="6" fill="currentColor" opacity="0.85"/>
                            <rect x="50" y="10" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            {{-- Row 3 --}}
                            <rect x="2" y="18" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            <rect x="16" y="18" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="30" y="18" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            <rect x="44" y="18" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            {{-- Row 4 (offset) --}}
                            <rect x="8" y="26" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="22" y="26" width="12" height="6" fill="currentColor" opacity="0.85"/>
                            <rect x="36" y="26" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="50" y="26" width="12" height="6" fill="currentColor" opacity="0.85"/>
                            {{-- Row 5 --}}
                            <rect x="2" y="34" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="16" y="34" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            <rect x="30" y="34" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="44" y="34" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            {{-- Row 6 (offset) --}}
                            <rect x="8" y="42" width="12" height="6" fill="currentColor" opacity="0.85"/>
                            <rect x="22" y="42" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="36" y="42" width="12" height="6" fill="currentColor" opacity="0.85"/>
                            <rect x="50" y="42" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            {{-- Row 7 --}}
                            <rect x="2" y="50" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            <rect x="16" y="50" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            <rect x="30" y="50" width="12" height="6" fill="currentColor" opacity="0.8"/>
                            <rect x="44" y="50" width="12" height="6" fill="currentColor" opacity="0.9"/>
                            {{-- Row 8 (offset) --}}
                            <rect x="8" y="58" width="12" height="4" fill="currentColor" opacity="0.9"/>
                            <rect x="22" y="58" width="12" height="4" fill="currentColor" opacity="0.85"/>
                            <rect x="36" y="58" width="12" height="4" fill="currentColor" opacity="0.9"/>
                            <rect x="50" y="58" width="12" height="4" fill="currentColor" opacity="0.85"/>
                        </g>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h1 class="text-4xl font-bold text-white mb-2">Retaining Wall Calculation</h1>
                <p class="text-gray-300 text-lg">Complete wall estimate with materials, labor breakdown, and pricing</p>
            </div>
        </div>
    </div>

    @include('calculators.partials.client_info', ['siteVisit' => $siteVisit])

    {{-- Final Price Card --}}
    <div class="bg-gradient-to-r from-gray-700 to-gray-800 rounded-xl shadow-xl p-8 text-center">
        <p class="text-gray-300 text-lg mb-2">Final Price</p>
        <p class="text-6xl font-bold text-white">${{ number_format($data['final_price'], 2) }}</p>
        <div class="mt-4 flex items-center justify-center gap-6 text-sm text-gray-300">
            <div>
                <span class="font-semibold">Labor:</span>
                <span>${{ number_format($data['labor_cost'], 2) }}</span>
            </div>
            <div class="w-1 h-4 bg-gray-600"></div>
            <div>
                <span class="font-semibold">Materials:</span>
                <span>${{ number_format($data['material_total'], 2) }}</span>
            </div>
            <div class="w-1 h-4 bg-gray-600"></div>
            <div>
                <span class="font-semibold">Total Hours:</span>
                <span>{{ number_format($data['total_hours'], 2) }}</span>
            </div>
        </div>
    </div>

    {{-- Project Specifications --}}
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
        <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4">
            <h2 class="text-2xl font-bold text-white">Project Specifications</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-3xl font-bold text-gray-800">{{ number_format($data['length'] * $data['height'], 2) }}</div>
                    <div class="text-sm text-gray-600 mt-1">Wall Area (sqft)</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-3xl font-bold text-gray-800">{{ number_format($data['block_count']) }}</div>
                    <div class="text-sm text-gray-600 mt-1">Wall Blocks</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-3xl font-bold text-gray-800">{{ ucfirst($data['block_system']) }}</div>
                    <div class="text-sm text-gray-600 mt-1">Block System</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-3xl font-bold text-gray-800">{{ ucfirst($data['block_brand']) }}</div>
                    <div class="text-sm text-gray-600 mt-1">Block Brand</div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-3xl font-bold text-gray-800">{{ number_format($data['length'], 1) }}ft</div>
                    <div class="text-sm text-gray-600 mt-1">Wall Length</div>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-3xl font-bold text-gray-800">{{ number_format($data['height'], 1) }}ft</div>
                    <div class="text-sm text-gray-600 mt-1">Wall Height</div>
                </div>
                @if($data['cap_count'] > 0)
                <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-3xl font-bold text-gray-800">{{ number_format($data['cap_count']) }}</div>
                    <div class="text-sm text-gray-600 mt-1">Capstones</div>
                </div>
                @endif
                @if($data['geogrid_layers'] > 0)
                <div class="text-center p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-3xl font-bold text-gray-800">{{ $data['geogrid_layers'] }}</div>
                    <div class="text-sm text-gray-600 mt-1">Geogrid Layers</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Allan Block Components (if applicable) --}}
    @if(($data['block_system'] ?? 'standard') === 'allan_block')
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-blue-200">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <h2 class="text-2xl font-bold text-white">Allan Block Components</h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-300">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Component</th>
                            <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Quantity</th>
                            <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Labor Hours</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @if(($data['ab_straight_sqft'] ?? 0) > 0)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 text-gray-800">Straight Wall Area</td>
                            <td class="py-3 px-4 text-right text-gray-800 font-semibold">{{ number_format($data['ab_straight_sqft'], 2) }} sqft</td>
                            <td class="py-3 px-4 text-right text-gray-800 font-semibold">{{ number_format($data['labor_by_task']['ab_straight_wall'] ?? 0, 2) }} hrs</td>
                        </tr>
                        @endif
                        @if(($data['ab_curved_sqft'] ?? 0) > 0)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 text-gray-800">Curved Wall Area</td>
                            <td class="py-3 px-4 text-right text-gray-800 font-semibold">{{ number_format($data['ab_curved_sqft'], 2) }} sqft</td>
                            <td class="py-3 px-4 text-right text-gray-800 font-semibold">{{ number_format($data['labor_by_task']['ab_curved_wall'] ?? 0, 2) }} hrs</td>
                        </tr>
                        @endif
                        @if(($data['ab_step_count'] ?? 0) > 0)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 text-gray-800">Stairs</td>
                            <td class="py-3 px-4 text-right text-gray-800 font-semibold">{{ $data['ab_step_count'] }} steps</td>
                            <td class="py-3 px-4 text-right text-gray-800 font-semibold">{{ number_format($data['labor_by_task']['ab_stairs'] ?? 0, 2) }} hrs</td>
                        </tr>
                        @endif
                        @if(($data['ab_column_count'] ?? 0) > 0)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 text-gray-800">Columns</td>
                            <td class="py-3 px-4 text-right text-gray-800 font-semibold">{{ $data['ab_column_count'] }} columns</td>
                            <td class="py-3 px-4 text-right text-gray-800 font-semibold">{{ number_format($data['labor_by_task']['ab_columns'] ?? 0, 2) }} hrs</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Materials Breakdown --}}
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
        <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4">
            <h2 class="text-2xl font-bold text-white">Materials</h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-300">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Material</th>
                            <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Quantity</th>
                            <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Unit Cost</th>
                            <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($data['materials'] as $name => $item)
                            @if(is_array($item) && isset($item['qty']) && $item['qty'] > 0)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-4 text-gray-800">
                                    {{ $name }}
                                    @if(!empty($item['is_custom']))
                                        <span class="ml-2 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full">Custom</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right text-gray-800">{{ number_format($item['qty'], 2) }}</td>
                                <td class="py-3 px-4 text-right text-gray-800">${{ number_format($item['unit_cost'], 2) }}</td>
                                <td class="py-3 px-4 text-right font-semibold text-gray-900">${{ number_format($item['total'], 2) }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gradient-to-r from-gray-100 to-gray-200 border-t-2 border-gray-300">
                            <td colspan="3" class="py-4 px-4 text-left font-bold text-gray-900">Material Total</td>
                            <td class="py-4 px-4 text-right font-bold text-gray-900 text-lg">${{ number_format($data['material_total'], 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Labor Breakdown --}}
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
            <h2 class="text-2xl font-bold text-white">Labor Breakdown</h2>
        </div>
        <div class="p-6">
            @if(!empty($data['labor_tasks']))
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b-2 border-gray-300">
                            <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Task</th>
                            <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Quantity</th>
                            <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Hours</th>
                            <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Rate</th>
                            <th class="text-right py-3 px-4 text-sm font-semibold text-gray-700">Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($data['labor_tasks'] as $task)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900">{{ $task['task_name'] }}</div>
                                <div class="text-xs text-gray-500">{{ $task['description'] }}</div>
                            </td>
                            <td class="py-3 px-4 text-right text-gray-700">{{ number_format($task['quantity'], 2) }} {{ $task['unit'] }}</td>
                            <td class="py-3 px-4 text-right text-gray-700">{{ number_format($task['hours'], 2) }}</td>
                            <td class="py-3 px-4 text-right text-gray-700">${{ number_format($task['hourly_rate'], 2) }}/hr</td>
                            <td class="py-3 px-4 text-right font-semibold text-gray-900">${{ number_format($task['total_cost'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            {{-- Fallback to old format --}}
            <div class="space-y-2">
                @foreach($data['labor_by_task'] as $task => $hours)
                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                    <span class="text-gray-800 capitalize">{{ str_replace('_', ' ', $task) }}</span>
                    <span class="font-semibold text-gray-900">{{ number_format($hours, 2) }} hrs</span>
                </div>
                @endforeach
            </div>
            @endif

            <div class="mt-6 space-y-3 pt-4 border-t-2 border-gray-300">
                <div class="flex justify-between text-gray-700">
                    <span>Base Labor Hours</span>
                    <span class="font-semibold">{{ number_format($data['labor_hours'], 2) }} hrs</span>
                </div>
                <div class="flex justify-between text-gray-700">
                    <span>Overhead Hours</span>
                    <span class="font-semibold">{{ number_format($data['overhead_hours'], 2) }} hrs</span>
                </div>
                <div class="flex justify-between text-gray-700">
                    <span>Drive Time Hours</span>
                    <span class="font-semibold">{{ number_format($data['drive_time_hours'], 2) }} hrs</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-gray-900 pt-3 border-t border-gray-300">
                    <span>Total Labor Hours</span>
                    <span>{{ number_format($data['total_hours'], 2) }} hrs</span>
                </div>
                <div class="flex justify-between text-xl font-bold text-purple-700 pt-2 border-t-2 border-purple-300">
                    <span>Total Labor Cost</span>
                    <span>${{ number_format($data['labor_cost'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Pricing Summary --}}
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
        <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4">
            <h2 class="text-2xl font-bold text-white">Pricing Summary</h2>
        </div>
        <div class="p-6 space-y-3">
            <div class="flex justify-between items-center text-lg">
                <span class="text-gray-700">Labor Cost</span>
                <span class="font-semibold text-gray-900">${{ number_format($data['labor_cost'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-lg">
                <span class="text-gray-700">Material Cost</span>
                <span class="font-semibold text-gray-900">${{ number_format($data['material_total'], 2) }}</span>
            </div>
            <div class="flex justify-between items-center text-lg pt-3 border-t-2 border-gray-300">
                <span class="text-gray-700 font-semibold">Subtotal</span>
                <span class="font-semibold text-gray-900">${{ number_format($data['labor_cost'] + $data['material_total'], 2) }}</span>
            </div>
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 rounded-lg p-4 mt-4">
                <div class="flex justify-between items-center">
                    <span class="text-white text-xl font-bold">Final Price</span>
                    <span class="text-white text-3xl font-bold">${{ number_format($data['final_price'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Job Notes --}}
    @if(!empty($data['job_notes']))
    <div class="bg-yellow-50 border-l-4 border-yellow-400 rounded-lg shadow-lg p-6">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-lg font-bold text-yellow-900 mb-2">Job Notes</h3>
                <p class="text-gray-800 whitespace-pre-line">{{ $data['job_notes'] }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Enhanced Import to Estimate Section --}}
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
        <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4">
            <h2 class="text-2xl font-bold text-white">Import to Estimate</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('calculations.importToEstimate') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="calculation_id" value="{{ $calculation->id ?? '' }}">
                <input type="hidden" name="calculation_type" value="retaining_wall">

                {{-- Estimate Selection --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Select Target Estimate <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="estimate_id" 
                        required 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors"
                    >
                        <option value="">Choose an estimate...</option>
                        @if(isset($siteVisit) && $siteVisit->client && $siteVisit->client->estimates)
                            @foreach($siteVisit->client->estimates as $estimate)
                                <option value="{{ $estimate->id }}">
                                    Estimate #{{ $estimate->id }} - {{ $estimate->title ?? 'Untitled' }}
                                    ({{ $estimate->created_at->format('M d, Y') }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <p class="text-xs text-gray-500 mt-1.5">Select which estimate to import this calculation into</p>
                </div>

                {{-- Work Area Name --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Work Area Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="work_area_name" 
                        required 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 transition-colors" 
                        placeholder="e.g., Backyard Retaining Wall, Front Yard Wall"
                        value="Retaining Wall - {{ ucfirst($data['block_system']) }} {{ ucfirst($data['block_brand']) }}"
                    >
                    <p class="text-xs text-gray-500 mt-1.5">This will be the name of the work area in the estimate</p>
                </div>

                {{-- Import Mode --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Import Mode</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-gray-500 transition-colors bg-white">
                            <input type="radio" name="import_mode" value="granular" class="mt-1 mr-3" checked>
                            <div>
                                <div class="font-semibold text-gray-900">Granular (Detailed)</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    Creates separate line items for each material and labor task. Best for detailed estimates with full transparency.
                                </div>
                            </div>
                        </label>
                        <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-gray-500 transition-colors bg-white">
                            <input type="radio" name="import_mode" value="collapsed" class="mt-1 mr-3">
                            <div>
                                <div class="font-semibold text-gray-900">Collapsed (Summary)</div>
                                <div class="text-sm text-gray-600 mt-1">
                                    Creates a single line item with the total price. Best for simplified estimates or when showing less detail.
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        <svg class="inline w-4 h-4 text-gray-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        This will create new line items in the selected estimate
                    </p>
                    <button 
                        type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-gray-700 to-gray-800 hover:from-gray-800 hover:to-gray-900 text-white rounded-lg shadow-lg transition-all transform hover:scale-105 font-semibold"
                    >
                        Import to Estimate
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex items-center justify-between gap-4 pt-6 border-t-2 border-gray-300">
        <a 
            href="{{ route('site-visits.show', $siteVisit->id) }}" 
            class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors font-medium inline-flex items-center gap-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Site Visit
        </a>
        
        @php $downloadUrl = isset($calculation) ? route('calculations.wall.downloadPdf', $calculation->id) : null; @endphp
        @if($downloadUrl)
        <a 
            href="{{ $downloadUrl }}" 
            class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-lg shadow-lg transition-all transform hover:scale-105 font-semibold inline-flex items-center gap-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Download PDF
        </a>
        @endif
    </div>
</div>
@endsection
