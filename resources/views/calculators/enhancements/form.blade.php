@extends('layouts.sidebar')

@section('content')
    <div class="max-w-4xl mx-auto py-6">
        <h1 class="text-2xl font-bold mb-4">Landscape Enhancements Calculator</h1>

        <form method="POST" action="{{ url('/calculators/landscape-enhancements') }}">
            @csrf

            {{-- Pruning Section --}}
            @include('calculators.enhancements.partials.pruning')

            {{-- Mulching Section --}}
            @include('calculators.enhancements.partials.mulching')

            {{-- More sections coming soon --}}

            <div class="mt-6">
                <button type="submit" class="btn btn-primary">Calculate</button>
            </div>
        </form>
    </div>
@endsection
