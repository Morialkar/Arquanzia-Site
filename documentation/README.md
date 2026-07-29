# Documentation

## Contenu

| Document | Statut |
|---|---|
| [plan de reprise.md](plan%20de%20reprise.md) | À jour — chantiers ouverts, priorisés |
| [Épics/Epic 6 RELOOKING.md](Épics/Epic%206%20RELOOKING.md) | À jour — direction artistique, c'est l'identité visuelle actuelle |
| [Épics/Epic 3 Contenu avancé.md](Épics/Epic%203%20Contenu%20avancé.md) | Partiellement périmé — voir ci-dessous |

**Epic 3** décrit la bibliothèque et l'encyclopédie telles qu'elles existent, y compris les
routes françaises encore en usage. En revanche tout ce qui concerne le palier « Lecteur », le
verrouillage de contenu, les CTA d'accès, les règles de bannissement et les pages
`/connexion`, `/mon-compte`, `/mon-acces` ne s'applique plus : il n'y a pas de compte lecteur
et tout le contenu est en lecture publique.

## Documents retirés

Le projet visait initialement un portail client adossé à Shopify : feed communautaire type
Patreon, paliers VIP et Lecteur, authentification par App Proxy Shopify, livraison de
chapitres par courriel, statut des imprimantes via Home Assistant. Cette direction a été
abandonnée au profit d'un site de contenu public.

Les documents qui la décrivaient ont été retirés, en deux générations de planification :

- `plan de projet.md` — découpage initial en neuf épics
- `architecture auth portable.md` — identité interne portable et sortie de Shopify
- `notes importantes.md` — contraintes App Proxy, intégrations, feuille de route
- `Épics/Epic 0 Cadrage & garde-fous.md` — cadrage du feed et des audiences
- `Épics/Epic 1 Fondation Technique.md` — socle technique, dans son cadrage d'origine
- `Épics/Epic 2 …App Proxy et Magic Link…md` — authentification Shopify
- `Épics/Epic 4 …LIVRAISON PAR COURRIEL.md` — confort de lecture et envoi par courriel
- `Épics/Epic 5 ENGAGEMENT, COMMUNAUTÉ…md` — notifications, profils, communauté

Ils restent consultables dans l'historique git, au commit `5d951ed` :

```bash
git show 5d951ed:"documentation/notes importantes.md"
```
