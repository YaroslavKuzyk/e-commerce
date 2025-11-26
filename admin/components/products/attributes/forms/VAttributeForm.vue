<template>
  <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
    <UFormField label="Назва" name="name">
      <UInput
        v-model="state.name"
        placeholder="Колір"
        class="w-full"
        @blur="handleNameBlur"
      />
    </UFormField>

    <UFormField label="Slug" name="slug">
      <div class="flex gap-2">
        <UInput
          v-model="state.slug"
          placeholder="color"
          class="flex-1"
        />
        <UButton
          type="button"
          variant="outline"
          icon="i-heroicons-sparkles"
          @click="generateSlugFromName"
        >
          Згенерувати
        </UButton>
      </div>
    </UFormField>

    <UFormField label="Тип" name="type">
      <USelect
        v-model="state.type"
        :items="typeItems"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Статус" name="status">
      <USelect
        v-model="state.status"
        :items="statusItems"
        class="w-full"
      />
    </UFormField>

    <UFormField label="Порядок сортування" name="sort_order">
      <UInput
        v-model.number="state.sort_order"
        type="number"
        placeholder="0"
        class="w-full"
      />
    </UFormField>

    <!-- Values Section -->
    <div class="border rounded-lg p-4 space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="font-medium">Значення атрибута</h3>
        <UButton
          type="button"
          size="sm"
          variant="outline"
          icon="i-heroicons-plus"
          @click="addValue"
        >
          Додати значення
        </UButton>
      </div>

      <div v-if="state.values.length === 0" class="text-sm text-gray-500 text-center py-4">
        Немає значень. Додайте перше значення атрибута.
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="(value, index) in state.values"
          :key="index"
          class="flex items-start gap-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
        >
          <div class="flex-1 space-y-2">
            <div class="grid grid-cols-2 gap-2">
              <UInput
                v-model="value.value"
                placeholder="Значення (напр. Червоний)"
                size="sm"
                @blur="generateValueSlug(index)"
              />
              <UInput
                v-model="value.slug"
                placeholder="Slug (напр. red)"
                size="sm"
              />
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div v-if="isColorType" class="flex items-center gap-2">
                <input
                  type="color"
                  v-model="value.color_code"
                  class="w-10 h-8 rounded cursor-pointer border border-gray-300"
                />
                <UInput
                  v-model="value.color_code"
                  placeholder="#FF0000"
                  size="sm"
                  class="flex-1"
                />
              </div>
              <div v-else />
              <UInput
                v-model.number="value.sort_order"
                type="number"
                placeholder="Порядок"
                size="sm"
              />
            </div>
          </div>
          <UButton
            type="button"
            size="sm"
            variant="ghost"
            color="error"
            icon="i-heroicons-trash"
            @click="removeValue(index)"
          />
        </div>
      </div>
    </div>

    <USeparator />

    <div class="flex justify-end gap-2">
      <UButton
        type="button"
        variant="outline"
        color="neutral"
        @click="emits('cancel')"
      >
        <template #leading>
          <Ban class="w-4 h-4" />
        </template>
        Скасувати
      </UButton>
      <UButton
        type="submit"
        :loading="loading"
      >
        <template #leading>
          <Send class="w-4 h-4" />
        </template>
        Підтвердити
      </UButton>
    </div>
  </UForm>
</template>

<script setup lang="ts">
import { z } from "zod";
import { Ban, Send } from "lucide-vue-next";
import type { Attribute, AttributeType, AttributeStatus } from "~/models/attribute";

interface Props {
  attribute: Attribute | null;
}

interface Emits {
  (e: "cancel"): void;
  (e: "success"): void;
}

const props = defineProps<Props>();
const emits = defineEmits<Emits>();
const toast = useToast();
const attributeStore = useAttributeStore();

const schema = z.object({
  name: z.string().min(1, "Назва є обов'язковою"),
  slug: z.string().min(1, "Slug є обов'язковим"),
  type: z.enum(["select", "multi_select", "checkbox", "switch", "color"]),
  status: z.enum(["draft", "published"]),
  sort_order: z.number().optional(),
  values: z.array(z.object({
    value: z.string().min(1, "Значення є обов'язковим"),
    slug: z.string().min(1, "Slug є обов'язковим"),
    color_code: z.string().nullable().optional(),
    sort_order: z.number().optional(),
  })).optional(),
});

interface ValueState {
  id?: number;
  value: string;
  slug: string;
  color_code: string | null;
  sort_order: number;
}

const state = reactive({
  name: props.attribute?.name || "",
  slug: props.attribute?.slug || "",
  type: (props.attribute?.type || "select") as AttributeType,
  status: (props.attribute?.status || "draft") as AttributeStatus,
  sort_order: props.attribute?.sort_order || 0,
  values: (props.attribute?.values || []).map((v, i) => ({
    id: v.id,
    value: v.value,
    slug: v.slug,
    color_code: v.color_code,
    sort_order: v.sort_order ?? i,
  })) as ValueState[],
});

const typeItems = [
  { label: "Вибір (одне значення)", value: "select" },
  { label: "Множинний вибір", value: "multi_select" },
  { label: "Чекбокс", value: "checkbox" },
  { label: "Перемикач", value: "switch" },
  { label: "Колір", value: "color" },
];

const isColorType = computed(() => state.type === "color");

const statusItems = [
  { label: "Чернетка", value: "draft" },
  { label: "Опубліковано", value: "published" },
];

// Values management
const addValue = () => {
  state.values.push({
    value: "",
    slug: "",
    color_code: null,
    sort_order: state.values.length,
  });
};

const removeValue = (index: number) => {
  state.values.splice(index, 1);
};

const generateValueSlug = async (index: number) => {
  const value = state.values[index];
  if (!value.slug && value.value) {
    try {
      const { data, error } = await attributeStore.onGenerateSlug(value.value);
      if (!error.value && data.value) {
        value.slug = data.value.slug;
      }
    } catch (err) {
      console.error("Failed to generate value slug:", err);
    }
  }
};

// Slug generation
const handleNameBlur = () => {
  if (!state.slug && state.name) {
    generateSlugFromName();
  }
};

const generateSlugFromName = async () => {
  if (!state.name) return;

  try {
    const { data, error } = await attributeStore.onGenerateSlug(state.name);
    if (!error.value && data.value) {
      state.slug = data.value.slug;
    }
  } catch (err) {
    console.error("Failed to generate slug:", err);
  }
};

// Form submission
const loading = ref(false);

const onSubmit = async () => {
  loading.value = true;

  try {
    let result;
    const payload = {
      name: state.name,
      slug: state.slug,
      type: state.type,
      status: state.status,
      sort_order: state.sort_order,
      values: state.values.map((v, i) => ({
        value: v.value,
        slug: v.slug,
        color_code: v.color_code || null,
        sort_order: v.sort_order ?? i,
      })),
    };

    if (props.attribute) {
      // For update, we update the attribute and handle values separately
      result = await attributeStore.onUpdateAttribute(
        props.attribute.id,
        {
          name: payload.name,
          slug: payload.slug,
          type: payload.type,
          status: payload.status,
          sort_order: payload.sort_order,
        }
      );

      // Handle values for existing attribute
      if (!result.error.value) {
        // Delete removed values
        const existingValueIds = props.attribute.values.map(v => v.id);
        const currentValueIds = state.values.filter(v => v.id).map(v => v.id);

        for (const existingId of existingValueIds) {
          if (!currentValueIds.includes(existingId)) {
            await attributeStore.onDeleteValue(props.attribute.id, existingId);
          }
        }

        // Update existing and add new values
        for (const value of state.values) {
          if (value.id) {
            await attributeStore.onUpdateValue(props.attribute.id, value.id, {
              value: value.value,
              slug: value.slug,
              color_code: value.color_code,
              sort_order: value.sort_order,
            });
          } else {
            await attributeStore.onAddValue(props.attribute.id, {
              value: value.value,
              slug: value.slug,
              color_code: value.color_code,
              sort_order: value.sort_order,
            });
          }
        }
      }
    } else {
      result = await attributeStore.onCreateAttribute(payload);
    }

    if (result.error.value) {
      toast.add({
        title: "Помилка",
        description: "Не вдалося зберегти атрибут",
        color: "error",
      });
      return;
    }

    toast.add({
      title: "Успіх",
      description: props.attribute
        ? "Атрибут успішно оновлено"
        : "Атрибут успішно створено",
      color: "success",
    });

    await refreshNuxtData('attributes');

    emits("success");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося зберегти атрибут",
      color: "error",
    });
  } finally {
    loading.value = false;
  }
};
</script>
