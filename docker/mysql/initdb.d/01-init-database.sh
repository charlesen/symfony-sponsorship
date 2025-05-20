#!/bin/bash
set -e

# Attendre que le serveur MySQL soit prêt
until mysqladmin ping -h"localhost" --silent; do
    echo "En attente du démarrage de MySQL..."
    sleep 2
done

# Exécuter les commandes SQL
mysql -u"root" -p"$MYSQL_ROOT_PASSWORD" <<-EOSQL
    -- Créer la base de données si elle n'existe pas
    CREATE DATABASE IF NOT EXISTS ${MYSQL_DATABASE} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    
    -- Créer l'utilisateur s'il n'existe pas
    CREATE USER IF NOT EXISTS '${MYSQL_USER}'@'%' IDENTIFIED BY '${MYSQL_PASSWORD}';
    
    -- Accorder tous les privilèges sur la base de données à l'utilisateur
    GRANT ALL PRIVILEGES ON ${MYSQL_DATABASE}.* TO '${MYSQL_USER}'@'%';
    
    -- Recharger les privilèges
    FLUSH PRIVILEGES;
    
    -- Afficher les bases de données pour vérification
    SHOW DATABASES;
EOSQL

echo "Base de données initialisée avec succès !"
