# Guide de déploiement

Ce document explique comment configurer le déploiement continu pour ce projet Symfony avec GitHub Actions et Deployer.

## Prérequis

1. Un serveur avec accès SSH
2. PHP 8.2+ et les extensions requises
3. MySQL/MariaDB
4. Redis (optionnel, pour le cache et les files de messages)
5. Composer
6. Node.js et Yarn (pour les assets)

## Configuration du serveur

1. **Créer un utilisateur de déploiement** :
   ```bash
   adduser deploy
   usermod -aG www-data deploy
   ```

2. **Configurer les permissions** :
   ```bash
   chown -R deploy:www-data /var/www
   chmod -R 775 /var/www
   ```

3. **Configurer le serveur web** (Nginx/Apache) pour pointer vers `/var/www/symfony-sponsorship/current/public`

## Configuration des secrets GitHub

Ajoutez les secrets suivants dans les paramètres de votre dépôt GitHub (`Settings` > `Secrets` > `Actions`):

- `SSH_PRIVATE_KEY`: Clé privée SSH pour se connecter au serveur
- `SSH_KNOWN_HOSTS`: Sortie de `ssh-keyscan -t rsa your-server-ip`
- `DEPLOY_AUTH`: Informations d'authentification pour Deployer
- `SLACK_WEBHOOK_URL`: URL du webhook Slack pour les notifications (optionnel)
- `CODECOV_TOKEN`: Token pour l'upload des rapports de couverture de code (optionnel)

## Variables d'environnement

Créez un fichier `.env.prod` sur le serveur avec les variables d'environnement nécessaires :

```bash
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=votre_secret_ici

# Configuration de la base de données
DATABASE_URL="mysql://user:password@127.0.0.1:3306/db_name?serverVersion=8.0.32&charset=utf8mb4"

# Configuration du mailer
MAILER_DSN=smtp://localhost:1025

# Configuration Redis
REDIS_DSN=redis://localhost:6379

# Autres configurations...
```

## Déploiement manuel

Si nécessaire, vous pouvez effectuer un déploiement manuel avec la commande suivante :

```bash
ssh-add ~/.ssh/your_private_key
dep deploy production
```

## Surveillance

- **Logs d'application** : `/var/www/symfony-sponsorship/current/var/log/prod.log`
- **Supervisor** : Pour les workers et les tâches en arrière-plan
- **Monitoring** : Configurez un outil comme New Relic, Datadog ou simplement Uptime Robot

## Rollback

Pour annuler le dernier déploiement :

```bash
dep rollback production
```

## Dépannage

- **Problèmes de permissions** : Vérifiez que l'utilisateur `www-data` a les bonnes permissions
- **Échec des migrations** : Vérifiez les logs avec `tail -f var/log/prod.log`
- **Problèmes de cache** : Videz le cache manuellement avec `php bin/console cache:clear --env=prod`
