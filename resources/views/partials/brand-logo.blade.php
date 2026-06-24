@props([
    'href' => null,
    'class' => 'flex items-center gap-2.5 font-black text-2xl',
])

<a href="{{ $href ?? route('home') }}" {{ $attributes->merge(['class' => $class]) }}>
  <span class="w-9 h-9 rounded-xl bg-taxi grid place-items-center shadow-taxi text-xl shrink-0">🚕</span>
  <span>Kin<span class="text-taxi-dark">Taxi</span>Booking</span>
</a>
