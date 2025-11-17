@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
  <x-page-header title="Cost Codes" eyebrow="Settings & Configurations" subtitle="Map cost codes to divisions and QBO service items.">
    <x-slot:actions>
      <x-brand-button href="{{ route('admin.cost-codes.create') }}">+ New Cost Code</x-brand-button>
    </x-slot:actions>
  </x-page-header>

  @if(session('success'))
    <div class="p-3 rounded border border-emerald-200 bg-emerald-50 text-emerald-900">{{ session('success') }}</div>
  @endif

  <div class="bg-white rounded shadow overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
        <tr>
          <th class="text-left px-3 py-2">Code</th>
          <th class="text-left px-3 py-2">Name</th>

          <th class="text-left px-3 py-2">QBO Item</th>
          <th class="text-left px-3 py-2">Active</th>
          <th class="text-right px-3 py-2">Actions</th>
        </tr>
      </thead>
      <tbody>
      @forelse($codes as $c)
        <tr class="border-t">
          <td class="px-3 py-2 font-mono">{{ $c->code }}</td>
          <td class="px-3 py-2">{{ $c->name }}</td>

          <td class="px-3 py-2">{{ $c->qbo_item_name ?: '—' }}</td>
          <td class="px-3 py-2">{{ $c->is_active ? 'Yes' : 'No' }}</td>
          <td class="px-3 py-2 text-right space-x-2">
            <x-brand-button as="a" href="{{ route('admin.cost-codes.edit', $c) }}" size="xs" variant="outline">Edit</x-brand-button>
            <form action="{{ route('admin.cost-codes.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('Delete cost code?')">
              @csrf @method('DELETE')
              <x-danger-button size="xs" type="submit">Delete</x-danger-button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="px-3 py-4 text-gray-500">No cost codes yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
