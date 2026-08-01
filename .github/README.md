# Intégration et déploiement continus

Deux workflows :

| Fichier | Rôle |
|---|---|
| `workflows/ci.yml` | Installe PHP 8.4, vérifie le style avec Pint et lance la suite de tests. Se déclenche sur les pull requests, manuellement, et lorsqu'il est appelé par le déploiement. |
| `workflows/deploy.yml` | Sur un push vers `main` : lance d'abord `ci.yml`, puis synchronise et met à jour la production. Déclenchable aussi à la main. |

Le déploiement ne part **que si les tests et le style passent** : `deploy` dépend de `test`.
Un seul déploiement s'exécute à la fois et il n'est jamais annulé en cours de route — couper
un `rsync` ou une migration à mi-parcours laisserait le serveur dans un état incohérent.

## Secrets à créer

Dans **Settings → Secrets and variables → Actions** du dépôt :

| Secret | Contenu | Exemple |
|---|---|---|
| `DEPLOY_SSH_KEY` | Clé privée SSH **dédiée au déploiement**, au format OpenSSH, en entier | `-----BEGIN OPENSSH PRIVATE KEY-----…` |
| `DEPLOY_KNOWN_HOSTS` | Empreinte du serveur, pour ne pas accepter n'importe quel hôte | sortie de `ssh-keyscan <hôte>` |
| `DEPLOY_HOST` | Hôte **SSH** du serveur — pas forcément le domaine du site | l'hôte de connexion |
| `DEPLOY_USER` | Utilisateur SSH | l'utilisateur cPanel |
| `DEPLOY_PATH` | Dossier distant du site | le dossier sur le serveur |
| `DEPLOY_PHP_BIN` | Binaire PHP du serveur | `/opt/alt/php84/usr/bin/php` |
| `DEPLOY_PORT` | Port SSH, **facultatif** — vaut 22 par défaut | `22` |

### Générer la clé de déploiement

Depuis le poste local, une clé dédiée — ne pas réutiliser une clé personnelle :

```bash
ssh-keygen -t ed25519 -f ~/.ssh/arquanzia_deploy -C "github-actions-deploy" -N ""
```

Puis autoriser la clé publique sur le serveur, et coller la **privée** dans `DEPLOY_SSH_KEY` :

```bash
ssh-copy-id -i ~/.ssh/arquanzia_deploy.pub arquanzia
```

Pour `DEPLOY_KNOWN_HOSTS` :

```bash
ssh-keyscan <hôte>
```

## Ce que le déploiement fait sur le serveur

1. `rsync -az --delete`, en excluant `.git`, `.github`, `.env`, `node_modules`, `vendor`,
   `storage`, `bootstrap/cache`, `tests` et `sync-watch.sh`.
2. `composer install --no-dev --optimize-autoloader`
3. `php artisan migrate --force`
4. `view:clear`, `cache:clear`, puis `config:cache` et `route:cache`
5. `chmod -R 775 storage bootstrap/cache`
6. Appel de `DEPLOY_HEALTH_URL` : le workflow échoue si le site ne répond pas 200.

Le `.env` du serveur n'est jamais touché, comme avec l'ancien script.

## Ce qui n'y est pas encore

- **Aucune étape de construction d'assets.** Tailwind est chargé depuis un CDN et `@vite`
  n'apparaît dans aucune vue : il n'y a rien à construire. Le job viendra avec le lot 5.1,
  qui rapatrie les assets. Il faudra alors un `package-lock.json`, absent aujourd'hui, sans
  quoi `npm ci` ne peut pas fonctionner.
- **Aucun retour arrière automatique.** Si le contrôle de santé échoue, le workflow échoue
  mais le serveur reste dans son nouvel état. Le retour arrière consiste à repousser le
  commit précédent.

## Transition depuis `sync-watch.sh`

L'ancien script synchronisait le poste local vers la production à chaque sauvegarde de
fichier, sans test ni retour arrière. Il reste dans le dépôt le temps de vérifier que la
chaîne fonctionne, puis sera supprimé. **Ne pas faire tourner les deux en même temps.**
