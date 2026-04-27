# Translation Management Service

An API-driven Translation Management Service built with Laravel 12, Redis. Designed to store, manage, and export translations across multiple locales with contextual tagging support.

---

## Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Architecture](#architecture)
- [Design Decisions](#design-decisions)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Setup Instructions](#setup-instructions)
- [API Documentation](#api-documentation)
- [Running Tests](#running-tests)
- [Performance](#performance)

---

## Overview

The Translation Management Service allows developers and content managers to:

- Store translations for multiple locales (en, fr, es, ur, de, etc.)
- Tag translations for context (mobile, web, desktop)
- Create, update, view, and search translations
- Export translations as JSON for frontend applications like Vue.js
- Secure all endpoints with token-based authentication via Laravel Sanctum

---

## Tech Stack

| Technology | Purpose |
|---|---|
| Laravel 12 | PHP Framework |
| MySQL 8.0 | Primary Database |
| Redis | Caching Layer |
| Laravel Sanctum | API Authentication |
| Nginx | Web Server |
| Docker | Containerization |
| PHPUnit | Testing |

---

## Architecture

The application follows a layered architecture with clear separation of concerns:
```
Request
   │
Router
   │
Middleware   ← Sanctum Auth
   │
Controller   ← Handles Request / Response Only
   │
Service      ← All Business Logic
   │
Repository   ← All Database Queries
   │
Model        ← Eloquent + Relationships
   │
Database
```

Controller only holds the request and response. Business logic is in the services, and database queries are in the repository.

### Folder Structure
```
translation-management-service/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── TagController.php
│   │   │       └── TranslationController.php
│   │   ├── Requests/
│   │   │   ├── StoreTranslationRequest.php
│   │   │   └── UpdateTranslationRequest.php
│   │   └── Resources/
│   │       └── TranslationResource.php
│   ├── Models/
│   │   ├── Tag.php
│   │   ├── Translation.php
│   │   └── User.php
│   ├── Repositories/
│   │   ├── Contracts/
│   │   │   └── TranslationRepositoryInterface.php
│   │   └── TranslationRepository.php
│   ├── Services/
│   │   └── TranslationService.php
│   └── Providers/
│       └── AppServiceProvider.php
├── database/
│   ├── factories/
│   │   ├── TagFactory.php
│   │   └── TranslationFactory.php
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── create_personal_access_tokens_table.php
│   │   ├── create_translations_table.php
│   │   ├── create_tags_table.php
│   │   └── create_translation_tag_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── TagSeeder.php
│       └── TranslationSeeder.php
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   ├── Feature/
│   │   ├── AuthTest.php
│   │   ├── ExportTest.php
│   │   ├── TagTest.php
│   │   └── TranslationTest.php
│   └── Unit/
│       └── ExampleTest.php
├── .env
├── .env.example
├── docker-compose.yml
├── Dockerfile
└── README.md
```

---

## Design Decisions

### 1. Repository Pattern
All database queries are isolated in the Repository layer. This keeps the Service layer clean and makes it easy to swap the data source in the future without touching business logic.

### 2. Service Layer
All business logic lives in the Service layer. Controllers are kept thin — they only receive the request, call the service, and return the response.

### 3. Interface Binding
The Service layer depends on `TranslationRepositoryInterface` not the concrete `TranslationRepository`. This follows the Dependency Inversion Principle (SOLID) and makes the codebase easily testable and extendable.

### 4. Caching Strategy
The export endpoint is cached using Redis. The cache is automatically invalidated whenever a translation is created, updated, or deleted. This ensures the frontend always receives fresh translations while keeping response times under 500ms even with 100k+ records.

### 5. Token-Based Authentication
Laravel Sanctum is used for API authentication. It is lightweight, built into Laravel, and perfectly suited for API token management without the overhead of OAuth2 (Passport).

### 6. Pagination
The translations list endpoint returns paginated results (50 per page) to ensure consistent performance regardless of the number of records in the database.

### 7. Database Indexing
The following indexes are in place for optimal query performance:
- Index on `translations.locale`
- Index on `translations.key`
- Unique composite index on `translations.locale` + `translations.key`
- Index on `translation_tag.translation_id`
- Index on `translation_tag.tag_id`

---

## Database Schema
```
translations
├── id (PK)
├── locale (indexed)
├── key (indexed)
├── value
├── created_at
└── updated_at
tags
├── id (PK)
├── name (unique)
├── created_at
└── updated_at
translation_tag (pivot)
├── id (PK)
├── translation_id (FK → translations.id)
└── tag_id (FK → tags.id)
users
├── id (PK)
├── name
├── email (unique)
├── password
├── created_at
└── updated_at
```

---

## API Endpoints

### Authentication
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/auth/register` | No | Register a new user |
| POST | `/api/auth/login` | No | Login and get token |
| POST | `/api/auth/logout` | Yes | Logout and revoke token |

### Tags
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/api/tags` | Yes | List all tags |
| POST | `/api/tags` | Yes | Create a tag |
| GET | `/api/tags/{id}` | Yes | View a tag |
| PUT | `/api/tags/{id}` | Yes | Update a tag |
| DELETE | `/api/tags/{id}` | Yes | Delete a tag |

### Translations
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/api/translations` | Yes | List and search translations |
| POST | `/api/translations` | Yes | Create a translation |
| GET | `/api/translations/{id}` | Yes | View a translation |
| PUT | `/api/translations/{id}` | Yes | Update a translation |
| DELETE | `/api/translations/{id}` | Yes | Delete a translation |

### Export
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/api/export/{locale}` | No | Export all translations for a locale |

### Search Parameters
The `/api/translations` endpoint supports the following query parameters:

| Parameter | Description | Example |
|---|---|---|
| `locale` | Filter by locale | `?locale=en` |
| `key` | Search by key | `?key=welcome` |
| `content` | Search by value | `?content=Welcome` |
| `tag` | Filter by tag name | `?tag=mobile` |

---

## Setup Instructions

### Requirements
- PHP 8.3+
- Composer
- MySQL 8.0+
- Redis

### Local Setup

**1. Clone the repository**
```bash
git clone https://github.com/your-username/translation-management-service.git
cd translation-management-service
```

**2. Install dependencies**
```bash
composer install
```

**3. Copy environment file**
```bash
cp .env.example .env
```

**4. Generate application key**
```bash
php artisan key:generate
```

**5. Configure your `.env`**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_translation
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

**6. Run migrations**
```bash
php artisan migrate
```

**7. Seed the database with 100k records**
```bash
php artisan db:seed
```

**8. Generate Swagger documentation**
```bash
php artisan l5-swagger:generate
```

**9. Start the server**
```bash
php artisan serve
```

API is now available at `http://127.0.0.1:8000/api`

---

## API Documentation

Interactive API documentation is available via Swagger UI.

### Accessing the Documentation

Start the server and visit:
http://127.0.0.1:8000/api/documentation

### How to Authenticate in Swagger UI

1. Register or Login via the Auth endpoints to get a token
2. Click the **Authorize** button (top right of Swagger UI)
3. Enter your token in this format:
Bearer your_token_here
4. Click **Authorize** then **Close**
5. All protected endpoints will now work directly from the browser

### Regenerating the Documentation

If you make changes to the API, regenerate the docs with:
```bash
php artisan l5-swagger:generate
```

### Auto Regeneration

To automatically regenerate docs on every request (development only), set this in your `.env`:
```env
L5_SWAGGER_GENERATE_ALWAYS=true
```

> **Note:** Keep `L5_SWAGGER_GENERATE_ALWAYS=false` in production for performance reasons.

---

## Running Tests

**Run all tests:**
```bash
php artisan test
```

**Run with coverage:**
```bash
php artisan test --coverage
```

**Run specific test file:**
```bash
php artisan test --filter AuthTest
php artisan test --filter TagTest
php artisan test --filter TranslationTest
php artisan test --filter ExportTest
```

---

## Performance

| Endpoint | Target | Strategy |
|---|---|---|
| All endpoints | < 200ms | DB indexing + optimized queries |
| Export endpoint | < 500ms | Redis caching + selective columns |
| 100k+ records | Supported | Batch seeding + pagination |

### CDN Support
The JSON export endpoint returns standard JSON responses that can be cached and served via any CDN (CloudFront, Cloudflare, Fastly). To enable CDN caching, configure your CDN to cache `GET /api/export/{locale}` responses with an appropriate TTL. Cache invalidation should be triggered via CDN API whenever translations are updated.