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
}

# Vérifier si Docker est en cours d'exécution
if ! docker info > /dev/null 2>&1; then
    warning "Docker n'est pas en cours d'exécution. Aucun conteneur à arrêter."
    exit 0
fi

info "🛑 Arrêt de l'environnement de développement..."

# Arrêter les conteneurs
info "Arrêt des conteneurs Docker en cours..."
if docker-compose down; then
    success "Les conteneurs ont été arrêtés avec succès."
else
    error "Erreur lors de l'arrêt des conteneurs."
    exit 1
fi

# Nettoyer les ressources inutilisées
info "Nettoyage des ressources Docker inutilisées..."
docker system prune -f

# Vérifier s'il reste des conteneurs en cours d'exécution
if [ "$(docker ps -q)" ]; then
    warning "Certains conteneurs sont toujours en cours d'exécution :"
    docker ps
    
    echo ""
    read -p "Voulez-vous forcer l'arrêt de tous les conteneurs ? (o/N) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[OoYy]$ ]]; then
        info "Arrêt forcé de tous les conteneurs en cours d'exécution..."
        docker stop $(docker ps -q)
        success "Tous les conteneurs ont été arrêtés."
    fi
fi

# Vérifier les volumes orphelins
VOLUMES=$(docker volume ls -qf dangling=true)
if [ ! -z "$VOLUMES" ]; then
    info "Nettoyage des volumes orphelins..."
    docker volume rm $VOLUMES 2>/dev/null || true
fi

# Vérifier les réseaux orphelins
NETWORKS=$(docker network ls --filter 'name=app_*' -q)
if [ ! -z "$NETWORKS" ]; then
    info "Suppression des réseaux inutilisés..."
    docker network rm $NETWORKS 2>/dev/null || true
fi

success "✅ L'environnement a été arrêté et nettoyé avec succès !"

# Afficher l'utilisation des ressources système
info "Utilisation actuelle des ressources Docker :"
docker system df

exit 0
