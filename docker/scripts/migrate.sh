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
    echo "Utilisation : $0 [options] [commande]"
    echo "Gestion des migrations de base de données"
    echo ""
    echo "Commandes :"
    echo "  status         Affiche l'état des migrations"
    echo "  list           Liste toutes les migrations disponibles"
    echo "  latest         Exécute les migrations en attente"
    echo "  up [n]         Exécute les n prochaines migrations"
    echo "  down [n]       Annule les n dernières migrations"
    echo "  diff           Affiche les différences avec la base de données"
    echo "  generate       Génère une nouvelle migration"
    echo "  execute [sql]  Exécute une requête SQL directement"
    echo "  dump [file]    Exporte la structure de la base de données"
    echo "  import [file]  Importe un fichier SQL dans la base de données"
    echo ""
    echo "Options :"
    echo "  -e, --env=ENV   Environnement (dev, test, prod)"
    echo "  -h, --help     Affiche ce message d'aide"
    echo ""
    echo "Exemples :"
    echo "  $0 status"
    echo "  $0 latest"
    echo "  $0 generate"
    echo "  $0 up 1"
    echo "  $0 down 1"
    echo "  $0 dump database.sql"
    echo "  $0 import database.sql"
    exit 0
}

# Variables par défaut
ENV="dev"
COMMAND="status"
PARAM=""

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
        -h|--help)
            show_help
            ;;
        -*)
            error "Option non reconnue : $1"
            ;;
        *)
            if [ "$COMMAND" = "status" ] || [ "$COMMAND" = "list" ] || [ "$COMMAND" = "latest" ] || [ "$COMMAND" = "diff" ] || [ "$COMMAND" = "generate" ]; then
                COMMAND="$1"
            else
                PARAM="$1"
            fi
            shift
            ;;
    esac
done

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

# Exécuter la commande appropriée
case "$COMMAND" in
    status)
        info "Vérification de l'état des migrations..."
        $DOCKER_COMPOSE exec -T $SERVICE $SYMFONY_CONSOLE doctrine:migrations:status --env=$ENV
        ;;
    list)
        info "Liste des migrations disponibles :"
        $DOCKER_COMPOSE exec -T $SERVICE $SYMFONY_CONSOLE doctrine:migrations:list --env=$ENV
        ;;
    latest)
        info "Exécution des migrations en attente..."
        $DOCKER_COMPOSE exec -T $SERVICE $SYMFONY_CONSOLE doctrine:migrations:migrate --no-interaction --allow-no-migration --env=$ENV
        success "Migrations terminées avec succès"
        ;;
    up)
        if [ -z "$PARAM" ]; then
            PARAM=1
        fi
        info "Exécution des $PARAM prochaine(s) migration(s)..."
        $DOCKER_COMPOSE exec -T $SERVICE $SYMFONY_CONSOLE doctrine:migrations:migrate $PARAM --no-interaction --allow-no-migration --env=$ENV
        success "Migrations effectuées avec succès"
        ;;
    down)
        if [ -z "$PARAM" ]; then
            PARAM=1
        fi
        warning "Annulation des $PARAM dernière(s) migration(s)..."
        $DOCKER_COMPOSE exec -T $SERVICE $SYMFONY_CONSOLE doctrine:migrations:execute --down --no-interaction --env=$ENV $PARAM
        success "Migrations annulées avec succès"
        ;;
    diff)
        info "Génération du diff des migrations..."
        $DOCKER_COMPOSE exec -T $SERVICE $SYMFONY_CONSOLE doctrine:migrations:diff --env=$ENV
        ;;
    generate)
        info "Génération d'une nouvelle migration..."
        $DOCKER_COMPOSE exec -T $SERVICE $SYMFONY_CONSOLE doctrine:migrations:generate --env=$ENV
        ;;
    execute)
        if [ -z "$PARAM" ]; then
            error "Veuillez spécifier une requête SQL à exécuter"
        fi
        info "Exécution de la requête SQL..."
        $DOCKER_COMPOSE exec -T $SERVICE $SYMFONY_CONSOLE dbal:run-sql "$PARAM" --env=$ENV
        ;;
    dump)
        local output_file="${PARAM:-database_$(date +%Y%m%d_%H%M%S).sql}"
        info "Exportation de la structure de la base de données vers $output_file..."
        $DOCKER_COMPOSE exec -T $SERVICE $SYMFONY_CONSOLE doctrine:schema:create:dump --output=$output_file --env=$ENV
        success "Exportation terminée avec succès : $output_file"
        ;;
    import)
        if [ -z "$PARAM" ] || [ ! -f "$PARAM" ]; then
            error "Veuillez spécifier un fichier SQL valide à importer"
        fi
        warning "Importation du fichier $PARAM dans la base de données..."
        $DOCKER_COMPOSE exec -T $SERVICE $SYMFONY_CONSOLE doctrine:database:import $PARAM --env=$ENV
        success "Importation terminée avec succès"
        ;;
    *)
        error "Commande non reconnue : $COMMAND. Utilisez -h pour l'aide."
        ;;
esac

exit 0
