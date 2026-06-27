@props([
    'type' => 'eco',
    'class' => 'w-12 h-12',
])

<div {{ $attributes->merge(['class' => 'inline-flex items-center justify-center text-taxi-dark shrink-0']) }}>
    @switch($type)
        @case('confort')
            {{-- Berline premium — profil allongé --}}
            <svg class="{{ $class }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.5h1.1c.3 0 .6-.1.8-.4l1.2-1.6c.4-.5 1-.8 1.6-.8h9.6c.6 0 1.2.3 1.6.8l1.2 1.6c.2.3.5.4.8.4H21M5 13.5V11c0-.6.4-1 1-1h1.5l1.8-2.4c.3-.4.8-.6 1.3-.6h4.8c.5 0 1 .2 1.3.6L18 10h1c.6 0 1 .4 1 1v2.5M7 17.25a1.125 1.125 0 1 0 0-2.25 1.125 1.125 0 0 0 0 2.25Zm10 0a1.125 1.125 0 1 0 0-2.25 1.125 1.125 0 0 0 0 2.25Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 10.5h6" />
            </svg>
            @break

        @case('van')
            {{-- Minivan — carrosserie haute, 3 vitres --}}
            <svg class="{{ $class }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 14h1.2c.3 0 .5-.1.7-.3l.8-1c.3-.4.8-.7 1.3-.7h1.5V8.5c0-.8.7-1.5 1.5-1.5h5c.8 0 1.5.7 1.5 1.5V11.9h1.5c.5 0 1 .3 1.3.7l.8 1c.2.2.4.3.7.3H21M5.5 14v2.5M18.5 14v2.5" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.5v3M10.5 8.5v3M13.5 8.5v3M16.5 8.5v3" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 17.25a1.125 1.125 0 1 0 0-2.25 1.125 1.125 0 0 0 0 2.25Zm10 0a1.125 1.125 0 1 0 0-2.25 1.125 1.125 0 0 0 0 2.25Z" />
            </svg>
            @break

        @default
            {{-- Éco — citadine compacte --}}
            <svg class="{{ $class }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 13.5h1c.3 0 .5-.1.7-.3l1.4-1.8c.3-.4.8-.6 1.3-.6h7.2c.5 0 1 .2 1.3.6l1.4 1.8c.2.2.4.3.7.3h1M6 13.5V11.5c0-.6.4-1 1-1h1.2l1.5-2c.3-.4.7-.5 1.1-.5h3.4c.4 0 .8.1 1.1.5l1.5 2H17c.6 0 1 .4 1 1v2M8 17a1 1 0 1 0 0-2 1 1 0 0 0 0 2Zm8 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
            </svg>
    @endswitch
</div>
