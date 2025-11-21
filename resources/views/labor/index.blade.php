@extends('layouts.sidebar')

@section('content')
@php
    $budgetName = $budgetName ?? null;
    $overheadRate = $overheadRate ?? 0;
    $overheadHours = $overheadHours ?? 0;
    $profitMarginPct = $profitMarginPct ?? null;
@endphp

<div class="space-y-8 max-w-7xl mx-auto">
    <x-page-header eyebrow="Catalogs" class="shadow-sm">
        <x-slot:leading>
            <div class="h-10 w-10 rounded-full bg-brand-600 text-white flex items-center justify-center text-lg">🛠️</div>
        </x-slot:leading>
        <x-slot:title>
            <div class="flex items-center gap-2">
                <span class="text-2xl font-semibold text-gray-900">Labor Catalog</span>
            </div>
        </x-slot:title>
    </x-page-header>

    <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-4">
            <p class="text-xs uppercase tracking-wide text-gray-500">Budget</p>
            <p class="text-lg font-semibold text-gray-900 mt-1">{{ $budgetName ?? 'No active budget' }}</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-4">
            <p class="text-xs uppercase tracking-wide text-gray-500">Overhead</p>
            <p class="text-lg font-semibold text-gray-900 mt-1">${{ number_format($overheadRate ?? 0, 2) }}/hr</p>
        </div>
        <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-4">
            <p class="text-xs uppercase tracking-wide text-gray-500">Profit</p>
            <p class="text-lg font-semibold text-gray-900 mt-1">{{ isset($profitMarginPct) ? number_format($profitMarginPct, 1) . '%' : '—' }}</p>
        </div>
    </section>

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="p-5 sm:p-7 border-b border-brand-100/60">
            <form method="GET" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or type" class="form-input w-full sm:max-w-xs rounded-full border-brand-200 focus:ring-brand-500 focus:border-brand-500">
                <div class="flex items-center gap-3">
                    <x-brand-button type="submit" size="sm">Search</x-brand-button>
                    <x-brand-button as="a" href="{{ route('labor.create') }}" variant="muted" size="sm">+ New</x-brand-button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
        @php $overheadRate = $overheadRate ?? 0; @endphp
        <table class="w-full text-sm">
            <thead class="bg-brand-50/80 text-xs uppercase text-brand-500">
            <tr>
                <th class="text-left px-4 py-3">Name</th>
                <th class="text-right px-4 py-3 whitespace-nowrap">Wage/Hr</th>
                <th class="text-right px-4 py-3 whitespace-nowrap">Cost/Hr</th>
                <th class="text-right px-4 py-3">Breakeven</th>
                <th class="text-right px-4 py-3 whitespace-nowrap">Rate/Hr</th>
                <th class="text-right px-4 py-3">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($labor as $entry)
                @php
                    $wage = (float) ($entry->average_wage ?? 0);
                    $otPct = max(0, (float) ($entry->overtime_factor ?? 0));
                    $burdenPct = max(0, (float) ($entry->labor_burden_percentage ?? 0));
                    $unbillPct = min(99.9, max(0, (float) ($entry->unbillable_percentage ?? 0)));
                    $effectiveWage = $wage * (1 + ($otPct / 100));
                    $costPerHour = $effectiveWage * (1 + ($burdenPct / 100));
                    $billableFraction = max(0.01, 1 - ($unbillPct / 100));
                    $breakeven = ($costPerHour / $billableFraction) + $overheadRate;
                @endphp
                <tr class="border-t">
                    <td class="px-4 py-3">
                        <div class="font-semibold text-gray-900">{{ $entry->name }}</div>
                        <div class="text-xs text-gray-500">{{ ucfirst($entry->type) }} · {{ $entry->unit }}</div>
                    </td>
                    <td class="px-4 py-3 text-right text-gray-900">${{ number_format($wage, 2) }}</td>
                    <td class="px-4 py-3 text-right text-gray-900">${{ number_format($costPerHour, 2) }}</td>
                    <td class="px-4 py-3 text-right text-gray-900">${{ number_format($breakeven, 2) }}</td>
                    <td class="px-4 py-3 text-right text-gray-900">${{ number_format($entry->base_rate, 2) }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="inline-flex items-center gap-3">
                            <a href="{{ route('labor.edit', $entry) }}" class="text-blue-600 hover:underline">Edit</a>
                            <form action="{{ route('labor.destroy', $entry) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Delete this labor entry?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-brand-400">No labor entries yet.</td>
            </tr>
            @endforelse
            </tbody>
        </table>
        <div class="px-5 py-4 border-t border-brand-100/60">
            {{ $labor->links() }}
        </div>
    </section>
</div>
@endsection
