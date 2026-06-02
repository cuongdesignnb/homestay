<template>
  <div class="admin-rooms">
    <section class="glass-card table-card">
      <header class="panel-head">
        <div>
          <p class="text-sm text-slate-400">{{ $t("admin.rooms_manage") }}</p>
          <h2 class="text-2xl font-semibold text-white">
            {{ $t("admin.manage_rooms") }}
          </h2>
        </div>
        <div class="panel-actions">
          <select
            v-model="filters.room_category_id"
            class="filter-select"
            @change="applyFilter"
          >
            <option value="">{{ $t("common.all", "All") }}</option>
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
            <option value="">{{ $t("common.status", "Status") }}</option>
            <option value="available">Available</option>
            <option value="unavailable">Unavailable</option>
            <option value="maintenance">Maintenance</option>
          </select>
          <button class="btn btn-primary" @click="openCreateForm">
            + {{ $t("admin.add_new", "Add new") }}
          </button>
        </div>
      </header>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>{{ $t("rooms.title") }}</th>
              <th>{{ $t("common.category", "Category") }}</th>
              <th>Type</th>
              <th>{{ $t("rooms.capacity") }}</th>
              <th>{{ $t("rooms.per_night") }}</th>
              <th>{{ $t("common.status", "Status") }}</th>
              <th>{{ $t("common.actions", "Actions") }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="roomLoading">
              <td colspan="7" class="text-center text-slate-400 py-6">
                {{ $t("common.loading") }}
              </td>
            </tr>
            <tr v-else-if="!rooms.length">
              <td colspan="7" class="text-center text-slate-400 py-6">
                {{ $t("common.no_data", "No data yet") }}
              </td>
            </tr>
            <tr v-for="room in rooms" :key="room.id">
              <td>
                <p class="font-semibold text-white">
                  {{ room.name_en || room.name }}
                </p>
                <small class="text-slate-400"
                  >{{ room.type }} · {{ room.beds }} beds</small
                >
              </td>
              <td>
                <span v-if="room.room_category" class="badge">{{
                  room.room_category.name
                }}</span>
                <span v-else class="text-slate-400">—</span>
              </td>
              <td>{{ room.type }}</td>
              <td>{{ room.capacity }}</td>
              <td>{{ formatCurrency(room.price_per_night) }}</td>
              <td>
                <span class="status-pill" :class="room.status">{{
                  room.status
                }}</span>
              </td>
              <td>
                <div class="table-actions">
                  <button class="btn btn-text" @click="openEditForm(room)">
                    {{ $t("common.edit") }}
                  </button>
                  <button
                    class="btn btn-text danger"
                    @click="deleteRoom(room.id)"
                  >
                    {{ $t("common.delete") }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <footer
        class="table-footer"
        v-if="pagination.total > pagination.per_page"
      >
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
          <p class="text-sm text-slate-400">
            {{ $t("common.category", "Category") }}
          </p>
          <h3 class="text-xl font-semibold text-white">Room Categories</h3>
        </div>
      </header>

      <form class="grid gap-3" @submit.prevent="submitCategory">
        <label class="form-field">
          <span>{{ $t("common.name", "Name") }}</span>
          <input v-model="categoryForm.name" type="text" required />
        </label>
        <label class="form-field">
          <span>{{ $t("common.description", "Description") }}</span>
          <textarea v-model="categoryForm.description" rows="3"></textarea>
        </label>
        <label class="form-checkbox">
          <input v-model="categoryForm.is_active" type="checkbox" />
          <span>{{ $t("common.active", "Active") }}</span>
        </label>
        <div class="flex gap-2">
          <button class="btn btn-primary w-full" :disabled="categoryLoading">
            {{ editingCategoryId ? $t("common.update") : $t("common.add") }}
          </button>
          <button
            v-if="editingCategoryId"
            class="btn btn-secondary"
            type="button"
            @click="resetCategoryForm"
          >
            {{ $t("common.cancel") }}
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
            <small class="text-slate-400"
              >{{ category.rooms_count }} rooms</small
            >
          </div>
          <div class="category-actions">
            <span class="badge" :class="{ inactive: !category.is_active }">
              {{
                category.is_active
                  ? $t("common.active", "Active")
                  : $t("common.inactive", "Inactive")
              }}
            </span>
            <button class="btn btn-text" @click="editCategory(category)">
              {{ $t("common.edit") }}
            </button>
            <button
              class="btn btn-text danger"
              @click="deleteCategory(category.id)"
            >
              {{ $t("common.delete") }}
            </button>
          </div>
        </li>
      </ul>
    </section>

    <transition name="fade-scale">
      <div v-if="modalOpen" class="modal-overlay" @click.self="closeModal">
        <section class="modal-panel">
          <header class="modal-header">
            <div>
              <p class="text-sm text-slate-400">
                {{
                  formMode === "create"
                    ? $t("admin.add_new", "Add new")
                    : $t("common.edit")
                }}
              </p>
              <h3 class="text-xl font-semibold text-white">
                {{ $t("rooms.title") }}
              </h3>
            </div>
            <button class="btn btn-text" type="button" @click="closeModal">
              ✕
            </button>
          </header>

          <form class="modal-body" @submit.prevent="submitRoom">
            <section class="modal-section">
              <h4>{{ $t("common.general", "General information") }}</h4>
              <div class="grid grid-2">
                <label class="form-field">
                  <span>{{ $t("common.category", "Category") }}</span>
                  <select v-model="form.room_category_id">
                    <option value="">{{ $t("common.none", "None") }}</option>
                    <option
                      v-for="category in categories"
                      :key="category.id"
                      :value="category.id"
                    >
                      {{ category.name }}
                    </option>
                  </select>
                </label>
                <label class="form-field">
                  <span>Type</span>
                  <input
                    v-model="form.type"
                    type="text"
                    placeholder="Suite, Villa..."
                    required
                  />
                </label>
              </div>
              <div class="grid grid-2">
                <label class="form-field">
                  <span>{{ $t("rooms.capacity") }}</span>
                  <input
                    v-model.number="form.capacity"
                    type="number"
                    min="1"
                    required
                  />
                </label>
                <label class="form-field">
                  <span>Size (m²)</span>
                  <input
                    v-model.number="form.size"
                    type="number"
                    min="0"
                    step="0.1"
                    required
                  />
                </label>
                <label class="form-field">
                  <span>{{ $t("rooms.beds") }}</span>
                  <input
                    v-model.number="form.beds"
                    type="number"
                    min="1"
                    required
                  />
                </label>
                <label class="form-field">
                  <span>{{ $t("rooms.bathrooms") }}</span>
                  <input
                    v-model.number="form.bathrooms"
                    type="number"
                    min="1"
                    required
                  />
                </label>
              </div>
              <div class="grid grid-2">
                <label class="form-field">
                  <span>{{ $t("rooms.per_night") }}</span>
                  <input
                    v-model.number="form.price_per_night"
                    type="number"
                    min="0"
                    required
                  />
                </label>
                <label class="form-field">
                  <span>Discount</span>
                  <input
                    v-model.number="form.discount_price"
                    type="number"
                    min="0"
                    step="0.01"
                  />
                </label>
              </div>
              <label class="form-field">
                <span>Amenities (comma separated)</span>
                <input
                  v-model="form.amenitiesText"
                  type="text"
                  placeholder="Wifi, Breakfast"
                />
              </label>
            </section>

            <section class="modal-section">
              <div class="modal-section-head">
                <h4>{{ $t("common.content", "Content & SEO") }}</h4>
                <div class="locale-tabs">
                  <button
                    v-for="locale in availableLocales"
                    :key="locale"
                    type="button"
                    class="tab-btn"
                    :class="{ active: activeLocale === locale }"
                    @click="activeLocale = locale"
                  >
                    {{ localeLabels[locale] }}
                  </button>
                </div>
              </div>

              <div
                v-for="locale in availableLocales"
                :key="`locale-${locale}`"
                v-show="activeLocale === locale"
                class="locale-panel"
              >
                <label class="form-field">
                  <span
                    >{{ $t("rooms.title") }} ({{ localeLabels[locale] }})</span
                  >
                  <input
                    v-model="form.translations[locale].name"
                    type="text"
                    required
                    @input="handleNameInput(locale)"
                  />
                </label>
                <label class="form-field">
                  <span>Slug</span>
                  <input
                    v-model="form.translations[locale].slug"
                    type="text"
                    placeholder="auto-generated"
                  />
                </label>
                <label class="form-field">
                  <span>{{ $t("common.description", "Description") }}</span>
                  <RichTextEditor
                    v-model="form.translations[locale].description"
                    :placeholder="$t('common.description')"
                  />
                </label>
                <div class="seo-grid">
                  <label class="form-field">
                    <span>Meta title</span>
                    <input
                      v-model="form.translations[locale].metaTitle"
                      type="text"
                    />
                  </label>
                  <label class="form-field">
                    <span>Meta description</span>
                    <textarea
                      v-model="form.translations[locale].metaDescription"
                      rows="2"
                    ></textarea>
                  </label>
                  <label class="form-field">
                    <span>Meta keywords</span>
                    <input
                      v-model="form.translations[locale].metaKeywords"
                      type="text"
                      placeholder="luxury, happy island tour"
                    />
                  </label>
                </div>
              </div>
            </section>

            <section class="modal-section">
              <div class="form-field gap-3">
                <div class="modal-section-head">
                  <span>{{ $t("media.cover_image", "Ảnh đại diện") }}</span>
                  <div class="gallery-actions">
                    <button
                      type="button"
                      class="btn btn-secondary"
                      @click="showCoverModal = true"
                    >
                      {{
                        form.cover_image
                          ? $t("media.change_cover", "Đổi ảnh")
                          : $t("media.choose_cover", "Chọn ảnh đại diện")
                      }}
                    </button>
                  </div>
                </div>
                <div v-if="form.cover_image" class="cover-wrapper">
                  <img :src="form.cover_image" alt="Cover image" />
                  <div class="cover-actions">
                    <button
                      type="button"
                      class="btn btn-secondary"
                      @click="showCoverModal = true"
                    >
                      {{ $t("media.change_cover", "Đổi ảnh") }}
                    </button>
                    <button
                      type="button"
                      class="btn btn-text danger"
                      @click="clearCoverImage"
                    >
                      ✕
                    </button>
                  </div>
                </div>
                <p v-else class="text-slate-400 text-sm">
                  {{
                    $t(
                      "media.cover_image_hint",
                      "Chọn một ảnh đại diện nổi bật hiển thị đầu trang."
                    )
                  }}
                </p>
              </div>
            </section>

            <section class="modal-section">
              <div class="form-field gap-3">
                <div class="modal-section-head">
                  <span>{{ $t("media.gallery", "Gallery") }}</span>
                  <div class="gallery-actions">
                    <button
                      type="button"
                      class="btn btn-secondary"
                      @click="showAlbumModal = true"
                    >
                      {{ $t("media.choose_album", "Choose album") }}
                    </button>
                    <button
                      type="button"
                      class="btn btn-secondary"
                      @click="showMediaModal = true"
                    >
                      {{ $t("media.open_library", "Open media library") }}
                    </button>
                  </div>
                </div>
                <div v-if="selectedAlbum" class="album-summary">
                  <div>
                    <p class="album-name">{{ selectedAlbum.name }}</p>
                    <small
                      >{{ selectedAlbum.media_count }}
                      {{ $t("media.photos", "images") }}</small
                    >
                  </div>
                  <button
                    type="button"
                    class="btn btn-text"
                    @click="clearAlbum"
                  >
                    ✕
                  </button>
                </div>
                <div v-if="form.images.length" class="image-grid">
                  <div
                    v-for="(image, index) in form.images"
                    :key="image"
                    class="image-chip"
                    :class="{ active: form.cover_image === image }"
                  >
                    <img :src="image" alt="Room image" />
                    <button
                      type="button"
                      class="chip-remove"
                      @click="removeImage(index)"
                    >
                      ×
                    </button>
                    <button
                      type="button"
                      class="chip-cover-btn"
                      :class="{ active: form.cover_image === image }"
                      @click="setCoverImage(image)"
                    >
                      {{
                        form.cover_image === image
                          ? $t("media.cover", "Ảnh chính")
                          : $t("media.set_cover", "Đặt làm ảnh chính")
                      }}
                    </button>
                  </div>
                </div>
                <p v-else class="text-slate-400 text-sm">
                  {{
                    $t(
                      "media.no_images_selected",
                      "Chưa có ảnh nào, hãy chọn từ thư viện hoặc album."
                    )
                  }}
                </p>
              </div>
            </section>

            <section class="modal-section">
              <label class="form-field">
                <span>Status</span>
                <select v-model="form.status">
                  <option value="available">Available</option>
                  <option value="unavailable">Unavailable</option>
                  <option value="maintenance">Maintenance</option>
                </select>
              </label>
              <label class="form-field">
                <span>Sort Order</span>
                <input
                  v-model.number="form.sort_order"
                  type="number"
                  min="0"
                  placeholder="Lower = higher priority"
                />
              </label>
            </section>

            <button class="btn btn-primary w-full" :disabled="savingRoom">
              {{
                savingRoom
                  ? $t("common.loading")
                  : formMode === "create"
                  ? $t("admin.create_room", "Create room")
                  : $t("common.update")
              }}
            </button>
            <p v-if="roomMessage" class="text-xs text-emerald-300">
              {{ roomMessage }}
            </p>
            <p v-if="roomError" class="text-xs text-rose-300">
              {{ roomError }}
            </p>
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
import { computed, reactive, ref, onMounted, watch } from "vue";
import api from "@/services/api";
import MediaLibraryModal from "@/components/admin/MediaLibraryModal.vue";
import MediaAlbumModal from "@/components/admin/MediaAlbumModal.vue";
import RichTextEditor from "@/components/common/RichTextEditor.vue";
import { useSettingsStore } from "@/stores/settings";

const rooms = ref([]);
const roomLoading = ref(false);
const pagination = reactive({
  current_page: 1,
  per_page: 10,
  total: 0,
});
const filters = reactive({
  status: "",
  room_category_id: "",
});

const categories = ref([]);
const categoryLoading = ref(false);
const categoryForm = ref({
  name: "",
  description: "",
  is_active: true,
});
const categoryMessage = ref("");
const categoryError = ref("");
const editingCategoryId = ref(null);

const modalOpen = ref(false);
const formMode = ref("create");
const editingRoomId = ref(null);
const showMediaModal = ref(false);
const showCoverModal = ref(false);
const showAlbumModal = ref(false);
const selectedAlbum = ref(null);
const roomMessage = ref("");
const roomError = ref("");
const savingRoom = ref(false);
const settingsStore = useSettingsStore();
const availableLocales = computed(() =>
  settingsStore.bilingualEnabled ? ["vi", "en"] : ["en"]
);
const localeLabels = {
  vi: "Tiếng Việt",
  en: "English",
};
const activeLocale = ref("en");
const galleryMeta = ref({});

const totalPages = computed(() =>
  pagination.per_page ? Math.ceil(pagination.total / pagination.per_page) : 1
);

const allLocales = ["vi", "en"];

const getLocales = () => availableLocales.value;

const resolveDefaultLocale = () => {
  const preferred = settingsStore.defaultLanguage || "en";
  return getLocales().includes(preferred) ? preferred : getLocales()[0];
};

watch(availableLocales, (next) => {
  if (!next.includes(activeLocale.value)) {
    activeLocale.value = resolveDefaultLocale();
  }
  // Ensure form translations have all locale keys
  if (form.value?.translations) {
    next.forEach((locale) => {
      if (!form.value.translations[locale]) {
        form.value.translations[locale] = createEmptyTranslation();
      }
    });
  }
});

const createEmptyTranslation = () => ({
  name: "",
  slug: "",
  description: "",
  metaTitle: "",
  metaDescription: "",
  metaKeywords: "",
});

function getDefaultForm() {
  return {
    translations: allLocales.reduce((acc, locale) => {
      acc[locale] = createEmptyTranslation();
      return acc;
    }, {}),
    room_category_id: "",
    type: "",
    capacity: 2,
    size: 30,
    beds: 1,
    bathrooms: 1,
    price_per_night: 1200000,
    discount_price: null,
    amenitiesText: "",
    cover_image: null,
    cover_media_id: null,
    images: [],
    media_album_id: null,
    status: "available",
    sort_order: null,
  };
}

const form = ref(getDefaultForm());

const hydrateTranslationsFromRoom = (room) =>
  allLocales.reduce((acc, locale) => {
    acc[locale] = {
      name: room[`name_${locale}`] ?? room.name ?? "",
      slug: room[`slug_${locale}`] ?? "",
      description: room[`description_${locale}`] ?? room.description ?? "",
      metaTitle: room[`meta_title_${locale}`] ?? "",
      metaDescription: room[`meta_description_${locale}`] ?? "",
      metaKeywords: room[`meta_keywords_${locale}`] ?? "",
    };
    return acc;
  }, {});

const slugify = (value = "") =>
  (value || "")
    .toString()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/(^-|-$)+/g, "");

const handleNameInput = (locale) => {
  const translation = form.value.translations[locale];
  if (!translation.slug) {
    translation.slug = slugify(translation.name);
  }
};

const formatCurrency = (value) => {
  if (value == null) return "—";
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
    maximumFractionDigits: 0,
  }).format(value);
};

const fetchRooms = async (page = pagination.current_page) => {
  roomLoading.value = true;
  try {
    const params = new URLSearchParams({
      page,
      per_page: pagination.per_page,
    });
    if (filters.status) params.append("status", filters.status);
    if (filters.room_category_id)
      params.append("room_category_id", filters.room_category_id);

    const response = await api.get(`/admin/rooms?${params.toString()}`);
    rooms.value = response.data.data || [];
    pagination.current_page = response.data.current_page;
    pagination.per_page = response.data.per_page;
    pagination.total = response.data.total;
  } catch (err) {
    roomError.value = err.response?.data?.message || "Failed to load rooms";
  } finally {
    roomLoading.value = false;
  }
};

const fetchCategories = async () => {
  categoryLoading.value = true;
  try {
    const response = await api.get("/admin/room-categories");
    categories.value = response.data || [];
  } catch (err) {
    categoryError.value =
      err.response?.data?.message || "Failed to load categories";
  } finally {
    categoryLoading.value = false;
  }
};

const openCreateForm = () => {
  formMode.value = "create";
  editingRoomId.value = null;
  form.value = getDefaultForm();
  galleryMeta.value = {};
  roomMessage.value = "";
  roomError.value = "";
  selectedAlbum.value = null;
  activeLocale.value = resolveDefaultLocale();
  modalOpen.value = true;
};

const openEditForm = (room) => {
  formMode.value = "edit";
  editingRoomId.value = room.id;
  galleryMeta.value = {};
  form.value = {
    translations: hydrateTranslationsFromRoom(room),
    room_category_id: room.room_category_id || room.room_category?.id || "",
    type: room.type || "",
    capacity: room.capacity || 1,
    size: room.size || 0,
    beds: room.beds || 1,
    bathrooms: room.bathrooms || 1,
    price_per_night: room.price_per_night || 0,
    discount_price: room.discount_price,
    amenitiesText: (room.amenities || []).join(", "),
    images: room.images || [],
    cover_image: room.cover_image || (room.images?.[0] ?? null),
    cover_media_id: room.cover_media_id || null,
    media_album_id: room.media_album_id || null,
    status: room.status || "available",
    sort_order: room.sort_order || null,
  };
  roomMessage.value = "";
  roomError.value = "";
  activeLocale.value = resolveDefaultLocale();
  modalOpen.value = true;

  if (form.value.media_album_id) {
    fetchAlbumDetails(form.value.media_album_id);
  } else {
    selectedAlbum.value = null;
  }

  if (!form.value.cover_image && form.value.images.length) {
    form.value.cover_image = form.value.images[0];
  }
};

const closeModal = () => {
  modalOpen.value = false;
  form.value = getDefaultForm();
  editingRoomId.value = null;
  selectedAlbum.value = null;
  galleryMeta.value = {};
};

const payloadFromForm = () => {
  const payload = {
    room_category_id: form.value.room_category_id || null,
    type: form.value.type,
    capacity: form.value.capacity,
    size: form.value.size,
    beds: form.value.beds,
    bathrooms: form.value.bathrooms,
    price_per_night: form.value.price_per_night,
    discount_price: form.value.discount_price || null,
    amenities: form.value.amenitiesText
      .split(",")
      .map((item) => item.trim())
      .filter(Boolean),
    images: form.value.images,
    cover_image: form.value.cover_image,
    cover_media_id: form.value.cover_media_id,
    media_album_id: form.value.media_album_id,
    status: form.value.status,
    sort_order: form.value.sort_order || null,
  };

  getLocales().forEach((locale) => {
    const translation = form.value.translations[locale];
    payload[`name_${locale}`] = translation.name;
    payload[`slug_${locale}`] = translation.slug || null;
    payload[`description_${locale}`] = translation.description;
    payload[`meta_title_${locale}`] = translation.metaTitle || null;
    payload[`meta_description_${locale}`] = translation.metaDescription || null;
    payload[`meta_keywords_${locale}`] = translation.metaKeywords || null;
  });

  return payload;
};

const submitRoom = async () => {
  savingRoom.value = true;
  roomMessage.value = "";
  roomError.value = "";
  try {
    if (formMode.value === "edit" && editingRoomId.value) {
      await api.put(`/admin/rooms/${editingRoomId.value}`, payloadFromForm());
      roomMessage.value = "Room updated successfully";
    } else {
      await api.post("/admin/rooms", payloadFromForm());
      roomMessage.value = "Room created successfully";
    }
    await fetchRooms();
    closeModal();
  } catch (err) {
    roomError.value = err.response?.data?.message || "Unable to save room";
  } finally {
    savingRoom.value = false;
  }
};

const deleteRoom = async (roomId) => {
  if (!confirm("Delete this room?")) return;
  try {
    await api.delete(`/admin/rooms/${roomId}`);
    await fetchRooms();
  } catch (err) {
    roomError.value = err.response?.data?.message || "Unable to delete room";
  }
};

const appendGalleryItems = (items) => {
  if (!items?.length) return;
  const meta = { ...galleryMeta.value };
  const existing = new Set(form.value.images);
  items.forEach((item) => {
    if (!item?.url) return;
    meta[item.url] = item.id;
    if (!existing.has(item.url)) {
      form.value.images.push(item.url);
      existing.add(item.url);
    }
  });
  galleryMeta.value = meta;
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

const applyFilter = () => {
  pagination.current_page = 1;
  fetchRooms(1);
};

const submitCategory = async () => {
  categoryLoading.value = true;
  categoryMessage.value = "";
  categoryError.value = "";
  try {
    const payload = {
      name: categoryForm.value.name,
      description: categoryForm.value.description || null,
      is_active: categoryForm.value.is_active,
    };
    if (editingCategoryId.value) {
      await api.put(
        `/admin/room-categories/${editingCategoryId.value}`,
        payload
      );
      categoryMessage.value = "Category updated";
    } else {
      await api.post("/admin/room-categories", payload);
      categoryMessage.value = "Category created";
    }
    resetCategoryForm();
    await fetchCategories();
  } catch (err) {
    categoryError.value =
      err.response?.data?.message || "Unable to save category";
  } finally {
    categoryLoading.value = false;
  }
};

const editCategory = (category) => {
  editingCategoryId.value = category.id;
  categoryForm.value = {
    name: category.name,
    description: category.description || "",
    is_active: !!category.is_active,
  };
};

const resetCategoryForm = () => {
  editingCategoryId.value = null;
  categoryForm.value = { name: "", description: "", is_active: true };
};

const deleteCategory = async (categoryId) => {
  if (!confirm("Delete this category?")) return;
  try {
    await api.delete(`/admin/room-categories/${categoryId}`);
    await fetchCategories();
  } catch (err) {
    categoryError.value =
      err.response?.data?.message || "Unable to delete category";
  }
};

const changePage = (page) => {
  if (page < 1 || page > totalPages.value) return;
  fetchRooms(page);
};

onMounted(() => {
  settingsStore.fetchSettings();
  fetchRooms();
  fetchCategories();
});
</script>

<style scoped>
.admin-rooms {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  position: relative;
}

.glass-card {
  border-radius: 1.5rem;
  padding: 2rem;
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(148, 163, 184, 0.2);
  box-shadow: 0 20px 45px rgba(2, 6, 23, 0.55);
}

.table-card {
  width: 100%;
}

.panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}

.panel-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

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

.status-pill.available {
  background: rgba(34, 197, 94, 0.2);
  color: #86efac;
}

.status-pill.unavailable {
  background: rgba(248, 113, 113, 0.2);
  color: #fecaca;
}

.status-pill.maintenance {
  background: rgba(250, 204, 21, 0.2);
  color: #fde68a;
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

.categories-card {
  width: 100%;
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
  width: min(1080px, 100%);
  margin-top: 1rem;
  background: rgba(15, 23, 42, 0.96);
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 1.5rem;
  box-shadow: 0 30px 60px rgba(2, 6, 23, 0.65);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.15);
}

.modal-body {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.modal-section {
  padding: 1.25rem;
  border-radius: 1rem;
  background: rgba(30, 41, 59, 0.55);
  border: 1px solid rgba(148, 163, 184, 0.15);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.modal-section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.gallery-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.album-summary {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 0.9rem;
  border: 1px solid rgba(59, 130, 246, 0.35);
  background: rgba(59, 130, 246, 0.1);
}

.album-name {
  font-weight: 600;
  color: #e2e8f0;
}

.locale-tabs {
  .cover-wrapper {
    position: relative;
    border-radius: 1rem;
    overflow: hidden;
    border: 1px solid rgba(148, 163, 184, 0.4);
    min-height: 220px;
  }

  .cover-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .cover-actions {
    position: absolute;
    bottom: 0.75rem;
    left: 0.75rem;
    right: 0.75rem;
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
  }
  display: inline-flex;
  background: rgba(15, 23, 42, 0.6);
  border-radius: 999px;
  padding: 0.25rem;
  border: 1px solid rgba(148, 163, 184, 0.25);
}

.tab-btn {
  border: none;
  width: 110px;
  height: 110px;
  padding: 0.35rem 1rem;
  border-radius: 999px;
  font-size: 0.85rem;
  cursor: pointer;

  .image-chip.active {
    border-color: rgba(79, 70, 229, 0.9);
    box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.35);
  }
}

.tab-btn.active {
  background: #2563eb;
  color: #fff;
}

.chip-remove {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.seo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 0.75rem;
}

.chip-cover-btn {
  position: absolute;
  left: 6px;
  right: 6px;
  bottom: 6px;
  border: none;
  border-radius: 0.65rem;
  padding: 0.25rem 0.5rem;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: rgba(15, 23, 42, 0.75);
  color: #cbd5f5;
  cursor: pointer;
}

.chip-cover-btn.active {
  background: rgba(79, 70, 229, 0.9);
  color: #fff;
}
.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  font-size: 0.9rem;
  color: #cbd5f5;
}

.form-field input,
.form-field textarea,
.form-field select {
  background: rgba(15, 23, 42, 0.65);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 0.75rem;
  padding: 0.75rem 1rem;
  color: #e2e8f0;
}

.form-checkbox {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  color: #cbd5f5;
}

.grid-2 {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
}

.image-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.image-chip {
  position: relative;
  width: 90px;
  height: 90px;
  border-radius: 0.75rem;
  overflow: hidden;
  border: 1px solid rgba(148, 163, 184, 0.3);
}

.image-chip img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-chip button {
  position: absolute;
  top: 4px;
  right: 4px;
  background: rgba(15, 23, 42, 0.85);
  border: none;
  color: #f87171;
  font-size: 0.85rem;
  padding: 0.1rem 0.35rem;
  cursor: pointer;
  border-radius: 999px;
}

.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.97);
}

@media (max-width: 1024px) {
  .modal-overlay {
    padding: 1.5rem;
  }

  .modal-body {
    padding: 1.5rem;
  }
}

@media (max-width: 640px) {
  .modal-overlay {
    padding: 1rem;
  }

  .modal-body {
    padding: 1.25rem;
  }

  .modal-section {
    padding: 1rem;
  }

  .locale-tabs {
    width: 100%;
    justify-content: space-between;
  }
}

@media (min-width: 1024px) {
  .admin-rooms {
    flex-direction: row;
  }

  .table-card {
    flex: 2;
  }

  .categories-card {
    flex: 1;
  }
}
</style>
