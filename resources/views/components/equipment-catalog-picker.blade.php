{{-- Equipment Catalog Picker Modal --}}
<div x-data="equipmentCatalogPicker()" 
     x-init="init()"
     x-cloak>
    
    {{-- Trigger Button --}}
    <button type="button"
            @click="openModal()"
            class="inline-flex items-center px-4 py-2 bg-brand-800 hover:bg-brand-700 text-white font-semibold rounded-lg shadow-sm transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        Browse Equipment Catalog
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
                        <h3 class="text-xl font-semibold text-gray-900">Select Equipment from Catalog</h3>
                        <button type="button" @click="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    {{-- Search & Filters --}}
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-2">
                            <input type="text"
                                   x-model="searchQuery"
                                   @input.debounce.300ms="filterEquipment()"
                                   placeholder="Search equipment by name, SKU, model..."
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <select x-model="selectedCategory"
                                    @change="filterEquipment()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="">All Categories</option>
                                <template x-for="category in categories" :key="category">
                                    <option :value="category" x-text="category"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <select x-model="selectedOwnership"
                                    @change="filterEquipment()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                <option value="">All Types</option>
                                <option value="company">🏢 Company</option>
                                <option value="rental">🔑 Rental</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                {{-- Equipment List --}}
                <div class="overflow-y-auto px-6 py-4" style="max-height: 50vh;">
                    <template x-if="filteredEquipment.length === 0 && allEquipment.length === 0">
                        <div class="text-center py-12">
                            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <p class="mt-4 text-lg font-semibold text-gray-700">No equipment in catalog</p>
                            <p class="mt-2 text-gray-500">Add equipment to your catalog to select them here.</p>
                            <p class="mt-1 text-sm text-gray-400">Go to Equipment → Add Equipment</p>
                        </div>
                    </template>
                    
                    <template x-if="filteredEquipment.length === 0 && allEquipment.length > 0">
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <p class="mt-4 text-gray-500">No equipment matches your search</p>
                            <button @click="searchQuery = ''; selectedCategory = ''; selectedOwnership = ''; filterEquipment()" 
                                    class="mt-3 text-blue-600 hover:text-blue-800 underline">
                                Clear filters
                            </button>
                        </div>
                    </template>
                    
                    <div class="space-y-2">
                        <template x-for="equipment in filteredEquipment" :key="equipment.id">
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-500 hover:bg-blue-50 cursor-pointer transition"
                                 @click="selectEquipment(equipment)">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-semibold text-gray-900" x-text="equipment.name"></h4>
                                            <span x-show="equipment.ownership_type === 'company'" 
                                                  class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded-full border border-green-200">
                                                🏢 Company
                                            </span>
                                            <span x-show="equipment.ownership_type === 'rental'" 
                                                  class="px-2 py-0.5 bg-blue-100 text-blue-800 text-xs rounded-full border border-blue-200">
                                                🔑 Rental
                                            </span>
                                        </div>
                                        <div class="mt-1 flex items-center gap-4 text-sm text-gray-600">
                                            <span x-show="equipment.model" class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                                </svg>
                                                <span x-text="equipment.model"></span>
                                            </span>
                                            <span x-show="equipment.category" class="px-2 py-1 bg-gray-100 rounded text-xs" x-text="equipment.category"></span>
                                            <span x-show="equipment.vendor_name" class="text-xs" x-text="'Vendor: ' + equipment.vendor_name"></span>
                                        </div>
                                        <p x-show="equipment.description" class="mt-2 text-sm text-gray-500" x-text="equipment.description"></p>
                                    </div>
                                    <div class="ml-4 text-right">
                                        <p class="text-lg font-bold text-green-600">
                                            $<span x-text="getRate(equipment)"></span>
                                        </p>
                                        <p class="text-sm text-gray-500">per <span x-text="equipment.unit || 'hr'"></span></p>
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
                            Showing <span x-text="filteredEquipment.length"></span> of <span x-text="allEquipment.length"></span> equipment items
                        </p>
                        <button type="button" @click="closeModal()" 
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
function equipmentCatalogPicker() {
    return {
        isOpen: false,
        allEquipment: [],
        filteredEquipment: [],
        categories: [],
        searchQuery: '',
        selectedCategory: '',
        selectedOwnership: '',
        onSelectCallback: null,
        
        init() {
            this.loadEquipment();
        },
        
        async loadEquipment() {
            try {
                const response = await fetch('/api/equipment/active');
                const data = await response.json();
                
                if (data.success) {
                    this.allEquipment = data.equipment || [];
                    this.categories = [...new Set(
                        this.allEquipment
                            .map(e => e.category)
                            .filter(c => c && c.trim() !== '')
                    )].sort();
                    
                    this.filteredEquipment = this.allEquipment;
                    console.log(`✅ Loaded ${this.allEquipment.length} equipment items`);
                } else {
                    console.error('API returned error:', data);
                }
            } catch (error) {
                console.error('Error loading equipment:', error);
                alert('Error loading equipment. Please check console for details.');
            }
        },
        
        filterEquipment() {
            let results = this.allEquipment;
            
            // Filter by search query
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                results = results.filter(e => 
                    (e.name && e.name.toLowerCase().includes(query)) ||
                    (e.sku && e.sku.toLowerCase().includes(query)) ||
                    (e.model && e.model.toLowerCase().includes(query)) ||
                    (e.category && e.category.toLowerCase().includes(query))
                );
            }
            
            // Filter by category
            if (this.selectedCategory) {
                results = results.filter(e => e.category === this.selectedCategory);
            }
            
            // Filter by ownership type
            if (this.selectedOwnership) {
                results = results.filter(e => e.ownership_type === this.selectedOwnership);
            }
            
            this.filteredEquipment = results;
        },
        
        getRate(equipment) {
            const rate = equipment.unit === 'day' ? equipment.daily_rate : equipment.hourly_rate;
            return rate ? parseFloat(rate).toFixed(2) : '0.00';
        },
        
        openModal(callback) {
            this.isOpen = true;
            this.onSelectCallback = callback;
            this.searchQuery = '';
            this.selectedCategory = '';
            this.selectedOwnership = '';
            this.filterEquipment();
        },
        
        closeModal() {
            this.isOpen = false;
            this.onSelectCallback = null;
        },
        
        selectEquipment(equipment) {
            console.log('Selected equipment:', equipment);
            
            if (this.onSelectCallback && typeof this.onSelectCallback === 'function') {
                this.onSelectCallback(equipment);
            }
            
            // Dispatch custom event for parent components to listen to
            window.dispatchEvent(new CustomEvent('equipment-selected', {
                detail: equipment
            }));
            
            this.closeModal();
        }
    }
}
</script>
