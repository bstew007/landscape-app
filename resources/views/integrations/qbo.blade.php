@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <x-page-header title="QuickBooks Online" eyebrow="Integrations" subtitle="Connect your QuickBooks company to sync contacts as customers.">
    <x-slot:actions>
      @if($token)
        <x-secondary-button as="a" href="{{ route('integrations.qbo.connect') }}">Reconnect</x-secondary-button>
      @else
        <x-brand-button href="{{ route('integrations.qbo.connect') }}">Connect QBO</x-brand-button>
      @endif
    </x-slot:actions>
  </x-page-header>

  @if(session('success'))
    <div class="p-3 rounded border border-brand-200 bg-brand-50 text-brand-900">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="p-3 rounded border border-red-200 bg-red-50 text-red-900">{{ session('error') }}</div>
  @endif

  <div class="bg-white rounded shadow p-4">
    @if($token)
      <p class="text-sm text-gray-700">Connected realm: <span class="font-mono">{{ $token->realm_id }}</span></p>
      <p class="text-sm text-gray-500">Last updated: {{ $token->updated_at->diffForHumans() }}</p>
    @else
      <p class="text-sm text-gray-600">Not connected.</p>
    @endif
  </div>
</div>
@endsection
