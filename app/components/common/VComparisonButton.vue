<template>
  <UButton
    :variant="variant"
    :color="isCompared ? 'info' : 'neutral'"
    :size="size"
    :class="buttonClass"
    @click.stop.prevent="handleClick"
  >
    <Scale
      :class="[
        iconClass,
        'transition-transform',
        isAnimating ? 'scale-125' : '',
      ]"
    />
    <span v-if="showLabel" class="ml-2">
      {{
        isCompared ? $t("comparison.inComparison") : $t("comparison.compare")
      }}
    </span>
  </UButton>
</template>

<script lang="ts" setup>
import { Scale } from "lucide-vue-next";
import { useComparisonStore } from "~/stores/useComparisonStore";

interface Props {
  variantId: number;
  categoryId: number;
  variant?: "ghost" | "soft" | "outline" | "solid";
  size?: "xs" | "sm" | "md" | "lg" | "xl";
  iconClass?: string;
  buttonClass?: string;
  showLabel?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  variant: "ghost",
  size: "sm",
  iconClass: "w-5 h-5",
  buttonClass: "",
  showLabel: false,
});

const comparisonStore = useComparisonStore();

const isCompared = computed(() => comparisonStore.isCompared(props.variantId));
const isAnimating = ref(false);

const handleClick = async () => {
  isAnimating.value = true;

  await comparisonStore.toggle(props.variantId, props.categoryId);

  setTimeout(() => {
    isAnimating.value = false;
  }, 200);
};

onMounted(() => {
  comparisonStore.init();
});
</script>
