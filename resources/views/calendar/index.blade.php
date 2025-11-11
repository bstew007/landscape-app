@extends('layouts.sidebar')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Calendar</h1>
            <p class="text-gray-600">{{ $currentMonth->format('F Y') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('calendar.index', ['month' => $previousMonth->month, 'year' => $previousMonth->year]) }}"
               class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                &larr; {{ $previousMonth->format('M Y') }}
            </a>
            <a href="{{ route('calendar.index') }}"
               class="inline-flex items-center rounded-md border border-blue-600 bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                Today
            </a>
            <a href="{{ route('calendar.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
               class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                {{ $nextMonth->format('M Y') }} &rarr;
            </a>
        </div>
    </div>

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
                                <a href="{{ route('clients.site-visits.show', [$visit->client_id, $visit->id]) }}"
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
