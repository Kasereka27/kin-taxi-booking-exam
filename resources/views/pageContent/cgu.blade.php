@extends('mainPages.app')

@section('title', 'Conditions générales')

@section('content')
<section class="py-12 bg-ink text-white text-center">
  <div class="max-w-6xl mx-auto px-5">
    <h1 class="text-3xl font-extrabold">Conditions générales d'utilisation</h1>
    <p class="mt-2 text-gray-300">Dernière mise à jour : {{ now()->translatedFormat('F Y') }}</p>
  </div>
</section>

<section class="py-16">
  <div class="max-w-3xl mx-auto px-5 prose prose-gray max-w-none">
    <div class="bg-white rounded-2xl p-8 shadow-soft border border-gray-200 space-y-6 text-gray-700 leading-relaxed">
      <div>
        <h2 class="text-xl font-bold text-ink mb-2">1. Objet</h2>
        <p>Les présentes conditions régissent l'utilisation de la plateforme {{ config('app.name', 'KinTaxiBooking') }}, service de réservation et de suivi de courses de taxi à Kinshasa et en République Démocratique du Congo.</p>
      </div>
      <div>
        <h2 class="text-xl font-bold text-ink mb-2">2. Compte utilisateur</h2>
        <p>L'inscription implique la fourniture d'informations exactes. Vous êtes responsable de la confidentialité de vos identifiants. Les comptes chauffeurs sont soumis à validation par l'équipe KinTaxiBooking.</p>
      </div>
      <div>
        <h2 class="text-xl font-bold text-ink mb-2">3. Réservation et annulation</h2>
        <p>Une course confirmée engage le client jusqu'à son exécution ou son annulation conforme aux règles affichées sur la plateforme. L'annulation gratuite est possible tant que la course est en attente d'un chauffeur.</p>
      </div>
      <div>
        <h2 class="text-xl font-bold text-ink mb-2">4. Paiement</h2>
        <p>Les paiements s'effectuent en francs congolais via Mobile Money (Labyrinthe RDC). Les montants affichés incluent les frais applicables au moment de la réservation.</p>
      </div>
      <div>
        <h2 class="text-xl font-bold text-ink mb-2">5. Responsabilité</h2>
        <p>{{ config('app.name', 'KinTaxiBooking') }} met en relation clients et chauffeurs indépendants. La plateforme n'est pas responsable des incidents survenus pendant le trajet, sous réserve de la réglementation en vigueur.</p>
      </div>
      <div>
        <h2 class="text-xl font-bold text-ink mb-2">6. Contact</h2>
        <p>Pour toute question : <a href="{{ route('contact') }}" class="text-taxi-dark font-semibold">formulaire de contact</a> ou support@kintaxibooking.com.</p>
      </div>
    </div>
  </div>
</section>
@endsection
