@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
  <x-page-header title="Import Vendors from QuickBooks" eyebrow="Contacts" subtitle="Search your QuickBooks vendors and import or link them to contacts." />

  @if(session('error') || !empty($error))
    <div class="p-3 rounded border border-red-200 bg-red-50 text-red-900">{{ session('error') ?? $error }}</div>
  @endif
  @if(session('success'))
    <div class="p-3 rounded border border-brand-200 bg-brand-50 text-brand-900">{{ session('success') }}</div>
  @endif

  <div class="bg-white rounded shadow p-4">
    <form method="GET" action="{{ route('contacts.qbo.vendor.search') }}" class="flex flex-wrap gap-2 items-center">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by vendor name..." class="form-input flex-1 min-w-[240px] border-brand-300 focus:ring-brand-500 focus:border-brand-500" />
      <input type="number" name="max" value="{{ $max ?? 25 }}" min="1" max="100" class="form-input w-24 border-brand-300 focus:ring-brand-500 focus:border-brand-500" title="Max results" />
      <input type="hidden" name="fetch_all" value="1" />
      <x-brand-button type="submit">Search / List</x-brand-button>
      <a href="{{ route('contacts.qbo.vendor.search') }}" class="text-sm text-gray-600 hover:underline">Clear</a>
    </form>

    @if(!empty($vendors))
      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
            <tr>
              <th class="px-3 py-2 text-left">Vendor</th>
              <th class="px-3 py-2 text-left">Email</th>
              <th class="px-3 py-2 text-left">Phone</th>
              <th class="px-3 py-2 text-left">Address</th>
              <th class="px-3 py-2 text-left">Status</th>
              <th class="px-3 py-2 text-right">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach($vendors as $v)
              <tr class="border-t {{ $v['is_linked'] ? 'bg-gray-50' : '' }}">
                <td class="px-3 py-2">
                  <div class="font-medium">{{ $v['DisplayName'] ?? ($v['CompanyName'] ?? '—') }}</div>
                  <div class="text-xs text-gray-500">QBO ID: {{ $v['Id'] ?? '—' }}</div>
                </td>
                <td class="px-3 py-2">{{ $v['PrimaryEmailAddr']['Address'] ?? '—' }}</td>
                <td class="px-3 py-2">{{ $v['PrimaryPhone']['FreeFormNumber'] ?? '—' }}</td>
                <td class="px-3 py-2">
                  @php($addr = $v['BillAddr'] ?? [])
                  @if(!empty($addr))
                    {{ ($addr['Line1'] ?? '') }} {{ ($addr['City'] ?? '') }} {{ ($addr['CountrySubDivisionCode'] ?? '') }} {{ ($addr['PostalCode'] ?? '') }}
                  @else
                    <span class="text-gray-400">—</span>
                  @endif
                </td>
                <td class="px-3 py-2">
                  @if($v['is_linked'])
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs">
                      ✓ Linked
                    </span>
                    @if($v['local_contact'])
                      <div class="text-xs text-gray-500 mt-1">
                        <a href="{{ route('contacts.show', $v['local_contact']) }}" class="hover:underline">
                          {{ $v['local_contact']->name }}
                        </a>
                      </div>
                    @endif
                  @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 border border-gray-200 text-xs">
                      Not Linked
                    </span>
                  @endif
                </td>
                <td class="px-3 py-2 text-right">
                  @if(!$v['is_linked'])
                    <form method="POST" action="{{ route('contacts.qbo.vendor.import') }}" class="inline">
                      @csrf
                      <input type="hidden" name="qbo_vendor_id" value="{{ $v['Id'] }}" />
                      <x-brand-button type="submit" size="sm">Import as New</x-brand-button>
                    </form>
                  @else
                    <span class="text-xs text-gray-400">Already linked</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="mt-3 flex items-center justify-between text-xs text-gray-600">
        <div>
          Showing {{ count($vendors) }} vendor{{ count($vendors) !== 1 ? 's' : '' }} starting at {{ $start ?? 1 }}
        </div>
        <div class="flex items-center gap-3">
          @if(($start ?? 1) > 1)
            @php($prevStart = max(1, ($start ?? 1) - ($max ?? 25)))
            <a class="hover:underline" href="{{ route('contacts.qbo.vendor.search', array_filter(['q'=>request('q'), 'start'=>$prevStart, 'max'=>$max ?? 25])) }}">← Prev</a>
          @endif
          @if(count($vendors) >= ($max ?? 25))
            @php($nextStart = ($start ?? 1) + ($max ?? 25))
            <a class="hover:underline" href="{{ route('contacts.qbo.vendor.search', array_filter(['q'=>request('q'), 'start'=>$nextStart, 'max'=>$max ?? 25])) }}">Next →</a>
          @endif
        </div>
      </div>
    @else
      <p class="mt-4 text-sm text-gray-600">Enter a vendor name and click Search, or leave blank to list all QuickBooks vendors.</p>
    @endif
  </div>
  
  <div class="bg-blue-50 border border-blue-200 rounded p-4 text-sm">
    <h3 class="font-semibold text-blue-900 mb-2">💡 How to Link Vendors</h3>
    <ul class="space-y-1 text-blue-800">
      <li><strong>Import as New:</strong> Creates a new contact from the QuickBooks vendor</li>
      <li><strong>Already Linked:</strong> This QB vendor is already connected to a local contact</li>
      <li><strong>Manual Link:</strong> To link an existing contact, edit the contact and use the "Link to QB Vendor" button</li>
    </ul>
  </div>
</div>
@endsection
