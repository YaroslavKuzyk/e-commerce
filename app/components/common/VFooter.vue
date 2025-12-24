<template>
  <footer class="py-6 border-t border-neutral-200">
    <UContainer class="flex">
      <div class="max-w-[33%] w-full">
        <div class="pb-5 mb-5 border-b border-neutral-200 pr-4">
          <span class="text-sx font-medium mb-2 block">{{
            $t("footer.workingHours")
          }}</span>

          <div class="grid grid-cols-2">
            <div>
              <span
                class="text-xs text-white bg-slate-600 px-[4px] py-[1px] inline-block mr-2"
              >
                {{ footerWorkingHours.weekdays.label }}
              </span>
              <span class="text-xs font-medium text-muted"
                >{{ footerWorkingHours.weekdays.from }}-{{
                  footerWorkingHours.weekdays.to
                }}</span
              >
            </div>
            <div>
              <span
                v-if="footerWorkingHours.phone1?.value"
                class="text-xs font-bold text-muted"
              >
                {{
                  footerWorkingHours.phone1.label
                    ? `${footerWorkingHours.phone1.label}: `
                    : ""
                }}{{ footerWorkingHours.phone1.value }}
              </span>
            </div>

            <div>
              <span
                class="text-xs text-white bg-slate-600 px-[4px] py-[1px] inline-block mr-2"
              >
                {{ footerWorkingHours.weekends.label }}
              </span>
              <span class="text-xs font-medium text-muted"
                >{{ footerWorkingHours.weekends.from }}-{{
                  footerWorkingHours.weekends.to
                }}</span
              >
            </div>
            <div>
              <span
                v-if="footerWorkingHours.phone2?.value"
                class="text-xs font-bold text-muted"
              >
                {{
                  footerWorkingHours.phone2.label
                    ? `${footerWorkingHours.phone2.label}: `
                    : ""
                }}{{ footerWorkingHours.phone2.value }}
              </span>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 pr-4 gap-4">
          <div
            v-for="(email, index) in emails"
            :key="index"
            class="flex flex-col"
          >
            <span class="text-xs font-medium text-muted">{{
              email.email
            }}</span>
            <span class="text-xs text-muted">{{ email.label }}</span>
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
              v-for="(link, linkIndex) in item.items"
              :key="linkIndex"
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
          {{ $t("footer.socialNetworks") }}
        </span>

        <div class="grid grid-cols-3 gap-2">
          <a
            v-for="(item, index) in socialsComputed"
            :key="index"
            :href="item.url"
            target="_blank"
            class="flex flex-col items-center bg-slate-100 rounded-[12px] p-[7.5px]"
          >
            <component :is="item.icon" class="w-[20px] h-[20px] mb-[6px]" />
            <span class="text-xs font-medium text-muted mb-[2px]">{{
              item.name
            }}</span>
            <span
              v-if="item.followers"
              class="text-[10px] font-light text-dimmed"
              >{{ item.followers }}</span
            >
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

const { t } = useI18n();
const { emails, footerWorkingHours, socialLinks } = useStoreSettings();

const localePath = useLocalePath();

const nav = computed(() => {
  return [
    {
      title: t("footer.navCustomers"),
      items: [
        { title: t("footer.delivery"), href: "#" },
        { title: t("footer.payment"), href: "#" },
        { title: t("footer.warranty"), href: "#" },
        { title: t("footer.returnAndExchange"), href: "#" },
        { title: t("footer.bonusProgram"), href: "#" },
        { title: t("footer.privacyPolicy"), href: "#" },
        { title: t("footer.corporateClients"), href: "#" },
        { title: t("footer.allCategories"), href: localePath("/category") },
      ],
    },
    {
      title: t("footer.navCompanyInfo"),
      items: [
        {
          title: t("footer.discountProducts"),
          href: localePath("/store/ucinka"),
        },
        { title: t("footer.promotions"), href: localePath("/store/akcii") },
        { title: t("footer.aboutUs"), href: "#" },
        { title: t("footer.contacts"), href: "#" },
        { title: t("footer.partnership"), href: "#" },
        { title: t("footer.vacancies"), href: "#" },
        { title: t("footer.blog"), href: localePath("/blog") },
      ],
    },
  ];
});

const socialIconMap: Record<string, any> = {
  instagram: IconInstagram,
  telegram: IconTelegram,
  tiktok: IconTikTiok,
  twitter: IconTwitter,
  discord: IconDiscord,
  facebook: IconFacebook,
};

const socialNameMap: Record<string, string> = {
  instagram: 'Instagram',
  telegram: 'Telegram',
  tiktok: 'TikTok',
  twitter: 'X',
  discord: 'Discord',
  facebook: 'Facebook',
};

const socialsComputed = computed(() => {
  if (socialLinks.value.length > 0) {
    return socialLinks.value.map((link) => ({
      ...link,
      name: link.name || socialNameMap[link.platform] || link.platform,
      icon: socialIconMap[link.platform] || IconInstagram,
      followers: link.followers ? `${link.followers} ${t("footer.subscribers")}` : undefined,
    }));
  }
});
</script>
