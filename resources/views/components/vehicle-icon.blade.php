@props([
    'type' => 'eco',
    'class' => 'w-12 h-12',
])

@php
    $icon = match ($type) {
        'confort' => 'sparkles',
        'van' => 'truck',
        default => 'car',
    };
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center justify-center text-taxi-dark']) }}>
    <x-icon :name="$icon" :class="$class" />
</div>
