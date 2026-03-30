# Symfony FrankenPHP CI/CD

Production-grade Symfony 7 application with FrankenPHP, automated CI/CD pipeline via GitHub Actions and a self-hosted runner.

## Stack technique

- **Runtime** : FrankenPHP (PHP 8.3 + Caddy intégré)
- **Framework** : Symfony 7
- **ORM** : Doctrine ORM + Migrations
- **Base de données** : MySQL 8.0
- **Assets** : Symfony Asset Mapper
- **Tests** : PHPUnit 12
- **Conteneurisation** : Docker + Docker Compose
- **CI/CD** : GitHub Actions + Self-hosted runner

## Architecture
```
cicd/
├── appDev/                        # Environnement de développement
│   ├── Dockerfile                 # Image FrankenPHP PHP 8.3
│   ├── docker-compose.yml         # Services dev (php:8080, mysql:3306)
│   ├── .env.compose               # Credentials Docker (non commité)
│   ├── .github/
│   │   └── workflows/
│   │       └── ci-cd.yml          # Pipeline GitHub Actions
│   └── symfony/                   # Code source Symfony
│       ├── src/
│       │   ├── Entity/Article.php
│       │   └── Repository/ArticleRepository.php
│       ├── migrations/            # Migrations Doctrine
│       ├── tests/                 # Tests PHPUnit
│       ├── .env                   # Template variables (commité)
│       └── .env.local             # Vraies valeurs (non commité)
│
├── appProd/                       # Environnement de production simulé
│   ├── docker-compose.prod.yml    # Services prod (php:9080, mysql:3307)
│   ├── .env.compose               # Credentials prod (non commité)
│   └── symfony/
│       └── .env.local             # Config prod APP_ENV=prod (non commité)
│
└── actions-runner/                # Self-hosted GitHub Actions runner
```

## Pipeline CI/CD

Le pipeline se déclenche automatiquement à chaque `git push` sur `master` :
```
git push origin master
        │
        ▼
┌─────────────────────┐
│   Tests PHPUnit      │  ← ubuntu-latest (serveur GitHub)
│   composer install   │
│   phpunit            │
└────────┬────────────┘
         │ si succès
         ▼
┌─────────────────────┐
│  Deploy Production   │  ← self-hosted runner (WSL2 local)
│  git pull            │
│  composer install    │
│  migrations          │
│  cache:warmup        │
└─────────────────────┘
```

## Séparation des environnements

| Fichier | Commité | Rôle |
|---|---|---|
| `.env` | Oui | Template — documente les variables disponibles |
| `.env.local` | Non | Valeurs réelles dev/prod — jamais sur GitHub |
| `.env.compose` | Non | Credentials MySQL pour Docker Compose |

## Ports

| Service | Dev | Prod |
|---|---|---|
| FrankenPHP (HTTP) | 8080 | 9080 |
| FrankenPHP (HTTPS) | 8443 | 9443 |
| MySQL | 3306 | 3307 |

## Démarrage

### Développement
```bash
cd appDev
docker compose --env-file .env.compose up -d --build
docker exec appdev-php-1 php bin/console doctrine:migrations:migrate --no-interaction
```

### Tests
```bash
docker exec appdev-php-1 php vendor/bin/phpunit
```

### Production
```bash
cd appProd
docker compose -f docker-compose.prod.yml --env-file .env.compose up -d --build
docker exec appprod-php-1 composer install --no-dev --optimize-autoloader
docker exec appprod-php-1 php bin/console doctrine:migrations:migrate --no-interaction
```

## Sécurité

- Aucun secret commité sur GitHub
- Clé SSH dédiée au CI/CD stockée dans GitHub Secrets
- `APP_ENV=prod` + `APP_DEBUG=0` en production
- `composer install --no-dev` en production
- Self-hosted runner isolé sur la machine locale
