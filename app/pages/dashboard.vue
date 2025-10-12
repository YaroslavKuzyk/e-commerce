<template>
  <div class="container">
    <div class="dashboard">
      <h1>Dashboard</h1>

      <div v-if="authStore.user" class="user-info">
        <h2>Welcome, {{ authStore.user.name }}!</h2>
        <p><strong>Email:</strong> {{ authStore.user.email }}</p>
        <p>
          <strong>Account created:</strong>
          {{ formatDate(authStore.user.created_at) }}
        </p>
      </div>

      <div class="actions">
        <button @click="goHome">Go to Home</button>
        <button @click="handleLogout" class="logout">Logout</button>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: ["sanctum:auth"],
});

const { logout } = useSanctumAuth();
const authStore = useAuthStore();

const formatDate = (dateString) => {
  if (!dateString) return "N/A";
  return new Date(dateString).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

const goHome = () => {
  navigateTo("/");
};

const handleLogout = async () => {
  await logout();
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

.dashboard {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

h1 {
  color: #333;
  margin-bottom: 2rem;
  text-align: center;
}

.user-info {
  background: #f9fafb;
  padding: 1.5rem;
  border-radius: 6px;
  margin-bottom: 2rem;
}

.user-info h2 {
  color: #1f2937;
  margin-bottom: 1rem;
}

.user-info p {
  color: #4b5563;
  margin-bottom: 0.5rem;
}

.actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

button {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
}

button:hover {
  background: #2563eb;
}

button.logout {
  background: #ef4444;
}

button.logout:hover {
  background: #dc2626;
}
</style>
