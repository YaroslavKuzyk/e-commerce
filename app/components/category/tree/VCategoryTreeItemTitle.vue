<template>
  <div
    class="flex items-center gap-3 p-3 rounded-[14px] cursor-pointer hover:bg-primary-50 duration-200"
    :class="{
      'bg-primary-50': active,
    }"
  >
    <img :src="img" :alt="title" />
    <span>{{ title }}</span>
    <ChevronRight class="ml-auto" />
  </div>
</template>

<script lang="ts" setup>
import { ChevronRight } from "lucide-vue-next";

interface IProps {
  title: string;
  img: string;
  active: boolean;
}

const props = defineProps<IProps>();
</script>
