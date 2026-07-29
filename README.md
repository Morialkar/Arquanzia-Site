# Arquanzia

Le site de l'univers d'Arquanzia, pour [Créations Sortilège](https://creations-sortilege.com) :
une encyclopédie, une bibliothèque de textes, un fil d'actualités et une collection de
fragments, servis par une application Laravel avec son propre back-office.

Tout le contenu est en lecture publique. La seule règle d'accès est le statut de publication
d'un livre, d'un chapitre ou d'un article.

## Sections

| Chemin | Contenu |
|---|---|
| `/` | Page d'accueil |
| `/fil` | Fil d'actualités |
| `/bibliotheque` | Livres et chapitres, avec export PDF et EPUB |
| `/encyclopedie` | Encyclopédie arborescente de l'univers, avec galeries d'images |
| `/fragments` | Fragments, notes et pièces courtes |
| `/recherche` | Recherche sur les titres |
| `/admin` | Back-office de rédaction |

## Pile technique

- **PHP 8.2+** et **Laravel 12**, gabarits Blade
- **MySQL** en production, **SQLite** possible en développement
- **Tailwind CSS**, **Vite** pour les assets
- `dompdf`, `fpdf` et `fpdi` pour les exports de livres et de chapitres
- `intervention/image` pour le traitement des médias

## Structure

```
arquanzia-backend/     Application Laravel
documentation/         Cadrage du projet, épics, plan de reprise
```

## Installation en développement

```bash
cd arquanzia-backend
composer install
cp .env.example .env
php artisan key:generate
```

Renseigner la connexion à la base dans `.env`, puis :

```bash
php artisan migrate
npm install
composer run dev
```

`composer run dev` lance en parallèle le serveur PHP, l'écoute de la file d'attente, les
journaux et Vite.

## Back-office

L'accès au back-office se fait par lien magique à usage unique, valable quinze minutes,
envoyé par courriel et restreint à une liste d'adresses autorisées. Il n'y a pas de compte
lecteur : aucune inscription, aucun mot de passe côté visiteur.

## État du projet

Le site a été refondu : il abandonne un modèle de portail client adossé à Shopify pour
devenir un site de contenu public. Cette refonte laisse des chantiers ouverts — correctifs,
suite de tests, chaîne de déploiement, dette technique — recensés et priorisés dans
[documentation/plan de reprise.md](documentation/plan%20de%20reprise.md).

Deux points à connaître avant de faire tourner le projet :

- Tailwind est encore chargé depuis un CDN plutôt que construit par Vite, bien que Vite soit
  configuré. Le pipeline d'assets n'est donc pas encore actif.
- Il n'y a pas encore de suite de tests.

## Licence

Sous [licence PolyForm Strict 1.0.0](LICENSE.md) : le code est consultable et utilisable à
des fins non commerciales, mais ne peut être ni modifié ni redistribué.

L'univers d'Arquanzia lui-même — textes, articles d'encyclopédie, illustrations — n'est
couvert par aucune licence de réutilisation et reste intégralement réservé.
