<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <template v-if="isAdminRoute">
      <RouterView />
    </template>
    <template v-else>
      <Navbar />
      <main class="main-content">
        <RouterView />
      </main>
      <FloatingLanguageSwitcher />
      <FloatingContactButtons />
      <Footer />
    </template>
  </div>
</template>

<script setup>
import { RouterView, useRoute } from "vue-router";
import { computed, watchEffect } from "vue";
import Navbar from "./components/layout/Navbar.vue";
import Footer from "./components/layout/Footer.vue";
import FloatingContactButtons from "./components/layout/FloatingContactButtons.vue";
import FloatingLanguageSwitcher from "./components/layout/FloatingLanguageSwitcher.vue";
import { useSettingsStore } from "./stores/settings";

const route = useRoute();
const isAdminRoute = computed(() => route.path.startsWith("/admin"));

const settingsStore = useSettingsStore();

// Helper to update or create a meta tag
const setMeta = (attr, key, content) => {
  if (!content) return;
  let el = document.querySelector(`meta[${attr}="${key}"]`);
  if (!el) {
    el = document.createElement("meta");
    el.setAttribute(attr, key);
    document.head.appendChild(el);
  }
  el.setAttribute("content", content);
};

// Reactively update head meta tags from settings
watchEffect(() => {
  if (!settingsStore.loaded) return;

  const siteName = settingsStore.siteName || "Happy Island Tour";
  const title = settingsStore.metaTitle || siteName;
  const description = settingsStore.metaDescription || `${siteName} - Discover amazing tours, rooms, and experiences.`;
  const ogImage = settingsStore.ogImage;

  document.title = title;

  setMeta("name", "description", description);
  setMeta("property", "og:site_name", siteName);
  setMeta("property", "og:title", title);
  setMeta("property", "og:description", description);
  if (ogImage) setMeta("property", "og:image", ogImage);
  setMeta("name", "twitter:title", title);
  setMeta("name", "twitter:description", description);
});
</script>

<style>
#app {
  font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
    "Helvetica Neue", Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.main-content {
  min-height: 100vh;
  padding-top: 72px; /* Height of navbar */
}
</style>
