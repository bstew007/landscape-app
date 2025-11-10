@extends('layouts.sidebar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="flex flex-col gap-4 mb-6 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-semibold text-gray-800">Client Hub</h1>
            <p class="text-gray-500 text-sm">Search by client or company name.</p>
        </div>
        <div class="flex flex-col gap-3 md:flex-row md:items-center">
            <form method="GET" action="{{ route('clients.index') }}" class="flex flex-1 items-center">
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Search clients..."
                       class="flex-1 form-input border-gray-300 rounded-l-lg focus:ring-blue-500 focus:border-blue-500">
                @if(!empty($search))
                    <a href="{{ route('clients.index') }}"
                       class="px-3 py-2 border-t border-b border-gray-300 text-gray-600 bg-gray-100 hover:bg-gray-200">
                        ✕
                    </a>
                @endif
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700">
                    🔍
                </button>
            </form>
            <a href="{{ route('clients.create') }}"
               class="inline-flex items-center justify-center px-4 py-2 text-lg font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow">
                ➕ Add Client
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-lg border border-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if ($clients->count())
        <div class="overflow-x-auto bg-white rounded-lg shadow-md">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 text-gray-700 text-left text-sm uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Company</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Phone</th>
                        <th class="px-6 py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-gray-800 text-lg">
                    @foreach ($clients as $client)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $client->first_name }} {{ $client->last_name }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $client->company_name ?? '—' }}
                            </td>
                            <td class="px-6 py-4">{{ $client->email }}</td>
                            <td class="px-6 py-4">{{ $client->phone }}</td>
                            <td class="px-6 py-4 flex flex-wrap gap-3">
                                <a href="{{ route('clients.show', $client) }}"
                                   class="text-blue-600 hover:underline">
                                    👁 View
                                </a>
                                <a href="{{ route('clients.site-visits.index', $client) }}"
                                   class="text-blue-600 hover:underline">
                                    📋 Visits
                                </a>
                                <a href="{{ route('clients.edit', $client) }}"
                                   class="text-blue-600 hover:underline">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('clients.destroy', $client) }}" method="POST"
                                      onsubmit="return confirm('Delete this client?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-600 hover:underline">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="text-gray-600 text-lg mt-4">No clients yet. Click “Add Client” to get started.</p>
    @endif

</div>
@endsection
