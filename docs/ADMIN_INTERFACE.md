# Interface d'Administration

Ce document décrit l'interface d'administration créée pour la gestion de la plateforme vidéo.

## Accès à l'Administration

### URL d'accès
- **URL locale :** `http://localhost/admin`
- **Middleware requis :** Authentification + Droits administrateur

### Compte administrateur par défaut
- **Email :** `admin@seledjam.com`
- **Mot de passe :** `admin123`
- **Note :** Ce compte est créé automatiquement via le seeder `AdminUserSeeder`

## Fonctionnalités Principales

### 1. Tableau de Bord (`/admin`)
- **Statistiques générales :** Utilisateurs, vidéos, créateurs, vues totales
- **Graphiques interactifs :** Inscriptions et activité sur 30 jours
- **Listes récentes :** Nouveaux utilisateurs, vidéos récentes, activités
- **Aperçu en temps réel** des métriques importantes

### 2. Gestion des Utilisateurs (`/admin/users`)

#### Fonctionnalités
- **Liste paginée** avec recherche et filtres
- **Filtres disponibles :**
  - Statut email (vérifié/non vérifié)
  - Statut téléphone (vérifié/non vérifié)
  - Administrateurs
- **Actions par utilisateur :**
  - Voir les détails complets
  - Modifier les informations
  - Promouvoir/Rétrograder administrateur
  - Vérifier email/téléphone manuellement
  - Supprimer (avec protection du dernier admin)

#### Vue détaillée
- **Informations complètes :** Profil, contact, statuts
- **Statistiques d'activité :** Favoris, téléchargements, temps de visionnage
- **Historique :** Favoris récents, activités récentes
- **Actions rapides :** Vérifications, gestion des droits

### 3. Gestion des Vidéos (`/admin/videos`)

#### Fonctionnalités
- **Liste complète** avec thumbnails et métadonnées
- **Filtres avancés :**
  - Recherche par titre/description
  - Visibilité (public/privé/non listé)
  - Créateur
- **Actions individuelles :**
  - Voir détails et statistiques
  - Modifier informations
  - Supprimer vidéo
- **Actions en lot :**
  - Changer la visibilité
  - Suppression multiple

#### Informations affichées
- Thumbnail et titre
- Créateur (avec badge de vérification)
- Visibilité avec icônes colorées
- Statistiques (vues, favoris, téléchargements)
- Date de création

### 4. Gestion des Créateurs (`/admin/creators`)

#### Fonctionnalités
- **Liste des créateurs** avec statistiques
- **Filtres :**
  - Recherche par nom/description
  - Statut de vérification
- **Actions :**
  - Voir profil détaillé
  - Modifier informations
  - Basculer la vérification
  - Supprimer (si aucune vidéo)

#### Informations créateur
- Statistiques complètes
- Top 10 des vidéos les plus populaires
- Répartition par visibilité

## Architecture Technique

### Structure des Contrôleurs
```
app/Http/Controllers/Admin/
├── DashboardController.php    # Tableau de bord
├── UserController.php         # Gestion utilisateurs
├── VideoController.php        # Gestion vidéos
└── CreatorController.php      # Gestion créateurs
```

### Middleware de Sécurité
- **AdminMiddleware :** Vérifie les droits administrateur
- **Authentification :** Contrôle de l'accès
- **Protection CSRF :** Sur tous les formulaires

### Routes Administratives
```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Tableau de bord
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Gestion des utilisateurs
    Route::resource('users', AdminUserController::class);
    Route::post('users/{user}/toggle-admin', [AdminUserController::class, 'toggleAdmin']);
    Route::post('users/{user}/verify-email', [AdminUserController::class, 'verifyEmail']);
    Route::post('users/{user}/verify-phone', [AdminUserController::class, 'verifyPhone']);
    
    // Gestion des vidéos
    Route::resource('videos', AdminVideoController::class);
    Route::post('videos/bulk-visibility', [AdminVideoController::class, 'bulkUpdateVisibility']);
    Route::delete('videos/bulk-delete', [AdminVideoController::class, 'bulkDelete']);
    
    // Gestion des créateurs
    Route::resource('creators', AdminCreatorController::class);
    Route::post('creators/{creator}/toggle-verification', [AdminCreatorController::class, 'toggleVerification']);
});
```

## Interface Utilisateur

### Design System
- **Framework :** Tailwind CSS
- **Icônes :** Font Awesome 6
- **Graphiques :** Chart.js
- **Layout :** Responsive et mobile-first

### Composants Principaux
- **Navigation :** Sidebar avec liens contextuels
- **Cartes de statistiques :** Métriques avec icônes colorées
- **Tableaux :** Pagination, tri, filtres
- **Formulaires :** Validation côté client et serveur
- **Notifications :** Messages flash avec auto-masquage

### Fonctionnalités UX
- **Recherche en temps réel**
- **Filtres dynamiques**
- **Actions en lot** avec sélection multiple
- **Confirmations** pour actions destructives
- **Messages de feedback** pour toutes les actions

## Sécurité

### Contrôle d'Accès
- **Middleware admin** obligatoire
- **Vérification de session** active
- **Protection du dernier administrateur**
- **Validation des permissions** par action

### Protection des Données
- **Validation des entrées** (client + serveur)
- **Protection CSRF** sur tous les formulaires
- **Échappement XSS** automatique
- **Logs d'audit** pour actions sensibles

### Bonnes Pratiques
- **Mots de passe hashés** (bcrypt)
- **Sessions sécurisées**
- **Tokens CSRF** renouvelés
- **Validation stricte** des fichiers uploadés

## Installation et Configuration

### 1. Migration de la Base de Données
```bash
php artisan migrate
```

### 2. Création de l'Administrateur
```bash
php artisan db:seed --class=AdminUserSeeder
```

### 3. Configuration des Permissions
La colonne `is_admin` dans la table `users` détermine les droits administrateur.

### 4. Middleware
Le middleware `admin` est enregistré automatiquement dans `bootstrap/app.php`.

## Utilisation

### Première Connexion
1. Accéder à `/login`
2. Se connecter avec le compte admin
3. Naviguer vers `/admin`
4. Changer le mot de passe par défaut

### Gestion Quotidienne
- **Tableau de bord :** Vue d'ensemble quotidienne
- **Modération :** Vérification des nouveaux contenus
- **Support :** Gestion des utilisateurs et problèmes
- **Maintenance :** Actions en lot et nettoyage

### Surveillance
- **Métriques temps réel :** Activité utilisateur
- **Alertes automatiques :** Actions suspectes
- **Rapports périodiques :** Statistiques d'usage
- **Logs d'activité :** Traçabilité complète

## Maintenance

### Sauvegarde
- **Base de données :** Backup régulier recommandé
- **Fichiers uploadés :** Sauvegarde du stockage
- **Configuration :** Versioning des settings

### Mises à Jour
- **Migration :** Tests en environnement de développement
- **Rollback :** Plan de retour en arrière
- **Monitoring :** Surveillance post-déploiement

### Optimisation
- **Cache :** Redis/Memcached pour les statistiques
- **Index :** Optimisation des requêtes lourdes
- **CDN :** Distribution des assets statiques

## Support

### Logs
- **Laravel logs :** `storage/logs/laravel.log`
- **Admin actions :** Tracé automatique des actions
- **Erreurs :** Monitoring des exceptions

### Dépannage
- **Accès perdu :** Récupération via base de données
- **Permissions :** Vérification des droits
- **Performance :** Analyse des requêtes lentes

### Contact
Pour toute question technique concernant l'interface d'administration, consulter l'équipe de développement. 