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

# Vérifier si une commande existe
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Afficher l'en-tête
echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Vérification de l'environnement   ${NC}"
echo -e "${BLUE}========================================${NC}"

# Vérification de Docker
info "Vérification de Docker..."
if command_exists docker; then
    success "Docker est installé ($(docker --version | awk '{print $3}'))"
    
    # Vérifier si Docker est en cours d'exécution
    if docker info >/dev/null 2>&1; then
        success "Docker est en cours d'exécution"
    else
        error "Docker n'est pas en cours d'exécution. Veuillez démarrer Docker et réessayer."
    fi
else
    error "Docker n'est pas installé. Veuillez installer Docker avant de continuer."
fi

# Vérification de Docker Compose
info "Vérification de Docker Compose..."
if command_exists docker-compose; then
    success "Docker Compose est installé ($(docker-compose --version | awk '{print $3}'))"
elif docker compose version >/dev/null 2>&1; then
    success "Docker Compose (plugin) est disponible"
else
    error "Docker Compose n'est pas installé. Veuillez installer Docker Compose avant de continuer."
fi

# Vérification des fichiers de configuration
info "Vérification des fichiers de configuration..."

# Vérifier si .env.local existe
if [ -f "../../.env" ]; then
    success "Fichier .env trouvé"
    
    # Vérifier les variables importantes
    required_vars=("APP_ENV" "APP_SECRET" "DATABASE_URL" "MAILER_DSN")
    missing_vars=()
    
    for var in "${required_vars[@]}"; do
        if ! grep -q "^$var=" "../../.env"; then
            missing_vars+=("$var")
        fi
    done
    
    if [ ${#missing_vars[@]} -eq 0 ]; then
        success "Toutes les variables d'environnement requises sont présentes"
    else
        warning "Variables manquantes dans .env: ${missing_vars[*]}"
    fi
else
    warning "Fichier .env introuvable. Créez-le à partir de .env.local.example"
fi

# Vérifier si .env.local existe
if [ -f "../../.env.local" ]; then
    success "Fichier .env.local trouvé"
else
    warning "Fichier .env.local introuvable. Créez-le à partir de .env.local.example"
fi

# Vérification des ports
info "Vérification des ports..."

# Liste des ports à vérifier
declare -A ports=(
    ["Nginx"]=8080
    ["MySQL"]=3306
    ["Redis"]=6379
    ["Mailpit"]=8025
    ["Adminer"]=8081
)

# Vérifier chaque port
for service in "${!ports[@]}"; do
    port=${ports[$service]}
    if lsof -i :$port >/dev/null 2>&1; then
        warning "Le port $port ($service) est déjà utilisé par un autre processus"
    else
        success "Le port $port ($service) est disponible"
    fi
done

# Vérification de l'espace disque
info "Vérification de l'espace disque..."
disk_space=$(df -h . | awk 'NR==2 {print $4}')
echo "Espace disque disponible : $disk_space"

# Vérification de la mémoire disponible
info "Vérification de la mémoire disponible..."
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    total_mem=$(sysctl -n hw.memsize)
    total_mem_gb=$(echo "scale=2; $total_mem/1024/1024/1024" | bc)
    echo "Mémoire totale : ${total_mem_gb}GB"
elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
    # Linux
    total_mem=$(free -h | awk '/^Mem:/ {print $2}')
    echo "Mémoire totale : $total_mem"
fi

# Vérification des conteneurs en cours d'exécution
info "Vérification des conteneurs en cours d'exécution..."
running_containers=$(docker ps --format '{{.Names}}' | wc -l)
if [ "$running_containers" -gt 0 ]; then
    warning "$running_containers conteneur(s) en cours d'exécution :"
    docker ps --format "- {{.Names}} ({{.Status}})"
else
    success "Aucun conteneur en cours d'exécution"
fi

# Vérification des volumes
info "Vérification des volumes Docker..."
volumes_count=$(docker volume ls -q | wc -l)
if [ "$volumes_count" -gt 0 ]; then
    success "$volumes_count volume(s) Docker trouvé(s)"
else
    warning "Aucun volume Docker trouvé"
fi

# Vérification des images
info "Vérification des images Docker..."
images_count=$(docker images -q | wc -l)
if [ "$images_count" -gt 0 ]; then
    success "$images_count image(s) Docker trouvée(s)"
else
    warning "Aucune image Docker trouvée"
fi

# Vérification des dépendances système
info "Vérification des dépendances système..."

# Liste des commandes requises
declare -A required_commands=(
    ["git"]="Git"
    ["curl"]="cURL"
    ["unzip"]="Unzip"
    ["tar"]="Tar"
    ["grep"]="Grep"
    ["awk"]="AWK"
    ["sed"]="Sed"
)

# Vérifier chaque commande
for cmd in "${!required_commands[@]}"; do
    if command_exists "$cmd"; then
        success "${required_commands[$cmd]} est installé"
    else
        warning "${required_commands[$cmd]} n'est pas installé"
    fi
done

# Vérification des versions de PHP et extensions
info "Vérification de PHP et des extensions..."
if command_exists php; then
    php_version=$(php -r 'echo PHP_VERSION;' 2>/dev/null || echo "Non disponible")
    success "PHP est installé (version $php_version)"
    
    # Vérifier les extensions PHP requises
    required_extensions=("pdo_mysql" "intl" "zip" "gd" "opcache" "mbstring" "xml")
    missing_extensions=()
    
    for ext in "${required_extensions[@]}"; do
        if php -m | grep -q "^$ext$"; then
            success "Extension PHP $ext est activée"
        else
            missing_extensions+=("$ext")
        fi
    done
    
    if [ ${#missing_extensions[@]} -gt 0 ]; then
        warning "Extensions PHP manquantes : ${missing_extensions[*]}"
    fi
else
    warning "PHP n'est pas installé ou n'est pas dans le PATH"
fi

# Vérification des outils de développement frontend
info "Vérification des outils de développement frontend..."

# Node.js et npm
if command_exists node; then
    success "Node.js est installé (version $(node --version))"
    
    if command_exists npm; then
        success "npm est installé (version $(npm --version))"
    else
        warning "npm n'est pas installé"
    fi
else
    warning "Node.js n'est pas installé"
fi

# Yarn
if command_exists yarn; then
    success "Yarn est installé (version $(yarn --version))"
else
    warning "Yarn n'est pas installé"
fi

# Résumé
echo -e "\n${BLUE}========================================${NC}"
echo -e "${BLUE}         RÉSUMÉ DE LA VÉRIFICATION        ${NC}"
echo -e "${BLUE}========================================${NC}"

echo -e "${GREEN}✓${NC} Vérifications réussies : $(grep -c "\[SUCCÈS\]" $0 | xargs)"
echo -e "${YELLOW}⚠${NC} Avertissements : $(grep -c "\[ATTENTION\]" $0 | xargs)"
echo -e "${RED}✗${NC} Erreurs critiques : $(grep -c "\[ERREUR\]" $0 | xargs)"

# Afficher les prochaines étapes
echo -e "\n${BLUE}Prochaines étapes :${NC}"
echo "1. Configurez vos variables d'environnement dans .env.local"
echo "2. Lancez le script de démarrage : ./docker/scripts/start.sh"
echo "3. Accédez à l'application sur http://localhost:8080"

exit 0
