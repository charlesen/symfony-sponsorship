# Symfony Sponsorship

Une application Symfony pour gérer un système de parrainage moderne avec authentification par magic link, missions personnalisables et intégration Brevo.

## Fonctionnalités principales
- Authentification sans mot de passe (magic link par email)
- Tableau de bord utilisateur avec missions, points, statut
- Système de parrainage (URL unique, suivi des filleuls)
- Missions personnalisables (BDD)
- Intégration Brevo (envoi de contacts/actions)

## Installation

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

## Contribution
Les étapes de développement sont détaillées dans le fichier `DEVBOOK.md`.

## Licence
MIT
