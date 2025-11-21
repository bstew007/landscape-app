@extends('layouts.sidebar')

@section('content')
@php
    $firstBudget = $budgets->first();
@endphp

<div class="space-y-8">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-3 max-w-3xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Admin</p>
                <h1 class="text-3xl sm:text-4xl font-semibold">Company Budgets</h1>
                <p class="text-sm text-brand-100/85">Define desired margins, overhead, and effective dates. Budgets drive catalog pricing and estimate defaults.</p>
            </div>
            <div class="flex gap-3 ml-auto">
                <x-brand-button href="{{ route('admin.budgets.create') }}" variant="muted">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    New Budget
                </x-brand-button>
            </div>
        </div>
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 text-sm text-brand-100">
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Total Budgets</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ number_format($budgets->total()) }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Current Year</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ $firstBudget?->year ?? '—' }}</dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Target Margin</dt>
                <dd class="text-2xl font-semibold text-white mt-2">
                    {{ $firstBudget && $firstBudget->desired_profit_margin ? number_format($firstBudget->desired_profit_margin * 100, 1) . '%' : '—' }}
                </dd>
            </div>
            <div class="rounded-2xl bg-white/10 border border-white/20 p-4">
                <dt class="text-xs uppercase tracking-wide text-brand-200">Next Effective</dt>
                <dd class="text-2xl font-semibold text-white mt-2">{{ optional($firstBudget?->effective_from)->format('M j') ?? '—' }}</dd>
            </div>
        </dl>
    </section>

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-brand-50/80 text-xs uppercase text-brand-500">
                    <tr>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">Year</th>
                        <th class="px-4 py-3 text-left">Active</th>
                        <th class="px-4 py-3 text-left">Effective</th>
                        <th class="px-4 py-3 text-left">Desired Margin</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50 text-brand-900 text-sm">
                    @forelse ($budgets as $budget)
                        <tr class="transition hover:bg-brand-50/70">
                            <td class="px-4 py-3 font-semibold">{{ $budget->name }}</td>
                            <td class="px-4 py-3">{{ $budget->year ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $budget->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-50 text-gray-600 border border-gray-200' }}">
                                    {{ $budget->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ optional($budget->effective_from)->format('M j, Y') ?? '—' }}</td>
                            <td class="px-4 py-3">{{ number_format($budget->desired_profit_margin * 100, 1) }}%</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.budgets.edit', $budget) }}" class="text-brand-700 hover:text-brand-900">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-brand-400">No budgets yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-brand-100/60">
            {{ $budgets->links() }}
        </div>
    </section>
</div>
@endsection
