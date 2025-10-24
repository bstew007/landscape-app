@extends('layouts.sidebar')

@section('content')

<form method="GET" action="{{ route('production-rates.index') }}" class="mb-6 flex flex-wrap gap-4 items-end">
    {{-- Calculator Filter --}}
    <div class="flex-1 min-w-[200px]">
        <label for="calculator" class="block text-sm font-medium text-gray-700">Calculator</label>
        <select name="calculator" id="calculator" class="w-full border rounded px-3 py-2">
            <option value="">All</option>
            @foreach ($calculators as $calc)
                <option value="{{ $calc }}" @if(request('calculator') === $calc) selected @endif>{{ ucfirst($calc) }}</option>
            @endforeach
        </select>
    </div>

    {{-- Task Search --}}
    <div class="flex-1 min-w-[200px]">
        <label for="task" class="block text-sm font-medium text-gray-700">Task</label>
        <input type="text" name="task" id="task" value="{{ request('task') }}" class="w-full border rounded px-3 py-2">
    </div>

    {{-- Submit --}}
    <div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            🔍 Filter
        </button>
    </div>
</form>

<div class="max-w-6xl mx-auto py-10">
    <h1 class="text-2xl font-bold mb-6">🛠️ Manage Production Rates</h1>

    {{-- Success message --}}
    @if (session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Add New Rate --}}
    <div class="mb-6 p-4 border rounded bg-gray-50">
        <h2 class="font-semibold mb-2">➕ Add New Rate</h2>
        <form method="POST" action="{{ route('production-rates.store') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-sm">Task</label>
                <input type="text" name="task" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-sm">Unit</label>
                <input type="text" name="unit" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-sm">Rate (hrs/unit)</label>
                <input type="number" name="rate" step="0.0001" class="form-input w-full" required>
            </div>
            <div>
                <label class="block text-sm">Calculator</label>
                <input type="text" name="calculator" class="form-input w-full">
            </div>
            <div>
                <label class="block text-sm">Note</label>
                <input type="text" name="note" class="form-input w-full">
            </div>
            <div class="col-span-5 text-right mt-4">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Save New Rate
                </button>
            </div>
        </form>
    </div>

    {{-- Existing Rates Table --}}
    <h2 class="text-xl font-semibold mb-2">📋 Existing Rates</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded text-sm">
            <thead class="bg-gray-100 text-left text-sm text-gray-600">
                <tr>
                    <th class="py-2 px-3 w-1/4">Task</th>
                    <th class="py-2 px-3 w-20">Unit</th>
                    <th class="py-2 px-3 w-24">Rate</th>
                    <th class="py-2 px-3 w-32">Calculator</th>
                    <th class="py-2 px-3 w-1/3">Note</th>
                    <th class="py-2 px-3 w-24">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($productionRates as $rate)
                    <tr class="border-t">
                        <form method="POST" action="{{ route('production-rates.update', $rate) }}">
                            @csrf
                            @method('PUT')
                            <td class="px-3 py-2">
                                <input type="text" name="task" value="{{ $rate->task }}" class="form-input w-full text-xs px-2 py-1 truncate">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="unit" value="{{ $rate->unit }}" class="form-input w-full text-xs px-2 py-1">
                            </td>
                            <td class="px-3 py-2">
                                <input type="number" step="0.0001" name="rate" value="{{ $rate->rate }}" class="form-input w-full text-xs px-2 py-1">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="calculator" value="{{ $rate->calculator }}" class="form-input w-full text-xs px-2 py-1">
                            </td>
                            <td class="px-3 py-2">
                                <input type="text" name="note" value="{{ $rate->note }}" class="form-input w-full text-xs px-2 py-1 whitespace-normal break-words">
                            </td>
                            <td class="px-3 py-2 flex space-x-2">
                                <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                                    💾 Save
                                </button>
                        </form>
                        {{-- Delete form --}}
                        <form method="POST" action="{{ route('production-rates.destroy', $rate) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
                                    onclick="return confirm('Are you sure?')">
                                🗑️
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

