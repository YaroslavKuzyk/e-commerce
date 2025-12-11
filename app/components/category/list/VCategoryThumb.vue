<template>
  <NuxtLink
    to="/"
    class="flex justify-between p-[22px] gap-3 items-end max-w-[570px] w-full border-2 border-primary rounded-[20px]"
  >
    <div>
      <span class="uppercase text-dimmed text-sm font-bold mb-[-12px] block"
        >Для фотографів</span
      >
      <p class="text-[42px] font-bold">Фотографії</p>
    </div>
    <div>
      <img
        src="https://picsum.photos/640/640?random=1"
        alt="Фотографії"
        class="max-w-[205px] w-full h-[256px] object-cover rounded-[10px]"
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
