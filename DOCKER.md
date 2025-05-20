# 🐳 Guide Docker - Symfony Sponsorship

Ce guide explique en détail l'environnement de développement Docker du projet.

## 📦 Services disponibles

| Service  | Description                    | Port local | URL d'accès                |
|----------|--------------------------------|------------|----------------------------|
| Nginx    | Serveur web                   | 8080       | http://localhost:8080      |
| PHP-FPM  | Processeur PHP                | 9000       | (interne)                  |
| MySQL    | Base de données               | 3306       | localhost:3306             |
| Adminer  | Interface BDD                 | 8081       | http://localhost:8081      |
| Mailpit  | Serveur mail de test          | 8025/1025  | http://localhost:8025      |
| Redis    | Cache, sessions et messages   | 6379       | localhost:6379             |

## 🚀 Démarrage rapide

1. **Configuration de l'environnement**
   ```bash
   # Créer votre fichier d'environnement local
   cp .env.local.example .env.local
   
   # Personnaliser les variables dans .env.local
   # - MYSQL_ROOT_PASSWORD (mot de passe root MySQL)
   # - MYSQL_PASSWORD (mot de passe utilisateur MySQL)
   # - APP_SECRET (générer une nouvelle valeur)
   ```

2. **Démarrer l'environnement**
   ```bash
   ./docker/scripts/start.sh
   ```

## Configuration des services

### Base de données
- **MySQL 8.0** avec les paramètres suivants :
  - Utilisateur : `${MYSQL_USER:-app}`
  - Base de données : `${MYSQL_DATABASE:-symfony_sponsorship}`
  - Port : 3306

### Cache et sessions
- **Redis** est utilisé pour :
  - Le cache applicatif
  - Les sessions PHP
  - Le système de messagerie

### Emails
- **Mailpit** est utilisé pour capturer les emails en développement
  - Interface web : http://localhost:8025
  - Port SMTP : 1025

## 🛠 Scripts utilitaires

### `start.sh`
- Démarre l'environnement complet
- Installe les dépendances PHP et Node.js
- Configure la base de données
- Compile les assets

```bash
./docker/scripts/start.sh
```

### `stop.sh`
- Arrête proprement tous les services
- Préserve les données des volumes

```bash
./docker/scripts/stop.sh
```

### `reset.sh`
- Réinitialise complètement l'environnement
- Supprime tous les volumes et le cache
- ⚠️ Action destructive, utilisez avec précaution

```bash
./docker/scripts/reset.sh
```

## ⚙️ Configuration des services

### PHP-FPM
- Version : 8.2
- Extensions installées :
  - pdo_mysql
  - zip
  - gd
  - intl
  - opcache
  - redis
  - xdebug (désactivé par défaut)
- Composer et Node.js/Yarn inclus

### Nginx
- Configuration optimisée pour Symfony
- Cache des assets statiques
- Endpoint de healthcheck : `/ping`

### MySQL
- Version : 8.0
- Charset : utf8mb4
- Optimisations de performance :
  - Buffer pool : 256M
  - Query cache : 32M
  - Slow query log activé

### Redis
- Persistence activée (appendonly)
- Utilisé pour :
  - Cache applicatif
  - Sessions PHP
  - Files d'attente

### Mailhog
- Interface web de gestion des mails
- Capture tous les emails sortants
- Parfait pour tester les notifications

## 🔧 Configurations courantes

### Activer Xdebug
1. Modifier dans `.env.local` :
   ```env
   XDEBUG_MODE=debug
   ```
2. Redémarrer les containers :
   ```bash
   docker compose restart php
   ```

### Changer les ports
1. Modifier les ports dans `.env.local` :
   ```env
   NGINX_PORT=8080
   ADMINER_PORT=8081
   ```
2. Redémarrer l'environnement :
   ```bash
   ./docker/scripts/stop.sh
   ./docker/scripts/start.sh
   ```

### Accéder aux logs
```bash
# Logs Nginx
docker compose logs nginx

# Logs PHP
docker compose logs php

# Logs MySQL
docker compose logs database
```

### Exécuter des commandes
```bash
# Commandes Symfony
docker compose exec php bin/console cache:clear

# Composer
docker compose exec php composer require package-name

# Yarn
docker compose exec php yarn add package-name
```

## 🔍 Monitoring

### Healthchecks
- Tous les services principaux sont surveillés
- Vérification automatique de la santé
- Points de contrôle :
  - MySQL : ping de la base
  - Redis : commande PING
  - Nginx : endpoint `/ping`

### Points d'accès aux outils
- **Adminer** : http://localhost:8081
  - Système : MySQL
  - Serveur : database
  - Utilisateur : défini dans .env
- **Mailhog** : http://localhost:8025
  - SMTP : localhost:1025
- **Redis** : 
  - CLI : `docker compose exec redis redis-cli`

## 🆘 Dépannage

### Problèmes courants

1. **Les ports sont déjà utilisés**
   - Modifier les ports dans `.env.local`
   - Vérifier les processus : `sudo lsof -i :8080`

2. **Problèmes de permissions**
   ```bash
   sudo chown -R $USER:$USER .
   ```

3. **Container qui ne démarre pas**
   ```bash
   docker compose logs [service]
   ```

4. **Base de données inaccessible**
   - Vérifier les logs : `docker compose logs database`
   - Réinitialiser : `./docker/scripts/reset.sh`
