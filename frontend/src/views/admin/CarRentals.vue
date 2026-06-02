<template>
  <div class="admin-cars">
    <div class="admin-cars-grid">
      <section class="glass-card table-card">
      <header class="panel-head">
        <div>
          <p class="text-sm text-slate-400">Quản lý danh sách</p>
          <h2 class="text-2xl font-semibold text-white">Cho thuê xe</h2>
        </div>
        <div class="panel-actions">
          <input
            v-model="filters.search"
            type="text"
            class="filter-input"
            placeholder="Tìm theo tên, hãng, mẫu..."
            @input="debouncedSearch"
          />
          <select
            v-model="filters.car_rental_category_id"
            class="filter-select"
            @change="applyFilter"
          >
            <option value="">Tất cả loại</option>
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
            <option value="">Trạng thái</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
          <select
            v-model="filters.is_available"
            class="filter-select"
            @change="applyFilter"
          >
            <option value="">Khả dụng</option>
            <option value="true">Available</option>
            <option value="false">Unavailable</option>
          </select>
          <button class="btn btn-primary" @click="openCreateForm">
            + Thêm xe
          </button>
        </div>
      </header>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Tên xe</th>
              <th>Loại</th>
              <th>Chỗ</th>
              <th>Giá/ngày</th>
              <th>Khả dụng</th>
              <th>Trạng thái</th>
              <th>Hành động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="7" class="text-center text-slate-400 py-6">
                Đang tải...
              </td>
            </tr>
            <tr v-else-if="!carRentals.length">
              <td colspan="7" class="text-center text-slate-400 py-6">
                Chưa có dữ liệu
              </td>
            </tr>
            <tr v-for="car in carRentals" :key="car.id">
              <td>
                <p class="font-semibold text-white">{{ car.name }}</p>
                <small class="text-slate-400">
                  {{ [car.brand, car.model].filter(Boolean).join(" · ") || "—" }}
                </small>
              </td>
              <td>
                <span v-if="car.car_rental_category" class="badge">
                  {{ car.car_rental_category.name }}
                </span>
                <span v-else class="text-slate-500">—</span>
              </td>
              <td>{{ car.seats || "—" }}</td>
              <td>{{ formatCurrency(car.price_per_day) }}</td>
              <td>
                <span class="status-pill" :class="car.is_available ? 'active' : 'inactive'">
                  {{ car.is_available ? "Available" : "Unavailable" }}
                </span>
              </td>
              <td>
                <span class="status-pill" :class="car.status">
                  {{ car.status }}
                </span>
              </td>
              <td>
                <div class="table-actions">
                  <button class="btn-text" @click="openEditForm(car)">
                    Sửa
                  </button>
                  <button class="btn-text danger" @click="deleteCar(car.id)">
                    Xóa
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
          <p class="text-sm text-slate-400">Danh mục</p>
          <h3 class="text-xl font-semibold text-white">Loại xe</h3>
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
          <span>Tên loại</span>
          <input v-model="categoryForm.translations[activeLocale].name" type="text" required />
        </label>
        <label class="form-field">
          <span>Mô tả</span>
          <textarea v-model="categoryForm.translations[activeLocale].description" rows="3"></textarea>
        </label>
        <label class="form-checkbox">
          <input v-model="categoryForm.is_active" type="checkbox" />
          <span>Kích hoạt</span>
        </label>
        <div class="flex gap-2">
          <button class="btn btn-primary w-full" :disabled="categoryLoading">
            {{ editingCategoryId ? "Cập nhật" : "Thêm" }}
          </button>
          <button
            v-if="editingCategoryId"
            class="btn btn-secondary"
            type="button"
            @click="resetCategoryForm"
          >
            Hủy
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
            <p class="font-semibold text-white">{{ category.name }}</p>
            <small class="text-slate-400">
              {{ category.car_rentals_count }} xe
            </small>
          </div>
          <div class="category-actions">
            <span class="badge" :class="{ inactive: !category.is_active }">
              {{ category.is_active ? "Active" : "Inactive" }}
            </span>
            <button class="btn btn-text" @click="editCategory(category)">Sửa</button>
            <button class="btn btn-text danger" @click="deleteCategory(category.id)">Xóa</button>
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
                {{ formMode === "create" ? "Thêm xe" : "Cập nhật xe" }}
              </p>
              <h3 class="text-xl font-semibold text-white">Thông tin xe</h3>
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

          <form class="form-grid" @submit.prevent="saveCar">
            <label class="form-field">
              <span>Tên xe *</span>
              <input v-model="form.translations[activeLocale].name" type="text" required />
            </label>
            <label class="form-field">
              <span>Hãng</span>
              <input v-model="form.translations[activeLocale].brand" type="text" />
            </label>
            <label class="form-field">
              <span>Model</span>
              <input v-model="form.translations[activeLocale].model" type="text" />
            </label>
            <label class="form-field">
              <span>Loại</span>
              <input v-model="form.translations[activeLocale].type" type="text" />
            </label>
            <label class="form-field">
              <span>Số chỗ</span>
              <input v-model.number="form.seats" type="number" min="1" />
            </label>
            <label class="form-field">
              <span>Hộp số</span>
              <input v-model="form.translations[activeLocale].transmission" type="text" />
            </label>
            <label class="form-field">
              <span>Nhiên liệu</span>
              <input v-model="form.translations[activeLocale].fuel_type" type="text" />
            </label>
            <label class="form-field">
              <span>Giá/ngày *</span>
              <input v-model.number="form.price_per_day" type="number" min="0" required />
            </label>
            <label class="form-field">
              <span>Địa điểm</span>
              <input v-model="form.translations[activeLocale].location" type="text" />
            </label>
            <label class="form-field">
              <span>Danh mục</span>
              <select v-model="form.car_rental_category_id">
                <option value="">Chưa chọn</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </label>
            <label class="form-field full">
              <span>Mô tả ngắn</span>
              <textarea v-model="form.translations[activeLocale].short_description" rows="2"></textarea>
            </label>
            <label class="form-field full">
              <span>Mô tả chi tiết</span>
              <textarea v-model="form.translations[activeLocale].description" rows="4"></textarea>
            </label>
            <label class="form-field full">
              <span>Tính năng (phân tách bởi dấu phẩy)</span>
              <textarea v-model="form.features" rows="2"></textarea>
            </label>

            <section class="form-field full">
              <div class="gallery-head">
                <div>
                  <p class="text-sm text-slate-400">Thư viện ảnh</p>
                  <h4 class="text-lg font-semibold text-white">Hình ảnh xe</h4>
                </div>
                <div class="gallery-actions">
                  <button type="button" class="btn btn-secondary" @click="showAlbumModal = true">
                    Chọn album
                  </button>
                  <button type="button" class="btn btn-secondary" @click="showMediaModal = true">
                    Thêm ảnh
                  </button>
                  <button type="button" class="btn btn-secondary" @click="showCoverModal = true">
                    Chọn ảnh chính
                  </button>
                </div>
              </div>

              <div v-if="selectedAlbum" class="album-chip">
                <span>Album: {{ selectedAlbum.name }}</span>
                <button type="button" class="btn btn-text" @click="clearAlbum">Bỏ album</button>
              </div>

              <div class="gallery-grid" v-if="form.images.length">
                <div v-for="(image, index) in form.images" :key="image" class="gallery-item">
                  <img :src="image" :alt="image" />
                  <div class="gallery-actions">
                    <button type="button" class="btn btn-text" @click="removeImage(index)">
                      Xóa
                    </button>
                    <button
                      type="button"
                      class="chip-cover-btn"
                      :class="{ active: form.cover_image === image }"
                      @click="setCoverImage(image)"
                    >
                      {{ form.cover_image === image ? "Ảnh chính" : "Đặt làm ảnh chính" }}
                    </button>
                  </div>
                </div>
              </div>
              <p v-else class="text-slate-400 text-sm">
                Chưa có ảnh nào, hãy chọn từ thư viện hoặc album.
              </p>
            </section>

            <label class="form-field">
              <span>Trạng thái</span>
              <select v-model="form.status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </label>
            <label class="form-field checkbox">
              <span>Khả dụng</span>
              <input v-model="form.is_available" type="checkbox" />
            </label>
            <label class="form-field">
              <span>Số điện thoại</span>
              <input v-model="form.contact_phone" type="text" />
            </label>
            <label class="form-field">
              <span>WhatsApp</span>
              <input v-model="form.contact_whatsapp" type="text" />
            </label>

            <div class="form-actions full">
              <button class="btn btn-primary" type="submit" :disabled="saving">
                {{ saving ? "Đang lưu..." : "Lưu" }}
              </button>
              <button class="btn btn-secondary" type="button" @click="closeModal">
                Hủy
              </button>
              <p v-if="formError" class="text-xs text-rose-300">{{ formError }}</p>
            </div>
          </form>
        </section>
      </div>
    </transition>

    <MediaLibraryModal v-model="showMediaModal" @select="handleMediaInsert" />
    <MediaLibraryModal
      v-model="showCoverModal"
      :multiple="false"
      @select="handleCoverSelect"
    />
    <MediaAlbumModal v-model="showAlbumModal" @select="applyAlbumSelection" />
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import api from "@/services/api";
import MediaLibraryModal from "@/components/admin/MediaLibraryModal.vue";
import MediaAlbumModal from "@/components/admin/MediaAlbumModal.vue";
import { useSettingsStore } from "@/stores/settings";

const settingsStore = useSettingsStore();
const availableLocales = computed(() =>
  settingsStore.bilingualEnabled ? ["vi", "en"] : ["en"]
);
const localeLabels = { vi: "Tiếng Việt", en: "English" };
const allLocales = ["vi", "en"];
const activeLocale = ref("en");

const resolveDefaultLocale = () => {
  const preferred = settingsStore.defaultLanguage || "en";
  return availableLocales.value.includes(preferred)
    ? preferred
    : availableLocales.value[0];
};

const carRentals = ref([]);
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
  is_available: "",
  car_rental_category_id: "",
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

const ensureLocaleBuckets = () => {
  availableLocales.value.forEach((locale) => {
    if (!form.value.translations[locale]) {
      form.value.translations[locale] = {
        name: "",
        brand: "",
        model: "",
        type: "",
        transmission: "",
        fuel_type: "",
        location: "",
        short_description: "",
        description: "",
      };
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

const fetchCars = async (page = 1) => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    params.set("page", page);
    params.set("per_page", pagination.value.per_page);

    if (filters.value.search) params.set("search", filters.value.search);
    if (filters.value.status) params.set("status", filters.value.status);
    if (filters.value.is_available) {
      params.set("is_available", filters.value.is_available);
    }
    if (filters.value.car_rental_category_id) {
      params.set("car_rental_category_id", filters.value.car_rental_category_id);
    }

    const { data } = await api.get(`/admin/car-rentals?${params.toString()}`);
    carRentals.value = data.data || [];
    pagination.value = {
      current_page: data.current_page,
      per_page: data.per_page,
      total: data.total,
      last_page: data.last_page,
    };
  } catch (error) {
    carRentals.value = [];
  } finally {
    loading.value = false;
  }
};

const fetchCategories = async () => {
  categoryLoading.value = true;
  try {
    const response = await api.get("/admin/car-rental-categories");
    categories.value = response.data || [];
  } catch (error) {
    categoryError.value =
      error.response?.data?.message || "Không thể tải danh mục";
  } finally {
    categoryLoading.value = false;
  }
};

const applyFilter = () => {
  fetchCars(1);
};

const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => fetchCars(1), 350);
};

const changePage = (page) => {
  if (page < 1 || page > totalPages.value) return;
  fetchCars(page);
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

const openEditForm = (car) => {
  formMode.value = "edit";
  editingId.value = car.id;
  formError.value = "";
  galleryMeta.value = {};
  form.value = buildFormFromCar(car);
  activeLocale.value = resolveDefaultLocale();
  modalOpen.value = true;

  if (car.media_album_id) {
    fetchAlbumDetails(car.media_album_id);
  }
};

const closeModal = () => {
  modalOpen.value = false;
};

const buildPayload = () => {
  const features = form.value.features
    ? form.value.features
        .split(/[,\n]+/)
        .map((item) => item.trim())
        .filter(Boolean)
    : [];

  const defaultLocale = resolveDefaultLocale();
  const translations = form.value.translations;

  const payload = {
    name: translations[defaultLocale]?.name || "",
    brand: translations[defaultLocale]?.brand || null,
    model: translations[defaultLocale]?.model || null,
    type: translations[defaultLocale]?.type || null,
    transmission: translations[defaultLocale]?.transmission || null,
    fuel_type: translations[defaultLocale]?.fuel_type || null,
    location: translations[defaultLocale]?.location || null,
    short_description: translations[defaultLocale]?.short_description || null,
    description: translations[defaultLocale]?.description || null,
    seats: form.value.seats || null,
    price_per_day: form.value.price_per_day || 0,
    car_rental_category_id: form.value.car_rental_category_id || null,
    features,
    images: form.value.images,
    cover_image: form.value.cover_image || null,
    cover_media_id: form.value.cover_media_id || null,
    media_album_id: form.value.media_album_id || null,
    status: form.value.status || "active",
    is_available: Boolean(form.value.is_available),
    contact_phone: form.value.contact_phone || null,
    contact_whatsapp: form.value.contact_whatsapp || null,
  };

  payload.name_en = translations.en?.name || payload.name;
  payload.name_vi = translations.vi?.name || payload.name;
  payload.brand_en = translations.en?.brand || payload.brand;
  payload.brand_vi = translations.vi?.brand || payload.brand;
  payload.model_en = translations.en?.model || payload.model;
  payload.model_vi = translations.vi?.model || payload.model;
  payload.type_en = translations.en?.type || payload.type;
  payload.type_vi = translations.vi?.type || payload.type;
  payload.transmission_en = translations.en?.transmission || payload.transmission;
  payload.transmission_vi = translations.vi?.transmission || payload.transmission;
  payload.fuel_type_en = translations.en?.fuel_type || payload.fuel_type;
  payload.fuel_type_vi = translations.vi?.fuel_type || payload.fuel_type;
  payload.location_en = translations.en?.location || payload.location;
  payload.location_vi = translations.vi?.location || payload.location;
  payload.short_description_en =
    translations.en?.short_description || payload.short_description;
  payload.short_description_vi =
    translations.vi?.short_description || payload.short_description;
  payload.description_en = translations.en?.description || payload.description;
  payload.description_vi = translations.vi?.description || payload.description;

  return payload;
};

const saveCar = async () => {
  saving.value = true;
  formError.value = "";
  try {
    const payload = buildPayload();
    if (formMode.value === "edit" && editingId.value) {
      await api.put(`/admin/car-rentals/${editingId.value}`, payload);
    } else {
      await api.post("/admin/car-rentals", payload);
    }
    modalOpen.value = false;
    fetchCars(pagination.value.current_page || 1);
  } catch (error) {
    formError.value =
      error.response?.data?.message || "Không thể lưu dữ liệu";
  } finally {
    saving.value = false;
  }
};

const deleteCar = async (id) => {
  const confirmed = window.confirm("Bạn có chắc muốn xóa xe này?");
  if (!confirmed) return;

  try {
    await api.delete(`/admin/car-rentals/${id}`);
    fetchCars(pagination.value.current_page || 1);
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
      description:
        categoryForm.value.translations[defaultLocale]?.description || null,
      name_en:
        categoryForm.value.translations.en?.name || categoryForm.value.translations[defaultLocale]?.name || "",
      name_vi:
        categoryForm.value.translations.vi?.name || categoryForm.value.translations[defaultLocale]?.name || "",
      description_en:
        categoryForm.value.translations.en?.description || categoryForm.value.translations[defaultLocale]?.description || null,
      description_vi:
        categoryForm.value.translations.vi?.description || categoryForm.value.translations[defaultLocale]?.description || null,
      is_active: categoryForm.value.is_active,
    };

    if (editingCategoryId.value) {
      await api.put(
        `/admin/car-rental-categories/${editingCategoryId.value}`,
        payload
      );
      categoryMessage.value = "Category updated";
    } else {
      await api.post("/admin/car-rental-categories", payload);
      categoryMessage.value = "Category created";
    }
    resetCategoryForm();
    await fetchCategories();
  } catch (error) {
    categoryError.value =
      error.response?.data?.message || "Unable to save category";
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
    await api.delete(`/admin/car-rental-categories/${categoryId}`);
    await fetchCategories();
  } catch (error) {
    categoryError.value =
      error.response?.data?.message || "Unable to delete category";
  }
};

const appendGalleryItems = (items = []) => {
  const nextMeta = { ...galleryMeta.value };
  const urls = [...form.value.images];

  items.forEach((item) => {
    if (!item?.url) return;
    if (!urls.includes(item.url)) {
      urls.push(item.url);
    }
    if (item.id) {
      nextMeta[item.url] = item.id;
    }
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
  if (!imageUrl) {
    clearCoverImage();
    return;
  }
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
  if (!form.value.cover_image && form.value.images.length) {
    setCoverImage(form.value.images[0]);
  }
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

  if (removed && removed === form.value.cover_image) {
    setCoverImage(form.value.images[0]);
  }
};

const getAlbumCoverItem = (album) => {
  if (!album?.media_items?.length) return null;
  return (
    album.media_items.find((item) => item.id === album.cover_media_id) ||
    album.media_items[0]
  );
};

const mapAlbumMedia = (album, options = { replaceGallery: true }) => {
  if (!album) return;
  const replaceGallery = options?.replaceGallery ?? true;
  const nextMeta = replaceGallery ? {} : { ...galleryMeta.value };
  const urls = [];
  (album.media_items || []).forEach((item) => {
    nextMeta[item.url] = item.id;
    if (replaceGallery) {
      urls.push(item.url);
    }
  });

  galleryMeta.value = nextMeta;

  if (replaceGallery) {
    form.value.images = urls;
  }
};

const applyAlbumSelection = (album) => {
  if (!album) return;
  form.value.media_album_id = album.id;
  selectedAlbum.value = album;
  mapAlbumMedia(album, { replaceGallery: true });
  const coverItem = getAlbumCoverItem(album);
  if (coverItem) {
    setCoverImage(coverItem.url);
    form.value.cover_media_id = coverItem.id ?? form.value.cover_media_id;
  } else {
    clearCoverImage();
  }
};

const clearAlbum = () => {
  form.value.media_album_id = null;
  selectedAlbum.value = null;
};

const fetchAlbumDetails = async (albumId) => {
  try {
    const { data } = await api.get(`/admin/media-albums/${albumId}`);
    selectedAlbum.value = data;
    mapAlbumMedia(data, { replaceGallery: false });
    if (!form.value.cover_image) {
      const coverItem = getAlbumCoverItem(data);
      if (coverItem) {
        setCoverImage(coverItem.url);
      }
    }
  } catch (error) {
    console.error("Failed to fetch album", error);
  }
};

function getEmptyForm() {
  return {
    translations: allLocales.reduce((acc, locale) => {
      acc[locale] = {
        name: "",
        brand: "",
        model: "",
        type: "",
        transmission: "",
        fuel_type: "",
        location: "",
        short_description: "",
        description: "",
      };
      return acc;
    }, {}),
    seats: 4,
    price_per_day: 0,
    car_rental_category_id: "",
    features: "",
    images: [],
    cover_image: null,
    cover_media_id: null,
    media_album_id: null,
    status: "active",
    is_available: true,
    contact_phone: "",
    contact_whatsapp: "",
  };
}

function buildFormFromCar(car) {
  const translations = allLocales.reduce((acc, locale) => {
    acc[locale] = {
      name: car[`name_${locale}`] ?? car.name ?? "",
      brand: car[`brand_${locale}`] ?? car.brand ?? "",
      model: car[`model_${locale}`] ?? car.model ?? "",
      type: car[`type_${locale}`] ?? car.type ?? "",
      transmission: car[`transmission_${locale}`] ?? car.transmission ?? "",
      fuel_type: car[`fuel_type_${locale}`] ?? car.fuel_type ?? "",
      location: car[`location_${locale}`] ?? car.location ?? "",
      short_description:
        car[`short_description_${locale}`] ?? car.short_description ?? "",
      description: car[`description_${locale}`] ?? car.description ?? "",
    };
    return acc;
  }, {});

  return {
    translations,
    seats: car.seats || 4,
    price_per_day: Number(car.price_per_day || 0),
    car_rental_category_id:
      car.car_rental_category_id || car.car_rental_category?.id || "",
    features: Array.isArray(car.features) ? car.features.join(", ") : "",
    images: Array.isArray(car.images) ? car.images : [],
    cover_image: car.cover_image || null,
    cover_media_id: car.cover_media_id || null,
    media_album_id: car.media_album_id || null,
    status: car.status || "active",
    is_available: Boolean(car.is_available),
    contact_phone: car.contact_phone || "",
    contact_whatsapp: car.contact_whatsapp || "",
  };
}

function getEmptyCategoryForm() {
  return {
    translations: allLocales.reduce((acc, locale) => {
      acc[locale] = { name: "", description: "" };
      return acc;
    }, {}),
    is_active: true,
  };
}

function buildCategoryForm(category) {
  return {
    translations: allLocales.reduce((acc, locale) => {
      acc[locale] = {
        name: category[`name_${locale}`] ?? category.name ?? "",
        description: category[`description_${locale}`] ?? category.description ?? "",
      };
      return acc;
    }, {}),
    is_active: !!category.is_active,
  };
}

onMounted(() => {
  settingsStore.fetchSettings();
  activeLocale.value = resolveDefaultLocale();
  ensureLocaleBuckets();
  fetchCars();
  fetchCategories();
});
</script>

<style scoped>
.admin-cars {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  position: relative;
}

.admin-cars-grid {
  display: grid;
  grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
  gap: 2rem;
  align-items: start;
}

.glass-card {
  border-radius: 1.5rem;
  padding: 2rem;
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(148, 163, 184, 0.2);
  box-shadow: 0 20px 45px rgba(2, 6, 23, 0.55);
}

.table-card,
.categories-card {
  width: 100%;
}

.panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.panel-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.filter-input,
.filter-select {
  border-radius: 999px;
  padding: 0.5rem 1.25rem;
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(148, 163, 184, 0.3);
  color: #e2e8f0;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 720px;
}

th,
td {
  padding: 0.85rem 0.5rem;
  text-align: left;
  border-bottom: 1px solid rgba(148, 163, 184, 0.15);
}

th {
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8;
}

.badge {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 0.2rem 0.75rem;
  font-size: 0.75rem;
  background: rgba(59, 130, 246, 0.15);
  color: #93c5fd;
}

.badge.inactive {
  background: rgba(248, 113, 113, 0.15);
  color: #fecaca;
}

.status-pill {
  padding: 0.2rem 0.8rem;
  border-radius: 999px;
  font-size: 0.75rem;
  text-transform: capitalize;
}

.status-pill.active {
  background: rgba(34, 197, 94, 0.2);
  color: #86efac;
}

.status-pill.inactive {
  background: rgba(248, 113, 113, 0.2);
  color: #fecaca;
}

.table-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-text {
  background: transparent;
  border: none;
  color: #93c5fd;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
}

.btn-text.danger {
  color: #fca5a5;
}

.table-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 1rem;
  margin-top: 1rem;
  color: #94a3b8;
}

.locale-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.locale-tab {
  border-radius: 999px;
  padding: 0.4rem 1rem;
  border: 1px solid rgba(148, 163, 184, 0.3);
  background: transparent;
  color: #e2e8f0;
  cursor: pointer;
}

.locale-tab.active {
  background: rgba(59, 130, 246, 0.25);
  color: #93c5fd;
}

.category-list {
  margin-top: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.category-list li {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(148, 163, 184, 0.15);
  padding-bottom: 1rem;
}

.category-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.75);
  backdrop-filter: blur(6px);
  padding: 2rem;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  overflow-y: auto;
  z-index: 40;
}

.modal-panel {
  width: min(1100px, 100%);
  margin-top: 1rem;
  background: rgba(15, 23, 42, 0.96);
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 1.5rem;
  padding: 2rem;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.form-field.full {
  grid-column: 1 / -1;
}

.form-field.checkbox {
  flex-direction: row;
  align-items: center;
  gap: 0.6rem;
}

.form-field input,
.form-field textarea,
.form-field select {
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 0.75rem;
  color: #e2e8f0;
  padding: 0.65rem 0.85rem;
}

.form-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.form-actions.full {
  grid-column: 1 / -1;
}

.gallery-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.gallery-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 1rem;
}

.gallery-item {
  border-radius: 1rem;
  overflow: hidden;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(148, 163, 184, 0.2);
}

.gallery-item img {
  width: 100%;
  height: 140px;
  object-fit: cover;
}

.gallery-item .gallery-actions {
  padding: 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.chip-cover-btn {
  border: 1px solid rgba(148, 163, 184, 0.3);
  background: transparent;
  color: #e2e8f0;
  border-radius: 999px;
  padding: 0.3rem 0.6rem;
  cursor: pointer;
}

.chip-cover-btn.active {
  background: rgba(59, 130, 246, 0.2);
  color: #93c5fd;
}

.album-chip {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0.75rem;
  border-radius: 999px;
  background: rgba(59, 130, 246, 0.15);
  color: #bfdbfe;
  margin-bottom: 1rem;
}

@media (max-width: 900px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 1100px) {
  .admin-cars-grid {
    grid-template-columns: 1fr;
  }
}
</style>
