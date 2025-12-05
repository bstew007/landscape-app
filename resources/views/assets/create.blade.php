@extends('layouts.sidebar')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Branded Header --}}
        <section class="rounded-[20px] sm:rounded-[28px] bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 text-white p-6 sm:p-8 shadow-2xl border border-brand-800/40">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 flex items-center justify-center flex-shrink-0">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7 text-white">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-brand-200/80">Assets</p>
                    <h1 class="text-2xl sm:text-3xl font-semibold text-white mt-1">Add New Asset</h1>
                    <p class="text-sm text-brand-100/85 mt-1">Create a new vehicle or equipment record.</p>
                </div>
            </div>
        </section>

        @if ($errors->any())
            <div class="rounded-xl bg-red-50 border-2 border-red-200 text-red-800 px-5 py-4">
                <div class="flex items-start gap-3">
                    <svg class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="rounded-2xl bg-white border-2 border-brand-100 shadow-sm p-6">
        @include('assets._form', [
            'asset' => $asset,
            'route' => route('assets.store'),
            'method' => 'POST',
        ])
        </div>
    </div>
@endsection
