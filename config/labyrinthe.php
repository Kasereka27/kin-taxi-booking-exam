<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Passerelle de paiement Labyrinthe RDC (Mobile Money)
    |--------------------------------------------------------------------------
    |
    | Configuration de l'intégration de l'API Labyrinthe RDC pour les paiements
    | Mobile Money (M-Pesa, Airtel Money, Orange Money) en CDF.
    |
    */

    'token' => env('LABYRINTHE_TOKEN'),

    'base_url' => env('LABYRINTHE_BASE_URL', 'https://api.labyrinthe-rdc.com/api/V1'),

    'gateway' => env('LABYRINTHE_GATEWAY', 'https://payment.labyrinthe-rdc.com/'),

    'callback_url' => env('LABYRINTHE_CALLBACK_URL'),

    'currency' => env('LABYRINTHE_CURRENCY', 'CDF'),

    'country' => env('LABYRINTHE_COUNTRY', 'CD'),

    'deposit_endpoint' => env('LABYRINTHE_DEPOSIT_ENDPOINT', '/payment/mobile'),

    'min_amount' => (int) env('LABYRINTHE_MIN_AMOUNT', 500),

    /*
     | Commission (en %) prélevée par Labyrinthe, répercutée sur le client.
     | Mode « markup » : total payé = prix course + (prix course × commission).
     | Le client voit ces frais avant de payer pour éviter toute surprise.
     */
    'commission_percent' => (float) env('LABYRINTHE_COMMISSION_PERCENT', 0),

    'timeout' => (int) env('LABYRINTHE_TIMEOUT', 30),

    /*
     | Délai (en secondes) au-delà duquel un paiement resté « pending » est
     | considéré comme refusé/expiré (ex. code PIN non saisi sur le téléphone).
     | Évite que le suivi automatique tourne indéfiniment.
     */
    'payment_timeout' => (int) env('LABYRINTHE_PAYMENT_TIMEOUT', 120),

    /*
     | Vérification du certificat SSL. À laisser à true en production.
     | En dev local Windows sans bundle CA, mettre LABYRINTHE_VERIFY_SSL=false
     | pour débloquer les tests (NE PAS faire en production).
     */
    'verify_ssl' => filter_var(env('LABYRINTHE_VERIFY_SSL', true), FILTER_VALIDATE_BOOL),
];
