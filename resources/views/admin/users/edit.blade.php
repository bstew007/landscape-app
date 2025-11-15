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
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_admin" value="1" class="form-checkbox" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
            Admin
        </label>
        <div class="flex justify-end gap-2">
            <x-brand-button href="{{ route('admin.users.index') }}" variant="outline">Cancel</x-brand-button>
            <x-brand-button type="submit">Update User</x-brand-button>
        </div>
    </form>
</div>
@endsection
