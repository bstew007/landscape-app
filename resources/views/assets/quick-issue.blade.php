@extends('layouts.sidebar')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <x-page-header title="Log Asset Issue" eyebrow="Assets" subtitle="Quickly capture breakdowns, damage, or maintenance requests." />

        @if (session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('assets.issues.quickStore') }}" method="POST" class="space-y-4 bg-white rounded shadow p-6 mt-2">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">Asset</label>
                <select name="asset_id" class="form-select w-full mt-1" required>
                    <option value="">Select asset</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}" @selected(old('asset_id') == $asset->id)>
                            {{ $asset->name }} ({{ ucwords(str_replace('_', ' ', $asset->type)) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" class="form-input w-full mt-1" value="{{ old('title') }}" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="4" class="form-textarea w-full mt-1">{{ old('description') }}</textarea>
            </div>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Severity</label>
                    <select name="severity" class="form-select w-full mt-1">
                        @foreach ($issueSeverities as $severity)
                            <option value="{{ $severity }}" @selected(old('severity') === $severity)>{{ ucfirst($severity) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="form-select w-full mt-1">
                        @foreach ($issueStatuses as $status)
                            <option value="{{ $status }}" @selected(old('status', 'open') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Reported On</label>
                    <input type="date" name="reported_on" class="form-input w-full mt-1" value="{{ old('reported_on', now()->format('Y-m-d')) }}">
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <x-brand-button href="{{ route('assets.index') }}" variant="outline">Cancel</x-brand-button>
                <x-brand-button type="submit">Log Issue</x-brand-button>
            </div>
        </form>
    </div>
@endsection
