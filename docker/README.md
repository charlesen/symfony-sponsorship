# Configuration Docker pour Symfony Sponsorship

Ce dossier contient la configuration Docker pour le projet Symfony Sponsorship, offrant un environnement de développement complet et isolé.

## 🚀 Démarrage rapide

1. **Cloner le dépôt**
   ```bash
   git clone [url-du-depot] symfony-sponsorship
   cd symfony-sponsorship
   ```

2. **Configurer l'environnement**
   ```bash
   cp .env.local.example .env.local
   # Modifier les variables selon vos besoins
   ```

3. **Démarrer l'environnement**
   ```bash
   ./docker/scripts/start.sh
   ```

4. **Accéder à l'application**
   - Application : http://localhost:8080
   - Mailpit (emails) : http://localhost:8025
   - Adminer (BDD) : http://localhost:8081

## 🏗️ Structure des dossiers

```
docker/
├── mysql/               # Configuration MySQL
│   ├── conf.d/          # Fichiers de configuration personnalisés
│   └── initdb.d/        # Scripts d'initialisation de la base de données
├── nginx/               # Configuration Nginx
│   └── conf.d/          # Configuration des sites
├── php/                 # Configuration PHP
│   └── conf.d/          # Fichiers de configuration PHP
└── scripts/             # Scripts utilitaires
    ├── dev.sh           # Script de développement
    ├── reset.sh         # Réinitialisation complète
    ├── start.sh         # Démarrage des services
    └── stop.sh          # Arrêt des services
```

## 🛠️ Services disponibles

| Service   | Port | URL                             | Description                          |
|-----------|------|---------------------------------|--------------------------------------|
| Nginx     | 8080 | http://localhost:8080           | Serveur web                          |
| MySQL     | 3306 | -                               | Base de données                      |
| Redis     | 6379 | -                               | Cache et file d'attente             |
| Mailpit   | 8025 | http://localhost:8025           | Serveur mail de développement       |
| Adminer   | 8081 | http://localhost:8081           | Interface d'administration de BDD   |
| PHP-FPM   | 9000 | -                               | Interpréteur PHP                    |
| Xdebug    | 9003 | -                               | Débogage PHP                        |


## ⚙️ Configuration

### Variables d'environnement

Copiez et configurez le fichier `.env.local` :

```bash
cp .env.local.example .env.local
```

Variables importantes :
- `DATABASE_URL` - URL de connexion à la base de données
- `MAILER_DSN` - Configuration du serveur d'emails
- `APP_ENV` - Environnement (dev, prod, test)
- `APP_SECRET` - Clé secrète de l'application
- `MESSENGER_TRANSPORT_DSN` - Configuration de la file de messages

## 🚦 Scripts utilitaires

### 🔍 Vérifier l'environnement

Vérifiez que tous les prérequis sont installés et que l'environnement est correctement configuré :

```bash
./docker/scripts/check-env.sh
```

Ce script vérifie :
- Installation de Docker et Docker Compose
- Configuration des fichiers d'environnement
- Disponibilité des ports nécessaires
- Espace disque et mémoire disponibles
- État des conteneurs et volumes Docker
- Dépendances système requises
- Installation de PHP et extensions
- Outils de développement frontend (Node.js, npm, Yarn)

### 🏃‍♂️ Démarrer l'environnement

```bash
./docker/scripts/start.sh
```

Options :
- `--build` - Reconstruit les images
- `--no-cache` - Désactive le cache lors de la construction

### ⏹️ Arrêter l'environnement

```bash
./docker/scripts/stop.sh
```

### 🔄 Réinitialiser l'environnement

⚠️ **Attention** : Cette commande supprime toutes les données !

```bash
./docker/scripts/reset.sh
```

Options :
- `-y, --yes` - Exécuter sans confirmation
- `--no-docker` - Ne pas nettoyer les ressources Docker
- `--no-project` - Ne pas nettoyer le projet
- `--no-deps` - Ne pas réinstaller les dépendances

### 🗃️ Gestion des migrations de base de données

Gérez facilement les migrations de base de données :

```bash
# Afficher l'état des migrations
./docker/scripts/migrate.sh status

# Lister toutes les migrations disponibles
./docker/scripts/migrate.sh list

# Exécuter les migrations en attente
./docker/scripts/migrate.sh latest

# Exécuter une migration spécifique
./docker/scripts/migrate.sh up 1

# Annuler la dernière migration
./docker/scripts/migrate.sh down 1

# Générer une nouvelle migration
./docker/scripts/migrate.sh generate

# Exporter la structure de la base de données
./docker/scripts/migrate.sh dump database.sql

# Importer un fichier SQL
./docker/scripts/migrate.sh import database.sql
```

### 🌱 Gestion des fixtures

Chargez facilement des jeux de données de test :

```bash
# Charger toutes les fixtures (demande confirmation)
./docker/scripts/fixtures.sh all

# Charger des groupes spécifiques de fixtures
./docker/scripts/fixtures.sh user content

# Forcer le rechargement sans confirmation
./docker/scripts/fixtures.sh --force all

# Vider la base avant de charger les fixtures
./docker/scripts/fixtures.sh --purge all

# Ajouter des fixtures sans vider la base
./docker/scripts/fixtures.sh --append dev

# Spécifier l'environnement
./docker/scripts/fixtures.sh --env=test user
```

### 🧪 Exécution des tests

Exécutez les tests de l'application :

```bash
# Exécuter tous les tests
./docker/scripts/test.sh

# Exécuter uniquement les tests unitaires
./docker/scripts/test.sh --unit

# Exécuter les tests avec couverture de code
./docker/scripts/test.sh --coverage

# Générer un rapport JUnit
./docker/scripts/test.sh --report

# Exécuter en mode verbeux
./docker/scripts/test.sh -v

# Exécuter des suites spécifiques
./docker/scripts/test.sh unit functional

# Combiner les options
./docker/scripts/test.sh --coverage --report -v
```

### 📦 Gestion des dépendances

Gérez facilement les dépendances du projet :

```bash
# Installer toutes les dépendances
./docker/scripts/deps.sh

# Mettre à jour toutes les dépendances
./docker/scripts/deps.sh update

# Vérifier les vulnérabilités de sécurité
./docker/scripts/deps.sh audit

# Gérer les dépendances Composer
./docker/scripts/deps.sh composer require vendor/package
./docker/scripts/deps.sh composer update

# Gérer les dépendances Yarn
./docker/scripts/deps.sh yarn add package
./docker/scripts/deps.sh yarn upgrade

# Exécuter sans confirmation
./docker/scripts/deps.sh --yes audit

# Afficher plus de détails
./docker/scripts/deps.sh --verbose install
```

### 🗄️ Gestion du cache

Gérez facilement le cache de l'application :

```bash
# Vider le cache (développement par défaut)
./docker/scripts/cache.sh

# Vider le cache pour un environnement spécifique
./docker/scripts/cache.sh --env=prod

# Préchauffer le cache
./docker/scripts/cache.sh warmup

# Afficher l'état du cache
./docker/scripts/cache.sh status

# Vider et préchauffer le cache
./docker/scripts/cache.sh all

# Afficher plus de détails
./docker/scripts/cache.sh --verbose clear
```

### 🔧 Exécuter des commandes dans les conteneurs

Exécutez des commandes dans les conteneurs Docker :

```bash
# Exécuter une commande dans le conteneur PHP
./docker/scripts/exec.sh php composer install

# Exécuter une commande en tant qu'utilisateur spécifique
./docker/scripts/exec.sh -u www-data php bin/console cache:clear

# Définir des variables d'environnement
./docker/scripts/exec.sh -e SYMFONY_ENV=dev php bin/console debug:container

# Se connecter à MySQL
./docker/scripts/exec.sh mysql mysql -u root -p

# Utiliser redis-cli
./docker/scripts/exec.sh redis redis-cli

# Lancer un shell interactif dans un conteneur
./docker/scripts/exec.sh php
```

### 📜 Afficher les logs

Affichez les logs des conteneurs Docker :

```bash
# Afficher les logs d'un service spécifique (ex: nginx)
./docker/scripts/logs.sh nginx

# Suivre les logs en temps réel
./docker/scripts/logs.sh -f nginx

# Afficher les logs avec horodatages
./docker/scripts/logs.sh -t nginx

# Afficher les 50 dernières lignes des logs
./docker/scripts/logs.sh -n 50 nginx

# Afficher les logs de plusieurs services
./docker/scripts/logs.sh nginx php mysql

# Afficher les logs de tous les conteneurs
./docker/scripts/logs.sh -a
```

### 🧪 Script de développement

```bash
./docker/scripts/dev.sh
```

Ce script effectue les opérations suivantes :
1. Démarre les conteneurs Docker
2. Installe les dépendances Composer
3. Installe les dépendances Node.js
4. Compile les assets
5. Configure la base de données
6. Lance le serveur de développement

## 🔍 Dépannage

### Problèmes de connexion à la base de données

1. Vérifiez que MySQL est en cours d'exécution :
   ```bash
   docker compose ps
   ```

2. Vérifiez les logs de MySQL :
   ```bash
   docker compose logs mysql
   ```

3. Vérifiez les variables d'environnement dans `.env.local`

### Problèmes de permissions

Si vous rencontrez des erreurs de permissions :

```bash
sudo chown -R $USER:$USER .
chmod -R 755 .
```

### Réinitialisation complète

Pour tout réinitialiser (conteneurs, volumes, images) :

```bash
./docker/scripts/reset.sh
```

## ⚡ Optimisation des performances

### Xdebug

Pour activer Xdebug, ajoutez à `.env.local` :

```ini
XDEBUG_MODE=debug,develop
XDEBUG_CONFIG=client_host=host.docker.client
```

### Cache OPcache

Configuration par défaut dans `docker/php/conf.d/opcache.ini` :

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=1
opcache.revalidate_freq=0
opcache.jit=1255
opcache.jit_buffer_size=100M
```

### Redis

Configuration recommandée pour le cache et les sessions dans `config/packages/cache.yaml` :

```yaml
framework:
    cache:
        app: cache.adapter.redis
        default_redis_provider: 'redis://%env(REDIS_URL)%'
```

## 🔒 Sécurité

### Variables sensibles

Ne jamais commiter de données sensibles dans `.env.local`. Utilisez `.env.local` pour les configurations locales et `.env` pour les configurations par défaut.

### Mise à jour des images

Pour mettre à jour les images Docker :

```bash
docker compose pull
docker compose build --pull
```

## 📚 Ressources

- [Documentation Symfony](https://symfony.com/doc/current/index.html)
- [Documentation Docker](https://docs.docker.com/)
- [Documentation Nginx](https://nginx.org/en/docs/)
- [Documentation MySQL](https://dev.mysql.com/doc/)
- [Documentation Redis](https://redis.io/documentation)
