@php
    use App\Models\CompanySetting;
    $company = CompanySetting::getSettings();
@endphp

<!-- Header with Logo on Right -->
<div class="header-new">
    <div class="header-left-new">
        <h1 class="estimate-title">Estimate #{{ $estimate->id }}</h1>
        <p class="estimate-subtitle">{{ $estimate->title }}</p>
    </div>
    <div class="header-right-new">
        @if($company->logo_path && file_exists(public_path($company->logo_path)))
            <img src="{{ asset($company->logo_path) }}" alt="{{ $company->company_name }} Logo" class="company-logo-new">
        @elseif(file_exists(public_path('images/logo.png')))
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="company-logo-new">
        @elseif(file_exists(public_path('images/logo.svg')))
            <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="company-logo-new">
        @endif
    </div>
</div>

<!-- Two-Column Layout for Company and Client Info -->
<div class="two-col-info">
    <!-- Company Information Table -->
    <div class="info-half">
        <table class="info-table-bordered">
            <tr>
                <td colspan="2" class="table-header-dark">Company Information</td>
            </tr>
            <tr>
                <td class="label-cell">Company Name</td>
                <td>{{ $company->company_name }}</td>
            </tr>
            @if($company->address)
            <tr>
                <td class="label-cell">Address</td>
                <td>{{ $company->address }}</td>
            </tr>
            @endif
            @if($company->city || $company->state || $company->postal_code)
            <tr>
                <td class="label-cell">City, State ZIP</td>
                <td>{{ collect([$company->city, $company->state])->filter()->join(', ') }} {{ $company->postal_code }}</td>
            </tr>
            @endif
            @if($company->phone)
            <tr>
                <td class="label-cell">Phone</td>
                <td>{{ $company->phone }}</td>
            </tr>
            @endif
            @if($company->email)
            <tr>
                <td class="label-cell">Email</td>
                <td>{{ $company->email }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    <!-- Client Information Table -->
    <div class="info-half">
        <table class="info-table-bordered">
            <tr>
                <td colspan="2" class="table-header-dark">Client Information</td>
            </tr>
            <tr>
                <td class="label-cell">Client Name</td>
                <td>{{ $estimate->client->name }}</td>
            </tr>
            @if($estimate->property)
            <tr>
                <td class="label-cell">Property</td>
                <td>{{ $estimate->property->name }}</td>
            </tr>
            @endif
            <tr>
                <td class="label-cell">Status</td>
                <td>{{ ucfirst($estimate->status) }}</td>
            </tr>
            <tr>
                <td class="label-cell">Created Date</td>
                <td>{{ $estimate->created_at->format('M j, Y') }}</td>
            </tr>
            @if($estimate->expires_at)
            <tr>
                <td class="label-cell">Expires</td>
                <td>{{ $estimate->expires_at->format('M j, Y') }}</td>
            </tr>
            @endif
        </table>
    </div>
</div>
