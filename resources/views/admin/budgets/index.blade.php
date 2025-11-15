@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <x-page-header title="Company Budgets" eyebrow="Admin" subtitle="Define desired margins and global financial settings.">
        <x-slot:actions>
            <x-brand-button href="{{ route('admin.budgets.create') }}">New Budget</x-brand-button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white rounded-lg shadow overflow-hidden mt-6">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                <tr>
                    <th class="px-3 py-2 text-left">Name</th>
                    <th class="px-3 py-2 text-left">Year</th>
                    <th class="px-3 py-2 text-left">Active</th>
                    <th class="px-3 py-2 text-left">Effective</th>
                    <th class="px-3 py-2 text-left">Desired Margin</th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($budgets as $budget)
                    <tr class="border-t">
                        <td class="px-3 py-2">{{ $budget->name }}</td>
                        <td class="px-3 py-2">{{ $budget->year ?? '—' }}</td>
                        <td class="px-3 py-2">{!! $budget->is_active ? '<span class="text-green-700">Yes</span>' : 'No' !!}</td>
                        <td class="px-3 py-2">{{ optional($budget->effective_from)->format('M j, Y') ?? '—' }}</td>
                        <td class="px-3 py-2">{{ number_format($budget->desired_profit_margin * 100, 1) }}%</td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('admin.budgets.edit', $budget) }}" class="text-blue-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-gray-500">No budgets yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $budgets->links() }}
    </div>
</div>
@endsection
