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
    echo "Utilisation : $0 [options] [groupe...]"
    echo "Charge les fixtures de la base de données"
    echo ""
    echo "Options :"
    echo "  -e, --env=ENV       Environnement (dev, test, prod)"
    echo "  -f, --force        Forcer le rechargement des fixtures"
    echo "  -a, --append       Ajouter les fixtures sans vider la base"
    echo "  -p, --purge        Vider la base avant de charger les fixtures"
    echo "  -h, --help         Affiche ce message d'aide"
    echo ""
    echo "Groupes disponibles :"
    echo "  all                Toutes les fixtures"
    echo "  dev                Données de développement"
    echo "  test               Données de test"
    echo "  prod               Données de production"
    echo "  user               Utilisateurs"
    echo "  content            Contenu de l'application"
    echo ""
    echo "Exemples :"
    echo "  $0 --env=dev all"
    echo "  $0 -e test user content"
    echo "  $0 --force --purge dev"
    exit 0
}

# Variables par défaut
ENV="dev"
FORCE=false
APPEND=false
PURGE=false
GROUPS=()

# Traiter les arguments
while [[ $# -gt 0 ]]; do
    case "$1" in
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
        -f|--force)
            FORCE=true
            shift
            ;;
        -a|--append)
            APPEND=true
            shift
            ;;
        -p|--purge)
            PURGE=true
            shift
            ;;
        -h|--help)
            show_help
            ;;
        -*)
            error "Option non reconnue : $1"
            ;;
        *)
            GROUPS+=("$1")
            shift
            ;;
    esac
done

# Si aucun groupe n'est spécifié, utiliser "all"
if [ ${#GROUPS[@]} -eq 0 ]; then
    GROUPS=("all")
fi

# Charger les variables d'environnement
if [ -f "../../.env" ]; then
    export $(grep -v '^#' ../../.env | xargs)
fi

# Définir les variables par défaut
SYMFONY_CONSOLE="php bin/console"
DOCKER_COMPOSE="docker compose"
SERVICE="php"

# Vérifier si Docker est disponible
if ! command -v docker &> /dev/null; then
    error "Docker n'est pas installé ou n'est pas dans le PATH"
fi

# Vérifier si le service est en cours d'exécution
if ! $DOCKER_COMPOSE ps $SERVICE --status running &> /dev/null; then
    error "Le service $SERVICE n'est pas en cours d'exécution. Utilisez './docker/scripts/start.sh' pour démarrer l'environnement."
fi

# Construire la commande de chargement des fixtures
CMD=("$DOCKER_COMPOSE" "exec" "-T" "$SERVICE" "$SYMFONY_CONSOLE" "doctrine:fixtures:load" "--env=$ENV")

# Ajouter les options
if [ "$FORCE" = true ]; then
    CMD+=("--force")
fi

if [ "$APPEND" = true ]; then
    CMD+=("--append")
fi

if [ "$PURGE" = true ]; then
    CMD+=("--purge-with-truncate")
fi

# Ajouter les groupes
for group in "${GROUPS[@]}"; do
    CMD+=("--group=$group")
done

# Afficher la commande exécutée
info "Exécution : ${CMD[*]}"

# Demander confirmation si on n'est pas en mode force
if [ "$FORCE" = false ]; then
    warning "Cette opération va modifier la base de données. Voulez-vous continuer ? [y/N]"
    read -r response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        info "Opération annulée"
        exit 0
    fi
fi

# Exécuter la commande
"${CMD[@]}"

if [ $? -eq 0 ]; then
    success "Les fixtures ont été chargées avec succès"
else
    error "Erreur lors du chargement des fixtures"
fi

exit 0
