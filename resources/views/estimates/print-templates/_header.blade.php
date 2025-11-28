@php
    use App\Models\CompanySetting;
    $company = CompanySetting::getSettings();
@endphp

<div class="header clearfix">
    <div class="company-info">
        <h1 class="company-name">{{ $company->company_name }}</h1>
        <div class="company-details">
            @if($company->address)
                <p>{{ $company->address }}</p>
            @endif
            @if($company->city || $company->state || $company->postal_code)
                <p>{{ collect([$company->city, $company->state])->filter()->join(', ') }} {{ $company->postal_code }}</p>
            @endif
            @if($company->phone)
                <p>📞 {{ $company->phone }}</p>
            @endif
            @if($company->email)
                <p>✉️ {{ $company->email }}</p>
            @endif
            @if($company->website)
                <p>🌐 {{ $company->website }}</p>
            @endif
        </div>
    </div>
    
    <div class="company-logo-wrapper">
        @if($company->logo_path && file_exists(public_path($company->logo_path)))
            <img src="{{ asset($company->logo_path) }}" alt="{{ $company->company_name }} Logo" class="company-logo">
        @elseif(file_exists(public_path('images/logo.png')))
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="company-logo">
        @elseif(file_exists(public_path('images/logo.svg')))
            <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="company-logo">
        @endif
    </div>
</div>
