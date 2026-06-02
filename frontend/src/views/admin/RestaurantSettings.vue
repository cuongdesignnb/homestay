<template>
  <div class="restaurant-settings-page">
    <div class="page-header">
      <h1>{{ $t("admin.restaurant.settings_title") }}</h1>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
    </div>

    <form v-else @submit.prevent="saveSettings" class="settings-form">
      <!-- Restaurant Name -->
      <div class="form-section">
        <h2>{{ $t("admin.restaurant.basic_info") }}</h2>

        <div class="form-row">
          <div class="form-group">
            <label>{{ $t("admin.restaurant.name_vi") }}</label>
            <input
              type="text"
              v-model="form.restaurant_name_vi"
              :placeholder="$t('admin.restaurant.name_placeholder')"
            />
          </div>
          <div class="form-group">
            <label>{{ $t("admin.restaurant.name_en") }}</label>
            <input
              type="text"
              v-model="form.restaurant_name_en"
              placeholder="Restaurant name in English"
            />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>{{ $t("admin.restaurant.phone") }}</label>
            <input
              type="text"
              v-model="form.restaurant_phone_vi"
              placeholder="+84 123 456 789"
            />
          </div>
          <div class="form-group">
            <label>{{ $t("admin.restaurant.opening_hours_vi") }}</label>
            <input
              type="text"
              v-model="form.restaurant_opening_hours_vi"
              placeholder="7:00 - 22:00"
            />
          </div>
          <div class="form-group">
            <label>{{ $t("admin.restaurant.opening_hours_en") }}</label>
            <input
              type="text"
              v-model="form.restaurant_opening_hours_en"
              placeholder="7:00 AM - 10:00 PM"
            />
          </div>
        </div>
      </div>

      <!-- Banner Image -->
      <div class="form-section">
        <h2>{{ $t("admin.restaurant.banner") }}</h2>
        <div class="banner-picker">
          <div v-if="form.restaurant_banner_vi" class="banner-preview">
            <img :src="form.restaurant_banner_vi" alt="Banner" />
            <button
              type="button"
              class="btn-remove"
              @click="form.restaurant_banner_vi = ''"
            >
              ×
            </button>
          </div>
          <button type="button" class="btn-secondary" @click="openMediaLibrary">
            {{ $t("admin.restaurant.select_banner") }}
          </button>
        </div>
      </div>

      <!-- Introduction -->
      <div class="form-section">
        <h2>{{ $t("admin.restaurant.introduction") }}</h2>

        <div class="form-group">
          <label>{{ $t("admin.restaurant.intro_vi") }}</label>
          <textarea
            v-model="form.restaurant_intro_vi"
            rows="5"
            :placeholder="$t('admin.restaurant.intro_placeholder')"
          ></textarea>
        </div>

        <div class="form-group">
          <label>{{ $t("admin.restaurant.intro_en") }}</label>
          <textarea
            v-model="form.restaurant_intro_en"
            rows="5"
            placeholder="Introduction in English..."
          ></textarea>
        </div>
      </div>

      <!-- Submit -->
      <div class="form-actions">
        <button type="submit" class="btn-primary" :disabled="saving">
          {{ saving ? $t("common.saving") : $t("common.save") }}
        </button>
      </div>
    </form>

    <!-- Media Library Modal -->
    <MediaLibraryModal
      v-model="showMediaLibrary"
      :multiple="false"
      @select="onMediaSelect"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import MediaLibraryModal from "@/components/admin/MediaLibraryModal.vue";

const { t } = useI18n();

const loading = ref(true);
const saving = ref(false);
const showMediaLibrary = ref(false);

const form = ref({
  restaurant_name_vi: "",
  restaurant_name_en: "",
  restaurant_intro_vi: "",
  restaurant_intro_en: "",
  restaurant_banner_vi: "",
  restaurant_opening_hours_vi: "",
  restaurant_opening_hours_en: "",
  restaurant_phone_vi: "",
});

const normalizeSettingsList = (data) => {
  if (Array.isArray(data)) return data;
  if (data && typeof data === "object") {
    const flat = [];
    Object.keys(data).forEach((group) => {
      if (Array.isArray(data[group])) {
        flat.push(...data[group]);
      }
    });
    return flat;
  }
  return [];
};

const fetchSettings = async () => {
  loading.value = true;
  try {
    const response = await api.get("/admin/settings");
    const settings = normalizeSettingsList(response.data.data);

    // Map settings to form
    const settingsMap = {};
    settings.forEach((s) => {
      if (s?.key) settingsMap[s.key] = s;
    });

    form.value = {
      restaurant_name_vi: settingsMap.restaurant_name?.value_vi || "",
      restaurant_name_en: settingsMap.restaurant_name?.value_en || "",
      restaurant_intro_vi: settingsMap.restaurant_intro?.value_vi || "",
      restaurant_intro_en: settingsMap.restaurant_intro?.value_en || "",
      restaurant_banner_vi: settingsMap.restaurant_banner?.value_vi || "",
      restaurant_opening_hours_vi:
        settingsMap.restaurant_opening_hours?.value_vi || "",
      restaurant_opening_hours_en:
        settingsMap.restaurant_opening_hours?.value_en || "",
      restaurant_phone_vi: settingsMap.restaurant_phone?.value_vi || "",
    };
  } catch (error) {
    console.error("Error fetching settings:", error);
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    // Backend expects: { settings: { [key]: { value_vi, value_en, group, type, ... } } }
    const settingsToSave = {
      restaurant_name: {
        value_vi: form.value.restaurant_name_vi,
        value_en: form.value.restaurant_name_en,
        group: "restaurant",
        type: "text",
        label: "Restaurant Name",
      },
      restaurant_intro: {
        value_vi: form.value.restaurant_intro_vi,
        value_en: form.value.restaurant_intro_en,
        group: "restaurant",
        type: "textarea",
        label: "Restaurant Introduction",
      },
      restaurant_banner: {
        value_vi: form.value.restaurant_banner_vi,
        value_en: form.value.restaurant_banner_vi,
        group: "restaurant",
        type: "image",
        label: "Restaurant Banner",
      },
      restaurant_opening_hours: {
        value_vi: form.value.restaurant_opening_hours_vi,
        value_en: form.value.restaurant_opening_hours_en,
        group: "restaurant",
        type: "text",
        label: "Opening Hours",
      },
      restaurant_phone: {
        value_vi: form.value.restaurant_phone_vi,
        value_en: form.value.restaurant_phone_vi,
        group: "restaurant",
        type: "text",
        label: "Phone",
      },
    };

    await api.post("/admin/settings/bulk", { settings: settingsToSave });
    await fetchSettings();
    alert(t("common.saved"));
  } catch (error) {
    console.error("Error saving settings:", error);
    alert(error.response?.data?.message || t("common.error"));
  } finally {
    saving.value = false;
  }
};

const openMediaLibrary = () => {
  showMediaLibrary.value = true;
};

const onMediaSelect = (mediaArray) => {
  if (mediaArray && mediaArray.length > 0) {
    form.value.restaurant_banner_vi = mediaArray[0].url;
  }
  showMediaLibrary.value = false;
};

onMounted(() => {
  fetchSettings();
});
</script>

<style scoped>
.restaurant-settings-page {
  padding: 20px;
  max-width: 900px;
}

.page-header {
  margin-bottom: 24px;
}

.page-header h1 {
  margin: 0;
  font-size: 24px;
  color: #2c3e50;
}

.loading-container {
  display: flex;
  justify-content: center;
  padding: 60px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.settings-form {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.form-section {
  margin-bottom: 32px;
  padding-bottom: 24px;
  border-bottom: 1px solid #eee;
}

.form-section:last-of-type {
  border-bottom: none;
  margin-bottom: 0;
}

.form-section h2 {
  margin: 0 0 16px 0;
  font-size: 18px;
  color: #2c3e50;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 16px;
}

.form-group label {
  font-weight: 500;
  color: #333;
  font-size: 14px;
}

.form-group input,
.form-group textarea {
  padding: 10px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
  color: #333;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: #888;
  opacity: 1;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #3498db;
}

.banner-picker {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.banner-preview {
  position: relative;
  max-width: 400px;
  border-radius: 8px;
  overflow: hidden;
}

.banner-preview img {
  width: 100%;
  height: auto;
  display: block;
}

.btn-remove {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 32px;
  height: 32px;
  background: rgba(231, 76, 60, 0.9);
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  font-size: 20px;
  line-height: 1;
}

.btn-secondary {
  padding: 10px 20px;
  background: #ecf0f1;
  color: #333;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  width: fit-content;
}

.btn-secondary:hover {
  background: #ddd;
}

.form-actions {
  margin-top: 24px;
  display: flex;
  justify-content: flex-end;
}

.btn-primary {
  padding: 12px 32px;
  background: #3498db;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 500;
}

.btn-primary:hover:not(:disabled) {
  background: #2980b9;
}

.btn-primary:disabled {
  background: #bdc3c7;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
