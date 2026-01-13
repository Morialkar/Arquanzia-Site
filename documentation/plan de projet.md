Epic 0 — Cadrage & garde-fous

Objectif : clarifier le “minimum lovable community” et les contraintes.
	•	Décisions :
	•	Community = temps réel (chat) vs asynchrone (feed + commentaires)
	•	Accès : “tous les clients” vs “clients actifs / VIP / commandes en cours”
	•	Livrables :
	•	Liste des features MVP
	•	Politique de modération & règles de communauté
	•	Done when :
	•	Une doc “Scope MVP / non-scope” est figée

⸻

Epic 1 — Fondation technique (backend + infra)

Objectif : un backend propre, testable, loggé, prêt prod.
	•	Choix minimal :
	•	API backend (Node/Express, Fastify, Remix, etc.)
	•	DB (Postgres recommandé)
	•	Storage images (optionnel au début)
	•	Done when :
	•	Environnement dev + prod
	•	Monitoring minimal (logs structurés + alerting erreur 5xx)

⸻

Epic 2 — Auth App Proxy + Identité interne portable

Objectif : se connecter via Shopify sans dépendre de Shopify à long terme.
	•	Sous-tâches :
	•	Vérif signature App Proxy (HMAC “signature” proxy)  ￼
	•	Gestion shop + logged_in_customer_id  ￼
	•	Création internal_user (UUID) + mapping Shopify
	•	Token interne stateless (pas cookies) car App Proxy strip cookies  ￼
	•	RBAC simple (roles: customer, vip, moderator, admin)
	•	Done when :
	•	Un client connecté Shopify arrive dans le portail sans re-login
	•	Un non-connecté est redirigé vers login Shopify / message “connecte-toi”
	•	DB contient internal_user stable

⸻

Epic 3 — Portail “Zone client” (UI)

Objectif : une page simple, mobile-first, qui “feel” comme votre marque.
	•	MVP UI :
	•	Page d’accueil communauté
	•	Fil d’updates (annonces / posts)
	•	Page “Statut imprimantes” (liens screenshots)
	•	Done when :
	•	Navigation claire
	•	Temps de chargement correct sur mobile

⸻

Epic 4 — Communauté MVP (feed + commentaires + réactions)

Objectif : “communauté” sans chat lourd (meilleur pour clients pas tech).
	•	Features :
	•	Posts (texte + image optionnelle)
	•	Commentaires
	•	Réactions (❤️ / 🔥 / ✨)
	•	Épingler un post “règles / liens / status”
	•	Modération MVP :
	•	Signalement
	•	Suppression (modo/admin)
	•	Done when :
	•	Les clients peuvent interagir comme dans un mini “groupe”

⸻

Epic 5 — Intégration Home Assistant / Imprimantes

Objectif : status fiable + screenshots.
	•	Features :
	•	Récupération états depuis HA (poll ou webhooks)
	•	Page status : liste imprimantes + “en cours / idle”
	•	Screenshot on-demand (ton système webhook binaire n8n)
	•	Done when :
	•	Status s’actualise (manuel + auto-refresh optionnel)
	•	Screenshot accessible via URL sécurisée (token)

⸻

Epic 6 — Notifications (douces)

Objectif : garder la vibe communauté sans spam.
	•	MVP :
	•	Email “digest” hebdo (nouveaux posts + nouveautés)
	•	Mentions / réponses → notification (email ou in-app)
	•	Done when :
	•	Les clients reviennent sans devoir “surveiller”

⸻

Epic 7 — Admin console

Objectif : gestion simple pour vous.
	•	Dashboard :
	•	Gérer imprimantes (noms, ordre, visibilité)
	•	Gérer clients/roles (VIP, modérateur)
	•	Gérer contenu (pin, delete, ban)
	•	Done when :
	•	Vous pouvez opérer sans toucher au code

⸻

Epic 8 — Migration-ready (sortie de Shopify)

Objectif : pouvoir quitter Shopify sans reset la communauté.
	•	Travail :
	•	Ajouter 2e méthode de login : email magic link / passkey
	•	Linking: lier compte Shopify ↔ email
	•	Export/import des users
	•	Done when :
	•	Un utilisateur peut se connecter même si Shopify n’existe plus
	•	internal_user_id reste identique

⸻

Epic 9 — App mobile (phase 2)

Objectif : “app maison” sans réécrire tout.
	•	Stratégie :
	•	PWA d’abord (icône sur écran d’accueil)
	•	Puis wrapper natif si nécessaire
	•	Done when :
	•	Notifications push (optionnel) + UX fluide