
EPIC 5 — ENGAGEMENT, COMMUNAUTÉ & ÉVOLUTIVITÉ

VERSION FINALE

⸻

OBJECTIF

Renforcer l’engagement et la rétention sans alourdir l’expérience, en gardant une communauté douce, lisible et maîtrisée :
	•	notifications internes
	•	annonces claires
	•	profils légers
	•	préparation mobile / API
	•	analytics internes simples
	•	zéro bruit inutile

⸻

5.1 — NOTIFICATIONS IN-APP

5.1.1 — Centre de notifications

Page : /notifications

Types de notifications :
	•	nouveau chapitre publié (Lecteur)
	•	nouvelle annonce importante
	•	nouvel article encyclopédie public
	•	rappel : accès Lecteur bientôt expiré

Caractéristiques :
	•	notifications uniquement in-app
	•	badge compteur dans la navigation
	•	aucune notification email (hors liseuse déjà gérée)

Données
	•	notifications (user_id, type, payload, is_read, created_at)

⸻

5.1.2 — Préférences utilisateur

Dans /mon-compte :
	•	activer / désactiver :
	•	notifications nouveaux chapitres
	•	notifications encyclopédie publique
	•	annonces générales

⸻

5.2 — POSTS ÉPINGLÉS & ANNONCES

5.2.1 — Posts épinglés
	•	1 post épinglé maximum par section :
	•	feed principal
	•	bibliothèque
	•	affiché en haut
	•	non compté comme “nouveau”

5.2.2 — Annonce système

Type de post spécial :
	•	“Annonce Arquanzia”
	•	visibilité :
	•	public
	•	connecté
	•	Lecteur
	•	usage :
	•	sortie de chapitre
	•	sortie de livre
	•	information importante
	•	pause / délai

⸻

5.3 — COMMENTAIRES (PÉRIMÈTRE RÉDUIT)

5.3.1 — Où les commentaires sont autorisés
	•	uniquement sur les posts
	•	notamment :
	•	posts d’annonce de publication de chapitre
	•	annonces générales

5.3.2 — Où les commentaires sont interdits
	•	❌ livres
	•	❌ chapitres
	•	❌ encyclopédie

5.3.3 — Règles
	•	commentaires à un seul niveau (pas de threads)
	•	suppression / masquage par admin
	•	bannis = lecture seule (déjà géré)

⸻

5.4 — PROFIL PUBLIC LÉGER

5.4.1 — Page profil

URL : /profil/{pseudo}

Contenu visible :
	•	pseudo
	•	badges :
	•	Lecteur
	•	VIP (si tu décides de l’afficher)
	•	activité récente optionnelle :
	•	derniers commentaires sur posts

Jamais affiché :
	•	email
	•	historique d’achats
	•	progression de lecture

⸻

5.5 — PRÉPARATION MOBILE / API (SANS APP)

5.5.1 — API readiness

Toutes les fonctionnalités clés doivent être accessibles via routes/API :
	•	feed
	•	posts
	•	bibliothèque (lecture)
	•	encyclopédie
	•	notifications
	•	compte / accès

Auth :
	•	session actuelle (web)
	•	token personnel (prévu, désactivé par défaut)

⸻

5.5.2 — Token personnel (désactivé par défaut)

Dans /mon-compte :
	•	générer / révoquer un token
	•	usage futur :
	•	app mobile
	•	outils internes
	•	intégrations privées

⸻

5.6 — ANALYTIQUES INTERNES (LÉGÈRES)

5.6.1 — Mesures autorisées
	•	chapitres les plus lus
	•	livres les plus consultés
	•	articles encyclopédie publics les plus vus
	•	taux de reprise de lecture

5.6.2 — Exclusions
	•	aucun tracking tiers
	•	aucun cookie invasif
	•	aucune revente de données

⸻

5.7 — OPS & QUALITÉ

5.7.1 — Soft delete
	•	posts
	•	commentaires
	•	notifications

5.7.2 — Audit
	•	bannissements
	•	changements d’accès
	•	actions admin critiques

⸻

5.8 — TICKETS EPIC 5 (FINAUX)
	1.	Centre de notifications /notifications
	2.	Génération notifications (chapitres, annonces, rappels)
	3.	Préférences notifications utilisateur
	4.	Posts épinglés par section
	5.	Annonces système
	6.	Commentaires uniquement sur posts
	7.	Page profil public /profil/{pseudo}
	8.	API readiness (routes propres)
	9.	Tokens personnels (désactivés)
	10.	Analytique interne basique
	11.	Soft delete & audit admin

⸻

STATUT EPIC 5

NETTOYÉ – ALIGNÉ – COHÉRENT
	•	aucune discussion sur les livres
	•	aucune discussion sur l’encyclopédie
	•	pas d’internationalisation
	•	communauté calme et maîtrisée
	•	prêt pour mobile sans dépendance

⸻

Prochaine étape possible
	•	Prioriser Epic 5 (P1 / P2 / P3)
	•	ou attaquer Epic 6 : App mobile / Offline / Liseuse avancée
	•	ou faire un roadmap global (Epics 1 → 5) pour planifier le développement

Dis-moi ce que tu veux attaquer ensuite 🚀