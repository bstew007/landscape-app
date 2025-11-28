@extends('layouts.sidebar')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
  <x-page-header 
    title="Link Customers to QuickBooks" 
    eyebrow="Customer Management" 
    subtitle="Match your local customers to QuickBooks customers or create new ones." 
  />

  @if(session('error'))
    <div class="p-4 rounded-lg border-2 border-red-300 bg-red-50 text-red-900 font-medium">
      ❌ {{ session('error') }}
    </div>
  @endif
  @if(session('success'))
    <div class="p-4 rounded-lg border-2 border-emerald-300 bg-emerald-50 text-emerald-900 font-medium">
      ✅ {{ session('success') }}
    </div>
  @endif

  <div class="bg-white rounded-lg shadow-sm border border-brand-100/60">
    <div class="p-4 border-b border-brand-100 flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-brand-900">Customer Linking</h2>
        <p class="text-sm text-brand-600 mt-1">{{ $unlinkedCount }} customer{{ $unlinkedCount !== 1 ? 's' : '' }} need linking</p>
      </div>
      <div class="flex gap-2">
        <form method="POST" action="{{ route('contacts.qbo.customer.sync-all') }}" onsubmit="return confirm('Create all unlinked customers in QuickBooks?');">
          @csrf
          <x-brand-button type="submit" variant="outline">
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Sync All to QB
          </x-brand-button>
        </form>
        <x-secondary-button as="a" href="{{ route('contacts.qbo.search') }}">
          Browse QB Customers
        </x-secondary-button>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-brand-50/80 text-left text-xs uppercase tracking-wide text-brand-600">
          <tr>
            <th class="px-4 py-3 w-1/3">Local Customer</th>
            <th class="px-4 py-3 w-1/3">QuickBooks Customer</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-50">
          @forelse($customers as $customer)
            <tr class="hover:bg-brand-50/30 transition" data-customer-id="{{ $customer->id }}">
              <td class="px-4 py-3">
                <div class="font-medium text-brand-900">{{ $customer->company_name ?: $customer->name }}</div>
                <div class="text-xs text-brand-500">
                  ID: {{ $customer->id }}
                  @if($customer->email)
                    · {{ $customer->email }}
                  @endif
                </div>
                @if($customer->phone)
                  <div class="text-xs text-brand-400 mt-1">{{ $customer->phone }}</div>
                @endif
              </td>
              
              <td class="px-4 py-3">
                @if($customer->qbo_customer_id)
                  <div class="font-medium text-emerald-700">
                    {{ $customer->qbo_customer_name ?? 'Linked' }}
                  </div>
                  <div class="text-xs text-emerald-600">QBO ID: {{ $customer->qbo_customer_id }}</div>
                @else
                  <select 
                    name="qbo_customer_id" 
                    class="form-select text-sm border-brand-300 rounded focus:ring-brand-500 focus:border-brand-500 w-full"
                    data-customer-local-id="{{ $customer->id }}"
                  >
                    <option value="">Select QB Customer...</option>
                    <option value="__create__" class="font-semibold text-brand-700">+ Create New in QB</option>
                    @foreach($qboCustomers as $qc)
                      <option value="{{ $qc['Id'] }}" 
                        {{ collect($linkedQboIds)->contains($qc['Id']) ? 'disabled' : '' }}
                      >
                        {{ $qc['DisplayName'] }} 
                        @if(collect($linkedQboIds)->contains($qc['Id']))
                          (Already Linked)
                        @endif
                      </option>
                    @endforeach
                  </select>
                @endif
              </td>
              
              <td class="px-4 py-3">
                @if($customer->qbo_customer_id)
                  @php
                    $needsSync = $customer->qbo_last_synced_at && $customer->updated_at && $customer->updated_at->gt($customer->qbo_last_synced_at);
                  @endphp
                  @if($needsSync)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-xs">
                      Needs Sync
                    </span>
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs">
                      ✓ Synced
                    </span>
                  @endif
                  @if($customer->qbo_last_synced_at)
                    <div class="text-xs text-brand-400 mt-1">{{ $customer->qbo_last_synced_at->diffForHumans() }}</div>
                  @endif
                @else
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-50 text-gray-700 border border-gray-200 text-xs">
                    Not Linked
                  </span>
                @endif
              </td>
              
              <td class="px-4 py-3 text-right">
                @if($customer->qbo_customer_id)
                  <form method="POST" action="{{ route('contacts.qbo.sync', $customer) }}" class="inline">
                    @csrf
                    <x-brand-button type="submit" size="sm" variant="outline">
                      Re-sync
                    </x-brand-button>
                  </form>
                @else
                  <x-brand-button 
                    size="sm" 
                    data-action="link-customer" 
                    data-customer-id="{{ $customer->id }}"
                    onclick="linkCustomer({{ $customer->id }})"
                  >
                    Link
                  </x-brand-button>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-4 py-8 text-center text-brand-500">
                All customers are linked to QuickBooks! 🎉
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($customers->hasPages())
      <div class="px-4 py-3 border-t border-brand-100">
        {{ $customers->links() }}
      </div>
    @endif
  </div>

  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm">
    <h3 class="font-semibold text-blue-900 mb-2">💡 How to Link Customers</h3>
    <ul class="space-y-1 text-blue-800">
      <li><strong>+ Create New in QB:</strong> Creates a new customer in QuickBooks with your local data</li>
      <li><strong>Select existing QB Customer:</strong> Links your local customer to an existing QuickBooks customer</li>
      <li><strong>Sync All to QB:</strong> Automatically creates all unlinked customers in QuickBooks</li>
      <li><strong>Re-sync:</strong> Updates the QuickBooks customer with any changes made locally</li>
    </ul>
  </div>
</div>

@push('scripts')
<script>
function linkCustomer(customerId) {
  const row = document.querySelector(`tr[data-customer-id="${customerId}"]`);
  const select = row.querySelector('select[name="qbo_customer_id"]');
  const qboCustomerId = select.value;
  
  if (!qboCustomerId) {
    alert('Please select a QuickBooks customer or choose "Create New in QB"');
    return;
  }
  
  const button = row.querySelector('[data-action="link-customer"]');
  button.disabled = true;
  button.textContent = 'Linking...';
  
  const formData = new FormData();
  formData.append('_token', '{{ csrf_token() }}');
  formData.append('qbo_customer_id', qboCustomerId);
  
  const url = qboCustomerId === '__create__' 
    ? `/contacts/${customerId}/qbo-sync`
    : `/contacts/${customerId}/qbo-link`;
  
  fetch(url, {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      window.location.reload();
    } else {
      alert(data.message || 'Failed to link customer');
      button.disabled = false;
      button.textContent = 'Link';
    }
  })
  .catch(error => {
    console.error(error);
    alert('An error occurred. Please try again.');
    button.disabled = false;
    button.textContent = 'Link';
  });
}
</script>
@endpush
@endsection
