#!/bin/bash

# Définition des couleurs pour les messages
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher un message d'information
info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

# Fonction pour afficher un message de succès
success() {
    echo -e "${GREEN}[SUCCÈS]${NC} $1"
}

# Fonction pour afficher un avertissement
warning() {
    echo -e "${YELLOW}[ATTENTION]${NC} $1"
}

# Fonction pour afficher une erreur
error() {
    echo -e "${RED}[ERREUR]${NC} $1"
    exit 1
}

# Fonction pour afficher l'aide
show_help() {
    echo "Utilisation : $0 [options] [service] [commande]"
    echo "Exécute une commande dans un conteneur Docker"
    echo ""
    echo "Options :"
    echo "  -u, --user=UTILISATEUR   Exécuter la commande en tant qu'utilisateur spécifique"
    echo "  -e, --env=ENV           Définir une variable d'environnement (peut être utilisé plusieurs fois)
"
    echo "  -h, --help              Afficher ce message d'aide"
    echo ""
    echo "Services disponibles :"
    echo "  php       Conteneur PHP-FPM"
    echo "  nginx     Conteneur Nginx"
    echo "  mysql     Conteneur MySQL"
    echo "  redis     Conteneur Redis"
    echo "  mailpit   Conteneur Mailpit"
    echo "  app       Conteneur de l'application"
    echo ""
    echo "Exemples :"
    echo "  $0 php composer install"
    echo "  $0 -u www-data php bin/console cache:clear"
    echo "  $0 -e SYMFONY_ENV=dev php bin/console debug:container"
    echo "  $0 mysql mysql -u root -p"
    echo "  $0 redis redis-cli"
    exit 0
}

# Variables par défaut
USER=""
SERVICE=""
COMMAND=()
ENV_VARS=()

# Charger les variables d'environnement
if [ -f "../../.env" ]; then
    export $(grep -v '^#' ../../.env | xargs)
fi

# Définir le nom du projet s'il n'est pas défini
COMPOSE_PROJECT_NAME=${COMPOSE_PROJECT_NAME:-symfony}

# Fonction pour obtenir le nom du conteneur à partir du service
get_container_name() {
    local service=$1
    case $service in
        php) echo "${COMPOSE_PROJECT_NAME}-php-1" ;;
        nginx) echo "${COMPOSE_PROJECT_NAME}-nginx-1" ;;
        mysql) echo "${COMPOSE_PROJECT_NAME}-mysql-1" ;;
        redis) echo "${COMPOSE_PROJECT_NAME}-redis-1" ;;
        mailpit) echo "${COMPOSE_PROJECT_NAME}-mailpit-1" ;;
        app) echo "${COMPOSE_PROJECT_NAME}-app-1" ;;
        *) echo "" ;;
    esac
}

# Traiter les arguments
while [[ $# -gt 0 ]]; do
    case "$1" in
        -u|--user)
            if [ -n "$2" ] && [ ${2:0:1} != "-" ]; then
                USER="$2"
                shift 2
            else
                error "L'option $1 nécessite un argument"
            fi
            ;;
        --user=*)
            USER="${1#*=}"
            shift
            ;;
        -e|--env)
            if [ -n "$2" ] && [ ${2:0:1} != "-" ]; then
                ENV_VARS+=("-e" "$2")
                shift 2
            else
                error "L'option $1 nécessite un argument"
            fi
            ;;
        --env=*)
            ENV_VARS+=("-e" "${1#*=}")
            shift
            ;;
        -h|--help)
            show_help
            ;;
        -*)
            error "Option non reconnue : $1"
            ;;
        *)
            if [ -z "$SERVICE" ]; then
                SERVICE="$1"
            else
                COMMAND+=("$1")
            fi
            shift
            ;;
    esac
done

# Vérifier si un service a été spécifié
if [ -z "$SERVICE" ]; then
    error "Aucun service spécifié. Utilisez -h pour l'aide."
fi

# Obtenir le nom du conteneur
CONTAINER=$(get_container_name "$SERVICE")

if [ -z "$CONTAINER" ]; then
    error "Service inconnu : $SERVICE"
fi

# Vérifier si le conteneur existe et est en cours d'exécution
if ! docker ps --format '{{.Names}}' | grep -q "^$CONTAINER$"; then
    error "Le conteneur $CONTAINER n'existe pas ou n'est pas en cours d'exécution"
fi

# Si aucune commande n'est spécifiée, lancer un shell interactif
if [ ${#COMMAND[@]} -eq 0 ]; then
    info "Aucune commande spécifiée. Lancement d'un shell interactif..."
    if [ "$SERVICE" = "mysql" ]; then
        COMMAND=("mysql" "-u" "root" "-p$MYSQL_ROOT_PASSWORD")
    elif [ "$SERVICE" = "redis" ]; then
        COMMAND=("redis-cli")
    else
        COMMAND=("sh" "-c" "[ -e /bin/bash ] && /bin/bash || /bin/sh")
    fi
fi

# Construire la commande Docker
DOCKER_CMD=("docker" "exec" "-it")

# Ajouter les variables d'environnement
for env_var in "${ENV_VARS[@]}"; do
    DOCKER_CMD+=("$env_var")
done

# Ajouter l'utilisateur si spécifié
if [ -n "$USER" ]; then
    DOCKER_CMD+=("--user" "$USER")
fi

# Ajouter le conteneur et la commande
DOCKER_CMD+=("$CONTAINER" "${COMMAND[@]}")

# Afficher la commande exécutée
info "Exécution : ${DOCKER_CMD[*]}"

# Exécuter la commande
exec "${DOCKER_CMD[@]}"
