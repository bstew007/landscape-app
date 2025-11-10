@php
    $sectionId = $overrideSectionId ?? ('overrideFields_' . uniqid());
    $toggleId = $overrideToggleId ?? ('toggleOverride_' . uniqid());
    $toggleName = $overrideToggleName ?? 'materials_override_enabled';
    $toggleLabel = $overrideToggleLabel ?? 'Override material pricing';
    $overrideChecked = (bool) ($overrideChecked ?? false);
    $fields = $fields ?? [];
    $customContent = $customContent ?? null;
    $fullFields = array_filter($fields, fn ($field) => ($field['width'] ?? 'full') !== 'half');
    $halfFields = array_filter($fields, fn ($field) => ($field['width'] ?? 'full') === 'half');
@endphp

<div class="mt-4">
    <label class="inline-flex items-center">
        <input type="checkbox"
               id="{{ $toggleId }}"
               name="{{ $toggleName }}"
               value="1"
               class="form-checkbox h-5 w-5 text-blue-600"
               {{ $overrideChecked ? 'checked' : '' }}>
        <span class="ml-2 text-sm font-medium">{{ $toggleLabel }}</span>
    </label>
</div>

@if (!empty($customContent))
    <div class="mt-4">
        {!! $customContent !!}
    </div>
@endif

<div id="{{ $sectionId }}" class="mt-4 space-y-4 {{ $overrideChecked ? '' : 'hidden' }}">
    @foreach ($fullFields as $field)
        <div>
            <label class="block text-sm font-semibold">{{ $field['label'] }}</label>
            <input
                type="{{ $field['type'] ?? 'text' }}"
                name="{{ $field['name'] }}"
                class="form-input w-full"
                value="{{ old($field['name'], $field['value'] ?? '') }}"
                @if(isset($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
                @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
            >
            @if(!empty($field['help']))
                <p class="text-sm text-gray-500 mt-1">{{ $field['help'] }}</p>
            @endif
        </div>
    @endforeach

    @if (!empty($halfFields))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($halfFields as $field)
                <div>
                    <label class="block text-sm font-semibold">{{ $field['label'] }}</label>
                    <input
                        type="{{ $field['type'] ?? 'number' }}"
                        name="{{ $field['name'] }}"
                        class="form-input w-full"
                        value="{{ old($field['name'], $field['value'] ?? '') }}"
                        @if(isset($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
                        @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                        @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
                    >
                    @if(!empty($field['help']))
                        <p class="text-sm text-gray-500 mt-1">{{ $field['help'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('{{ $toggleId }}');
        const section = document.getElementById('{{ $sectionId }}');
        if (!toggle || !section) return;

        const updateVisibility = () => {
            section.classList.toggle('hidden', !toggle.checked);
        };

        toggle.addEventListener('change', updateVisibility);
        updateVisibility();
    });
</script>
@endpush
