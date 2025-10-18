@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-semibold mb-6">✏️ Edit Site Visit</h1>

    @include('site-visits._form', [
        'route' => route('clients.site-visits.update', [$client, $siteVisit]),
        'method' => 'PUT',
        'siteVisit' => $siteVisit
    ])
</div>
@endsection
