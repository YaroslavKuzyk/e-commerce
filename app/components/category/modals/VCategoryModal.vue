<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="model"
        class="fixed inset-0 bg-black/50 z-40"
        @click="model = false"
      />
    </Transition>

    <Transition name="modal">
      <div
        v-if="model"
        class="fixed inset-0 z-50 flex justify-center items-start overflow-y-auto pt-[120px] pb-4"
        @click.self="model = false"
      >
        <div class="modal-content">
          <VCategoryTreeItem in-modal />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import VCategoryTreeItem from "~/components/category/tree/VCategoryTreeItem.vue";

const model = defineModel<boolean>({ default: false });

watch(model, (isOpen) => {
  if (isOpen) {
    document.body.style.overflow = "hidden";
  } else {
    document.body.style.overflow = "";
  }
});

onUnmounted(() => {
  document.body.style.overflow = "";
});
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

.modal-content {
  position: relative;
  width: 100%;
  max-width: 1280px;
  margin: 0 1rem;

  &::before {
    content: "";
    position: absolute;
    top: -0.75rem;
    left: -0.75rem;
    right: -0.75rem;
    bottom: -0.75rem;
    z-index: -1;
    width: calc(100% + 1.5rem);
    height: calc(100% + 1.5rem);
    border-radius: 1rem;
    background-color: rgba(255, 255, 255);
  }
}
</style>
