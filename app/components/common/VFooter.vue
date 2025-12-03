<template>
  <footer class="py-6 border-t border-neutral-200">
    <UContainer class="flex">
      <div class="max-w-[33%] w-full">
        <div class="pb-5 mb-5 border-b border-neutral-200 pr-4">
          <span class="text-sx font-medium mb-2 block">Режим роботи</span>

          <div class="grid grid-cols-2">
            <div>
              <span
                class="text-xs text-white bg-slate-600 px-[4px] py-[1px] inline-block mr-2"
              >
                Пн-Пт
              </span>
              <span class="text-xs font-medium text-muted">09:00-20:00</span>
            </div>
            <div>
              <span class="text-xs font-bold text-muted">{{ phoneOne }}</span>
            </div>

            <div>
              <span
                class="text-xs text-white bg-slate-600 px-[4px] py-[1px] inline-block mr-2"
              >
                Сб-Нд
              </span>
              <span class="text-xs font-medium text-muted">10:00-20:00</span>
            </div>
            <div>
              <span class="text-xs font-bold text-muted">{{ phoneTwo }}</span>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 pr-4 gap-4">
          <div class="flex flex-col">
            <span class="text-xs font-medium text-muted"
              >support@platforma.com</span
            >
            <span class="text-xs text-muted">Підтримка клієнтів</span>
          </div>

          <div class="flex flex-col">
            <span class="text-xs font-medium text-muted"
              >sales@platforma.com</span
            >
            <span class="text-xs text-muted">Комерційні питання</span>
          </div>

          <div class="flex flex-col">
            <span class="text-xs font-medium text-muted">hr@platforma.com</span>
            <span class="text-xs text-muted">Робота в компанії</span>
          </div>

          <div class="flex flex-col">
            <span class="text-xs font-medium text-muted">telegram chat</span>
            <span class="text-xs text-muted">Онлайн чат</span>
          </div>
        </div>
      </div>
      <div
        class="max-w-[33%] w-full border-l border-r border-neutral-200 flex gap-6 px-4"
      >
        <div
          v-for="(item, index) in nav"
          :key="index"
          class="max-w-[50%] w-full"
        >
          <span class="text-xs text-dimmed">{{ item.title }}</span>
          <div class="flex flex-col gap-1">
            <NuxtLink
              v-for="(link, index) in item.items"
              :key="index"
              :to="link.href"
              class="text-xs text-muted"
            >
              {{ link.title }}
            </NuxtLink>
          </div>
        </div>
      </div>
      <div class="max-w-[33%] w-full pl-4">
        <span class="text-sx font-medium mb-2 block">
          Наші соціальні мережі
        </span>

        <div class="grid grid-cols-3 gap-2">
          <a
            v-for="(item, index) in socials"
            :key="index"
            :href="item.href"
            target="_blank"
            class="flex flex-col items-center bg-slate-100 rounded-[12px] p-[7.5px]"
          >
            <component :is="item.icon" class="w-[20px] h-[20px] mb-[6px]" />
            <span class="text-xs font-medium text-muted mb-[2px]">{{
              item.title
            }}</span>
            <span class="text-[10px] font-light text-dimmed">{{
              item.stats
            }}</span>
          </a>
        </div>
      </div>
    </UContainer>
  </footer>
</template>

<script lang="ts" setup>
import IconInstagram from "@/components/icons/IconInstagram.vue";
import IconTelegram from "@/components/icons/IconTelegram.vue";
import IconTikTiok from "@/components/icons/IconTikTiok.vue";
import IconTwitter from "@/components/icons/IconTwitter.vue";
import IconDiscord from "@/components/icons/IconDiscord.vue";
import IconFacebook from "@/components/icons/IconFacebook.vue";

const { phoneOne, phoneTwo } = useConstants();

const nav = computed(() => {
  return [
    {
      title: "Покупцям",
      items: [
        { title: "Доставка", href: "#" },
        { title: "Оплата", href: "#" },
        { title: "Гарантія", href: "#" },
        { title: "Повернення та обмін", href: "#" },
        { title: "Бонусна програма", href: "#" },
        { title: "Політика конфіденційності", href: "#" },
        { title: "Корпоративним клієнтам", href: "#" },
        { title: "Всі категорії", href: "#" },
      ],
    },
    {
      title: "Інформація про компанію",
      items: [
        { title: "Уцінка", href: "#" },
        { title: "Акції", href: "#" },
        { title: "Про нас", href: "#" },
        { title: "Контакти", href: "#" },
        { title: "Партнерство", href: "#" },
        { title: "Вакансії", href: "#" },
        { title: "Блог", href: "#" },
      ],
    },
  ];
});

const socials = computed(() => {
  return [
    {
      title: "Instagram",
      href: "#",
      icon: IconInstagram,
      stats: "1.2M підписників",
    },
    {
      title: "Telegram",
      href: "#",
      icon: IconTelegram,
      stats: "1.2M підписників",
    },
    {
      title: "TikTok",
      href: "#",
      icon: IconTikTiok,
      stats: "1.2M підписників",
    },
    {
      title: "Twitter",
      href: "#",
      icon: IconTwitter,
      stats: "1.2M підписників",
    },
    {
      title: "Discord",
      href: "#",
      icon: IconDiscord,
      stats: "1.2M підписників",
    },
    {
      title: "Facebook",
      href: "#",
      icon: IconFacebook,
      stats: "1.2M підписників",
    },
  ];
});
</script>
