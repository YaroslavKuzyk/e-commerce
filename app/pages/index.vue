<template>
  <div class="container">
    <h1>Welcome to Nuxt + Laravel Sanctum Auth</h1>

    <UAvatar src="https://github.com/benjamincanac.png" />

    <div v-if="authStore.user">
      <p>Hello, {{ authStore.user.name }}!</p>
      <p>Email: {{ authStore.user.email }}</p>

      <button @click="logout">Logout</button>
      <NuxtLink to="/dashboard">Go to Dashboard</NuxtLink>
    </div>

    <div v-else>
      <p>Please login or register to continue</p>
      <NuxtLink to="/login">Login</NuxtLink>
      <NuxtLink to="/register">Register</NuxtLink>
    </div>
  </div>
</template>

<script setup>
const { logout: sanctumLogout } = useSanctumAuth();
const authStore = useAuthStore();

const logout = async () => {
  await sanctumLogout();
  navigateTo("/login");
};
</script>

<style scoped>
.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
  font-family: Arial, sans-serif;
}

h1 {
  color: #333;
  margin-bottom: 2rem;
}

button {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 1rem;
}

button:hover {
  background: #dc2626;
}

a {
  background: #3b82f6;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  text-decoration: none;
  margin-right: 1rem;
  display: inline-block;
}

a:hover {
  background: #2563eb;
}
</style>
