# README — Examen Final Laravel
### Faculté des Sciences Informatiques (FASI) — Université Protestant du Congo (UPC)
### Promotion L3 · Année académique 2025–2026

---

## Objectif du projet

Concevoir et développer une application web complète avec **Laravel**, en respectant les bonnes pratiques du développement moderne. Le projet doit démontrer la maîtrise des concepts fondamentaux et avancés vus en cours, tout en répondant à un besoin réel et concret.

---

## Structure attendue du dépôt

```
nom-du-projet/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   └── Mail/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   └── lang/
├── routes/
│   ├── web.php
│   └── api.php
├── .env.example
├── README.md
└── ...
```

---

## Exigences techniques

Les exigences sont classées en **3 niveaux** : Fondamentaux, Intermédiaires, et Avancés.

---

### Niveau 1 — Fondamentaux *(obligatoires)*

#### 1. Architecture MVC & Structure du projet
- Respect strict de l'architecture MVC (Models, Views, Controllers)
- Code organisé, lisible et commenté
- Utilisation des conventions de nommage Laravel

#### 2. Base de données & Migrations
- Toutes les tables créées via des **migrations** (aucune table créée manuellement)
- Utilisation des **relations Eloquent** (hasMany, belongsTo, belongsToMany, etc.)
- **Seeders** pour peupler la base de données avec des données de test réalistes
- Respect de la normalisation des données (pas de redondance inutile)

#### 3. Opérations CRUD complètes
- Au moins **une entité principale** avec les 4 opérations (Create, Read, Update, Delete)
- Pagination des listes (`paginate()`)
- Recherche et filtrage des données

#### 4. Routage & Contrôleurs
- Utilisation des **Resource Controllers** (`Route::resource`)
- Séparation claire des routes web et API
- Routes nommées (`route('nom.action')`)

#### 5. Vues Blade
- Utilisation des **layouts Blade** (`@extends`, `@section`, `@yield`)
- Composants réutilisables (`@include`, `@component`)
- Affichage conditionnel selon les rôles (`@can`, `@auth`, `@role`)

#### 6. Validation des formulaires
- Validation via **Requests** dédiés
- Messages d'erreur personnalisés et affichés à l'utilisateur
- Protection **CSRF** sur tous les formulaires (`@csrf`)

---

### Niveau 2 — Intermédiaires *(obligatoires)*

#### 7. Authentification multi-rôles
- Au minimum **3 types d'utilisateurs** avec des rôles distincts, par exemple :
  - `Administrateur` — accès total
  - `Gestionnaire / Agent` — accès métier partiel
  - `Client / Utilisateur` — accès restreint à son propre espace
- Système de rôles implémenté
- Redirection automatique selon le rôle après connexion
- Protection des routes par rôle

#### 8. Middleware personnalisés
- Au moins **3 middleware** créés manuellement (ex: `CheckRole`, `CheckAccountActive`, `RateLimited`)
- Middleware appliqué aux routes ou groupes de routes concernés
- Explication claire du rôle de chaque middleware dans le README

#### 9. Dashboard Administrateur
- Tableau de bord dédié à l'administrateur avec :
  - **Statistiques globales** (nombre d'utilisateurs, transactions, etc.)
  - **Graphiques** (Chart.js ou autre)
  - **Activité récente** (dernières inscriptions, actions, alertes)
  - Gestion complète des utilisateurs (liste, activation/désactivation, suppression)
  - Gestion des contenus ou entités principales de l'application

#### 10. Mailing (Email)
- Configuration d'un service d'envoi d'email (Mailtrap en dev, SMTP en prod)
- Au moins **4 types d'emails** envoyés automatiquement :
  - Email de bienvenue à l'inscription
  - Email de confirmation d'une action importante (commande, paiement, etc.)
  - Email de confirmation de l'adresse mail après création de compte
  - Email de notification (alerte, changement de statut, etc.)
- Utilisation des **Mailables** et des **templates Blade** pour les emails
- Emails en file d'attente avec **Laravel Queues** (bonus apprécié)

#### 11. Double Authentification (2FA)
- Implémentation de la vérification en deux étapes :
  - Envoi d'un **code OTP par email** à la connexion
  - Saisie du code pour valider l'accès
  - Expiration du code après un délai défini (5 à 10 minutes)
- Option d'activation/désactivation du 2FA depuis le profil utilisateur

---

### Niveau 3 — Avancés *(au moins 3 parmi les suivants)*

#### 12. API REST
- Endpoints RESTful documentés et fonctionnels
- Authentification de l'API via **Laravel Sanctum** (tokens)
- Réponses JSON standardisées (code HTTP, message, data)
- Au moins 5 endpoints couvrant les opérations CRUD d'une entité

#### 13. Upload & Gestion de fichiers
- Upload sécurisé de fichiers (images, documents PDF)
- Validation du type et de la taille du fichier
- Stockage organisé dans `storage/app/public`
- Génération de miniatures pour les images (bonus)

#### 14. Notifications
- Utilisation du système de **Notifications Laravel** (`php artisan make:notification`)
- Notifications en base de données (cloche d'alertes dans l'interface)
- Notifications par email déclenchées par des événements métier

#### 15. Événements & Listeners (Facultatif)
- Au moins **1 Event/Listener** implémenté (ex: `UserRegistered`, `PaymentCompleted`)
- Découplage logique entre les actions et leurs conséquences

#### 16. Paiement mobile intégré
- Intégration **une API de paiement mobile** (M-Pesa, Airtel Money, Orange Money)
- Workflow complet : initiation → confirmation → mise à jour du statut
- Historique des transactions avec statuts (en attente, réussi, échoué)
- Reçu de paiement généré automatiquement (PDF ou email)
- Lien de la documentation de LabPay : [https://doc.api.labyrinthe-rdc.com/](https://doc.api.labyrinthe-rdc.com/)

#### 17. Génération de PDF
- Génération dynamique de documents PDF (factures, reçus, rapports, attestations)
- Utilisation d'un package dédié (DomPDF, Snappy, etc.)
- Téléchargement et/ou envoi par email du PDF

#### 18. Recherche avancée & Filtres
- Filtres combinés sur les listes (par date, statut, catégorie, etc.)
- Barre de recherche avec résultats en temps réel (AJAX ou Livewire)

#### 19. Logs & Traçabilité
- Journalisation des actions sensibles (connexions, modifications, suppressions)
- Interface d'administration pour consulter les logs
- Utilisation de `Log::info()`, `Log::warning()` etc. de manière cohérente

#### 20. Déploiement
- Application déployée sur un serveur en ligne (Heroku, Railway, VPS, etc.)
- Fichier `.env.example` fourni avec toutes les variables nécessaires
- Base de données de production configurée

---

## Base de données

- Fournir le **schéma de la base de données** dans le dossier `docs/`
- Toutes les tables doivent avoir des clés étrangères correctement définies
- Les **seeders** doivent permettre de tester l'application immédiatement après installation

---

## Documentation (README.md et fichier pdf)

Le fichier README doit obligatoirement contenir :

- [ ] Titre et description du projet
- [ ] Nom de l'étudiant(e)
- [ ] Technologies utilisées (Laravel version, PHP version, base de données, dépendances rajoutées)
- [ ] Instructions d'installation pas à pas
- [ ] Comptes de test (email + mot de passe pour chaque rôle)
- [ ] Liste des fonctionnalités implémentées
- [ ] Schéma de la base de données (image ou lien)
- [ ] Difficultés rencontrées et solutions trouvées
- [ ] Lien vers l'application déployée *(si applicable)*

---

## Installation du projet

```bash
# 1. Cloner le dépôt
git clone https://github.com/votre-username/nom-du-projet.git
cd nom-du-projet

# 2. Installer les dépendances
composer install
npm install && npm run build

# 3. Configurer l'environnement
cp .env.example .env
php artisan key:generate

# 4. Configurer la base de données dans le fichier .env
# DB_DATABASE=nom_de_la_base
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Exécuter les migrations et les seeders
php artisan migrate --seed

# 6. Lier le stockage public
php artisan storage:link

# 7. Lancer le serveur
php artisan serve
```

---

## Comptes de test

| Rôle | Email | Mot de passe |
|------|-------|--------------|
| Administrateur | admin@exemple.com | password |
| Gestionnaire | gestionnaire@exemple.com | password |
| Client | client@exemple.com | password |

> Ces comptes doivent être créés automatiquement par les **Seeders**.

---

## Grille d'évaluation

| Critère | Points |
|--------|--------|
| Architecture MVC & Structure du code | 10 |
| Base de données, Migrations & Relations Eloquent | 15 |
| Authentification multi-rôles & Middleware | 15 |
| Dashboard administrateur | 10 |
| CRUD complet & Validation | 10 |
| Mailing | 10 |
| Double authentification (2FA) | 10 |
| Fonctionnalités avancées (API, Paiement…) | 15 |
| Documentation & Qualité du code | 5 |
| **Total** | **100** |

---

## Règles importantes

- Le projet doit être **individuel** sauf indication contraire du professeur
- Tout plagiat ou copie de code entre étudiants entraînera la note de **0**
- Le code doit être versionné sur **GitHub** (historique de commits exigé)
- Un projet sans migrations (tables créées à la main) sera **pénalisé**
- L'application doit fonctionner sans erreur au moment de la soutenance

---

## Calendrier

| Étape | Date limite |
|-------|-------------|
| Choix du sujet validé | À définir |
| Remise du schéma de base de données | À définir |
| Remise du projet complet (GitHub) | À définir |
| Soutenance orale | À définir |

---

*Document préparé par le corps enseignant de la FASI/UPC — Cours Laravel L3 · 2025–2026*