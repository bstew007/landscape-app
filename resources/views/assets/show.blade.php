@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <x-page-header title="{{ $asset->name }}" eyebrow="Asset" subtitle="{{ ucwords(str_replace('_', ' ', $asset->type)) }} · {{ $asset->identifier ?: 'No ID' }}">
        <x-slot:actions>
            <x-brand-button href="{{ route('assets.edit', $asset) }}" variant="outline">Edit</x-brand-button>
            <form action="{{ route('assets.destroy', $asset) }}" method="POST" onsubmit="return confirm('Remove this asset?');">
                @csrf
                @method('DELETE')
                <x-brand-button type="submit" variant="outline" class="border-red-300 text-red-700 hover:bg-red-50">Delete</x-brand-button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase">Status</h2>
            <p class="text-xl font-bold text-gray-900">{{ ucwords(str_replace('_', ' ', $asset->status)) }}</p>
            <p class="text-sm text-gray-600 mt-2"><strong>Assigned:</strong> {{ $asset->assigned_to ?: 'Unassigned' }}</p>
            <p class="text-sm text-gray-600"><strong>Mileage / Hours:</strong> {{ $asset->mileage_hours ?: 'N/A' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase">Maintenance</h2>
            <p class="text-xl font-bold text-gray-900">{{ optional($asset->next_service_date)->format('M j, Y') ?? 'No date' }}</p>
            <p class="text-sm text-gray-600 mt-2">{{ Str::limit($asset->notes, 80) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h2 class="text-sm font-semibold text-gray-500 uppercase">Issues</h2>
            <p class="text-xl font-bold text-gray-900">{{ $asset->issues->where('status', '!=', 'resolved')->count() }} open</p>
            <p class="text-sm text-gray-600 mt-2"><strong>Purchase:</strong> {{ optional($asset->purchase_date)->format('M j, Y') ?? 'N/A' }}</p>
            <p class="text-sm text-gray-600"><strong>Price:</strong> {{ $asset->purchase_price ? '$' . number_format($asset->purchase_price, 2) : 'N/A' }}</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <section class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Maintenance Schedule</h2>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    @foreach ($asset->maintenances as $maintenance)
                        <div class="rounded border p-3">
                            <p class="text-sm text-gray-500">{{ $maintenance->type ?? 'Service' }}</p>
                            <p class="font-semibold text-gray-900">{{ optional($maintenance->completed_at ?? $maintenance->scheduled_at)->format('M j, Y') ?? 'No date' }}</p>
                            <p class="text-xs text-gray-500">Hours: {{ $maintenance->mileage_hours ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600 mt-2">{{ $maintenance->notes ?: 'No notes' }}</p>
                        </div>
                    @endforeach
                </div>
                <form action="{{ route('assets.maintenance.store', $asset) }}" method="POST" class="mt-4 grid md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Maintenance Type</label>
                        <input type="text" name="type" class="form-input w-full mt-1" placeholder="Inspection / Service">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Scheduled Date</label>
                        <input type="date" name="scheduled_at" class="form-input w-full mt-1">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Completed Date</label>
                        <input type="date" name="completed_at" class="form-input w-full mt-1">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mileage / Hours</label>
                        <input type="number" name="mileage_hours" class="form-input w-full mt-1">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="3" class="form-textarea w-full mt-1"></textarea>
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <x-brand-button type="submit">Add Maintenance</x-brand-button>
                    </div>
                </form>
            </section>

            <section class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold">Issues & Repairs</h2>
                </div>
                <div class="space-y-3">
                    @forelse ($asset->issues as $issue)
                        <div class="border rounded p-3">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-gray-900">{{ $issue->title }}</p>
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
                            <p class="text-sm text-gray-600">{{ $issue->description ?: 'No description' }}</p>
                            <p class="text-xs text-gray-500 mt-2">
                                Status: {{ ucwords(str_replace('_', ' ', $issue->status)) }} · Reported {{ optional($issue->reported_on)->format('M j, Y') ?? 'N/A' }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No issues logged.</p>
                    @endforelse
                </div>
                <form action="{{ route('assets.issues.store', $asset) }}" method="POST" class="mt-4 grid md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" class="form-input w-full mt-1" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Severity</label>
                        <select name="severity" class="form-select w-full mt-1">
                            @foreach ($issueSeverities as $severity)
                                <option value="{{ $severity }}">{{ ucfirst($severity) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status" class="form-select w-full mt-1">
                            @foreach ($issueStatuses as $status)
                                <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reported On</label>
                        <input type="date" name="reported_on" class="form-input w-full mt-1">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="3" class="form-textarea w-full mt-1"></textarea>
                    </div>
                    <div class="md:col-span-2 flex justify-end">
                        <x-brand-button type="submit">Log Issue</x-brand-button>
                    </div>
                </form>
            </section>
        </div>

        <div class="space-y-6">
            <section class="bg-white rounded-lg shadow p-4">
                <h2 class="text-lg font-semibold mb-4">Attachments & Docs</h2>
                <div class="space-y-3">
                    @forelse ($asset->attachments as $attachment)
                        <div class="border rounded p-3 flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $attachment->label }}</p>
                                <p class="text-xs text-gray-500">{{ $attachment->mime_type }} · {{ number_format(($attachment->size ?? 0) / 1024, 1) }} KB</p>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ $attachment->url }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">Open</a>
                                <form action="{{ route('assets.attachments.destroy', [$asset, $attachment]) }}" method="POST" onsubmit="return confirm('Delete file?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No files uploaded.</p>
                    @endforelse
                </div>

                <form action="{{ route('assets.attachments.store', $asset) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-2">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Label</label>
                        <input type="text" name="label" class="form-input w-full mt-1" placeholder="Insurance card, inspection, etc.">
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
