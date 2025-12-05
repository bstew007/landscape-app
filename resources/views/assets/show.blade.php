@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    {{-- Modern Branded Header --}}
    <section class="rounded-[20px] sm:rounded-[28px] lg:rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-6">
            <div class="h-16 w-16 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0 overflow-hidden">
                @switch($asset->type)
                    @case('crew_truck')
                        <img src="{{ asset('images/crewtruck.jpg') }}" alt="Crew Truck" class="h-full w-full object-contain p-1">
                    @break
                    @case('dump_truck')
                        <img src="{{ asset('images/dumptruck.jpg') }}" alt="Dump Truck" class="h-full w-full object-contain p-1">
                    @break
                    @case('skid_steer')
                        <img src="{{ asset('images/skid.jpg') }}" alt="Skid Steer" class="h-full w-full object-contain p-1">
                    @break
                    @case('excavator')
                        <img src="{{ asset('images/excavator.jpg') }}" alt="Excavator" class="h-full w-full object-contain p-1">
                    @break
                    @case('enclosed_trailer')
                        <img src="{{ asset('images/enlosed.png') }}" alt="Enclosed Trailer" class="h-full w-full object-contain p-1">
                    @break
                    @case('dump_trailer')
                    @case('equipment_trailer')
                        <img src="{{ asset('images/trailer.jpg') }}" alt="Trailer" class="h-full w-full object-contain p-1">
                    @break
                    @case('mower')
                        <img src="{{ asset('images/mower.png') }}" alt="Mower" class="h-full w-full object-contain p-1">
                    @break
                    @default
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8 text-white">
                            <path d="M14.7 6.3a5 5 0 1 0-8.4 5.4l-4 4a2 2 0 1 0 2.8 2.8l4-4a5 5 0 0 0 5.6-8.2z"/>
                        </svg>
                @endswitch
            </div>
            <div class="flex-1">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Asset Management</p>
                <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">{{ $asset->name }}</h1>
                <p class="text-sm text-brand-100/85 mt-1">
                    {{ ucwords(str_replace('_', ' ', $asset->type)) }}
                    @if($asset->model)
                        <span class="text-brand-200">·</span> {{ $asset->model }}
                    @endif
                    @if($asset->identifier)
                        <span class="text-brand-200">·</span> {{ $asset->identifier }}
                    @endif
                </p>
            </div>
            <div class="flex gap-2 flex-wrap">
                @php
                    $activeCheckout = $asset->usageLogs()->where('status', 'checked_out')->latest()->first();
                @endphp
                
                @if($activeCheckout)
                    <x-brand-button href="{{ route('assets.checkin', $asset) }}" variant="primary" class="bg-green-600 hover:bg-green-700 border-green-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        Check In
                    </x-brand-button>
                @else
                    <x-brand-button href="{{ route('assets.checkout', $asset) }}" variant="primary">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Check Out
                    </x-brand-button>
                @endif
                
                <x-brand-button href="{{ route('assets.edit', $asset) }}" variant="outline" class="border-white/30 text-white hover:bg-white/10">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit
                </x-brand-button>
                <form action="{{ route('assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Remove this asset?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <x-brand-button type="submit" variant="outline" class="border-red-300/50 text-red-100 hover:bg-red-500/20">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                        Delete
                    </x-brand-button>
                </form>
            </div>
        </div>
    </section>

    {{-- Stats Cards with Brand Colors --}}
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-5">
            <h2 class="text-xs font-semibold text-brand-500 uppercase tracking-wide">Status</h2>
            <p class="text-2xl font-bold text-brand-900 mt-2">{{ ucwords(str_replace('_', ' ', $asset->status)) }}</p>
            <div class="mt-3 space-y-1 text-sm text-brand-700">
                <p><strong class="text-brand-800">Assigned:</strong> {{ $asset->assigned_to ?: 'Unassigned' }}</p>
                <p><strong class="text-brand-800">Mileage / Hours:</strong> {{ $asset->mileage_hours ?: 'N/A' }}</p>
            </div>
        </div>
        <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-5">
            <h2 class="text-xs font-semibold text-brand-500 uppercase tracking-wide">Maintenance</h2>
            <p class="text-2xl font-bold text-brand-900 mt-2">{{ optional($asset->next_service_date)->format('M j, Y') ?? 'No date' }}</p>
            <p class="text-sm text-brand-600 mt-3">{{ Str::limit($asset->notes, 80) }}</p>
        </div>
        <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-5">
            <h2 class="text-xs font-semibold text-brand-500 uppercase tracking-wide">Issues</h2>
            <p class="text-2xl font-bold text-brand-900 mt-2">{{ $asset->issues->where('status', '!=', 'resolved')->count() }} open</p>
            <div class="mt-3 space-y-1 text-sm text-brand-700">
                <p><strong class="text-brand-800">Purchase:</strong> {{ optional($asset->purchase_date)->format('M j, Y') ?? 'N/A' }}</p>
                <p><strong class="text-brand-800">Price:</strong> {{ $asset->purchase_price ? '$' . number_format($asset->purchase_price, 2) : 'N/A' }}</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            {{-- Usage Logs Section --}}
            <section class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-brand-900">Usage & Sign-Out Log</h2>
                </div>
                
                @php
                    $activeCheckout = $asset->usageLogs()->where('status', 'checked_out')->latest()->first();
                    $recentLogs = $asset->usageLogs()->with('user')->latest()->limit(10)->get();
                @endphp

                @if($activeCheckout)
                    <div class="mb-4 p-4 rounded-xl bg-green-50 border-2 border-green-200">
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-green-600 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>
                            </svg>
                            <div class="flex-1">
                                <p class="font-semibold text-green-900">Currently Checked Out</p>
                                <p class="text-sm text-green-700 mt-1">
                                    <strong>User:</strong> {{ $activeCheckout->user->name ?? 'Unknown' }}<br>
                                    <strong>Since:</strong> {{ $activeCheckout->checked_out_at->format('M j, Y g:i A') }}<br>
                                    @if($activeCheckout->mileage_out)
                                        <strong>Starting Mileage/Hours:</strong> {{ number_format($activeCheckout->mileage_out) }}
                                    @endif
                                </p>
                            </div>
                            <x-brand-button href="{{ route('assets.checkin', $asset) }}" variant="primary" class="bg-green-600 hover:bg-green-700 border-green-600">
                                Check In
                            </x-brand-button>
                        </div>
                    </div>
                @endif

                <div class="space-y-3">
                    @forelse($recentLogs as $log)
                        <div class="border-2 @if($log->isCheckedOut()) border-green-200 bg-green-50/30 @else border-brand-100 @endif rounded-xl p-4 hover:border-brand-300 transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-brand-900">{{ $log->user->name ?? 'Unknown User' }}</p>
                                        <span class="text-xs px-2 py-0.5 rounded-full @if($log->isCheckedOut()) bg-green-100 text-green-800 @else bg-gray-100 text-gray-700 @endif">
                                            {{ $log->isCheckedOut() ? 'Checked Out' : 'Returned' }}
                                        </span>
                                    </div>
                                    
                                    <div class="mt-2 text-sm text-brand-700 space-y-1">
                                        <p><strong>Out:</strong> {{ $log->checked_out_at->format('M j, Y g:i A') }}</p>
                                        @if($log->checked_in_at)
                                            <p><strong>In:</strong> {{ $log->checked_in_at->format('M j, Y g:i A') }}</p>
                                            <p class="text-brand-600">
                                                <strong>Duration:</strong> {{ $log->checked_out_at->diffForHumans($log->checked_in_at, true) }}
                                            </p>
                                        @endif
                                        
                                        @if($log->mileage_out || $log->mileage_in)
                                            <p>
                                                <strong>Mileage/Hours:</strong> 
                                                {{ $log->mileage_out ? number_format($log->mileage_out) : 'N/A' }}
                                                @if($log->mileage_in)
                                                    → {{ number_format($log->mileage_in) }}
                                                    <span class="text-brand-500">({{ number_format($log->mileage_in - $log->mileage_out) }} used)</span>
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                    
                                    @if($log->notes)
                                        <div class="mt-2 p-2 bg-brand-50 rounded-lg">
                                            <p class="text-xs font-semibold text-brand-700">Notes:</p>
                                            <p class="text-sm text-brand-800 whitespace-pre-line">{{ $log->notes }}</p>
                                        </div>
                                    @endif

                                    @if($log->inspection_data && count($log->inspection_data) > 0)
                                        <details class="mt-2">
                                            <summary class="text-xs font-semibold text-brand-600 cursor-pointer hover:text-brand-800">
                                                View Inspection Details ({{ count($log->inspection_data) }} items checked)
                                            </summary>
                                            <div class="mt-2 p-2 bg-brand-50 rounded-lg">
                                                <div class="grid grid-cols-2 gap-1 text-xs text-brand-700">
                                                    @foreach($log->inspection_data as $key => $value)
                                                        @if($value)
                                                            <div class="flex items-center gap-1">
                                                                <svg class="h-3 w-3 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                                    <path d="M5 13l4 4L19 7"/>
                                                                </svg>
                                                                <span>{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </details>
                                    @endif
                                </div>

                                <div class="flex flex-col gap-2 flex-shrink-0">
                                    <a href="{{ route('assets.usage-logs.edit', [$asset, $log]) }}" 
                                       class="text-brand-600 hover:text-brand-800 text-xs font-medium flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('assets.usage-logs.destroy', [$asset, $log]) }}" method="POST" 
                                          onsubmit="return confirm('Delete this usage log?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-brand-500 text-center py-4">No usage history yet.</p>
                    @endforelse
                </div>
            </section>

            {{-- Expenses Section --}}
            <section class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-brand-900">Expenses</h2>
                    <x-brand-button href="{{ route('assets.expenses.create', $asset) }}" variant="primary">
                        Add Expense
                    </x-brand-button>
                </div>

                @php
                    $expenses = $asset->expenses()->with(['submittedBy', 'assetIssue', 'attachments'])->latest('expense_date')->get();
                    $totalExpenses = $expenses->sum('amount');
                    $fuelExpenses = $expenses->where('category', 'fuel')->sum('amount');
                    $repairExpenses = $expenses->where('category', 'repairs')->sum('amount');
                    $generalExpenses = $expenses->where('category', 'general')->sum('amount');
                @endphp

                {{-- Summary Cards --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
                    <div class="p-3 rounded-lg bg-brand-50 border border-brand-200">
                        <p class="text-xs text-brand-600 font-semibold">Total</p>
                        <p class="text-lg font-bold text-brand-900">${{ number_format($totalExpenses, 2) }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-blue-50 border border-blue-200">
                        <p class="text-xs text-blue-600 font-semibold">Fuel</p>
                        <p class="text-lg font-bold text-blue-900">${{ number_format($fuelExpenses, 2) }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-orange-50 border border-orange-200">
                        <p class="text-xs text-orange-600 font-semibold">Repairs</p>
                        <p class="text-lg font-bold text-orange-900">${{ number_format($repairExpenses, 2) }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-green-50 border border-green-200">
                        <p class="text-xs text-green-600 font-semibold">General</p>
                        <p class="text-lg font-bold text-green-900">${{ number_format($generalExpenses, 2) }}</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($expenses as $expense)
                        <div class="border-2 border-brand-100 rounded-xl p-4 hover:border-brand-300 transition">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                                            @if($expense->category === 'fuel') bg-blue-100 text-blue-800
                                            @elseif($expense->category === 'repairs') bg-orange-100 text-orange-800
                                            @else bg-green-100 text-green-800
                                            @endif">
                                            {{ ucfirst($expense->category) }}
                                            @if($expense->subcategory)
                                                : {{ $expense->subcategory }}
                                            @endif
                                        </span>
                                        <p class="font-bold text-brand-900 text-lg">${{ number_format($expense->amount, 2) }}</p>
                                        @if($expense->isApproved())
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-800">✓ Approved</span>
                                        @endif
                                        @if($expense->is_reimbursable)
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-800">Reimbursable</span>
                                        @endif
                                        @if($expense->isSyncedToQbo())
                                            <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800">Synced to QBO</span>
                                        @endif
                                    </div>
                                    
                                    <div class="mt-2 text-sm text-brand-700 space-y-1">
                                        <p><strong>Date:</strong> {{ $expense->expense_date->format('M j, Y') }}</p>
                                        @if($expense->vendor)
                                            <p><strong>Vendor:</strong> {{ $expense->vendor }}</p>
                                        @endif
                                        @if($expense->receipt_number)
                                            <p><strong>Receipt #:</strong> {{ $expense->receipt_number }}</p>
                                        @endif
                                        @if($expense->odometer_hours)
                                            <p><strong>Odometer/Hours:</strong> {{ number_format($expense->odometer_hours) }}</p>
                                        @endif
                                        @if($expense->assetIssue)
                                            <p><strong>Linked Issue:</strong> {{ $expense->assetIssue->title }}</p>
                                        @endif
                                        <p><strong>Submitted by:</strong> {{ $expense->submittedBy->name ?? 'Unknown' }}</p>
                                    </div>
                                    
                                    @if($expense->description)
                                        <div class="mt-2 p-2 bg-brand-50 rounded-lg">
                                            <p class="text-xs font-semibold text-brand-700">Description:</p>
                                            <p class="text-sm text-brand-800">{{ $expense->description }}</p>
                                        </div>
                                    @endif

                                    @if($expense->attachments->count() > 0)
                                        <div class="mt-2">
                                            <p class="text-xs font-semibold text-brand-700 mb-1">Attachments:</p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($expense->attachments as $attachment)
                                                    <a href="{{ route('assets.expenses.attachments.download', [$asset, $expense, $attachment]) }}" 
                                                       class="inline-flex items-center gap-1 px-2 py-1 bg-brand-100 hover:bg-brand-200 rounded text-xs text-brand-800 transition-colors">
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                        </svg>
                                                        {{ Str::limit($attachment->file_name, 20) }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-col gap-2 flex-shrink-0">
                                    @if(!$expense->isApproved())
                                        <form action="{{ route('assets.expenses.approve', [$asset, $expense]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-800 text-xs font-medium flex items-center gap-1">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Approve
                                            </button>
                                        </form>
                                    @endif
                                    @if($expense->isApproved() && !$expense->isSyncedToQbo())
                                        <form action="{{ route('assets.expenses.sync-qbo', [$asset, $expense]) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:text-blue-800 text-xs font-medium flex items-center gap-1">
                                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                Sync to QBO
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('assets.expenses.edit', [$asset, $expense]) }}" 
                                       class="text-brand-600 hover:text-brand-800 text-xs font-medium flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                        Edit
                                    </a>
                                    <form action="{{ route('assets.expenses.destroy', [$asset, $expense]) }}" method="POST" 
                                          onsubmit="return confirm('Delete this expense?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium flex items-center gap-1">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="mt-2 text-sm text-brand-500">No expenses recorded yet.</p>
                            <p class="text-xs text-brand-400 mt-1">Add fuel, repairs, or other expenses to track costs.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-brand-900">Maintenance Schedule</h2>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach ($asset->maintenances as $maintenance)
                        <div class="rounded-xl border-2 border-brand-100 p-4 hover:border-brand-300 transition">
                            <p class="text-xs text-brand-500 uppercase tracking-wide">{{ $maintenance->type ?? 'Service' }}</p>
                            <p class="font-bold text-brand-900 mt-1">{{ optional($maintenance->completed_at ?? $maintenance->scheduled_at)->format('M j, Y') ?? 'No date' }}</p>
                            <p class="text-xs text-brand-500 mt-2">Hours: {{ $maintenance->mileage_hours ?? 'N/A' }}</p>
                            <p class="text-sm text-brand-700 mt-2">{{ $maintenance->notes ?: 'No notes' }}</p>
                        </div>
                    @endforeach
                </div>
                <form action="{{ route('assets.maintenance.store', $asset) }}" method="POST" class="mt-6 grid md:grid-cols-2 gap-4 p-4 rounded-xl bg-brand-50/50 border-2 border-brand-100">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-brand-800 mb-1">Maintenance Type</label>
                        <input type="text" name="type" class="form-input w-full" placeholder="Inspection / Service">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-800 mb-1">Scheduled Date</label>
                        <input type="date" name="scheduled_at" class="form-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-800 mb-1">Completed Date</label>
                        <input type="date" name="completed_at" class="form-input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-800 mb-1">Mileage / Hours</label>
                        <input type="number" name="mileage_hours" class="form-input w-full">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-brand-800 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="form-textarea w-full"></textarea>
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <x-brand-button type="submit">Add Maintenance</x-brand-button>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-brand-900">Issues & Repairs</h2>
                </div>
                <div class="space-y-3">
                    @forelse ($asset->issues as $issue)
                        <div class="border-2 border-brand-100 rounded-xl p-4 hover:border-brand-300 transition">
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-brand-900">{{ $issue->title }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full
                                    @class([
                                        'bg-red-100 text-red-800' => $issue->severity === 'critical',
                                        'bg-orange-100 text-orange-800' => $issue->severity === 'high',
                                        'bg-yellow-100 text-yellow-800' => $issue->severity === 'normal',
                                        'bg-gray-100 text-gray-700' => $issue->severity === 'low',
                                    ])">
                                    {{ ucfirst($issue->severity) }}
                                </span>
                            </div>
                            <p class="text-sm text-brand-700 mt-2">{{ $issue->description ?: 'No description' }}</p>
                            <p class="text-xs text-brand-500 mt-2">
                                Status: {{ ucwords(str_replace('_', ' ', $issue->status)) }} · Reported {{ optional($issue->reported_on)->format('M j, Y') ?? 'N/A' }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-brand-500">No issues logged.</p>
                    @endforelse
                </div>
                <form action="{{ route('assets.issues.store', $asset) }}" method="POST" class="mt-6 grid md:grid-cols-2 gap-4 p-4 rounded-xl bg-brand-50/50 border-2 border-brand-100">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-brand-800 mb-1">Title</label>
                        <input type="text" name="title" class="form-input w-full" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-800 mb-1">Severity</label>
                        <select name="severity" class="form-select w-full mt-1">
                            @foreach ($issueSeverities as $severity)
                                <option value="{{ $severity }}">{{ ucfirst($severity) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-800 mb-1">Status</label>
                        <select name="status" class="form-select w-full">
                            @foreach ($issueStatuses as $status)
                                <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-800 mb-1">Reported On</label>
                        <input type="date" name="reported_on" class="form-input w-full">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-brand-800 mb-1">Description</label>
                        <textarea name="description" rows="3" class="form-textarea w-full"></textarea>
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <x-brand-button type="submit">Log Issue</x-brand-button>
                    </div>
                </form>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-5">
                <h2 class="text-lg font-bold text-brand-900 mb-4">Attachments & Docs</h2>
                <div class="space-y-3">
                    @forelse ($asset->attachments as $attachment)
                        <div class="border-2 border-brand-100 rounded-xl p-3 flex items-center justify-between hover:border-brand-300 transition">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-brand-900 truncate">{{ $attachment->label }}</p>
                                <p class="text-xs text-brand-500">{{ $attachment->mime_type }} · {{ number_format(($attachment->size ?? 0) / 1024, 1) }} KB</p>
                            </div>
                            <div class="flex gap-2 flex-shrink-0">
                                <a href="{{ $attachment->url }}" target="_blank" class="text-brand-600 hover:text-brand-800 text-sm font-medium">Open</a>
                                <form action="{{ route('assets.attachments.destroy', [$asset, $attachment]) }}" method="POST" onsubmit="return confirm('Delete file?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-brand-500">No files uploaded.</p>
                    @endforelse
                </div>

                <form action="{{ route('assets.attachments.store', $asset) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3 p-4 rounded-xl bg-brand-50/50 border-2 border-brand-100">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-brand-800 mb-1">Label</label>
                        <input type="text" name="label" class="form-input w-full" placeholder="Insurance card, inspection, etc.">
                    </div>
                    <div>
                        <input type="file" name="file" required class="form-input w-full">
                    </div>
                    <x-brand-button type="submit" class="w-full justify-center">Upload File</x-brand-button>
                </form>
            </section>

            <section class="rounded-2xl bg-blue-50 border-2 border-blue-200 shadow-sm p-5">
                <h2 class="text-lg font-bold text-blue-900 mb-4">Linked Assets</h2>
                
                {{-- Assets this contains/carries --}}
                @if($asset->linkedAssets->count() > 0)
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-brand-700 mb-2">Contains/Carries:</h3>
                        <div class="space-y-2">
                            @foreach($asset->linkedAssets as $linked)
                                <div class="border-2 border-brand-100 rounded-xl p-3 flex items-center justify-between hover:border-brand-300 transition">
                                    <div class="flex-1">
                                        <p class="font-semibold text-brand-900">{{ $linked->name }}</p>
                                        <p class="text-xs text-brand-500">{{ ucwords(str_replace('_', ' ', $linked->type)) }}@if($linked->pivot->relationship_type) · {{ ucwords($linked->pivot->relationship_type) }}@endif</p>
                                        @if($linked->pivot->notes)
                                            <p class="text-xs text-brand-600 mt-1">{{ $linked->pivot->notes }}</p>
                                        @endif
                                    </div>
                                    <div class="flex gap-2 flex-shrink-0">
                                        <a href="{{ route('assets.show', $linked) }}" class="text-brand-600 hover:text-brand-800 text-sm font-medium">View</a>
                                        <form action="{{ route('assets.unlink', [$asset, $linked]) }}" method="POST" onsubmit="return confirm('Unlink this asset?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Unlink</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Assets that contain/carry this asset --}}
                @if($asset->parentAssets->count() > 0)
                    <div class="mb-4">
                        <h3 class="text-sm font-semibold text-brand-700 mb-2">Carried By/Attached To:</h3>
                        <div class="space-y-2">
                            @foreach($asset->parentAssets as $parent)
                                <div class="border-2 border-brand-100 rounded-xl p-3 flex items-center justify-between hover:border-brand-300 transition">
                                    <div class="flex-1">
                                        <p class="font-semibold text-brand-900">{{ $parent->name }}</p>
                                        <p class="text-xs text-brand-500">{{ ucwords(str_replace('_', ' ', $parent->type)) }}@if($parent->pivot->relationship_type) · {{ ucwords($parent->pivot->relationship_type) }}@endif</p>
                                        @if($parent->pivot->notes)
                                            <p class="text-xs text-brand-600 mt-1">{{ $parent->pivot->notes }}</p>
                                        @endif
                                    </div>
                                    <a href="{{ route('assets.show', $parent) }}" class="text-brand-600 hover:text-brand-800 text-sm font-medium">View</a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Link new asset form --}}
                <form action="{{ route('assets.link', $asset) }}" method="POST" class="mt-4 space-y-3 p-4 rounded-xl bg-brand-50/50 border-2 border-brand-100">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-brand-800 mb-1">Link Asset</label>
                        <select name="linked_asset_id" class="form-select w-full" required>
                            <option value="">Select an asset...</option>
                            @foreach($availableAssets->groupBy('type') as $type => $typeAssets)
                                <optgroup label="{{ ucwords(str_replace('_', ' ', $type)) }}">
                                    @foreach($typeAssets as $availableAsset)
                                        <option value="{{ $availableAsset->id }}">{{ $availableAsset->name }}@if($availableAsset->model) ({{ $availableAsset->model }})@endif</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-800 mb-1">Relationship Type</label>
                        <select name="relationship_type" class="form-select w-full">
                            <option value="contains">Contains/Carries</option>
                            <option value="towing">Towing</option>
                            <option value="attached">Attached To</option>
                            <option value="linked">Linked</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-brand-800 mb-1">Notes (optional)</label>
                        <input type="text" name="notes" class="form-input w-full" placeholder="Additional details...">
                    </div>
                    <x-brand-button type="submit" class="w-full justify-center">Link Asset</x-brand-button>
                </form>
            </section>
        </div>
    </div>
</div>
@endsection
