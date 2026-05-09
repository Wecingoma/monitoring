# MonitorIA - Système Intelligent de Monitoring et d'Alerte

Plateforme professionnelle de monitoring intégrant **Zabbix**, **ELK Stack** et **Intelligence Artificielle** pour la supervision, l'analyse et la détection d'anomalies en temps réel.

## Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Interface Web (Laravel Blade)          │
│                   Dashboard SOC/NOC temps réel           │
└─────────────────────┬───────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────┐
│              API REST Laravel 12 (Backend)               │
│     JWT Auth │ Middleware │ Validation │ Services       │
└───┬──────────┬──────────┬──────────┬────────────────────┘
    │          │          │          │
    │          │          │          │
┌───▼──┐  ┌───▼──┐  ┌───▼──┐  ┌───▼──┐
│Zabbix│  │ ELK  │  │  IA  │  │  PG  │
│  API │  │Stack │  │Module│  │  DB  │
└──────┘  └──────┘  └──────┘  └──────┘
```

## Stack Technologique

| Composant     | Technologie                         |
|---------------|-------------------------------------|
| Backend       | Laravel 12, PHP 8.3                 |
| Frontend      | Blade, Tailwind CSS, ApexCharts     |
| Auth          | Laravel Sanctum (token API)         |
| Base de données| PostgreSQL 16                       |
| Cache/Queue   | Redis 7                             |
| Monitoring    | Zabbix 7.0                         |
| Logs          | Elasticsearch 8.13 + Logstash + Kibana |
| IA             | Python 3.12, Flask, Scikit-learn   |
| Déploiement   | Docker Compose                     |

## Structure du Projet

```
monitoring/
├── backend-laravel/           # Application Laravel
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/   # Contrôleurs API REST
│   │   │   ├── Middleware/        # Middleware customs
│   │   │   └── Requests/          # FormRequest validation
│   │   ├── Models/               # Modèles Eloquent
│   │   └── Services/              # Services métier
│   ├── database/
│   │   ├── factories/            # Factories
│   │   ├── migrations/           # Migrations PostgreSQL
│   │   └── seeders/              # Seeders
│   ├── resources/
│   │   └── views/                # Vues Blade (Dashboard SOC)
│   ├── routes/
│   │   ├── api.php               # Routes API REST
│   │   └── web.php               # Routes Web
│   └── Dockerfile
├── python-ai/                    # Module IA (Flask API)
│   ├── app.py                    # API Flask
│   ├── anomaly_detector.py       # Détection d'anomalies
│   ├── requirements.txt
│   └── Dockerfile
├── docker/
│   ├── nginx/default.conf        # Configuration Nginx
│   ├── logstash/pipeline/        # Pipeline Logstash
│   └── filebeat/                 # Configuration Filebeat
├── docker-compose.yml            # Orchestration complète
└── README.md
```

## Installation

### Prérequis

- Docker & Docker Compose
- PHP 8.3+ (pour développement local)
- Composer 2+
- Node.js 18+ & NPM
- Python 3.12+

### Déploiement avec Docker

```bash
# Cloner le projet
git clone <repo-url> monitoring && cd monitoring

# Lancer tous les services
docker-compose up -d

# Installer les dépendances Laravel
docker-compose exec app composer install

# Générer la clé d'application
docker-compose exec app php artisan key:generate

# Exécuter les migrations
docker-compose exec app php artisan migrate

# Seed la base de données
docker-compose exec app php artisan db:seed

# Installer les dépendances frontales
docker-compose exec app npm install && npm run build
```

### Développement Local (sans Docker)

```bash
# Backend Laravel
cd backend-laravel
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve

# Module IA
cd ../python-ai
pip install -r requirements.txt
python app.py

# Dans un autre terminal, lancer Filebeat/Logstash/Elasticsearch
```

## Comptes par Défaut

| Service       | URL                    | Identifiants                  |
|---------------|------------------------|-------------------------------|
| Application   | http://localhost       | admin@monitoria.local / password123 |
| API           | http://localhost/api/v1| Bearer Token                  |
| Zabbix        | http://localhost:8080  | Admin / zabbix                |
| Kibana        | http://localhost:5601  | -                             |
| IA Service     | http://localhost:5000  | -                             |

## Endpoints API

### Authentification

| Méthode | Endpoint              | Description        |
|---------|-----------------------|--------------------|
| POST    | /api/v1/auth/login    | Connexion          |
| POST    | /api/v1/auth/register| Inscription        |
| POST    | /api/v1/auth/logout   | Déconnexion       |
| GET     | /api/v1/auth/me       | Utilisateur actuel |
| POST    | /api/v1/auth/refresh  | Rafraîchir token  |

### Dashboard

| Méthode | Endpoint                     | Description              |
|---------|-------------------------------|--------------------------|
| GET     | /api/v1/dashboard             | Dashboard complet        |
| GET     | /api/v1/dashboard/stats       | Statistiques d'ensemble  |
| GET     | /api/v1/dashboard/metrics     | Métriques système        |
| GET     | /api/v1/dashboard/alerts      | Alertes récentes         |
| GET     | /api/v1/dashboard/anomalies   | Anomalies récentes       |

### Serveurs

| Méthode | Endpoint                          | Description            |
|---------|------------------------------------|------------------------|
| GET     | /api/v1/servers                    | Liste des serveurs     |
| POST    | /api/v1/servers                     | Créer un serveur       |
| GET     | /api/v1/servers/{id}               | Détails serveur        |
| PUT     | /api/v1/servers/{id}               | Mettre à jour          |
| DELETE  | /api/v1/servers/{id}               | Supprimer              |
| POST    | /api/v1/servers/sync-zabbix        | Sync Zabbix            |
| POST    | /api/v1/servers/{id}/detect-anomalies | Détection IA        |

### Alertes

| Méthode | Endpoint                          | Description            |
|---------|------------------------------------|------------------------|
| GET     | /api/v1/alerts                     | Liste des alertes      |
| POST    | /api/v1/alerts                      | Créer une alerte       |
| GET     | /api/v1/alerts/{id}                 | Détails alerte         |
| POST    | /api/v1/alerts/{id}/acknowledge     | Reconnaître            |
| POST    | /api/v1/alerts/{id}/resolve         | Résoudre               |

### Anomalies IA

| Méthode | Endpoint                           | Description         |
|---------|-------------------------------------|---------------------|
| GET     | /api/v1/anomalies                    | Liste anomalies    |
| GET     | /api/v1/anomalies/{id}              | Détails anomalie   |
| POST    | /api/v1/anomalies/{id}/false-positive| Marquer faux positif |
| POST    | /api/v1/anomalies/detect            | Lancer détection   |

### Incidents

| Méthode | Endpoint                    | Description          |
|---------|------------------------------|-----------------------|
| GET     | /api/v1/incidents            | Liste incidents       |
| POST    | /api/v1/incidents            | Créer incident        |
| GET     | /api/v1/incidents/{id}       | Détails incident     |
| PUT     | /api/v1/incidents/{id}       | Mettre à jour        |
| POST    | /api/v1/incidents/{id}/resolve | Résoudre incident  |

### Logs

| Méthode | Endpoint                       | Description          |
|---------|---------------------------------|-----------------------|
| GET     | /api/v1/logs                    | Liste logs           |
| GET     | /api/v1/logs/critical           | Logs critiques       |
| POST    | /api/v1/logs/search-elastic     | Recherche Elastic    |
| GET     | /api/v1/logs/stats              | Statistiques logs    |

### Zabbix

| Méthode | Endpoint                   | Description           |
|---------|-----------------------------|------------------------|
| GET     | /api/v1/zabbix/hosts        | Hôtes Zabbix          |
| GET     | /api/v1/zabbix/triggers    | Triggers Zabbix       |
| GET     | /api/v1/zabbix/problems    | Problèmes Zabbix      |
| GET     | /api/v1/zabbix/availability | Disponibilité         |

### Intelligence Artificielle

| Méthode | Endpoint                    | Description            |
|---------|------------------------------|------------------------|
| GET     | /api/v1/ai/detect/{server}  | Analyser un serveur    |
| POST    | /api/v1/ai/detect-all       | Analyser tous          |
| GET     | /api/v1/ai/stats            | Statistiques IA        |
| GET     | /api/ai/health              | Santé du module IA     |

## Sécurité

- **Authentification JWT** via Laravel Sanctum
- **Validation** des entrées via FormRequest
- **Protection CSRF** intégrée Laravel
- **Middleware de sécurité** (headers XSS, CSP, etc.)
- **Rate limiting** sur les routes API (60 req/min)
- **Hash bcrypt** pour les mots de passe
- **Gestion des rôles** : Administrateur, Analyste, Utilisateur
- **Journal d'audit** complet des actions

## Module IA

Le module Python utilise **Isolation Forest** et **Random Forest** de scikit-learn pour :

- Détection d'anomalies CPU/RAM/Réseau/Disque
- Scoring de sévérité automatique
- Recommandations en français
- Auto-apprentissage avec nouvelles données

Exemple de réponse IA :

```json
{
    "anomaly": true,
    "score": 0.92,
    "severity": "critical",
    "description": "Anomalie critique détectée: utilisation CPU à 95.3%",
    "recommendation": "Vérifier immédiatement les processus consommateurs",
    "data_points": {
        "current_value": 95.3,
        "average_value": 42.1,
        "max_value": 98.7,
        "trend": "increasing"
    },
    "model_info": {
        "model": "IsolationForest",
        "version": "1.0",
        "contamination": 0.1
    }
}
```

## Licence

Ce projet est développé dans un cadre académique et professionnel.#   m o n i t o r i n g  
 