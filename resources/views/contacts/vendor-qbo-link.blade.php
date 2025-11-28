@extends('layouts.sidebar')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
  <x-page-header 
    title="Link Vendors to QuickBooks" 
    eyebrow="Vendor Management" 
    subtitle="Match your local vendors to QuickBooks vendors or create new ones." 
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
        <h2 class="text-lg font-semibold text-brand-900">Vendor Linking</h2>
        <p class="text-sm text-brand-600 mt-1">{{ $unlinkedCount }} vendor{{ $unlinkedCount !== 1 ? 's' : '' }} need linking</p>
      </div>
      <div class="flex gap-2">
        <form method="POST" action="{{ route('contacts.qbo.vendor.sync-all') }}" onsubmit="return confirm('Create all unlinked vendors in QuickBooks?');">
          @csrf
          <x-brand-button type="submit" variant="outline">
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Sync All to QB
          </x-brand-button>
        </form>
        <x-secondary-button as="a" href="{{ route('contacts.qbo.vendor.search') }}">
          Browse QB Vendors
        </x-secondary-button>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-brand-50/80 text-left text-xs uppercase tracking-wide text-brand-600">
          <tr>
            <th class="px-4 py-3 w-1/3">Local Vendor</th>
            <th class="px-4 py-3 w-1/3">QuickBooks Vendor</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-brand-50">
          @forelse($vendors as $vendor)
            <tr class="hover:bg-brand-50/30 transition" data-vendor-id="{{ $vendor->id }}">
              <td class="px-4 py-3">
                <div class="font-medium text-brand-900">{{ $vendor->company_name ?: $vendor->name }}</div>
                <div class="text-xs text-brand-500">
                  ID: {{ $vendor->id }}
                  @if($vendor->phone)
                    · {{ $vendor->phone }}
                  @endif
                </div>
                <div class="text-xs text-brand-400 mt-1">
                  {{ $vendor->materials_count ?? 0 }} material{{ ($vendor->materials_count ?? 0) !== 1 ? 's' : '' }}
                </div>
              </td>
              
              <td class="px-4 py-3">
                @if($vendor->qbo_vendor_id)
                  <div class="font-medium text-emerald-700">
                    {{ $vendor->qbo_vendor_name ?? 'Linked' }}
                  </div>
                  <div class="text-xs text-emerald-600">QBO ID: {{ $vendor->qbo_vendor_id }}</div>
                @else
                  <select 
                    name="qbo_vendor_id" 
                    class="form-select text-sm border-brand-300 rounded focus:ring-brand-500 focus:border-brand-500 w-full"
                    data-vendor-local-id="{{ $vendor->id }}"
                  >
                    <option value="">Select QB Vendor...</option>
                    <option value="__create__" class="font-semibold text-brand-700">+ Create New in QB</option>
                    @foreach($qboVendors as $qv)
                      <option value="{{ $qv['Id'] }}" 
                        {{ collect($linkedQboIds)->contains($qv['Id']) ? 'disabled' : '' }}
                      >
                        {{ $qv['DisplayName'] }} 
                        @if(collect($linkedQboIds)->contains($qv['Id']))
                          (Already Linked)
                        @endif
                      </option>
                    @endforeach
                  </select>
                @endif
              </td>
              
              <td class="px-4 py-3">
                @if($vendor->qbo_vendor_id)
                  @php
                    $needsSync = $vendor->qbo_last_synced_at && $vendor->updated_at && $vendor->updated_at->gt($vendor->qbo_last_synced_at);
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
                  @if($vendor->qbo_last_synced_at)
                    <div class="text-xs text-brand-400 mt-1">{{ $vendor->qbo_last_synced_at->diffForHumans() }}</div>
                  @endif
                @else
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-50 text-gray-700 border border-gray-200 text-xs">
                    Not Linked
                  </span>
                @endif
              </td>
              
              <td class="px-4 py-3 text-right">
                @if($vendor->qbo_vendor_id)
                  <form method="POST" action="{{ route('contacts.qbo.vendor.sync', $vendor) }}" class="inline">
                    @csrf
                    <x-brand-button type="submit" size="sm" variant="outline">
                      Re-sync
                    </x-brand-button>
                  </form>
                @else
                  <x-brand-button 
                    size="sm" 
                    data-action="link-vendor" 
                    data-vendor-id="{{ $vendor->id }}"
                    onclick="linkVendor({{ $vendor->id }})"
                  >
                    Link
                  </x-brand-button>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-4 py-8 text-center text-brand-500">
                All vendors are linked to QuickBooks! 🎉
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($vendors->hasPages())
      <div class="px-4 py-3 border-t border-brand-100">
        {{ $vendors->links() }}
      </div>
    @endif
  </div>

  <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm">
    <h3 class="font-semibold text-blue-900 mb-2">💡 How to Link Vendors</h3>
    <ul class="space-y-1 text-blue-800">
      <li><strong>+ Create New in QB:</strong> Creates a new vendor in QuickBooks with your local data</li>
      <li><strong>Select existing QB Vendor:</strong> Links your local vendor to an existing QuickBooks vendor</li>
      <li><strong>Sync All to QB:</strong> Automatically creates all unlinked vendors in QuickBooks</li>
      <li><strong>Re-sync:</strong> Updates the QuickBooks vendor with any changes made locally</li>
    </ul>
  </div>
</div>

@push('scripts')
<script>
function linkVendor(vendorId) {
  const row = document.querySelector(`tr[data-vendor-id="${vendorId}"]`);
  const select = row.querySelector('select[name="qbo_vendor_id"]');
  const qboVendorId = select.value;
  
  if (!qboVendorId) {
    alert('Please select a QuickBooks vendor or choose "Create New in QB"');
    return;
  }
  
  const button = row.querySelector('[data-action="link-vendor"]');
  button.disabled = true;
  button.textContent = 'Linking...';
  
  const formData = new FormData();
  formData.append('_token', '{{ csrf_token() }}');
  formData.append('qbo_vendor_id', qboVendorId);
  
  const url = qboVendorId === '__create__' 
    ? `/contacts/${vendorId}/qbo-vendor-sync`
    : `/contacts/${vendorId}/qbo-vendor-link`;
  
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
      alert(data.message || 'Failed to link vendor');
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
