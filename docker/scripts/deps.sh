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
    echo "Gère les dépendances du projet (Composer et Yarn)"
    echo ""
    echo "Options :"
    echo "  -h, --help      Affiche ce message d'aide"
    echo "  -y, --yes       Répondre automatiquement 'oui' aux questions"
    echo "  -v, --verbose   Afficher plus de détails"
    echo ""
    echo "Commandes disponibles :"
    echo "  install         Installe toutes les dépendances (par défaut)"
    echo "  update          Met à jour les dépendances"
    echo "  audit           Vérifie les vulnérabilités de sécurité"
    echo "  composer        Gère les dépendances PHP avec Composer"
    echo "  yarn            Gère les dépendances JavaScript avec Yarn"
    echo "  all             Exécute toutes les commandes"
    echo ""
    echo "Exemples :"
    echo "  $0                    # Installe toutes les dépendances"
    echo "  $0 update             # Met à jour toutes les dépendances"
    echo "  $0 composer install   # Installe les dépendances PHP"
    echo "  $0 yarn add package   # Ajoute un package JavaScript"
    echo "  $0 -y audit          # Vérifie les vulnérabilités sans confirmation"
    exit 0
}

# Variables par défaut
AUTO_CONFIRM=false
VERBOSE=false
COMMANDS=()

# Traiter les arguments
while [[ $# -gt 0 ]]; do
    case "$1" in
        -h|--help)
            show_help
            ;;
        -y|--yes)
            AUTO_CONFIRM=true
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

# Si aucune commande n'est spécifiée, utiliser 'install' par défaut
if [ ${#COMMANDS[@]} -eq 0 ]; then
    COMMANDS=("install")
fi

# Charger les variables d'environnement
if [ -f "../../.env" ]; then
    export $(grep -v '^#' ../../.env | xargs)
fi

# Définir les variables par défaut
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

# Fonction pour exécuter une commande dans le conteneur PHP
run_in_php() {
    $DOCKER_COMPOSE exec -T $SERVICE "$@"
}

# Fonction pour exécuter une commande dans le conteneur Node
run_in_node() {
    $DOCKER_COMPOSE exec -T node "$@"
}

# Fonction pour demander une confirmation
confirm() {
    if [ "$AUTO_CONFIRM" = true ]; then
        return 0
    fi
    
    local message="${1:-Voulez-vous continuer ?} [y/N] "
    read -r -p "$message" response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        info "Opération annulée"
        exit 0
    fi
}

# Fonction pour installer les dépendances PHP
install_composer() {
    info "Installation des dépendances PHP..."
    run_in_php composer install --optimize-autoloader --no-interaction
    
    if [ $? -eq 0 ]; then
        success "Dépendances PHP installées avec succès"
    else
        error "Erreur lors de l'installation des dépendances PHP"
    fi
}

# Fonction pour mettre à jour les dépendances PHP
update_composer() {
    info "Mise à jour des dépendances PHP..."
    run_in_php composer update --optimize-autoloader --no-interaction
    
    if [ $? -eq 0 ]; then
        success "Dépendances PHP mises à jour avec succès"
    else
        error "Erreur lors de la mise à jour des dépendances PHP"
    fi
}

# Fonction pour installer les dépendances JavaScript
install_yarn() {
    info "Installation des dépendances JavaScript..."
    run_in_node yarn install --frozen-lockfile
    
    if [ $? -eq 0 ]; then
        success "Dépendances JavaScript installées avec succès"
    else
        error "Erreur lors de l'installation des dépendances JavaScript"
    fi
}

# Fonction pour mettre à jour les dépendances JavaScript
update_yarn() {
    info "Mise à jour des dépendances JavaScript..."
    run_in_node yarn upgrade
    
    if [ $? -eq 0 ]; then
        success "Dépendances JavaScript mises à jour avec succès"
    else
        error "Erreur lors de la mise à jour des dépendances JavaScript"
    fi
}

# Fonction pour vérifier les vulnérabilités de sécurité
audit_deps() {
    info "Vérification des vulnérabilités de sécurité..."
    
    # Vérifier les vulnérabilités Composer
    info "Vérification des vulnérabilités Composer..."
    run_in_php composer audit
    
    # Vérifier les vulnérabilités Yarn
    info "Vérification des vulnérabilités Yarn..."
    run_in_node yarn audit
}

# Traiter les commandes
for cmd in "${COMMANDS[@]}"; do
    case "$cmd" in
        install)
            install_composer
            install_yarn
            ;;
        update)
            update_composer
            update_yarn
            ;;
        audit)
            audit_deps
            ;;
        composer)
            shift
            info "Exécution de la commande Composer : $*"
            run_in_php composer "$@"
            shift $#
            ;;
        yarn)
            shift
            info "Exécution de la commande Yarn : $*"
            run_in_node yarn "$@"
            shift $#
            ;;
        all)
            install_composer
            install_yarn
            audit_deps
            ;;
        *)
            warning "Commande non reconnue : $cmd"
            ;;
    esac
done

exit 0
