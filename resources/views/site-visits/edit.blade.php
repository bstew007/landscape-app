@extends('layouts.sidebar')

@section('content')
<div class="max-w-3xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">Edit Site Visit</h1>

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-800 border border-red-300 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

          @include('site-visits._form', [
          'route' => route('contacts.site-visits.update', [$client, $siteVisit]),
          'method' => 'PUT',
          'siteVisit' => $siteVisit,
        'properties' => $properties,
        'preferredPropertyId' => $preferredPropertyId ?? null,
    ])
</div>
@endsection
