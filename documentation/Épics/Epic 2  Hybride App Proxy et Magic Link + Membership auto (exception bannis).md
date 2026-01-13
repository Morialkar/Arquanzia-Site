Objectif
	1.	Garder l’entrée Shopify (App Proxy) avec auto-login vers arquanzia.creations-sortilege.com
	2.	Offrir un fallback universel par magic link email-only
	3.	Automatiser VIP/Reader via achats Shopify (mois calendaire)
	4.	Bannissement = login autorisé mais lecture seule + feed filtré (pas de teasers VIP/Reader)

⸻

2.1 — Shopify App Proxy (entrée “boutique”)

Shopify
	•	Proxy path: /apps/arquanzia
	•	Target: https://arquanzia.creations-sortilege.com/proxy/arquanzia

Backend
	•	GET /proxy/arquanzia

Done
	•	Visiting /apps/arquanzia appelle ton backend

⸻

2.2 — Sécurité App Proxy : HMAC obligatoire

Middleware
	•	Vérifie la signature Shopify sur toutes les routes /proxy/*
	•	Sinon 401

Done
	•	Un lien non signé ou altéré est refusé

⸻

2.3 — Auto-login cross-domain via Bridge Token (clé de l’hybride)

Pourquoi

Shopify est sur creations-sortilege.com, ton portail sur arquanzia... : on ne peut pas déposer proprement une session cookie cross-domain. On fait donc un “bridge”.

Table bridge_tokens
	•	token_hash
	•	shopify_customer_id
	•	expires_at (~60s)
	•	used_at
	•	unique/one-time

Flux
	1.	Client ouvre /apps/arquanzia
	2.	Shopify proxy → /proxy/arquanzia
	3.	Backend :
	•	si logged_in_customer_id présent
	•	crée un bridge token one-time
	•	redirect vers https://arquanzia.creations-sortilege.com/bridge?token=...
	4.	/bridge consomme le token, crée session client (cookie httpOnly), redirect /

Routes
	•	GET /proxy/arquanzia
	•	GET /bridge?token=...

Fallback
	•	si logged_in_customer_id absent → afficher une page avec CTA magic link

Done
	•	Auto-login fonctionne sans clic supplémentaire quand Shopify fournit l’ID
	•	Sinon on retombe sur magic link

⸻

2.4 — Auth client Magic Link (email-only)

Table client_magic_links
	•	email
	•	token_hash
	•	expires_at (15 min)
	•	used_at

Routes
	•	GET /login
	•	POST /login (envoi lien)
	•	GET /magic/{token} (consomme + session)
	•	POST /logout

Sécurité
	•	rate-limit IP + email
	•	tokens hashés, usage unique
	•	normalisation email (lowercase + trim)

Done
	•	Connexion universelle par email, même sans Shopify

⸻

2.5 — Modèle identité interne

users
	•	email unique (index unique sur lower(email))

user_identities
	•	provider = shopify_customer
	•	provider_user_id = logged_in_customer_id
	•	FK vers users

Résolution
	•	Auto-login (proxy) : find/create par shopify_customer_id
	•	Magic link : find/create par email

(Fusion de comptes si nécessaire = Epic ultérieur, pas bloquant.)

Done
	•	Un utilisateur a un compte interne stable

⸻

2.6 — Memberships VIP/Reader (mois calendaire)

Tables

entitlement_state (1 ligne par user + type)
	•	user_id
	•	type vip|reader
	•	ends_at nullable
	•	updated_source admin_manual|shopify_order
	•	updated_by_admin_email nullable
	•	updated_at
	•	unique (user_id,type)

entitlement_events (idempotence/audit)
	•	user_id
	•	type
	•	source_ref = order_id:line_item_id unique
	•	months_added int
	•	created_at

access_controls
	•	is_banned
	•	is_readonly
	•	reason, timestamps

Extension “même date mois suivant”

Pour chaque mois ajouté :
	•	base = max(now, ends_at) (null → now)
	•	new_ends_at = base + N mois (calendaire)

Done
	•	Un rachat avant expiration étend depuis la fin
	•	VIP et Reader séparés

⸻

2.7 — Webhook Shopify orders/paid (auto-attribution)

Endpoint
	•	POST /webhooks/shopify/orders-paid

Sécurité
	•	vérif signature webhook Shopify
	•	idempotence via source_ref unique

Mapping produit
	•	utiliser Variant IDs (ou Product IDs) en config:
	•	VIP_VARIANT_IDS
	•	READER_VARIANT_IDS

Comportement
	1.	extraire email commande (normalisé)
	2.	find/create user par email (compte système si inexistant)
	3.	si is_banned → n’applique rien
	4.	sinon appliquer entitlements (mois calendaire)

Done
	•	Achat VIP/Reader active automatiquement le bon accès

⸻

2.8 — Viewer resolver (rôles + capacités)

Pour chaque requête :
	•	is_logged_in (session)
	•	is_banned, is_readonly
	•	viewer_tier :
	•	si pas loggé → public
	•	si loggé → connected
	•	si VIP actif → vip
	•	si Reader actif → reader
	•	si banni → forcer tier = public
	•	can_interact = false si banni ou readonly

Done
	•	Banni connecté = lecture seule + jamais premium

⸻

2.9 — Feed “Patreon strict” + exception bannis

Normal (non banni)
	•	Feed liste tous les posts
	•	Posts non accessibles → locked + preview + image locked + CTA

Banni
	•	Feed filtré : retourne uniquement les posts accessibles à public
	•	Donc :
	•	pas de VIP/Reader dans la liste
	•	pas de teasers VIP/Reader

Done
	•	Exactement ce que tu veux : seuls les bannis ne voient pas de teasers

⸻

2.10 — Interactions bloquées (enforcement serveur)

Endpoints :
	•	POST /comments → 403 si readonly/banni
	•	POST /reactions → 403 si readonly/banni

Done
	•	Impossible de bypass via requêtes directes

⸻

2.11 — Admin (support + sync)

Admin peut :
	•	toggle readonly / ban
	•	ajuster ends_at VIP/Reader manuellement
	•	bouton reconcile:
	•	“scanner commandes payées X jours”
	•	“sync order_id”

Règle
	•	Un achat futur réapplique/étend automatiquement (sauf banni)

Done
	•	Support client facile, et tu peux rattraper un webhook manqué

⸻

Critères DONE Epic 2

✅ /apps/arquanzia → auto-login quand Shopify fournit l’ID
✅ sinon /apps/arquanzia propose magic link
✅ arquanzia... direct = public + login email
✅ achats Shopify activent VIP/Reader (mois calendaire)
✅ bannis : login ok, lecture seule, feed filtré, aucun teaser VIP/Reader
✅ non bannis : Patreon strict (teasers locked)
✅ aucune fuite d’originaux/texte complet sans droit