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
        class="fixed inset-0 z-50 flex justify-center items-center overflow-y-auto p-4"
        @click.self="model = false"
      >
        <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold">{{ $t('callback.title') }}</h2>
            <UButton
              variant="ghost"
              color="neutral"
              size="sm"
              @click="model = false"
            >
              <X class="w-5 h-5" />
            </UButton>
          </div>

          <p class="text-sm text-muted mb-6">{{ $t('callback.description') }}</p>

          <form @submit.prevent="submitForm">
            <div class="space-y-4">
              <UFormField :label="$t('callback.phone')" required class="w-full">
                <UInput
                  v-model="form.phone"
                  type="tel"
                  :placeholder="$t('callback.phonePlaceholder')"
                  required
                  class="w-full"
                />
              </UFormField>

              <UFormField :label="$t('callback.name')" class="w-full">
                <UInput
                  v-model="form.name"
                  :placeholder="$t('callback.namePlaceholder')"
                  class="w-full"
                />
              </UFormField>

              <UFormField :label="$t('callback.comment')" class="w-full">
                <UTextarea
                  v-model="form.comment"
                  :placeholder="$t('callback.commentPlaceholder')"
                  :rows="3"
                  class="w-full"
                />
              </UFormField>
            </div>

            <UButton
              type="submit"
              class="w-full mt-6"
              size="lg"
              :loading="loading"
            >
              {{ $t('callback.submit') }}
            </UButton>
          </form>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { X } from "lucide-vue-next";

const model = defineModel<boolean>({ default: false });
const { t } = useI18n();
const toast = useToast();

const loading = ref(false);
const form = reactive({
  phone: '',
  name: '',
  comment: '',
});

const resetForm = () => {
  form.phone = '';
  form.name = '';
  form.comment = '';
};

const submitForm = async () => {
  if (!form.phone) return;

  loading.value = true;

  try {
    const client = useSanctumClient();
    await client('/api/callback-requests', {
      method: 'POST',
      body: {
        phone: form.phone,
        name: form.name || null,
        comment: form.comment || null,
      },
    });

    toast.add({
      title: t('callback.successTitle'),
      description: t('callback.successMessage'),
      color: 'success',
    });

    resetForm();
    model.value = false;
  } catch (error) {
    toast.add({
      title: t('callback.errorTitle'),
      description: t('callback.errorMessage'),
      color: 'error',
    });
  } finally {
    loading.value = false;
  }
};

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
</style>
