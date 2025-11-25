<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div class="flex-1 min-w-0" x-data="{
        editing: false,
        title: @js($estimate->title),
        originalTitle: @js($estimate->title),
        saving: false,
        async saveTitle() {
            if (this.title.trim() === '' || this.title === this.originalTitle) {
                this.title = this.originalTitle;
                this.editing = false;
                return;
            }
            this.saving = true;
            try {
                const response = await fetch('{{ route('estimates.update', $estimate) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        title: this.title
                    })
                });
                if (!response.ok) throw new Error('Failed to save');
                this.originalTitle = this.title;
                this.editing = false;
                if (window.showToast) {
                    window.showToast('Title updated', 'success');
                }
            } catch (error) {
                console.error(error);
                if (window.showToast) {
                    window.showToast('Failed to update title', 'error');
                }
                this.title = this.originalTitle;
            } finally {
                this.saving = false;
            }
        }
    }">
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs text-gray-500">Estimate #{{ $estimate->id }}</span>
            <span class="text-gray-300">·</span>
            <span class="text-xs text-gray-600">{{ $estimate->client->name }}</span>
            @if($estimate->property)
                <span class="text-gray-300">·</span>
                <span class="text-xs text-gray-600">{{ $estimate->property->name }}</span>
            @endif
        </div>
        
        <div class="flex items-center gap-2 group">
            <template x-if="!editing">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <h1 class="text-2xl font-semibold text-gray-900 truncate" x-text="title"></h1>
                    <button type="button" 
                            @click="editing = true; $nextTick(() => $refs.titleInput.select())"
                            class="opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0 p-1 hover:bg-gray-100 rounded"
                            title="Edit title">
                        <svg class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                        </svg>
                    </button>
                </div>
            </template>
            <template x-if="editing">
                <div class="flex items-center gap-2 w-full">
                    <input type="text" 
                           x-ref="titleInput"
                           x-model="title"
                           @keydown.enter="saveTitle()"
                           @keydown.escape="title = originalTitle; editing = false"
                           @blur="saveTitle()"
                           :disabled="saving"
                           class="form-input flex-1 text-xl font-semibold py-1 px-2 border-gray-300 focus:ring-brand-500 focus:border-brand-500 rounded"
                           placeholder="Enter estimate title">
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button type="button" 
                                @click="saveTitle()"
                                :disabled="saving"
                                class="p-1 hover:bg-green-50 rounded transition disabled:opacity-50"
                                title="Save">
                            <svg class="h-4 w-4 text-green-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </button>
                        <button type="button" 
                                @click="title = originalTitle; editing = false"
                                :disabled="saving"
                                class="p-1 hover:bg-red-50 rounded transition disabled:opacity-50"
                                title="Cancel">
                            <svg class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 6L6 18M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>
    
    <div class="flex items-center gap-2">
        <button type="button" id="saveAllBtn" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md text-sm font-medium bg-brand-600 text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-1 shadow-sm">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
            </svg>
            Save All
        </button>
        
        @if($previewEmailRoute ?? false)
            <x-secondary-button as="a" :href="$previewEmailRoute" size="sm">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                Email
            </x-secondary-button>
        @endif
        
        @if($printRoute ?? false)
            <x-secondary-button as="a" :href="$printRoute" target="_blank" size="sm">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Print
            </x-secondary-button>
        @endif
        
        <x-secondary-button as="a" href="{{ route('estimates.edit', $estimate) }}" size="sm">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            Edit
        </x-secondary-button>
    </div>
</div>
