<template>
  <teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 flex items-center justify-center bg-black/50 px-4"
      style="z-index: 9999"
      @click.self="closeModal"
    >
      <div
        class="flex w-full max-w-5xl flex-col overflow-hidden rounded-lg bg-white shadow-xl"
      >
        <header class="flex items-center justify-between border-b px-6 py-4">
          <div>
            <h2 class="text-lg font-semibold">Media Library</h2>
            <p class="text-sm text-gray-500">
              Upload images and select them for your content.
            </p>
          </div>
          <button
            type="button"
            class="text-gray-500 transition hover:text-gray-700"
            @click="closeModal"
          >
            ✕
          </button>
        </header>

        <div
          class="flex flex-col gap-4 border-b px-6 py-4 lg:flex-row lg:items-center"
        >
          <div class="flex flex-1 items-center gap-3">
            <label
              class="inline-flex cursor-pointer items-center rounded-md border border-dashed border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-primary hover:text-primary"
            >
              <input
                type="file"
                class="hidden"
                multiple
                accept="image/*"
                @change="handleFiles"
              />
              <span>{{
                uploading
                  ? uploadProgress
                    ? `Uploading ${uploadProgress}…`
                    : "Uploading…"
                  : "Upload Images"
              }}</span>
            </label>

            <div class="relative w-full max-w-xs">
              <input
                v-model="search"
                type="text"
                placeholder="Search"
                class="w-full rounded-md border px-3 py-2 text-sm focus:border-primary focus:outline-none"
                @keyup.enter="applySearch"
              />
              <button
                class="absolute inset-y-0 right-2 text-sm text-gray-500"
                type="button"
                @click="applySearch"
              >
                Go
              </button>
            </div>
          </div>

          <div class="text-sm text-gray-500">
            Selected: <span class="font-semibold">{{ selected.length }}</span>
          </div>
        </div>

        <div
          v-if="uploadError"
          class="mx-6 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-600"
        >
          {{ uploadError }}
          <button
            type="button"
            class="ml-2 font-semibold text-red-800 hover:underline"
            @click="uploadError = ''"
          >
            ✕
          </button>
        </div>

        <div class="flex flex-1 flex-col overflow-y-auto">
          <div
            v-if="loading && mediaItems.length === 0"
            class="flex flex-1 items-center justify-center py-12 text-gray-500"
          >
            Loading media…
          </div>

          <div
            v-else-if="mediaItems.length === 0"
            class="flex flex-1 items-center justify-center py-16 text-gray-400"
          >
            No media yet. Upload images to get started.
          </div>

          <div
            v-else
            class="grid gap-4 p-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4"
          >
            <button
              v-for="item in mediaItems"
              :key="item.id"
              type="button"
              class="group flex flex-col overflow-hidden rounded-md border text-left shadow-sm transition hover:shadow-md"
              :class="
                isSelected(item)
                  ? 'border-primary ring-2 ring-primary'
                  : 'border-gray-200'
              "
              @click="toggleSelect(item)"
            >
              <div class="relative h-36 w-full overflow-hidden bg-gray-50">
                <img
                  :src="item.url"
                  :alt="item.alt_text || item.original_name"
                  class="h-full w-full object-cover"
                />
              </div>
              <div class="flex flex-1 flex-col gap-1 px-3 py-2 text-xs">
                <p class="truncate font-medium text-gray-900">
                  {{ item.original_name }}
                </p>
                <p class="text-gray-500">
                  {{ item.width }} × {{ item.height }}
                </p>
                <p class="text-gray-400">{{ formatFileSize(item.size) }}</p>
              </div>
            </button>
          </div>
        </div>

        <div class="flex items-center justify-between border-t px-6 py-4">
          <button
            v-if="hasMore"
            type="button"
            class="text-sm font-medium text-primary"
            @click="loadMore"
          >
            {{ loading ? "Loading…" : "Load more" }}
          </button>
          <span v-else class="text-sm text-gray-500">No more media</span>

          <div class="flex items-center gap-3">
            <button
              type="button"
              class="rounded-md border px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50"
              @click="closeModal"
            >
              Cancel
            </button>
            <button
              type="button"
              class="rounded-md bg-primary px-4 py-2 text-sm font-semibold text-white disabled:opacity-70"
              :disabled="selected.length === 0"
              @click="insertSelection"
            >
              Insert {{ multiple ? "Media" : "Image" }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </teleport>
</template>

<script setup>
import { ref, watch } from "vue";
import api from "@/services/api";

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  multiple: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(["update:modelValue", "select"]);

const mediaItems = ref([]);
const selected = ref([]);
const search = ref("");
const loading = ref(false);
const uploading = ref(false);
const uploadProgress = ref("");
const uploadError = ref("");
const currentPage = ref(1);
const hasMore = ref(true);

const resetState = (options = { resetSearch: false }) => {
  mediaItems.value = [];
  selected.value = [];
  currentPage.value = 1;
  hasMore.value = true;
  if (options.resetSearch) {
    search.value = "";
  }
};

const fetchMedia = async (page = 1, reset = false) => {
  if (loading.value) return;
  loading.value = true;
  try {
    const { data } = await api.get("/admin/media", {
      params: {
        page,
        q: search.value || undefined,
      },
    });

    if (reset) {
      mediaItems.value = data.data;
    } else {
      mediaItems.value = [...mediaItems.value, ...data.data];
    }

    currentPage.value = data.current_page;
    hasMore.value = data.current_page < data.last_page;
  } finally {
    loading.value = false;
  }
};

const loadMore = () => {
  if (!hasMore.value) return;
  fetchMedia(currentPage.value + 1);
};

const applySearch = () => {
  resetState();
  fetchMedia(1, true);
};

const handleFiles = async (event) => {
  const files = event.target.files;
  if (!files?.length) return;
  if (uploading.value) return;

  uploading.value = true;
  uploadError.value = "";
  uploadProgress.value = "";

  const fileList = Array.from(files);
  const allUploaded = [];
  const errors = [];

  // Upload files one by one to avoid PHP post_max_size limits
  for (let i = 0; i < fileList.length; i++) {
    uploadProgress.value = `${i + 1}/${fileList.length}`;
    const formData = new FormData();
    formData.append("files[]", fileList[i]);

    try {
      const { data } = await api.post("/admin/media", formData, {
        headers: { "Content-Type": "multipart/form-data" },
      });
      if (data.media?.length) {
        allUploaded.push(...data.media);
      }
    } catch (err) {
      const fileName = fileList[i].name;
      const msg =
        err.response?.data?.message ||
        (err.response?.data?.errors
          ? Object.values(err.response.data.errors).flat().join(", ")
          : null) ||
        err.message ||
        "Upload failed";
      errors.push(`${fileName}: ${msg}`);
    }
  }

  if (allUploaded.length) {
    mediaItems.value = [...allUploaded, ...mediaItems.value];
  }

  if (errors.length) {
    uploadError.value = errors.join(" | ");
  }

  uploading.value = false;
  uploadProgress.value = "";
  event.target.value = "";
};

const isSelected = (item) =>
  selected.value.some((entry) => entry.id === item.id);

const toggleSelect = (item) => {
  if (!props.multiple) {
    selected.value = [item];
    insertSelection();
    return;
  }

  if (isSelected(item)) {
    selected.value = selected.value.filter((entry) => entry.id !== item.id);
  } else {
    selected.value = [...selected.value, item];
  }
};

const insertSelection = () => {
  if (!selected.value.length) return;
  emit(
    "select",
    selected.value.map((item) => ({ ...item }))
  );
  closeModal();
};

const closeModal = () => {
  emit("update:modelValue", false);
};

const formatFileSize = (bytes) => {
  if (!bytes) return "—";
  const units = ["B", "KB", "MB", "GB"];
  let size = bytes;
  let unitIndex = 0;
  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024;
    unitIndex += 1;
  }
  return `${size.toFixed(1)} ${units[unitIndex]}`;
};

watch(
  () => props.modelValue,
  (open) => {
    if (open) {
      resetState({ resetSearch: true });
      fetchMedia(1, true);
    }
  }
);
</script>
