<template>
  <VSidebarContent title="Профіль">
    <template #toolbar>
      <div class="flex items-center justify-end gap-2 w-full">
        <UButton
          to="/settings"
          variant="solid"
          color="primary"
        >
          <template #leading>
            <Settings class="w-5 h-5" />
          </template>
          Налаштування профілю
        </UButton>
      </div>
    </template>

    <div class="p-6">
      <div v-if="user" class="grid grid-cols-1 lg:grid-cols-[400px_1fr] gap-6">
        <!-- Left Column - Profile Card -->
        <div class="space-y-6">
          <!-- Profile Card -->
          <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden"
          >
            <!-- Avatar Section -->
            <div class="relative">
              <div
                class="h-32 bg-gradient-to-br from-primary-400 to-primary-600"
              ></div>
              <div
                class="absolute -bottom-16 left-1/2 transform -translate-x-1/2"
              >
                <VAvatar :name="user.name" :file-id="user.avatar_file_id" size="2xl" :border="true" />
              </div>
            </div>

            <!-- Profile Info -->
            <div class="pt-20 pb-6 px-6 text-center">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                {{ user.name }}
              </h2>
              <div
                v-if="user.role"
                class="inline-flex items-center justify-center mb-4"
              >
                <UBadge color="neutral" size="md" variant="subtle">
                  {{ user.role.name }}
                </UBadge>
              </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 dark:border-gray-700"></div>

            <!-- Details Section -->
            <div class="p-6">
              <h3
                class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4"
              >
                Деталі
              </h3>
              <dl class="space-y-3">
                <div>
                  <dt
                    class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1"
                  >
                    Ім'я:
                  </dt>
                  <dd class="text-sm text-gray-900 dark:text-white">
                    {{ user.name }}
                  </dd>
                </div>
                <div>
                  <dt
                    class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1"
                  >
                    Email:
                  </dt>
                  <dd class="text-sm text-gray-900 dark:text-white">
                    {{ user.email }}
                  </dd>
                </div>
                <div>
                  <dt
                    class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1"
                  >
                    Роль:
                  </dt>
                  <dd class="text-sm text-gray-900 dark:text-white">
                    {{ user.role?.name || "Не призначена" }}
                  </dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- Right Column - Permissions -->
        <div>
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-center gap-3">
                <div
                  class="p-2 bg-primary-100 dark:bg-primary-900/20 rounded-lg w-10 h-10 flex items-center justify-center"
                >
                  <UIcon
                    name="i-heroicons-lock-closed"
                    class="w-5 h-5 text-primary-600 dark:text-primary-400"
                  />
                </div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                  Мої дозволи
                </h2>
              </div>
            </div>

            <div class="p-6">
              <div
                v-if="
                  user.role?.permissions && user.role.permissions.length > 0
                "
                class="space-y-0 divide-y divide-gray-100 dark:divide-gray-700"
              >
                <div
                  v-for="permission in user.role.permissions"
                  :key="permission.id"
                  class="py-3 first:pt-0 last:pb-0"
                >
                  <div class="text-sm text-gray-700 dark:text-gray-300">
                    {{ permission.name }}
                  </div>
                </div>
              </div>
              <div
                v-else
                class="text-center py-12 text-gray-500 dark:text-gray-400"
              >
                <UIcon
                  name="i-heroicons-lock-open"
                  class="w-12 h-12 mx-auto mb-3 opacity-50"
                />
                <p>Немає призначених дозволів</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center text-gray-500 dark:text-gray-400">
        Не вдалося завантажити дані профілю
      </div>
    </div>
  </VSidebarContent>
</template>

<script setup lang="ts">
import { Settings } from "lucide-vue-next";
import type { IUser } from "~/models/auth";
import VSidebarContent from "~/components/sidebar/VSidebarContent.vue";

definePageMeta({
  middleware: ["sanctum:auth"],
});

const userData = useSanctumUser<{ data: IUser }>();

const user = computed(() => userData.value?.data);
</script>
