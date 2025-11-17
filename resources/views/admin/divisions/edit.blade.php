@extends('layouts.sidebar')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
  <x-page-header title="Edit Division" eyebrow="Settings & Configurations" />

  <form method="POST" action="{{ route('admin.divisions.update', $division) }}" class="bg-white rounded shadow p-4 space-y-3">
    @csrf @method('PUT')
    <div>
      <label class="block text-sm font-medium">Name</label>
      <input type="text" name="name" class="form-input w-full" value="{{ old('name', $division->name) }}" required />
    </div>
    <div>
      <label class="block text-sm font-medium">Sort Order</label>
      <input type="number" name="sort_order" class="form-input w-full" value="{{ old('sort_order', $division->sort_order) }}" />
    </div>
    <div class="flex items-center gap-2">
      <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $division->is_active)) />
      <span class="text-sm">Active</span>
    </div>
    <div class="flex justify-end gap-2">
      <x-secondary-button as="a" href="{{ route('admin.divisions.index') }}">Cancel</x-secondary-button>
      <x-brand-button type="submit">Update</x-brand-button>
    </div>
  </form>
</div>
@endsection
