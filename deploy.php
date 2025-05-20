<?php

namespace Deployer;

require 'recipe/symfony.php';

// Configuration du projet
set('application', 'Symfony Sponsorship');
set('repository', 'git@github.com:charlesen/symfony-sponsorship-bundle.git');
set('git_tty', true);
set('keep_releases', 3);
set('allow_anonymous_stats', false);

// Configuration des dossiers partagés (fichiers uploadés, etc.)
add('shared_dirs', [
    'var/log',
    'var/sessions',
    'public/uploads',
]);

// Configuration des fichiers partagés (fichiers de configuration locaux, .env, etc.)
add('shared_files', [
    '.env.local',
    '.env.prod',
]);

// Configuration des dossiers avec droits en écriture (pour les caches, etc.)
add('writable_dirs', [
    'var/cache',
    'var/log',
    'var/sessions',
]);

// Configuration des tâches personnalisées
task('deploy:cache:warmup', function () {
    // Désactive le warmup du cache en production pour éviter les problèmes de performance
})->desc('Warm up cache');

task('deploy:assets', function () {
    run('{{bin/php}} {{bin/composer}} run build:assets');
})->desc('Build assets');

// Configuration des serveurs
host('production')
    ->hostname('54.38.189.216')  // Votre adresse IP
    ->port(22)
    ->user('charles')  // Votre utilisateur
    ->set('deploy_path', '/var/www/symfony-sponsorship')
    ->set('branch', 'main')
    ->set('http_user', 'www-data')
    ->set('writable_mode', 'chmod')
    ->set('writable_use_sudo', false)
    ->set('clear_paths', ['.git', 'deploy.php', 'docker-compose.yml', 'docker-compose.override.yml', 'Dockerfile', '.docker'])
    ->set('composer_options', '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader --no-scripts')
    ->set('env', [
        'APP_ENV' => 'prod',
        'APP_DEBUG' => '0',
    ]);

// Configuration des tâches à exécuter avant et après le déploiement
after('deploy:failed', 'deploy:unlock');

after('deploy:vendors', 'deploy:assets');

// Tâche de déploiement complète
task('deploy', [
    'deploy:info',
    'deploy:setup',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);
