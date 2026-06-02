import { defineStore } from "pinia";
import { ref, computed } from "vue";
import api from "@/services/api";

export const useAuthStore = defineStore("auth", () => {
  const user = ref(JSON.parse(localStorage.getItem("user")) || null);
  const token = ref(localStorage.getItem("token") || null);

  const isAuthenticated = computed(() => !!token.value);
  const isAdmin = computed(() => user.value?.role === "admin");

  async function login(credentials) {
    try {
      const response = await api.post("/login", credentials);
      user.value = response.data.user;
      token.value = response.data.token;

      localStorage.setItem("user", JSON.stringify(response.data.user));
      localStorage.setItem("token", response.data.token);

      return response.data;
    } catch (error) {
      throw error;
    }
  }

  async function register(userData) {
    try {
      const response = await api.post("/register", userData);
      user.value = response.data.user;
      token.value = response.data.token;

      localStorage.setItem("user", JSON.stringify(response.data.user));
      localStorage.setItem("token", response.data.token);

      return response.data;
    } catch (error) {
      throw error;
    }
  }

  async function logout() {
    try {
      await api.post("/logout");
    } catch (error) {
      console.error("Logout error:", error);
    } finally {
      user.value = null;
      token.value = null;
      localStorage.removeItem("user");
      localStorage.removeItem("token");
    }
  }

  async function fetchUser() {
    try {
      const response = await api.get("/user");
      user.value = response.data;
      localStorage.setItem("user", JSON.stringify(response.data));
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    login,
    register,
    logout,
    fetchUser,
  };
});
