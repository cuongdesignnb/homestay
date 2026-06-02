<template>
  <div class="menu-categories-page">
    <div class="page-header">
      <h1>{{ $t("admin.menu.categories_title") }}</h1>
      <button class="btn-primary" @click="openCreateModal">
        <span class="icon">+</span>
        {{ $t("admin.menu.add_category") }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
    </div>

    <!-- Categories List -->
    <div v-else class="categories-grid">
      <div
        v-for="category in categories"
        :key="category.id"
        class="category-card"
        :class="{ inactive: !category.is_active }"
      >
        <div class="category-image">
          <img
            v-if="category.image"
            :src="category.image"
            :alt="category.name"
          />
          <div v-else class="no-image">
            <span>🍽️</span>
          </div>
          <span v-if="!category.is_active" class="inactive-badge">{{
            $t("common.inactive")
          }}</span>
        </div>

        <div class="category-content">
          <h3>{{ category.name }}</h3>
          <p v-if="category.name_en" class="name-en">{{ category.name_en }}</p>
          <p v-if="category.description" class="description">
            {{ category.description }}
          </p>
          <div class="meta">
            <span class="items-count">
              {{ category.items_count || 0 }} {{ $t("admin.menu.items") }}
            </span>
            <span class="sort-order">
              {{ $t("admin.menu.order") }}: {{ category.sort_order }}
            </span>
          </div>
        </div>

        <div class="category-actions">
          <button
            class="btn-icon"
            @click="editCategory(category)"
            :title="$t('common.edit')"
          >
            ✏️
          </button>
          <button
            class="btn-icon"
            @click="viewItems(category)"
            :title="$t('admin.menu.view_items')"
          >
            📋
          </button>
          <button
            class="btn-icon danger"
            @click="confirmDelete(category)"
            :title="$t('common.delete')"
          >
            🗑️
          </button>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="categories.length === 0" class="empty-state">
        <span class="icon">🍽️</span>
        <p>{{ $t("admin.menu.no_categories") }}</p>
        <button class="btn-primary" @click="openCreateModal">
          {{ $t("admin.menu.add_first_category") }}
        </button>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>
            {{
              editingCategory
                ? $t("admin.menu.edit_category")
                : $t("admin.menu.add_category")
            }}
          </h2>
          <button class="btn-close" @click="closeModal">×</button>
        </div>

        <form @submit.prevent="saveCategory" class="modal-body">
          <div class="form-row">
            <div v-if="shouldShowVietnamese" class="form-group">
              <label>{{ getFieldLabel($t("admin.menu.name"), true) }} *</label>
              <input
                type="text"
                v-model="form.name"
                :required="!shouldShowEnglish"
                :placeholder="$t('admin.menu.name_vi_placeholder')"
              />
            </div>
            <div v-if="shouldShowEnglish" class="form-group">
              <label>{{ getFieldLabel($t("admin.menu.name"), false) }} *</label>
              <input
                type="text"
                v-model="form.name_en"
                required
                :placeholder="$t('admin.menu.name_en_placeholder')"
              />
            </div>
          </div>

          <div class="form-row">
            <div v-if="shouldShowVietnamese" class="form-group">
              <label>{{
                getFieldLabel($t("admin.menu.description"), true)
              }}</label>
              <textarea
                v-model="form.description"
                rows="3"
                :placeholder="$t('admin.menu.description_placeholder')"
              ></textarea>
            </div>
            <div v-if="shouldShowEnglish" class="form-group">
              <label>{{
                getFieldLabel($t("admin.menu.description"), false)
              }}</label>
              <textarea
                v-model="form.description_en"
                rows="3"
                :placeholder="$t('admin.menu.description_placeholder')"
              ></textarea>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>{{ $t("admin.menu.image") }}</label>
              <div class="image-picker">
                <div v-if="form.image" class="selected-image">
                  <img :src="form.image" alt="Category" />
                  <button
                    type="button"
                    class="btn-remove"
                    @click="form.image = ''"
                  >
                    ×
                  </button>
                </div>
                <button
                  type="button"
                  class="btn-secondary"
                  @click="openMediaLibrary"
                >
                  {{ $t("admin.menu.select_image") }}
                </button>
              </div>
            </div>

            <div class="form-group-half">
              <div class="form-group">
                <label>{{ $t("admin.menu.sort_order") }}</label>
                <input type="number" v-model.number="form.sort_order" min="0" />
              </div>
              <div class="form-group">
                <label class="checkbox-label">
                  <input type="checkbox" v-model="form.is_active" />
                  {{ $t("admin.menu.is_active") }}
                </label>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn-secondary" @click="closeModal">
              {{ $t("common.cancel") }}
            </button>
            <button type="submit" class="btn-primary" :disabled="saving">
              {{ saving ? $t("common.saving") : $t("common.save") }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Media Library Modal -->
    <MediaLibraryModal
      v-model="showMediaLibrary"
      :multiple="false"
      @select="onMediaSelect"
    />

    <!-- Delete Confirmation -->
    <div
      v-if="showDeleteConfirm"
      class="modal-overlay"
      @click.self="showDeleteConfirm = false"
    >
      <div class="modal modal-small">
        <div class="modal-header">
          <h2>{{ $t("common.confirm_delete") }}</h2>
        </div>
        <div class="modal-body">
          <p>{{ $t("admin.menu.delete_category_confirm") }}</p>
          <p class="category-name">{{ deletingCategory?.name }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showDeleteConfirm = false">
            {{ $t("common.cancel") }}
          </button>
          <button class="btn-danger" @click="deleteCategory" :disabled="saving">
            {{ $t("common.delete") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import MediaLibraryModal from "@/components/admin/MediaLibraryModal.vue";
import { useBilingualForm } from "@/composables/useBilingualForm";
import { useSettingsStore } from "@/stores/settings";

const { t } = useI18n();
const router = useRouter();
const settingsStore = useSettingsStore();

// Bilingual form support
const {
  shouldShowVietnamese,
  shouldShowEnglish,
  getFieldLabel,
  prepareBilingualData,
} = useBilingualForm();

const categories = ref([]);
const loading = ref(true);
const saving = ref(false);
const showModal = ref(false);
const showMediaLibrary = ref(false);
const showDeleteConfirm = ref(false);
const editingCategory = ref(null);
const deletingCategory = ref(null);

const defaultForm = {
  name: "",
  name_en: "",
  description: "",
  description_en: "",
  image: "",
  sort_order: 0,
  is_active: true,
};

const form = ref({ ...defaultForm });

const fetchCategories = async () => {
  loading.value = true;
  try {
    const response = await api.get("/admin/menu-categories");
    categories.value = response.data.data || [];
  } catch (error) {
    console.error("Error fetching categories:", error);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  editingCategory.value = null;
  form.value = { ...defaultForm };
  showModal.value = true;
};

const editCategory = (category) => {
  editingCategory.value = category;
  form.value = {
    name: category.name,
    name_en: category.name_en || "",
    description: category.description || "",
    description_en: category.description_en || "",
    image: category.image || "",
    sort_order: category.sort_order || 0,
    is_active: category.is_active,
  };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingCategory.value = null;
  form.value = { ...defaultForm };
};

const saveCategory = async () => {
  saving.value = true;
  try {
    if (!settingsStore.bilingualEnabled) {
      if (!form.value.name && form.value.name_en) {
        form.value.name = form.value.name_en;
      }
      if (!form.value.description && form.value.description_en) {
        form.value.description = form.value.description_en;
      }
    }
    // Prepare bilingual data before sending
    const formData = prepareBilingualData(form.value);

    if (editingCategory.value) {
      await api.put(
        `/admin/menu-categories/${editingCategory.value.id}`,
        formData
      );
    } else {
      await api.post("/admin/menu-categories", formData);
    }
    closeModal();
    fetchCategories();
  } catch (error) {
    console.error("Error saving category:", error);
    alert(error.response?.data?.message || t("common.error"));
  } finally {
    saving.value = false;
  }
};

const confirmDelete = (category) => {
  deletingCategory.value = category;
  showDeleteConfirm.value = true;
};

const deleteCategory = async () => {
  saving.value = true;
  try {
    await api.delete(`/admin/menu-categories/${deletingCategory.value.id}`);
    showDeleteConfirm.value = false;
    deletingCategory.value = null;
    fetchCategories();
  } catch (error) {
    console.error("Error deleting category:", error);
    alert(error.response?.data?.message || t("common.error"));
  } finally {
    saving.value = false;
  }
};

const viewItems = (category) => {
  router.push(`/admin/menu-items?category=${category.id}`);
};

const openMediaLibrary = () => {
  showMediaLibrary.value = true;
};

const onMediaSelect = (mediaArray) => {
  if (mediaArray && mediaArray.length > 0) {
    form.value.image = mediaArray[0].url;
  }
  showMediaLibrary.value = false;
};

onMounted(() => {
  fetchCategories();
});
</script>

<style scoped>
.menu-categories-page {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.page-header h1 {
  margin: 0;
  font-size: 24px;
  color: #2c3e50;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: #3498db;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s;
}

.btn-primary:hover {
  background: #2980b9;
}

.btn-secondary {
  padding: 10px 20px;
  background: #ecf0f1;
  color: #333;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.btn-danger {
  padding: 10px 20px;
  background: #e74c3c;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
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

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.category-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.2s, box-shadow 0.2s;
}

.category-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.category-card.inactive {
  opacity: 0.7;
}

.category-image {
  position: relative;
  height: 160px;
  background: #f5f6fa;
}

.category-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.category-image .no-image {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  font-size: 48px;
}

.inactive-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  padding: 4px 8px;
  background: #e74c3c;
  color: white;
  font-size: 12px;
  border-radius: 4px;
}

.category-content {
  padding: 16px;
}

.category-content h3 {
  margin: 0 0 4px 0;
  font-size: 18px;
  color: #2c3e50;
}

.name-en {
  margin: 0 0 8px 0;
  color: #7f8c8d;
  font-size: 14px;
}

.description {
  margin: 0 0 12px 0;
  color: #555;
  font-size: 14px;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.meta {
  display: flex;
  gap: 16px;
  font-size: 13px;
  color: #7f8c8d;
}

.category-actions {
  display: flex;
  gap: 8px;
  padding: 12px 16px;
  background: #f8f9fa;
  border-top: 1px solid #eee;
}

.btn-icon {
  padding: 8px 12px;
  background: white;
  border: 1px solid #ddd;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: #f0f0f0;
}

.btn-icon.danger:hover {
  background: #fee;
  border-color: #e74c3c;
}

.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 12px;
}

.empty-state .icon {
  font-size: 64px;
  display: block;
  margin-bottom: 16px;
}

.empty-state p {
  color: #7f8c8d;
  margin-bottom: 20px;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-small {
  max-width: 400px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #eee;
}

.modal-header h2 {
  margin: 0;
  font-size: 20px;
}

.btn-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
}

.modal-body {
  padding: 20px;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px;
  border-top: 1px solid #eee;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}

/* Single column layout when only one field is visible */
.form-row:has(.form-group:only-child) {
  grid-template-columns: 1fr;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
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

.form-group-half {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox-label input {
  width: 18px;
  height: 18px;
}

.image-picker {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.selected-image {
  position: relative;
  width: 120px;
  height: 80px;
}

.selected-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
}

.btn-remove {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 24px;
  height: 24px;
  background: #e74c3c;
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  font-size: 16px;
  line-height: 1;
}

.category-name {
  font-weight: 600;
  color: #2c3e50;
  margin-top: 8px;
}

@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
  }

  .categories-grid {
    grid-template-columns: 1fr;
  }
}
</style>
