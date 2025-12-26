<template>
  <UCard class="relative">
    <div class="flex items-start justify-between gap-4">
      <div class="flex items-start gap-4">
        <div class="flex-shrink-0">
          <div
            class="w-12 h-12 rounded-lg flex items-center justify-center"
            :class="iconBgClass"
          >
            <UIcon :name="iconName" class="text-2xl" :class="iconColorClass" />
          </div>
        </div>

        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              {{ item.name_uk || item.name }}
            </h3>
            <UBadge
              :color="item.is_active ? 'success' : 'neutral'"
              variant="subtle"
              size="xs"
            >
              {{ item.is_active ? 'Активно' : 'Неактивно' }}
            </UBadge>
          </div>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ item.description_uk || item.description }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <HasPermissions :required-permissions="['Update System Settings']" hide-message>
          <USwitch
            :model-value="item.is_active || false"
            @update:model-value="$emit('toggle', item.type, $event)"
          />
        </HasPermissions>

        <HasPermissions :required-permissions="['Update System Settings']" hide-message>
          <UButton
            icon="i-heroicons-cog-6-tooth"
            color="neutral"
            variant="ghost"
            @click="$emit('edit', item)"
          />
        </HasPermissions>
      </div>
    </div>

    <!-- Show configured fields preview -->
    <div v-if="hasConfiguredFields" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
      <div class="flex flex-wrap gap-2">
        <UBadge
          v-for="field in configuredFields"
          :key="field"
          color="primary"
          variant="subtle"
          size="xs"
        >
          {{ formatFieldName(field) }}: ****
        </UBadge>
      </div>
    </div>
  </UCard>
</template>

<script setup lang="ts">
import HasPermissions from "~/components/common/VHasPermissions.vue";
import type { SystemSettingType, SystemSettingTypeInfo } from "~/models/systemSettings";

interface Props {
  item: SystemSettingTypeInfo;
}

const props = defineProps<Props>();

defineEmits<{
  edit: [item: SystemSettingTypeInfo];
  toggle: [type: SystemSettingType, isActive: boolean];
}>();

const iconConfig: Record<string, { icon: string; bgClass: string; colorClass: string }> = {
  nova_poshta: {
    icon: "i-heroicons-truck",
    bgClass: "bg-red-100 dark:bg-red-900/30",
    colorClass: "text-red-600 dark:text-red-400",
  },
  ukrposhta: {
    icon: "i-heroicons-envelope",
    bgClass: "bg-yellow-100 dark:bg-yellow-900/30",
    colorClass: "text-yellow-600 dark:text-yellow-400",
  },
  meest: {
    icon: "i-heroicons-cube",
    bgClass: "bg-blue-100 dark:bg-blue-900/30",
    colorClass: "text-blue-600 dark:text-blue-400",
  },
  monopay: {
    icon: "i-heroicons-banknotes",
    bgClass: "bg-purple-100 dark:bg-purple-900/30",
    colorClass: "text-purple-600 dark:text-purple-400",
  },
  liqpay: {
    icon: "i-heroicons-credit-card",
    bgClass: "bg-green-100 dark:bg-green-900/30",
    colorClass: "text-green-600 dark:text-green-400",
  },
  sms_club: {
    icon: "i-heroicons-chat-bubble-left-right",
    bgClass: "bg-orange-100 dark:bg-orange-900/30",
    colorClass: "text-orange-600 dark:text-orange-400",
  },
  turbosms: {
    icon: "i-heroicons-bolt",
    bgClass: "bg-cyan-100 dark:bg-cyan-900/30",
    colorClass: "text-cyan-600 dark:text-cyan-400",
  },
};

const iconName = computed(() => iconConfig[props.item.type]?.icon || "i-heroicons-cog-6-tooth");
const iconBgClass = computed(() => iconConfig[props.item.type]?.bgClass || "bg-gray-100 dark:bg-gray-800");
const iconColorClass = computed(() => iconConfig[props.item.type]?.colorClass || "text-gray-600 dark:text-gray-400");

const configuredFields = computed(() => {
  if (!props.item.data) return [];
  return Object.entries(props.item.data)
    .filter(([_, value]) => value && String(value).length > 0)
    .map(([key]) => key);
});

const hasConfiguredFields = computed(() => configuredFields.value.length > 0);

const formatFieldName = (field: string) => {
  return field
    .replace(/_/g, " ")
    .replace(/\b\w/g, (l) => l.toUpperCase());
};
</script>
