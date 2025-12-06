@extends('layouts.sidebar')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
  <x-page-header 
    title="Add Expense" 
    eyebrow="Assets & Equipment" 
    subtitle="Select an asset to add an expense.">
  </x-page-header>

  <div class="bg-white rounded-2xl border-2 border-brand-100 shadow-sm p-6">
    <div class="mb-4">
      <input type="text" id="assetSearch" placeholder="Search assets..." 
             class="w-full px-4 py-2.5 border-2 border-brand-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 transition-all">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="assetGrid">
      @forelse($assets as $asset)
        <a href="{{ route('assets.expenses.create', $asset) }}" 
           class="asset-card border-2 border-brand-100 rounded-xl p-4 hover:border-brand-500 hover:shadow-md transition-all group"
           data-name="{{ strtolower($asset->name) }}"
           data-tag="{{ strtolower($asset->asset_tag ?? '') }}"
           data-type="{{ strtolower($asset->type) }}">
          <div class="flex items-start gap-3">
            <div class="h-12 w-12 bg-brand-100 rounded-lg flex items-center justify-center flex-shrink-0 group-hover:bg-brand-500 transition-colors">
              <svg class="h-6 w-6 text-brand-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($asset->type === 'vehicle')
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12M8 11h12m-8 4h8m-12-4v8m0 0H4m4 0a2 2 0 11-4 0 2 2 0 014 0zm12 0a2 2 0 11-4 0 2 2 0 014 0z"/>
                @elseif($asset->type === 'trailer')
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                @else
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a5 5 0 1 0-8.4 5.4l-4 4a2 2 0 1 0 2.8 2.8l4-4a5 5 0 0 0 5.6-8.2z"/>
                @endif
              </svg>
            </div>
            
            <div class="flex-1 min-w-0">
              <h3 class="font-bold text-brand-900 group-hover:text-brand-600 transition-colors truncate">{{ $asset->name }}</h3>
              <div class="flex items-center gap-2 mt-1">
                @if($asset->asset_tag)
                  <span class="text-xs px-2 py-0.5 rounded-full bg-brand-100 text-brand-700 font-semibold">
                    #{{ $asset->asset_tag }}
                  </span>
                @endif
                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-semibold">
                  {{ ucfirst($asset->type) }}
                </span>
              </div>
              @if($asset->make || $asset->model)
                <p class="text-xs text-brand-600 mt-1 truncate">
                  {{ $asset->make }} {{ $asset->model }}
                </p>
              @endif
            </div>
          </div>
        </a>
      @empty
        <div class="col-span-full text-center py-12">
          <svg class="h-16 w-16 text-brand-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
          <p class="text-brand-600 font-medium">No assets found</p>
          <p class="text-sm text-brand-500 mt-1">Create an asset first before adding expenses</p>
          <a href="{{ route('assets.create') }}" class="inline-block mt-4 px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl transition-all">
            Add Asset
          </a>
        </div>
      @endforelse
    </div>
  </div>
</div>

<script>
  // Search functionality
  const searchInput = document.getElementById('assetSearch');
  const assetCards = document.querySelectorAll('.asset-card');

  searchInput.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    
    assetCards.forEach(card => {
      const name = card.dataset.name;
      const tag = card.dataset.tag;
      const type = card.dataset.type;
      
      const matches = name.includes(searchTerm) || 
                     tag.includes(searchTerm) || 
                     type.includes(searchTerm);
      
      card.style.display = matches ? 'block' : 'none';
    });
  });
</script>
@endsection
