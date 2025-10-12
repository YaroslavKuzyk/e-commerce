# Laravel + Nuxt 3 Docker Setup

Цей проект містить Laravel API backend та Nuxt 3 frontend, які працюють у Docker контейнерах.

## Структура проекту

```
.
├── api/                    # Laravel API
│   ├── Dockerfile
│   └── ...
├── app/                    # Nuxt 3 Frontend
│   ├── Dockerfile
│   ├── .dockerignore
│   └── ...
└── docker-compose.yml      # Головний Docker Compose файл
```

## Сервіси

- **api** - Laravel API (порт 8000)
- **nuxt** - Nuxt 3 Frontend (порт 3000)
- **db** - MySQL 8.0 Database (порт 3306)
- **phpmyadmin** - PHPMyAdmin (порт 8080)

## Швидкий старт

### 1. Клонувати репозиторій та підготувати .env файли

```bash
# Копіювати .env для Laravel
cd api
cp .env.example .env
cd ..

# Копіювати .env для Nuxt
cd app
cp .env.example .env
cd ..
```

### 2. Запустити всі сервіси

```bash
# З кореневої директорії проекту
docker-compose up -d
```

### 3. Налаштувати Laravel (перший раз)

```bash
# Згенерувати ключ додатку
docker-compose exec api php artisan key:generate

# Запустити міграції
docker-compose exec api php artisan migrate

# (Опціонально) Запустити seeders
docker-compose exec api php artisan db:seed
```

### 4. Налаштувати Nuxt (перший раз)

```bash
# Встановити залежності (якщо потрібно)
docker-compose exec nuxt npm install
```

## Доступ до сервісів

- **Nuxt Frontend:** http://localhost:3000
- **Laravel API:** http://localhost:8000
- **PHPMyAdmin:** http://localhost:8080
- **Nuxt DevTools:** http://localhost:24678

## Корисні команди

### Загальні команди

```bash
# Запустити всі сервіси
docker-compose up -d

# Зупинити всі сервіси
docker-compose down

# Переглянути логи
docker-compose logs -f

# Переглянути логи конкретного сервісу
docker-compose logs -f api
docker-compose logs -f nuxt

# Перезібрати контейнери
docker-compose up -d --build

# Зупинити та видалити всі контейнери, мережі та volumes
docker-compose down -v
```

### Laravel API команди

```bash
# Виконати artisan команду
docker-compose exec api php artisan [команда]

# Приклади:
docker-compose exec api php artisan migrate
docker-compose exec api php artisan db:seed
docker-compose exec api php artisan cache:clear
docker-compose exec api php artisan config:clear

# Відкрити bash в Laravel контейнері
docker-compose exec api bash

# Встановити composer залежності
docker-compose exec api composer install
```

### Nuxt Frontend команди

```bash
# Виконати npm команду
docker-compose exec nuxt npm [команда]

# Приклади:
docker-compose exec nuxt npm install
docker-compose exec nuxt npm run build

# Відкрити shell в Nuxt контейнері
docker-compose exec nuxt sh
```

### База даних

```bash
# Підключитися до MySQL
docker-compose exec db mysql -u root -p

# Експортувати базу даних
docker-compose exec db mysqldump -u root -p laravel > backup.sql

# Імпортувати базу даних
docker-compose exec -T db mysql -u root -p laravel < backup.sql
```

## Змінні оточення

### Laravel API (api/.env)

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=root
```

### Nuxt Frontend (app/.env)

```env
NUXT_SANCTUM_BASE_URL=http://localhost:8000
NUXT_SANCTUM_ORIGIN=http://localhost:3000
```

**Важливо:** Nuxt контейнер використовує `network_mode: host`, що дозволяє йому напряму звертатися до `localhost:8000` як до Laravel API. Це вирішує проблему SSR, коли Nuxt робить запити на сервері (всередині контейнера) та в браузері (на клієнті) - обидва використовують localhost.

## Розробка

### Hot Reload

- **Nuxt:** Автоматично перезавантажується при зміні файлів
- **Laravel:** Зміни застосовуються автоматично завдяки volume mounting

### Встановлення нових пакетів

```bash
# Laravel
docker-compose exec api composer require [пакет]

# Nuxt
docker-compose exec nuxt npm install [пакет]
```

## Troubleshooting

### Порт вже зайнятий

Якщо порт 3000, 8000, або 3306 вже використовується, змініть маппінг портів у `docker-compose.yml`:

```yaml
ports:
  - "3001:3000"  # Змінити перше число
```

### Проблеми з правами доступу

```bash
# Laravel storage permissions
docker-compose exec api chmod -R 777 storage bootstrap/cache
```

### Очистити все та почати заново

```bash
docker-compose down -v
docker-compose up -d --build
```

### Nuxt не може підключитися до API

Переконайтеся, що в `app/.env`:
- `NUXT_SANCTUM_BASE_URL=http://localhost:8000` (для браузера)
- В docker-compose.yml: `NUXT_SANCTUM_BASE_URL=http://api:8000` (для серверного SSR)

## Production Build

### Laravel

```bash
docker-compose exec api composer install --optimize-autoloader --no-dev
docker-compose exec api php artisan config:cache
docker-compose exec api php artisan route:cache
docker-compose exec api php artisan view:cache
```

### Nuxt

```bash
docker-compose exec nuxt npm run build
```

Або змініть команду в docker-compose.yml з `npm run dev` на `node .output/server/index.mjs`

## Підтримка

При виникненні проблем перевірте логи:

```bash
docker-compose logs -f
```
