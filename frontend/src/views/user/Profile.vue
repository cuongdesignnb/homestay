<template>
  <div class="profile-page">
    <!-- Hero Section -->
    <section class="page-hero">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <Breadcrumb :items="breadcrumbItems" />
        <h1>{{ $t("nav.profile") }}</h1>
        <p class="hero-subtitle">
          {{
            $t(
              "profile.subtitle",
              "Manage your personal information and security"
            )
          }}
        </p>
      </div>
    </section>

    <div class="container mx-auto px-4 py-12 max-w-4xl">
      <div class="profile-grid">
        <!-- Sidebar -->
        <aside class="profile-sidebar">
          <div class="profile-avatar-card">
            <div class="avatar-wrapper">
              <div class="avatar">{{ avatarLetter }}</div>
              <span class="avatar-badge">✓</span>
            </div>
            <h3>{{ form.name }}</h3>
            <p class="text-muted">{{ form.email }}</p>
            <div class="member-since">
              <span class="icon">📅</span>
              {{ $t("profile.member_since", "Member since") }} {{ memberSince }}
            </div>
          </div>

          <nav class="profile-nav">
            <button
              :class="['nav-item', { active: activeSection === 'info' }]"
              @click="activeSection = 'info'"
            >
              <span class="icon">👤</span>
              {{ $t("profile.personal_info", "Personal Info") }}
            </button>
            <button
              :class="['nav-item', { active: activeSection === 'security' }]"
              @click="activeSection = 'security'"
            >
              <span class="icon">🔒</span>
              {{ $t("profile.security", "Security") }}
            </button>
          </nav>
        </aside>

        <!-- Main Content -->
        <main class="profile-content">
          <!-- Message Alert -->
          <div v-if="message" :class="['alert', messageType]">
            <span class="alert-icon">{{
              messageType === "success" ? "✓" : "⚠"
            }}</span>
            {{ message }}
          </div>

          <!-- Personal Info Section -->
          <div v-if="activeSection === 'info'" class="content-section">
            <div class="section-header">
              <h2>{{ $t("profile.personal_info", "Personal Information") }}</h2>
              <p>
                {{
                  $t(
                    "profile.personal_info_desc",
                    "Update your personal details here"
                  )
                }}
              </p>
            </div>

            <form @submit.prevent="updateProfile" class="profile-form">
              <div class="form-group">
                <label>{{ $t("auth.name") }}</label>
                <div class="input-wrapper">
                  <span class="input-icon">👤</span>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    placeholder="Your full name"
                  />
                </div>
              </div>

              <div class="form-group">
                <label>{{ $t("auth.email") }}</label>
                <div class="input-wrapper disabled">
                  <span class="input-icon">✉️</span>
                  <input v-model="form.email" type="email" disabled />
                  <span class="input-badge">{{
                    $t("profile.verified", "Verified")
                  }}</span>
                </div>
              </div>

              <div class="form-group">
                <label>{{ $t("auth.phone") }}</label>
                <div class="input-wrapper">
                  <span class="input-icon">📱</span>
                  <input
                    v-model="form.phone"
                    type="tel"
                    placeholder="+84 xxx xxx xxx"
                  />
                </div>
              </div>

              <div class="form-group">
                <label>{{ $t("profile.address", "Address") }}</label>
                <div class="input-wrapper textarea">
                  <span class="input-icon">📍</span>
                  <textarea
                    v-model="form.address"
                    rows="3"
                    placeholder="Your address"
                  ></textarea>
                </div>
              </div>

              <div class="form-actions">
                <button
                  type="submit"
                  :disabled="loading"
                  class="btn btn-primary"
                >
                  <span v-if="loading" class="spinner-sm"></span>
                  {{ loading ? $t("common.loading") : $t("common.save") }}
                </button>
              </div>
            </form>
          </div>

          <!-- Security Section -->
          <div v-if="activeSection === 'security'" class="content-section">
            <div class="section-header">
              <h2>{{ $t("profile.change_password", "Change Password") }}</h2>
              <p>
                {{
                  $t(
                    "profile.password_desc",
                    "Ensure your account is using a secure password"
                  )
                }}
              </p>
            </div>

            <form @submit.prevent="updatePassword" class="profile-form">
              <div class="form-group">
                <label>{{
                  $t("profile.current_password", "Current Password")
                }}</label>
                <div class="input-wrapper">
                  <span class="input-icon">🔑</span>
                  <input
                    v-model="passwordForm.current_password"
                    type="password"
                    required
                  />
                </div>
              </div>

              <div class="form-group">
                <label>{{ $t("profile.new_password", "New Password") }}</label>
                <div class="input-wrapper">
                  <span class="input-icon">🔒</span>
                  <input
                    v-model="passwordForm.password"
                    type="password"
                    required
                    minlength="8"
                  />
                </div>
                <p class="form-hint">
                  {{ $t("profile.password_hint", "Minimum 8 characters") }}
                </p>
              </div>

              <div class="form-group">
                <label>{{
                  $t("profile.confirm_password", "Confirm New Password")
                }}</label>
                <div class="input-wrapper">
                  <span class="input-icon">🔒</span>
                  <input
                    v-model="passwordForm.password_confirmation"
                    type="password"
                    required
                  />
                </div>
              </div>

              <div class="form-actions">
                <button
                  type="submit"
                  :disabled="passwordLoading"
                  class="btn btn-primary"
                >
                  <span v-if="passwordLoading" class="spinner-sm"></span>
                  {{
                    passwordLoading
                      ? $t("common.loading")
                      : $t("profile.update_password", "Update Password")
                  }}
                </button>
              </div>
            </form>
          </div>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import { useAuthStore } from "@/stores/auth";
import api from "@/services/api";
import Breadcrumb from "@/components/Breadcrumb.vue";
import dayjs from "dayjs";

const { t } = useI18n();

const authStore = useAuthStore();

const activeSection = ref("info");
const form = ref({
  name: "",
  email: "",
  phone: "",
  address: "",
});

const passwordForm = ref({
  current_password: "",
  password: "",
  password_confirmation: "",
});

const loading = ref(false);
const passwordLoading = ref(false);
const message = ref("");
const messageType = ref("");

const breadcrumbItems = computed(() => [
  { label: t("nav.home"), path: "/" },
  { label: t("nav.profile"), path: "/profile" },
]);

const avatarLetter = computed(() => {
  return form.value.name ? form.value.name.charAt(0).toUpperCase() : "U";
});

const memberSince = computed(() => {
  if (authStore.user?.created_at) {
    return dayjs(authStore.user.created_at).format("MMM YYYY");
  }
  return "N/A";
});

const updateProfile = async () => {
  loading.value = true;
  message.value = "";

  try {
    await api.put("/user/profile", form.value);
    await authStore.fetchUser();
    message.value = "Profile updated successfully";
    messageType.value = "success";
  } catch (error) {
    message.value = error.response?.data?.message || "Update failed";
    messageType.value = "error";
  } finally {
    loading.value = false;
  }
};

const updatePassword = async () => {
  if (
    passwordForm.value.password !== passwordForm.value.password_confirmation
  ) {
    message.value = "Passwords do not match";
    messageType.value = "error";
    return;
  }

  passwordLoading.value = true;
  message.value = "";

  try {
    await api.put("/user/password", passwordForm.value);
    message.value = "Password updated successfully";
    messageType.value = "success";
    passwordForm.value = {
      current_password: "",
      password: "",
      password_confirmation: "",
    };
  } catch (error) {
    message.value = error.response?.data?.message || "Password update failed";
    messageType.value = "error";
  } finally {
    passwordLoading.value = false;
  }
};

onMounted(() => {
  if (authStore.user) {
    form.value = {
      name: authStore.user.name,
      email: authStore.user.email,
      phone: authStore.user.phone || "",
      address: authStore.user.address || "",
    };
  }
});
</script>

<style scoped>
.profile-page {
  min-height: 100vh;
  background: var(--bg-primary, #f8fafc);
}

/* Hero Section */
.page-hero {
  position: relative;
  background: linear-gradient(135deg, #1e40af 0%, #7c3aed 50%, #be185d 100%);
  padding: 4rem 1rem 3rem;
  margin-bottom: 0;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.hero-content {
  position: relative;
  max-width: 1200px;
  margin: 0 auto;
  text-align: center;
  color: white;
}

.hero-content h1 {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 1rem 0 0.5rem;
}

.hero-subtitle {
  font-size: 1.1rem;
  opacity: 0.9;
}

/* Profile Grid */
.profile-grid {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 2rem;
  align-items: start;
}

@media (max-width: 900px) {
  .profile-grid {
    grid-template-columns: 1fr;
  }
}

/* Sidebar */
.profile-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.profile-avatar-card {
  background: white;
  border-radius: 1rem;
  padding: 2rem;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.avatar-wrapper {
  position: relative;
  display: inline-block;
  margin-bottom: 1rem;
}

.avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 700;
}

.avatar-badge {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 24px;
  height: 24px;
  background: #10b981;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  border: 3px solid white;
}

.profile-avatar-card h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.text-muted {
  color: #64748b;
  font-size: 0.9rem;
}

.member-since {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
  color: #64748b;
  font-size: 0.85rem;
}

/* Profile Nav */
.profile-nav {
  background: white;
  border-radius: 1rem;
  padding: 0.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.nav-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  border: none;
  background: transparent;
  border-radius: 0.75rem;
  font-size: 0.95rem;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}

.nav-item:hover {
  background: #f1f5f9;
}

.nav-item.active {
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  color: white;
}

.nav-item .icon {
  font-size: 1.1rem;
}

/* Main Content */
.profile-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Alert */
.alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  border-radius: 0.75rem;
  font-weight: 500;
}

.alert.success {
  background: #ecfdf5;
  color: #059669;
  border: 1px solid #a7f3d0;
}

.alert.error {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.alert-icon {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-size: 0.85rem;
}

.alert.success .alert-icon {
  background: #059669;
  color: white;
}

.alert.error .alert-icon {
  background: #dc2626;
  color: white;
}

/* Content Section */
.content-section {
  background: white;
  border-radius: 1rem;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.section-header {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.section-header h2 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.section-header p {
  color: #64748b;
  font-size: 0.95rem;
}

/* Form */
.profile-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 500;
  font-size: 0.9rem;
  color: #374151;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background: #f8fafc;
  border: 2px solid #e2e8f0;
  border-radius: 0.75rem;
  transition: all 0.2s;
}

.input-wrapper:focus-within {
  border-color: #3b82f6;
  background: white;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.input-wrapper.disabled {
  background: #f1f5f9;
}

.input-icon {
  padding-left: 1rem;
  font-size: 1.1rem;
}

.input-wrapper input,
.input-wrapper textarea {
  flex: 1;
  padding: 0.875rem 1rem;
  border: none;
  background: transparent;
  font-size: 1rem;
  outline: none;
}

.input-wrapper textarea {
  min-height: 80px;
  resize: vertical;
}

.input-wrapper.textarea {
  align-items: flex-start;
}

.input-wrapper.textarea .input-icon {
  padding-top: 0.875rem;
}

.input-badge {
  padding: 0.25rem 0.75rem;
  background: #10b981;
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 1rem;
  margin-right: 0.75rem;
}

.form-hint {
  font-size: 0.8rem;
  color: #64748b;
}

.form-actions {
  margin-top: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem 2rem;
  font-size: 1rem;
  font-weight: 600;
  border-radius: 0.75rem;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spinner-sm {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Responsive */
@media (max-width: 640px) {
  .hero-content h1 {
    font-size: 2rem;
  }

  .content-section {
    padding: 1.5rem;
  }
}
</style>
