# Laravel API з Docker

## Запуск проекту

### 1. Запустити Docker контейнери
```bash
docker compose up -d --build
```

### 2. Запустити міграції та сідери
```bash
docker compose exec app php artisan migrate:fresh --seed
```

## Доступи

### API
- **URL**: http://localhost:8000
- **Базовий шлях**: `/api`

### База даних MySQL
- **Host**: localhost
- **Port**: 3306
- **Database**: laravel
- **Username**: laravel
- **Password**: root

### phpMyAdmin
- **URL**: http://localhost:8080
- **Server**: db
- **Username**: root
- **Password**: root

### Тестовий користувач
- **Email**: admin@admin.com
- **Password**: superpassword
- **Role**: SuperAdmin
- **Permissions**: Create Role, Read Roles, Update Role, Delete Role

## API Endpoints

### Публічні роути
```bash
POST /api/register - Реєстрація нового користувача
POST /api/login    - Авторизація користувача
```

### Захищені роути (потребують Bearer токен)
```bash
GET    /api/auth/me       - Отримати інформацію про поточного користувача
POST   /api/auth/logout   - Вийти з системи
POST   /api/logout        - Вийти з системи (дублікат)
GET    /api/user          - Отримати поточного користувача

# Управління ролями
GET    /api/roles         - Список всіх ролей (permission: Read Roles)
POST   /api/roles         - Створити нову роль (permission: Create Role)
GET    /api/roles/{id}    - Отримати роль за ID (permission: Read Roles)
PUT    /api/roles/{id}    - Оновити роль (permission: Update Role)
PATCH  /api/roles/{id}    - Оновити роль (permission: Update Role)
DELETE /api/roles/{id}    - Видалити роль (permission: Delete Role)
```

## Приклади використання

### 1. Авторизація
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@admin.com","password":"superpassword"}'
```

**Відповідь:**
```json
{
  "message": "Login successful",
  "user": {...},
  "token": "1|xxxxxx..."
}
```

### 2. Отримати інформацію про користувача
```bash
curl -X GET http://localhost:8000/api/auth/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3. Отримати список ролей
```bash
curl -X GET http://localhost:8000/api/roles \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. Створити нову роль
```bash
curl -X POST http://localhost:8000/api/roles \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"name":"Manager","description":"Manager role with limited permissions"}'
```

## Корисні команди

### Керування контейнерами
```bash
# Переглянути логи
docker compose logs -f app

# Зупинити контейнери
docker compose down

# Перезапустити контейнери
docker compose restart

# Перезапустити тільки app
docker compose restart app
```

### Робота з Laravel в контейнері
```bash
# Виконати artisan команду
docker compose exec app php artisan COMMAND

# Перевірити статус міграцій
docker compose exec app php artisan migrate:status

# Створити нову міграцію
docker compose exec app php artisan make:migration NAME

# Переглянути список роутів
docker compose exec app php artisan route:list

# Очистити кеш
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
```

### Робота з базою даних
```bash
# Підключитися до MySQL консолі
docker compose exec db mysql -uroot -proot laravel

# Експорт бази даних
docker compose exec db mysqldump -uroot -proot laravel > backup.sql

# Імпорт бази даних
docker compose exec -T db mysql -uroot -proot laravel < backup.sql
```

## Структура проекту

```
api/
├── docker-compose.yml       # Docker Compose конфігурація
├── Dockerfile              # Docker образ для Laravel
├── docker/
│   └── php/
│       └── local.ini       # PHP конфігурація
├── app/
│   ├── Models/             # Моделі (User, Role, Permission)
│   ├── Http/
│   │   ├── Controllers/    # Контролери (AuthController, RoleController)
│   │   ├── Middleware/     # Middleware (CheckPermission)
│   │   └── Resources/      # API Resources
│   └── ...
├── database/
│   ├── migrations/         # Міграції
│   └── seeders/           # Сідери (RoleSeeder, UserSeeder)
└── routes/
    └── api.php            # API роути
```

## Система ролей та дозволів

### Моделі
- **User** - користувачі системи
- **Role** - ролі (наприклад, SuperAdmin, Manager, User)
- **Permission** - дозволи (наприклад, Create Role, Read Roles)

### Зв'язки
- User belongsToMany Role (many-to-many через `role_user`)
- Role belongsToMany Permission (many-to-many через `permission_role`)

### Middleware
- **CheckPermission** - перевіряє чи має користувач необхідний дозвіл

### Використання
```php
// В роутах
Route::get('roles', [RoleController::class, 'index'])
    ->middleware('permission:Read Roles');

// В контролері
if (!$user->hasPermission('Create Role')) {
    abort(403);
}

// Призначити роль користувачу
$user->assignRole('SuperAdmin');

// Видалити роль
$user->removeRole('Manager');

// Перевірити роль
if ($user->hasRole('SuperAdmin')) {
    // ...
}
```

## Troubleshooting

### Помилка доступу до бази даних
Переконайтесь що в `.env` встановлені правильні налаштування:
```env
DB_HOST=db
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=root
```

### Контейнер не запускається
```bash
# Переглянути логи
docker compose logs app

# Перебудувати контейнери
docker compose down
docker compose up -d --build
```
