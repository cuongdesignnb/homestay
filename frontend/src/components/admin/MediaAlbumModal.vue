<template>
  <teleport to="body">
    <div v-if="modelValue" class="modal-overlay" @click.self="close">
      <section class="album-modal">
        <header class="modal-head">
          <div>
            <p class="subtitle">
              {{ $t("media.album_subtitle", "Quản lý album ảnh") }}
            </p>
            <h2>{{ $t("media.albums", "Album ảnh") }}</h2>
          </div>
          <button type="button" class="btn btn-text" @click="close">✕</button>
        </header>

        <div class="tabs">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            type="button"
            class="tab-btn"
            :class="{ active: activeTab === tab.id }"
            @click="activeTab = tab.id"
          >
            {{ tab.label }}
          </button>
        </div>

        <div v-if="activeTab === 'list'" class="tab-panel">
          <div class="panel-head">
            <input
              v-model="search"
              type="text"
              class="search-input"
              :placeholder="$t('media.album_search', 'Tìm kiếm album')"
              @keyup.enter="loadAlbums"
            />
            <button class="btn" @click="loadAlbums" :disabled="loading">
              {{ $t("common.refresh", "Làm mới") }}
            </button>
          </div>

          <div v-if="loading" class="empty-state">
            {{ $t("common.loading") }}
          </div>
          <div v-else-if="!albums.length" class="empty-state">
            {{
              $t(
                "media.album_empty",
                "Chưa có album nào, hãy tạo mới ở tab bên cạnh."
              )
            }}
          </div>
          <div v-else class="album-grid">
            <article
              v-for="album in albums"
              :key="album.id"
              class="album-card"
              @click="selectAlbum(album)"
            >
              <div class="preview-grid">
                <img
                  v-for="(image, idx) in previewImages(album)"
                  :key="idx"
                  :src="image"
                  :alt="album.name"
                />
                <div v-if="album.media_count > 3" class="more-pill">
                  +{{ album.media_count - 3 }}
                </div>
              </div>
              <div class="card-body">
                <h3>{{ album.name }}</h3>
                <p>{{ album.media_count }} {{ $t("media.photos", "ảnh") }}</p>
                <button type="button" class="btn btn-secondary">
                  {{ $t("media.use_album", "Chọn album") }}
                </button>
              </div>
            </article>
          </div>
        </div>

        <div v-else class="tab-panel">
          <form class="album-form" @submit.prevent="submitAlbum">
            <label class="form-field">
              <span>{{ $t("common.name", "Tên album") }}</span>
              <input
                v-model="albumForm.name"
                type="text"
                required
                @input="handleNameInput"
              />
            </label>
            <label class="form-field">
              <span>{{ $t("common.slug", "Đường dẫn") }}</span>
              <input
                v-model="albumForm.slug"
                type="text"
                :placeholder="
                  $t('media.slug_hint', 'Tự tạo từ tên, có thể chỉnh sửa.')
                "
                @input="handleSlugInput"
              />
              <small class="hint">{{
                $t("media.slug_hint", "Slug sẽ dùng làm đường dẫn duy nhất.")
              }}</small>
            </label>
            <label class="form-field">
              <span>{{ $t("common.description", "Mô tả") }}</span>
              <textarea v-model="albumForm.description" rows="3" />
            </label>

            <div class="form-field">
              <div class="form-head">
                <span>{{ $t("media.photos", "Ảnh") }}</span>
                <button
                  type="button"
                  class="btn btn-secondary"
                  @click="libraryOpen = true"
                >
                  {{ $t("media.open_library", "Chọn ảnh") }}
                </button>
              </div>
              <div v-if="albumForm.mediaItems.length" class="image-list">
                <div
                  v-for="item in albumForm.mediaItems"
                  :key="item.id"
                  class="album-chip"
                  :class="{ active: albumForm.cover_media_id === item.id }"
                  @click="albumForm.cover_media_id = item.id"
                >
                  <img :src="item.url" :alt="item.original_name" />
                  <span
                    v-if="albumForm.cover_media_id === item.id"
                    class="cover-pill"
                  >
                    {{ $t("media.cover", "Ảnh bìa") }}
                  </span>
                </div>
              </div>
              <p v-else class="hint">
                {{
                  $t(
                    "media.select_photos_hint",
                    "Chọn ít nhất 1 ảnh để tạo album."
                  )
                }}
              </p>
            </div>

            <div class="form-actions">
              <button
                class="btn btn-primary"
                :disabled="saving || !albumForm.mediaItems.length"
              >
                {{
                  saving
                    ? $t("common.loading")
                    : $t("media.save_album", "Lưu album")
                }}
              </button>
              <button
                type="button"
                class="btn"
                @click="resetForm"
                :disabled="saving"
              >
                {{ $t("common.reset", "Làm lại") }}
              </button>
            </div>
            <p v-if="formMessage" class="text-success">{{ formMessage }}</p>
            <p v-if="formError" class="text-error">{{ formError }}</p>
          </form>
        </div>
      </section>
      <MediaLibraryModal v-model="libraryOpen" @select="handleMediaInsert" />
    </div>
  </teleport>
</template>

<script setup>
import { ref, watch } from "vue";
import api from "@/services/api";
import MediaLibraryModal from "./MediaLibraryModal.vue";

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["update:modelValue", "select"]);

const tabs = [
  { id: "list", label: "Album hiện có" },
  { id: "create", label: "Tạo album" },
];

const activeTab = ref("list");
const albums = ref([]);
const loading = ref(false);
const search = ref("");
const libraryOpen = ref(false);
const saving = ref(false);
const formMessage = ref("");
const formError = ref("");

const albumForm = ref({
  name: "",
  slug: "",
  description: "",
  mediaItems: [],
  media_ids: [],
  cover_media_id: null,
});
const slugManuallyEdited = ref(false);

const slugify = (value = "") =>
  value
    .toString()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/(^-|-$)+/g, "");

const handleNameInput = () => {
  if (!slugManuallyEdited.value) {
    albumForm.value.slug = slugify(albumForm.value.name);
  }
};

const handleSlugInput = () => {
  if (!albumForm.value.slug) {
    slugManuallyEdited.value = false;
    return;
  }
  slugManuallyEdited.value = true;
  albumForm.value.slug = slugify(albumForm.value.slug);
};

const loadAlbums = async () => {
  loading.value = true;
  try {
    const { data } = await api.get("/admin/media-albums", {
      params: {
        q: search.value || undefined,
        per_page: 50,
      },
    });
    albums.value = data.data;
  } catch (error) {
    console.error("Failed to load albums", error);
  } finally {
    loading.value = false;
  }
};

const previewImages = (album) => {
  if (!album.media_items?.length) return [];
  return album.media_items.slice(0, 3).map((item) => item.url);
};

const selectAlbum = (album) => {
  emit("select", album);
  close();
};

const handleMediaInsert = (items) => {
  albumForm.value.mediaItems = items;
  albumForm.value.media_ids = items.map((item) => item.id);
  albumForm.value.cover_media_id = items[0]?.id || null;
};

const resetForm = () => {
  albumForm.value = {
    name: "",
    slug: "",
    description: "",
    mediaItems: [],
    media_ids: [],
    cover_media_id: null,
  };
  formMessage.value = "";
  formError.value = "";
  slugManuallyEdited.value = false;
};

const submitAlbum = async () => {
  formMessage.value = "";
  formError.value = "";

  try {
    saving.value = true;
    const payload = {
      name: albumForm.value.name,
      slug: albumForm.value.slug || undefined,
      description: albumForm.value.description,
      media_ids: albumForm.value.media_ids,
      cover_media_id: albumForm.value.cover_media_id,
    };
    const { data } = await api.post("/admin/media-albums", payload);
    formMessage.value = data.message;
    albums.value = [data.album, ...albums.value];
    activeTab.value = "list";
    resetForm();
  } catch (error) {
    formError.value = error.response?.data?.message || "Cannot save album";
  } finally {
    saving.value = false;
  }
};

const close = () => {
  emit("update:modelValue", false);
};

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      activeTab.value = "list";
      loadAlbums();
    }
  }
);
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.65);
  display: grid;
  place-items: center;
  padding: 1rem;
  z-index: 9998;
}

.album-modal {
  width: min(1100px, 100%);
  max-height: 90vh;
  overflow-y: auto;
  background: #0f172a;
  border-radius: 1.5rem;
  padding: 2rem;
  color: #e2e8f0;
  border: 1px solid rgba(148, 163, 184, 0.3);
  box-shadow: 0 45px 120px rgba(2, 6, 23, 0.65);
}

.modal-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.subtitle {
  color: #94a3b8;
  margin-bottom: 0.25rem;
}

.tabs {
  margin-top: 1.5rem;
  display: inline-flex;
  background: rgba(15, 23, 42, 0.6);
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.3);
}

.tab-btn {
  padding: 0.4rem 1.5rem;
  border-radius: 999px;
  font-weight: 600;
  color: #94a3b8;
}

.tab-btn.active {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
}

.tab-panel {
  margin-top: 1.75rem;
}

.panel-head {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.search-input {
  flex: 1;
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.35);
  padding: 0.65rem 1.25rem;
  background: rgba(15, 23, 42, 0.4);
  color: #e2e8f0;
}

.album-grid {
  margin-top: 1.5rem;
  display: grid;
  gap: 1.25rem;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
}

.album-card {
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 1rem;
  overflow: hidden;
  cursor: pointer;
  background: rgba(15, 23, 42, 0.6);
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.album-card:hover {
  transform: translateY(-4px);
  border-color: #818cf8;
}

.preview-grid {
  position: relative;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2px;
  height: 160px;
  background: #020617;
}

.preview-grid img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.more-pill {
  position: absolute;
  right: 0.75rem;
  bottom: 0.75rem;
  background: rgba(15, 23, 42, 0.85);
  color: white;
  padding: 0.2rem 0.75rem;
  border-radius: 0.75rem;
  font-size: 0.75rem;
}

.card-body {
  padding: 1rem;
}

.card-body h3 {
  font-size: 1rem;
  margin-bottom: 0.25rem;
}

.album-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 1rem;
  padding: 1.5rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-field input,
.form-field textarea {
  border-radius: 0.75rem;
  border: 1px solid rgba(148, 163, 184, 0.4);
  background: rgba(2, 6, 23, 0.4);
  color: #f8fafc;
  padding: 0.6rem 0.9rem;
}

.form-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.image-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.album-chip {
  position: relative;
  width: 96px;
  height: 96px;
  border-radius: 0.75rem;
  overflow: hidden;
  border: 2px solid transparent;
  cursor: pointer;
}

.album-chip.active {
  border-color: #38bdf8;
}

.album-chip img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cover-pill {
  position: absolute;
  bottom: 0.35rem;
  left: 0.35rem;
  background: rgba(15, 23, 42, 0.85);
  color: white;
  padding: 0.1rem 0.4rem;
  border-radius: 0.5rem;
  font-size: 0.65rem;
}

.form-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.empty-state {
  margin-top: 2rem;
  text-align: center;
  color: #94a3b8;
}

.text-success {
  color: #34d399;
}

.text-error {
  color: #f87171;
}
</style>
