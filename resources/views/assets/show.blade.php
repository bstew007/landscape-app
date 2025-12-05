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
                <p class="text-sm text-brand-100/85 mt-1">{{ ucwords(str_replace('_', ' ', $asset->type)) }} · {{ $asset->identifier ?: 'No ID' }}</p>
            </div>
            <div class="flex gap-2 flex-wrap">
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

            <section class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-5">rder-brand-100 shadow-sm p-5">
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
        </div>
    </div>
</div>
@endsection
