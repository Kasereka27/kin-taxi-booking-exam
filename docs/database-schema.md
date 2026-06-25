# Schéma de base de données — KinTaxiBooking

> Modèle relationnel normalisé. Toutes les tables sont créées via **migrations Laravel** (`database/migrations/`).

## Diagramme entité-relation

```mermaid
erDiagram
    users ||--o| driver_profiles : "1 chauffeur"
    users ||--o{ rides : "client_id"
    users ||--o{ rides : "driver_id"
    users ||--o{ payments : "payeur"
    users ||--o{ ratings : "from_user"
    users ||--o{ ratings : "to_user"
    users ||--o{ otp_codes : "2FA"
    users ||--o{ activity_logs : "audit"
    users ||--o{ notifications : "notifiable"

    rides ||--o{ payments : "1-N"
    rides ||--o{ ratings : "1-N"

    users {
        bigint id PK
        string firstname
        string lastname
        string email UK
        string phone UK
        string google_id UK
        timestamp email_verified_at
        string password
        enum role "admin|driver|client"
        boolean is_active
        boolean two_factor_enabled
        timestamps created_at
        softDeletes deleted_at
    }

    driver_profiles {
        bigint id PK
        bigint user_id FK UK
        string vehicle_model
        string plate UK
        enum vehicle_type
        decimal rating
        boolean is_online
        decimal current_lat
        decimal current_lng
        enum approval_status
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
        enum vehicle_type
        enum status
        decimal price
        decimal distance_km
        timestamps requested_at
        timestamps accepted_at
        timestamps completed_at
        timestamps cancelled_at
    }

    payments {
        bigint id PK
        bigint ride_id FK
        bigint user_id FK
        string order_number UK
        enum method
        string provider_reference
        decimal amount
        decimal fee
        enum status
        string failure_reason
        string receipt_path
        timestamp paid_at
    }

    ratings {
        bigint id PK
        bigint ride_id FK
        bigint from_user_id FK
        bigint to_user_id FK
        tinyint stars
        text comment
    }

    otp_codes {
        bigint id PK
        bigint user_id FK
        string code
        timestamp expires_at
        timestamp used_at
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
        morphs notifiable
        text data
        timestamp read_at
    }
```

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
