{{-- Shared Client Info block for calculator views --}}
{{-- Expects: $siteVisit (with client relation loaded) --}}
<div class="bg-white p-6 rounded-lg shadow mb-8 mt-10">
    <hr class="my-4">
    <h2 class="text-2xl font-semibold mb-4">👤 Client Information: {{ $siteVisit->client->name }}</h2>
    <table class="mb-6">
        <tr><td class="pr-4 font-semibold">Name:</td><td>{{ $siteVisit->client->first_name }} {{ $siteVisit->client->last_name }}</td></tr>
        <tr><td class="pr-4 font-semibold">Email:</td><td>{{ $siteVisit->client->email ?? '—' }}</td></tr>
        <tr><td class="pr-4 font-semibold">Phone:</td><td>{{ $siteVisit->client->phone ?? '—' }}</td></tr>
        <tr><td class="pr-4 font-semibold">Address:</td><td>{{ $siteVisit->client->address ?? '—' }}</td></tr>
        <tr><td class="pr-4 font-semibold">Site Visit Date:</td><td>{{ optional($siteVisit->created_at)->format('F j, Y') }}</td></tr>
    </table>
</div>
