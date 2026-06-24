@extends('mainPages.app')

@section('title', 'Politique de confidentialité')

@section('content')
<section class="py-12 bg-ink text-white text-center">
  <div class="max-w-6xl mx-auto px-5">
    <h1 class="text-3xl font-extrabold">Politique de confidentialité</h1>
    <p class="mt-2 text-gray-300">Protection de vos données personnelles</p>
  </div>
</section>

<section class="py-16">
  <div class="max-w-3xl mx-auto px-5">
    <div class="bg-white rounded-2xl p-8 shadow-soft border border-gray-200 space-y-6 text-gray-700 leading-relaxed">
      <div>
        <h2 class="text-xl font-bold text-ink mb-2">Données collectées</h2>
        <p>Nous collectons les informations nécessaires au service : identité, e-mail, téléphone, historique de courses, données de paiement et, pour les chauffeurs, informations véhicule et position GPS pendant les courses actives.</p>
      </div>
      <div>
        <h2 class="text-xl font-bold text-ink mb-2">Utilisation</h2>
        <p>Vos données servent à traiter les réservations, le suivi en temps réel, les notifications, la facturation et le support client. Elles ne sont pas vendues à des tiers.</p>
      </div>
      <div>
        <h2 class="text-xl font-bold text-ink mb-2">Conservation</h2>
        <p>Les données de course et de paiement sont conservées conformément aux obligations légales et comptables. Vous pouvez demander la suppression de votre compte via le <a href="{{ route('contact') }}" class="text-taxi-dark font-semibold">support</a>.</p>
      </div>
      <div>
        <h2 class="text-xl font-bold text-ink mb-2">Sécurité</h2>
        <p>Les mots de passe sont chiffrés. Les communications sensibles passent par des connexions sécurisées. L'accès aux données est limité au personnel autorisé.</p>
      </div>
      <div>
        <h2 class="text-xl font-bold text-ink mb-2">Vos droits</h2>
        <p>Vous pouvez accéder, corriger ou supprimer vos données en nous contactant. Consultez aussi nos <a href="{{ route('legal.cgu') }}" class="text-taxi-dark font-semibold">conditions générales</a>.</p>
      </div>
    </div>
  </div>
</section>
@endsection
