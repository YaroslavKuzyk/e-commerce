// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: "2024-11-01",
  devtools: { enabled: true },
  ssr: true,

  app: {
    head: {
      title: "Admin iD",
      link: [{ rel: "icon", type: "image/png", href: "/favicon.ico" }],
    },
  },

  modules: ["@pinia/nuxt", "nuxt-auth-sanctum", "@nuxt/ui", "@nuxtjs/i18n"],

  i18n: {
    locales: [
      { code: "ua", files: ["ua.json"], name: "Українська" },
      { code: "en", files: ["en.json"], name: "English" },
    ],
    defaultLocale: "ua",
    strategy: "prefix",
    langDir: "locales",
    detectBrowserLanguage: false,
  },

  css: ["~/assets/styles/tailwind.css"],

  devServer: {
    port: 3001,
  },

  sanctum: {
    baseUrl: process.env.NUXT_SANCTUM_BASE_URL,
    origin: process.env.NUXT_SANCTUM_ORIGIN,
    mode: "token",
    endpoints: {
      login: "/api/admin/login",
      logout: "/api/admin/logout",
      user: "/api/admin/user",
    },
    redirect: {
      keepRequestedRoute: false,
      onLogin: "/ua/profile",
      onLogout: "/ua/login",
      onAuthOnly: "/ua/login",
      onGuestOnly: "/",
    },
    token: {
      property: "token",
    },
    globalMiddleware: {
      enabled: false,
    },
  },
});
