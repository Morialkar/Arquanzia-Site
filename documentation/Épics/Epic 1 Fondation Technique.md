Epic 1 — Fondation technique (Laravel + Postgres + médias + admin) — VERSION COMPLÈTE (finale)

Objectif

Mettre en place la base technique stable, portable et sécurisée qui va supporter :
	•	le micro-site public (feed Patreon strict)
	•	l’API (feed, posts, médias)
	•	le stockage médias (originaux + locked floutés)
	•	une console admin maison avec magic links
	•	le tout déployé sur ton serveur WHM/cPanel
	•	avec un backend réel sur : arquanzia.creations-sortilege.com
	•	et prêt à recevoir l’App Proxy Shopify plus tard (Epic 2)

⸻

Architecture finale (Epic 1)

A) Domaine & rôles
	•	Backend réel (source of truth) : https://arquanzia.creations-sortilege.com
	•	Laravel
	•	Postgres
	•	Storage images local
	•	Admin console
	•	API
	•	Shopify App Proxy (plus tard) : https://creations-sortilege.com/apps/arquanzia
	•	Shopify sert de “porte d’entrée” et d’auth client
	•	Shopify proxifie vers le backend réel (routes /proxy/*)

Epic 1 ne nécessite pas encore Shopify : on construit le backend + portail public, puis Epic 2 branche l’App Proxy.

⸻

Stories / Tickets

1.1 — Projet Laravel + déploiement cPanel

Description
	•	Créer app Laravel (PHP 8.2+ idéal)
	•	Config env dev/prod
	•	Déploiement sur arquanzia.creations-sortilege.com
	•	HTTPS actif
	•	Config logs (fichier)

Endpoints
	•	GET /healthz → JSON {"ok":true}

Done
	•	healthz fonctionne en prod
	•	erreurs Laravel loggées correctement

⸻

1.2 — Postgres + migrations (schéma MVP)

Description
	•	Config connexion Postgres
	•	Ajouter migrations + UUIDs
	•	Index/contraintes de base

Tables (MVP)

users
	•	id UUID PK
	•	handle (pseudo) nullable, unique
	•	created_at, updated_at

user_identities
	•	id UUID PK
	•	user_id FK
	•	provider (ex: shopify, futur email)
	•	provider_user_id (ex: customer_id Shopify)
	•	created_at
	•	Unique (provider, provider_user_id)

posts
	•	id UUID PK
	•	author_user_id FK (team)
	•	audience enum: public|connected|vip|reader
	•	title
	•	preview_text (obligatoire, personnalisable)
	•	content_full (texte complet)
	•	created_at, updated_at

post_media
	•	id UUID PK
	•	post_id FK
	•	position int
	•	original_path
	•	locked_path
	•	mime
	•	original_width, original_height (optionnel)
	•	locked_width, locked_height (optionnel)
	•	created_at

comments
	•	id UUID PK
	•	post_id FK
	•	user_id FK
	•	body
	•	created_at
	•	deleted_at (soft delete)

reactions
	•	post_id FK
	•	user_id FK
	•	type (ex: sparkle|heart|fire)
	•	created_at
	•	Unique selon modèle choisi (souvent (post_id,user_id,type))

access_controls
	•	user_id FK unique
	•	is_readonly bool
	•	is_banned bool
	•	ban_reason text
	•	banned_at
	•	(futur Epic 2/7) vip_until, reader_until

admin_allowlist
	•	id UUID PK
	•	email unique
	•	role (admin|editor)
	•	created_by_email
	•	created_at

magic_links
	•	id UUID PK
	•	email
	•	token_hash
	•	expires_at
	•	used_at
	•	created_at
	•	index sur (email, expires_at)

audit_log
	•	id UUID PK
	•	actor_email nullable
	•	action
	•	meta jsonb
	•	ip nullable
	•	created_at

Done
	•	migrations OK en prod
	•	seed minimal (team user) OK

⸻

1.3 — Stockage médias local + service par route (anti-bypass total)

Décision
	•	Aucune image ne doit être accessible par URL directe de fichier
	•	Tout est servi par route backend

Description
	•	Stocker fichiers dans storage/app/media/... (non web)
	•	Créer route unique de service :
	•	GET /media/{mediaId}
	•	La route :
	•	renvoie locked ou original selon un paramètre/contrôle (en attendant Epic 2)
	•	ajoute headers cache raisonnables

Done
	•	Impossible d’atteindre un original sans passer par /media/{id}
	•	Les locked sont servies publiquement, les originals seulement quand “unlocked” (même si pour l’instant c’est un mode test)

⸻

1.4 — Génération synchronisée des images locked (0 lisible)

Décision
	•	Génération synchrone à la création de post (posts rares)

Description
	•	À l’upload d’une image :
	•	stocker original
	•	générer locked :
	•	blur fort + pixelate + downscale
	•	objectif : 0 lisible
	•	Support multi-images par post

Done
	•	Chaque image a 2 versions stockées
	•	La locked est inutilisable pour lire/déduire le contenu

⸻

1.5 — Modèle “Patreon strict” au niveau API (field-level access)

Description
Implémenter une logique de rendu par “viewer” (temporaire pour dev) :
	•	viewer = public | connected | vip | reader

Endpoints :
	•	GET /api/feed?viewer=... → liste tous les posts
	•	GET /api/posts/{id}?viewer=... → détail

Règles
	•	Tous les posts sont listés, mais :
	•	toujours : title, preview_text, audience, created_at
	•	si locked :
	•	pas de content_full
	•	médias renvoyés = locked uniquement
	•	si unlocked :
	•	content_full présent
	•	médias = original

Matrice d’accès

Utilisateur \ Audience	public	connected	vip	reader
public	✅	🔒	🔒	🔒
connected	✅	✅	🔒	🔒
vip	✅	✅	✅	🔒
reader	✅	✅	🔒	✅

Done
	•	L’API ne “leak” jamais le contenu verrouillé
	•	Aucun original n’est renvoyé si locked

⸻

1.6 — Front public minimal (sur arquanzia.creations-sortilege.com)

Description
Pages :
	•	GET / : feed public

Comportements UI :
	•	Afficher tous les posts
	•	Titre + preview toujours visibles
	•	Image visible mais locked si besoin
	•	Si unlocked :
	•	afficher 450 chars + bouton “Voir plus”
	•	Si locked :
	•	ne pas afficher de texte flouté (juste un bloc locked + CTA)

CTA (placeholder) :
	•	“Se connecter pour voir”
	•	“Accès VIP requis”
	•	“Accès Lecteur requis”

Done
	•	UX “Patreon strict” fonctionne sur mobile
	•	Aucun contenu complet n’apparaît côté public

⸻

Admin (auth + console)

1.7 — Admin auth par magic links (Root admin hardcodé)

Décision
	•	Root admin hardcodé : info@creations-sortilege.com
	•	Non supprimable, non en DB

Description
Routes :
	•	GET/POST /admin/login (email)
	•	GET /admin/magic/{token} (consomme le token, ouvre session)
	•	Session admin via cookie httpOnly (OK ici)

Règle d’accès :
	•	email autorisé si :
	•	email == root_admin (config)
	•	OU présent dans admin_allowlist

Sécurité :
	•	rate limit sur /admin/login (IP + email)
	•	token stocké hashé, usage unique, TTL recommandé 15 min
	•	audit log : link sent/used

Done
	•	Seul info@... peut entrer au départ
	•	magic link expire et ne se réutilise pas
	•	pas de lock-out possible du root

⸻

1.8 — Admin UI : gestion des admins (allowlist)

Description
	•	/admin/admins
	•	Root admin peut :
	•	ajouter email + role
	•	retirer email
	•	Root admin affiché “Root”, sans action suppression

Done
	•	Ajout/retrait fonctionne
	•	Audit log des changements

⸻

1.9 — Admin UI : création de posts (core)

Description
Formulaire :
	•	audience (public|connected|vip|reader)
	•	title
	•	preview_text (obligatoire)
	•	content_full
	•	upload multi-images
	•	aperçu “view as” (public/connected/vip/reader)

Done
	•	Créer post + images fonctionne
	•	locked généré automatiquement
	•	Preview as viewer cohérent avec l’API

⸻

1.10 — Admin UI : modération minimale

Description
	•	vue commentaires récents
	•	supprimer commentaire (soft delete)
	•	mettre user en readonly / banned

Done
	•	readonly empêche commenter/réagir (API refuse)
	•	banned empêche actions (et plus tard accès sections)

⸻

Ops / qualité

1.11 — Backups & runbook

Description
	•	backup Postgres quotidien (cron cPanel ou script)
	•	backup dossier storage/app/media
	•	doc courte restauration

Done
	•	backups tournent
	•	doc existe et est testable

⸻

1.12 — Logging, erreurs, sécurité basique

Description
	•	logs structurés + request_id
	•	pages erreur propres
	•	headers de sécurité (CSP simple plus tard)
	•	rate limiting sur endpoints sensibles (admin login, media si nécessaire)

Done
	•	on peut diagnostiquer un crash
	•	pas de spam brut sur login admin

⸻

Paramètres figés
	•	Backend principal : arquanzia.creations-sortilege.com
	•	Admin : magic links + root hardcodé info@creations-sortilege.com
	•	DB : Postgres (cPanel plugin)
	•	Médias : stockage local non public, servis uniquement par route
	•	Locked images : générées synchrones, 0 lisible
	•	UX feed : “Patreon strict” (titre + preview visibles, rien d’autre si locked)

⸻

Dépendances vers Epic 2 (pour que ça s’emboîte)

Epic 1 prépare :
	•	/proxy/* routes (placeholder) — sera utilisé par App Proxy Shopify
	•	logique “viewer” temporaire → remplacée par rôle dérivé de Shopify (public/connected/vip/reader)
	•	endpoints prêts à appliquer la matrice d’accès sans refactor

⸻

Si tu veux, on peut enchaîner tout de suite sur Epic 2 et je te le découpe pareil, avec :
	•	config App Proxy Shopify (/apps/arquanzia → /proxy/arquanzia)
	•	vérification signature proxy
	•	comment déterminer connected/vip/reader sans te “marier” à Shopify (produits + webhooks + table d’abonnements interne, etc.).