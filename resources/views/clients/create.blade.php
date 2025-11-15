@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <x-page-header title="Add Contact" eyebrow="Contact" subtitle="Create a new client record" variant="compact" class="mb-6">
        <x-slot:leading>
            <div class="h-10 w-10 rounded-full bg-brand-600 text-white flex items-center justify-center text-sm font-semibold shadow-sm">➕</div>
        </x-slot:leading>
        <x-slot:actions>
            <x-brand-button href="{{ route('clients.index') }}" class="px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white">Back to clients</x-brand-button>
        </x-slot:actions>
    </x-page-header>

    @include('clients._form', [
        'route' => route('clients.store'),
        'method' => 'POST',
        'client' => new \App\Models\Client(),
        'types' => $types ?? ['lead','client','vendor','owner']
    ])
</div>
@endsection
