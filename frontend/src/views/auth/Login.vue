<template>
  <div class="login-page py-12">
    <div class="container mx-auto px-4">
      <div class="max-w-md mx-auto card p-8">
        <h2 class="text-3xl font-bold text-center mb-6">
          {{ $t("auth.login_title") }}
        </h2>

        <form @submit.prevent="handleLogin">
          <div
            v-if="error"
            class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"
          >
            {{ error }}
          </div>

          <div class="mb-4">
            <label class="block text-gray-700 mb-2">{{
              $t("auth.email")
            }}</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-2 border rounded focus:outline-none focus:border-blue-500"
            />
          </div>

          <div class="mb-6">
            <label class="block text-gray-700 mb-2">{{
              $t("auth.password")
            }}</label>
            <input
              v-model="form.password"
              type="password"
              required
              class="w-full px-4 py-2 border rounded focus:outline-none focus:border-blue-500"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full btn btn-primary mb-4"
          >
            {{ loading ? $t("common.loading") : $t("auth.submit") }}
          </button>

          <div class="text-center">
            <p class="text-gray-600">
              {{ $t("auth.no_account") }}
              <router-link to="/register" class="text-blue-600 hover:underline">
                {{ $t("nav.register") }}
              </router-link>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  email: "",
  password: "",
});

const loading = ref(false);
const error = ref("");

const handleLogin = async () => {
  loading.value = true;
  error.value = "";

  try {
    await authStore.login(form.value);
    router.push("/");
  } catch (err) {
    error.value = err.response?.data?.message || "Login failed";
  } finally {
    loading.value = false;
  }
};
</script>
