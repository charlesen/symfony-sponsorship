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
    echo "Utilisation : $0 [options] [commandes...]"
    echo "Gère le cache de l'application"
    echo ""
    echo "Options :"
    echo "  -h, --help      Affiche ce message d'aide"
    echo "  -e, --env=ENV   Environnement (dev, prod, test)"
    echo "  -v, --verbose   Afficher plus de détails"
    echo ""
    echo "Commandes disponibles :"
    echo "  clear           Vide le cache (par défaut)"
    echo "  warmup          Préchauffe le cache"
    echo "  status          Affiche l'état du cache"
    echo "  all             Vide et préchauffe le cache"
    echo ""
    echo "Exemples :"
    echo "  $0                    # Vide le cache"
    echo "  $0 warmup            # Préchauffe le cache"
    echo "  $0 all               # Vide et préchauffe le cache"
    echo "  $0 --env=prod clear  # Vide le cache en production"
    exit 0
}

# Variables par défaut
ENV="dev"
VERBOSE=false
COMMANDS=()

# Traiter les arguments
while [[ $# -gt 0 ]]; do
    case "$1" in
        -h|--help)
            show_help
            ;;
        -e|--env)
            if [ -n "$2" ] && [ ${2:0:1} != "-" ]; then
                ENV="$2"
                shift 2
            else
                error "L'option $1 nécessite un argument"
            fi
            ;;
        --env=*)
            ENV="${1#*=}"
            shift
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        -*)
            error "Option non reconnue : $1"
            ;;
        *)
            COMMANDS+=("$1")
            shift
            ;;
    esac
done

# Si aucune commande n'est spécifiée, utiliser 'clear' par défaut
if [ ${#COMMANDS[@]} -eq 0 ]; then
    COMMANDS=("clear")
fi

# Charger les variables d'environnement
if [ -f "../../.env" ]; then
    export $(grep -v '^#' ../../.env | xargs)
fi

# Définir les variables par défaut
DOCKER_COMPOSE="docker compose"
SERVICE="php"
SYMFONY_CONSOLE="php bin/console"

# Vérifier si Docker est disponible
if ! command -v docker &> /dev/null; then
    error "Docker n'est pas installé ou n'est pas dans le PATH"
fi

# Vérifier si le service est en cours d'exécution
if ! $DOCKER_COMPOSE ps $SERVICE --status running &> /dev/null; then
    error "Le service $SERVICE n'est pas en cours d'exécution. Utilisez './docker/scripts/start.sh' pour démarrer l'environnement."
fi

# Fonction pour exécuter une commande dans le conteneur PHP
run_in_php() {
    $DOCKER_COMPOSE exec -T $SERVICE "$@"
}

# Fonction pour vider le cache
clear_cache() {
    info "Vidage du cache pour l'environnement $ENV..."
    
    # Vider le cache Symfony
    run_in_php $SYMFONY_CONSOLE cache:clear --no-warmup --env=$ENV
    
    # Vider le cache Redis si configuré
    if [ "$ENV" != "test" ] && [ -n "$REDIS_URL" ]; then
        info "Vidage du cache Redis..."
        run_in_php $SYMFONY_CONSOLE redis:flushall --no-interaction --env=$ENV
    fi
    
    # Vider le cache système
    if [ "$ENV" != "prod" ]; then
        run_in_php rm -rf var/cache/$ENV/*
    fi
    
    success "Cache vidé avec succès pour l'environnement $ENV"
}

# Fonction pour préchauffer le cache
warmup_cache() {
    info "Préchauffage du cache pour l'environnement $ENV..."
    
    # Créer le répertoire de cache s'il n'existe pas
    run_in_php mkdir -p var/cache/$ENV
    
    # Définir les permissions
    run_in_php chmod -R 777 var/cache
    
    # Préchauffer le cache Symfony
    run_in_php $SYMFONY_CONSOLE cache:warmup --env=$ENV
    
    success "Cache préchauffé avec succès pour l'environnement $ENV"
}

# Fonction pour afficher l'état du cache
cache_status() {
    info "État du cache pour l'environnement $ENV :"
    
    # Afficher les informations sur le cache
    run_in_php $SYMFONY_CONSOLE about --env=$ENV | grep -E 'Cache|OPcache|APCu|Redis'
    
    # Afficher l'utilisation du disque
    echo ""
    info "Utilisation du disque pour le cache :"
    run_in_php du -sh var/cache/$ENV
    
    # Afficher les informations Redis si configuré
    if [ -n "$REDIS_URL" ]; then
        echo ""
        info "Informations Redis :"
        run_in_php $SYMFONY_CONSOLE redis:info --no-interaction --env=$ENV
    fi
}

# Traiter les commandes
for cmd in "${COMMANDS[@]}"; do
    case "$cmd" in
        clear)
            clear_cache
            ;;
        warmup)
            warmup_cache
            ;;
        status)
            cache_status
            ;;
        all)
            clear_cache
            warmup_cache
            ;;
        *)
            warning "Commande non reconnue : $cmd"
            ;;
    esac
done

exit 0
