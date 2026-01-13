# Arquanzia Backend

Backend Laravel pour la section communautaire Arquanzia - Feed type "Patreon strict".

## Prérequis

- PHP 8.2+
- Composer
- PostgreSQL (prod) ou SQLite (dev)

## Installation (Dev)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

## Configuration Production (cPanel)

1. Configurer le sous-domaine `arquanzia.creations-sortilege.com`
2. Pointer vers le dossier `public/`
3. Configurer PostgreSQL et mettre à jour `.env`:
```
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_DATABASE=arquanzia
DB_USERNAME=...
DB_PASSWORD=...
```
4. Configurer le mail pour les magic links admin

## Endpoints

- `GET /healthz` - Health check
- `GET /` - Feed public
- `GET /api/feed?viewer=public|connected|vip|reader` - API feed
- `GET /media/{id}` - Service médias (locked par défaut)
- `GET /admin/login` - Admin login (magic links)

## Audiences

| Audience | Public | Connecté | VIP | Reader |
|----------|--------|----------|-----|--------|
| public   | ✅     | ✅       | ✅  | ✅     |
| connected| 🔒     | ✅       | ✅  | ✅     |
| vip      | 🔒     | 🔒       | ✅  | 🔒     |
| reader   | 🔒     | 🔒       | 🔒  | ✅     |

## Admin

- **Root admin**: `info@creations-sortilege.com` (hardcodé, non supprimable)
- Authentification par magic links (15 min TTL)
- Rate limiting sur login

## Structure

```
app/
├── Models/          # User, Post, PostMedia, Comment, Reaction, etc.
├── Services/        # MediaService (blur/pixelate)
├── Http/
│   ├── Controllers/
│   │   ├── Api/     # FeedController
│   │   └── Admin/   # Auth, Posts, Modération
│   └── Middleware/  # AdminAuth
resources/views/
├── components/layouts/
├── feed/            # index, show
└── admin/           # login, dashboard, posts, moderation
```

## Epic 2 - Auth Hybride

### Flux Shopify App Proxy
1. Client visite `/apps/arquanzia` sur Shopify
2. Proxy redirige vers `/proxy/arquanzia` (HMAC vérifié)
3. Si `logged_in_customer_id` présent → bridge token → auto-login
4. Sinon → page avec CTA magic link

### Variables d'environnement Shopify
```
SHOPIFY_API_SECRET=xxx
SHOPIFY_WEBHOOK_SECRET=xxx
SHOPIFY_VIP_VARIANT_IDS=12345,67890
SHOPIFY_READER_VARIANT_IDS=11111,22222
```

### Webhook orders/paid
- `POST /webhooks/shopify/orders-paid`
- Signature HMAC vérifiée
- Applique VIP/Reader selon variant IDs (mois calendaire)
- Idempotent via `source_ref`

### Règles bannis
- Peuvent se connecter
- Feed filtré (uniquement posts `public`)
- Aucune interaction (comments/reactions → 403)

## Backups (à configurer)

- Postgres: `pg_dump` quotidien via cron
- Médias: backup `storage/app/media/`
