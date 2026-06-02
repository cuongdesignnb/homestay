<template>
  <header
    class="navbar"
    :class="{ 'navbar--scrolled': isScrolled, 'navbar--open': mobileMenuOpen }"
  >
    <div class="navbar__container">
      <!-- Logo -->
      <router-link to="/" class="navbar__logo" @click="closeMobileMenu">
        <div class="navbar__logo-icon">
          <img v-if="settingsStore.logo" :src="settingsStore.logo" alt="Logo" class="navbar__logo-img" />
          <svg
            v-else
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            <polyline points="9,22 9,12 15,12 15,22" />
          </svg>
        </div>
        <span class="navbar__logo-text">{{
          $t("common.app_name", "Happy Island Tour")
        }}</span>
      </router-link>

      <!-- Desktop Navigation -->
      <nav class="navbar__nav">
        <router-link
          to="/"
          class="navbar__link"
          active-class="navbar__link--active"
        >
          <span class="navbar__link-icon">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            </svg>
          </span>
          {{ $t("nav.home") }}
        </router-link>
        <router-link
          to="/about"
          class="navbar__link"
          active-class="navbar__link--active"
        >
          <span class="navbar__link-icon">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <circle cx="12" cy="12" r="10" />
              <path d="M12 16v-4" />
              <path d="M12 8h.01" />
            </svg>
          </span>
          {{ $t("nav.about") }}
        </router-link>
        <router-link
          to="/tours"
          class="navbar__link"
          active-class="navbar__link--active"
        >
          <span class="navbar__link-icon">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <circle cx="12" cy="10" r="3" />
              <path
                d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"
              />
            </svg>
          </span>
          {{ $t("nav.tour_information", "Tour Information") }}
        </router-link>
        <router-link
          v-if="settingsStore.featureRoomsEnabled"
          to="/rooms"
          class="navbar__link"
          active-class="navbar__link--active"
        >
          <span class="navbar__link-icon">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M2 4v16h20V8l-6-4H2zM2 8h20" />
              <path d="M10 12h4v8h-4z" />
            </svg>
          </span>
          {{ $t("nav.rooms") }}
        </router-link>
        <router-link
          v-if="settingsStore.featureCarRentalsEnabled"
          to="/car-rentals"
          class="navbar__link navbar__link--tour-info"
          active-class="navbar__link--active"
        >
          <span class="navbar__link-icon">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M3 13l2-5a2 2 0 0 1 1.9-1.4h10.2A2 2 0 0 1 19 8l2 5" />
              <path d="M5 16h14" />
              <circle cx="7.5" cy="17.5" r="1.5" />
              <circle cx="16.5" cy="17.5" r="1.5" />
            </svg>
          </span>
          {{ $t("nav.car_rental", "Car Rental") }}
        </router-link>
        <router-link
          to="/blog"
          class="navbar__link"
          active-class="navbar__link--active"
        >
          <span class="navbar__link-icon">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
              <path
                d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"
              />
            </svg>
          </span>
          {{ $t("nav.blog") }}
        </router-link>
      </nav>

      <!-- Desktop Actions -->
      <div class="navbar__actions">
        <!-- Language Switcher -->
        <div class="navbar__lang">
          <button
            class="navbar__lang-btn"
            :class="{ 'navbar__lang-btn--active': currentLocale === 'vi' }"
            @click="setLocale('vi')"
          >
            🇻🇳
          </button>
          <button
            class="navbar__lang-btn"
            :class="{ 'navbar__lang-btn--active': currentLocale === 'en' }"
            @click="setLocale('en')"
          >
            🇬🇧
          </button>
        </div>

        <!-- Auth Section -->
        <div v-if="authStore.isAuthenticated" class="navbar__user">
          <router-link
            v-if="authStore.isAdmin"
            to="/admin"
            class="navbar__action-btn navbar__action-btn--admin"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
              <path
                d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"
              />
            </svg>
            <span>{{ $t("nav.admin") }}</span>
          </router-link>

          <router-link to="/bookings" class="navbar__action-btn">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
          </router-link>

          <!-- User Dropdown -->
          <div class="navbar__dropdown" ref="userDropdown">
            <button class="navbar__user-btn" @click="toggleUserMenu">
              <div class="navbar__avatar">
                {{ authStore.user?.name?.charAt(0)?.toUpperCase() || "U" }}
              </div>
              <span class="navbar__user-name">{{ authStore.user?.name }}</span>
              <svg
                class="navbar__chevron"
                :class="{ 'navbar__chevron--open': userMenuOpen }"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <polyline points="6 9 12 15 18 9" />
              </svg>
            </button>
            <Transition name="dropdown">
              <div v-if="userMenuOpen" class="navbar__dropdown-menu">
                <router-link
                  to="/profile"
                  class="navbar__dropdown-item"
                  @click="userMenuOpen = false"
                >
                  <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                  {{ $t("nav.profile") }}
                </router-link>
                <router-link
                  to="/bookings"
                  class="navbar__dropdown-item"
                  @click="userMenuOpen = false"
                >
                  <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                  </svg>
                  {{ $t("nav.bookings") }}
                </router-link>
                <div class="navbar__dropdown-divider"></div>
                <button
                  class="navbar__dropdown-item navbar__dropdown-item--danger"
                  @click="handleLogout"
                >
                  <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                  </svg>
                  {{ $t("nav.logout") }}
                </button>
              </div>
            </Transition>
          </div>
        </div>

        <!-- Guest Actions -->
        <div v-else class="navbar__guest">
          <router-link to="/login" class="navbar__login-btn">
            {{ $t("nav.login") }}
          </router-link>
          <router-link to="/register" class="navbar__register-btn">
            {{ $t("nav.register") }}
          </router-link>
        </div>
      </div>

      <!-- Mobile Menu Toggle -->
      <button
        class="navbar__mobile-toggle"
        @click="toggleMobileMenu"
        aria-label="Toggle menu"
      >
        <span
          class="navbar__hamburger"
          :class="{ 'navbar__hamburger--open': mobileMenuOpen }"
        >
          <span></span>
          <span></span>
          <span></span>
        </span>
      </button>
    </div>

    <!-- Mobile Menu -->
    <Transition name="mobile-menu">
      <div v-if="mobileMenuOpen" class="navbar__mobile">
        <nav class="navbar__mobile-nav">
          <router-link
            to="/"
            class="navbar__mobile-link"
            @click="closeMobileMenu"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            </svg>
            {{ $t("nav.home") }}
          </router-link>
          <router-link
            to="/about"
            class="navbar__mobile-link"
            @click="closeMobileMenu"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <circle cx="12" cy="12" r="10" />
              <path d="M12 16v-4" />
              <path d="M12 8h.01" />
            </svg>
            {{ $t("nav.about") }}
          </router-link>
          <router-link
            to="/tours"
            class="navbar__mobile-link"
            @click="closeMobileMenu"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <circle cx="12" cy="10" r="3" />
              <path
                d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"
              />
            </svg>
            {{ $t("nav.tour_information", "Tour Information") }}
          </router-link>
          <router-link
            v-if="settingsStore.featureRoomsEnabled"
            to="/rooms"
            class="navbar__mobile-link"
            @click="closeMobileMenu"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M2 4v16h20V8l-6-4H2zM2 8h20" />
              <path d="M10 12h4v8h-4z" />
            </svg>
            {{ $t("nav.rooms") }}
          </router-link>
          <router-link
            v-if="settingsStore.featureCarRentalsEnabled"
            to="/car-rentals"
            class="navbar__mobile-link"
            @click="closeMobileMenu"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M3 13l2-5a2 2 0 0 1 1.9-1.4h10.2A2 2 0 0 1 19 8l2 5" />
              <path d="M5 16h14" />
              <circle cx="7.5" cy="17.5" r="1.5" />
              <circle cx="16.5" cy="17.5" r="1.5" />
            </svg>
            {{ $t("nav.car_rental", "Car Rental") }}
          </router-link>
          <router-link
            to="/blog"
            class="navbar__mobile-link"
            @click="closeMobileMenu"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
              <path
                d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"
              />
            </svg>
            {{ $t("nav.blog") }}
          </router-link>
        </nav>

        <div
          class="navbar__mobile-divider"
        ></div>

        <!-- Mobile Language -->
        <div class="navbar__mobile-lang">
          <span class="navbar__mobile-lang-label">{{
            $t("nav.language", "Language")
          }}</span>
          <div class="navbar__mobile-lang-btns">
            <button
              class="navbar__mobile-lang-btn"
              :class="{
                'navbar__mobile-lang-btn--active': currentLocale === 'vi',
              }"
              @click="setLocale('vi')"
            >
              🇻🇳 Tiếng Việt
            </button>
            <button
              class="navbar__mobile-lang-btn"
              :class="{
                'navbar__mobile-lang-btn--active': currentLocale === 'en',
              }"
              @click="setLocale('en')"
            >
              🇬🇧 English
            </button>
          </div>
        </div>

        <div class="navbar__mobile-divider"></div>

        <!-- Mobile Auth -->
        <div v-if="authStore.isAuthenticated" class="navbar__mobile-auth">
          <div class="navbar__mobile-user">
            <div class="navbar__mobile-avatar">
              {{ authStore.user?.name?.charAt(0)?.toUpperCase() || "U" }}
            </div>
            <div class="navbar__mobile-user-info">
              <span class="navbar__mobile-user-name">{{
                authStore.user?.name
              }}</span>
              <span class="navbar__mobile-user-email">{{
                authStore.user?.email
              }}</span>
            </div>
          </div>

          <router-link
            v-if="authStore.isAdmin"
            to="/admin"
            class="navbar__mobile-link navbar__mobile-link--admin"
            @click="closeMobileMenu"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
              <path
                d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"
              />
            </svg>
            {{ $t("nav.admin") }}
          </router-link>
          <router-link
            to="/profile"
            class="navbar__mobile-link"
            @click="closeMobileMenu"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            {{ $t("nav.profile") }}
          </router-link>
          <router-link
            to="/bookings"
            class="navbar__mobile-link"
            @click="closeMobileMenu"
          >
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
            </svg>
            {{ $t("nav.bookings") }}
          </router-link>
          <button class="navbar__mobile-logout" @click="handleLogout">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
              <polyline points="16 17 21 12 16 7" />
              <line x1="21" y1="12" x2="9" y2="12" />
            </svg>
            {{ $t("nav.logout") }}
          </button>
        </div>
        <div v-else class="navbar__mobile-guest">
          <router-link
            to="/login"
            class="navbar__mobile-login"
            @click="closeMobileMenu"
          >
            {{ $t("nav.login") }}
          </router-link>
          <router-link
            to="/register"
            class="navbar__mobile-register"
            @click="closeMobileMenu"
          >
            {{ $t("nav.register") }}
          </router-link>
        </div>
      </div>
    </Transition>

    <!-- Overlay -->
    <Transition name="fade">
      <div
        v-if="mobileMenuOpen"
        class="navbar__overlay"
        @click="closeMobileMenu"
      ></div>
    </Transition>
  </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import i18n from "@/locales";
import { useAuthStore } from "@/stores/auth";
import { useSettingsStore } from "@/stores/settings";

const router = useRouter();
const { locale } = useI18n({ useScope: "global" });
const authStore = useAuthStore();
const settingsStore = useSettingsStore();

const mobileMenuOpen = ref(false);
const userMenuOpen = ref(false);
const userDropdown = ref(null);
const isScrolled = ref(false);
const currentLocale = ref(locale.value);

const setLocale = (lang) => {
  currentLocale.value = lang;
  locale.value = lang;
  i18n.global.locale.value = lang;
  localStorage.setItem("locale", lang);
  settingsStore.setLocale(lang);
  settingsStore.refreshSettings();
};

// Watch bilingual setting changes
watch(
  () => settingsStore.bilingualEnabled,
  (newValue) => {
    if (!settingsStore.loaded) return;
    if (!newValue && currentLocale.value !== settingsStore.defaultLanguage) {
      setLocale(settingsStore.defaultLanguage);
    }
  }
);

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value;
  if (mobileMenuOpen.value) {
    document.body.style.overflow = "hidden";
  } else {
    document.body.style.overflow = "";
  }
};

const closeMobileMenu = () => {
  mobileMenuOpen.value = false;
  document.body.style.overflow = "";
};

const toggleUserMenu = () => {
  userMenuOpen.value = !userMenuOpen.value;
};

const handleLogout = async () => {
  await authStore.logout();
  userMenuOpen.value = false;
  closeMobileMenu();
  router.push("/");
};

const handleScroll = () => {
  isScrolled.value = window.scrollY > 20;
};

const handleClickOutside = (event) => {
  if (userDropdown.value && !userDropdown.value.contains(event.target)) {
    userMenuOpen.value = false;
  }
};

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
  document.addEventListener("click", handleClickOutside);
  handleScroll();

  settingsStore.setLocale(locale.value);

  // Fetch settings để có thông tin bilingual
  settingsStore.fetchSettings().then(() => {
    // Nếu bilingual bị tắt, chuyển về ngôn ngữ mặc định
    if (
      !settingsStore.bilingualEnabled &&
      currentLocale.value !== settingsStore.defaultLanguage
    ) {
      setLocale(settingsStore.defaultLanguage);
    }
  });
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
  document.removeEventListener("click", handleClickOutside);
  document.body.style.overflow = "";
});
</script>

<style scoped>
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  transition: all 0.3s ease;
  border-bottom: 1px solid transparent;
}

.navbar--scrolled {
  background: rgba(255, 255, 255, 0.98);
  border-bottom-color: rgba(0, 0, 0, 0.08);
  box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
}

.navbar__container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 1rem;
  height: 72px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

/* Logo */
.navbar__logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  text-decoration: none;
  flex-shrink: 0;
}

.navbar__logo-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.navbar__logo-icon svg {
  width: 22px;
  height: 22px;
}

.navbar__logo-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 8px;
}

.navbar__logo-text {
  font-size: 1.35rem;
  font-weight: 700;
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Desktop Navigation */
.navbar__nav {
  display: none;
  align-items: center;
  gap: 0.25rem;
}

@media (min-width: 1024px) {
  .navbar__nav {
    display: flex;
  }
}

.navbar__link {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 0.75rem;
  color: #374151;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.9rem;
  border-radius: 10px;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.navbar__link--tour-info {
  padding-left: 0.6rem;
  padding-right: 0.6rem;
  font-size: 0.88rem;
}

.navbar__link:hover {
  color: #2563eb;
  background: rgba(37, 99, 235, 0.08);
}

.navbar__link--active {
  color: #2563eb;
  background: rgba(37, 99, 235, 0.1);
}

.navbar__link-icon {
  display: none;
}

@media (min-width: 1200px) {
  .navbar__link-icon {
    display: flex;
    width: 18px;
    height: 18px;
  }

  .navbar__link-icon svg {
    width: 100%;
    height: 100%;
  }
}

/* Actions */
.navbar__actions {
  display: none;
  align-items: center;
  gap: 1rem;
}

@media (min-width: 1024px) {
  .navbar__actions {
    display: flex;
  }
}

/* Language Switcher */
.navbar__lang {
  display: flex;
  gap: 0.25rem;
  padding: 0.25rem;
  background: #f3f4f6;
  border-radius: 10px;
}

.navbar__lang-btn {
  padding: 0.4rem 0.6rem;
  background: transparent;
  border: none;
  border-radius: 8px;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  opacity: 0.6;
}

.navbar__lang-btn:hover {
  opacity: 1;
}

.navbar__lang-btn--active {
  background: white;
  opacity: 1;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

/* Action Buttons */
.navbar__action-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  color: #374151;
  background: transparent;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s ease;
}

.navbar__action-btn svg {
  width: 20px;
  height: 20px;
}

.navbar__action-btn:hover {
  color: #2563eb;
  background: rgba(37, 99, 235, 0.08);
}

.navbar__action-btn--admin {
  color: #7c3aed;
}

.navbar__action-btn--admin:hover {
  color: #6d28d9;
  background: rgba(124, 58, 237, 0.08);
}

/* User Section */
.navbar__user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

/* User Dropdown */
.navbar__dropdown {
  position: relative;
}

.navbar__user-btn {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.4rem 0.6rem 0.4rem 0.4rem;
  background: #f3f4f6;
  border: none;
  border-radius: 50px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.navbar__user-btn:hover {
  background: #e5e7eb;
}

.navbar__avatar {
  width: 32px;
  height: 32px;
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.9rem;
}

.navbar__user-name {
  font-weight: 500;
  color: #1f2937;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.navbar__chevron {
  width: 16px;
  height: 16px;
  color: #6b7280;
  transition: transform 0.2s ease;
}

.navbar__chevron--open {
  transform: rotate(180deg);
}

.navbar__dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  min-width: 200px;
  background: white;
  border-radius: 14px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
  border: 1px solid rgba(0, 0, 0, 0.06);
  padding: 0.5rem;
  z-index: 100;
}

.navbar__dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.7rem 1rem;
  color: #374151;
  text-decoration: none;
  border-radius: 10px;
  transition: all 0.2s ease;
  width: 100%;
  border: none;
  background: transparent;
  cursor: pointer;
  font-size: 0.95rem;
}

.navbar__dropdown-item svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

.navbar__dropdown-item:hover {
  background: #f3f4f6;
  color: #2563eb;
}

.navbar__dropdown-item--danger {
  color: #dc2626;
}

.navbar__dropdown-item--danger:hover {
  background: #fef2f2;
  color: #dc2626;
}

.navbar__dropdown-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 0.5rem 0;
}

/* Guest Buttons */
.navbar__guest {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.navbar__login-btn {
  padding: 0.6rem 1.25rem;
  color: #374151;
  text-decoration: none;
  font-weight: 500;
  border-radius: 10px;
  transition: all 0.2s ease;
}

.navbar__login-btn:hover {
  color: #2563eb;
  background: rgba(37, 99, 235, 0.08);
}

.navbar__register-btn {
  padding: 0.6rem 1.5rem;
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  color: white;
  text-decoration: none;
  font-weight: 600;
  border-radius: 10px;
  transition: all 0.2s ease;
  box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
}

.navbar__register-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
}

/* Mobile Toggle */
.navbar__mobile-toggle {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  background: transparent;
  border: none;
  cursor: pointer;
  border-radius: 12px;
  transition: background 0.2s ease;
}

.navbar__mobile-toggle:hover {
  background: #f3f4f6;
}

@media (min-width: 1024px) {
  .navbar__mobile-toggle {
    display: none;
  }
}

.navbar__hamburger {
  width: 22px;
  height: 16px;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.navbar__hamburger span {
  display: block;
  width: 100%;
  height: 2px;
  background: #1f2937;
  border-radius: 2px;
  transition: all 0.3s ease;
}

.navbar__hamburger--open span:nth-child(1) {
  transform: translateY(7px) rotate(45deg);
}

.navbar__hamburger--open span:nth-child(2) {
  opacity: 0;
  transform: scaleX(0);
}

.navbar__hamburger--open span:nth-child(3) {
  transform: translateY(-7px) rotate(-45deg);
}

/* Mobile Menu */
.navbar__mobile {
  position: fixed;
  top: 72px;
  right: 0;
  width: 100%;
  max-width: 360px;
  height: calc(100vh - 72px);
  background: white;
  overflow-y: auto;
  padding: 1.5rem;
  z-index: 999;
}

@media (max-width: 400px) {
  .navbar__mobile {
    max-width: 100%;
  }
}

.navbar__mobile-nav {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.navbar__mobile-link {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  color: #374151;
  text-decoration: none;
  font-weight: 500;
  font-size: 1.05rem;
  border-radius: 14px;
  transition: all 0.2s ease;
}

.navbar__mobile-link svg {
  width: 22px;
  height: 22px;
  flex-shrink: 0;
}

.navbar__mobile-link:hover,
.navbar__mobile-link.router-link-active {
  color: #2563eb;
  background: rgba(37, 99, 235, 0.08);
}

.navbar__mobile-link--admin {
  color: #7c3aed;
}

.navbar__mobile-link--admin:hover {
  color: #6d28d9;
  background: rgba(124, 58, 237, 0.08);
}

.navbar__mobile-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 1rem 0;
}

/* Mobile Language */
.navbar__mobile-lang {
  padding: 0.5rem 0;
}

.navbar__mobile-lang-label {
  display: block;
  font-size: 0.85rem;
  color: #6b7280;
  margin-bottom: 0.75rem;
  padding-left: 0.25rem;
}

.navbar__mobile-lang-btns {
  display: flex;
  gap: 0.5rem;
}

.navbar__mobile-lang-btn {
  flex: 1;
  padding: 0.75rem;
  background: #f3f4f6;
  border: 2px solid transparent;
  border-radius: 12px;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.navbar__mobile-lang-btn:hover {
  background: #e5e7eb;
}

.navbar__mobile-lang-btn--active {
  background: rgba(37, 99, 235, 0.1);
  border-color: #2563eb;
  color: #2563eb;
}

/* Mobile Auth */
.navbar__mobile-auth {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.navbar__mobile-user {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 14px;
  margin-bottom: 0.5rem;
}

.navbar__mobile-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.navbar__mobile-user-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.navbar__mobile-user-name {
  font-weight: 600;
  color: #1f2937;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.navbar__mobile-user-email {
  font-size: 0.875rem;
  color: #6b7280;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.navbar__mobile-logout {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  color: #dc2626;
  background: transparent;
  border: none;
  font-weight: 500;
  font-size: 1.05rem;
  border-radius: 14px;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
}

.navbar__mobile-logout svg {
  width: 22px;
  height: 22px;
}

.navbar__mobile-logout:hover {
  background: #fef2f2;
}

/* Mobile Guest */
.navbar__mobile-guest {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.navbar__mobile-login {
  display: block;
  padding: 1rem;
  text-align: center;
  color: #374151;
  text-decoration: none;
  font-weight: 500;
  font-size: 1.05rem;
  border: 2px solid #e5e7eb;
  border-radius: 14px;
  transition: all 0.2s ease;
}

.navbar__mobile-login:hover {
  border-color: #2563eb;
  color: #2563eb;
}

.navbar__mobile-register {
  display: block;
  padding: 1rem;
  text-align: center;
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  color: white;
  text-decoration: none;
  font-weight: 600;
  font-size: 1.05rem;
  border-radius: 14px;
  transition: all 0.2s ease;
}

.navbar__mobile-register:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
}

/* Overlay */
.navbar__overlay {
  position: fixed;
  top: 72px;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 998;
}

/* Transitions */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.mobile-menu-enter-active,
.mobile-menu-leave-active {
  transition: transform 0.3s ease;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
  transform: translateX(100%);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
