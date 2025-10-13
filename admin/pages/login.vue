<template>
  <div class="container">
    <div class="form-card">
      <h1>Login</h1>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            id="email"
            v-model="credentials.email"
            type="email"
            required
            placeholder="Enter your email"
          />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            id="password"
            v-model="credentials.password"
            type="password"
            required
            placeholder="Enter your password"
          />
        </div>

        <div v-if="error" class="error">
          {{ error }}
        </div>

        <button type="submit" :disabled="loading">
          {{ loading ? "Logging in..." : "Login" }}
        </button>
      </form>

      <p class="link">
        Don't have an account?
        <NuxtLink to="/register">Register here</NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup>
const authStore = useAuthStore();
const credentials = ref({
  email: "",
  password: "",
});

const loading = ref(false);
const error = ref("");

const handleLogin = async () => {
  loading.value = true;
  error.value = "";

  try {
    await authStore.login(credentials.value);

    navigateTo("/dashboard");
  } catch (err) {
    console.log(err);

    error.value =
      err.data?.message || "Login failed. Please check your credentials.";
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.container {
  max-width: 400px;
  margin: 0 auto;
  padding: 2rem;
  font-family: Arial, sans-serif;
}

.form-card {
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

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: #555;
  font-weight: 500;
}

input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: #3b82f6;
}

button {
  width: 100%;
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 500;
}

button:hover:not(:disabled) {
  background: #2563eb;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: #ef4444;
  background: #fee2e2;
  padding: 0.75rem;
  border-radius: 4px;
  margin-bottom: 1rem;
}

.link {
  text-align: center;
  margin-top: 1rem;
  color: #666;
}

.link a {
  color: #3b82f6;
  text-decoration: none;
}

.link a:hover {
  text-decoration: underline;
}
</style>
