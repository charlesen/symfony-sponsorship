# DEVBOOK

## Fonctionnalités à implémenter (par étapes)

### 1. Authentification simple
- [x] Utilisateur entre son email
- [x] Envoi d’un lien de connexion via Symfony Mailer (magic link)
- [x] Token à usage unique, valable X minutes
- [ ] Stocké temporairement en base (table magic_link_tokens)

### 2. Tableau de bord utilisateur
- [ ] URL de parrainage unique (type /invite/xxxx)
- [ ] Liste des missions (suivre, partager, inviter)
- [ ] Points et statut affichés
- [ ] Suivi des actions remplies

### 3. Missions personnalisables (en BDD)
- [ ] Entité Mission (nom, description, type, points, lien)
    - follow_instagram
    - share_page
    - invite_friend
    - etc.
- [ ] L’utilisateur coche des cases ou remplit des champs pour valider.

### 4. Stockage des contacts dans Brevo
- [ ] À chaque action :
    - L’email + prénom sont ajoutés via l’API Brevo
    - Tu peux associer des attributs personnalisés (parrain, score, actions remplies, etc.)
