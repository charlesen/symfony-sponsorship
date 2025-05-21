#!/bin/bash
set -e

# Définir les couleurs pour les messages
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction pour afficher un message d'information
info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

# Fonction pour afficher un avertissement
warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Vérifier si Docker est en cours d'exécution
if ! docker info > /dev/null 2>&1; then
    echo "Docker n'est pas en cours d'exécution. Démarrage de Docker..."
    open -a Docker
    # Attendre que Docker démarre
    while ! docker info > /dev/null 2>&1; do
        echo "En attente du démarrage de Docker..."
        sleep 5
    done
fi

# Créer .env.local à partir de l'exemple s'il n'existe pas
if [ ! -f ".env.local" ] && [ -f ".env.local.example" ]; then
    info "Création du fichier .env.local à partir du fichier .env.local.example..."
    cp .env.local.example .env.local
    warning "Le fichier .env.local a été créé. Veuillez le configurer avec vos paramètres avant de continuer."
    exit 1
elif [ ! -f ".env.local" ]; then
    error "Le fichier .env.local n'existe pas et aucun fichier .env.local.example n'a été trouvé pour le créer."
    exit 1
fi

# Charger les variables d'environnement
if [ -f ".env.local" ]; then
    # Source le fichier .env.local
    set -a
    . ./.env.local
    set +a
fi

# Vérifier si les conteneurs sont déjà en cours d'exécution
if [ "$(docker ps -q -f name=nginx)" ] || [ "$(docker ps -q -f name=php)" ] || [ "$(docker ps -q -f name=mysql)" ] || [ "$(docker ps -q -f name=redis)" ] || [ "$(docker ps -q -f name=mailpit)" ]; then
    warning "Des conteneurs sont déjà en cours d'exécution."
    echo "Voulez-vous les redémarrer ? (O/n) "
    read -r response
    if [[ "$response" =~ ^([oO]|[oO][uU][iI]|[yY]|[yY][eE][sS])?$ ]]; then
        info "Arrêt des conteneurs existants..."
        docker-compose down
    else
        info "Utilisation des conteneurs existants."
        exit 0
    fi
fi

# Démarrer les conteneurs
info "Démarrage des conteneurs Docker..."
docker-compose up -d --build

# Attendre que les services soient prêts
info "Attente du démarrage des services..."

# Attendre que MySQL soit prêt
info "En attente du démarrage de MySQL..."
until docker-compose exec -T database mysqladmin ping -h"localhost" -u"root" -p"$MYSQL_ROOT_PASSWORD" --silent; do
    echo -n "."
    sleep 1
done

echo ""
info "MySQL est prêt !"

# Vérifier si la base de données existe
if ! docker-compose exec -T database mysql -u"root" -p"$MYSQL_ROOT_PASSWORD" -e "USE $MYSQL_DATABASE" 2>/dev/null; then
    info "Création de la base de données $MYSQL_DATABASE..."
    # Création de la base de données avec une syntaxe plus simple
    docker-compose exec -T database mysql -u"root" -p"$MYSQL_ROOT_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS \`$MYSQL_DATABASE\`;"

    # Définition du charset et collation après création
    docker-compose exec -T database mysql -u"root" -p"$MYSQL_ROOT_PASSWORD" -e "ALTER DATABASE \`$MYSQL_DATABASE\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

    info "Création de l'utilisateur $MYSQL_USER..."
    docker-compose exec -T database mysql -u"root" -p"$MYSQL_ROOT_PASSWORD" -e "CREATE USER IF NOT EXISTS '$MYSQL_USER'@'%' IDENTIFIED BY '$MYSQL_PASSWORD';"

    info "Attribution des droits sur la base de données..."
    docker-compose exec -T database mysql -u"root" -p"$MYSQL_ROOT_PASSWORD" -e "GRANT ALL PRIVILEGES ON \`$MYSQL_DATABASE\`.* TO '$MYSQL_USER'@'%';"
    docker-compose exec -T database mysql -u"root" -p"$MYSQL_ROOT_PASSWORD" -e "FLUSH PRIVILEGES;"

    info "Base de données et utilisateur créés avec succès !"
else
    info "La base de données $MYSQL_DATABASE existe déjà."
fi

# Installer les dépendances Composer
info "Installation des dépendances Composer..."
docker-compose exec -u www-data php composer install --optimize-autoloader --no-interaction

# Installer les dépendances Yarn
info "Installation des dépendances Yarn..."
docker-compose exec -u www-data php yarn install

# Compiler les assets
info "Compilation des assets..."
docker-compose exec -u www-data php yarn dev

# Vider le cache
info "Vidage du cache..."
docker-compose exec -u www-data php bin/console cache:clear --no-warmup
docker-compose exec -u www-data php bin/console cache:warmup

# Exécuter les migrations
info "Exécution des migrations..."
docker-compose exec -u www-data php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Charger les fixtures si nécessaire
info "Chargement des données de test..."
docker-compose exec -u www-data php bin/console doctrine:fixtures:load --no-interaction

# Afficher les informations de connexion
info "\n=========================================="
info "  ENVIRONNEMENT DE DÉVELOPPEMENT PRÊT"
info "=========================================="
info "Application : http://localhost:${NGINX_HTTP_PORT:-8080}"
info "Adminer (Gestion BDD) : http://localhost:${ADMINER_PORT:-8081}"
info "Mailpit (Emails) : http://localhost:${MAILHOG_HTTP_PORT:-8025}"
info "\nInformations de connexion à la base de données :"
info "- Hôte : localhost"
info "- Port : ${MYSQL_PORT:-3306}"
info "- Base de données : ${MYSQL_DATABASE:-symfony_sponsorship}"
info "- Utilisateur : ${MYSQL_USER:-app}"
info "- Mot de passe : ${MYSQL_PASSWORD:-app}"
info "\nCommandes utiles :"
info "- Arrêter les conteneurs : docker-compose down"
info "- Voir les logs : docker-compose logs -f"
info "- Accéder au conteneur PHP : docker-compose exec php bash"
info "- Lancer les tests : docker-compose exec -u www-data php php bin/phpunit"
info "=========================================="

# Afficher les logs des conteneurs
info "Affichage des logs (CTRL+C pour quitter)..."
docker-compose logs -f
