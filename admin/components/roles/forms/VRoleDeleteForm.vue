<template>
  <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
    <UFormField label="Виберіть роль для видалення" name="deleteRole">
      <USelectMenu
        v-model="state.deleteRole"
        :items="deletedRoleList"
        class="w-[100%]"
        placeholder="Обрати роль"
        value-key="id"
        label-key="name"
      >
        <template #trailing>
          <UButton
            v-if="state.deleteRole"
            size="sm"
            variant="link"
            icon="i-lucide-circle-x"
            aria-label="Очистити"
            @click.stop="state.deleteRole = null"
            color="neutral"
          />
        </template>
      </USelectMenu>
    </UFormField>
    <UFormField label="Виберіть роль для заміни" name="replacementRole">
      <USelectMenu
        v-model="state.replacementRole"
        :items="replacementRoleList"
        class="w-[100%]"
        placeholder="Обрати роль для заміни"
        value-key="id"
        label-key="name"
      >
        <template #trailing>
          <UButton
            v-if="state.replacementRole"
            size="sm"
            variant="link"
            icon="i-lucide-circle-x"
            aria-label="Очистити"
            @click.stop="state.replacementRole = null"
            color="neutral"
          />
        </template>
      </USelectMenu>
    </UFormField>
    <div class="flex justify-end">
      <UButton type="submit"> Видалити роль </UButton>
    </div>
  </UForm>
</template>

<script setup lang="ts">
import { z } from "zod";

interface IEmits {
  (e: "close"): void;
}

const emits = defineEmits<IEmits>();
const toast = useToast();

const schema = z.object({
  deleteRole: z.number().nullable(),
  replacementRole: z.number().nullable(),
});

const state = reactive({
  deleteRole: null as number | null,
  replacementRole: null as number | null,
});

const roleStore = useRoleStore();

const { data: rolesData } = await roleStore.fetchRoles();
const deletedRoleList = computed(() =>
  rolesData.value?.filter((role) => role.id !== state.replacementRole)
);
const replacementRoleList = computed(() =>
  rolesData.value?.filter((role) => role.id !== state.deleteRole)
);

const onSubmit = async (event: any) => {
  try {
    const payload = event.data;
    await roleStore.onDeleteRole({
      roleId: payload.deleteRole,
      replacementRoleId: payload.replacementRole,
    });

    toast.add({
      title: "Успішно",
      description: "Роль успішно видалена",
      color: "success",
    });

    emits("close");
  } catch (error) {
    toast.add({
      title: "Помилка",
      description: "Не вдалося видалити роль",
      color: "error",
    });
  }
};
</script>
