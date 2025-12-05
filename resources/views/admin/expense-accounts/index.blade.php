@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
  <x-page-header title="Expense Account Mappings" eyebrow="Settings & Configurations" subtitle="Map internal expense categories to QuickBooks Online Chart of Accounts.">
    <x-slot:actions>
      <form action="{{ route('admin.expense-accounts.sync-all') }}" method="POST" class="inline">
        @csrf
        <x-brand-button type="submit" variant="outline">
          <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Sync QBO Accounts
        </x-brand-button>
      </form>
    </x-slot:actions>
  </x-page-header>

  @if(session('success'))
    <div class="p-3 rounded border border-emerald-200 bg-emerald-50 text-emerald-900">{{ session('success') }}</div>
  @endif

  @if(session('error'))
    <div class="p-3 rounded border border-red-200 bg-red-50 text-red-900">{{ session('error') }}</div>
  @endif

  <div class="bg-white rounded-2xl shadow-sm border-2 border-brand-100 overflow-hidden">
    <div class="p-6 border-b-2 border-brand-100">
      <h2 class="text-lg font-bold text-brand-900">Expense Categories</h2>
      <p class="text-sm text-brand-600 mt-1">Map each expense category to the appropriate QuickBooks Online expense account.</p>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-brand-50 text-brand-900">
          <tr>
            <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider">Category</th>
            <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider">Label</th>
            <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider">QBO Account</th>
            <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider">Account Type</th>
            <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider">Status</th>
            <th class="text-right px-6 py-4 text-xs font-bold uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-100">
        @forelse($mappings as $mapping)
          <tr class="hover:bg-brand-50/50 transition-colors">
            <td class="px-6 py-4">
              <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold
                @if($mapping->category === 'fuel') bg-blue-100 text-blue-800
                @elseif($mapping->category === 'repairs') bg-purple-100 text-purple-800
                @else bg-indigo-100 text-indigo-800
                @endif">
                {{ ucfirst($mapping->category) }}
              </span>
            </td>
            <td class="px-6 py-4 font-medium text-brand-900">{{ $mapping->category_label }}</td>
            <td class="px-6 py-4">
              @if($mapping->isMapped())
                <div class="flex items-center gap-2">
                  <svg class="h-4 w-4 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 13l4 4L19 7"/>
                  </svg>
                  <span class="text-brand-900">{{ $mapping->qbo_account_name }}</span>
                </div>
              @else
                <span class="text-brand-400 text-xs">Not mapped</span>
              @endif
            </td>
            <td class="px-6 py-4">
              <span class="text-brand-600">{{ $mapping->qbo_account_type ?? '—' }}</span>
            </td>
            <td class="px-6 py-4">
              @if($mapping->is_active)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Active</span>
              @else
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">Inactive</span>
              @endif
            </td>
            <td class="px-6 py-4 text-right">
              <x-brand-button as="a" href="{{ route('admin.expense-accounts.edit', $mapping) }}" size="xs" variant="outline">
                Map Account
              </x-brand-button>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="px-6 py-8 text-center text-brand-500">
              No expense account mappings found. Click "Sync QBO Accounts" to get started.
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-6">
    <div class="flex gap-3">
      <svg class="h-6 w-6 text-blue-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <path d="M12 16v-4M12 8h.01"/>
      </svg>
      <div>
        <h3 class="font-semibold text-blue-900">How Expense Account Mappings Work</h3>
        <p class="text-sm text-blue-800 mt-1">
          When you create an expense for an asset, the system uses these mappings to determine which QuickBooks Online expense account to post to. 
          Click "Map Account" to select the appropriate QBO account for each category. 
          Make sure your QuickBooks Online connection is active before syncing.
        </p>
      </div>
    </div>
  </div>
</div>
@endsection
