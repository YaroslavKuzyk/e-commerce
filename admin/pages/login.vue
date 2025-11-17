<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 px-4 py-12"
  >
    <div class="w-full max-w-md">
      <!-- Logo and Title -->
      <div class="text-center mb-8">
        <div
          class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 mb-4"
        >
          <UIcon
            name="i-lucide-shopping-cart"
            class="w-8 h-8 text-primary-600 dark:text-primary-400"
          />
        </div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
          Ласкаво просимо
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
          Увійдіть до адміністративної панелі
        </p>
      </div>

      <!-- Login Card -->
      <UCard class="shadow-xl backdrop-blur-sm bg-white/90 dark:bg-gray-800/90">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Email Field -->
          <UFormGroup label="Email" name="email" required>
            <UInput
              class="w-full mb-4"
              v-model="credentials.email"
              type="email"
              placeholder="Введіть ваш email"
              icon="i-lucide-mail"
              size="lg"
              :disabled="loading"
              autocomplete="email"
            />
          </UFormGroup>

          <!-- Password Field -->
          <UFormGroup label="Пароль" name="password" required>
            <UInput
              class="w-full mb-4"
              v-model="credentials.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Введіть ваш пароль"
              icon="i-lucide-lock"
              size="lg"
              :disabled="loading"
              autocomplete="current-password"
            >
              <template #trailing>
                <UButton
                  variant="ghost"
                  color="neutral"
                  size="xs"
                  :icon="showPassword ? 'i-lucide-eye-off' : 'i-lucide-eye'"
                  @click="showPassword = !showPassword"
                  tabindex="-1"
                />
              </template>
            </UInput>
          </UFormGroup>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-end w-full">
            <NuxtLink
              to="/forgot-password"
              class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 transition-colors flex-shrink-0"
            >
              Забули пароль?
            </NuxtLink>
          </div>

          <!-- Error Alert -->
          <UAlert
            v-if="error"
            color="error"
            variant="soft"
            :title="error"
            icon="i-lucide-alert-circle"
            :close-button="{
              icon: 'i-lucide-x',
              color: 'error',
              variant: 'ghost',
            }"
            @close="error = ''"
          />

          <!-- Submit Button -->
          <UButton
            type="submit"
            block
            size="lg"
            :loading="loading"
            :disabled="loading || !credentials.email || !credentials.password"
            icon="i-lucide-log-in"
          >
            {{ loading ? "Вхід..." : "Увійти" }}
          </UButton>
        </form>
      </UCard>

      <!-- Footer -->
      <div class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
        <p>© 2025 E-Commerce Admin. Всі права захищені.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: "guest",
});

const authStore = useAuthStore();

const credentials = ref({
  email: "",
  password: "",
});

const loading = ref(false);
const error = ref("");
const showPassword = ref(false);

const handleLogin = async () => {
  loading.value = true;
  error.value = "";

  try {
    await authStore.login(credentials.value);

    // Show success message
    useToast().add({
      title: "Успішно!",
      description: "Ви успішно увійшли в систему",
      icon: "i-lucide-check-circle",
      color: "success",
    });

    navigateTo("/dashboard");
  } catch (err: any) {
    console.error("Login error:", err);

    error.value = err.data?.message || "Помилка входу. Перевірте ваші дані.";

    // Show error toast
    useToast().add({
      title: "Помилка входу",
      description: error.value,
      icon: "i-lucide-alert-circle",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};

// Auto-fill for development (remove in production)
onMounted(() => {
  if (process.dev) {
    credentials.value = {
      email: "",
      password: "",
    };
  }
});
</script>
