# 🚕 TaxiGo — Application de réservation de taxi avec suivi en temps réel

Front-end **HTML + Tailwind CSS + JavaScript** complet, prêt à être branché sur une API back-end.
Design jaune & noir moderne, responsive, avec carte de suivi en temps réel (Leaflet + OpenStreetMap, gratuit).
Le style est entièrement réalisé avec **Tailwind CSS v4** (via le CDN navigateur `@tailwindcss/browser`) — aucun fichier CSS personnalisé.

## 📄 Pages disponibles

| Page | Fichier | Description |
|------|---------|-------------|
| Accueil | `index.html` | Landing : hero, avantages, flotte, étapes |
| Réservation | `reservation.html` | Formulaire de course + estimateur de prix |
| Suivi temps réel | `suivi.html` | Carte live + progression de la course |
| Connexion | `connexion.html` | Authentification |
| Inscription | `inscription.html` | Création de compte (passager/chauffeur) |
| Espace client | `dashboard-client.html` | Stats, course en cours, historique |
| Espace chauffeur | `dashboard-chauffeur.html` | Demandes, revenus, courses |
| Administration | `dashboard-admin.html` | Pilotage plateforme, courses live |
| Historique | `historique.html` | Toutes les courses + reçus |
| Paiement | `paiement.html` | Cartes enregistrées + factures |
| Profil | `profil.html` | Infos perso, sécurité, préférences |
| Tarifs | `tarifs.html` | Grille tarifaire |
| À propos | `a-propos.html` | Présentation entreprise |
| Contact | `contact.html` | Formulaire + FAQ |
| API | `api-docs.html` | Documentation des endpoints back-end |

## 📁 Structure

```
sabin/
├── index.html
├── reservation.html  suivi.html  connexion.html  inscription.html
├── dashboard-client.html  dashboard-chauffeur.html  dashboard-admin.html
├── historique.html  paiement.html  profil.html
├── tarifs.html  a-propos.html  contact.html  api-docs.html
├── assets/
│   └── js/
│       ├── tw.js           # Thème Tailwind v4 partagé (@theme CSS-first, injecté)
│       ├── app.js          # Navigation, formulaires, données démo, config API
│       └── map.js          # Suivi temps réel (carte Leaflet)
└── README.md
```

## ▶️ Lancer le projet

Aucune dépendance à installer. Ouvrez `index.html` dans un navigateur, ou servez le dossier :

```bash
# Python
python -m http.server 8000
# Node
npx serve .
```

Puis ouvrez http://localhost:8000

## 🔌 Brancher le back-end

Le front est conçu pour être facilement connecté à une API REST + WebSocket.

1. **Configuration** : adaptez l'objet `API` dans `assets/js/app.js`.
2. **Formulaires** : la fonction `handleForm()` dans `app.js` simule les soumissions — remplacez les `setTimeout` par des appels `fetch(API.base + ...)`.
3. **Suivi temps réel** : dans `assets/js/map.js`, remplacez la simulation par un WebSocket :
   ```js
   const ws = new WebSocket("wss://api.taxigo.fr/api/v1/rides/ID/track");
   ws.onmessage = (e) => { const d = JSON.parse(e.data); driver.setLatLng([d.lat, d.lng]); };
   ```
4. **Endpoints** : voir `api-docs.html` pour le contrat complet.

## 🗄️ Schéma de base de données suggéré

```sql
users(id, firstname, lastname, email, phone, password_hash, role, created_at)
drivers(id, user_id, vehicle_model, plate, vehicle_type, rating, is_online, lat, lng)
rides(id, client_id, driver_id, pickup_addr, pickup_lat, pickup_lng,
      dropoff_addr, dropoff_lat, dropoff_lng, vehicle_type, status,
      price, distance_km, created_at, completed_at)
payments(id, ride_id, user_id, method, amount, status, created_at)
payment_methods(id, user_id, type, last4, token, is_default)
ratings(id, ride_id, from_user, to_user, stars, comment)
```

Statuts de course : `pending` → `assigned` → `approche` → `course` → `completed` (ou `cancelled`).

## 🧰 Stack back-end recommandée

- **API** : Node.js (Express / NestJS) ou Python (FastAPI)
- **Base de données** : PostgreSQL + **PostGIS** (requêtes géospatiales « chauffeurs à proximité »)
- **Temps réel** : Socket.IO / WebSocket + Redis (positions GPS)
- **Paiement** : Stripe
- **Auth** : JWT

## 🎨 Stack front-end

HTML5 · **Tailwind CSS v4** (CDN navigateur) · JavaScript vanilla · Leaflet (carte OpenStreetMap, gratuit).

> Le thème (couleurs `taxi`, `ink`, ombres) est défini en **CSS-first** via la directive `@theme`
> dans `assets/js/tw.js`, qui injecte un bloc `<style type="text/tailwindcss">` partagé par toutes les pages.
> Le CDN navigateur (`https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4`) est réservé au développement.
> Pour la production, remplacez-le par un build CLI/Vite : créez un `input.css` contenant `@import "tailwindcss";`
> suivi du bloc `@theme`, puis compilez avec `npx @tailwindcss/cli -i input.css -o dist/app.css --minify`.
> Tailwind v4 cible les navigateurs récents (Safari 16.4+, Chrome 111+, Firefox 128+).

---
© 2026 TaxiGo.

