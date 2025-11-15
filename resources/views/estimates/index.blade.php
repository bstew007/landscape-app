@extends('layouts.sidebar')

@section('content')
<div class="space-y-6">
    <x-page-header title="Estimates" eyebrow="Sales" subtitle="Draft, send, and track pricing packages.">
        <x-slot:actions>
            <x-brand-button href="{{ route('estimates.create') }}">+ New Estimate</x-brand-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="bg-white rounded-lg shadow p-4 grid md:grid-cols-3 gap-4 mt-6">
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" class="form-select w-full mt-1">
                <option value="">All</option>
                @foreach (\App\Models\Estimate::STATUSES as $option)
                    <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Client</label>
            <select name="client_id" class="form-select w-full mt-1">
                <option value="">All</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}" @selected($clientId == $client->id)>{{ $client->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <x-brand-button type="submit" class="w-full justify-center">Filter</x-brand-button>
        </div>
    </form>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3">Estimate</th>
                <th class="px-4 py-3">Client / Property</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3">Expires</th>
                <th class="px-4 py-3"></th>
            </tr>
            </thead>
            <tbody class="divide-y">
            @foreach ($estimates as $estimate)
                <tr>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-gray-900">{{ $estimate->title }}</p>
                        <p class="text-xs text-gray-500">Created {{ $estimate->created_at->format('M j, Y') }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-sm text-gray-800">{{ $estimate->client->name }}</p>
                        <p class="text-xs text-gray-500">{{ $estimate->property->name ?? 'No property' }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold
                            @class([
                                'bg-gray-100 text-gray-700' => $estimate->status === 'draft',
                                'bg-amber-100 text-amber-700' => $estimate->status === 'pending',
                                'bg-blue-100 text-blue-700' => $estimate->status === 'sent',
                                'bg-green-100 text-green-700' => $estimate->status === 'approved',
                                'bg-red-100 text-red-700' => $estimate->status === 'rejected',
                            ])">
                            {{ ucfirst($estimate->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if ($estimate->email_last_sent_at)
                            <div class="text-xs font-semibold text-green-700">
                                Sent {{ $estimate->email_last_sent_at->format('M j, Y') }}
                            </div>
                            <div class="text-[11px] text-gray-500">
                                {{ $estimate->email_send_count }} {{ \Illuminate\Support\Str::plural('time', $estimate->email_send_count) }}
                            </div>
                        @else
                            <span class="text-xs text-gray-400">Not sent</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                        {{ $estimate->total ? '$' . number_format($estimate->total, 2) : '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        {{ optional($estimate->expires_at)->format('M j, Y') ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('estimates.show', $estimate) }}" class="text-blue-600 hover:text-blue-800 text-sm">Open</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div>
        {{ $estimates->links() }}
    </div>
</div>
@endsection
