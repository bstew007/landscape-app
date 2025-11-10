@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-semibold mb-6">New Site Visit for {{ $client->first_name }}</h1>

    @include('site-visits._form', [
        'route' => route('clients.site-visits.store', $client),
        'method' => 'POST',
        'siteVisit' => new \App\Models\SiteVisit(),
        'properties' => $properties,
        'preferredPropertyId' => $preferredPropertyId ?? null,
    ])
</div>
@endsection
