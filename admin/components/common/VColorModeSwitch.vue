<template>
  <UPopover arrow :content="{side: 'bottom', sideOffset: 0}">
    <UButton color="neutral" variant="ghost" size="sm">
      <Sun v-if="colorMode.preference === 'light'" class="size-5" />
      <Moon v-else-if="colorMode.preference === 'dark'" class="size-5" />
      <Monitor v-else class="size-5" />
    </UButton>
    <template #content>
      <div class="p-1 w-30">
        <UButton
          v-for="item in items"
          :key="item.value"
          color="neutral"
          variant="ghost"
          block
          class="justify-start"
          @click="setColorMode(item.value)"
        >
          <template #leading>
            <component :is="item.icon" class="size-4" />
          </template>
          {{ item.label }}
        </UButton>
      </div>
    </template>
  </UPopover>
</template>

<script setup lang="ts">
import { Sun, Moon, Monitor } from "lucide-vue-next";

const colorMode = useColorMode();

const items = [
  { label: "Світла", value: "light", icon: Sun },
  { label: "Темна", value: "dark", icon: Moon },
  { label: "Системна", value: "system", icon: Monitor },
];

const setColorMode = (value: string) => {
  colorMode.preference = value;
};
</script>
