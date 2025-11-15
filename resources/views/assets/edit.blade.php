@extends('layouts.sidebar')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <x-page-header title="Edit Asset" eyebrow="Assets" subtitle="Update asset details and status." />

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-6">
        @include('assets._form', [
            'asset' => $asset,
            'route' => route('assets.update', $asset),
            'method' => 'PUT',
        ])
        </div>
    </div>
@endsection
