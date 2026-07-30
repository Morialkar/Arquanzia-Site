# Plan de reprise — Arquanzia

La refonte « site de contenu public » est versionnée sur `main`, pas encore poussée — le
dépôt distant doit changer (lot 3.3). Ce document couvre ce qui reste à faire, par lots
ordonnés. Les lots sont marqués au fur et à mesure.

---

## Lot 0 — Correctifs bloquants ✅ fait (commit `b849172`)

Sauf 0.5, qui est une variable d'environnement à changer sur le serveur — voir ci-dessous.

### 0.1 — `/admin/users/{id}` renvoie une erreur 500 — corrigé

`Admin\UserController::show()` ne passe que `$user` à la vue, mais
`resources/views/admin/users/show.blade.php` :

- appelle `route('admin.users.readonly')` ligne 43, `admin.users.ban` ligne 49,
  `admin.users.ban-handle` ligne 55, `admin.users.reset-handle-bans` ligne 62,
  `admin.users.entitlement` lignes 85 et 122, `admin.users.sync-order` ligne 150 —
  **sept noms de routes qui n'existent plus** ;
- lit `$entitlements['vip']` ligne 76 et `$entitlements['reader']` ligne 113 — **variable
  jamais définie** ;
- lit `$user->accessControl?->is_banned` ligne 31 — relation supprimée du modèle.

Correctif : réécrire la vue pour n'afficher que ce qui existe encore (identité, courriel,
pseudo, date de création, publications). Il ne reste aucune action de modération à proposer.

### 0.2 — La déconnexion admin ne déconnecte pas — corrigé

`Admin\AuthController::logout()` oublie `admin_email` et `admin_role`, mais laisse `user_id`
en session. Or `AdminAuth::handle()` re-promeut automatiquement tout `user_id` présent dans
l'allowlist. Résultat : après « déconnexion », un simple retour sur `/admin` réauthentifie.

Correctif : `$request->session()->invalidate()` + `regenerateToken()`.

### 0.3 — Fixation de session à la connexion — corrigé

`consumeMagicLink()` écrit dans la session sans la régénérer. Un identifiant de session
obtenu avant la connexion reste valide après. Correctif : `session()->regenerate()` avant
d'écrire `admin_email`.

### 0.4 — Fuite d'exceptions vers le public — corrigé

`DownloadController::downloadBook()` et `downloadChapter()` renvoient `$e->getMessage()` en
JSON avec un statut 500. Chemins serveur, requêtes SQL et détails d'implémentation exposés à
n'importe quel visiteur. Correctif : journaliser l'exception, renvoyer un message neutre.

### 0.5 — Vérifier `APP_DEBUG` sur le serveur — ⚠️ à faire par Naomi

Le `.env` local porte `APP_ENV=production` avec `APP_DEBUG=true` et `LOG_LEVEL=debug`. Le
`.env` n'est pas synchronisé, donc l'état du serveur est inconnu — **à vérifier en premier**.
En debug, une page d'erreur expose la configuration, les variables d'environnement et le code.

### 0.6 — Visibilité de l'encyclopédie — corrigé

Correction de l'audit initial : le diagnostic « invisible pour tout le monde » était faux.
Les pages publiques de l'encyclopédie ne filtraient **pas** sur `visibility` — seuls
`SearchController`, `SitemapController` et `HomepageController` le faisaient. Un nœud
« reader » était donc lisible par URL directe et listé dans l'index, mais absent des surfaces
de découverte. C'était une incohérence, et une fuite dans le sens inverse de celui annoncé.

Corrigé : l'énumération `visibility` (`public`/`reader`) est remplacée par un booléen
`is_published`, aligné sur les livres et chapitres. Les pages publiques le respectent
désormais, et un brouillon rend tout son sous-arbre inaccessible, URL directe comprise.

**Report des données** : les nœuds `public` deviennent publiés, les nœuds `reader` deviennent
des brouillons. Ces derniers étaient déjà hors recherche, sitemap et accueil, donc rien n'est
publié de nouveau — mais si certains d'entre eux devaient être visibles, il faut les repasser
en publié depuis le back-office, où ils portent un badge « Brouillon ».

---

## Lot 1 — Audit de sécurité

### 1.1 — Points vérifiés et sains

- **Magic links** : jeton de 64 caractères aléatoires, stocké en SHA-256, usage unique,
  expiration 15 minutes, limitation de débit par IP+courriel. Correct.
- **Injection SQL** : aucune requête brute, tout passe par Eloquent avec liaison de
  paramètres. Les `LIKE "%{$query}%"` sont paramétrés par le constructeur de requêtes.
- **Échappement des vues** : les 13 usages de `{!! !!}` portent tous sur du contenu rédigé
  par un administrateur (HTML d'encyclopédie, markdown de chapitre). Pas d'entrée visiteur
  rendue sans échappement.

### 1.2 — À corriger

| Gravité | Sujet | Détail |
|---|---|---|
| Élevée | `APP_DEBUG` en production | Voir 0.5 |
| Élevée | Fuite d'exceptions | Voir 0.4 |
| Élevée | Déconnexion inopérante | Voir 0.2 |
| Moyenne | **Téléversement de SVG brut** | `Admin\SettingsController::updateLogo()` accepte le SVG et le déplace tel quel dans `storage/app/public/logos/`, servi ensuite depuis la même origine que le site. Un SVG peut contenir `<script>`. Réservé aux admins, donc pas une porte ouverte, mais un XSS stocké permanent en cas de compte admin compromis. Correctif : assainir le SVG (retirer `script`, `on*`, `foreignObject`) ou servir les logos avec `Content-Disposition: attachment` / depuis un sous-domaine distinct. |
| Moyenne | Aucune CSP | Aggravée par le chargement de Tailwind et Google Fonts depuis des CDN tiers : le site exécute du JavaScript distant sur toutes ses pages. Poser une CSP suppose d'abord de rapatrier ces ressources (lot 5.1). |
| Moyenne | Cookies de session | `SESSION_ENCRYPT=false`, `SESSION_DOMAIN=null`, durée de vie 30 jours. Vérifier `secure`, `http_only`, `same_site=lax` dans `config/session.php` et forcer HTTPS. |
| Moyenne | Aucune limitation de débit publique | `/api/recherche` et `/api/reader-preferences` sont ouverts sans limite. Chaque appel de recherche déclenche trois `LIKE %…%` sans index : facilement saturable. Ajouter `throttle`. |
| Faible | Médias sans contrôle d'accès | `MediaController::show()` sert tout média à qui connaît son identifiant (UUID, donc non énumérable). À confirmer comme choix assumé, le README affirme l'inverse. |
| Faible | Traversée de chemin | `DownloadController::downloadImage()` compose des chemins depuis `$media->filename`. Valeur écrite par un admin, donc risque théorique — à borner par une validation de nom de fichier. |
| Faible | `EncyclopediaImportService` | Import de fichiers depuis une archive : à relire pour la traversée de chemin et les types de fichiers acceptés. |

### 1.3 — Surface réduite par le lot 4 ✅

Le retrait du socle WebAuthn/mot de passe (lot 4) supprime au passage la colonne `password`,
la table `webauthn_credentials` et le guard maison. Autant de surface d'attaque en moins, et
un seul chemin d'authentification restant à auditer : le magic link admin, déjà validé en 1.1.

---

## Lot 2 — Suite de tests ✅ fait (commit `92d946d`)

Aujourd'hui : deux stubs Laravel (`tests/Unit/ExampleTest.php`, `tests/Feature/ExampleTest.php`).
PHPUnit est configuré et jamais lancé.

Objectif : pas une couverture exhaustive, mais un filet qui rattrape les régressions du type
lot 0 — pages qui explosent, routes disparues, contenu privé qui fuit.

### 2.1 — Socle — en place

- Base SQLite en mémoire pour les tests (`phpunit.xml`), `RefreshDatabase`.
- Fabriques (`database/factories/`) pour `User`, `Book`, `Chapter`, `EncyclopediaNode`,
  `FragmentNode`, `FragmentItem`, `Post`. Aucune n'existe actuellement.

### 2.2 — Tests de fumée — en place, et rentables immédiatement

14 URL publiques et les 26 écrans du back-office, rendus pour de vrai. Ils ont trouvé
**trois pages de plus en erreur 500** dès le premier passage, toutes par accès à du code
supprimé et toutes invisibles à l'analyse statique :

- `/admin/analytics` lisait `$stats['active_readers']` et `$stats['active_vips']`, jamais
  fournis par le contrôleur. Les cartes de synthèse reposent désormais sur des mesures qui ont
  encore un sens : contenus publiés et pages vues sur 30 jours.
- `PageView::getReadingResumeRate()` interrogeait le modèle `ReadingProgress`, supprimé avec le
  login lecteur. Métrique retirée.
- Un import résiduel de `ModerationController` dans `routes/web.php`, classe supprimée depuis
  le lot 0.

Le filet a été éprouvé : en réintroduisant temporairement le bug de déconnexion du lot 0, les
deux tests concernés échouent, puis repassent une fois le correctif rétabli.

### 2.3 — Tests d'accès — en place

Le statut de publication est désormais la seule règle d'accès du site : c'est donc là qu'il
faut mettre l'essentiel du filet.

- Un livre non publié renvoie 404 ; un chapitre non publié renvoie 404 ; un chapitre
  « bientôt disponible » renvoie 404.
- Un livre non publié n'apparaît ni dans la bibliothèque, ni dans la recherche, ni dans le
  sitemap. Idem pour un chapitre non publié et un nœud d'encyclopédie en brouillon.
- Le téléchargement PDF/EPUB d'un livre ou d'un chapitre non publié renvoie 404.
- Une route admin sans session redirige vers `/admin/login`.
- Après déconnexion, `/admin` redirige vers la connexion (le test qui verrouille 0.2).

### 2.4 — Tests unitaires ciblés — en place

- `MarkdownHelper` : gras non fermé, italique sur plusieurs lignes, lignes d'astérisques
  seules, fins de ligne Windows. Ce sont exactement les rustines du parseur maison, et rien
  ne les protège.
- `MagicLink` : jeton expiré rejeté, jeton déjà utilisé rejeté, jeton inconnu rejeté.
- `BookExportService` : l'export PDF et EPUB produit un fichier non vide au bon type MIME.

### 2.5 — Contrainte levée

La base est distante, ce qui rendait toute vérification locale impossible. Avec SQLite en
mémoire, `php artisan test` tourne en local sans toucher à la production. C'est aussi le
premier moyen fiable de valider une migration avant de l'envoyer :

```bash
cd arquanzia-backend && php artisan test
```

### 2.6 — Reste à faire dans ce lot

- **Pint n'a jamais été lancé** : `./vendor/bin/pint --test` échoue sur 45 fichiers, soit
  presque tout le code. Le job de style du lot 3 échouerait donc dès le premier passage. Deux
  options : un commit dédié « formatage seul » avant de brancher la CI, ou retirer Pint du
  pipeline. Décision à prendre avec Naomi — 45 fichiers de diff, même purement cosmétique,
  ça se valide.
- Aucun test sur les écritures du back-office (création et modification de livres, chapitres,
  entrées d'encyclopédie, fragments). Les tests de fumée ne couvrent que les `GET`.

---

## Lot 3 — Pipeline de déploiement

### 3.1 — Ce qui remplace quoi

`sync-watch.sh` fait un `rsync --delete` depuis le poste local vers la production, à chaque
sauvegarde de fichier, sans build ni test ni possibilité de revenir en arrière. C'est le
maillon le plus fragile de la chaîne.

Cible : un déclenchement sur `push` vers `main`, qui teste, construit, puis déploie.

### 3.2 — Étapes du workflow

1. **Job `test`** : PHP 8.4, `composer install`, `php artisan test`, `./vendor/bin/pint --test`.
2. **Job `build`** : `npm ci && npm run build` (dépend du lot 5.1 — aujourd'hui il n'y a rien
   à construire, Tailwind vient d'un CDN).
3. **Job `deploy`** (uniquement sur `main`, si `test` et `build` passent) :
   - `rsync` vers le serveur via clé SSH stockée en secret ;
   - `composer install --no-dev --optimize-autoloader` ;
   - `php artisan migrate --force` ;
   - `php artisan view:clear && cache:clear && config:cache && route:cache`.

### 3.3 — Migration du dépôt vers GitHub

Décision prise : le dépôt passe sur GitHub, qui fournit les runners.

Étapes, dans l'ordre :

1. Créer un dépôt **privé** `Arquanzia` sur GitHub — à faire depuis ton compte, je ne crée
   pas de dépôt à ta place.
2. Basculer l'origine : `git remote set-url origin git@github.com:<compte>/Arquanzia.git`,
   en gardant Gitea en second distant (`git remote add gitea …`) le temps de vérifier que
   tout est bien arrivé.
3. Pousser les 16 commits de la refonte, aujourd'hui uniquement locaux.
4. Une fois la CI verte et un déploiement réussi, décider du sort du dépôt Gitea (archive ou
   suppression).

### 3.4 — Points à régler

- Secrets à créer dans les paramètres du dépôt GitHub : clé SSH de déploiement, hôte, chemin
  distant. La clé publique correspondante va dans `~/.ssh/authorized_keys` sur le serveur.
- Le dépôt étant privé, les minutes de runner sont comptées : garder les workflows courts et
  mettre en cache `vendor/` et `node_modules/`.
- Le déploiement doit exclure `.env`, `storage/`, `vendor/`, `node_modules/` — comme le
  script actuel.
- Prévoir un déclenchement manuel (`workflow_dispatch`) pour les urgences.
- Conserver `sync-watch.sh` le temps de la transition, puis le supprimer.

---

## Lot 4 — Retirer l'authentification lecteur ✅ fait (commit `c5c074b`)

Décision prise : **il n'y a pas de login lecteur.** Tout le contenu est en lecture publique,
et la seule règle d'accès est le statut de publication. Le seul compte du site est l'admin,
qui se connecte par magic link — mécanisme indépendant de tout ce qui suit.

### 4.1 — Socle WebAuthn / mot de passe — retiré

Posé mais totalement inerte : aucune route, aucun contrôleur, aucun appel à `Auth::login()`.
`ArquanziaGuard` référence même en commentaire un `RememberLoginService` qui n'existe pas.

À retirer :

- `app/Auth/ArquanziaGuard.php` et l'enregistrement du guard dans `AppServiceProvider::boot()`
- `config/webauthn.php`, `resources/js/vendor/webauthn/`
- `config/auth.php` : revenir à `driver => 'session'` et `driver => 'eloquent'`
- `User implements Authenticatable` et les sept méthodes `getAuth*` / `*RememberToken`
- Dépendance `laragear/webauthn` dans `composer.json`
- Migrations : `create_webauthn_credentials`, `add_password_to_users_table`,
  `create_remember_logins_table` — remplacées par des migrations de retrait, les tables
  existant déjà en production

### 4.2 — Préférences de lecture côté serveur — retirées

Découverte en vérifiant la portée de la décision : **ce stack est une boucle fermée.**
`ReaderPreferenceController` renvoie 401 à tout visiteur non connecté — donc à tous les
lecteurs, désormais. Et le front n'appelle même pas cet endpoint : `library/chapter.blade.php`
gère déjà la taille de police et la police dyslexique **entièrement en `localStorage`**
(lignes 115 à 140). Le comportement visible pour le lecteur est correct et le restera.

À retirer : `ReaderPreferenceController`, la route `/api/reader-preferences`, les colonnes
`reader_font` / `reader_font_size` / `theme_pref` sur `users` (le thème passe aussi par
`localStorage`), et les constantes désormais inutilisées de `App\Support\ReaderPreferences`.

À conserver : la conversion pourcentage ↔ pixels si les exports PDF/EPUB s'en servent — à
vérifier, `DownloadController` lit `font` et `size` directement depuis la requête.

### 4.3 — `ViewerResolver` et les paliers résiduels — retirés

`ViewerResolver` retourne un tableau de sept clés dont six sont des constantes. Les conditions
qui le consomment (`viewer_tier in ['reader','vip_reader']`, `is_banned`, `is_logged_in`) sont
mortes mais toujours écrites, dans `SearchController`, `EncyclopediaController` et les vues.

À retirer entièrement, en remplaçant les conditions par le seul filtre de publication. C'est
ce qui débloque 0.6.

### 4.4 — Deux pages cassées découvertes pendant le lot 4

Ni le contrôle des noms de routes du lot 0, ni la compilation des vues ne pouvaient les
détecter : ce sont des accès à des attributs disparus, invisibles avant l'exécution.

- **`/admin/users`** lisait `$user->entitlements['vip']`. `entitlements` n'existant plus,
  l'expression valait `null['vip']`, que le gestionnaire d'erreurs de Laravel transforme en
  `ErrorException` — donc une 500, comme `/admin/users/{id}`.
- **`/api/feed` et `/api/posts/{post}`** chargeaient les relations `reactions` et `comments` et
  appelaient `toFeedArray()` et `isAccessibleBy()`, tous supprimés.

L'API a été retirée plutôt que réparée : rien ne la consomme, le site est rendu côté serveur,
et son contrat reposait entièrement sur un paramètre `viewer=vip|reader`.

**Leçon pour le lot 2** : seuls des tests qui *rendent* réellement les pages attrapent cette
classe de défaut. Les tests de fumée du lot 2.2 sont donc la priorité, pas un accessoire.

### 4.5 — `posts.audience` retiré

La colonne `audience` (`public`/`connected`/`vip`/`reader`) ne filtrait plus rien depuis la
refonte, mais restait proposée au moment de la rédaction : un billet marqué « VIP »
s'affichait publiquement. Une promesse de confidentialité que le site ne tenait pas.

---

## Lot 5 — Dette technique

### 5.1 — Rapatrier les assets (le plus impactant)

Tailwind est chargé depuis `cdn.tailwindcss.com` dans les deux gabarits, avec la
configuration en ligne, alors que **Vite et Tailwind 4 sont installés et configurés**… et
que `@vite` n'apparaît dans aucune vue. Le pipeline d'assets est entièrement mort.

Conséquences : compilation du CSS dans le navigateur à chaque page, apparition brutale du
style non stylé, dépendance à un CDN tiers, et ce CDN n'est pas destiné à la production.
Idem pour Google Fonts alors que `public/fonts/` contient déjà les polices.

C'est aussi le préalable à la CSP (1.2) et au job `build` (3.2).

### 5.2 — Découper `app.blade.php`

1095 lignes : configuration Tailwind, thème clair/sombre, navigation desktop, navigation
mobile, JavaScript. À éclater en composants.

### 5.3 — Code mort

Retiré avec le lot 0, ces fichiers étant la cause même du 500 de la fiche utilisateur :
`resources/views/admin/moderation/`, `resources/views/admin/delivery/`,
`resources/views/components/access/` (six composants orphelins),
`app/Console/Commands/DispatchChapterDeliveries.php`, et le doublon jamais utilisé
`resources/views/components/admin/encyclopedia/form.blade.php`. Plus aucune vue du projet ne
référence une route inexistante — vérifié en comparant les `route('…')` du code à la liste
des routes déclarées.

Reste à traiter :

- `arquanzia-backend/.run-migration` : fichier vide versionné, alors que `sync-watch.sh`
  cherche `.run-migrations` (pluriel). L'un des deux est un vestige.
- Migrations mal datées : `2024_01_15_000002_create_printers_table.php` a été écrite bien
  après, et une migration `drop_printers` la suit immédiatement. Un déploiement à froid crée
  puis détruit la table. À fusionner ou supprimer.

### 5.4 — Remplacer le parseur Markdown maison

`MarkdownHelper` est un parseur ligne à ligne avec des rustines (« fermer le gras impair »,
« supprimer les lignes d'astérisques »). `league/commonmark` est déjà dans `vendor/`, exposé
par `Str::markdown()`. Conserver éventuellement un pré-traitement pour les particularités
Obsidian, mais déléguer le rendu.

### 5.5 — Recherche

`LIKE %…%` sur les titres uniquement, aucun index utilisable, trois requêtes par appel. Ne
cherche pas dans le contenu. Piste : index `FULLTEXT` MySQL, ou une table d'index dédiée
alimentée à l'enregistrement.

### 5.6 — Documentation

- `arquanzia-backend/README.md` décrit un produit qui n'existe plus : App Proxy Shopify,
  paliers VIP/Reader, webhooks `orders/paid`, commentaires, règles de bannissement. À
  réécrire intégralement.
- `documentation/notes importantes.md` et les sept Épics décrivent la trajectoire Shopify
  abandonnée. À archiver dans un dossier `historique/` plutôt qu'à supprimer — ils expliquent
  pourquoi le code ressemblait à ça.

### 5.7 — Accessibilité

`user-scalable=no` dans la balise viewport du gabarit public empêche le zoom sur mobile.

---

## Ordre suggéré

1. **Lot 0.1 à 0.5** — les correctifs bloquants, dans la journée. Vérifier `APP_DEBUG` sur le
   serveur en premier, c'est une variable à changer, pas du code à écrire.
2. **Lot 4** — le retrait de l'authentification lecteur et des paliers, qui règle 0.6 au
   passage. À faire avant les tests, sinon on écrit des tests sur du code à supprimer.
3. **Lot 2** — le socle de tests et les tests de fumée, qui verrouillent les lots 0 et 4.
4. **Lot 3** — la migration GitHub et le pipeline, une fois qu'il y a des tests à y faire
   tourner.
5. **Lot 5.1** — les assets, qui débloquent le job de build et la CSP.
6. **Lot 1** — le reste des correctifs de sécurité, CSP comprise.
7. **Lot 5** — le reste de la dette, au fil de l'eau.

À noter : les lots 0 et 4 touchent beaucoup les mêmes fichiers (`ViewerResolver`,
`SearchController`, les vues admin). Les enchaîner évite de repasser deux fois dessus.
