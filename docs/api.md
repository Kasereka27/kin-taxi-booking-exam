# API REST — KinTaxiBooking

Base URL : `{APP_URL}/api`

Authentification : **Bearer token** (Laravel Sanctum).

## Format de réponse

Succès :

```json
{
  "success": true,
  "message": "OK",
  "data": { }
}
```

Erreur :

```json
{
  "success": false,
  "message": "Description de l'erreur",
  "errors": null
}
```

En-tête requis pour les routes protégées :

```
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json
```

---

## Authentification

### `POST /api/login`

Obtenir un token d'accès.

**Corps (JSON)**

| Champ | Type | Requis |
|-------|------|--------|
| `email` | string | oui |
| `password` | string | oui |
| `device_name` | string | non (défaut : `mobile-app`) |

**Réponse 200**

```json
{
  "success": true,
  "message": "Connexion réussie.",
  "data": {
    "token": "1|…",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "firstname": "Client",
      "lastname": "Test",
      "email": "client@exemple.com",
      "phone": "0990000003",
      "role": "client"
    }
  }
}
```

**Erreurs** : `401` identifiants invalides, `403` compte désactivé ou 2FA activé.

---

### `POST /api/logout`

Révoque le token courant. **Auth requise.**

---

### `GET /api/user`

Profil de l'utilisateur connecté. **Auth requise.**

---

## Courses (`rides`)

Toutes les routes ci-dessous requièrent un token Sanctum.

### `GET /api/rides`

Liste paginée des courses (filtrée selon le rôle).

**Query**

| Paramètre | Description |
|-----------|-------------|
| `status` | Filtre par statut (`pending`, `completed`, …) |
| `search` | Recherche dans les adresses |
| `per_page` | Taille de page (défaut : 10) |

---

### `POST /api/rides`

Crée une course (**client** ou **admin**).

**Corps**

```json
{
  "pickup_addr": "Gare Centrale, Kinshasa",
  "dropoff_addr": "Aéroport de N'djili",
  "vehicle_type": "eco"
}
```

Valeurs `vehicle_type` : `eco`, `confort`, `van`.

**Réponse** : `201 Created`

---

### `GET /api/rides/{id}`

Détail d'une course (client, chauffeur assigné ou admin).

---

### `PATCH /api/rides/{id}/cancel`

Annule une course (propriétaire client).

---

### `DELETE /api/rides/{id}`

Supprime une course en statut `pending` (propriétaire client).

---

## Webhook paiement

### `POST /api/labyrinthe/callback`

Callback serveur-à-serveur Labyrinthe (sans authentification Sanctum).

---

## Exemple cURL

```bash
# Connexion
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"client@exemple.com","password":"password"}'

# Liste des courses
curl http://localhost:8000/api/rides \
  -H "Accept: application/json" \
  -H "Authorization: Bearer VOTRE_TOKEN"
```
