<template>
  <UButton
    :variant="variant"
    :color="isInComparison ? 'primary' : 'neutral'"
    :size="size"
    :loading="isToggling"
    :class="buttonClass"
    @click.stop.prevent="handleToggle"
  >
    <template #leading>
      <GitCompareArrows :class="['transition-colors', iconClass, isInComparison ? 'text-primary' : '']" />
    </template>
    <span v-if="showLabel">
      {{ isInComparison ? $t('comparison.removeFromComparison') : $t('comparison.addToComparison') }}
    </span>
  </UButton>
</template>

<script lang="ts" setup>
import { GitCompareArrows } from "lucide-vue-next";
import { useComparisonStore } from "~/stores/useComparisonStore";

interface Props {
  productId: number;
  categoryId: number;
  variant?: 'solid' | 'outline' | 'soft' | 'ghost' | 'link';
  size?: 'xs' | 'sm' | 'md' | 'lg' | 'xl';
  showLabel?: boolean;
  iconClass?: string;
  buttonClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'ghost',
  size: 'md',
  showLabel: false,
  iconClass: 'w-5 h-5',
  buttonClass: '',
});

const comparisonStore = useComparisonStore();

const isInComparison = computed(() => comparisonStore.isInComparison(props.productId));
const isToggling = ref(false);

const handleToggle = async () => {
  if (isToggling.value) return;

  isToggling.value = true;
  try {
    await comparisonStore.toggle(props.productId, props.categoryId);
  } finally {
    isToggling.value = false;
  }
};
</script>
