OBJECTIF

Améliorer l’expérience globale des utilisateurs (surtout Lecteurs) sans modifier l’architecture de base :
	•	confort de lecture
	•	navigation et recherche
	•	self-serve côté client
	•	outils ops/admin
	•	livraison automatique des nouveaux chapitres par courriel (liseuses)
	•	envoi uniquement en pièces jointes (PDF / EPUB)
	•	aucun accès public ponctuel
	•	aucune validation obligatoire des adresses

⸻

4.1 — EXPÉRIENCE DE LECTURE (LECTEURS)

4.1.1 — Progression de lecture
	•	mémoriser :
	•	dernier chapitre lu par livre
	•	position (offset ou ancre, optionnel)
	•	affichage :
	•	bouton « Reprendre la lecture » sur la page du livre
	•	badges :
	•	Lu
	•	En cours
	•	Nouveau chapitre

Données
	•	reading_progress (user_id, book_id, chapter_id, progress, updated_at)

⸻

4.1.2 — Favoris
	•	favoris possibles sur :
	•	livres
	•	articles d’encyclopédie
	•	accès rapide depuis :
	•	/mon-compte
	•	/bibliotheque
	•	/encyclopedie

Données
	•	favorites (user_id, type, target_id, created_at)

⸻

4.2 — RECHERCHE

4.2.1 — Recherche globale
	•	champ unique dans la navigation
	•	index :
	•	titres de livres
	•	titres de chapitres
	•	titres et catégories encyclopédie
	•	métadonnées uniquement (pas le contenu markdown)

4.2.2 — Filtrage par droits
	•	aucun résultat ne révèle du contenu inaccessible
	•	bannis : uniquement contenu public

⸻

4.3 — UX « MON ACCÈS » (SELF-SERVE)

4.3.1 — Rafraîchir mon accès
	•	bouton « Rafraîchir mon accès »
	•	recalcul côté serveur :
	•	entitlements actifs
	•	dates d’expiration
	•	aucune dépendance directe à Shopify

⸻

4.3.2 — Messages intelligents
	•	aucun accès actif :
	•	“As-tu utilisé le même courriel que lors de l’achat ?”
	•	liens directs :
	•	produit VIP
	•	produit Lecteur

⸻

4.4 — LIVRAISON AUTOMATIQUE DES CHAPITRES PAR COURRIEL (LISEUSES)

PRINCIPE
	•	réservé aux Lecteurs actifs
	•	envoi automatique à chaque nouveau chapitre publié
	•	pièce jointe uniquement
	•	formats : EPUB / PDF
	•	aucune URL publique
	•	aucun lien cliquable requis

⸻

4.4.1 — UI UTILISATEUR (/mon-acces)

Section « Liseuse / Livraison automatique »

Fonctionnalités :
	•	ajouter une adresse courriel
	•	bouton « Utiliser l’adresse de mon compte »
	•	activer / désactiver la livraison
	•	choisir le format :
	•	EPUB
	•	PDF
	•	Les deux
	•	liste des adresses actives

Règles :
	•	visible pour tout utilisateur connecté
	•	livraison activable seulement si Lecteur actif
	•	si Lecteur expire :
	•	envois suspendus
	•	configuration conservée

⸻

4.4.2 — AJOUT D’ADRESSE (SANS VALIDATION)
	•	aucune confirmation par clic
	•	aucune vérification obligatoire
	•	l’adresse est active dès l’ajout

Feedback UX :

« Adresse ajoutée.
Assure-toi qu’elle est autorisée à recevoir des fichiers depuis notre adresse d’envoi. »

Sécurité alternative :
	•	max 3 adresses par utilisateur
	•	uniquement pour Lecteurs actifs
	•	désactivation automatique après échecs répétés

⸻

4.4.3 — DÉCLENCHEUR « NOUVEAU CHAPITRE »

Un envoi est déclenché quand :
	•	is_published = true
	•	published_at <= now

Pipeline :
	•	sélectionner les Lecteurs actifs
	•	récupérer leurs adresses actives
	•	créer des jobs d’envoi

⸻

4.4.4 — JOBS D’ENVOI (PIÈCES JOINTES)

Chaque job :
	•	lit le fichier via chapter_files
	•	attache le fichier (PDF / EPUB)
	•	envoie le courriel

Logs
	•	delivery_jobs (user_id, delivery_email_id, book_id, chapter_id, format_sent, status, error_message, attempts, timestamps)

Gestion erreurs :
	•	retry avec backoff
	•	compteur d’échecs par adresse
	•	désactivation automatique si seuil atteint

⸻

4.4.5 — SELF-SERVE LIVRAISON

Dans /mon-acces :
	•	bouton « Tester l’envoi »
	•	historique des 20 derniers envois
	•	statuts clairs :
	•	Envoyé
	•	Échoué
	•	Désactivé

⸻

4.5 — MÉDIAS & ROBUSTESSE

4.5.1 — Previews dérivées
	•	génération :
	•	miniatures
	•	images floutées (locked)
	•	toujours servies par routes backend

⸻

4.6 — OPS & ADMIN

4.6.1 — Dashboard admin « Santé »
	•	derniers envois de chapitres
	•	erreurs récurrentes
	•	chapitres sans fichiers attachés
	•	statistiques simples :
	•	envois réussis
	•	échecs

⸻

4.6.2 — Actions admin
	•	relancer un envoi
	•	désactiver une adresse
	•	consulter l’historique par utilisateur

⸻

4.7 — TICKETS EPIC 4 (FINAUX)
	1.	Progression de lecture (livres/chapitres)
	2.	Favoris (bibliothèque + encyclopédie)
	3.	Recherche globale (titres + métadonnées)
	4.	Rafraîchir mon accès (self-serve)
	5.	Messages UX intelligents /mon-acces
	6.	UI gestion adresses liseuse
	7.	Limites & règles adresses (max, erreurs)
	8.	Déclencheur publication chapitre
	9.	Jobs d’envoi courriel avec pièces jointes
	10.	Historique utilisateur des envois
	11.	Dashboard admin livraison
	12.	Gestion erreurs / retries / désactivation

⸻

STATUT EPIC 4

COMPLET – VALIDÉ – PRÊT À DÉVELOPPER
Aucune régression, aucune surface d’attaque inutile, UX adaptée aux liseuses, et cohérence totale avec les Epics précédents.