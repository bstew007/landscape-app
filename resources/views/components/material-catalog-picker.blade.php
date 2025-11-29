{{-- Material Catalog Picker Modal --}}
<div x-data="materialCatalogPicker()" 
     x-init="init()"
     x-cloak>
    
    {{-- Trigger Button --}}
    <button type="button"
            @click="openModal()"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        Browse Material Catalog
    </button>
    
    {{-- Modal --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeModal()"></div>
        
        {{-- Modal Content --}}
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[80vh] overflow-hidden"
                 @click.away="closeModal()">
                
                {{-- Header --}}
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-900">Select Material from Catalog</h3>
                        <button @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    {{-- Search & Filters --}}
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <input type="text"
                                   x-model="searchQuery"
                                   @input.debounce.300ms="filterMaterials()"
                                   placeholder="Search materials by name, SKU, or category..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <select x-model="selectedCategory"
                                    @change="filterMaterials()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="">All Categories</option>
                                <template x-for="category in categories" :key="category">
                                    <option :value="category" x-text="category"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>
                
                {{-- Material List --}}
                <div class="overflow-y-auto px-6 py-4" style="max-height: 50vh;">
                    <template x-if="filteredMaterials.length === 0 && allMaterials.length === 0">
                        <div class="text-center py-12">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="mt-4 text-lg font-semibold text-gray-700">No materials in catalog</p>
                            <p class="mt-2 text-gray-500">Add materials to your catalog to select them here.</p>
                            <p class="mt-1 text-sm text-gray-400">Go to Materials → Add New Material</p>
                        </div>
                    </template>
                    
                    <template x-if="filteredMaterials.length === 0 && allMaterials.length > 0">
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <p class="mt-4 text-gray-500">No materials match your search</p>
                            <button @click="searchQuery = ''; selectedCategory = ''; filterMaterials()" 
                                    class="mt-3 text-blue-600 hover:text-blue-800 underline">
                                Clear filters
                            </button>
                        </div>
                    </template>
                    
                    <div class="space-y-2">
                        <template x-for="material in filteredMaterials" :key="material.id">
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:bg-blue-50 cursor-pointer transition"
                                 @click="selectMaterial(material)">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900" x-text="material.name"></h4>
                                        <div class="mt-1 flex items-center gap-4 text-sm text-gray-600">
                                            <span x-show="material.sku" class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                                </svg>
                                                <span x-text="material.sku"></span>
                                            </span>
                                            <span x-show="material.category" class="px-2 py-1 bg-gray-100 rounded text-xs" x-text="material.category"></span>
                                            <span x-show="material.vendor_name" class="text-xs" x-text="'Vendor: ' + material.vendor_name"></span>
                                        </div>
                                        <p x-show="material.description" class="mt-2 text-sm text-gray-500" x-text="material.description"></p>
                                    </div>
                                    <div class="ml-4 text-right">
                                        <p class="text-lg font-bold text-green-600">
                                            $<span x-text="parseFloat(material.unit_cost).toFixed(2)"></span>
                                        </p>
                                        <p class="text-sm text-gray-500">per <span x-text="material.unit || 'ea'"></span></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <p class="text-sm text-gray-600">
                            Showing <span x-text="filteredMaterials.length"></span> of <span x-text="allMaterials.length"></span> materials
                        </p>
                        <button @click="closeModal()" 
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function materialCatalogPicker() {
    return {
        isOpen: false,
        allMaterials: [],
        filteredMaterials: [],
        categories: [],
        searchQuery: '',
        selectedCategory: '',
        onSelectCallback: null,
        
        init() {
            this.loadMaterials();
        },
        
        async loadMaterials() {
            try {
                const response = await fetch('/api/materials/active');
                const data = await response.json();
                
                console.log('Materials loaded:', data); // Debug
                
                if (data.success) {
                    this.allMaterials = data.materials || [];
                    // Get unique categories, excluding empty ones
                    this.categories = [...new Set(
                        this.allMaterials
                            .map(m => m.category)
                            .filter(c => c && c.trim() !== '')
                    )].sort();
                    
                    // Add "No Category" option if there are materials without categories
                    const hasUncategorized = this.allMaterials.some(m => !m.category || m.category.trim() === '');
                    if (hasUncategorized) {
                        this.categories.unshift('(No Category)');
                    }
                    
                    this.filteredMaterials = this.allMaterials;
                    
                    console.log(`✅ Loaded ${this.allMaterials.length} materials`); // Debug
                    console.log(`📁 Categories:`, this.categories); // Debug
                } else {
                    console.error('API returned error:', data);
                }
            } catch (error) {
                console.error('Error loading materials:', error);
                alert('Error loading materials. Please check console for details.');
            }
        },
        
        filterMaterials() {
            let results = this.allMaterials;
            
            // Filter by search query
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                results = results.filter(m => 
                    (m.name && m.name.toLowerCase().includes(query)) ||
                    (m.sku && m.sku.toLowerCase().includes(query)) ||
                    (m.category && m.category.toLowerCase().includes(query)) ||
                    (m.description && m.description.toLowerCase().includes(query))
                );
            }
            
            // Filter by category
            if (this.selectedCategory) {
                if (this.selectedCategory === '(No Category)') {
                    results = results.filter(m => !m.category || m.category.trim() === '');
                } else {
                    results = results.filter(m => m.category === this.selectedCategory);
                }
            }
            
            this.filteredMaterials = results;
        },
        
        openModal(callback = null) {
            this.isOpen = true;
            this.onSelectCallback = callback;
            document.body.style.overflow = 'hidden';
        },
        
        closeModal() {
            this.isOpen = false;
            document.body.style.overflow = '';
            this.searchQuery = '';
            this.selectedCategory = '';
            this.filterMaterials();
        },
        
        selectMaterial(material) {
            console.log('🎯 Material selected:', material); // Debug
            
            // Emit custom event with selected material
            const event = new CustomEvent('material-selected', { 
                detail: material,
                bubbles: true
            });
            window.dispatchEvent(event);
            
            console.log('✅ Event dispatched'); // Debug
            
            // Call callback if provided
            if (this.onSelectCallback) {
                this.onSelectCallback(material);
            }
            
            this.closeModal();
        }
    }
}
</script>
