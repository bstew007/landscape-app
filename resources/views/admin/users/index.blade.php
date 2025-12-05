@extends('layouts.sidebar')

@section('content')
<x-page-header title="Users" eyebrow="Admin" subtitle="Manage application users and roles.">
    <x-slot:actions>
        <x-brand-button href="{{ route('admin.users.create') }}">+ Add User</x-brand-button>
    </x-slot:actions>
</x-page-header>

@if (session('success'))
    <div class="mt-4 bg-green-100 text-green-800 p-3 rounded">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="mt-4 bg-red-100 text-red-800 p-3 rounded">{{ $errors->first() }}</div>
@endif

<div class="mt-6 bg-white rounded shadow overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-left text-gray-600 text-xs uppercase">
            <tr>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Email</th>
                <th class="px-4 py-2">Role</th>
                <th class="px-4 py-2 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $user->name }}</td>
                    <td class="px-4 py-2">{{ $user->email }}</td>
                    <td class="px-4 py-2">{{ ucfirst($user->role) }}</td>
                    <td class="px-4 py-2 text-right space-x-2">
                        <x-brand-button href="{{ route('admin.users.edit', $user) }}" variant="outline">Edit</x-brand-button>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Delete this user?');">
                            @csrf
                            @method('DELETE')
                            <x-brand-button type="submit" variant="outline" class="border-red-300 text-red-700 hover:bg-red-50">Delete</x-brand-button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-4 py-3">{{ $users->links() }}</div>
</div>
@endsection
