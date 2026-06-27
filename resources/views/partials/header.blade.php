
<!-- Navbar -->
<header class="sticky top-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-200">
    <div class="max-w-6xl mx-auto px-5">
      <div class="flex items-center justify-between h-[72px]">
        @include('partials.brand-logo')
        <nav class="hidden md:flex items-center gap-7">
          <a href="{{ route('home') }}" class="nav-link font-semibold text-gray-600 hover:text-ink transition">Accueil</a>
          <a href="{{ route('reservation') }}" class="nav-link font-semibold text-gray-600 hover:text-ink transition">Réserver</a>
          <a href="{{ route('suivi') }}" class="nav-link font-semibold text-gray-600 hover:text-ink transition">Suivi</a>
          <a href="{{ route('tarifs') }}" class="nav-link font-semibold text-gray-600 hover:text-ink transition">Tarifs</a>
          <a href="{{ route('about') }}" class="nav-link font-semibold text-gray-600 hover:text-ink transition">À propos</a>
          <a href="{{ route('contact') }}" class="nav-link font-semibold text-gray-600 hover:text-ink transition">Contact</a>
        </nav>
        <div class="flex items-center gap-3">
          @auth
            @include('partials.notifications')
            <a href="{{ route(auth()->user()->dashboardRouteName()) }}" class="hidden sm:inline-flex px-4 py-2 rounded-full font-bold text-sm hover:bg-gray-100 transition">Mon espace</a>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="inline-flex px-4 py-2 rounded-full font-bold text-sm bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Déconnexion</button>
            </form>
          @else
            <a href="{{ route('login') }}" class="hidden sm:inline-flex px-4 py-2 rounded-full font-bold text-sm hover:bg-gray-100 transition">Connexion</a>
            <a href="{{ route('register') }}" class="inline-flex px-4 py-2 rounded-full font-bold text-sm bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">S'inscrire</a>
          @endauth
          <button id="menuToggle" type="button" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100 transition" aria-label="Menu"><x-icon name="bars-3" class="w-6 h-6" /></button>
        </div>
      </div>
      <nav id="mobileMenu" class="hidden md:hidden flex-col gap-2 pb-4">
        <a href="{{ route('home') }}" class="block py-2 font-semibold text-gray-700">Accueil</a>
        <a href="{{ route('reservation') }}" class="block py-2 font-semibold text-gray-700">Réserver</a>
        <a href="{{ route('suivi') }}" class="block py-2 font-semibold text-gray-700">Suivi</a>
        <a href="{{ route('tarifs') }}" class="block py-2 font-semibold text-gray-700">Tarifs</a>
        <a href="{{ route('about') }}" class="block py-2 font-semibold text-gray-700">À propos</a>
        <a href="{{ route('contact') }}" class="block py-2 font-semibold text-gray-700">Contact</a>
        <div class="border-t border-gray-200 mt-2 pt-2">
          @auth
            <a href="{{ route(auth()->user()->dashboardRouteName()) }}" class="block py-2 font-semibold text-gray-700">Mon espace</a>
            <form method="POST" action="{{ route('logout') }}" class="pt-1">
              @csrf
              <button type="submit" class="block w-full text-left py-2 font-semibold text-taxi-dark">Déconnexion</button>
            </form>
          @else
            <a href="{{ route('login') }}" class="block py-2 font-semibold text-gray-700">Connexion</a>
            <a href="{{ route('register') }}" class="block py-2 font-semibold text-taxi-dark">S'inscrire</a>
          @endauth
        </div>
      </nav>
    </div>
  </header>