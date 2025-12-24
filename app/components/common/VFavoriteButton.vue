<template>
  <UButton
    :variant="variant"
    :color="isFavorite ? 'error' : 'neutral'"
    :size="size"
    :class="buttonClass"
    @click.stop.prevent="handleClick"
  >
    <Heart
      :class="[
        iconClass,
        isFavorite ? 'fill-current' : '',
        'transition-transform',
        isAnimating ? 'scale-125' : '',
      ]"
    />
  </UButton>
</template>

<script lang="ts" setup>
import { Heart } from "lucide-vue-next";
import { useFavoriteStore } from "~/stores/useFavoriteStore";

interface Props {
  variantId: number;
  variant?: "ghost" | "soft" | "outline" | "solid";
  size?: "xs" | "sm" | "md" | "lg" | "xl";
  iconClass?: string;
  buttonClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  variant: "ghost",
  size: "sm",
  iconClass: "w-5 h-5",
  buttonClass: "",
});

const favoriteStore = useFavoriteStore();

const isFavorite = computed(() => favoriteStore.isFavorite(props.variantId));
const isAnimating = ref(false);

const handleClick = async () => {
  isAnimating.value = true;

  await favoriteStore.toggle(props.variantId);

  setTimeout(() => {
    isAnimating.value = false;
  }, 200);
};

// Initialize store on mount
onMounted(() => {
  favoriteStore.init();
});
</script>
