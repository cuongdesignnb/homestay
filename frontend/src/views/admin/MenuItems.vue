<template>
  <div class="menu-items-page">
    <div class="page-header">
      <div class="header-left">
        <h1>{{ $t("admin.menu.items_title") }}</h1>
        <div class="filter-bar">
          <select v-model="selectedCategory" @change="fetchItems">
            <option value="">{{ $t("admin.menu.all_categories") }}</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
          <input
            type="text"
            v-model="searchQuery"
            :placeholder="$t('common.search')"
            @input="debouncedSearch"
          />
        </div>
      </div>
      <button class="btn-primary" @click="openCreateModal">
        <span class="icon">+</span>
        {{ $t("admin.menu.add_item") }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
    </div>

    <!-- Items Table -->
    <div v-else class="items-table-container">
      <table class="items-table">
        <thead>
          <tr>
            <th style="width: 60px">{{ $t("admin.menu.image") }}</th>
            <th>{{ $t("admin.menu.name") }}</th>
            <th>{{ $t("admin.menu.category") }}</th>
            <th style="width: 120px">{{ $t("admin.menu.price") }}</th>
            <th style="width: 100px">{{ $t("admin.menu.status") }}</th>
            <th style="width: 120px">{{ $t("common.actions") }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td>
              <div class="item-image">
                <img v-if="item.image" :src="item.image" :alt="item.name" />
                <span v-else class="no-img">🍲</span>
              </div>
            </td>
            <td>
              <div class="item-name">
                <strong>{{ item.name }}</strong>
                <span v-if="item.name_en" class="name-en">{{
                  item.name_en
                }}</span>
                <span v-if="item.note" class="note">{{ item.note }}</span>
              </div>
            </td>
            <td>
              <span class="category-badge">{{ item.category?.name }}</span>
            </td>
            <td>
              <div class="price">
                {{ formatPrice(item.price) }}
                <span v-if="item.unit" class="unit">/{{ item.unit }}</span>
              </div>
            </td>
            <td>
              <button
                class="status-toggle"
                :class="{ active: item.is_available }"
                @click="toggleAvailability(item)"
              >
                {{
                  item.is_available
                    ? $t("admin.menu.available")
                    : $t("admin.menu.unavailable")
                }}
              </button>
            </td>
            <td>
              <div class="actions">
                <button
                  class="btn-icon"
                  @click="toggleFeatured(item)"
                  :class="{ featured: item.is_featured }"
                  :title="$t('admin.menu.featured')"
                >
                  ⭐
                </button>
                <button
                  class="btn-icon"
                  @click="editItem(item)"
                  :title="$t('common.edit')"
                >
                  ✏️
                </button>
                <button
                  class="btn-icon danger"
                  @click="confirmDelete(item)"
                  :title="$t('common.delete')"
                >
                  🗑️
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="items.length === 0">
            <td colspan="6" class="empty-row">
              {{ $t("admin.menu.no_items") }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h2>
            {{
              editingItem
                ? $t("admin.menu.edit_item")
                : $t("admin.menu.add_item")
            }}
          </h2>
          <button class="btn-close" @click="closeModal">×</button>
        </div>

        <form @submit.prevent="saveItem" class="modal-body">
          <div class="form-group">
            <label>{{ $t("admin.menu.category") }} *</label>
            <select v-model="form.category_id" required>
              <option value="">{{ $t("admin.menu.select_category") }}</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <div class="form-row">
            <div v-if="shouldShowVietnamese" class="form-group">
              <label>{{ getFieldLabel($t("admin.menu.name"), true) }} *</label>
              <input type="text" v-model="form.name" required />
            </div>
            <div v-if="shouldShowEnglish" class="form-group">
              <label>{{ getFieldLabel($t("admin.menu.name"), false) }}</label>
              <input
                type="text"
                v-model="form.name_en"
                :required="!shouldShowVietnamese"
              />
            </div>
          </div>

          <div class="form-row">
            <div v-if="shouldShowVietnamese" class="form-group">
              <label>{{ getFieldLabel($t("common.description"), true) }}</label>
              <textarea v-model="form.description" rows="2"></textarea>
            </div>
            <div v-if="shouldShowEnglish" class="form-group">
              <label>{{ getFieldLabel($t("common.description"), false) }}</label>
              <textarea v-model="form.description_en" rows="2"></textarea>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>{{ $t("admin.menu.price") }} (VNĐ) *</label>
              <input
                type="number"
                v-model.number="form.price"
                min="0"
                required
              />
            </div>
            <div v-if="shouldShowVietnamese" class="form-group">
              <label>{{ getFieldLabel($t("common.unit"), true) }}</label>
              <input
                type="text"
                v-model="form.unit"
                :placeholder="$t('admin.menu.unit_placeholder')"
              />
            </div>
            <div v-if="shouldShowEnglish" class="form-group">
              <label>{{ getFieldLabel($t("common.unit"), false) }}</label>
              <input
                type="text"
                v-model="form.unit_en"
                placeholder="plate, portion..."
              />
            </div>
          </div>

          <div class="form-row">
            <div v-if="shouldShowVietnamese" class="form-group">
              <label>{{ getFieldLabel($t("common.note"), true) }}</label>
              <input
                type="text"
                v-model="form.note"
                :placeholder="$t('admin.menu.note_placeholder')"
              />
            </div>
            <div v-if="shouldShowEnglish" class="form-group">
              <label>{{ getFieldLabel($t("common.note"), false) }}</label>
              <input
                type="text"
                v-model="form.note_en"
                placeholder="For 2-3 people..."
              />
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>{{ $t("admin.menu.image") }}</label>
              <div class="image-picker">
                <div v-if="form.image" class="selected-image">
                  <img :src="form.image" alt="Item" />
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

            <div class="form-group-options">
              <div class="form-group">
                <label>{{ $t("admin.menu.sort_order") }}</label>
                <input type="number" v-model.number="form.sort_order" min="0" />
              </div>
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.is_available" />
                {{ $t("admin.menu.is_available") }}
              </label>
              <label class="checkbox-label">
                <input type="checkbox" v-model="form.is_featured" />
                {{ $t("admin.menu.is_featured") }}
              </label>
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
          <p>{{ $t("admin.menu.delete_item_confirm") }}</p>
          <p class="item-name-confirm">{{ deletingItem?.name }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showDeleteConfirm = false">
            {{ $t("common.cancel") }}
          </button>
          <button class="btn-danger" @click="deleteItem" :disabled="saving">
            {{ $t("common.delete") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import MediaLibraryModal from "@/components/admin/MediaLibraryModal.vue";
import { useSettingsStore } from "@/stores/settings";
import { useBilingualForm } from "@/composables/useBilingualForm";

const { t } = useI18n();
const route = useRoute();
const settingsStore = useSettingsStore();
const { shouldShowVietnamese, shouldShowEnglish, getFieldLabel } =
  useBilingualForm();

const items = ref([]);
const categories = ref([]);
const loading = ref(true);
const saving = ref(false);
const showModal = ref(false);
const showMediaLibrary = ref(false);
const showDeleteConfirm = ref(false);
const editingItem = ref(null);
const deletingItem = ref(null);
const selectedCategory = ref("");
const searchQuery = ref("");
let searchTimeout = null;

const defaultForm = {
  category_id: "",
  name: "",
  name_en: "",
  description: "",
  description_en: "",
  price: 0,
  unit: "",
  unit_en: "",
  image: "",
  note: "",
  note_en: "",
  is_available: true,
  is_featured: false,
  sort_order: 0,
};

const form = ref({ ...defaultForm });

const formatPrice = (price) => {
  return new Intl.NumberFormat("vi-VN").format(price) + "đ";
};

const fetchCategories = async () => {
  try {
    const response = await api.get("/admin/menu-categories");
    categories.value = response.data.data || [];
  } catch (error) {
    console.error("Error fetching categories:", error);
  }
};

const fetchItems = async () => {
  loading.value = true;
  try {
    const params = {};
    if (selectedCategory.value) params.category_id = selectedCategory.value;
    if (searchQuery.value) params.search = searchQuery.value;

    const response = await api.get("/admin/menu-items", { params });
    items.value = response.data.data || [];
  } catch (error) {
    console.error("Error fetching items:", error);
  } finally {
    loading.value = false;
  }
};

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(fetchItems, 300);
};

const openCreateModal = () => {
  editingItem.value = null;
  form.value = { ...defaultForm };
  if (selectedCategory.value) {
    form.value.category_id = selectedCategory.value;
  }
  showModal.value = true;
};

const editItem = (item) => {
  editingItem.value = item;
  form.value = {
    category_id: item.category_id,
    name: item.name,
    name_en: item.name_en || item.name || "",
    description: item.description || "",
    description_en: item.description_en || item.description || "",
    price: item.price,
    unit: item.unit || "",
    unit_en: item.unit_en || item.unit || "",
    image: item.image || "",
    note: item.note || "",
    note_en: item.note_en || item.note || "",
    is_available: item.is_available,
    is_featured: item.is_featured,
    sort_order: item.sort_order || 0,
  };
  showModal.value = true;
};

const normalizeItemPayload = (payload) => {
  const normalized = { ...payload };
  if (!settingsStore.bilingualEnabled) {
    if (!normalized.name && normalized.name_en) {
      normalized.name = normalized.name_en;
    }
    if (!normalized.name_en && normalized.name) {
      normalized.name_en = normalized.name;
    }
    if (!normalized.description && normalized.description_en) {
      normalized.description = normalized.description_en;
    }
    if (!normalized.description_en && normalized.description) {
      normalized.description_en = normalized.description;
    }
    if (!normalized.unit && normalized.unit_en) {
      normalized.unit = normalized.unit_en;
    }
    if (!normalized.unit_en && normalized.unit) {
      normalized.unit_en = normalized.unit;
    }
    if (!normalized.note && normalized.note_en) {
      normalized.note = normalized.note_en;
    }
    if (!normalized.note_en && normalized.note) {
      normalized.note_en = normalized.note;
    }
  }
  return normalized;
};

const closeModal = () => {
  showModal.value = false;
  editingItem.value = null;
  form.value = { ...defaultForm };
};

const saveItem = async () => {
  saving.value = true;
  try {
    const payload = normalizeItemPayload(form.value);
    if (editingItem.value) {
      await api.put(`/admin/menu-items/${editingItem.value.id}`, payload);
    } else {
      await api.post("/admin/menu-items", payload);
    }
    closeModal();
    fetchItems();
  } catch (error) {
    console.error("Error saving item:", error);
    alert(error.response?.data?.message || t("common.error"));
  } finally {
    saving.value = false;
  }
};

const confirmDelete = (item) => {
  deletingItem.value = item;
  showDeleteConfirm.value = true;
};

const deleteItem = async () => {
  saving.value = true;
  try {
    await api.delete(`/admin/menu-items/${deletingItem.value.id}`);
    showDeleteConfirm.value = false;
    deletingItem.value = null;
    fetchItems();
  } catch (error) {
    console.error("Error deleting item:", error);
    alert(error.response?.data?.message || t("common.error"));
  } finally {
    saving.value = false;
  }
};

const toggleAvailability = async (item) => {
  try {
    await api.post(`/admin/menu-items/${item.id}/toggle-availability`);
    item.is_available = !item.is_available;
  } catch (error) {
    console.error("Error toggling availability:", error);
  }
};

const toggleFeatured = async (item) => {
  try {
    await api.post(`/admin/menu-items/${item.id}/toggle-featured`);
    item.is_featured = !item.is_featured;
  } catch (error) {
    console.error("Error toggling featured:", error);
  }
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
  // Check if category filter from URL
  if (route.query.category) {
    selectedCategory.value = route.query.category;
  }
  fetchCategories();
  fetchItems();
});
</script>

<style scoped>
.menu-items-page {
  padding: 20px;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
  gap: 20px;
  flex-wrap: wrap;
}

.header-left h1 {
  margin: 0 0 12px 0;
  font-size: 24px;
  color: #2c3e50;
}

.filter-bar {
  display: flex;
  gap: 12px;
}

.filter-bar select,
.filter-bar input {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}

.filter-bar select {
  min-width: 180px;
}

.filter-bar input {
  min-width: 200px;
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

.items-table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.items-table {
  width: 100%;
  border-collapse: collapse;
}

.items-table th,
.items-table td {
  padding: 12px 16px;
  text-align: left;
  border-bottom: 1px solid #eee;
}

.items-table th {
  background: #f8f9fa;
  font-weight: 600;
  color: #555;
  font-size: 13px;
  text-transform: uppercase;
}

.item-image {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  overflow: hidden;
  background: #f5f6fa;
  display: flex;
  align-items: center;
  justify-content: center;
}

.item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-image .no-img {
  font-size: 24px;
}

.item-name strong {
  display: block;
  color: #2c3e50;
}

.item-name .name-en {
  display: block;
  color: #7f8c8d;
  font-size: 13px;
}

.item-name .note {
  display: block;
  color: #e67e22;
  font-size: 12px;
  font-style: italic;
  margin-top: 4px;
}

.category-badge {
  padding: 4px 10px;
  background: #e8f4fc;
  color: #3498db;
  border-radius: 12px;
  font-size: 13px;
}

.price {
  font-weight: 600;
  color: #e74c3c;
}

.price .unit {
  font-weight: normal;
  color: #7f8c8d;
}

.status-toggle {
  padding: 6px 12px;
  border: none;
  border-radius: 20px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s;
}

.status-toggle.active {
  background: #d4edda;
  color: #155724;
}

.status-toggle:not(.active) {
  background: #f8d7da;
  color: #721c24;
}

.actions {
  display: flex;
  gap: 6px;
}

.btn-icon {
  padding: 6px 10px;
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

.btn-icon.featured {
  background: #fff3cd;
  border-color: #ffc107;
}

.empty-row {
  text-align: center;
  padding: 40px !important;
  color: #7f8c8d;
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
  max-width: 800px;
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
.form-group textarea,
.form-group select {
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
.form-group textarea:focus,
.form-group select:focus {
  outline: none;
  border-color: #3498db;
}

.form-group-options {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 14px;
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
  width: 100px;
  height: 70px;
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

.item-name-confirm {
  font-weight: 600;
  color: #2c3e50;
  margin-top: 8px;
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
  }

  .filter-bar {
    flex-direction: column;
  }

  .filter-bar select,
  .filter-bar input {
    width: 100%;
  }

  .items-table-container {
    overflow-x: auto;
  }
}
</style>
