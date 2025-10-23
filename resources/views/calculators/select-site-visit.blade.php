@extends('layouts.sidebar')

@section('content')
    <div class="max-w-xl mx-auto bg-white shadow-md rounded px-6 py-8">
        <h1 class="text-2xl font-bold mb-6">🔍 Select a Site Visit</h1>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form method="GET" action="" id="siteVisitForm">
            {{-- Site Visit Dropdown --}}
            <div class="mb-4">
                <label for="site_visit_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Site Visit:
                </label>
                <select name="site_visit_id" id="site_visit_id" required class="block w-full border rounded px-3 py-2">
                    @foreach ($siteVisits as $visit)
                        @php
                            $client = optional($visit->client);
                            $clientName = trim($client->first_name . ' ' . $client->last_name) ?: 'No Client';
                            $formattedDate = $visit->visit_date->format('M d, Y');
                        @endphp
                        <option value="{{ $visit->id }}">
                            [ID: {{ $visit->client_id }}] {{ $clientName }} — {{ $formattedDate }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Hidden redirect target --}}
            <input type="hidden" name="redirect_to" id="redirect_to" value="{{ $redirectTo }}">

            {{-- Submit --}}
            <div class="mt-6">
                <button type="submit"
                        class="w-full bg-blue-600 text-white font-semibold py-2 px-4 rounded hover:bg-blue-700">
                    ➡️ Continue to Calculator
                </button>
            </div>
        </form>
    </div>

    {{-- JavaScript to handle redirect --}}
    <script>
        document.getElementById('siteVisitForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const form = e.target;
            const siteVisitId = form.site_visit_id.value;
            const redirectUrl = form.redirect_to.value;

            if (!siteVisitId || !redirectUrl) {
                alert('Missing required information.');
                return;
            }

            const fullUrl = redirectUrl.includes('?')
                ? `${redirectUrl}&site_visit_id=${siteVisitId}`
                : `${redirectUrl}?site_visit_id=${siteVisitId}`;

            window.location.href = fullUrl;
        });
    </script>
@endsection

