<footer class="bg-ink text-gray-400 pt-16 pb-7">
  <div class="max-w-6xl mx-auto px-5">
    <div class="grid md:grid-cols-4 gap-10">
      <div>
        @include('partials.brand-logo', ['class' => 'flex items-center gap-2.5 font-black text-2xl text-white mb-4'])
        <p>La plateforme de réservation de taxi avec suivi en temps réel. Voyagez l'esprit tranquille.</p>
      </div>
      <div>
        <h4 class="text-white font-bold mb-4">Service</h4>
        <a href="{{ route('reservation') }}" class="block py-1.5 hover:text-taxi transition">Réserver</a>
        <a href="{{ route('suivi') }}" class="block py-1.5 hover:text-taxi transition">Suivi en direct</a>
        <a href="{{ route('tarifs') }}" class="block py-1.5 hover:text-taxi transition">Tarifs</a>
        @auth
          @if (auth()->user()->isClient())
            <a href="{{ route('rides.index') }}" class="block py-1.5 hover:text-taxi transition">Mes paiements</a>
          @endif
        @else
          <a href="{{ route('login') }}" class="block py-1.5 hover:text-taxi transition">Mes paiements</a>
        @endauth
      </div>
      <div>
        <h4 class="text-white font-bold mb-4">Compte</h4>
        @guest
          <a href="{{ route('login') }}" class="block py-1.5 hover:text-taxi transition">Connexion</a>
          <a href="{{ route('register') }}" class="block py-1.5 hover:text-taxi transition">Inscription</a>
        @endguest
        @auth
          <a href="{{ route(auth()->user()->dashboardRouteName()) }}" class="block py-1.5 hover:text-taxi transition">Mon espace</a>
        @else
          <a href="{{ route('login') }}" class="block py-1.5 hover:text-taxi transition">Mon espace</a>
        @endauth
        @auth
          @if (auth()->user()->isDriver())
            <a href="{{ route('user.dashboardDriver') }}" class="block py-1.5 hover:text-taxi transition">Espace chauffeur</a>
          @else
            <a href="{{ route('register', ['role' => 'driver']) }}" class="block py-1.5 hover:text-taxi transition">Devenir chauffeur</a>
          @endif
        @else
          <a href="{{ route('register', ['role' => 'driver']) }}" class="block py-1.5 hover:text-taxi transition">Devenir chauffeur</a>
        @endauth
      </div>
      <div>
        <h4 class="text-white font-bold mb-4">Entreprise</h4>
        <a href="{{ route('about') }}" class="block py-1.5 hover:text-taxi transition">À propos</a>
        <a href="{{ route('contact') }}" class="block py-1.5 hover:text-taxi transition">Contact</a>
        @auth
          @if (auth()->user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="block py-1.5 hover:text-taxi transition">Administration</a>
          @endif
        @endauth
      </div>
    </div>
    <div class="border-t border-gray-800 mt-11 pt-6 flex flex-wrap justify-between gap-3 text-sm">
      <span>© {{ date('Y') }} {{ config('app.name', 'KinTaxiBooking') }}. Tous droits réservés.</span>
      <div class="flex flex-wrap gap-4">
        <a href="{{ route('legal.cgu') }}" class="hover:text-taxi transition">CGU</a>
        <a href="{{ route('legal.privacy') }}" class="hover:text-taxi transition">Confidentialité</a>
        <a href="{{ route('contact') }}" class="hover:text-taxi transition">Support</a>
      </div>
    </div>
  </div>
</footer>
