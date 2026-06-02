<template>
  <div
    v-if="settingsStore.bilingualEnabled"
    class="floating-lang"
  >
    <button
      class="lang-pill"
      :class="{ active: currentLocale === 'vi' }"
      @click="setLocale('vi')"
      aria-label="Tiếng Việt"
    >
      <span class="lang-flag">🇻🇳</span>
    </button>
    <button
      class="lang-pill"
      :class="{ active: currentLocale === 'en' }"
      @click="setLocale('en')"
      aria-label="English"
    >
      <span class="lang-flag">🇬🇧</span>
    </button>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import { useI18n } from "vue-i18n";
import i18n from "@/locales";
import { useSettingsStore } from "@/stores/settings";

const { locale } = useI18n({ useScope: "global" });
const settingsStore = useSettingsStore();
const currentLocale = ref(locale.value);

const setLocale = (lang) => {
  currentLocale.value = lang;
  locale.value = lang;
  i18n.global.locale.value = lang;
  localStorage.setItem("locale", lang);
  settingsStore.setLocale(lang);
  settingsStore.refreshSettings();
};

watch(locale, (val) => {
  currentLocale.value = val;
});
</script>

<style scoped>
/* Only visible on mobile */
.floating-lang {
  display: none;
}

@media (max-width: 1023px) {
  .floating-lang {
    position: fixed;
    top: 80px;
    right: 12px;
    z-index: 999;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-radius: 12px;
    box-shadow:
      0 2px 12px rgba(0, 0, 0, 0.1),
      0 0 0 1px rgba(0, 0, 0, 0.06);
  }

  .lang-pill {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid transparent;
    border-radius: 10px;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
  }

  .lang-pill:hover {
    background: rgba(37, 99, 235, 0.08);
  }

  .lang-pill.active {
    background: rgba(37, 99, 235, 0.12);
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
  }

  .lang-flag {
    font-size: 1.25rem;
    line-height: 1;
  }
}
</style>
