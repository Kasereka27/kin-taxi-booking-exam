# KinTaxiBooking

**Application web de réservation de taxi avec suivi en temps réel — Kinshasa, RDC**

| | |
|---|---|
| **Étudiant** | KASEREKA SALAMBUNGU SABIN |
| **Institution** | Faculté des Sciences Informatiques (FASI) — UPC |
| **Promotion** | L3 · Année académique 2025–2026 |
| **Cours** | Laravel — Examen final |
| **Dépôt GitHub** | [github.com/Kasereka27/kin-taxi-booking-exam](https://github.com/Kasereka27/kin-taxi-booking-exam) |

---

## Description

KinTaxiBooking permet à un **client** de réserver une course, de la payer via **Mobile Money** (Labyrinthe RDC), et de **suivre son chauffeur en direct** sur une carte Google Maps. Les **chauffeurs** acceptent les courses et mettent à jour leur position. Les **administrateurs** pilotent la plateforme via un tableau de bord statistique.

---

## Technologies

| Composant | Version / outil |
|-----------|-----------------|
| PHP | 8.4 |
| Laravel | 13.x |
| Base de données | SQLite (dev) / MySQL ou PostgreSQL (prod) |
| Front-end | Blade, Tailwind CSS 4, Vite 8 |
| Temps réel | Laravel Reverb + Echo |
| Cartographie | Google Maps (+ Mapbox en alternative) |
| Paiement | API Labyrinthe RDC (M-Pesa, Airtel, Orange) |
| PDF | DomPDF (`barryvdh/laravel-dompdf`) |
| OAuth | Laravel Socialite (Google) |
| Tests | Pest 4 (93 tests) |

**Dépendances PHP notables :** `laravel/reverb`, `laravel/socialite`, `barryvdh/laravel-dompdf`.

---

## Installation

### Prérequis

- PHP ≥ 8.3, Composer, Node.js ≥ 20, npm
- Extension PHP : `pdo_sqlite` (ou driver MySQL/PostgreSQL)

### Étapes

```bash
# 1. Cloner le dépôt
git clone https://github.com/Kasereka27/kin-taxi-booking-exam.git
cd kin-taxi-booking-exam

# 2. Dépendances
composer install
npm install

# 3. Environnement
cp .env.example .env
php artisan key:generate

# 4. Base de données (SQLite par défaut)
touch database/database.sqlite   # si le fichier n'existe pas
php artisan migrate --seed

# 5. Stockage public (reçus PDF, avatars…)
php artisan storage:link

# 6. Build front-end
npm run build

# 7. Lancer l'environnement de développement
composer run dev
```

`composer run dev` démarre en parallèle : serveur HTTP, file d'attente, Reverb (WebSocket) et Vite.

Application disponible sur **http://localhost:8000** (ou l'URL indiquée par `php artisan serve`).

### Variables d'environnement importantes

| Variable | Rôle |
|----------|------|
| `APP_NAME` | Nom affiché (ex. `KinTaxiBooking`) |
| `VITE_GOOGLE_MAPS_API_KEY` | Carte + Directions API (Google Cloud) |
| `VITE_MAP_PROVIDER` | `google` (défaut) ou `mapbox` |
| `REVERB_*` | WebSocket suivi temps réel (port **8090** par défaut) |
| `LABYRINTHE_TOKEN` | Paiement Mobile Money |
| `GOOGLE_CLIENT_*` | Connexion OAuth Google (optionnel) |
| `MAIL_*` | Envoi des e-mails (Mailtrap en dev) |
| `QUEUE_CONNECTION` | `database` — requis pour e-mails/PDF en queue |

Voir `.env.example` pour la liste complète.

---

## Comptes de test

Créés automatiquement par `php artisan migrate --seed` :

| Rôle | E-mail | Mot de passe |
|------|--------|--------------|
| Administrateur | admin@exemple.com | `password` |
| Chauffeur | chauffeur@exemple.com | `password` |
| Client | client@exemple.com | `password` |

Le seeder génère aussi des courses, paiements et avis de démonstration.

---

## Fonctionnalités implémentées

### Niveau 1 — Fondamentaux

- Architecture **MVC** stricte, code organisé et testé
- **Migrations**, relations Eloquent, **seeders** réalistes
- **CRUD courses** (`rides`) : création, liste paginée, détail, annulation, suppression
- Recherche et filtre par statut sur l'historique
- **Resource routes**, routes nommées, layouts Blade réutilisables
- Validation via **Form Requests**, protection **CSRF**

### Niveau 2 — Intermédiaires

- **Authentification multi-rôles** : `admin`, `driver`, `client` — redirection selon le rôle
- **3 middleware personnalisés** :
  - `CheckRole` — restriction par rôle (`role:admin`, etc.)
  - `CheckAccountActive` — blocage des comptes désactivés
  - `RedirectIfAuthenticated` — redirection des utilisateurs déjà connectés
- **Dashboard administrateur** : statistiques, graphique Chart.js, courses live, gestion utilisateurs
- **Mailing** (Mailables + queue) :
  - Bienvenue à l'inscription
  - Vérification d'e-mail
  - Confirmation de réservation
  - Notifications de statut (course acceptée / annulée)
  - Confirmation de paiement (+ reçu PDF joint)
- **Double authentification (2FA)** : code OTP par e-mail, expiration configurable, activation depuis le profil

### Niveau 3 — Avancées

- **Paiement mobile Labyrinthe** : initiation, callback, suivi, historique
- **Notifications Laravel** : cloche in-app + e-mails métier
- **Génération PDF** : reçu de paiement téléchargeable
- **Suivi temps réel** : WebSocket (Reverb), carte Google Directions
- **OAuth Google** (optionnel)

---

## Schéma de base de données

Documentation détaillée : **[docs/database-schema.md](docs/database-schema.md)**

---

## Tests

```bash
php artisan test --compact
```

**93 tests** Pest (features : auth, courses, paiement, notifications, 2FA, e-mails, PDF, suivi carte…).

---

## Middleware — rôle de chacun

| Middleware | Fichier | Rôle |
|------------|---------|------|
| `role` | `CheckRole.php` | Autorise uniquement les rôles passés en paramètre de route |
| `active` | `CheckAccountActive.php` | Déconnecte un utilisateur dont `is_active = false` |
| `guest` | `RedirectIfAuthenticated.php` | Empêche un utilisateur connecté d'accéder à login/register |

---

## Difficultés rencontrées et solutions

1. **Paiement Labyrinthe en local (Windows)** — Erreurs SSL avec le bundle CA local. Solution : `LABYRINTHE_VERIFY_SSL=false` en développement uniquement (jamais en production).

2. **WebSocket Reverb** — Le port 8080 était déjà occupé sur Windows. Solution : utilisation du port **8090** (`REVERB_PORT` / `REVERB_SERVER_PORT`).

3. **Carte de suivi** — Passage de tracés simulés à **Google Directions API** pour un itinéraire réaliste ; activation de l'API Directions dans Google Cloud Console.

4. **E-mails et PDF en queue** — Les Mailables implémentent `ShouldQueue` ; la file d'attente doit tourner (`composer run dev` ou `php artisan queue:listen`).

---

## Déploiement

Application non déployée en ligne au moment de la remise. Pour une mise en production :

- Configurer MySQL/PostgreSQL, Redis (optionnel), SMTP, Reverb
- `php artisan migrate --force`, `storage:link`, `npm run build`
- Exposer le callback Labyrinthe : `POST /api/labyrinthe/callback`

---

## Structure du dépôt

```
kin-taxi-booking-exam/
├── app/
│   ├── Http/Controllers/
│   ├── Http/Middleware/
│   ├── Http/Requests/
│   ├── Mail/
│   ├── Models/
│   ├── Notifications/
│   ├── Policies/
│   └── Services/
├── database/migrations/
├── database/seeders/
├── docs/
│   └── database-schema.md
├── resources/views/
├── routes/web.php
├── routes/api.php
├── tests/Feature/
└── .env.example
```

---

## Licence

Projet académique — UPC/FASI · 2025–2026.
