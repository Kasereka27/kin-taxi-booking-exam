<div {{ $attributes->class(['flex flex-col lg:grid lg:grid-cols-[260px_minmax(0,1fr)] lg:grid-rows-1 h-full min-h-0 w-full max-w-full overflow-hidden']) }}>
    <div class="shrink-0 min-w-0 lg:h-full lg:min-h-0 lg:flex">
        {{ $sidebar }}
    </div>
    <div class="min-h-0 min-w-0 flex-1 overflow-y-auto overflow-x-hidden overscroll-y-contain p-4 sm:p-7 lg:px-9">
        {{ $slot }}
    </div>
</div>
