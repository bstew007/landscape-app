@extends('layouts.sidebar')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
  <x-page-header title="New Division" eyebrow="Settings & Configurations" />

  <form method="POST" action="{{ route('admin.divisions.store') }}" class="bg-white rounded shadow p-4 space-y-3">
    @csrf
    <div>
      <label class="block text-sm font-medium">Name</label>
      <input type="text" name="name" class="form-input w-full" required />
    </div>
    <div>
      <label class="block text-sm font-medium">Sort Order</label>
      <input type="number" name="sort_order" class="form-input w-full" value="0" />
    </div>
    <div class="flex items-center gap-2">
      <input type="checkbox" name="is_active" value="1" checked />
      <span class="text-sm">Active</span>
    </div>
    <div class="flex justify-end gap-2">
      <x-secondary-button as="a" href="{{ route('admin.divisions.index') }}">Cancel</x-secondary-button>
      <x-brand-button type="submit">Save</x-brand-button>
    </div>
  </form>
</div>
@endsection
