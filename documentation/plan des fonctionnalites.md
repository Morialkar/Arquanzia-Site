# Plan des fonctionnalités

Cinq ajouts retenus après un premier tour écarté. Les contraintes qui ont guidé la sélection,
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

L'ordre suit le rapport effet/effort croissant en risque, chaque lot étant livrable seul :

1. **F1 — Temps de lecture** : socle de calcul réutilisé par F2 et F3, aucun changement de schéma.
2. **F4 — Au hasard** : une heure, aucune dépendance, gain immédiat d'exploration.
3. **F3 — Liens vers un paragraphe** : touche le rendu Markdown, donc à faire avant F2 qui s'y greffe.
4. **F2 — Commentaire d'autrice** : le plus différenciant. Notes ancrées, donc **strictement
   après F3**, dont il consomme les identifiants de paragraphe.
5. **F5 — Signalement de coquille** : dernier car il ouvre une surface d'envoi, à border.

---

## F1 — Temps de lecture et longueur

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

- Le compte doit ignorer le bandeau promotionnel et les notes de F2, qui ne sont pas le texte.
- Un chapitre vide doit afficher quelque chose de sensé, pas « 0 min ».

### Tests

Un texte connu donne un compte connu ; le balisage n'est pas compté ; un livre additionne ses
chapitres **publiés uniquement** ; un chapitre vide ne casse pas l'affichage.

---

## F2 — Commentaire d'autrice

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

**F3 devient donc un prérequis** — les notes s'accrochent aux identifiants de paragraphe qu'il
produit. C'est ce qui fixe l'ordre : F3 avant F2, sans exception.

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
- Ne pas compter la note dans le temps de lecture de F1.

### Tests

Un texte sans note n'affiche rien ; une note s'affiche au bon paragraphe et rend son Markdown ;
la note n'apparaît ni dans le flux ni dans la recherche ; réécrire un paragraphe annoté laisse
la note détachée et signalée, sans la perdre ; supprimer un texte supprime ses notes.

---

## F3 — Liens permanents vers un paragraphe

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

## F4 — Au hasard

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

## F5 — Signalement de coquille

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

## Ce qui vaut pour les cinq

- **Une branche et un lot par fonctionnalité**, comme pour la passe Pint : le diff reste
  relisible et chaque livraison est réversible seule.
- **Tests avant déploiement**, et vérification en production après — la suite tourne sur
  SQLite, elle ne détecte ni les divergences de dialecte MySQL ni ce qui dépend du serveur.
- **F1, F2 et F3 touchent le rendu des textes** : les enchaîner évite de repasser trois fois
  sur les mêmes gabarits.
- Toute nouvelle section publique doit être **liée depuis la navigation** — le fil est resté
  invisible des mois durant faute de ce réflexe, et un test le vérifie désormais.
