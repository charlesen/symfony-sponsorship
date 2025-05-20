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
    echo "Utilisation : $0 [options] [service...]"
    echo "Affiche les logs des services Docker"
    echo ""
    echo "Options :"
    echo "  -f, --follow       Suivre les logs en temps réel"
    echo "  -t, --timestamps  Afficher les horodatages"
    echo "  -n, --tail=N      Afficher les N dernières lignes (par défaut: 100)"
    echo "  -a, --all         Afficher tous les conteneurs"
    echo "  -h, --help        Afficher ce message d'aide"
    echo ""
    echo "Services disponibles :"
    echo "  php       Logs du conteneur PHP"
    echo "  nginx     Logs du serveur Nginx"
    echo "  mysql     Logs de la base de données MySQL"
    echo "  redis     Logs du serveur Redis"
    echo "  mailpit   Logs du serveur Mailpit"
    echo "  app       Logs de l'application Symfony"
    echo ""
    echo "Exemples :"
    echo "  $0 -f nginx php      # Suivre les logs de Nginx et PHP"
    echo "  $0 -n 50 mysql       # Afficher les 50 dernières lignes des logs MySQL"
    echo "  $0 -a               # Afficher les logs de tous les conteneurs"
    exit 0
}

# Variables par défaut
FOLLOW=""
TIMESTAMPS=""
TAIL=100
SERVICES=()
SHOW_ALL=false

# Traiter les arguments
while [[ $# -gt 0 ]]; do
    case "$1" in
        -f|--follow)
            FOLLOW="-f"
            shift
            ;;
        -t|--timestamps)
            TIMESTAMPS="--timestamps"
            shift
            ;;
        -n|--tail)
            if [ -n "$2" ] && [ ${2:0:1} != "-" ]; then
                TAIL="$2"
                shift 2
            else
                error "L'option $1 nécessite un argument"
            fi
            ;;
        -a|--all)
            SHOW_ALL=true
            shift
            ;;
        -h|--help)
            show_help
            ;;
        -*)
            error "Option non reconnue : $1"
            ;;
        *)
            SERVICES+=("$1")
            shift
            ;;
    esac
done

# Si aucun service n'est spécifié et que --all n'est pas utilisé, afficher l'aide
if [ ${#SERVICES[@]} -eq 0 ] && [ "$SHOW_ALL" = false ]; then
    show_help
fi

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

# Charger les variables d'environnement
if [ -f "../../.env" ]; then
    export $(grep -v '^#' ../../.env | xargs)
fi

# Définir le nom du projet s'il n'est pas défini
COMPOSE_PROJECT_NAME=${COMPOSE_PROJECT_NAME:-symfony}

# Afficher les logs pour tous les conteneurs si --all est spécifié
if [ "$SHOW_ALL" = true ]; then
    info "Affichage des logs pour tous les conteneurs..."
    docker compose logs $FOLLOW $TIMESTAMPS --tail=$TAIL
    exit 0
fi

# Afficher les logs pour les services spécifiés
for service in "${SERVICES[@]}"; do
    container=$(get_container_name "$service")
    
    if [ -z "$container" ]; then
        warning "Service inconnu : $service"
        continue
    fi
    
    # Vérifier si le conteneur existe et est en cours d'exécution
    if ! docker ps --format '{{.Names}}' | grep -q "^$container$"; then
        warning "Le conteneur $container n'existe pas ou n'est pas en cours d'exécution"
        continue
    fi
    
    info "=== Logs de $service ($container) ==="
    if [ "$FOLLOW" = "-f" ]; then
        docker logs $FOLLOW $TIMESTAMPS --tail=$TAIL "$container"
    else
        docker logs $TIMESTAMPS --tail=$TAIL "$container"
    fi
done

exit 0
