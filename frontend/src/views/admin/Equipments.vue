<template>
  <div class="admin-equipments">
    <div class="admin-equipments-grid">
      <section class="glass-card table-card">
      <header class="panel-head">
        <div>
          <p class="text-sm text-slate-400">{{ $t('equipment.subtitle') }}</p>
          <h2 class="text-2xl font-semibold text-white">{{ $t('equipment.title') }}</h2>
        </div>
        <div class="panel-actions">
          <input
            v-model="filters.search"
            type="text"
            class="filter-input"
            :placeholder="$t('equipment.search_placeholder')"
            @input="debouncedSearch"
          />
          <select
            v-model="filters.equipment_category_id"
            class="filter-select"
            @change="applyFilter"
          >
            <option value="">{{ $t('common.all') }} {{ $t('equipment.category') }}</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
          <select
            v-model="filters.status"
            class="filter-select"
            @change="applyFilter"
          >
            <option value="">{{ $t('common.status') }}</option>
            <option value="active">{{ $t('common.active') }}</option>
            <option value="inactive">{{ $t('common.inactive') }}</option>
          </select>
          <button class="btn btn-primary" @click="openCreateForm">
            + {{ $t('equipment.add_item') }}
          </button>
        </div>
      </header>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>{{ $t('common.name') }}</th>
              <th>{{ $t('equipment.category') }}</th>
              <th>{{ $t('equipment.rental_price') }}</th>
              <th>{{ $t('equipment.sale_price') }}</th>
              <th>{{ $t('equipment.stock') }}</th>
              <th>{{ $t('common.status') }}</th>
              <th>{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="text-center text-slate-400 py-6">
                {{ $t('common.loading') }}
              </td>
            </tr>
            <tr v-else-if="!equipments.length">
              <td colspan="7" class="text-center text-slate-400 py-6">
                {{ $t('equipment.no_items') }}
              </td>
            </tr>
            <tr v-for="eq in equipments" :key="eq.id">
              <td>
                <div class="item-name-cell">
                  <img
                    v-if="eq.cover_image"
                    :src="eq.cover_image"
                    :alt="eq.name"
                    class="item-thumb"
                  />
                  <div>
                    <p class="font-semibold text-white">{{ eq.name }}</p>
                    <small class="text-slate-400">
                      <span v-if="eq.is_rentable" class="type-badge rental">{{ $t('equipment.rental') }}</span>
                      <span v-if="eq.is_sellable" class="type-badge sale">{{ $t('equipment.purchase') }}</span>
                    </small>
                  </div>
                </div>
              </td>
              <td>
                <span v-if="eq.equipment_category" class="badge">
                  {{ eq.equipment_category.name }}
                </span>
                <span v-else class="text-slate-500">—</span>
              </td>
              <td>
                <span v-if="eq.is_rentable">{{ formatCurrency(eq.rental_price_per_day) }}/{{ $t('equipment.day') }}</span>
                <span v-else class="text-slate-500">—</span>
              </td>
              <td>
                <span v-if="eq.is_sellable && eq.sale_price">{{ formatCurrency(eq.sale_price) }}</span>
                <span v-else class="text-slate-500">—</span>
              </td>
              <td>
                <span class="stock-badge" :class="eq.stock_quantity > 0 ? 'in-stock' : 'out-of-stock'">
                  {{ eq.stock_quantity }}
                </span>
              </td>
              <td>
                <span class="status-pill" :class="eq.status">
                  {{ eq.status === 'active' ? $t('common.active') : $t('common.inactive') }}
                </span>
              </td>
              <td>
                <div class="table-actions">
                  <button class="btn-text" @click="openEditForm(eq)">
                    {{ $t('common.edit') }}
                  </button>
                  <button class="btn-text danger" @click="deleteEquipment(eq.id)">
                    {{ $t('common.delete') }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <footer class="table-footer" v-if="pagination.total > pagination.per_page">
        <button
          class="btn btn-text"
          :disabled="pagination.current_page === 1"
          @click="changePage(pagination.current_page - 1)"
        >
          ‹
        </button>
        <span>Page {{ pagination.current_page }} / {{ totalPages }}</span>
        <button
          class="btn btn-text"
          :disabled="pagination.current_page === totalPages"
          @click="changePage(pagination.current_page + 1)"
        >
          ›
        </button>
      </footer>
      </section>

      <section class="glass-card categories-card">
      <header class="panel-head">
        <div>
          <p class="text-sm text-slate-400">{{ $t('equipment.category') }}</p>
          <h3 class="text-xl font-semibold text-white">{{ $t('equipment.add_category') }}</h3>
        </div>
      </header>

      <div class="locale-tabs" v-if="availableLocales.length > 1">
        <button
          v-for="locale in availableLocales"
          :key="locale"
          type="button"
          class="locale-tab"
          :class="{ active: activeLocale === locale }"
          @click="activeLocale = locale"
        >
          {{ localeLabels[locale] || locale.toUpperCase() }}
        </button>
      </div>

      <form class="grid gap-3" @submit.prevent="submitCategory">
        <label class="form-field">
          <span>{{ $t('common.name') }}</span>
          <input v-model="categoryForm.translations[activeLocale].name" type="text" required />
        </label>
        <label class="form-field">
          <span>{{ $t('common.description') }}</span>
          <textarea v-model="categoryForm.translations[activeLocale].description" rows="3"></textarea>
        </label>
        <label class="form-field">
          <span>Icon (emoji)</span>
          <input v-model="categoryForm.icon" type="text" placeholder="🏊" maxlength="10" />
        </label>
        <label class="form-checkbox">
          <input v-model="categoryForm.is_active" type="checkbox" />
          <span>{{ $t('common.active') }}</span>
        </label>
        <div class="flex gap-2">
          <button class="btn btn-primary w-full" :disabled="categoryLoading">
            {{ editingCategoryId ? $t('common.update') : $t('common.add') }}
          </button>
          <button
            v-if="editingCategoryId"
            class="btn btn-secondary"
            type="button"
            @click="resetCategoryForm"
          >
            {{ $t('common.cancel') }}
          </button>
        </div>
        <p v-if="categoryMessage" class="text-xs text-emerald-300">
          {{ categoryMessage }}
        </p>
        <p v-if="categoryError" class="text-xs text-rose-300">
          {{ categoryError }}
        </p>
      </form>

      <ul class="category-list">
        <li v-for="category in categories" :key="category.id">
          <div>
            <p class="font-semibold text-white">
              {{ category.icon || '📦' }} {{ category.name }}
            </p>
            <small class="text-slate-400">
              {{ category.equipments_count }} {{ $t('equipment.items') }}
            </small>
          </div>
          <div class="category-actions">
            <span class="badge" :class="{ inactive: !category.is_active }">
              {{ category.is_active ? $t('common.active') : $t('common.inactive') }}
            </span>
            <button class="btn btn-text" @click="editCategory(category)">{{ $t('common.edit') }}</button>
            <button class="btn btn-text danger" @click="deleteCategory(category.id)">{{ $t('common.delete') }}</button>
          </div>
        </li>
      </ul>
      </section>
    </div>

    <transition name="fade-scale">
      <div v-if="modalOpen" class="modal-overlay" @click.self="closeModal">
        <section class="modal-panel">
          <header class="modal-header">
            <div>
              <p class="text-sm text-slate-400">
                {{ formMode === "create" ? $t('equipment.add_item') : $t('equipment.edit_item') }}
              </p>
              <h3 class="text-xl font-semibold text-white">{{ $t('equipment.title') }}</h3>
            </div>
            <button class="btn btn-text" type="button" @click="closeModal">
              ✕
            </button>
          </header>

          <div class="locale-tabs" v-if="availableLocales.length > 1">
            <button
              v-for="locale in availableLocales"
              :key="locale"
              type="button"
              class="locale-tab"
              :class="{ active: activeLocale === locale }"
              @click="activeLocale = locale"
            >
              {{ localeLabels[locale] || locale.toUpperCase() }}
            </button>
          </div>

          <form class="form-grid" @submit.prevent="saveEquipment">
            <label class="form-field">
              <span>{{ $t('common.name') }} *</span>
              <input v-model="form.translations[activeLocale].name" type="text" required />
            </label>
            <label class="form-field">
              <span>{{ $t('equipment.category') }}</span>
              <select v-model="form.equipment_category_id">
                <option value="">{{ $t('common.none') }}</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </label>
            <label class="form-field full">
              <span>{{ $t('equipment.short_description') }}</span>
              <textarea v-model="form.translations[activeLocale].short_description" rows="2"></textarea>
            </label>
            <label class="form-field full">
              <span>{{ $t('common.description') }}</span>
              <RichTextEditor
                v-model="form.translations[activeLocale].description"
                :placeholder="$t('common.description')"
              />
            </label>

            <div class="pricing-section full">
              <h4 class="text-base font-semibold text-white mb-3">💰 {{ $t('equipment.pricing') }}</h4>
              <div class="pricing-grid">
                <label class="form-field">
                  <span class="flex items-center gap-2">
                    <input v-model="form.is_rentable" type="checkbox" class="toggle-check" />
                    {{ $t('equipment.is_rentable') }}
                  </span>
                  <input
                    v-model.number="form.rental_price_per_day"
                    type="number"
                    min="0"
                    :disabled="!form.is_rentable"
                    :placeholder="$t('equipment.rental_price')"
                  />
                </label>
                <label class="form-field">
                  <span class="flex items-center gap-2">
                    <input v-model="form.is_sellable" type="checkbox" class="toggle-check" />
                    {{ $t('equipment.is_sellable') }}
                  </span>
                  <input
                    v-model.number="form.sale_price"
                    type="number"
                    min="0"
                    :disabled="!form.is_sellable"
                    :placeholder="$t('equipment.sale_price')"
                  />
                </label>
              </div>
            </div>

            <label class="form-field">
              <span>{{ $t('equipment.stock') }}</span>
              <input v-model.number="form.stock_quantity" type="number" min="0" />
            </label>
            <label class="form-field">
              <span>{{ $t('equipment.sort_order') }}</span>
              <input v-model.number="form.sort_order" type="number" min="0" />
            </label>

            <section class="form-field full">
              <div class="gallery-head">
                <div>
                  <p class="text-sm text-slate-400">{{ $t('media.gallery') }}</p>
                  <h4 class="text-lg font-semibold text-white">{{ $t('equipment.images') }}</h4>
                </div>
                <div class="gallery-actions">
                  <button type="button" class="btn btn-secondary" @click="showAlbumModal = true">
                    {{ $t('media.choose_album') }}
                  </button>
                  <button type="button" class="btn btn-secondary" @click="showMediaModal = true">
                    {{ $t('media.select_image') }}
                  </button>
                  <button type="button" class="btn btn-secondary" @click="showCoverModal = true">
                    {{ $t('media.choose_cover') }}
                  </button>
                </div>
              </div>

              <div v-if="selectedAlbum" class="album-chip">
                <span>Album: {{ selectedAlbum.name }}</span>
                <button type="button" class="btn btn-text" @click="clearAlbum">{{ $t('common.cancel') }}</button>
              </div>

              <div class="gallery-grid" v-if="form.images.length">
                <div v-for="(image, index) in form.images" :key="image" class="gallery-item">
                  <img :src="image" :alt="image" />
                  <div class="gallery-actions">
                    <button type="button" class="btn btn-text" @click="removeImage(index)">
                      {{ $t('common.delete') }}
                    </button>
                    <button
                      type="button"
                      class="chip-cover-btn"
                      :class="{ active: form.cover_image === image }"
                      @click="setCoverImage(image)"
                    >
                      {{ form.cover_image === image ? $t('media.cover') : $t('media.set_cover') }}
                    </button>
                  </div>
                </div>
              </div>
              <p v-else class="text-slate-400 text-sm">
                {{ $t('media.no_images_selected') }}
              </p>
            </section>

            <label class="form-field">
              <span>{{ $t('common.status') }}</span>
              <select v-model="form.status">
                <option value="active">{{ $t('common.active') }}</option>
                <option value="inactive">{{ $t('common.inactive') }}</option>
              </select>
            </label>
            <label class="form-field checkbox">
              <span>{{ $t('equipment.available') }}</span>
              <input v-model="form.is_available" type="checkbox" />
            </label>

            <div class="form-actions full">
              <button class="btn btn-primary" type="submit" :disabled="saving">
                {{ saving ? $t('common.saving') : $t('common.save') }}
              </button>
              <button class="btn btn-secondary" type="button" @click="closeModal">
                {{ $t('common.cancel') }}
              </button>
              <p v-if="formError" class="text-xs text-rose-300">{{ formError }}</p>
            </div>
          </form>
        </section>
      </div>
    </transition>

    <Teleport to="body">
      <MediaLibraryModal v-model="showMediaModal" @select="handleMediaInsert" />
      <MediaLibraryModal
        v-model="showCoverModal"
        :multiple="false"
        @select="handleCoverSelect"
      />
      <MediaAlbumModal v-model="showAlbumModal" @select="applyAlbumSelection" />
    </Teleport>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import api from "@/services/api";
import MediaLibraryModal from "@/components/admin/MediaLibraryModal.vue";
import MediaAlbumModal from "@/components/admin/MediaAlbumModal.vue";
import RichTextEditor from "@/components/common/RichTextEditor.vue";
import { useSettingsStore } from "@/stores/settings";

const settingsStore = useSettingsStore();
const availableLocales = computed(() =>
  settingsStore.bilingualEnabled ? ["vi", "en"] : ["en"]
);
const localeLabels = { vi: "Tiếng Việt", en: "English" };
const activeLocale = ref("en");

const resolveDefaultLocale = () => {
  const preferred = settingsStore.defaultLanguage || "en";
  return availableLocales.value.includes(preferred)
    ? preferred
    : availableLocales.value[0];
};

const equipments = ref([]);
const loading = ref(false);
const saving = ref(false);
const modalOpen = ref(false);
const formMode = ref("create");
const editingId = ref(null);
const formError = ref("");
const pagination = ref({
  current_page: 1,
  per_page: 15,
  total: 0,
  last_page: 1,
});

const filters = ref({
  search: "",
  status: "",
  equipment_category_id: "",
});

const categories = ref([]);
const categoryForm = ref(getEmptyCategoryForm());
const categoryLoading = ref(false);
const categoryMessage = ref("");
const categoryError = ref("");
const editingCategoryId = ref(null);

const showMediaModal = ref(false);
const showCoverModal = ref(false);
const showAlbumModal = ref(false);
const selectedAlbum = ref(null);
const galleryMeta = ref({});

const form = ref(getEmptyForm());
let searchTimeout;

const totalPages = computed(() => pagination.value.last_page || 1);

function getEmptyForm() {
  return {
    translations: {
      en: { name: "", short_description: "", description: "" },
      vi: { name: "", short_description: "", description: "" },
    },
    equipment_category_id: "",
    rental_price_per_day: 0,
    sale_price: null,
    is_rentable: true,
    is_sellable: false,
    stock_quantity: 0,
    sort_order: 0,
    images: [],
    cover_image: null,
    cover_media_id: null,
    media_album_id: null,
    status: "active",
    is_available: true,
  };
}

function getEmptyCategoryForm() {
  return {
    translations: {
      en: { name: "", description: "" },
      vi: { name: "", description: "" },
    },
    icon: "",
    is_active: true,
  };
}

function buildFormFromEquipment(eq) {
  return {
    translations: {
      en: {
        name: eq.name_en || eq.name || "",
        short_description: eq.short_description_en || eq.short_description || "",
        description: eq.description_en || eq.description || "",
      },
      vi: {
        name: eq.name_vi || eq.name || "",
        short_description: eq.short_description_vi || eq.short_description || "",
        description: eq.description_vi || eq.description || "",
      },
    },
    equipment_category_id: eq.equipment_category_id || "",
    rental_price_per_day: Number(eq.rental_price_per_day || 0),
    sale_price: eq.sale_price ? Number(eq.sale_price) : null,
    is_rentable: Boolean(eq.is_rentable),
    is_sellable: Boolean(eq.is_sellable),
    stock_quantity: eq.stock_quantity || 0,
    sort_order: eq.sort_order || 0,
    images: Array.isArray(eq.images) ? [...eq.images] : [],
    cover_image: eq.cover_image || null,
    cover_media_id: eq.cover_media_id || null,
    media_album_id: eq.media_album_id || null,
    status: eq.status || "active",
    is_available: Boolean(eq.is_available),
  };
}

function buildCategoryForm(cat) {
  return {
    translations: {
      en: { name: cat.name_en || cat.name || "", description: cat.description_en || cat.description || "" },
      vi: { name: cat.name_vi || cat.name || "", description: cat.description_vi || cat.description || "" },
    },
    icon: cat.icon || "",
    is_active: Boolean(cat.is_active),
  };
}

const ensureLocaleBuckets = () => {
  availableLocales.value.forEach((locale) => {
    if (!form.value.translations[locale]) {
      form.value.translations[locale] = { name: "", short_description: "", description: "" };
    }
    if (!categoryForm.value.translations[locale]) {
      categoryForm.value.translations[locale] = { name: "", description: "" };
    }
  });
};

watch(availableLocales, (next) => {
  if (!next.includes(activeLocale.value)) {
    activeLocale.value = resolveDefaultLocale();
  }
  ensureLocaleBuckets();
});

const formatCurrency = (value) => {
  const amount = Number(value || 0);
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
    maximumFractionDigits: 0,
  }).format(amount);
};

const fetchEquipments = async (page = 1) => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    params.set("page", page);
    params.set("per_page", pagination.value.per_page);

    if (filters.value.search) params.set("search", filters.value.search);
    if (filters.value.status) params.set("status", filters.value.status);
    if (filters.value.equipment_category_id) {
      params.set("equipment_category_id", filters.value.equipment_category_id);
    }

    const { data } = await api.get(`/admin/equipments?${params.toString()}`);
    equipments.value = data.data || [];
    pagination.value = {
      current_page: data.current_page,
      per_page: data.per_page,
      total: data.total,
      last_page: data.last_page,
    };
  } catch (error) {
    equipments.value = [];
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  categoryLoading.value = true;
  try {
    const response = await api.get("/admin/equipment-categories");
    categories.value = response.data || [];
  } catch (error) {
    categoryError.value = error.response?.data?.message || "Cannot load categories";
  } finally {
    categoryLoading.value = false;
  }
};

const applyFilter = () => fetchEquipments(1);

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => fetchEquipments(1), 350);
};

const changePage = (page) => {
  if (page < 1 || page > totalPages.value) return;
  fetchEquipments(page);
};

const openCreateForm = () => {
  formMode.value = "create";
  editingId.value = null;
  formError.value = "";
  form.value = getEmptyForm();
  galleryMeta.value = {};
  selectedAlbum.value = null;
  activeLocale.value = resolveDefaultLocale();
  modalOpen.value = true;
};

const openEditForm = (eq) => {
  formMode.value = "edit";
  editingId.value = eq.id;
  formError.value = "";
  galleryMeta.value = {};
  form.value = buildFormFromEquipment(eq);
  activeLocale.value = resolveDefaultLocale();
  modalOpen.value = true;

  if (eq.media_album_id) {
    fetchAlbumDetails(eq.media_album_id);
  }
};

const closeModal = () => {
  modalOpen.value = false;
};

const buildPayload = () => {
  const defaultLocale = resolveDefaultLocale();
  const translations = form.value.translations;

  const payload = {
    name: translations[defaultLocale]?.name || "",
    short_description: translations[defaultLocale]?.short_description || null,
    description: translations[defaultLocale]?.description || null,
    rental_price_per_day: form.value.rental_price_per_day || 0,
    sale_price: form.value.is_sellable ? (form.value.sale_price || null) : null,
    is_rentable: Boolean(form.value.is_rentable),
    is_sellable: Boolean(form.value.is_sellable),
    stock_quantity: form.value.stock_quantity || 0,
    sort_order: form.value.sort_order || 0,
    equipment_category_id: form.value.equipment_category_id || null,
    images: form.value.images,
    cover_image: form.value.cover_image || null,
    cover_media_id: form.value.cover_media_id || null,
    media_album_id: form.value.media_album_id || null,
    status: form.value.status || "active",
    is_available: Boolean(form.value.is_available),
  };

  payload.name_en = translations.en?.name || payload.name;
  payload.name_vi = translations.vi?.name || payload.name;
  payload.short_description_en = translations.en?.short_description || payload.short_description;
  payload.short_description_vi = translations.vi?.short_description || payload.short_description;
  payload.description_en = translations.en?.description || payload.description;
  payload.description_vi = translations.vi?.description || payload.description;

  return payload;
};

const saveEquipment = async () => {
  saving.value = true;
  formError.value = "";
  try {
    const payload = buildPayload();
    if (formMode.value === "edit" && editingId.value) {
      await api.put(`/admin/equipments/${editingId.value}`, payload);
    } else {
      await api.post("/admin/equipments", payload);
    }
    modalOpen.value = false;
    fetchEquipments(pagination.value.current_page || 1);
  } catch (error) {
    formError.value = error.response?.data?.message || "Cannot save data";
  } finally {
    saving.value = false;
  }
};

const deleteEquipment = async (id) => {
  if (!window.confirm("Delete this equipment?")) return;
  try {
    await api.delete(`/admin/equipments/${id}`);
    fetchEquipments(pagination.value.current_page || 1);
  } catch (error) {
    // no-op
  }
};

const submitCategory = async () => {
  categoryLoading.value = true;
  categoryMessage.value = "";
  categoryError.value = "";
  try {
    const defaultLocale = resolveDefaultLocale();
    const payload = {
      name: categoryForm.value.translations[defaultLocale]?.name || "",
      description: categoryForm.value.translations[defaultLocale]?.description || null,
      name_en: categoryForm.value.translations.en?.name || categoryForm.value.translations[defaultLocale]?.name || "",
      name_vi: categoryForm.value.translations.vi?.name || categoryForm.value.translations[defaultLocale]?.name || "",
      description_en: categoryForm.value.translations.en?.description || categoryForm.value.translations[defaultLocale]?.description || null,
      description_vi: categoryForm.value.translations.vi?.description || categoryForm.value.translations[defaultLocale]?.description || null,
      icon: categoryForm.value.icon || null,
      is_active: categoryForm.value.is_active,
    };

    if (editingCategoryId.value) {
      await api.put(`/admin/equipment-categories/${editingCategoryId.value}`, payload);
      categoryMessage.value = "Category updated";
    } else {
      await api.post("/admin/equipment-categories", payload);
      categoryMessage.value = "Category created";
    }
    resetCategoryForm();
    await fetchCategories();
  } catch (error) {
    categoryError.value = error.response?.data?.message || "Unable to save category";
  } finally {
    categoryLoading.value = false;
  }
};

const editCategory = (category) => {
  editingCategoryId.value = category.id;
  categoryForm.value = buildCategoryForm(category);
};

const resetCategoryForm = () => {
  editingCategoryId.value = null;
  categoryForm.value = getEmptyCategoryForm();
};

const deleteCategory = async (categoryId) => {
  if (!confirm("Delete this category?")) return;
  try {
    await api.delete(`/admin/equipment-categories/${categoryId}`);
    await fetchCategories();
  } catch (error) {
    categoryError.value = error.response?.data?.message || "Unable to delete category";
  }
};

// --- Gallery helpers ---
const appendGalleryItems = (items = []) => {
  const nextMeta = { ...galleryMeta.value };
  const urls = [...form.value.images];
  items.forEach((item) => {
    if (!item?.url) return;
    if (!urls.includes(item.url)) urls.push(item.url);
    if (item.id) nextMeta[item.url] = item.id;
  });
  form.value.images = urls;
  galleryMeta.value = nextMeta;
};

const ensureImageInGallery = (imageUrl) => {
  if (!imageUrl) return;
  const filtered = form.value.images.filter((url) => url !== imageUrl);
  form.value.images = [imageUrl, ...filtered];
};

const setCoverImage = (imageUrl) => {
  if (!imageUrl) { clearCoverImage(); return; }
  ensureImageInGallery(imageUrl);
  form.value.cover_image = imageUrl;
  form.value.cover_media_id = galleryMeta.value[imageUrl] ?? null;
};

const clearCoverImage = () => {
  form.value.cover_image = null;
  form.value.cover_media_id = null;
};

const handleMediaInsert = (items) => {
  appendGalleryItems(items);
  form.value.media_album_id = null;
  selectedAlbum.value = null;
  if (!form.value.cover_image && form.value.images.length) setCoverImage(form.value.images[0]);
};

const handleCoverSelect = (items) => {
  const item = items?.[0];
  if (!item) return;
  appendGalleryItems([item]);
  form.value.media_album_id = null;
  selectedAlbum.value = null;
  setCoverImage(item.url);
};

const removeImage = (index) => {
  const removed = form.value.images[index];
  form.value.images = form.value.images.filter((_, idx) => idx !== index);
  if (removed) {
    const meta = { ...galleryMeta.value };
    delete meta[removed];
    galleryMeta.value = meta;
  }
  if (!form.value.images.length) {
    clearCoverImage();
    form.value.media_album_id = null;
    selectedAlbum.value = null;
    return;
  }
  if (form.value.cover_image === removed) setCoverImage(form.value.images[0]);
};

const clearAlbum = () => {
  form.value.media_album_id = null;
  selectedAlbum.value = null;
};

const applyAlbumSelection = async (album) => {
  if (!album) return;
  selectedAlbum.value = album;
  form.value.media_album_id = album.id;
  form.value.images = [];
  galleryMeta.value = {};
  clearCoverImage();
  await fetchAlbumDetails(album.id);
};

const fetchAlbumDetails = async (albumId) => {
  try {
    const { data } = await api.get(`/admin/media-albums/${albumId}`);
    selectedAlbum.value = data;
    if (data.media_items?.length) {
      appendGalleryItems(data.media_items);
      if (!form.value.cover_image && form.value.images.length) setCoverImage(form.value.images[0]);
    }
  } catch {
    // ignore
  }
};

onMounted(() => {
  activeLocale.value = resolveDefaultLocale();
  fetchEquipments();
  fetchCategories();
});
</script>

<style scoped>
.admin-equipments-grid {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 2rem;
}

.glass-card {
  border-radius: 1.5rem;
  padding: 2rem;
  background: rgba(15, 23, 42, 0.55);
  border: 1px solid rgba(148, 163, 184, 0.15);
  backdrop-filter: blur(12px);
}

.panel-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.panel-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  align-items: center;
}

.filter-input,
.filter-select {
  padding: 0.5rem 0.85rem;
  border-radius: 1rem;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: rgba(15, 23, 42, 0.65);
  color: #e2e8f0;
  font-size: 0.875rem;
}

.filter-input::placeholder {
  color: #64748b;
}

.table-wrapper {
  overflow-x: auto;
  border-radius: 1rem;
  border: 1px solid rgba(148, 163, 184, 0.12);
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead th {
  padding: 0.75rem 1rem;
  text-align: left;
  font-weight: 600;
  color: #94a3b8;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: rgba(15, 23, 42, 0.45);
  border-bottom: 1px solid rgba(148, 163, 184, 0.12);
}

tbody td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.07);
  font-size: 0.875rem;
}

tbody tr:hover {
  background: rgba(99, 102, 241, 0.06);
}

.item-name-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.item-thumb {
  width: 40px;
  height: 40px;
  border-radius: 0.5rem;
  object-fit: cover;
  border: 1px solid rgba(148, 163, 184, 0.2);
}

.type-badge {
  display: inline-block;
  padding: 0.15rem 0.5rem;
  border-radius: 999px;
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: uppercase;
  margin-right: 0.25rem;
}

.type-badge.rental {
  background: rgba(56, 189, 248, 0.2);
  color: #38bdf8;
}

.type-badge.sale {
  background: rgba(168, 85, 247, 0.2);
  color: #a855f7;
}

.stock-badge {
  display: inline-block;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.stock-badge.in-stock {
  background: rgba(52, 211, 153, 0.15);
  color: #34d399;
}

.stock-badge.out-of-stock {
  background: rgba(244, 63, 94, 0.15);
  color: #f43f5e;
}

.status-pill {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.status-pill.active {
  background: rgba(52, 211, 153, 0.15);
  color: #34d399;
}

.status-pill.inactive {
  background: rgba(244, 63, 94, 0.15);
  color: #f43f5e;
}

.badge {
  display: inline-block;
  padding: 0.2rem 0.7rem;
  border-radius: 999px;
  font-size: 0.75rem;
  background: rgba(99, 102, 241, 0.15);
  color: #818cf8;
}

.badge.inactive {
  background: rgba(148, 163, 184, 0.15);
  color: #64748b;
}

.table-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-text {
  background: none;
  border: none;
  color: #818cf8;
  cursor: pointer;
  font-size: 0.8rem;
  padding: 0.25rem 0.5rem;
  border-radius: 0.5rem;
  transition: background 0.2s;
}

.btn-text:hover {
  background: rgba(99, 102, 241, 0.1);
}

.btn-text.danger {
  color: #f43f5e;
}

.btn-text.danger:hover {
  background: rgba(244, 63, 94, 0.1);
}

.table-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 1rem 0 0;
  color: #94a3b8;
  font-size: 0.875rem;
}

/* --- Categories panel --- */
.categories-card {
  align-self: start;
}

.category-list {
  list-style: none;
  margin-top: 1.25rem;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.category-list li {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.85rem 1rem;
  border-radius: 1rem;
  background: rgba(148, 163, 184, 0.06);
  border: 1px solid rgba(148, 163, 184, 0.12);
}

.category-actions {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

/* --- Locale tabs --- */
.locale-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.locale-tab {
  padding: 0.4rem 1rem;
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  background: rgba(15, 23, 42, 0.5);
  color: #94a3b8;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
}

.locale-tab.active {
  background: rgba(99, 102, 241, 0.2);
  border-color: rgba(99, 102, 241, 0.5);
  color: #c7d2fe;
}

/* --- Form --- */
.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.form-field span {
  font-size: 0.75rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.form-field input,
.form-field select,
.form-field textarea {
  padding: 0.55rem 0.85rem;
  border-radius: 1rem;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: rgba(15, 23, 42, 0.65);
  color: #e2e8f0;
  font-size: 0.875rem;
}

.form-field input:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.form-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: #cbd5e1;
}

.form-checkbox input[type="checkbox"] {
  accent-color: #6366f1;
}

.toggle-check {
  accent-color: #6366f1;
  width: 16px;
  height: 16px;
}

/* --- Pricing section --- */
.pricing-section {
  padding: 1.25rem;
  border-radius: 1.25rem;
  border: 1px solid rgba(148, 163, 184, 0.15);
  background: rgba(99, 102, 241, 0.05);
}

.pricing-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

/* --- Modal --- */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.65);
  backdrop-filter: blur(6px);
}

.modal-panel {
  width: min(760px, 94vw);
  max-height: 90vh;
  overflow-y: auto;
  border-radius: 1.75rem;
  padding: 2.5rem;
  background: linear-gradient(180deg, #0f172a, #020617);
  border: 1px solid rgba(148, 163, 184, 0.18);
  box-shadow: 0 35px 70px rgba(0, 0, 0, 0.6);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-grid .full {
  grid-column: 1 / -1;
}

.form-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
  padding-top: 1rem;
}

/* --- Gallery --- */
.gallery-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.gallery-head .gallery-actions {
  display: flex;
  gap: 0.5rem;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 0.75rem;
  margin-top: 0.75rem;
}

.gallery-item {
  position: relative;
  border-radius: 0.75rem;
  overflow: hidden;
  border: 1px solid rgba(148, 163, 184, 0.15);
}

.gallery-item img {
  width: 100%;
  aspect-ratio: 1;
  object-fit: cover;
}

.gallery-item .gallery-actions {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  display: flex;
  justify-content: space-between;
  padding: 0.25rem;
  background: rgba(0, 0, 0, 0.7);
}

.album-chip {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 999px;
  background: rgba(99, 102, 241, 0.12);
  border: 1px solid rgba(99, 102, 241, 0.3);
  color: #c7d2fe;
  font-size: 0.8rem;
  margin-bottom: 0.75rem;
}

.chip-cover-btn {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 0.6rem;
  cursor: pointer;
  padding: 0.15rem 0.35rem;
  border-radius: 0.35rem;
}

.chip-cover-btn.active {
  color: #fbbf24;
  font-weight: 600;
}

/* Transitions */
.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: all 0.25s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.96);
}

@media (max-width: 1024px) {
  .admin-equipments-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .pricing-grid {
    grid-template-columns: 1fr;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }
}

/* ── Quill RichTextEditor dark‑mode overrides ── */
:deep(.ql-toolbar.ql-snow) {
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 0.75rem 0.75rem 0 0;
  background: rgba(30, 41, 59, 0.9);
}

:deep(.ql-toolbar .ql-stroke) {
  stroke: #94a3b8;
}

:deep(.ql-toolbar .ql-fill) {
  fill: #94a3b8;
}

:deep(.ql-toolbar .ql-picker-label) {
  color: #94a3b8;
}

:deep(.ql-toolbar .ql-picker-options) {
  background: #1e293b;
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 0.5rem;
}

:deep(.ql-toolbar .ql-picker-item) {
  color: #cbd5e1;
}

:deep(.ql-toolbar button:hover .ql-stroke,
       .ql-toolbar .ql-active .ql-stroke) {
  stroke: #818cf8;
}

:deep(.ql-toolbar button:hover .ql-fill,
       .ql-toolbar .ql-active .ql-fill) {
  fill: #818cf8;
}

:deep(.ql-toolbar button:hover,
       .ql-toolbar button.ql-active) {
  background: rgba(99, 102, 241, 0.15);
  border-radius: 0.35rem;
}

:deep(.ql-container.ql-snow) {
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-top: none;
  border-radius: 0 0 0.75rem 0.75rem;
  background: rgba(15, 23, 42, 0.65);
}

:deep(.ql-editor) {
  min-height: 160px;
  color: #e2e8f0;
  font-size: 0.9rem;
  line-height: 1.6;
}

:deep(.ql-editor.ql-blank::before) {
  color: #64748b;
  font-style: italic;
}

:deep(.ql-editor a) {
  color: #818cf8;
}

:deep(.ql-snow .ql-tooltip) {
  background: #1e293b;
  border: 1px solid rgba(148, 163, 184, 0.3);
  color: #e2e8f0;
  border-radius: 0.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
}

:deep(.ql-snow .ql-tooltip input[type="text"]) {
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.3);
  color: #e2e8f0;
  border-radius: 0.35rem;
}
</style>
