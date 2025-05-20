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
    echo "Utilisation : $0 [options] [suite...]"
    echo "Exécute les tests de l'application"
    echo ""
    echo "Options :"
    echo "  -u, --unit           Exécuter les tests unitaires"
    echo "  -f, --functional     Exécuter les tests fonctionnels"
    echo "  -i, --integration    Exécuter les tests d'intégration"
    echo "  -a, --all            Exécuter tous les tests (par défaut)"
    echo "  -c, --coverage       Générer un rapport de couverture de code"
    echo "  -r, --report         Générer un rapport JUnit"
    echo "  -v, --verbose        Afficher plus de détails"
    echo "  -h, --help           Affiche ce message d'aide"
    echo ""
    echo "Suites disponibles :"
    echo "  unit                Tests unitaires"
    echo "  functional          Tests fonctionnels"
    echo "  integration         Tests d'intégration"
    echo "  all                 Tous les tests (par défaut)"
    echo ""
    echo "Exemples :"
    echo "  $0 -u               # Exécuter les tests unitaires"
    echo "  $0 -f -v           # Exécuter les tests fonctionnels en mode verbeux"
    echo "  $0 -c -r           # Exécuter tous les tests avec couverture et rapport"
    echo "  $0 unit functional # Exécuter les tests unitaires et fonctionnels"
    exit 0
}

# Variables par défaut
RUN_UNIT=false
RUN_FUNCTIONAL=false
RUN_INTEGRATION=false
COVERAGE=false
REPORT=false
VERBOSE=false
SUITES=()

# Traiter les arguments
while [[ $# -gt 0 ]]; do
    case "$1" in
        -u|--unit)
            RUN_UNIT=true
            shift
            ;;
        -f|--functional)
            RUN_FUNCTIONAL=true
            shift
            ;;
        -i|--integration)
            RUN_INTEGRATION=true
            shift
            ;;
        -a|--all)
            RUN_UNIT=true
            RUN_FUNCTIONAL=true
            RUN_INTEGRATION=true
            shift
            ;;
        -c|--coverage)
            COVERAGE=true
            shift
            ;;
        -r|--report)
            REPORT=true
            shift
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        -h|--help)
            show_help
            ;;
        -*)
            error "Option non reconnue : $1"
            ;;
        *)
            SUITES+=("$1")
            shift
            ;;
    esac
done

# Si aucune suite n'est spécifiée, exécuter tous les tests
if [ ${#SUITES[@]} -eq 0 ] && [ "$RUN_UNIT" = false ] && [ "$RUN_FUNCTIONAL" = false ] && [ "$RUN_INTEGRATION" = false ]; then
    RUN_UNIT=true
    RUN_FUNCTIONAL=true
    RUN_INTEGRATION=true
fi

# Traiter les suites spécifiées
for suite in "${SUITES[@]}"; do
    case "$suite" in
        unit)
            RUN_UNIT=true
            ;;
        functional)
            RUN_FUNCTIONAL=true
            ;;
        integration)
            RUN_INTEGRATION=true
            ;;
        all)
            RUN_UNIT=true
            RUN_FUNCTIONAL=true
            RUN_INTEGRATION=true
            ;;
        *)
            warning "Suite de tests inconnue : $suite"
            ;;
    esac
done

# Charger les variables d'environnement
if [ -f "../../.env.test" ]; then
    export $(grep -v '^#' ../../.env.test | xargs)
fi

# Définir les variables par défaut
DOCKER_COMPOSE="docker compose"
SERVICE="php"
PHPUNIT="php bin/phpunit"
PHPUNIT_OPTS=()

# Vérifier si Docker est disponible
if ! command -v docker &> /dev/null; then
    error "Docker n'est pas installé ou n'est pas dans le PATH"
fi

# Vérifier si le service est en cours d'exécution
if ! $DOCKER_COMPOSE ps $SERVICE --status running &> /dev/null; then
    error "Le service $SERVICE n'est pas en cours d'exécution. Utilisez './docker/scripts/start.sh' pour démarrer l'environnement."
fi

# Configurer les options de PHPUnit
if [ "$COVERAGE" = true ]; then
    PHPUNIT_OPTS+=(--coverage-html var/coverage)
    info "Génération du rapport de couverture activée"
fi

if [ "$REPORT" = true ]; then
    PHPUNIT_OPTS+=(--log-junit var/logs/junit.xml)
    info "Génération du rapport JUnit activée"
fi

if [ "$VERBOSE" = true ]; then
    PHPUNIT_OPTS+=(-v)
fi

# Fonction pour exécuter une commande dans le conteneur PHP
run_in_container() {
    $DOCKER_COMPOSE exec -T $SERVICE $@
}

# Créer les répertoires nécessaires
run_in_container mkdir -p var/logs var/cache/test var/coverage

# Installer les dépendances de test si nécessaire
if [ ! -d "../../vendor/phpunit" ]; then
    info "Installation des dépendances de test..."
    run_in_container composer require --dev phpunit/phpunit symfony/phpunit-bridge
fi

# Exécuter les tests
TOTAL_TESTS=0
FAILED_TESTS=0

# Tests unitaires
if [ "$RUN_UNIT" = true ]; then
    info "Exécution des tests unitaires..."
    if ! run_in_container $PHPUNIT ${PHPUNIT_OPTS[@]} tests/Unit; then
        FAILED_TESTS=$((FAILED_TESTS + 1))
    fi
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
fi

# Tests fonctionnels
if [ "$RUN_FUNCTIONAL" = true ]; then
    info "Exécution des tests fonctionnels..."
    if ! run_in_container $PHPUNIT ${PHPUNIT_OPTS[@]} tests/Functional; then
        FAILED_TESTS=$((FAILED_TESTS + 1))
    fi
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
fi

# Tests d'intégration
if [ "$RUN_INTEGRATION" = true ]; then
    info "Exécution des tests d'intégration..."
    if ! run_in_container $PHPUNIT ${PHPUNIT_OPTS[@]} tests/Integration; then
        FAILED_TESTS=$((FAILED_TESTS + 1))
    fi
    TOTAL_TESTS=$((TOTAL_TESTS + 1))
fi

# Afficher le résumé
if [ $TOTAL_TESTS -eq 0 ]; then
    warning "Aucun test exécuté. Vérifiez vos paramètres."
    exit 0
fi

if [ $FAILED_TESTS -eq 0 ]; then
    success "Tous les tests ont réussi ($TOTAL_TESTS/$TOTAL_TESTS)"
    exit 0
else
    error "$FAILED_TESTS test(s) ont échoué sur $TOTAL_TESTS"
    exit 1
fi
