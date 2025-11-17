<template>
  <div
    :class="[
      'flex items-center justify-center font-bold',
      sizeClasses,
      shapeClasses,
      'bg-gradient-to-br from-primary-400 to-primary-600 text-white',
      borderClasses,
    ]"
  >
    {{ initials }}
  </div>
</template>

<script setup lang="ts">
interface Props {
  name: string;
  size?: "xs" | "sm" | "md" | "lg" | "xl" | "2xl";
  shape?: "circle" | "square";
  border?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  size: "md",
  shape: "square",
  border: false,
});

const initials = computed(() => {
  if (!props.name) return "";
  return props.name
    .split(" ")
    .map((n) => n[0])
    .join("")
    .toUpperCase()
    .slice(0, 2);
});

const sizeClasses = computed(() => {
  const sizes = {
    xs: "w-6 h-6 text-xs",
    sm: "w-8 h-8 text-sm",
    md: "w-10 h-10 text-base",
    lg: "w-16 h-16 text-xl",
    xl: "w-24 h-24 text-2xl",
    "2xl": "w-32 h-32 text-4xl",
  };
  return sizes[props.size];
});

const shapeClasses = computed(() => {
  return props.shape === "circle" ? "rounded-full" : "rounded-lg";
});

const borderClasses = computed(() => {
  if (!props.border) return "";
  return "border-4 border-white dark:border-gray-800 shadow-lg";
});
</script>
