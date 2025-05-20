# Symfony Sponsorship

Une application Symfony pour gérer un système de parrainage moderne avec authentification par magic link, missions personnalisables et intégration Brevo.

## Fonctionnalités principales
- Authentification sans mot de passe (magic link par email)
- Tableau de bord utilisateur avec missions, points, statut
- Système de parrainage (URL unique, suivi des filleuls)
- Missions personnalisables (BDD)
- Intégration Brevo (envoi de contacts/actions)
- Environnement de développement conteneurisé avec Docker

## Prérequis

- [Docker](https://www.docker.com/get-started) (version 20.10.0 ou supérieure)
- [Docker Compose](https://docs.docker.com/compose/install/) (version 1.29.0 ou supérieure)
- [Git](https://git-scm.com/)

## Installation avec Docker (recommandé)

1. **Cloner le dépôt**
```bash
git clone https://github.com/charlesen/symfony-sponsorship-bundle.git
cd symfony-sponsorship-bundle
```

2. **Configurer l'environnement**
```bash
# Copier le fichier d'exemple de configuration
cp .env.local.example .env.local

# Éditer le fichier .env.local selon vos besoins
# nano .env.local
```

3. **Démarrer l'environnement de développement**
```bash
# Rendre le script exécutable
chmod +x docker/scripts/dev.sh

# Lancer le script de développement
./docker/scripts/dev.sh
```

Le script va :
- Démarrer tous les services nécessaires (Nginx, PHP, MySQL, Redis, Mailpit, Adminer)
- Configurer la base de données
- Installer les dépendances Composer et Yarn
- Compiler les assets
- Exécuter les migrations de base de données
- Charger les données de test

4. **Accéder à l'application**
- Application : http://localhost:8080
- Adminer (Gestion BDD) : http://localhost:8081
- Mailpit (Visualisation des emails) : http://localhost:8025

## Installation manuelle (sans Docker)

1. **Cloner le dépôt**
```bash
git clone https://github.com/charlesen/symfony-sponsorship-bundle.git
cd symfony-sponsorship-bundle
```

2. **Installer les dépendances**
```bash
composer install
npm install
```

3. **Configurer l'environnement**
- Copier `.env` en `.env.local` et adapter les variables (BDD, mailer, Brevo...)

4. **Créer la base de données et les migrations**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. **Compiler les assets**
```bash
npm run dev
```

6. **Lancer le serveur Symfony**
```bash
symfony serve -d
```

## Services disponibles avec Docker

- **Nginx** : Serveur web (port 8080)
- **PHP 8.2** : Interpréteur PHP avec Xdebug
- **MySQL 8.0** : Base de données (port 3306)
- **Redis** : Cache et sessions (port 6379)
- **Mailpit** : Serveur SMTP de test avec interface web (ports 1025/8025)
- **Adminer** : Interface d'administration de base de données (port 8081)

## Commandes utiles

### Avec Docker

```bash
# Démarrer les conteneurs en arrière-plan
docker-compose up -d

# Arrêter les conteneurs
docker-compose down

# Voir les logs des conteneurs
docker-compose logs -f

# Accéder au conteneur PHP
docker-compose exec php bash

# Exécuter des commandes Symfony
docker-compose exec -u www-data php bin/console cache:clear

# Exécuter les tests
docker-compose exec -u www-data php php bin/phpunit
```

### Sans Docker

```bash
# Vider le cache
php bin/console cache:clear

# Exécuter les migrations
php bin/console doctrine:migrations:migrate

# Charger les données de test
php bin/console doctrine:fixtures:load

# Lancer les tests
php bin/phpunit
```

## Configuration

### Variables d'environnement importantes

- `APP_ENV` : Environnement d'exécution (dev, test, prod)
- `APP_SECRET` : Clé secrète pour les tokens CSRF
- `DATABASE_URL` : URL de connexion à la base de données
- `MAILER_DSN` : Configuration du serveur SMTP
- `BREVO_API_KEY` : Clé API pour l'intégration Brevo
- `REDIS_DSN` : URL de connexion à Redis

### Configuration de la base de données

L'application utilise MySQL 8.0 par défaut. Les paramètres de connexion peuvent être modifiés dans le fichier `.env.local`.

### Configuration du mailer

En développement, les emails sont capturés par Mailpit et peuvent être visualisés à l'adresse http://localhost:8025.

Pour la production, configurez un véritable serveur SMTP dans le fichier `.env.local`.

## Dépannage

### Problèmes de permissions

Si vous rencontrez des problèmes de permissions avec les dossiers `var/` ou `public/uploads`, exécutez :

```bash
# Sous Linux/macOS
chmod -R 777 var/
chmod -R 777 public/uploads

# Avec Docker
docker-compose exec php chown -R www-data:www-data var/
docker-compose exec php chown -R www-data:www-data public/uploads
```

### Problèmes de base de données

Si la base de données ne se crée pas correctement :

```bash
# Supprimer et recréer la base de données
docker-compose exec php bin/console doctrine:database:drop --force
docker-compose exec php bin/console doctrine:database:create

# Exécuter les migrations
docker-compose exec php bin/console doctrine:migrations:migrate --no-interaction
```

## Contribution

Les étapes de développement sont détaillées dans le fichier `DEVBOOK.md`.

1. Créez une branche pour votre fonctionnalité : `git checkout -b ma-nouvelle-fonctionnalite`
2. Committez vos modifications : `git commit -am 'Ajout d\'une nouvelle fonctionnalité'`
3. Poussez vers la branche : `git push origin ma-nouvelle-fonctionnalite`
4. Créez une Pull Request

## Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## Auteurs

- Charles-Édouard LAVIE (https://github.com/charlesen)

## Remerciements

- L'équipe de développement de Symfony
- La communauté Open Source
- Tous les contributeurs qui ont rendu ce projet possible
