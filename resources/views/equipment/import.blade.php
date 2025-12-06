@extends('layouts.sidebar')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Import Equipment</h1>
        <p class="text-sm text-gray-600">Upload a JSON or CSV file containing equipment items to seed/update the catalog. Fields supported: name, sku, category, ownership_type, unit, hourly_cost, daily_cost, hourly_rate, daily_rate, vendor_name, model, description, is_active.</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded text-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('equipment.import') }}" enctype="multipart/form-data" class="bg-white shadow rounded p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold mb-1">JSON or CSV File</label>
            <input type="file" name="file" accept=".json,.txt,.csv" class="form-input w-full" required>
            <p class="text-xs text-gray-500 mt-1">Max 5MB. Supported formats: JSON, CSV</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="px-4 py-2 bg-brand-700 text-white rounded hover:bg-brand-800">Import</button>
            <a href="{{ route('equipment.index') }}" class="px-4 py-2 border border-brand-300 rounded text-brand-700 hover:bg-brand-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
