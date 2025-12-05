@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <x-page-header title="Create Expense Category" eyebrow="Settings & Configurations" subtitle="Add a new expense category and map it to a QuickBooks Online account.">
  </x-page-header>

  <form action="{{ route('admin.expense-accounts.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border-2 border-brand-100 p-6 space-y-6">
    @csrf

    {{-- Category Code --}}
    <div>
      <label class="block text-sm font-semibold text-brand-800 mb-2">
        Category Code <span class="text-red-500">*</span>
      </label>
      <input 
        type="text" 
        name="category" 
        value="{{ old('category') }}"
        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all @error('category') border-red-500 @enderror"
        placeholder="e.g., fuel, repairs, insurance"
        required>
      <p class="text-xs text-brand-600 mt-1">A unique slug identifier (lowercase, no spaces). This will be used in code.</p>
      @error('category')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @enderror
    </div>

    {{-- Category Label --}}
    <div>
      <label class="block text-sm font-semibold text-brand-800 mb-2">
        Category Label <span class="text-red-500">*</span>
      </label>
      <input 
        type="text" 
        name="category_label" 
        value="{{ old('category_label') }}"
        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all @error('category_label') border-red-500 @enderror"
        placeholder="e.g., Fuel & Gas, Repairs & Maintenance"
        required>
      <p class="text-xs text-brand-600 mt-1">The display name shown to users in forms and reports.</p>
      @error('category_label')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @enderror
    </div>

    {{-- QBO Account Selection --}}
    <div>
      <label class="block text-sm font-semibold text-brand-800 mb-2">QuickBooks Online Account (Optional)</label>
      @if(count($qboAccounts) > 0)
        <select 
          name="qbo_account_id" 
          id="qbo-account-select"
          class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
          <option value="">— Select an Expense Account —</option>
          @foreach($qboAccounts as $account)
            <option 
              value="{{ $account['Id'] }}" 
              data-name="{{ $account['Name'] }}"
              data-type="{{ $account['AccountType'] }}"
              {{ old('qbo_account_id') == $account['Id'] ? 'selected' : '' }}>
              {{ $account['Name'] }} ({{ $account['AccountType'] ?? 'Expense' }})
            </option>
          @endforeach
        </select>
        <input type="hidden" name="qbo_account_name" id="qbo-account-name" value="{{ old('qbo_account_name') }}">
        <input type="hidden" name="qbo_account_type" id="qbo-account-type" value="{{ old('qbo_account_type') }}">
        <p class="text-xs text-brand-600 mt-1">You can also map this later from the expense accounts list.</p>
      @else
        <div class="p-4 bg-amber-50 border-2 border-amber-200 rounded-xl text-sm text-amber-900">
          <p class="font-semibold mb-1">⚠️ No QBO Accounts Available</p>
          <p class="text-xs">
            Please ensure your QuickBooks Online connection is active and try syncing from the Expense Accounts page.
          </p>
        </div>
      @endif
    </div>

    {{-- Active Status --}}
    <div>
      <label class="flex items-center gap-3">
        <input 
          type="checkbox" 
          name="is_active" 
          value="1"
          {{ old('is_active', true) ? 'checked' : '' }}
          class="w-5 h-5 rounded border-2 border-brand-300 text-brand-600 focus:ring-2 focus:ring-brand-500/20">
        <span class="text-sm font-semibold text-brand-800">Active Category</span>
      </label>
      <p class="text-xs text-brand-600 mt-1 ml-8">Inactive categories won't appear in expense forms.</p>
    </div>

    {{-- Actions --}}
    <div class="flex gap-3 pt-4 border-t-2 border-brand-100">
      <x-brand-button as="a" href="{{ route('admin.expense-accounts.index') }}" variant="outline">
        Cancel
      </x-brand-button>
      <x-brand-button type="submit">
        Create Category
      </x-brand-button>
    </div>
  </form>

  {{-- Help Text --}}
  <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-6">
    <div class="flex gap-3">
      <svg class="h-6 w-6 text-blue-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <path d="M12 16v-4M12 8h.01"/>
      </svg>
      <div class="flex-1 text-sm text-blue-900">
        <p class="font-semibold mb-2">About Expense Categories</p>
        <p class="mb-2">
          Expense categories help organize and track different types of asset expenses. 
          Each category can be mapped to a specific QuickBooks Online expense account for automatic sync.
        </p>
        <p>
          Choose descriptive labels that match your business needs, like "Fuel", "Insurance", "Repairs", or "Tires".
        </p>
      </div>
    </div>
  </div>
</div>

<script>
  // Auto-populate hidden fields when QBO account is selected
  document.getElementById('qbo-account-select')?.addEventListener('change', function(e) {
    const selected = e.target.selectedOptions[0];
    document.getElementById('qbo-account-name').value = selected.dataset.name || '';
    document.getElementById('qbo-account-type').value = selected.dataset.type || '';
  });
</script>
@endsection
