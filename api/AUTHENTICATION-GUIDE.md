# Гайд по автентифікації з Sanctum (Token-based)

## Як працює автентифікація через токени

### 1. Логін

**Запит:**
```javascript
const response = await fetch('http://localhost:8000/api/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({
    email: 'admin@admin.com',
    password: 'superpassword'
  })
});

const data = await response.json();
console.log(data);
```

**Відповідь:**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "admin",
    "email": "admin@admin.com",
    ...
  },
  "token": "1|xxxxxxxxxxxxxx"
}
```

**ВАЖЛИВО:** Збережіть токен з відповіді!

### 2. Збереження токену

```javascript
// Зберегти в localStorage
localStorage.setItem('auth_token', data.token);

// Або в sessionStorage
sessionStorage.setItem('auth_token', data.token);

// Або в Pinia/Vuex store
store.commit('SET_TOKEN', data.token);
```

### 3. Використання токену в наступних запитах

**Всі захищені запити ПОВИННІ містити заголовок `Authorization`:**

```javascript
const token = localStorage.getItem('auth_token');

const response = await fetch('http://localhost:8000/api/auth/me', {
  method: 'GET',
  headers: {
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`  // ВАЖЛИВО!
  }
});

const userData = await response.json();
```

### 4. Приклад для Axios

```javascript
// Встановити токен один раз для всіх запитів
const token = localStorage.getItem('auth_token');
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

// Або для кожного запиту окремо
axios.get('http://localhost:8000/api/auth/me', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

### 5. Приклад для Fetch API з інтерцептором

```javascript
// Створіть wrapper для fetch
async function authFetch(url, options = {}) {
  const token = localStorage.getItem('auth_token');

  const headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    ...options.headers,
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  return fetch(url, {
    ...options,
    headers,
  });
}

// Використання
const response = await authFetch('http://localhost:8000/api/auth/me');
const data = await response.json();
```

## Nuxt 3 приклад

### composables/useAuth.ts

```typescript
export const useAuth = () => {
  const token = useCookie('auth_token');
  const user = useState('user', () => null);

  const login = async (email: string, password: string) => {
    const response = await $fetch('http://localhost:8000/api/login', {
      method: 'POST',
      body: { email, password },
    });

    token.value = response.token;
    user.value = response.user;

    return response;
  };

  const fetchUser = async () => {
    if (!token.value) return null;

    try {
      const response = await $fetch('http://localhost:8000/api/auth/me', {
        headers: {
          'Authorization': `Bearer ${token.value}`
        }
      });

      user.value = response.user;
      return response;
    } catch (error) {
      // Токен невалідний, очистити
      token.value = null;
      user.value = null;
      throw error;
    }
  };

  const logout = async () => {
    if (token.value) {
      await $fetch('http://localhost:8000/api/logout', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token.value}`
        }
      });
    }

    token.value = null;
    user.value = null;
  };

  return {
    token,
    user,
    login,
    fetchUser,
    logout,
  };
};
```

### plugins/auth.ts

```typescript
export default defineNuxtPlugin(async () => {
  const { token, fetchUser } = useAuth();

  // Автоматично завантажити дані користувача при старті
  if (token.value) {
    try {
      await fetchUser();
    } catch (error) {
      console.error('Failed to fetch user:', error);
    }
  }
});
```

### middleware/auth.ts

```typescript
export default defineNuxtRouteMiddleware((to, from) => {
  const { token } = useAuth();

  if (!token.value) {
    return navigateTo('/login');
  }
});
```

## Використання в компонентах

```vue
<script setup>
const { user, login, logout } = useAuth();

const email = ref('');
const password = ref('');
const error = ref('');

const handleLogin = async () => {
  try {
    await login(email.value, password.value);
    navigateTo('/dashboard');
  } catch (err) {
    error.value = 'Невірний email або пароль';
  }
};
</script>

<template>
  <div>
    <div v-if="user">
      <p>Привіт, {{ user.name }}!</p>
      <button @click="logout">Вийти</button>
    </div>

    <form v-else @submit.prevent="handleLogin">
      <input v-model="email" type="email" placeholder="Email" />
      <input v-model="password" type="password" placeholder="Пароль" />
      <button type="submit">Увійти</button>
      <p v-if="error">{{ error }}</p>
    </form>
  </div>
</template>
```

## Типові помилки

### 1. "Unauthenticated" помилка

**Причина:** Токен не відправляється в запиті або відправляється неправильно.

**Рішення:**
- Перевірте, чи токен зберігається після логіну
- Перевірте, чи додається заголовок `Authorization: Bearer {token}` до запиту
- Переконайтесь, що токен має формат `1|xxxxxxxxxxxx`

### 2. CORS помилки

**Причина:** Backend не дозволяє запити з вашого домену.

**Рішення:** Перевірте `config/cors.php` - повинно бути `'allowed_origins' => ['*']` або ваш конкретний домен.

### 3. Токен не працює після перезавантаження сторінки

**Причина:** Токен не зберігається або не завантажується з localStorage/cookie.

**Рішення:** Перевірте, що ви зберігаєте токен в localStorage або cookie, і завантажуєте його при ініціалізації додатка.

## Тестування через curl

```bash
# 1. Логін
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"admin@admin.com","password":"superpassword"}'

# 2. Скопіюйте токен з відповіді

# 3. Використайте токен
curl -X GET http://localhost:8000/api/auth/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer ВАШ_ТОКЕН_ТУТ"
```

## Налагодження

Якщо щось не працює, перевірте:

1. **Чи токен правильно зберігається?**
   ```javascript
   console.log('Token:', localStorage.getItem('auth_token'));
   ```

2. **Чи токен відправляється в запиті?**
   Відкрийте DevTools → Network → виберіть запит → Headers → перевірте наявність `Authorization: Bearer ...`

3. **Чи токен валідний?**
   Зробіть тестовий запит через curl з вашим токеном

4. **Чи правильно налаштований CORS?**
   Перевірте Console в браузері на наявність CORS помилок
