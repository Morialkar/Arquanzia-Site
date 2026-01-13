Parfait — voici Epic 0 refait au complet, avec toutes tes décisions intégrées (feed “Patreon strict”, audiences, flou backend, preview personnalisable, babillard équipe seulement, page imprimantes séparée, etc.).

⸻

Epic 0 — Cadrage & garde-fous (VERSION FINALE)

🎯 Vision

Créer une section communautaire publique (accessible à tous) qui sert de vitrine vivante de l’univers, avec un feed type Patreon où tout le monde voit tous les posts, mais où le contenu est verrouillé selon les droits d’accès.
	•	Le feed est un babillard éditorial : vous seuls publiez.
	•	Les clients peuvent réagir et commenter (selon permissions).
	•	Les contenus “premium” sont séparés en deux branches indépendantes :
	•	VIP (précommandes / nouveautés / accès avancé)
	•	Reader (chapitres / contenus lecture)
	•	La page Statut imprimantes est séparée pour un contrôle d’accès plus fin.

⸻

🧱 Modèle d’accès (identités)

Statuts utilisateurs
	•	Public : non connecté
	•	Connecté : compte Shopify connecté (sans abonnement requis)
	•	VIP : accès VIP actif
	•	Reader : accès Reader actif

✅ Règles :
	•	VIP ≠ Reader (aucun accès croisé)
	•	VIP et Reader sont aussi “connectés” (donc voient les contenus “Connecté”)

⸻

🔐 Audiences des posts (champ audience)

Chaque post est classé dans une seule audience :
	1.	public
	2.	connected
	3.	vip
	4.	reader

Matrice d’accès (verrouillage)

Utilisateur \ Audience du post	Public	Connecté	VIP	Reader
Non connecté	✅	🔒	🔒	🔒
Connecté	✅	✅	🔒	🔒
VIP	✅	✅	✅	🔒
Reader	✅	✅	🔒	✅


⸻

0.1 — Feed (Babillard) : UX “Patreon strict”

Règle d’or

Tous les posts apparaissent dans le feed, pour tout le monde,
mais le contenu est bloqué si l’utilisateur ne remplit pas la condition d’accès.

Aucun feed vide. Jamais un “403” à la place d’un post.

Affichage d’un post (toujours visible)
	•	Titre (toujours lisible)
	•	Preview (1–2 lignes lisibles) personnalisable par post
	•	Première image (toujours affichée) — MAIS :
	•	version floutée si l’utilisateur n’a pas accès
	•	version originale si l’utilisateur a accès
	•	Badges et CTA (“Accès VIP requis”, “Connecte-toi”, etc.)

Texte long
	•	Longueur du texte complet : illimitée
	•	Si accès autorisé :
	•	afficher les 450 premiers caractères, puis “Voir plus” pour dérouler
	•	Si accès non autorisé :
	•	aucun extrait automatique du contenu complet
	•	seul preview_text est visible
	•	le reste est remplacé par un bloc verrouillé (aucun texte lisible)

✅ Important :
	•	Le texte verrouillé doit être 0 lisible (pas de faux flou lisible).

⸻

0.2 — Règle sécurité : pas de bypass

Images

Si un post est verrouillé :
	•	le backend ne renvoie pas l’URL originale
	•	il renvoie une variante floutée générée côté backend (ou pré-générée)

➡️ Objectif : empêcher le bypass “je copie l’URL de l’image”.

Texte

Si un post est verrouillé :
	•	le backend ne renvoie jamais content_full
	•	il ne renvoie pas non plus une version “floutée” du contenu (même partielle)
	•	il renvoie uniquement :
	•	title
	•	preview_text (personnalisable)
	•	métadonnées
	•	images floutées

⸻

0.3 — Règles de communauté & modération (MVP léger)
	•	Les règles existent déjà (pas besoin de formaliser lourd)
	•	Comme seuls vous publiez les posts :
	•	la modération est concentrée sur les commentaires
	•	Fonctionnalités minimum attendues :
	•	supprimer commentaire
	•	“lecture seule” par utilisateur (bloquer commentaire/réaction)
	•	ban permanent (voir 0.4)

⸻

0.4 — Contrôles & sanctions

Modes
	•	Lecture seule activable par l’équipe pour un utilisateur :
	•	peut lire, mais ne peut pas commenter/réagir

Bannissement permanent
	•	Le ban est géré “au plus large possible” :
	•	email
	•	téléphone
	•	adresse
	•	IP (avec prudence — IP changeante, donc c’est un signal, pas une vérité)

Note (pour la suite) : l’objectif est la dissuasion et la réduction des abus, pas une “sécurité parfaite” (sinon ça devient disproportionné).

⸻

0.5 — Identité (pseudo)
	•	Les clients peuvent définir un pseudo
	•	Le pseudo est affiché dans :
	•	commentaires
	•	réactions
	•	Le nom réel Shopify n’est pas affiché publiquement

⸻

0.6 — Statut imprimantes (séparé)
	•	Page dédiée “Imprimantes”
	•	Contrôle d’accès plus fin que le feed
	•	(MVP) visible au minimum pour Connecté (à confirmer plus tard si tu veux restreindre davantage)
	•	Cette page n’est pas mêlée au feed pour garder une séparation claire “communauté vs monitoring”

⸻

✅ Périmètre MVP confirmé
	•	Feed (posts équipe)
	•	Commentaires + réactions (si non lecture seule / non banni)
	•	Posts verrouillés Patreon strict (backend)
	•	Audience public/connected/vip/reader
	•	Page imprimantes séparée (accès contrôlé)

❌ Hors-scope (pour maintenant)
	•	Chat temps réel
	•	Posts par clients
	•	DM
	•	Notifications push
	•	App mobile native

⸻

Livrables Epic 0 (ce qui est “done”)
	1.	Modèle d’audience + matrice d’accès (ci-dessus)
	2.	UX “Patreon strict” (preview personnalisable + flou backend)
	3.	Décisions sur rôles & séparation VIP/Reader
	4.	Décisions modération (lecture seule, ban)
	5.	Décision séparation page imprimantes

⸻

Si tu veux, on enchaîne direct avec Epic 2 (Auth App Proxy + RBAC) et je te le détaille pareil (objectifs, flows, données minimales, critères d’acceptation) en gardant l’auth portable hors Shopify.