<template>
  <div
    v-if="isLoading"
    class="flex items-center justify-center dark:bg-gray-800"
    :class="containerClasses"
    :style="sizeStyles"
  >
    <UIcon
      name="i-lucide-loader-2"
      class="w-6 h-6 animate-spin text-gray-400"
    />
  </div>
  <div
    v-else-if="error"
    class="flex items-center justify-center dark:bg-gray-800"
    :class="containerClasses"
    :style="sizeStyles"
  >
    <UIcon name="i-lucide-image-off" class="w-6 h-6 text-gray-400" />
  </div>
  <img
    v-else-if="imageUrl"
    :src="imageUrl"
    :alt="alt"
    :class="imageClasses"
    :style="sizeStyles"
    @error="handleImageError"
  />
</template>

<script setup lang="ts">
interface Props {
  fileId: number;
  alt?: string;
  width?: string | number;
  height?: string | number;
  objectFit?: "contain" | "cover" | "fill" | "none" | "scale-down";
  rounded?: boolean;
  imgClass?: string;
}

const props = withDefaults(defineProps<Props>(), {
  alt: "Image",
  objectFit: "cover",
  rounded: false,
});

const { getFileBlob } = useFiles();

const imageUrl = ref<string | null>(null);
const isLoading = ref(true);
const error = ref(false);

const sizeStyles = computed(() => {
  const styles: Record<string, string> = {};

  if (props.width) {
    styles.width =
      typeof props.width === "number" ? `${props.width}px` : props.width;
  }

  if (props.height) {
    styles.height =
      typeof props.height === "number" ? `${props.height}px` : props.height;
  }

  return styles;
});

const containerClasses = computed(() => {
  return props.rounded ? "rounded-lg" : "";
});

const imageClasses = computed(() => {
  const classes = [];

  if (props.imgClass) {
    classes.push(props.imgClass);
  }

  if (props.objectFit) {
    const objectFitMap = {
      contain: "object-contain",
      cover: "object-cover",
      fill: "object-fill",
      none: "object-none",
      "scale-down": "object-scale-down",
    };
    classes.push(objectFitMap[props.objectFit]);
  }

  if (props.rounded) {
    classes.push("rounded-lg");
  }

  return classes.join(" ");
});

const loadImage = async () => {
  try {
    isLoading.value = true;
    error.value = false;

    const blob = await getFileBlob(props.fileId);
    imageUrl.value = URL.createObjectURL(blob);
  } catch (err) {
    console.error("Error loading image:", err);
    error.value = true;
  } finally {
    isLoading.value = false;
  }
};

const handleImageError = () => {
  error.value = true;
};

// Load image on mount and when fileId changes
watch(
  () => props.fileId,
  (newFileId) => {
    if (imageUrl.value) {
      URL.revokeObjectURL(imageUrl.value);
    }
    if (newFileId) {
      loadImage();
    }
  },
  { immediate: true }
);

// Cleanup on unmount
onUnmounted(() => {
  if (imageUrl.value) {
    URL.revokeObjectURL(imageUrl.value);
  }
});
</script>
