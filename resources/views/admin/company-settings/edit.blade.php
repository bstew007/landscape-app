@extends('layouts.sidebar')

@section('content')
<div class="space-y-8 max-w-4xl mx-auto p-4">
    <section class="rounded-[32px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40 relative overflow-hidden">
        <div class="flex flex-wrap items-start gap-6">
            <div class="space-y-2 max-w-2xl">
                <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Admin / Settings</p>
                <h1 class="text-2xl sm:text-3xl font-semibold">Company Settings</h1>
                <p class="text-sm text-brand-100/85">Configure your company information for documents and forms</p>
            </div>
        </div>
    </section>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3">
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-[32px] bg-white shadow-2xl border border-brand-100/60 overflow-hidden p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.company-settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Company Name -->
            <div>
                <label for="company_name" class="block text-sm font-semibold text-gray-900 mb-2">
                    Company Name <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="company_name" 
                    name="company_name" 
                    value="{{ old('company_name', $settings->company_name) }}" 
                    required
                    class="form-input w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring-brand-500"
                    placeholder="Your Company Name">
            </div>

            <!-- Address -->
            <div>
                <label for="address" class="block text-sm font-semibold text-gray-900 mb-2">
                    Street Address
                </label>
                <input 
                    type="text" 
                    id="address" 
                    name="address" 
                    value="{{ old('address', $settings->address) }}"
                    class="form-input w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring-brand-500"
                    placeholder="123 Business St">
            </div>

            <!-- City, State, Postal Code -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="city" class="block text-sm font-semibold text-gray-900 mb-2">City</label>
                    <input 
                        type="text" 
                        id="city" 
                        name="city" 
                        value="{{ old('city', $settings->city) }}"
                        class="form-input w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring-brand-500"
                        placeholder="City">
                </div>
                <div>
                    <label for="state" class="block text-sm font-semibold text-gray-900 mb-2">State</label>
                    <input 
                        type="text" 
                        id="state" 
                        name="state" 
                        value="{{ old('state', $settings->state) }}"
                        class="form-input w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring-brand-500"
                        placeholder="NC"
                        maxlength="2">
                </div>
                <div>
                    <label for="postal_code" class="block text-sm font-semibold text-gray-900 mb-2">Postal Code</label>
                    <input 
                        type="text" 
                        id="postal_code" 
                        name="postal_code" 
                        value="{{ old('postal_code', $settings->postal_code) }}"
                        class="form-input w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring-brand-500"
                        placeholder="12345">
                </div>
            </div>

            <!-- Phone and Email -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-900 mb-2">Phone</label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        value="{{ old('phone', $settings->phone) }}"
                        class="form-input w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring-brand-500"
                        placeholder="(555) 123-4567">
                </div>
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-900 mb-2">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email', $settings->email) }}"
                        class="form-input w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring-brand-500"
                        placeholder="info@company.com">
                </div>
            </div>

            <!-- Website -->
            <div>
                <label for="website" class="block text-sm font-semibold text-gray-900 mb-2">Website</label>
                <input 
                    type="url" 
                    id="website" 
                    name="website" 
                    value="{{ old('website', $settings->website) }}"
                    class="form-input w-full rounded-lg border-brand-200 focus:border-brand-500 focus:ring-brand-500"
                    placeholder="https://www.yourcompany.com">
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3 pt-4">
                <button 
                    type="submit" 
                    class="inline-flex items-center px-6 py-3 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition font-semibold">
                    Save Settings
                </button>
                <a 
                    href="{{ route('admin.material-categories.index') }}" 
                    class="inline-flex items-center px-6 py-3 border border-brand-300 rounded-lg text-brand-700 hover:bg-brand-50 transition">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</div>
@endsection
