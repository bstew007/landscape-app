@extends('layouts.sidebar')

@section('content')
    <div class="max-w-4xl mx-auto py-6">
        <h1 class="text-2xl font-bold mb-4">Landscape Enhancements Calculator</h1>

        <form method="POST" action="{{ route('calculators.enhancements.calculate') }}">

            @csrf
                 <input type="hidden" name="site_visit_id" value="{{ $siteVisit->id }}">
            {{-- Pruning Section --}}
            @include('calculators.enhancements.partials.pruning')

             {{-- Weeding Section --}}
            @include('calculators.enhancements.partials.weeding')

            {{-- Mulching Section --}}
            @include('calculators.enhancements.partials.mulching')

            {{-- Pine Needle Section --}}
            @include('calculators.enhancements.partials.pine-needles')

            {{-- More sections coming soon --}}

               {{-- Bottom Action Buttons --}}
<div class="flex flex-col sm:flex-row sm:items-center gap-4 mt-6">
    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
        {{ $editMode ? '🔄 Recalculate' : '🧮 Calculate Enhancement Estimate' }}
    </button>

    <a href="{{ route('clients.show', $siteVisitId) }}"
       class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-3 rounded-lg font-semibold">
        🔙 Back to Site Visit
    </a>
</div>
        </form>
    </div>

 

@endsection
