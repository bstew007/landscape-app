@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <x-page-header title="Map Expense Account" eyebrow="Settings & Configurations" subtitle="Select the QuickBooks Online account for {{ $mapping->category_label }}.">
  </x-page-header>

  <form action="{{ route('admin.expense-accounts.update', $mapping) }}" method="POST" class="bg-white rounded-2xl shadow-sm border-2 border-brand-100 p-6 space-y-6">
    @csrf
    @method('PUT')

    <div>
      <label class="block text-sm font-semibold text-brand-900 mb-2">
        Internal Category Code
      </label>
      <div class="flex items-center gap-3">
        <span class="inline-flex items-center px-3 py-2 rounded-lg text-sm font-semibold
          @if($mapping->category === 'fuel') bg-blue-100 text-blue-800
          @elseif($mapping->category === 'repairs') bg-purple-100 text-purple-800
          @else bg-indigo-100 text-indigo-800
          @endif">
          {{ ucfirst($mapping->category) }}
        </span>
      </div>
      <p class="text-xs text-brand-600 mt-1">The category code cannot be changed after creation.</p>
    </div>

    <div>
      <label for="category_label" class="block text-sm font-semibold text-brand-900 mb-2">
        Category Label <span class="text-red-600">*</span>
      </label>
      <input 
        type="text" 
        name="category_label" 
        id="category_label"
        value="{{ old('category_label', $mapping->category_label) }}"
        class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all @error('category_label') border-red-500 @enderror"
        placeholder="e.g., Fuel & Gas, Repairs & Maintenance"
        required>
      <p class="text-xs text-brand-600 mt-1">The display name shown to users in forms and reports.</p>
      @error('category_label')
        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="qbo_account" class="block text-sm font-semibold text-brand-900 mb-2">
        QuickBooks Online Account <span class="text-red-600">*</span>
      </label>
      <select name="qbo_account" id="qbo_account" 
              class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              onchange="updateAccountDetails(this)">
        <option value="">Select a QBO account...</option>
        @foreach($qboAccounts as $account)
          <option value="{{ $account['Id'] }}" 
                  data-name="{{ $account['Name'] }}"
                  data-type="{{ $account['AccountType'] }}"
                  {{ $mapping->qbo_account_id == $account['Id'] ? 'selected' : '' }}>
            {{ $account['Name'] }} ({{ $account['AccountType'] }})
          </option>
        @endforeach
      </select>
      @error('qbo_account_id')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <input type="hidden" name="qbo_account_id" id="qbo_account_id" value="{{ old('qbo_account_id', $mapping->qbo_account_id) }}">
    <input type="hidden" name="qbo_account_name" id="qbo_account_name" value="{{ old('qbo_account_name', $mapping->qbo_account_name) }}">
    <input type="hidden" name="qbo_account_type" id="qbo_account_type" value="{{ old('qbo_account_type', $mapping->qbo_account_type) }}">

    <div>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" {{ $mapping->is_active ? 'checked' : '' }}
               class="h-4 w-4 rounded border-brand-300 text-brand-600 focus:ring-brand-500">
        <span class="text-sm font-medium text-brand-900">Active</span>
      </label>
      <p class="text-xs text-brand-600 mt-1">Inactive mappings will not be used when syncing expenses to QBO.</p>
    </div>

    @if(empty($qboAccounts))
      <div class="bg-yellow-50 border-2 border-yellow-200 rounded-lg p-4">
        <div class="flex gap-3">
          <svg class="h-5 w-5 text-yellow-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <div>
            <h4 class="font-semibold text-yellow-900">No QuickBooks Accounts Found</h4>
            <p class="text-sm text-yellow-800 mt-1">
              Please ensure your QuickBooks Online connection is active and try syncing from the Expense Accounts page.
            </p>
          </div>
        </div>
      </div>
    @endif

    <div class="flex gap-3 justify-end pt-4 border-t-2 border-brand-100">
      <x-brand-button as="a" href="{{ route('admin.expense-accounts.index') }}" variant="outline">
        Cancel
      </x-brand-button>
      <x-brand-button type="submit">
        Save Mapping
      </x-brand-button>
    </div>
  </form>

  <div class="bg-blue-50 border-2 border-blue-200 rounded-2xl p-6">
    <div class="flex gap-3">
      <svg class="h-6 w-6 text-blue-600 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <path d="M12 16v-4M12 8h.01"/>
      </svg>
      <div>
        <h3 class="font-semibold text-blue-900">Choosing the Right Account</h3>
        <p class="text-sm text-blue-800 mt-1">
          Select an Expense account from your QuickBooks Online Chart of Accounts. 
          The account type should be "Expense" for proper categorization. 
          Common choices: "Fuel" for fuel expenses, "Repairs and Maintenance" for repairs, "Insurance Expense" or "Other Business Expenses" for general expenses.
        </p>
      </div>
    </div>
  </div>
</div>

<script>
function updateAccountDetails(select) {
  const selectedOption = select.options[select.selectedIndex];
  
  if (selectedOption.value) {
    document.getElementById('qbo_account_id').value = selectedOption.value;
    document.getElementById('qbo_account_name').value = selectedOption.dataset.name;
    document.getElementById('qbo_account_type').value = selectedOption.dataset.type;
  } else {
    document.getElementById('qbo_account_id').value = '';
    document.getElementById('qbo_account_name').value = '';
    document.getElementById('qbo_account_type').value = '';
  }
}
</script>
@endsection
