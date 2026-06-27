# Schéma de base de données — KinTaxiBooking

> Modèle relationnel normalisé. Toutes les tables sont créées via **migrations Laravel** (`database/migrations/`).

## Diagramme entité-relation

```mermaid
erDiagram
    users ||--o| driver_profiles : "profil chauffeur"
    users ||--o{ rides : "client_id"
    users ||--o{ rides : "driver_id"
    users ||--o{ payments : "payeur"
    users ||--o{ ratings : "from_user_id"
    users ||--o{ ratings : "to_user_id"
    users ||--o{ otp_codes : "codes OTP"
    users ||--o{ activity_logs : "journal audit"
    users ||--o{ notifications : "notifiable"

    rides ||--o{ payments : "paiements"
    rides ||--o{ ratings : "avis"

    users {
        bigint id PK
        string firstname
        string lastname
        string email
        string phone
        string google_id
        datetime email_verified_at
        string password
        string role
        boolean is_active
        boolean two_factor_enabled
        datetime created_at
        datetime deleted_at
    }

    driver_profiles {
        bigint id PK
        bigint user_id FK
        string vehicle_model
        string plate
        string vehicle_type
        decimal rating
        boolean is_online
        decimal current_lat
        decimal current_lng
        string approval_status
    }

    rides {
        bigint id PK
        bigint client_id FK
        bigint driver_id FK
        string pickup_addr
        decimal pickup_lat
        decimal pickup_lng
        string dropoff_addr
        decimal dropoff_lat
        decimal dropoff_lng
        string vehicle_type
        string status
        decimal price
        decimal distance_km
        datetime requested_at
        datetime accepted_at
        datetime completed_at
        datetime cancelled_at
    }

    payments {
        bigint id PK
        bigint ride_id FK
        bigint user_id FK
        string order_number
        string method
        string provider_reference
        decimal amount
        decimal fee
        string status
        string failure_reason
        string receipt_path
        datetime paid_at
    }

    ratings {
        bigint id PK
        bigint ride_id FK
        bigint from_user_id FK
        bigint to_user_id FK
        int stars
        text review
    }

    otp_codes {
        bigint id PK
        bigint user_id FK
        string code
        datetime expires_at
        datetime used_at
    }

    activity_logs {
        bigint id PK
        bigint user_id FK
        string action
        text description
        string ip_address
        text user_agent
    }

    notifications {
        uuid id PK
        string type
        bigint notifiable_id
        string notifiable_type
        text data
        datetime read_at
    }
```

> **Note Mermaid :** les libellés de relation sont entre guillemets car Mermaid réserve certains mots (`to`, `from`, etc.). Une seule contrainte (`PK`, `FK` ou `UK`) est autorisée par attribut. L’attribut `review` dans le diagramme correspond à la colonne `comment` en base. Les index uniques et les `enum` sont détaillés dans les tableaux ci-dessous.

## Tables principales

### `users`

Comptes de la plateforme (admin, chauffeur, client).

| Colonne | Type | Description |
|---------|------|-------------|
| `role` | enum | `admin`, `driver`, `client` |
| `is_active` | boolean | Désactivation par l'admin |
| `two_factor_enabled` | boolean | 2FA OTP par e-mail |
| `google_id` | string | Lien OAuth Google (nullable) |
| `email_verified_at` | timestamp | Vérification d'e-mail |

### `driver_profiles`

Profil métier du chauffeur (relation 1–1 avec `users` où `role = driver`).

| Colonne | Type | Description |
|---------|------|-------------|
| `is_online` | boolean | Disponible pour de nouvelles courses |
| `current_lat/lng` | decimal | Position GPS pour le suivi |
| `approval_status` | enum | `pending`, `approved`, `rejected` |

### `rides`

Course de taxi — entité centrale du CRUD.

| Statut | Signification |
|--------|---------------|
| `pending` | En attente d'un chauffeur |
| `assigned` | Chauffeur assigné |
| `approche` | Chauffeur en route vers le client |
| `course` | Trajet en cours |
| `completed` | Terminée |
| `cancelled` | Annulée |

Référence publique affichée : `KTB-{id}` (ex. `KTB-42`).

### `payments`

Transactions Mobile Money via Labyrinthe.

| Colonne | Type | Description |
|---------|------|-------------|
| `order_number` | string | Référence interne unique |
| `method` | enum | `mpesa`, `airtel`, `orange`, … |
| `fee` | decimal | Commission Labyrinthe |
| `receipt_path` | string | Chemin du PDF généré |
| `status` | enum | `pending`, `success`, `failed` |

### `ratings`

Avis laissés après une course (étoiles + commentaire).

### `otp_codes`

Codes à usage unique pour la double authentification (hashés, expiration + `used_at`).

### `activity_logs`

Journal d'audit des actions importantes (connexion, courses, paiements, modération).

### `notifications`

Notifications Laravel (cloche) — colonnes polymorphes `notifiable`.

## Tables système Laravel

| Table | Usage |
|-------|--------|
| `sessions` | Sessions web (driver `database`) |
| `password_reset_tokens` | Réinitialisation mot de passe |
| `cache`, `jobs` | Cache et files d'attente |
| `notifications` | Notifications BDD |

## Relations Eloquent (résumé)

```
User
 ├── hasOne  DriverProfile
 ├── hasMany Ride (client_id)
 ├── hasMany Ride (driver_id)
 ├── hasMany Payment
 ├── hasMany OtpCode
 └── hasMany ActivityLog

Ride
 ├── belongsTo User (client, driver)
 ├── hasMany Payment
 └── hasMany Rating
```

## Seeders

`DatabaseSeeder` crée :

- 1 admin, 1 chauffeur test (en ligne), 1 client test
- 7 chauffeurs + 14 clients supplémentaires
- ~30 courses avec paiements et notes
- Courses terminées non payées (client test, 100 FC) pour tester le paiement
- Courses `pending` et `cancelled` pour les filtres

```bash
php artisan migrate:fresh --seed
```
