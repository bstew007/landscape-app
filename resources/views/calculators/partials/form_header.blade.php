{{-- Shared Form Header --}}
{{-- Expects: $title (string), optional $subtitle (string|null) --}}
<div class="mb-6">
    <h1 class="text-3xl font-bold">{!! $title !!}</h1>
    @isset($subtitle)
        <p class="text-gray-600 mt-2">{!! $subtitle !!}</p>
    @endisset
</div>
