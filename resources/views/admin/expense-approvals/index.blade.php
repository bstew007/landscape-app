@extends('layouts.sidebar')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
  <x-page-header 
    title="Expense Approvals & QBO Sync" 
    eyebrow="Finance Management" 
    subtitle="Review, approve, and sync expenses to QuickBooks Online.">
  </x-page-header>

  @if(session('success'))
    <div class="p-4 rounded-xl border-2 border-emerald-200 bg-emerald-50 text-emerald-900 font-medium">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="p-4 rounded-xl border-2 border-red-200 bg-red-50 text-red-900 font-medium">
      {{ session('error') }}
    </div>
  @endif

  @if(session('warning'))
    <div class="p-4 rounded-xl border-2 border-amber-200 bg-amber-50 text-amber-900 font-medium">
      {{ session('warning') }}
    </div>
  @endif

  {{-- Summary Cards --}}
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-semibold text-amber-800">Pending Approval</p>
          <p class="text-3xl font-bold text-amber-900 mt-1">{{ $pendingExpenses->count() }}</p>
          <p class="text-xs text-amber-700 mt-1">${{ number_format($pendingExpenses->sum('amount'), 2) }} total</p>
        </div>
        <div class="h-12 w-12 bg-amber-200 rounded-xl flex items-center justify-center">
          <svg class="h-6 w-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-semibold text-blue-800">Ready to Sync</p>
          <p class="text-3xl font-bold text-blue-900 mt-1">{{ $approvedExpenses->count() }}</p>
          <p class="text-xs text-blue-700 mt-1">${{ number_format($approvedExpenses->sum('amount'), 2) }} total</p>
        </div>
        <div class="h-12 w-12 bg-blue-200 rounded-xl flex items-center justify-center">
          <svg class="h-6 w-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-green-50 border-2 border-green-200 rounded-2xl p-5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-semibold text-green-800">Synced (30 days)</p>
          <p class="text-3xl font-bold text-green-900 mt-1">{{ $syncedExpenses->count() }}</p>
          <p class="text-xs text-green-700 mt-1">${{ number_format($syncedExpenses->sum('amount'), 2) }} total</p>
        </div>
        <div class="h-12 w-12 bg-green-200 rounded-xl flex items-center justify-center">
          <svg class="h-6 w-6 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
      </div>
    </div>
  </div>

  {{-- Tabs --}}
  <div class="bg-white rounded-2xl border-2 border-brand-100 shadow-sm">
    <div class="border-b-2 border-brand-100">
      <div class="flex gap-1 p-2">
        <a href="{{ route('admin.expense-approvals.index', ['tab' => 'pending']) }}" 
           class="px-6 py-3 rounded-xl font-semibold transition-all {{ $tab === 'pending' ? 'bg-brand-600 text-white' : 'text-brand-700 hover:bg-brand-50' }}">
          Pending Approval ({{ $pendingExpenses->count() }})
        </a>
        <a href="{{ route('admin.expense-approvals.index', ['tab' => 'approved']) }}" 
           class="px-6 py-3 rounded-xl font-semibold transition-all {{ $tab === 'approved' ? 'bg-brand-600 text-white' : 'text-brand-700 hover:bg-brand-50' }}">
          Ready to Sync ({{ $approvedExpenses->count() }})
        </a>
        <a href="{{ route('admin.expense-approvals.index', ['tab' => 'synced']) }}" 
           class="px-6 py-3 rounded-xl font-semibold transition-all {{ $tab === 'synced' ? 'bg-brand-600 text-white' : 'text-brand-700 hover:bg-brand-50' }}">
          Recently Synced ({{ $syncedExpenses->count() }})
        </a>
      </div>
    </div>

    <div class="p-6">
      @if($tab === 'pending')
        @include('admin.expense-approvals.partials.pending', ['expenses' => $pendingExpenses])
      @elseif($tab === 'approved')
        @include('admin.expense-approvals.partials.approved', ['expenses' => $approvedExpenses])
      @else
        @include('admin.expense-approvals.partials.synced', ['expenses' => $syncedExpenses])
      @endif
    </div>
  </div>
</div>

<script>
  function toggleAllCheckboxes(source) {
    const checkboxes = document.querySelectorAll('input[name="expense_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = source.checked);
    updateBulkActionButtons();
  }

  function updateBulkActionButtons() {
    const checkedBoxes = document.querySelectorAll('input[name="expense_ids[]"]:checked');
    const bulkButtons = document.querySelectorAll('.bulk-action-button');
    
    bulkButtons.forEach(button => {
      button.disabled = checkedBoxes.length === 0;
      button.classList.toggle('opacity-50', checkedBoxes.length === 0);
      button.classList.toggle('cursor-not-allowed', checkedBoxes.length === 0);
    });
  }

  function submitBulkAction(action) {
    const checkedBoxes = document.querySelectorAll('input[name="expense_ids[]"]:checked');
    if (checkedBoxes.length === 0) {
      alert('Please select at least one expense.');
      return;
    }

    const form = document.getElementById('bulk-action-form');
    form.action = action;
    form.onsubmit = null; // Remove the prevention handler
    form.submit();
  }

  // Initialize button states
  document.addEventListener('DOMContentLoaded', updateBulkActionButtons);
</script>
@endsection
