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
    echo "Gère les workers Symfony Messenger"
    echo ""
    echo "Options :"
    echo "  -h, --help          Affiche ce message d'aide"
    echo "  -e, --env=ENV       Environnement (dev, prod, test)"
    echo "  -v, --verbose       Afficher plus de détails"
    echo "  -l, --limit=LIMIT   Nombre maximum de messages à traiter avant de redémarrer"
    echo "  -m, --memory=LIMIT  Limite de mémoire en Mo avant redémarrage"
    echo "  -t, --time=LIMIT    Limite de temps en secondes avant redémarrage"
    echo "  -f, --force         Forcer l'arrêt des workers en cours d'exécution"
    echo ""
    echo "Commandes disponibles :"
    echo "  start [transport]   Démarrer un worker pour un transport spécifique (ou tous)"
    echo "  stop [transport]    Arrêter un worker (ou tous les workers)"
    echo "  restart [transport] Redémarrer un worker (ou tous les workers)"
    echo "  status             Afficher l'état des workers"
    echo "  list               Lister les transports disponibles"
    echo "  failed             Afficher les messages en échec"
    echo "  retry [id]         Réessayer un message en échec"
    echo ""
    echo "Exemples :"
    echo "  $0 start async           # Démarrer le worker pour le transport 'async'"
    echo "  $0 stop                 # Arrêter tous les workers"
    echo "  $0 status               # Afficher l'état des workers"
    echo "  $0 --env=prod start     # Démarrer en mode production"
    echo "  $0 -l 100 -m 128 start  # Limiter à 100 messages ou 128 Mo de RAM"
    exit 0
}

# Variables par défaut
ENV="dev"
VERBOSE=false
FORCE=false
LIMIT=""
MEMORY_LIMIT=""
TIME_LIMIT=""
COMMAND="status"
TRANSPORT=""
MESSAGE_ID=""

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
        -l|--limit)
            if [ -n "$2" ] && [ ${2:0:1} != "-" ]; then
                LIMIT="--limit=$2"
                shift 2
            else
                error "L'option $1 nécessite un argument"
            fi
            ;;
        --limit=*)
            LIMIT="--limit=${1#*=}"
            shift
            ;;
        -m|--memory)
            if [ -n "$2" ] && [ ${2:0:1} != "-" ]; then
                MEMORY_LIMIT="--memory-limit=$2"
                shift 2
            else
                error "L'option $1 nécessite un argument"
            fi
            ;;
        --memory=*)
            MEMORY_LIMIT="--memory-limit=${1#*=}"
            shift
            ;;
        -t|--time)
            if [ -n "$2" ] && [ ${2:0:1} != "-" ]; then
                TIME_LIMIT="--time-limit=$2"
                shift 2
            else
                error "L'option $1 nécessite un argument"
            fi
            ;;
        --time=*)
            TIME_LIMIT="--time-limit=${1#*=}"
            shift
            ;;
        -f|--force)
            FORCE=true
            shift
            ;;
        start|stop|restart|status|list|failed|retry)
            COMMAND="$1"
            # Si la commande est 'retry', le prochain argument est l'ID du message
            if [ "$1" = "retry" ] && [ -n "$2" ] && [ ${2:0:1} != "-" ]; then
                MESSAGE_ID="$2"
                shift 2
            # Sinon, le prochain argument est le transport
            elif [ "$1" != "status" ] && [ "$1" != "list" ] && [ "$1" != "failed" ] && [ -n "$2" ] && [ ${2:0:1} != "-" ]; then
                TRANSPORT="$2"
                shift 2
            else
                shift
            fi
            ;;
        -*)
            error "Option non reconnue : $1"
            ;;
        *)
            if [ -z "$TRANSPORT" ] && [ "$COMMAND" != "retry" ]; then
                TRANSPORT="$1"
            elif [ -n "$MESSAGE_ID" ]; then
                error "Trop d'arguments. Utilisez -h pour l'aide."
            elif [ "$COMMAND" = "retry" ] && [ -z "$MESSAGE_ID" ]; then
                MESSAGE_ID="$1"
            else
                error "Argument inattendu : $1"
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

# Fonction pour obtenir l'ID du conteneur PHP
get_php_container_id() {
    $DOCKER_COMPOSE ps -q $SERVICE
}

# Fonction pour lister les transports disponibles
list_transports() {
    info "Transports disponibles :"
    run_in_php $SYMFONY_CONSOLE debug:messenger --show-transports --env=$ENV
}

# Fonction pour démarrer un worker
start_worker() {
    local transport=${1:-async}
    local worker_name="worker_${transport}_${ENV}"
    
    # Vérifier si le worker est déjà en cours d'exécution
    if is_worker_running "$worker_name"; then
        warning "Le worker $worker_name est déjà en cours d'exécution"
        return 0
    fi
    
    info "Démarrage du worker pour le transport '$transport'..."
    
    # Construire la commande
    local cmd=("$SYMFONY_CONSOLE" "messenger:consume" "--env=$ENV")
    
    # Ajouter les options
    [ -n "$LIMIT" ] && cmd+=("$LIMIT")
    [ -n "$MEMORY_LIMIT" ] && cmd+=("$MEMORY_LIMIT")
    [ -n "$TIME_LIMIT" ] && cmd+=("$TIME_LIMIT")
    
    # Ajouter le transport
    cmd+=("$transport")
    
    # Démarrer en arrière-plan avec nohup
    run_in_php bash -c "nohup ${cmd[@]} > /var/log/workers/$worker_name.log 2>&1 & echo \$! > /var/run/$worker_name.pid"
    
    # Vérifier si le démarrage a réussi
    if [ $? -eq 0 ]; then
        success "Worker $worker_name démarré avec succès"
    else
        error "Échec du démarrage du worker $worker_name"
    fi
}

# Fonction pour arrêter un worker
stop_worker() {
    local transport=$1
    local worker_name
    
    if [ -z "$transport" ]; then
        # Arrêter tous les workers
        info "Arrêt de tous les workers..."
        run_in_php pkill -f "messenger:consume" || true
        run_in_php rm -f /var/run/worker_*_$ENV.pid
        success "Tous les workers ont été arrêtés"
    else
        # Arrêter un worker spécifique
        worker_name="worker_${transport}_$ENV"
        info "Arrêt du worker $worker_name..."
        
        if is_worker_running "$worker_name"; then
            run_in_php pkill -f "messenger:consume.*$transport" || true
            run_in_php rm -f "/var/run/$worker_name.pid"
            success "Worker $worker_name arrêté avec succès"
        else
            warning "Le worker $worker_name n'est pas en cours d'exécution"
        fi
    fi
}

# Fonction pour redémarrer un worker
restart_worker() {
    local transport=$1
    
    if [ -z "$transport" ]; then
        # Redémarrer tous les workers
        stop_worker
        start_worker
    else
        # Redémarrer un worker spécifique
        stop_worker "$transport"
        start_worker "$transport"
    fi
}

# Fonction pour vérifier si un worker est en cours d'exécution
is_worker_running() {
    local worker_name=$1
    run_in_php test -f "/var/run/$worker_name.pid" && run_in_php kill -0 "$(cat /var/run/$worker_name.pid)" 2>/dev/null
}

# Fonction pour afficher l'état des workers
show_status() {
    info "État des workers :"
    
    # Vérifier si des workers sont en cours d'exécution
    local running_workers
    running_workers=$(run_in_php ps -ef | grep "[m]essenger:consume" || true)
    
    if [ -z "$running_workers" ]; then
        warning "Aucun worker en cours d'exécution"
    else
        echo "Workers en cours d'exécution :"
        echo "$running_workers" | awk '{print "  " $0}'
    fi
    
    # Afficher les messages en échec
    local failed_count
    failed_count=$(run_in_php $SYMFONY_CONSOLE messenger:failed:count --env=$ENV 2>/dev/null || echo "0")
    
    if [ "$failed_count" -gt 0 ]; then
        warning "$failed_count message(s) en échec. Utilisez './docker/scripts/worker.sh failed' pour les voir."
    fi
}

# Fonction pour afficher les messages en échec
show_failed() {
    info "Messages en échec :"
    run_in_php $SYMFONY_CONSOLE messenger:failed:show --env=$ENV
}

# Fonction pour réessayer un message
retry_message() {
    local message_id=$1
    
    if [ -z "$message_id" ]; then
        error "Veuillez spécifier l'ID du message à réessayer"
    fi
    
    info "Nouvelle tentative pour le message $message_id..."
    run_in_php $SYMFONY_CONSOLE messenger:failed:retry "$message_id" --force --env=$ENV
    
    if [ $? -eq 0 ]; then
        success "Le message $message_id a été réessayé avec succès"
    else
        error "Échec de la nouvelle tentative pour le message $message_id"
    fi
}

# Créer les répertoires nécessaires
run_in_php mkdir -p /var/log/workers /var/run

# Exécuter la commande demandée
case "$COMMAND" in
    start)
        start_worker "$TRANSPORT"
        ;;
    stop)
        stop_worker "$TRANSPORT"
        ;;
    restart)
        restart_worker "$TRANSPORT"
        ;;
    status)
        show_status
        ;;
    list)
        list_transports
        ;;
    failed)
        show_failed
        ;;
    retry)
        retry_message "$MESSAGE_ID"
        ;;
    *)
        error "Commande non reconnue : $COMMAND"
        ;;
esac

exit 0
