{{-- Shared Section Heading --}}
{{-- Expects: $title (string), optional $hint (string|null), optional $right (string|Renderable) --}}
<div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between mb-3">
    <div>
        <h2 class="text-xl font-semibold">{!! $title !!}</h2>
        @isset($hint)
            <p class="text-gray-500 text-sm">{!! $hint !!}</p>
        @endisset
    </div>
    @isset($right)
        <div>{!! $right !!}</div>
    @endisset
</div>
