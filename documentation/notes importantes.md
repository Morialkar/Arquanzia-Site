1) Objectifs
	•	Créer une communauté client exclusive, simple, alignée marque.
	•	Centraliser status imprimantes + updates + interactions.
	•	Réduire dépendance à Instagram/Meta.
	•	Garder une trajectoire vers un site e-commerce maison.

2) Contraintes clés Shopify App Proxy
	•	App Proxy strip Cookie et Set-Cookie, donc pas de session cookie classique  ￼
	•	Auth = signature App Proxy + token interne stateless
	•	Shopify peut fournir logged_in_customer_id dans la query proxy  ￼
	•	Certains headers sont strip (liste “disallowed headers”)  ￼

3) Modèle d’identité (portable)
	•	internal_user (UUID) = clé primaire pour toujours
	•	shopify_customer_id = attribut / méthode de login
	•	roles (customer/vip/mod/admin)
	•	Plan futur : ajouter login email/passkey

4) Données (MVP)
	•	users(id, role, created_at)
	•	user_identities(user_id, provider, provider_user_id)
	•	posts(id, user_id, body, created_at, pinned)
	•	comments(id, post_id, user_id, body, created_at)
	•	reactions(post_id, user_id, type)
	•	printers(id, name, entity_id, visible)
	•	printer_status(printer_id, state, updated_at)

5) Sécurité
	•	Vérif signature App Proxy sur toutes les pages proxy  ￼
	•	Tokens internes courts (rotation)
	•	Protection anti-abus (rate limit) sur endpoints publics
	•	Webhooks screenshots : token en query (pas Basic Auth) (Meta/clients)

6) UX “clients pas tech”
	•	Feed + commentaires > chat temps réel (moins stressant)
	•	Un lien unique depuis Shopify (déjà familier)
	•	Onboarding en 2 phrases max

7) Intégrations
	•	Home Assistant : source status
	•	n8n : automatisations (screenshots, posts automatiques, digests)

8) Roadmap
	•	MVP = Auth + Portail + Feed + Status imprimantes
	•	Phase 2 = Notifications + Admin + Migration-ready
	•	Phase 3 = PWA / mobile