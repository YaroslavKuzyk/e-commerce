<template>
  <UPopover arrow :content="{ side: 'bottom', sideOffset: 0, align: 'start' }">
    <UButton color="neutral" variant="ghost" class="gap-1">
      <span class="uppercase">{{ locale }}</span>
      <ChevronDown size="16" />
    </UButton>
    <template #content>
      <div class="p-1 w-30">
        <UButton
          v-for="loc in [
            { code: 'ua', name: 'Українська' },
            { code: 'en', name: 'English' },
          ]"
          :key="loc.code"
          color="neutral"
          variant="ghost"
          block
          class="justify-start"
          @click="selectLang(loc.code)"
        >
          <template #leading>
            <Check v-if="loc.code === locale" class="size-4" />
            <span v-else class="size-4" />
          </template>
          {{ loc.name }}
        </UButton>
      </div>
    </template>
  </UPopover>
</template>

<script setup lang="ts">
import { Check, ChevronDown } from "lucide-vue-next";

const { locale, locales } = useI18n();
const switchLocalePath = useSwitchLocalePath();
const router = useRouter();

const selectLang = (newLang: "ua" | "en") => {
  router.push(switchLocalePath(newLang));
};
</script>
