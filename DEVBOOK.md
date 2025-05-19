# DEVBOOK

## État actuel du projet

### 1. Authentification
- [x] Système de connexion par email avec magic link
- [x] Gestion des utilisateurs avec entité User
- [x] Envoi d'emails avec Symfony Mailer
- [x] Internationalisation (traductions)

### 2. Structure de base
- [x] Système de routage avec localisation
- [x] Contrôleurs pour la gestion des pages et du tableau de bord
- [x] Entités de base (User, Page)
- [x] Système de slugs pour les URLs

## Prochaines étapes (24h)

### J-1 : Matin (4h)
1. **Tableau de bord utilisateur (2h)**
   - [ ] Créer l'interface du tableau de bord
   - [ ] Afficher les statistiques de base
   - [ ] Générer une URL de parrainage unique

2. **Système de missions (2h)**
   - [ ] Créer l'entité Mission avec ses propriétés
   - [ ] Implémenter le CRUD pour les missions
   - [ ] Créer les fixtures pour les missions de base

### J-1 : Après-midi (4h)
3. **Système de parrainage (2h)**
   - [ ] Implémenter la logique de parrainage
   - [ ] Créer la page d'invitation
   - [ ] Suivi des parrainages

4. **Intégration Brevo (2h)**
   - [ ] Configurer le SDK Brevo
   - [ ] Synchroniser les utilisateurs avec Brevo
   - [ ] Mettre à jour les attributs personnalisés

### J-1 : Soir (4h)
5. **Tests et optimisation (2h)**
   - [ ] Écrire des tests unitaires
   - [ ] Optimiser les requêtes
   - [ ] Vérifier la sécurité

6. **Finalisation (2h)**
   - [ ] Documentation utilisateur
   - [ ] Tests d'acceptation
   - [ ] Déploiement

## Notes techniques
- Utiliser les événements Symfony pour déclencher les actions de parrainage
- Implémenter un système de file d'attente pour les appels API vers Brevo
- Mettre en cache les données fréquemment accédées
