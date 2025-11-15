<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name', 'Landscape Estimator') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 font-sans antialiased" x-data="{ sidebarOpen: false }">

<div class="min-h-screen flex">

    {{-- Sidebar --}}
    <aside class="w-64 bg-gray-900 text-gray-100 shadow-md hidden md:block">
                <div class="p-6 font-bold text-lg border-b border-gray-800 text-white">
            🌿 CFL Landscape
        </div>

        {{-- Client Hub (moved to top) --}}
        <nav class="mt-4 px-4 space-y-6 text-sm">
            <div>
                <h3 class="text-xs text-gray-400 uppercase tracking-wide mb-1">Client Hub</h3>
                <a href="{{ route('client-hub') }}" class="block px-2 py-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M12 3L3 10v11h7v-7h4v7h7V10l-9-7z"/></svg><span>Home Dashboard</span></span></a>
                <a href="{{ route('contacts.index') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="10" r="3"/><path d="M2 21c0-3.314 2.686-6 6-6h2M22 21c0-3.314-2.686-6-6-6h-2"/></svg><span>Contacts</span></span></a>
                <a href="{{ route('calendar.index') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg><span>Schedule</span></span></a>
                <a href="{{ route('todos.index') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 12l2 2 4-4"/></svg><span>To-Do Board</span></span></a>
                <a href="{{ route('estimates.index') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M7 2h7l5 5v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v5h5"/></svg><span>Estimates</span></span></a>
                <a href="{{ route('calculator.templates.gallery') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M3 9h18"/></svg><span>Calculator Templates</span></span></a>
            </div>

            <div>
                <h3 class="text-xs text-gray-400 uppercase tracking-wide mb-1">Assets & Equipment</h3>
                <a href="{{ route('assets.index') }}" class="block px-2 py-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M14.7 6.3a5 5 0 1 0-8.4 5.4l-4 4a2 2 0 1 0 2.8 2.8l4-4a5 5 0 0 0 5.6-8.2z"/></svg><span>Dashboard</span></span></a>
                <a href="{{ route('assets.create') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M12 5v14M5 12h14"/></svg><span>Add Asset</span></span></a>
                <a href="{{ route('assets.issues.create') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M12 3v4M6 7l-2-2M18 7l2-2M4 13h16M5 17h14M7 10h10"/></svg><span>Log Issue</span></span></a>
                <a href="{{ route('assets.reminders.create') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><circle cx="12" cy="13" r="7"/><path d="M12 10v4l3 2M7 3h3M14 3h3"/></svg><span>Schedule Reminder</span></span></a>
            </div>
        </nav>

        {{-- Admin Section (moved to bottom) --}}
        <div class="mt-8 border-t border-gray-800 pt-4" @cannot('manage-users') x-data="{}" @endcannot>
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wide px-4 mb-2">Admin</h2>
            <ul>
                <li>
                    <a href="{{ route('production-rates.index') }}"
                       class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-800">
                        <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M12 3v4M6 7l-2-2M18 7l2-2M4 13h16M5 17h14M7 10h10"/></svg><span>Production Rates</span></span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.budgets.index') }}"
                       class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-800">
                        <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 10h8M8 14h5"/></svg><span>Company Budget</span></span>
                    </a>
                </li>
                @can('manage-users')
                <li>
                    <a href="{{ route('admin.users.index') }}"
                       class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-800">
                        <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><circle cx="12" cy="8" r="4"/><path d="M2 21c0-4 4-7 10-7s10 3 10 7"/></svg><span>Users</span></span>
                    </a>
                </li>
                @endcan
                <li>
                    <a href="{{ route('materials.index') }}"
                       class="block px-4 py-2 text-sm text-gray-200 hover:bg-gray-800">
                        <span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M3 9h18"/></svg><span>Materials Catalog</span></span>
                    </a>
                </li>
            </ul>
        </div>

    </aside>

      
    {{-- Mobile Sidebar --}}
    <div class="md:hidden fixed inset-0 bg-black bg-opacity-50 z-40" x-show="sidebarOpen" @click="sidebarOpen = false"></div>

    <aside class="fixed inset-y-0 left-0 bg-gray-900 text-gray-100 w-64 shadow-md z-50 transform transition-transform duration-300 md:hidden"
           x-show="sidebarOpen"
           x-transition:enter="transform transition-transform duration-300"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transform transition-transform duration-300"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full">
        <div class="p-6 font-bold text-lg border-b border-gray-800 text-white">🌿 CFL Landscape</div>

        <nav class="mt-4 px-4 space-y-6 text-sm">
            <div>
                <h3 class="text-xs text-gray-400 uppercase tracking-wide mb-1">Admin</h3>
                <a href="{{ route('production-rates.index') }}" class="block px-2 py-1 rounded text-gray-200 hover:bg-gray-800">⚙️ Production Rates</a>
                <a href="{{ route('admin.budgets.index') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800">💼 Company Budget</a>
                <a href="{{ route('materials.index') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800">🧱 Materials Catalog</a>
            </div>
            <div>
                <h3 class="text-xs text-gray-400 uppercase tracking-wide mb-1">Client Hub</h3>
                <a href="{{ route('client-hub') }}" class="block px-2 py-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M12 3L3 10v11h7v-7h4v7h7V10l-9-7z"/></svg><span>Dashboard</span></span></a>
                <a href="{{ route('contacts.index') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="10" r="3"/><path d="M2 21c0-3.314 2.686-6 6-6h2M22 21c0-3.314-2.686-6-6-6h-2"/></svg><span>Contacts</span></span></a>
                <a href="{{ route('calendar.index') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg><span>Schedule</span></span></a>
                <a href="{{ route('todos.index') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 12l2 2 4-4"/></svg><span>To-Do Board</span></span></a>
                <a href="{{ route('estimates.index') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M7 2h7l5 5v13a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/><path d="M14 2v5h5"/></svg><span>Estimates</span></span></a>
            </div>

            <div>
                <h3 class="text-xs text-gray-400 uppercase tracking-wide mb-1">Assets & Equipment</h3>
                <a href="{{ route('assets.index') }}" class="block px-2 py-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M14.7 6.3a5 5 0 1 0-8.4 5.4l-4 4a2 2 0 1 0 2.8 2.8l4-4a5 5 0 0 0 5.6-8.2z"/></svg><span>Dashboard</span></span></a>
                <a href="{{ route('assets.create') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M12 5v14M5 12h14"/></svg><span>Add Asset</span></span></a>
                <a href="{{ route('assets.issues.create') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><path d="M12 3v4M6 7l-2-2M18 7l2-2M4 13h16M5 17h14M7 10h10"/></svg><span>Log Issue</span></span></a>
                <a href="{{ route('assets.reminders.create') }}" class="block px-2 py-1 mt-1 rounded text-gray-200 hover:bg-gray-800"><span class="inline-flex items-center"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 mr-2"><circle cx="12" cy="13" r="7"/><path d="M12 10v4l3 2M7 3h3M14 3h3"/></svg><span>Schedule Reminder</span></span></a>
            </div>
        </nav>

    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-h-screen">

        {{-- Top Nav --}}
        <header class="sticky top-0 z-30 bg-white shadow-md">
            <div class="flex items-center justify-between px-4 py-3">
                <button @click="sidebarOpen = true" class="md:hidden text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div class="font-semibold text-lg text-gray-800">CFL Estimating</div>
                <div></div> {{-- Placeholder for spacing or user menu --}}
            </div>
        </header>


        {{-- Breadcrumb Slot --}}
        @isset($header)
            <div class="bg-gray-100 border-b">
                <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8 text-sm text-gray-600">
                    {{ $header }}
                </div>
            </div>
        @endisset

        {{-- Main Page Content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>
</div>

<!-- Toasts -->
<div id="toastRoot" class="fixed bottom-4 right-4 z-[200] space-y-2"></div>
<script>
(function(){
  if (window.showToast) return; // don't redefine
  function el(tag, cls){ const e = document.createElement(tag); if(cls) e.className = cls; return e; }
  function makeToast(msg, type){
    const root = document.getElementById('toastRoot'); if (!root) return;
    const wrap = el('div', 'pointer-events-auto min-w-[220px] max-w-sm rounded shadow-lg border p-3 text-sm flex items-start gap-2 transition-all duration-300');
    const colors = type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : type === 'error' ? 'bg-red-50 border-red-200 text-red-900' : 'bg-gray-50 border-gray-200 text-gray-900';
    wrap.className += ' ' + colors;
    const text = el('div'); text.textContent = String(msg || '');
    const btn = el('button','ml-auto text-xs opacity-60 hover:opacity-100'); btn.textContent = 'Dismiss'; btn.onclick = ()=>{ wrap.style.opacity='0'; setTimeout(()=>wrap.remove(), 200); };
    wrap.appendChild(text); wrap.appendChild(btn); root.appendChild(wrap);
    setTimeout(()=>{ wrap.style.opacity='0'; setTimeout(()=>wrap.remove(), 250); }, 3500);
  }
  window.showToast = makeToast;
})();
</script>

{{-- Google Places (via .env key) --}}
@if (config('services.google_places.key'))
<script>
  window.PLACES_COUNTRIES = "{{ config('services.google_places.country', 'us') }}".split(',');
  (function loadGoogleMapsPlaces(){
    if (window.__gmapsLoader) {
      window.__gmapsLoader.then(() => { if (window.__initPlaces) window.__initPlaces(); });
      return;
    }
    const params = new URLSearchParams({
      key: "{{ config('services.google_places.key') }}",
      v: 'weekly',
      libraries: 'places',
      loading: 'async',
    });
    window.__gmapsLoader = new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = `https://maps.googleapis.com/maps/api/js?${params.toString()}`;
      script.async = true;
      script.defer = true;
      script.addEventListener('load', resolve, { once: true });
      script.addEventListener('error', reject, { once: true });
      document.head.appendChild(script);
    })
    .then(() => {
      if (window.google?.maps?.importLibrary) {
        return window.google.maps.importLibrary('places');
      }
      return null;
    })
    .then(() => { if (window.__initPlaces) window.__initPlaces(); })
    .catch((err) => console.error('Google Maps JS failed to load', err));
  })();
</script>
@endif

 @stack('scripts')
</body>
</html>
