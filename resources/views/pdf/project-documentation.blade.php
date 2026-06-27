<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $appName }} — Documentation projet</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 11px; line-height: 1.45; margin: 0; padding: 28px 32px; }
        .cover { background: #111827; color: #fff; padding: 28px 32px; margin: -28px -32px 24px; }
        .brand { font-size: 26px; font-weight: bold; color: #ffce00; }
        .cover-meta { color: #d1d5db; margin-top: 8px; font-size: 11px; }
        .cover-title { font-size: 20px; font-weight: bold; margin-top: 18px; color: #fff; }
        h2 { font-size: 14px; color: #111827; border-bottom: 2px solid #ffce00; padding-bottom: 4px; margin: 22px 0 10px; page-break-after: avoid; }
        h3 { font-size: 12px; color: #374151; margin: 14px 0 6px; page-break-after: avoid; }
        p { margin: 0 0 8px; }
        ul, ol { margin: 0 0 10px 18px; padding: 0; }
        li { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 8px 0 14px; font-size: 10px; }
        th, td { border: 1px solid #e5e7eb; padding: 7px 8px; text-align: left; vertical-align: top; }
        th { background: #f9fafb; font-weight: 700; color: #374151; }
        .muted { color: #6b7280; }
        .badge { display: inline-block; background: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 999px; font-size: 9px; font-weight: bold; }
        .page-break { page-break-before: always; }
        .footer { margin-top: 24px; padding-top: 12px; border-top: 1px solid #e5e7eb; font-size: 9px; color: #6b7280; }
        code { background: #f3f4f6; padding: 1px 4px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="cover">
        <div class="brand">{{ $appName }}</div>
        <div class="cover-meta">{{ $institution }}</div>
        <div class="cover-meta">{{ $promotion }} · Cours Laravel — Examen final</div>
        <div class="cover-title">Documentation technique du projet</div>
        <div class="cover-meta" style="margin-top: 12px;">Étudiant : {{ $studentName }}</div>
        <div class="cover-meta">Généré le {{ $generatedAt }}</div>
    </div>

    <h2>1. Description du projet</h2>
    <p>
        <strong>{{ $appName }}</strong> est une application web de réservation de taxi avec suivi en temps réel,
        destinée au contexte de Kinshasa (RDC). Un <strong>client</strong> réserve une course, paie via
        <strong>Mobile Money</strong> (API Labyrinthe RDC) et suit son chauffeur sur une carte interactive.
        Un <strong>chauffeur</strong> accepte les demandes et met à jour sa position. Un
        <strong>administrateur</strong> supervise la plateforme via un tableau de bord statistique.
    </p>

    <h2>2. Technologies utilisées</h2>
    <table>
        <tr><th>Composant</th><th>Version / outil</th></tr>
        <tr><td>PHP</td><td>8.4</td></tr>
        <tr><td>Laravel</td><td>13.x</td></tr>
        <tr><td>Base de données</td><td>SQLite (dev) / MySQL ou PostgreSQL (prod)</td></tr>
        <tr><td>Front-end</td><td>Blade, Tailwind CSS 4, Vite 8</td></tr>
        <tr><td>Temps réel</td><td>Laravel Reverb + Laravel Echo</td></tr>
        <tr><td>Cartographie</td><td>Google Maps (Mapbox en alternative)</td></tr>
        <tr><td>Paiement</td><td>API Labyrinthe RDC (M-Pesa, Airtel, Orange)</td></tr>
        <tr><td>PDF</td><td>DomPDF (barryvdh/laravel-dompdf)</td></tr>
        <tr><td>OAuth</td><td>Laravel Socialite (Google, optionnel)</td></tr>
        <tr><td>API</td><td>Laravel Sanctum (tokens Bearer)</td></tr>
        <tr><td>Tests</td><td>Pest 4</td></tr>
    </table>

    <h2>3. Installation pas à pas</h2>
    <ol>
        <li>Cloner le dépôt : <code>git clone {{ $githubUrl }}.git</code></li>
        <li>Installer les dépendances : <code>composer install</code> puis <code>npm install</code></li>
        <li>Configurer l'environnement : <code>cp .env.example .env</code> et <code>php artisan key:generate</code></li>
        <li>Créer la base SQLite : <code>touch database/database.sqlite</code> (si nécessaire)</li>
        <li>Migrer et peupler : <code>php artisan migrate --seed</code></li>
        <li>Lier le stockage : <code>php artisan storage:link</code></li>
        <li>Compiler le front-end : <code>npm run build</code></li>
        <li>Lancer l'application : <code>composer run dev</code> (serveur, queue, Reverb, Vite)</li>
    </ol>
    <p class="muted">Application disponible sur http://localhost:8000 par défaut.</p>

    <h2>4. Comptes de test</h2>
    <p>Créés automatiquement par le seeder (<code>DatabaseSeeder</code>) :</p>
    <table>
        <tr><th>Rôle</th><th>E-mail</th><th>Mot de passe</th></tr>
        <tr><td>Administrateur</td><td>admin@exemple.com</td><td>password</td></tr>
        <tr><td>Chauffeur</td><td>chauffeur@exemple.com</td><td>password</td></tr>
        <tr><td>Client</td><td>client@exemple.com</td><td>password</td></tr>
    </table>

    <h2>5. Fonctionnalités implémentées</h2>

    <h3>Niveau 1 — Fondamentaux <span class="badge">Obligatoire</span></h3>
    <ul>
        <li>Architecture MVC stricte, conventions Laravel, code testé (Pest)</li>
        <li>Migrations, relations Eloquent, seeders réalistes</li>
        <li>CRUD courses (<code>rides</code>) : création, liste paginée, détail, annulation, suppression</li>
        <li>Recherche et filtre par statut sur l'historique</li>
        <li>Resource routes, routes nommées, layouts Blade réutilisables</li>
        <li>Validation via Form Requests, protection CSRF</li>
    </ul>

    <h3>Niveau 2 — Intermédiaires <span class="badge">Obligatoire</span></h3>
    <ul>
        <li>Authentification multi-rôles : admin, driver, client — redirection selon le rôle</li>
        <li>Middleware personnalisés : CheckRole, CheckAccountActive, RedirectIfAuthenticated</li>
        <li>Dashboard administrateur : statistiques, graphique Chart.js, courses live, gestion utilisateurs</li>
        <li>Mailing (Mailables + queue) : bienvenue, vérification e-mail, réservation, statut course, paiement</li>
        <li>Double authentification (2FA) : OTP par e-mail, expiration configurable, activation depuis le profil</li>
    </ul>

    <h3>Niveau 3 — Avancées <span class="badge">≥ 3 requis</span></h3>
    <ul>
        <li>API REST Sanctum — voir section 6</li>
        <li>Notifications Laravel (cloche in-app + e-mails métier)</li>
        <li>Paiement mobile Labyrinthe (initiation, callback, historique, statuts)</li>
        <li>Génération PDF (reçus de paiement + ce document de documentation)</li>
        <li>Journal d'activité admin (traçabilité connexions, courses, paiements, modération)</li>
        <li>Suivi temps réel WebSocket (Reverb) + carte Google Directions</li>
    </ul>

    <div class="page-break"></div>

    <h2>6. API REST (Sanctum)</h2>
    <table>
        <tr><th>Méthode</th><th>Endpoint</th><th>Description</th></tr>
        <tr><td>POST</td><td>/api/login</td><td>Obtenir un token Bearer</td></tr>
        <tr><td>POST</td><td>/api/logout</td><td>Révoquer le token (auth)</td></tr>
        <tr><td>GET</td><td>/api/user</td><td>Profil connecté (auth)</td></tr>
        <tr><td>GET</td><td>/api/rides</td><td>Liste paginée (auth)</td></tr>
        <tr><td>POST</td><td>/api/rides</td><td>Créer une course (auth, client)</td></tr>
        <tr><td>GET</td><td>/api/rides/{id}</td><td>Détail (auth)</td></tr>
        <tr><td>PATCH</td><td>/api/rides/{id}/cancel</td><td>Annuler (auth)</td></tr>
        <tr><td>DELETE</td><td>/api/rides/{id}</td><td>Supprimer (auth)</td></tr>
        <tr><td>POST</td><td>/api/labyrinthe/callback</td><td>Webhook paiement Labyrinthe</td></tr>
    </table>
    <p class="muted">Documentation détaillée : docs/api.md</p>

    <h2>7. Schéma de base de données</h2>
    <p>Toutes les tables sont créées via migrations Laravel. Documentation complète : <strong>docs/database-schema.md</strong></p>
    <table>
        <tr><th>Table</th><th>Rôle principal</th></tr>
        <tr><td>users</td><td>Comptes (admin, chauffeur, client), 2FA, statut actif</td></tr>
        <tr><td>driver_profiles</td><td>Véhicule, plaque, note, position GPS, statut en ligne</td></tr>
        <tr><td>rides</td><td>Courses : adresses, statut, prix, chauffeur assigné</td></tr>
        <tr><td>payments</td><td>Paiements Mobile Money Labyrinthe, reçu PDF</td></tr>
        <tr><td>ratings</td><td>Notes entre utilisateurs après course</td></tr>
        <tr><td>otp_codes</td><td>Codes 2FA temporaires</td></tr>
        <tr><td>activity_logs</td><td>Journal d'audit (connexions, actions sensibles)</td></tr>
        <tr><td>notifications</td><td>Notifications in-app Laravel</td></tr>
    </table>

    <h2>8. Middleware personnalisés</h2>
    <table>
        <tr><th>Alias</th><th>Classe</th><th>Rôle</th></tr>
        <tr><td>role</td><td>CheckRole</td><td>Autorise uniquement les rôles passés en paramètre de route</td></tr>
        <tr><td>active</td><td>CheckAccountActive</td><td>Bloque les comptes désactivés (is_active = false)</td></tr>
        <tr><td>guest</td><td>RedirectIfAuthenticated</td><td>Empêche l'accès login/register si déjà connecté</td></tr>
    </table>

    <h2>9. Difficultés rencontrées et solutions</h2>
    <ol>
        <li><strong>Paiement Labyrinthe en local (Windows)</strong> — Erreurs SSL avec le bundle CA local. Solution : LABYRINTHE_VERIFY_SSL=false en développement uniquement.</li>
        <li><strong>WebSocket Reverb</strong> — Port 8080 occupé sur Windows. Solution : port 8090 (REVERB_PORT / REVERB_SERVER_PORT).</li>
        <li><strong>Carte de suivi</strong> — Passage à Google Directions API pour un itinéraire réaliste.</li>
        <li><strong>E-mails et PDF en queue</strong> — Mailables ShouldQueue ; file d'attente requise (composer run dev ou queue:listen).</li>
    </ol>

    <h2>10. Déploiement</h2>
    <p>
        Application non déployée en ligne au moment de la remise. Pour la production : configurer MySQL/PostgreSQL,
        SMTP, Reverb, exécuter <code>php artisan migrate --force</code>, <code>storage:link</code>,
        <code>npm run build</code>, et exposer le callback <code>POST /api/labyrinthe/callback</code>.
    </p>

    <h2>11. Structure du dépôt</h2>
    <table>
        <tr><th>Dossier / fichier</th><th>Contenu</th></tr>
        <tr><td>app/Http/Controllers/</td><td>Contrôleurs web et API</td></tr>
        <tr><td>app/Http/Middleware/</td><td>Middleware personnalisés</td></tr>
        <tr><td>app/Http/Requests/</td><td>Validation des formulaires</td></tr>
        <tr><td>app/Mail/ · app/Notifications/</td><td>E-mails et notifications</td></tr>
        <tr><td>app/Services/</td><td>Logique métier (paiement, PDF, logs…)</td></tr>
        <tr><td>database/migrations/ · seeders/</td><td>Schéma et données de test</td></tr>
        <tr><td>docs/</td><td>Documentation (PDF, schéma BDD, API)</td></tr>
        <tr><td>resources/views/</td><td>Vues Blade et templates PDF</td></tr>
        <tr><td>routes/web.php · api.php</td><td>Routes web et API</td></tr>
        <tr><td>tests/Feature/</td><td>Tests Pest</td></tr>
    </table>

    <div class="footer">
        {{ $appName }} — Projet académique UPC/FASI · {{ $promotion }} · Dépôt : {{ $githubUrl }}
    </div>
</body>
</html>
