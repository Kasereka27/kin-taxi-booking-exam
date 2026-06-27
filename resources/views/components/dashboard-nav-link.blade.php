@props([
    'href',
    'icon',
    'label',
    'active' => false,
])

<a href="{{ $href }}" @class([
    'flex items-center gap-3 px-3.5 py-3 rounded-lg font-semibold whitespace-nowrap transition',
    'bg-taxi text-ink' => $active,
    'hover:bg-gray-800 hover:text-white' => ! $active,
])>
    <x-icon :name="$icon" class="w-5 h-5 shrink-0" />
    <span class="hidden lg:inline">{{ $label }}</span>
</a>
