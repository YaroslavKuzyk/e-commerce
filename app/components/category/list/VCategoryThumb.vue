<template>
  <NuxtLink
    to="/"
    class="group/category-thumb flex justify-between p-[22px] gap-3 items-end max-w-[570px] w-full ring ring-default rounded-[16px] transition hover:bg-elevated/50"
  >
    <div>
      <span class="uppercase text-dimmed text-sm font-bold mb-[-12px] block"
        >Для фотографів</span
      >
      <p class="text-[42px] font-bold">Фотографії</p>
    </div>
    <div class="relative overflow-hidden rounded-[10px]">
      <img
        src="https://picsum.photos/640/640?random=1"
        alt="Фотографії"
        class="max-w-[205px] w-full h-[256px] duration-200 group-hover/category-thumb:scale-110 object-cover object-top"
      />
    </div>
  </NuxtLink>
</template>

<script setup lang="ts">
interface IProps {
  item: any;
}

const props = defineProps<IProps>();
</script>
