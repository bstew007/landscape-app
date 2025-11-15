@extends('layouts.sidebar')

@section('content')
<x-page-header title="Add User" eyebrow="Admin" subtitle="Create a new user account." />

<div class="mt-6 max-w-xl">
    <form action="{{ route('admin.users.store') }}" method="POST" class="bg-white rounded shadow p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" class="form-input w-full" value="{{ old('name') }}" required>
            @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" class="form-input w-full" value="{{ old('email') }}" required>
            @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" class="form-input w-full" required>
                @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-input w-full" required>
            </div>
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_admin" value="1" class="form-checkbox" {{ old('is_admin') ? 'checked' : '' }}>
            Admin
        </label>
        <div class="flex justify-end gap-2">
            <x-brand-button href="{{ route('admin.users.index') }}" variant="outline">Cancel</x-brand-button>
            <x-brand-button type="submit">Save User</x-brand-button>
        </div>
    </form>
</div>
@endsection
