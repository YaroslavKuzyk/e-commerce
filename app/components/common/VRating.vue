<template>
  <div
    class="v-rating"
    :class="{
      'v-rating--readonly': readonly,
      'v-rating--interactive': !readonly,
    }"
    @mouseleave="onMouseLeave"
  >
    <span
      v-for="star in 5"
      :key="star"
      class="v-rating__star"
      @mouseenter="onMouseEnter(star)"
      @click="onStarClick(star)"
    >
      <IconStar v-if="getStarType(star) === 'full'" />
      <IconStarHalf v-else-if="getStarType(star) === 'half'" />
      <IconStarEmpty v-else />
    </span>
  </div>
</template>

<script setup lang="ts">
import IconStar from "~/components/icons/IconStar.vue";
import IconStarEmpty from "~/components/icons/IconStarEmpty.vue";
import IconStarHalf from "~/components/icons/IconStarHalf.vue";

const props = withDefaults(
  defineProps<{
    modelValue?: number;
    readonly?: boolean;
  }>(),
  {
    modelValue: 0,
    readonly: false,
  }
);

const emit = defineEmits<{
  "update:modelValue": [value: number];
}>();

const hoverValue = ref<number | null>(null);

const displayValue = computed(() => {
  if (!props.readonly && hoverValue.value !== null) {
    return hoverValue.value;
  }
  return props.modelValue;
});

function getStarType(star: number): "full" | "half" | "empty" {
  const value = displayValue.value;

  if (star <= Math.floor(value)) {
    return "full";
  }

  if (star === Math.ceil(value) && value % 1 > 0) {
    return "half";
  }

  return "empty";
}

function onMouseEnter(star: number) {
  if (props.readonly) return;
  hoverValue.value = star;
}

function onMouseLeave() {
  if (props.readonly) return;
  hoverValue.value = null;
}

function onStarClick(star: number) {
  if (props.readonly) return;
  emit("update:modelValue", star);
}
</script>

<style scoped lang="scss">
.v-rating {
  display: inline-flex;
  align-items: center;
  gap: 4px;

  &__star {
    display: flex;
    align-items: center;
    justify-content: center;

    svg {
      width: 20px;
      height: 20px;
    }
  }

  &--interactive {
    .v-rating__star {
      cursor: pointer;
      transition: transform 0.15s ease;

      &:hover {
        transform: scale(1.1);
      }
    }
  }

  &--readonly {
    .v-rating__star {
      cursor: default;
    }
  }
}
</style>
