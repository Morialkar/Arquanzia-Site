> ⚠️ **Document partiellement périmé.** Il décrit la bibliothèque et l'encyclopédie telles
> qu'elles existent, routes françaises comprises. En revanche tout ce qui concerne le palier
> « Lecteur », le verrouillage de contenu, les CTA d'accès, les règles de bannissement et les
> pages `/connexion`, `/mon-compte`, `/mon-acces` ne s'applique plus : le site n'a pas de
> compte lecteur et tout le contenu est en lecture publique. La seule règle d'accès est le
> statut de publication. Voir `documentation/plan de reprise.md`.

⸻

OBJECTIF

Mettre en place :
	•	une Bibliothèque Lecteur (livres + chapitres, lecture et téléchargement)
	•	une Encyclopédie Arquanzia (publique avec option Lecteur)
	•	une UX cohérente avec verrouillage, CTA et règles bannis
	•	des slugs français sans accents
	•	du contenu markdown rendu en HTML au moment de l’affichage

⸻

3.1 — SLUGS & ROUTES (FR, SANS ACCENTS)

Générales
	•	/
	•	/connexion
	•	/deconnexion
	•	/mon-compte
	•	/mon-acces

Bibliothèque
	•	/bibliotheque
	•	/bibliotheque/{slug-livre}
	•	/bibliotheque/{slug-livre}/chapitre/{slug-chapitre}

Encyclopédie
	•	/encyclopedie
	•	/encyclopedie/{chemin...}
	•	/encyclopedie/{chemin...}/{slug-article}

Téléchargements
	•	/telechargement/livre/{slug-livre}.{format}
	•	/telechargement/livre/{slug-livre}/chapitre/{slug-chapitre}.{format}

⸻

3.2 — BIBLIOTHÈQUE (LECTEURS)

VISIBILITÉ & ACCÈS

Statut	Bibliothèque	Livre	Chapitre	Téléchargement
Public	Visible	Locked + CTA	Locked + CTA	CTA
Connecté	Visible	Locked + CTA	Locked + CTA	CTA
Lecteur	Visible	Accès	Accès	Accès
Banni	Onglet masqué	URL directe → Locked + CTA	Locked + CTA	CTA


⸻

CHAPITRES “BIENTÔT”
	•	visibles pour tous
	•	affichés en gris avec badge “Bientôt”
	•	non lisibles
	•	non téléchargeables
	•	CTA visible

⸻

MODÈLE DE DONNÉES

books
	•	id
	•	slug (unique)
	•	title
	•	description_md
	•	cover_media_id
	•	is_published
	•	created_at

book_files
	•	book_id
	•	format (pdf|epub)
	•	file_media_id

chapters
	•	id
	•	book_id
	•	slug (unique par livre)
	•	title
	•	order_index
	•	content_md
	•	is_published
	•	published_at

chapter_files
	•	chapter_id
	•	format (pdf|epub)
	•	file_media_id

⸻

CONTENU
	•	stockage : markdown
	•	rendu : markdown → HTML au runtime
	•	aucun contenu markdown servi si accès verrouillé
	•	HTML sanitizé

⸻

TÉLÉCHARGEMENTS
	•	toujours servis par route backend
	•	contrôle d’accès strict
	•	non autorisé → page friendly avec CTA
	•	aucun fichier servi sans droit

⸻

3.3 — ENCYCLOPÉDIE (PUBLIQUE + LECTEUR)

VISIBILITÉ & ACCÈS

Statut	Article public	Article Lecteur
Public	Visible	Locked + CTA
Connecté	Visible	Locked + CTA
Lecteur	Visible	Visible
Banni	Visible	Masqué


⸻

MODÈLE DE DONNÉES

encyclopedia_nodes
	•	id
	•	parent_id
	•	type (category|article)
	•	slug
	•	title
	•	visibility (public|reader)
	•	teaser_md
	•	order_index

encyclopedia_articles
	•	node_id
	•	content_md
	•	cover_media_id
	•	updated_at

⸻

RÈGLES
	•	articles public accessibles à tous
	•	articles reader :
	•	non lecteur → locked + CTA
	•	lecteur → accès complet
	•	banni → masqué (non listé, accès direct refusé)
	•	aucun contenu réel servi si locked

⸻

3.4 — POSTS VS CHAPITRES
	•	posts = feed, annonces, imprimantes
	•	chapitres = bibliothèque
	•	systèmes distincts
	•	un post peut pointer vers un chapitre

⸻

3.5 — UX LOCKED & CTA
	•	locked ≠ vide
	•	toujours afficher :
	•	titre
	•	contexte
	•	CTA clair
	•	jamais afficher :
	•	texte complet
	•	images originales
	•	fichiers
	•	CTA uniforme :
	•	“Devenir Lecteur”
	•	“Déverrouiller l’accès Lecteur”

⸻

3.6 — BANNIS
	•	connexion autorisée
	•	lecture seule
	•	aucun contenu premium listé
	•	bibliothèque : onglet masqué, URL directe locked + CTA
	•	encyclopédie lecteur : masqué
	•	aucun téléchargement possible

⸻

3.7 — TICKETS EPIC 3

Bibliothèque
	1.	Tables livres / chapitres / fichiers (markdown)
	2.	Pages bibliothèque + livre + chapitre
	3.	Rendu markdown → HTML
	4.	Téléchargements sécurisés
	5.	Locked + CTA
	6.	Gestion “Bientôt”

Encyclopédie
	7.	Tables arborescence + articles (markdown)
	8.	CRUD admin catégories / articles
	9.	Navigation arborescente
	10.	Locked + CTA lecteur
	11.	Masquage bannis

Compte
	12.	Accès /mon-compte (pseudo)
	13.	Accès /mon-acces (statuts)
	14.	Respect des règles bannis / lecture seule

⸻

EPIC 3 — STATUT

COMPLET, FIGÉ, PRÊT À DÉVELOPPER