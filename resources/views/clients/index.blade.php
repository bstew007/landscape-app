@extends('layouts.sidebar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-semibold text-gray-800">Client Hub</h1>
        <a href="{{ route('clients.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-lg font-medium rounded-lg shadow">
            ➕ Add Client
        </a>
        

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
                            <td class="px-6 py-4 flex flex-wrap gap-2">
                                <a href="{{ route('clients.site-visits.index', $client) }}"
                                    class="text-blue-600 hover:underline mr-2">
                                    📋 Visits
                                </a>
                                <a href="{{ route('clients.edit', $client) }}"
                                   class="inline-block px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md">
                                    ✏️ Edit
                                </a>

                                <form action="{{ route('clients.destroy', $client) }}" method="POST"
                                      onsubmit="return confirm('Delete this client?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-md">
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
