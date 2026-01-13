Architecture d’auth portable (indépendante de Shopify)

Principe
	•	Shopify = méthode de login #1 (au début), via App Proxy
	•	Ton système = source of truth (identité + permissions + communauté)
	•	Tu maps shopify_customer_id → internal_user_id (UUID) dans ta DB
	•	Plus tard, quand tu quittes Shopify : tu branches une autre méthode de login (email magic link, passkey, etc.) sans casser les comptes

Pourquoi App Proxy aide
	•	L’utilisateur est déjà connecté à la boutique, Shopify forward la requête à ton backend via /apps/...
	•	Shopify signe la requête proxy; ton backend peut authentifier que ça vient bien de Shopify (signature)  ￼
	•	Shopify peut inclure un paramètre logged_in_customer_id (client connecté)  ￼
	•	Attention : pas de cookies côté app proxy (Shopify strip Cookie et Set-Cookie) donc il faut une auth stateless (signature + token interne)  ￼

Flow recommandé (stateless + portable)
	1.	Client visite https://ta-boutique.com/apps/communaute
	2.	Shopify proxy → ton backend reçoit query signé + shop, path_prefix, timestamp, signature + parfois logged_in_customer_id  ￼
	3.	Backend :
	•	vérifie la signature App Proxy  ￼
	•	récupère/ crée internal_user lié à shopify_customer_id
	•	émet un token interne (JWT court) dans l’HTML (pas cookie) ou via un endpoint /api/session (le front le conserve en mémoire/localStorage)
	4.	Toutes les requêtes “communauté” se font via ton API avec ce token interne.
	5.	Plus tard : tu remplaces l’étape 1 par ton propre login (email/passkey) mais tu gardes internal_user_id.