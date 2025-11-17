@extends('layouts.sidebar')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
  <x-page-header title="Divisions" eyebrow="Settings & Configurations" subtitle="Organize work into divisions for reporting.">
    <x-slot:actions>
      <x-brand-button href="{{ route('admin.divisions.create') }}">+ New Division</x-brand-button>
    </x-slot:actions>
  </x-page-header>

  @if(session('success'))
    <div class="p-3 rounded border border-emerald-200 bg-emerald-50 text-emerald-900">{{ session('success') }}</div>
  @endif

  <div class="bg-white rounded shadow">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
        <tr>
          <th class="text-left px-3 py-2">Name</th>
          <th class="text-left px-3 py-2">Sort</th>
          <th class="text-left px-3 py-2">Active</th>
          <th class="text-right px-3 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
      @forelse($divisions as $d)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $d->name }}</td>
          <td class="px-3 py-2">{{ $d->sort_order }}</td>
          <td class="px-3 py-2">{{ $d->is_active ? 'Yes' : 'No' }}</td>
          <td class="px-3 py-2 text-right space-x-2">
            <x-brand-button as="a" href="{{ route('admin.divisions.edit', $d) }}" size="xs" variant="outline">Edit</x-brand-button>
            <form action="{{ route('admin.divisions.destroy', $d) }}" method="POST" class="inline" onsubmit="return confirm('Delete division?')">
              @csrf @method('DELETE')
              <x-danger-button size="xs" type="submit">Delete</x-danger-button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="px-3 py-4 text-gray-500">No divisions yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
