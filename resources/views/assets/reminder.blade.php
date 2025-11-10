@extends('layouts.sidebar')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <h1 class="text-3xl font-bold">Schedule Service Reminder</h1>
            <p class="text-gray-600">Pick an asset, set the next service date, and configure the reminder window.</p>
        </div>

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

        <form action="{{ route('assets.reminders.store') }}" method="POST" class="space-y-4 bg-white rounded shadow p-6">
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
                <label class="block text-sm font-medium text-gray-700">Next Service Date</label>
                <input type="date" name="next_service_date" value="{{ old('next_service_date', now()->addWeek()->format('Y-m-d')) }}"
                       class="form-input w-full mt-1" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Reminder Window (days before)</label>
                <input type="number" name="reminder_days_before" min="1" max="60" value="{{ old('reminder_days_before', 7) }}"
                       class="form-input w-full mt-1" required>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="reminder_enabled" id="reminder_enabled" value="1"
                       class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                       @checked(old('reminder_enabled', true))>
                <label for="reminder_enabled" class="text-sm text-gray-700">Enable reminder notifications</label>
            </div>
            <div class="flex justify-end gap-2">
                <a href="{{ route('assets.index') }}" class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Save Reminder</button>
            </div>
        </form>
    </div>
@endsection
