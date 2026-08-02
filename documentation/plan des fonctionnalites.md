# Plan des fonctionnalités

Neuf ajouts, retenus au fil de trois tours d'idées dont deux largement écartés. Les contraintes qui ont guidé la sélection,
et qui doivent continuer à guider l'exécution :

- **rien qui dépende des dates** — la chronologie interne reste volontairement floue jusqu'à
  la fin du tome 1 ;
- **rien qui exige des illustrations à produire** — pas de carte, pas d'assets à dessiner ;
- **rien qui suppose beaucoup de contenu** — le site compte quinze documents, et une
  fonctionnalité qui ne prend son sens qu'à cent est prématurée ;
- **rien dont le comportement demande une explication embarrassante** — si une donnée peut
  disparaître sans que le lecteur comprenne pourquoi, l'idée est mauvaise.

Vérifié avant de proposer, pour ne pas réinventer l'existant : les wikilinks et leur aperçu
au survol **fonctionnent déjà** dans les chapitres comme dans l'encyclopédie.

---

## Ordre recommandé

Regroupé par thème **et** par fichiers touchés : plusieurs de ces ajouts modifient les mêmes
gabarits de lecture, et les enchaîner évite d'y repasser quatre fois.

### Lot A — Sortir le texte de l'écran

1. **A1 — Exposer le format « édition »** : réparation, pas fonctionnalité. Une demi-journée.
2. **A2 — Feuille de style d'impression** : deux heures, et porte d'entrée naturelle vers A1.

### Lot B — Aborder un texte

3. **B1 — Temps de lecture** (ex-F1) : aucun changement de schéma.
4. **B2 — Au hasard** (ex-F4) : une heure, aucune dépendance.

### Lot C — Ancrage et annotation

5. **C1 — Liens vers un paragraphe** (ex-F3) : touche le rendu Markdown.
6. **C2 — Commentaire d'autrice** (ex-F2) : notes ancrées, donc **strictement après C1**,
   dont il consomme les identifiants de paragraphe.

### Lot D — Explorer

7. **D1 — Rétroliens « Mentionné dans »** : lit les wikilinks dans l'autre sens.
8. **D2 — Révisions récentes** : comble un trou du flux RSS.

### Lot E — Retour des lecteurs

9. **E1 — Signalement de coquille** (ex-F5) : dernier, car il ouvre le premier formulaire
   public du site.

---

## A1 — Exposer le format « édition »

**Ce n'est pas une idée, c'est une réparation.** `BookExportService::imposeBooklet()` réordonne
les pages d'un PDF pour qu'il puisse être imprimé recto-verso, plié en cahier et relié. La
route `download.book` accepte déjà `format=edition`. **Aucune vue ne le propose** — c'est la
seconde fonctionnalité orpheline du projet après les Chroniques, et la plus alignée avec une
marque qui fabrique des objets.

### Mise en œuvre

- Ajouter le choix aux boutons de téléchargement d'un livre, à côté de PDF et EPUB.
- Le nommer pour ce qu'il fait : « Livret à relier » plutôt que « edition ».
- Une phrase d'explication : impression recto-verso, pliage au centre, agrafage.

### Pièges

- L'imposition suppose un nombre de pages multiple de quatre ; le service complète déjà, mais
  vérifier le rendu sur un livre très court, où le remplissage domine.
- La génération est plus lourde qu'un PDF simple : mesurer avant d'exposer, et poser une
  limitation de débit si nécessaire.

### Tests

Le format est proposé sur la fiche d'un livre publié ; il produit un PDF non vide ; il reste
refusé pour un livre non publié.

---

## A2 — Feuille de style d'impression

**Ce que ça résout.** Aujourd'hui, un `Ctrl+P` sur un chapitre imprime la navigation, le champ
d'étoiles et le pied de page. Le texte, lui, se retrouve dans une colonne étroite.

### Mise en œuvre

- Un bloc `@media print` dans `app.css` : masquer navigation, arrière-plans, boutons de thème
  et de police ; rendre le texte pleine largeur en noir sur blanc.
- Faire apparaître l'adresse de la page en pied, pour que la sortie papier reste traçable.
- Les ancres de C1, une fois posées, ne doivent pas s'imprimer.

### Pièges

- Le champ d'étoiles est en arrière-plan : il faut le masquer explicitement, faute de quoi
  certaines imprimantes le rendent.
- Ne pas casser l'impression du back-office, qui partage la même feuille.

### Tests

Difficile à tester automatiquement — vérifier à la main via l'aperçu avant impression, sur un
chapitre et sur un article. Un test peut au moins confirmer que le bloc `@media print` figure
dans la feuille construite.

---

## B1 — Temps de lecture et longueur

**Ce que ça résout.** La seule question qu'un lecteur se pose avant d'ouvrir un texte long :
est-ce que je m'engage pour cinq minutes ou pour une heure. Aujourd'hui rien ne le dit.

### Portée

- Fiche de livre : total des chapitres publiés.
- Fiche de chapitre : le chapitre seul, affiché près du titre.
- Article d'encyclopédie : idem.
- Liste de la bibliothèque : discrètement sur chaque carte.

### Mise en œuvre

- Un `App\Support\ReadingTime` qui prend du Markdown et rend `['mots' => int, 'minutes' => int]`.
  Compter sur le texte **dépouillé de son balisage** — sinon les URL et les attributs HTML
  gonflent le total.
- Base de 200 mots/minute pour du français littéraire, arrondie à la minute supérieure, avec
  un plancher à « moins d'une minute ».
- **Ne pas stocker en base.** Le calcul est instantané et se désynchroniserait à chaque
  modification du texte. Si le coût devenait sensible, un cache par identifiant et date de
  modification suffirait — pas une colonne.

### Pièges

- Le compte doit ignorer le bandeau promotionnel et les notes de C2, qui ne sont pas le texte.
- Un chapitre vide doit afficher quelque chose de sensé, pas « 0 min ».

### Tests

Un texte connu donne un compte connu ; le balisage n'est pas compté ; un livre additionne ses
chapitres **publiés uniquement** ; un chapitre vide ne casse pas l'affichage.

---

## B2 — Au hasard

**Ce que ça résout.** Transforme la consultation en exploration. C'est le classique des wikis
pour une bonne raison, et neuf entrées suffisent amplement.

### Mise en œuvre

- `GET /encyclopedie/au-hasard` qui redirige vers une entrée publiée tirée au sort.
- **Redirection plutôt qu'affichage direct** : l'URL finale est partageable et la page reste
  en cache. Une page « aléatoire » servie sous une URL fixe serait mise en cache par le
  navigateur et cesserait d'être aléatoire.
- Un bouton discret sur l'index de l'encyclopédie.
- Limitation de débit, comme toute route qui interroge la base sans paramètre.

### Pièges

- **Ne tirer que parmi les articles publiés**, pas les catégories — une catégorie vide serait
  une déception.
- Encyclopédie vide : rediriger vers l'index plutôt que renvoyer une erreur.

### Tests

La route redirige vers une entrée publiée ; un brouillon n'est jamais tiré ; une catégorie
n'est jamais tirée ; une encyclopédie vide redirige sans erreur.

---

## C1 — Liens permanents vers un paragraphe

**Ce que ça résout.** Discuter d'un passage précis. Rien à sauvegarder, rien à expliquer : le
lien fonctionne ou ne fonctionne pas.

### Mise en œuvre

- Après le rendu Markdown, poser un identifiant sur chaque paragraphe de premier niveau.
- **L'identifiant doit être stable** : dérivé du contenu du paragraphe (empreinte courte) et
  non de sa position. Un identifiant positionnel casserait tous les liens partagés dès qu'un
  paragraphe est inséré plus haut — exactement le défaut que le gel des slugs évite ailleurs.
- Une ancre discrète au survol, qui copie l'adresse complète.
- Mise en évidence du paragraphe visé à l'arrivée, via `:target`.

### Pièges

- Deux paragraphes identiques dans un même texte produiraient la même empreinte : ajouter un
  suffixe d'occurrence.
- Traiter le rendu **après** CommonMark, pas avant : manipuler le Markdown source casserait
  les blocs de code et les citations.
- Ne poser d'ancres que sur les paragraphes, pas à l'intérieur des listes ou des tableaux.

### Tests

Un paragraphe reçoit un identifiant ; le même texte rendu deux fois donne le même identifiant ;
insérer un paragraphe en tête ne change pas ceux d'après ; deux paragraphes identiques
reçoivent des identifiants distincts.

---

## C2 — Commentaire d'autrice

**Ce que ça résout.** C'est ce que les gens qui suivent un univers en construction viennent
chercher, et cela vaut **plus** avec peu de contenu qu'avec beaucoup. Purement éditorial :
aucune illustration, aucune donnée à produire hors de ce que tu écris déjà.

### Portée

Des notes attachées à un chapitre ou à un article, révélées par un bouton « Note d'autrice ».
Masquées par défaut : le texte reste premier, la note est un bonus assumé.

### Mise en œuvre

**Forme retenue : notes ancrées à un paragraphe.** Décision de Naomi, contre ma recommandation
initiale d'une note unique par texte. Sa raison est décisive et tranche aussi la granularité :
elle n'aura que rarement besoin de commenter plus fin qu'un paragraphe, et l'ancrage facilite
l'association entre la note et ce qu'elle a écrit.

**C1 devient donc un prérequis** — les notes s'accrochent aux identifiants de paragraphe qu'il
produit. C'est ce qui fixe l'ordre : C1 avant C2, sans exception.

- Table `author_notes` : `commentable_type`, `commentable_id`, `paragraph_id`, `note_md`,
  `position`. Le morphisme couvre chapitres et articles d'un coup.
- **La note survit à la réécriture du paragraphe**, ou elle ne survit pas — à trancher. Les
  identifiants de F3 dérivent du contenu : réécrire un paragraphe change son identifiant et
  orpheline sa note. Deux options :
  - conserver la note orpheline et la signaler dans l'admin (« ce paragraphe a changé ») ;
  - la rattacher au paragraphe le plus proche par similarité.
  **Recommandation : la première.** Une note mal rattachée est pire qu'une note signalée comme
  détachée, et l'admin est seule à voir l'avertissement.
- Rédaction : dans l'écran d'édition, chaque paragraphe rendu porte un bouton « annoter ».
- Rendu par `MarkdownHelper`, donc les wikilinks fonctionnent dans les notes.

### Pièges

- **Exclure les notes du flux RSS et de la recherche** — ou décider explicitement de les
  inclure. Une note d'autrice qui remonte comme un résultat de recherche sans contexte
  désoriente.
- Le bouton doit fonctionner sans JavaScript si possible (`<details>`), sinon il faut un
  module de plus.
- Ne pas compter la note dans le temps de lecture de B1.

### Tests

Un texte sans note n'affiche rien ; une note s'affiche au bon paragraphe et rend son Markdown ;
la note n'apparaît ni dans le flux ni dans la recherche ; réécrire un paragraphe annoté laisse
la note détachée et signalée, sans la perdre ; supprimer un texte supprime ses notes.

---

## D1 — Rétroliens « Mentionné dans »

**Ce que ça résout.** Sur la page de Thalria, lister les chapitres et entrées qui la citent.
C'est l'inverse du graphe de wikilinks, écarté comme prématuré — et bien plus utile : cela
fonctionne dès la deuxième entrée, se lit sans visualisation, et c'est ce qui fait qu'un wiki
paraît vivant plutôt qu'arborescent.

### Mise en œuvre

- Balayer le Markdown des chapitres, articles et fragments à la recherche des `[[Terme]]`
  visant l'entrée courante, en tenant compte des libellés `[[Terme|libellé]]`.
- **Ne pas balayer à chaque affichage.** Une table `mentions` (`source_type`, `source_id`,
  `target_node_id`) alimentée à l'enregistrement du texte, comme la carte de chemins du lot 5.
  Sinon chaque page d'encyclopédie lirait tout le contenu du site.
- Une commande de reconstruction pour l'existant, et pour rattraper un désaccord.

### Pièges

- **Ne montrer que les mentions issues de contenu publié.** Un brouillon citant une entrée ne
  doit pas trahir son existence.
- Un texte peut citer la même entrée dix fois : dédoublonner par source.
- Les mentions doivent disparaître quand le texte source est supprimé ou dépublié.

### Tests

Un chapitre citant une entrée apparaît dans ses rétroliens ; un brouillon n'apparaît pas ;
retirer la citation retire le rétrolien ; dix citations dans un même texte donnent une seule
entrée ; supprimer le texte source nettoie la table.

---

## D2 — Révisions récentes

**Ce que ça résout.** Le flux RSS annonce les **nouveautés**, par date de parution. Une entrée
d'encyclopédie corrigée ou étoffée n'y apparaît jamais : personne ne sait donc qu'un texte a
été retravaillé. Les données existent déjà (`updated_at`), rien à saisir.

### Mise en œuvre

- Une page listant les contenus publiés modifiés récemment, toutes sections confondues.
- **Distinguer création et révision** : un texte paru hier n'est pas une révision. Comparer
  `updated_at` à `created_at` avec une marge, ou ne lister que les écarts significatifs.
- Envisager un flux Atom dédié, réutilisant la mécanique du lot 3.5 — mais seulement si la
  page trouve son public.

### Pièges

- **Une modification anodine ne doit pas remonter.** Corriger une virgule déclencherait
  autrement une notification aussi bruyante qu'une réécriture. Envisager un indicateur
  « révision notable » coché à la main, plutôt qu'une heuristique qui se trompera.
- `updated_at` bouge pour des raisons invisibles au lecteur — un changement de vignette, une
  correction de slug. À filtrer, ou à assumer.

### Tests

Un contenu modifié apparaît ; un contenu jamais modifié depuis sa création n'apparaît pas ; un
brouillon n'apparaît pas ; la page répond même sans aucune révision.

---
## E1 — Signalement de coquille

**Ce que ça résout.** Pour une autrice seule qui publie au fil de l'eau, les lecteurs
deviennent des relecteurs. Sans compte, sans commentaires publics, sans modération à tenir.

### Mise en œuvre

- Un lien discret en fin de texte, qui ouvre un formulaire pré-rempli avec la page d'origine
  et, si du texte est sélectionné, le passage concerné.
- Enregistrement en base (`typo_reports`) **et** notification par courriel. La base seule
  serait oubliée ; le courriel seul serait perdu.
- Un écran d'administration minimal : liste, marquer comme traité, supprimer.

### Pièges — c'est la fonctionnalité la plus exposée

- **C'est le premier formulaire public du site.** Toutes les protections écartées jusqu'ici
  parce que rien n'était ouvert redeviennent nécessaires : limitation de débit stricte, champ
  piège anti-robot, longueur maximale, et **aucun rendu HTML du contenu signalé**.
- Ne pas demander d'adresse de contact, ou la rendre facultative : une adresse collectée fait
  entrer le site dans la Loi 25, ce que le projet a précisément évité jusqu'ici.
- Le courriel part par `sendmail` depuis un hébergement mutualisé : prévoir que l'envoi puisse
  échouer sans faire échouer l'enregistrement.

### Tests

Un signalement est enregistré ; le contenu n'est jamais rendu en HTML ; la limitation de débit
s'applique ; le champ piège rejette silencieusement ; un échec d'envoi de courriel ne perd pas
le signalement ; l'écran d'administration est inaccessible sans session.

---

## Ce qui vaut pour tous

- **Une branche et un lot par fonctionnalité**, comme pour la passe Pint : le diff reste
  relisible et chaque livraison est réversible seule.
- **Tests avant déploiement**, et vérification en production après — la suite tourne sur
  SQLite, elle ne détecte ni les divergences de dialecte MySQL ni ce qui dépend du serveur.
- **F1, F2 et F3 touchent le rendu des textes** : les enchaîner évite de repasser trois fois
  sur les mêmes gabarits.
- Toute nouvelle section publique doit être **liée depuis la navigation** — le fil est resté
  invisible des mois durant faute de ce réflexe, et un test le vérifie désormais.
