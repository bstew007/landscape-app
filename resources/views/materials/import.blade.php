@extends('layouts.sidebar')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold">Import Materials</h1>
        <p class="text-sm text-gray-600">Upload a JSON file containing an array of materials to seed/update the catalog. Fields supported: name, sku, category, unit, unit_cost, tax_rate, vendor_name, vendor_sku, description, is_taxable, is_active.</p>
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

    <form method="POST" action="{{ route('materials.import') }}" enctype="multipart/form-data" class="bg-white shadow rounded p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-semibold mb-1">JSON File</label>
            <input type="file" name="file" accept=".json,.txt" class="form-input w-full" required>
            <p class="text-xs text-gray-500 mt-1">Max 5MB. Example structure is in database/seeders/data/materials.json.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Import</button>
            <a href="{{ route('materials.index') }}" class="px-4 py-2 border rounded text-gray-700 hover:bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
