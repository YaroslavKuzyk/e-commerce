// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: "2024-11-01",
  devtools: { enabled: true },
  ssr: true,

  modules: [
    "@pinia/nuxt",
    "nuxt-auth-sanctum",
    "@nuxt/ui",
    "nuxt-lucide-icons",
  ],
  css: ["~/assets/styles/tailwind.css"],

  devServer: {
    port: 3001,
  },

  sanctum: {
    baseUrl: process.env.NUXT_SANCTUM_BASE_URL,
    origin: process.env.NUXT_SANCTUM_ORIGIN,
    mode: "token",
    endpoints: {
      login: "/api/login",
      logout: "/api/logout",
      user: "/api/user",
    },
    redirect: {
      keepRequestedRoute: false,
      onLogin: "/dashboard",
      onLogout: "/login",
      onAuthOnly: "/login",
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