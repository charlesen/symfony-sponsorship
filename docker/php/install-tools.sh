#!/bin/bash

# Installer les outils nécessaires
apt-get update && apt-get install -y --no-install-recommends \
    default-mysql-client \
    iputils-ping \
    && rm -rf /var/lib/apt/lists/*

# Vérifier la connexion réseau vers la base de données
echo "Testing network connection to database..."
if ! ping -c 2 database >/dev/null 2>&1; then
    echo "Cannot reach database container. Network issue detected."
    exit 1
fi

# Vérifier la connexion à la base de données
echo "Testing database connection..."
if ! mysql -h database -u app -p'!ChangeMe!' -e "SELECT 1" symfony_sponsorship 2>/dev/null; then
    echo "Failed to connect to database. Check the following:"
    echo "1. Is the database container running?"
    echo "2. Are the credentials correct?"
    echo "3. Is the database 'symfony_sponsorship' created?"
    exit 1
fi

echo "Connection to database successful!"
