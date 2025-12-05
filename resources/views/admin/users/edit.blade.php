@extends('layouts.sidebar')

@section('content')
<x-page-header title="Edit User" eyebrow="Admin" subtitle="Update account details." />

<div class="mt-6 max-w-xl">
    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="bg-white rounded shadow p-6 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input type="text" name="name" class="form-input w-full" value="{{ old('name', $user->name) }}" required>
            @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" class="form-input w-full" value="{{ old('email', $user->email) }}" required>
            @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Password (leave blank to keep)</label>
                <input type="password" name="password" class="form-input w-full">
                @error('password')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-input w-full">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Role</label>
            <select name="role" class="form-select w-full" required>
                <option value="user" @selected(old('role', $user->role) === 'user')>User</option>
                <option value="crew" @selected(old('role', $user->role) === 'crew')>Crew</option>
                <option value="foreman" @selected(old('role', $user->role) === 'foreman')>Foreman</option>
                <option value="office" @selected(old('role', $user->role) === 'office')>Office</option>
                <option value="manager" @selected(old('role', $user->role) === 'manager')>Manager</option>
                <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
            </select>
            @error('role')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_driver" value="1" class="form-checkbox" {{ old('is_driver', $user->is_driver) ? 'checked' : '' }}>
            Driver
        </label>
        <div class="flex justify-end gap-2">
            <x-brand-button href="{{ route('admin.users.index') }}" variant="outline">Cancel</x-brand-button>
            <x-brand-button type="submit">Update User</x-brand-button>
        </div>
    </form>
</div>
@endsection
