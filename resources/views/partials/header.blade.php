
<!-- Navbar -->
<header class="sticky top-0 z-50 bg-white/90 backdrop-blur-sm border-b border-gray-200">
    <div class="max-w-6xl mx-auto px-5">
      <div class="flex items-center justify-between h-[72px]">
        <a href="index.html" class="flex items-center gap-2.5 font-black text-2xl">
          <span class="w-9 h-9 rounded-xl bg-taxi grid place-items-center shadow-taxi text-xl">🚕</span> Taxi<span class="text-taxi-dark">Go</span>
        </a>
        <nav class="hidden md:flex items-center gap-7">
          <a href="{{ route('home') }}" class="nav-link font-semibold text-gray-600 hover:text-ink transition">Accueil</a>
          <a href="{{ route('reservation') }}" class="nav-link font-semibold text-gray-600 hover:text-ink transition">Réserver</a>
          <a href="{{ route('suivi') }}" class="nav-link font-semibold text-gray-600 hover:text-ink transition">Suivi</a>
          <a href="{{ route('tarifs') }}" class="nav-link font-semibold text-gray-600 hover:text-ink transition">Tarifs</a>
          <a href="{{ route('about') }}" class="nav-link font-semibold text-gray-600 hover:text-ink transition">À propos</a>
          <a href="{{ route('contact') }}" class="nav-link font-semibold text-gray-600 hover:text-ink transition">Contact</a>
        </nav>
        <div class="flex items-center gap-3">
          <a href="{{ route('login') }}" class="hidden sm:inline-flex px-4 py-2 rounded-full font-bold text-sm hover:bg-gray-100 transition">Connexion</a>
          <a href="{{ route('reservation') }}" class="inline-flex px-4 py-2 rounded-full font-bold text-sm bg-taxi text-ink shadow-taxi hover:bg-taxi-dark transition">Réserver</a>
          <button id="menuToggle" type="button" class="md:hidden text-2xl">☰</button>
        </div>
      </div>
      <nav id="mobileMenu" class="hidden md:hidden flex-col gap-2 pb-4">
        <a href="{{ route('home') }}" class="block py-2 font-semibold text-gray-700">Accueil</a>
        <a href="{{ route('reservation') }}" class="block py-2 font-semibold text-gray-700">Réserver</a>
        <a href="{{ route('suivi') }}" class="block py-2 font-semibold text-gray-700">Suivi</a>
        <a href="{{ route('tarifs') }}" class="block py-2 font-semibold text-gray-700">Tarifs</a>
        <a href="{{ route('about') }}" class="block py-2 font-semibold text-gray-700">À propos</a>
        <a href="{{ route('contact') }}" class="block py-2 font-semibold text-gray-700">Contact</a>
      </nav>
    </div>
  </header>