# Documentation des Besoins API - Application Seledjam

## Vue d'ensemble

L'application **Seledjam** est une plateforme de streaming vidéo mobile développée en React Native/Expo. Elle nécessite une API robuste pour gérer l'authentification, les vidéos, les utilisateurs et leurs interactions.

## Architecture Actuelle

- **Frontend** : React Native/Expo avec TypeScript
- **State Management** : Zustand
- **Backend actuel** : Appwrite (authentification et base de données)
- **Lecteur vidéo** : YouTube Player intégré
- **Authentification OTP** : WhatsApp via Baileys

---

## 1. Authentification et Gestion des Utilisateurs

### 1.1 Endpoints d'Authentification

#### Inscription par Email/Mot de passe
```http
POST /api/auth/register
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "motdepasse123",
  "phone": "+237123456789",
  "name": "John Doe"
}

Response 201:
{
  "success": true,
  "data": {
    "user": {
      "id": "user_123",
      "email": "user@example.com",
      "phone": "+237123456789",
      "name": "John Doe",
      "avatar": "https://api.seledjam.com/avatars/user_123.jpg",
      "accountId": "account_456",
      "createdAt": "2024-01-15T10:30:00Z"
    },
    "session": {
      "token": "jwt_token_here",
      "refreshToken": "refresh_token_here",
      "expiresAt": "2024-01-16T10:30:00Z"
    }
  }
}
```

#### Connexion par Email/Mot de passe
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "motdepasse123"
}

Response 200:
{
  "success": true,
  "data": {
    "user": { /* objet utilisateur */ },
    "session": { /* objet session */ }
  }
}
```

#### Authentification par Téléphone (OTP)
```http
POST /api/auth/phone/request-otp
Content-Type: application/json

{
  "phone": "+237123456789"
}

Response 200:
{
  "success": true,
  "data": {
    "userId": "temp_user_id",
    "message": "Code OTP envoyé",
    "expiresIn": 300
  }
}
```

```http
POST /api/auth/phone/verify-otp
Content-Type: application/json

{
  "userId": "temp_user_id",
  "otp": "123456",
  "phone": "+237123456789"
}

Response 200:
{
  "success": true,
  "data": {
    "user": { /* objet utilisateur */ },
    "session": { /* objet session */ },
    "isNewUser": false
  }
}
```

#### OAuth Google
```http
GET /api/auth/google/redirect
Response: Redirection vers Google OAuth

POST /api/auth/google/callback
{
  "code": "google_auth_code",
  "state": "csrf_token"
}
```

### 1.2 Gestion des Sessions

#### Rafraîchir le Token
```http
POST /api/auth/refresh
Authorization: Bearer {refresh_token}

Response 200:
{
  "success": true,
  "data": {
    "token": "nouveau_jwt_token",
    "expiresAt": "2024-01-16T10:30:00Z"
  }
}
```

#### Déconnexion
```http
POST /api/auth/logout
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "message": "Déconnexion réussie"
}
```

### 1.3 Profil Utilisateur

#### Obtenir le Profil Actuel
```http
GET /api/user/profile
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "data": {
    "id": "user_123",
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "+237123456789",
    "avatar": "https://api.seledjam.com/avatars/user_123.jpg",
    "joinDate": "2024-01-15T10:30:00Z",
    "statistics": {
      "videosWatched": 156,
      "favorites": 89,
      "downloads": 23,
      "totalWatchTime": 45600
    }
  }
}
```

#### Mettre à Jour le Profil
```http
PUT /api/user/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Doe Updated",
  "avatar": "base64_image_data"
}

Response 200:
{
  "success": true,
  "data": {
    "user": { /* objet utilisateur mis à jour */ }
  }
}
```

---

## 2. Gestion des Vidéos

### 2.1 Structure des Données Vidéo

```typescript
interface Video {
  id: string;
  title: string;
  description: string;
  thumbnail: string;
  content_url: string;
  creator: {
    id: string;
    name: string;
    avatar?: string;
    subscriberCount?: number;
  };
  views: number;
  duration: string;
  genre: string[];
  rating: number;
  visibility: 'public' | 'private' | 'unlisted';
  season?: string;
  createdAt: Date;
  updatedAt: Date;
}
```

### 2.2 Endpoints Vidéos

#### Obtenir les Vidéos de la Page d'Accueil
```http
GET /api/videos/home
Authorization: Bearer {token}
Query Parameters:
  - limit: number (optionnel, défaut: 10)
  - page: number (optionnel, défaut: 1)

Response 200:
{
  "success": true,
  "data": {
    "featuredVideos": [
      {
        "id": "video_123",
        "title": "Mulan",
        "description": "Description de la vidéo...",
        "thumbnail": "https://api.seledjam.com/thumbnails/video_123.jpg",
        "content_url": "https://youtube.com/watch?v=abc123",
        "creator": {
          "id": "creator_456",
          "name": "Disney",
          "avatar": "https://api.seledjam.com/avatars/creator_456.jpg",
          "subscriberCount": 125000
        },
        "views": 1500000,
        "duration": "1h 55m",
        "genre": ["Animation", "Adventure", "Family"],
        "rating": 4.5,
        "visibility": "public",
        "season": "Film",
        "createdAt": "2024-01-10T08:00:00Z"
      }
    ],
    "sections": [
      {
        "title": "Latest",
        "videos": [ /* array de vidéos */ ]
      },
      {
        "title": "Trending",
        "videos": [ /* array de vidéos */ ]
      }
    ]
  }
}
```

#### Recherche de Vidéos
```http
GET /api/videos/search
Authorization: Bearer {token}
Query Parameters:
  - q: string (terme de recherche)
  - genre: string (optionnel)
  - limit: number (optionnel, défaut: 20)
  - page: number (optionnel, défaut: 1)

Response 200:
{
  "success": true,
  "data": {
    "videos": [ /* array de vidéos */ ],
    "totalResults": 150,
    "page": 1,
    "totalPages": 8
  }
}
```

#### Obtenir une Vidéo Spécifique
```http
GET /api/videos/{videoId}
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "data": {
    "video": { /* objet vidéo complet */ },
    "relatedVideos": [ /* array de vidéos similaires */ ]
  }
}
```

#### Obtenir les Sections de Vidéos
```http
GET /api/videos/sections
Authorization: Bearer {token}
Query Parameters:
  - type: string (latest, trending, popular, recommended)

Response 200:
{
  "success": true,
  "data": {
    "sections": [
      {
        "title": "Latest",
        "type": "latest",
        "videos": [ /* array de vidéos */ ]
      }
    ]
  }
}
```

---

## 3. Gestion des Acteurs

### 3.1 Structure des Données Acteur

```typescript
interface Actor {
  id: string;
  name: string;
  profileImage: string;
  biography?: string;
  filmography?: string[];
}
```

### 3.2 Endpoints Acteurs

#### Obtenir la Liste des Acteurs
```http
GET /api/actors
Authorization: Bearer {token}
Query Parameters:
  - limit: number (optionnel, défaut: 20)
  - page: number (optionnel, défaut: 1)

Response 200:
{
  "success": true,
  "data": {
    "actors": [
      {
        "id": "actor_123",
        "name": "Leonardo DiCaprio",
        "profileImage": "https://api.seledjam.com/actors/actor_123.jpg",
        "filmography": ["Titanic", "Inception", "The Wolf of Wall Street"]
      }
    ]
  }
}
```

#### Recherche d'Acteurs
```http
GET /api/actors/search
Authorization: Bearer {token}
Query Parameters:
  - q: string (nom de l'acteur)

Response 200:
{
  "success": true,
  "data": {
    "actors": [ /* array d'acteurs */ ]
  }
}
```

---

## 4. Interactions Utilisateur

### 4.1 Favoris

#### Ajouter/Retirer des Favoris
```http
POST /api/user/favorites/{videoId}
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "data": {
    "isFavorite": true,
    "message": "Vidéo ajoutée aux favoris"
  }
}
```

#### Obtenir les Favoris
```http
GET /api/user/favorites
Authorization: Bearer {token}
Query Parameters:
  - limit: number (optionnel)
  - page: number (optionnel)

Response 200:
{
  "success": true,
  "data": {
    "favorites": [ /* array de vidéos */ ],
    "totalCount": 89
  }
}
```

### 4.2 Historique de Visionnage

#### Enregistrer une Vue
```http
POST /api/user/watch-history
Authorization: Bearer {token}
Content-Type: application/json

{
  "videoId": "video_123",
  "watchedDuration": 1800,
  "totalDuration": 3600,
  "completed": false
}

Response 200:
{
  "success": true,
  "message": "Progression enregistrée"
}
```

#### Obtenir l'Historique
```http
GET /api/user/watch-history
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "data": {
    "history": [
      {
        "video": { /* objet vidéo */ },
        "watchedAt": "2024-01-15T14:30:00Z",
        "watchedDuration": 1800,
        "totalDuration": 3600,
        "completed": false
      }
    ]
  }
}
```

### 4.3 Téléchargements

#### Demander un Téléchargement
```http
POST /api/user/downloads/{videoId}
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "data": {
    "downloadId": "download_123",
    "downloadUrl": "https://api.seledjam.com/downloads/video_123.mp4",
    "expiresAt": "2024-01-16T10:30:00Z"
  }
}
```

#### Obtenir les Téléchargements
```http
GET /api/user/downloads
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "data": {
    "downloads": [
      {
        "id": "download_123",
        "video": { /* objet vidéo */ },
        "downloadedAt": "2024-01-15T16:00:00Z",
        "fileSize": "1.2GB",
        "quality": "720p"
      }
    ]
  }
}
```

### 4.4 Activité Utilisateur

#### Obtenir l'Activité
```http
GET /api/user/activity
Authorization: Bearer {token}
Query Parameters:
  - type: string (like, watch, download, share)
  - limit: number (optionnel)

Response 200:
{
  "success": true,
  "data": {
    "activities": [
      {
        "id": "activity_123",
        "type": "like",
        "video": { /* objet vidéo */ },
        "timestamp": "2024-01-15T14:30:00Z"
      }
    ]
  }
}
```

#### Enregistrer une Activité
```http
POST /api/user/activity
Authorization: Bearer {token}
Content-Type: application/json

{
  "type": "share",
  "videoId": "video_123",
  "platform": "whatsapp"
}
```

---

## 5. Notifications

### 5.1 Gestion des Notifications

#### Obtenir les Notifications
```http
GET /api/user/notifications
Authorization: Bearer {token}
Query Parameters:
  - unread: boolean (optionnel)
  - limit: number (optionnel)

Response 200:
{
  "success": true,
  "data": {
    "notifications": [
      {
        "id": "notif_123",
        "title": "Nouvelle vidéo disponible",
        "message": "Découvrez la nouvelle vidéo de Disney",
        "type": "new_video",
        "read": false,
        "createdAt": "2024-01-15T12:00:00Z",
        "data": {
          "videoId": "video_456"
        }
      }
    ],
    "unreadCount": 5
  }
}
```

#### Marquer comme Lu
```http
PUT /api/user/notifications/{notificationId}/read
Authorization: Bearer {token}

Response 200:
{
  "success": true,
  "message": "Notification marquée comme lue"
}
```

---

## 6. Administration (Optionnel)

### 6.1 Gestion des Vidéos (Admin)

#### Ajouter une Vidéo
```http
POST /api/admin/videos
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data

{
  "title": "Nouveau Film",
  "description": "Description...",
  "thumbnail": File,
  "content_url": "https://youtube.com/watch?v=xyz789",
  "genre": ["Action", "Drama"],
  "visibility": "public"
}
```

#### Statistiques
```http
GET /api/admin/statistics
Authorization: Bearer {admin_token}

Response 200:
{
  "success": true,
  "data": {
    "totalUsers": 15420,
    "totalVideos": 1250,
    "totalViews": 2500000,
    "activeUsers": 3200
  }
}
```

---

## 7. Considérations Techniques

### 7.1 Authentification
- **JWT Tokens** avec expiration courte (15-30 minutes)
- **Refresh Tokens** avec expiration longue (7-30 jours)
- **Rate Limiting** sur les endpoints sensibles
- **CORS** configuré pour l'application mobile

### 7.2 Sécurité
- **HTTPS** obligatoire pour tous les endpoints
- **Validation** stricte des données d'entrée
- **Chiffrement** des données sensibles
- **Logs d'audit** pour les actions importantes

### 7.3 Performance
- **Pagination** sur toutes les listes
- **Cache** Redis pour les données fréquemment accessibles
- **CDN** pour les images et vidéos
- **Compression** des réponses JSON

### 7.4 Gestion d'Erreurs

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Les données fournies ne sont pas valides",
    "details": {
      "email": ["Format d'email invalide"],
      "password": ["Le mot de passe doit contenir au moins 8 caractères"]
    }
  }
}
```

### 7.5 Codes de Statut HTTP
- **200** : Succès
- **201** : Création réussie
- **400** : Erreur de validation
- **401** : Non authentifié
- **403** : Accès refusé
- **404** : Ressource non trouvée
- **429** : Trop de requêtes
- **500** : Erreur serveur

---

## 8. Intégrations Existantes

### 8.1 Appwrite
- Continuer à utiliser pour l'authentification de base
- Migrer progressivement vers la nouvelle API

### 8.2 WhatsApp OTP
- Maintenir l'intégration Baileys existante
- Endpoint `/api/auth/phone/request-otp` doit utiliser ce service

### 8.3 YouTube Player
- Les URLs `content_url` pointent vers YouTube
- Gérer l'extraction des IDs YouTube côté client

---

## 9. Roadmap de Développement

### Phase 1 : API Core
1. Authentification et gestion des utilisateurs
2. CRUD des vidéos
3. Système de favoris et historique

### Phase 2 : Fonctionnalités Avancées
1. Recherche et filtres
2. Recommandations
3. Notifications

### Phase 3 : Optimisations
1. Cache et performance
2. Analytiques
3. Administration

---

Ce document servira de base pour le développement de l'API backend qui supportera toutes les fonctionnalités de l'application Seledjam. 