@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="New Site Visit" eyebrow="Client" subtitle="For {{ $client->first_name }}" />

          @include('site-visits._form', [
          'route' => route('contacts.site-visits.store', $client),
          'method' => 'POST',
          'siteVisit' => new \App\Models\SiteVisit(),
        'properties' => $properties,
        'preferredPropertyId' => $preferredPropertyId ?? null,
    ])
</div>
@endsection
