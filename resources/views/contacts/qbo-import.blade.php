@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
  <x-page-header title="Import from QuickBooks" eyebrow="Contacts" subtitle="Search your QuickBooks Sandbox customers and import them as contacts." />

  @if(session('error'))
    <div class="p-3 rounded border border-red-200 bg-red-50 text-red-900">{{ session('error') }}</div>
  @endif
  @if(session('success'))
    <div class="p-3 rounded border border-brand-200 bg-brand-50 text-brand-900">{{ session('success') }}</div>
  @endif

  <div class="bg-white rounded shadow p-4">
    <form method="GET" action="{{ route('contacts.qbo.search') }}" class="flex flex-wrap gap-2 items-center">
      <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by display name..." class="form-input flex-1 min-w-[240px] border-brand-300 focus:ring-brand-500 focus:border-brand-500" />
      <input type="number" name="max" value="{{ $max ?? 25 }}" min="1" max="100" class="form-input w-24 border-brand-300 focus:ring-brand-500 focus:border-brand-500" title="Max results" />
      <x-brand-button type="submit">Search / List</x-brand-button>
      <a href="{{ route('contacts.qbo.search') }}" class="text-sm text-gray-600 hover:underline">Clear</a>
    </form>

    @if(!empty($results))
      <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wide">
            <tr>
              <th class="px-3 py-2 text-left">Customer</th>
              <th class="px-3 py-2 text-left">Email</th>
              <th class="px-3 py-2 text-left">Phone</th>
              <th class="px-3 py-2 text-left">Address</th>
              <th class="px-3 py-2 text-right"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($results as $c)
              <tr class="border-t">
                <td class="px-3 py-2">
                  <div class="font-medium">{{ $c['DisplayName'] ?? ($c['CompanyName'] ?? '—') }}</div>
                  <div class="text-xs text-gray-500">ID: {{ $c['Id'] ?? '—' }}</div>
                </td>
                <td class="px-3 py-2">{{ $c['PrimaryEmailAddr']['Address'] ?? '—' }}</td>
                <td class="px-3 py-2">{{ $c['PrimaryPhone']['FreeFormNumber'] ?? '—' }}</td>
                <td class="px-3 py-2">
                  @php($addr = $c['BillAddr'] ?? [])
                  {{ ($addr['Line1'] ?? '') }} {{ ($addr['City'] ?? '') }} {{ ($addr['CountrySubDivisionCode'] ?? '') }} {{ ($addr['PostalCode'] ?? '') }}
                </td>
                <td class="px-3 py-2 text-right">
                  <form method="POST" action="{{ route('contacts.qbo.import') }}" class="inline">
                    @csrf
                    <input type="hidden" name="qbo_customer_id" value="{{ $c['Id'] }}" />
                    <x-brand-button type="submit" size="sm">Import</x-brand-button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
        <div class="mt-3 flex justify-between text-xs text-gray-600">
          <div>
            Showing up to {{ $max ?? 25 }} results starting at {{ $start ?? 1 }}
          </div>
          <div class="space-x-2">
            @if(!empty($prevStart))
              <a class="hover:underline" href="{{ route('contacts.qbo.search', array_filter(['q'=>request('q'), 'start'=>$prevStart, 'max'=>$max ?? 25])) }}">Prev</a>
            @endif
            @if(!empty($nextStart))
              <a class="hover:underline" href="{{ route('contacts.qbo.search', array_filter(['q'=>request('q'), 'start'=>$nextStart, 'max'=>$max ?? 25])) }}">Next</a>
            @endif
          </div>
        </div>
      </div>
    @else
      <p class="mt-4 text-sm text-gray-600">Enter a term and click Search, or leave blank and click Search / List to list all customers.</p>
    @endif
  </div>
</div>
@endsection
