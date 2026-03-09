# Symfony 7 API & Web Application

PHP boilerplate project using **Symfony 7**, **PostgreSQL**, **Docker**, and **PHPUnit**.

## Stack

- **PHP 8.2+** with Symfony 7.4
- **PostgreSQL 16**
- **Docker** & **Docker Compose**
- **PHPUnit** for testing
- **API** (JSON) and **Web** (Twig) frontend

## Quick Start

### 1. Start with Docker

```bash
docker compose up -d
```

### 2. Install dependencies (inside container)

```bash
docker compose exec php composer install
```

### 3. Run migrations

```bash
docker compose exec php bin/console doctrine:migrations:migrate --no-interaction
```

### 4. Access the application

- **Web**: http://localhost:8080 (Docker) or http://localhost:8000 (Symfony CLI)
- **API**: http://localhost:8080/api/health or http://localhost:8000/api/health

## Project Structure

```
├── config/           # Symfony configuration
├── docker/           # Docker configs (nginx)
├── migrations/       # Doctrine migrations
├── public/           # Web root (index.php)
├── src/
│   ├── Controller/
│   │   ├── Api/      # API controllers (JSON)
│   │   └── Web/      # Web controllers (Twig)
│   └── Kernel.php
├── templates/        # Twig templates
├── tests/            # PHPUnit tests
├── Dockerfile
└── docker-compose.yml
```

## Messenger (AMQP & Redis)

Task events (`TaskCreatedEvent`, `TaskStatusUpdatedEvent`) are dispatched via **Symfony Messenger** to async transports:

- **TaskCreatedEvent** → `async_amqp` (RabbitMQ), port 5672 (AMQP), 15672 (management UI)
- **TaskStatusUpdatedEvent** → `async_redis` (Redis), port 6379

Run workers to consume messages (e.g. write task history):

```bash
# Consume AMQP (task created)
docker compose exec php bin/console messenger:consume async_amqp -vv

# Consume Redis (status updated)
docker compose exec php bin/console messenger:consume async_redis -vv
```

In tests, transports are overridden to `sync://` so no brokers are required.

## Commands

| Command | Description |
|---------|-------------|
| `docker compose up -d` | Start services |
| `docker compose exec php bin/console cache:clear` | Clear cache |
| `docker compose exec php bin/console doctrine:migrations:diff` | Generate migration |
| `docker compose exec php bin/console messenger:consume async_amqp` | Consume AMQP messages |
| `docker compose exec php bin/console messenger:consume async_redis` | Consume Redis messages |
| `docker compose exec php bin/phpunit` | Run tests |

## Environment

- `.env` – base config
- `.env.local` – local overrides (gitignored)
- `.env.test` – test environment

## API Routes

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/health` | Health check |

## Adding New API Endpoints

1. Create controller in `src/Controller/Api/`
2. Add route in `config/routes/api.yaml` or use attributes

## Adding New Web Pages

1. Create controller in `src/Controller/Web/`
2. Add template in `templates/web/`
3. Use `base.html.twig` as layout
