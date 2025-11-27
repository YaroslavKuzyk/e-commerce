<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 px-4 py-12 relative"
  >
    <div class="absolute top-4 right-4">
      <VColorModeSwitch />
    </div>
    <div class="w-full max-w-md">
      <!-- Logo and Title -->
      <div class="text-center mb-8">
        <div
          class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 mb-4"
        >
          <ShoppingCart class="w-8 h-8 text-primary-600 dark:text-primary-400" />
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
              size="lg"
              :disabled="loading"
              autocomplete="email"
            >
              <template #leading>
                <Mail class="w-4 h-4 text-gray-400" />
              </template>
            </UInput>
          </UFormGroup>

          <!-- Password Field -->
          <UFormGroup label="Пароль" name="password" required>
            <UInput
              class="w-full mb-4"
              v-model="credentials.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Введіть ваш пароль"
              size="lg"
              :disabled="loading"
              autocomplete="current-password"
            >
              <template #leading>
                <Lock class="w-4 h-4 text-gray-400" />
              </template>
              <template #trailing>
                <UButton
                  variant="ghost"
                  color="neutral"
                  size="xs"
                  @click="showPassword = !showPassword"
                  tabindex="-1"
                >
                  <EyeOff v-if="showPassword" class="w-4 h-4" />
                  <Eye v-else class="w-4 h-4" />
                </UButton>
              </template>
            </UInput>
          </UFormGroup>

          <!-- Error Alert -->
          <UAlert
            v-if="error"
            color="error"
            variant="soft"
            :title="error"
            @close="error = ''"
          >
            <template #icon>
              <AlertCircle class="w-5 h-5" />
            </template>
            <template #close>
              <UButton
                color="error"
                variant="ghost"
                size="xs"
                @click="error = ''"
              >
                <X class="w-4 h-4" />
              </UButton>
            </template>
          </UAlert>

          <!-- Submit Button -->
          <UButton
            type="submit"
            block
            size="lg"
            :loading="loading"
            :disabled="loading || !credentials.email || !credentials.password"
          >
            <template #leading>
              <LogIn class="w-5 h-5" />
            </template>
            {{ loading ? "Вхід..." : "Увійти" }}
          </UButton>
        </form>
      </UCard>

      <!-- Footer -->
      <div class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
        <p>© {{ currentYear }} Admin iD. Всі права захищені.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import VColorModeSwitch from '~/components/common/VColorModeSwitch.vue';
import { ShoppingCart, Mail, Lock, Eye, EyeOff, AlertCircle, X, LogIn, CheckCircle } from 'lucide-vue-next';

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
      color: "success",
    });

    navigateTo("/");
  } catch (err: any) {
    console.error("Login error:", err);

    error.value = err.data?.message || "Помилка входу. Перевірте ваші дані.";

    // Show error toast
    useToast().add({
      title: "Помилка входу",
      description: error.value,
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

const currentYear = new Date().getFullYear();
</script>
