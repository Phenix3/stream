# Documentation API Seledjam

## Vue d'ensemble

Cette documentation présente l'implémentation complète de l'API backend pour l'application **Seledjam**, une plateforme de streaming vidéo mobile. L'API est développée en **Laravel 11** avec **Laravel Sanctum** pour l'authentification.

## Architecture

- **Framework** : Laravel 11
- **Authentification** : Laravel Sanctum (tokens API)
- **Base de données** : MySQL/PostgreSQL
- **Cache** : Redis (recommandé)
- **Storage** : Local/S3 pour les fichiers

## Structure des fichiers créés

### API Resources (app/Http/Resources/)
- `ApiResponse.php` - Classe helper pour les réponses API standardisées
- `UserResource.php` - Resource pour les utilisateurs
- `VideoResource.php` - Resource pour les vidéos
- `CreatorResource.php` - Resource pour les créateurs
- `ActorResource.php` - Resource pour les acteurs
- `GenreResource.php` - Resource pour les genres
- `NotificationResource.php` - Resource pour les notifications
- `FavoriteResource.php` - Resource pour les favoris
- `WatchHistoryResource.php` - Resource pour l'historique
- `DownloadResource.php` - Resource pour les téléchargements
- `ActivityResource.php` - Resource pour les activités
- `VideoSectionResource.php` - Resource pour les sections de vidéos

### Contrôleurs API (app/Http/Controllers/Api/)
- `AuthController.php` - Authentification email/mot de passe
- `PhoneAuthController.php` - Authentification par téléphone (OTP)
- `SocialAuthController.php` - Authentification sociale (Google OAuth)
- `UserController.php` - Gestion du profil utilisateur
- `VideoController.php` - Gestion des vidéos
- `ActorController.php` - Gestion des acteurs
- `FavoriteController.php` - Gestion des favoris
- `WatchHistoryController.php` - Historique de visionnage
- `DownloadController.php` - Gestion des téléchargements
- `ActivityController.php` - Activités utilisateur
- `NotificationController.php` - Gestion des notifications

### Contrôleurs d'administration (app/Http/Controllers/Api/Admin/)
- `VideoController.php` - Gestion administrative des vidéos
- `StatisticsController.php` - Statistiques et tableaux de bord

### Middleware
- `AdminMiddleware.php` - Vérification des permissions d'administration

### Routes
- `routes/api.php` - Toutes les routes API définies

## Authentification

L'API utilise **Laravel Sanctum** pour l'authentification par tokens. Trois méthodes d'authentification sont supportées :

### 1. Email/Mot de passe
```http
POST /api/auth/register
POST /api/auth/login
```

### 2. Téléphone avec OTP
```http
POST /api/auth/phone/request-otp
POST /api/auth/phone/verify-otp
```

### 3. OAuth Google
```http
GET /api/auth/google/redirect
POST /api/auth/google/callback
```

## Endpoints principaux

### Authentification et Session
- `POST /api/auth/refresh` - Rafraîchir le token
- `POST /api/auth/logout` - Déconnexion
- `GET /api/auth/me` - Informations utilisateur actuel

### Profil utilisateur
- `GET /api/user/profile` - Obtenir le profil
- `PUT /api/user/profile` - Mettre à jour le profil
- `PUT /api/user/change-password` - Changer le mot de passe
- `DELETE /api/user/account` - Supprimer le compte

### Vidéos
- `GET /api/videos/home` - Page d'accueil avec sections
- `GET /api/videos/search` - Recherche de vidéos
- `GET /api/videos/sections` - Sections spécifiques (latest, trending, etc.)
- `GET /api/videos/{id}` - Détails d'une vidéo
- `GET /api/videos/genre/{slug}` - Vidéos par genre

### Acteurs
- `GET /api/actors` - Liste des acteurs
- `GET /api/actors/search` - Recherche d'acteurs
- `GET /api/actors/popular` - Acteurs populaires
- `GET /api/actors/{id}` - Détails d'un acteur

### Favoris
- `GET /api/user/favorites` - Liste des favoris
- `POST /api/user/favorites/{videoId}` - Ajouter/retirer des favoris
- `GET /api/user/favorites/check/{videoId}` - Vérifier si en favoris

### Historique de visionnage
- `GET /api/user/watch-history` - Historique complet
- `POST /api/user/watch-history` - Enregistrer une progression
- `PUT /api/user/watch-history/{videoId}/complete` - Marquer comme vu

### Téléchargements
- `GET /api/user/downloads` - Liste des téléchargements
- `POST /api/user/downloads/{videoId}` - Demander un téléchargement
- `DELETE /api/user/downloads/expired` - Supprimer les téléchargements expirés

### Activités
- `GET /api/user/activity` - Activités utilisateur
- `POST /api/user/activity` - Enregistrer une activité
- `GET /api/user/activity/stats` - Statistiques d'activité

### Notifications
- `GET /api/user/notifications` - Liste des notifications
- `PUT /api/user/notifications/{id}/read` - Marquer comme lue
- `PUT /api/user/notifications/mark-all-read` - Marquer toutes comme lues
- `GET /api/user/notifications/unread-count` - Nombre de non lues

### Administration
- `GET /api/admin/videos` - Gestion des vidéos (admin)
- `POST /api/admin/videos` - Créer une vidéo
- `PUT /api/admin/videos/{id}` - Modifier une vidéo
- `DELETE /api/admin/videos/{id}` - Supprimer une vidéo
- `GET /api/admin/statistics` - Statistiques générales
- `GET /api/admin/statistics/users` - Stats utilisateurs
- `GET /api/admin/statistics/videos` - Stats vidéos

## Format des réponses

Toutes les réponses suivent un format standardisé :

### Succès
```json
{
  "success": true,
  "data": { ... },
  "message": "Message optionnel"
}
```

### Erreur
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Message d'erreur",
    "details": { ... }
  }
}
```

## Codes d'erreur

- `VALIDATION_ERROR` - Erreurs de validation
- `UNAUTHORIZED` - Non authentifié
- `FORBIDDEN` - Accès refusé
- `NOT_FOUND` - Ressource non trouvée
- `TOO_MANY_REQUESTS` - Limitation de taux
- `INTERNAL_ERROR` - Erreur serveur

## Authentification des requêtes

Pour les routes protégées, inclure le token dans l'en-tête :

```http
Authorization: Bearer {token}
```

## Pagination

Les listes paginées acceptent les paramètres :
- `limit` : Nombre d'éléments par page (défaut: 20, max: 100)
- `page` : Numéro de page (défaut: 1)

Format de réponse paginée :
```json
{
  "success": true,
  "data": {
    "items": [...],
    "totalCount": 150,
    "page": 1,
    "totalPages": 8
  }
}
```

## Configuration requise

### Middleware à enregistrer

Dans `bootstrap/app.php` ou équivalent :
```php
$app->withRouting(web: __DIR__.'/../routes/web.php', api: __DIR__.'/../routes/api.php')
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    });
```

### Variables d'environnement

```env
# Configuration Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URL=https://yourdomain.com/api/auth/google/callback

# Configuration des services
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=.yourdomain.com
```

## Fonctionnalités spéciales

### Upload de fichiers
Les endpoints acceptent les images en base64 pour les avatars et thumbnails :
```json
{
  "avatar": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ..."
}
```

### Système de cache
Recommandé d'implémenter un cache Redis pour :
- Données fréquemment accessées
- Statistiques
- Sessions utilisateur

### Rate Limiting
Configurer la limitation de taux pour :
- Authentification : 5 tentatives/minute
- API générale : 60 requêtes/minute
- Upload : 10 requêtes/minute

## Prochaines étapes

1. **Configurer les middlewares** dans le fichier de configuration Laravel
2. **Implémenter le cache Redis** pour les performances
3. **Configurer les services externes** (Google OAuth, WhatsApp OTP)
4. **Ajouter les tests unitaires et d'intégration**
5. **Configurer la surveillance et les logs**
6. **Implémenter la limitation de taux**
7. **Optimiser les requêtes de base de données**

Cette API est maintenant prête pour être intégrée avec l'application React Native/Expo Seledjam. 