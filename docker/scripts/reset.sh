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

# Vérifier si Docker est en cours d'exécution
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        warning "Docker n'est pas en cours d'exécution. Démarrage de Docker..."
        if [[ "$OSTYPE" == "darwin"* ]]; then
            open -a Docker
        elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
            sudo systemctl start docker
        fi
        
        # Attendre que Docker démarre
        local max_attempts=30
        local attempt=0
        while ! docker info > /dev/null 2>&1; do
            if [ $attempt -ge $max_attempts ]; then
                error "Impossible de démarrer Docker. Veuillez démarrer Docker manuellement et réessayer."
            fi
            echo -n "."
            sleep 2
            ((attempt++))
        done
        echo ""
        success "Docker a été démarré avec succès."
    fi
}

# Nettoyer les conteneurs Docker
clean_containers() {
    info "Nettoyage des conteneurs Docker..."
    
    # Arrêter tous les conteneurs en cours d'exécution
    if [ "$(docker ps -q)" ]; then
        info "Arrêt des conteneurs en cours d'exécution..."
        docker stop $(docker ps -q) 2>/dev/null || warning "Aucun conteneur à arrêter."
    fi
    
    # Supprimer tous les conteneurs
    if [ "$(docker ps -a -q)" ]; then
        info "Suppression des conteneurs arrêtés..."
        docker rm -f $(docker ps -a -q) 2>/dev/null || warning "Aucun conteneur à supprimer."
    fi
    
    # Supprimer les conteneurs avec docker-compose
    if command -v docker-compose &> /dev/null; then
        info "Nettoyage avec docker-compose..."
        docker-compose down --remove-orphans --rmi local 2>/dev/null || warning "Aucun service docker-compose à arrêter."
    fi
    
    success "Nettoyage des conteneurs terminé."
}

# Nettoyer les volumes Docker
clean_volumes() {
    info "Nettoyage des volumes Docker..."
    
    # Supprimer les volumes orphelins
    local volumes=$(docker volume ls -qf dangling=true)
    if [ ! -z "$volumes" ]; then
        echo "$volumes" | xargs -r docker volume rm 2>/dev/null || warning "Impossible de supprimer certains volumes."
    fi
    
    # Supprimer les volumes spécifiques au projet
    local project_volumes=$(docker volume ls -q | grep -E '(symfony-sponsorship|mysql_data|redis_data|mailpit_data|xdebug_logs)')
    if [ ! -z "$project_volumes" ]; then
        echo "$project_volumes" | xargs -r docker volume rm 2>/dev/null || warning "Impossible de supprimer certains volumes du projet."
    fi
    
    success "Nettoyage des volumes terminé."
}

# Nettoyer les images Docker
clean_images() {
    info "Nettoyage des images Docker..."
    
    # Supprimer les images non utilisées
    docker image prune -a -f 2>/dev/null || warning "Impossible de nettoyer les images non utilisées."
    
    # Supprimer les images du projet
    local project_images=$(docker images -q --filter "reference=*symfony-sponsorship*")
    if [ ! -z "$project_images" ]; then
        docker rmi -f $project_images 2>/dev/null || warning "Impossible de supprimer certaines images du projet."
    fi
    
    success "Nettoyage des images terminé."
}

# Nettoyer les réseaux Docker
clean_networks() {
    info "Nettoyage des réseaux Docker..."
    
    # Supprimer les réseaux non utilisés
    docker network prune -f 2>/dev/null || warning "Impossible de nettoyer les réseaux non utilisés."
    
    # Supprimer les réseaux du projet
    local project_networks=$(docker network ls -q --filter "name=app_*")
    if [ ! -z "$project_networks" ]; then
        echo "$project_networks" | xargs -r docker network rm 2>/dev/null || warning "Impossible de supprimer certains réseaux du projet."
    fi
    
    success "Nettoyage des réseaux terminé."
}

# Nettoyer le projet
clean_project() {
    info "Nettoyage du projet..."
    
    # Sauvegarder le fichier .env.local s'il existe
    if [ -f "../../.env.local" ]; then
        info "Sauvegarde du fichier .env.local..."
        cp ../../.env.local ../../.env.local.bak
        success "Fichier .env.local sauvegardé dans .env.local.bak"
    fi
    
    # Supprimer les dossiers et fichiers générés
    local dirs_to_remove=(
        "../../var/cache" 
        "../../var/log" 
        "../../vendor" 
        "../../node_modules" 
        "../../public/build" 
        "../../public/bundles"
    )
    
    for dir in "${dirs_to_remove[@]}"; do
        if [ -d "$dir" ]; then
            info "Suppression de $dir..."
            rm -rf "$dir"
        fi
    done
    
    # Supprimer les fichiers de cache
    find ../../ -name "*.cache" -delete
    find ../../ -name "*.log" -delete
    
    # Réinitialiser les permissions
    if [ -d "../../var" ]; then
        info "Réinitialisation des permissions..."
        chmod -R 777 ../../var
    fi
    
    success "Nettoyage du projet terminé."
}

# Réinstaller les dépendances
reinstall_dependencies() {
    info "Réinstallation des dépendances..."
    
    # Installer les dépendances Composer
    if [ -f "../../composer.json" ]; then
        info "Installation des dépendances Composer..."
        docker run --rm -v "$(pwd)/../..":/app -w /app composer:latest install --ignore-platform-reqs --no-scripts
    fi
    
    # Installer les dépendances Node.js
    if [ -f "../../package.json" ]; then
        info "Installation des dépendances Node.js..."
        docker run --rm -v "$(pwd)/../..":/app -w /app node:18 yarn install --force
    fi
    
    success "Réinstallation des dépendances terminée."
}

# Afficher l'aide
show_help() {
    echo "Utilisation : $0 [options]"
    echo "Options :"
    echo "  -h, --help       Afficher ce message d'aide"
    echo "  -y, --yes        Exécuter sans confirmation"
    echo "  --no-docker      Ne pas nettoyer les ressources Docker"
    echo "  --no-project     Ne pas nettoyer le projet"
    echo "  --no-deps        Ne pas réinstaller les dépendances"
    exit 0
}

# Variables pour les options
AUTO_CONFIRM=false
CLEAN_DOCKER=true
CLEAN_PROJECT=true
REINSTALL_DEPS=true

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
        --no-docker)
            CLEAN_DOCKER=false
            shift
            ;;
        --no-project)
            CLEAN_PROJECT=false
            shift
            ;;
        --no-deps)
            REINSTALL_DEPS=false
            shift
            ;;
        *)
            error "Option non reconnue : $1"
            ;;
    esac
done

# Afficher l'en-tête
info "🔄 Réinitialisation complète de l'environnement de développement..."

echo "Cette opération est irréversible et effectuera les actions suivantes :"

if [ "$CLEAN_DOCKER" = true ]; then
    echo "- Arrêter et supprimer tous les conteneurs Docker"
    echo "- Supprimer tous les volumes Docker"
    echo "- Supprimer les images Docker non utilisées"
    echo "- Supprimer les réseaux Docker inutilisés"
fi

if [ "$CLEAN_PROJECT" = true ]; then
    echo "- Supprimer le cache de l'application"
    echo "- Supprimer le dossier vendor"
    echo "- Supprimer le dossier node_modules"
    echo "- Supprimer les fichiers générés"
    echo "- Sauvegarder le fichier .env.local"
fi

if [ "$REINSTALL_DEPS" = true ]; then
    echo "- Réinstaller les dépendances Composer"
    echo "- Réinstaller les dépendances Node.js"
fi

echo ""

# Demander confirmation si nécessaire
if [ "$AUTO_CONFIRM" = false ]; then
    read -p "⚠️  Êtes-vous sûr de vouloir continuer ? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        error "Opération annulée par l'utilisateur."
    fi
fi

# Vérifier Docker si nécessaire
if [ "$CLEAN_DOCKER" = true ]; then
    check_docker
    
    # Nettoyer Docker
    clean_containers
    clean_volumes
    clean_images
    clean_networks
fi

# Nettoyer le projet
if [ "$CLEAN_PROJECT" = true ]; then
    clean_project
fi

# Réinstaller les dépendances
if [ "$REINSTALL_DEPS" = true ]; then
    reinstall_dependencies
fi

# Message de fin
success "✅ Réinitialisation terminée avec succès !"

if [ -f "../../.env.local" ] && [ ! -f "../../.env" ]; then
    warning "Le fichier .env.local a été préservé. N'oubliez pas de le vérifier avant de redémarrer."
fi

echo -e "${YELLOW}💡 Utilisez ./docker/scripts/start.sh pour redémarrer l'environnement${NC}"

exit 0
