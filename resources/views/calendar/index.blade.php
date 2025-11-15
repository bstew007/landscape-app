@extends('layouts.sidebar')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <x-page-header title="Calendar" eyebrow="Schedule" subtitle="{{ $currentMonth->format('F Y') }}">
        <x-slot:actions>
            <x-brand-button href="{{ route('calendar.index', ['month' => $previousMonth->month, 'year' => $previousMonth->year]) }}" variant="outline">&larr; {{ $previousMonth->format('M Y') }}</x-brand-button>
            <x-brand-button href="{{ route('calendar.index') }}">Today</x-brand-button>
            <x-brand-button href="{{ route('calendar.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" variant="outline">{{ $nextMonth->format('M Y') }} &rarr;</x-brand-button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        @php
            $daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        @endphp

        <div class="grid grid-cols-7 bg-gray-50 text-center text-sm font-semibold text-gray-600 uppercase tracking-wide border-b">
            @foreach ($daysOfWeek as $dayName)
                <div class="py-3">{{ $dayName }}</div>
            @endforeach
        </div>

        @foreach ($weeks as $week)
            <div class="grid grid-cols-7 border-b last:border-b-0">
                @foreach ($week as $day)
                    <div class="min-h-[120px] border-r last:border-r-0 p-2 text-sm {{ $day['in_month'] ? 'bg-white' : 'bg-gray-50 text-gray-400' }}">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold {{ $day['is_today'] ? 'text-blue-600' : '' }}">
                                {{ $day['date']->format('j') }}
                            </span>
                            @if ($day['visits']->count() > 0)
                                <span class="inline-flex items-center justify-center rounded-full bg-blue-100 px-2 text-xs font-semibold text-blue-800">
                                    {{ $day['visits']->count() }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-2 space-y-1">
                            @forelse ($day['visits'] as $visit)
                                                                  <a href="{{ route('contacts.site-visits.show', [$visit->client_id, $visit->id]) }}"
                                     class="block rounded border border-blue-100 bg-blue-50 px-2 py-1 text-xs text-blue-900 hover:bg-blue-100">
                                    <p class="font-semibold">
                                        {{ $visit->client->name }}
                                    </p>
                                    <p class="text-[11px] text-blue-800 truncate">
                                        {{ $visit->client->address ?? ($visit->property->address ?? 'No address on file') }}
                                    </p>
                                </a>
                            @empty
                                @if ($day['in_month'])
                                    <p class="text-xs text-gray-400">No visits</p>
                                @endif
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
@endsection
