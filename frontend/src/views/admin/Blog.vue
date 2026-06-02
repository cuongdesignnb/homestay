<template>
  <div class="admin-blog">
    <section class="glass-card table-card">
      <header class="panel-head">
        <div>
          <p class="text-sm text-slate-400">
            {{ $t("admin.blog_manage", "Blog & Stories") }}
          </p>
          <h2 class="text-2xl font-semibold text-white">
            {{ $t("admin.blog_title", "Lan tỏa câu chuyện Happy Island Tour") }}
          </h2>
        </div>
        <button class="btn btn-primary" type="button" @click="openCreateForm">
          + {{ $t("admin.add_new", "Add new") }}
        </button>
      </header>

      <section class="filter-grid">
        <div class="filter-card search-card">
          <label>{{ $t("common.search", "Search posts") }}</label>
          <div class="search-field">
            <input
              v-model="searchTerm"
              type="text"
              :placeholder="
                $t('blog.search_placeholder', 'Tìm theo tiêu đề, tags...')
              "
              @keyup.enter="searchPosts"
            />
            <button
              class="btn btn-secondary"
              type="button"
              @click="searchPosts"
            >
              {{ $t("common.search") }}
            </button>
          </div>
        </div>
        <div class="filter-card">
          <label>{{ $t("common.status", "Status") }}</label>
          <select v-model="filters.status" @change="applyFilters">
            <option value="">{{ $t("common.all", "All") }}</option>
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
          </select>
        </div>
        <div class="filter-card">
          <label>{{ $t("common.category", "Category") }}</label>
          <select v-model="filters.category_id" @change="applyFilters">
            <option value="">{{ $t("common.all", "All") }}</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name_vi || category.name_en || category.name }}
            </option>
          </select>
        </div>
      </section>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>{{ $t("blog.title", "Title") }}</th>
              <th>{{ $t("common.category", "Category") }}</th>
              <th>{{ $t("common.status", "Status") }}</th>
              <th>{{ $t("blog.published_at", "Published at") }}</th>
              <th>{{ $t("common.actions", "Actions") }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="postLoading">
              <td colspan="5" class="text-center text-slate-400 py-6">
                {{ $t("common.loading") }}
              </td>
            </tr>
            <tr v-else-if="!posts.length">
              <td colspan="5" class="text-center text-slate-400 py-6">
                {{ $t("common.no_data", "No data yet") }}
              </td>
            </tr>
            <tr v-for="post in posts" :key="post.id">
              <td>
                <p class="font-semibold text-white">
                  {{ post.title_vi || post.title_en || post.title }}
                </p>
                <small class="text-slate-400 line-clamp-1">{{
                  post.excerpt_vi || post.excerpt_en || post.excerpt
                }}</small>
                <div
                  class="tag-row"
                  v-if="Array.isArray(post.tags) && post.tags.length"
                >
                  <span class="tag-chip" v-for="tag in post.tags" :key="tag"
                    >#{{ tag }}</span
                  >
                </div>
              </td>
              <td>
                <span class="badge" v-if="post.category">
                  {{
                    post.category.name_vi ||
                    post.category.name_en ||
                    post.category.name
                  }}
                </span>
                <span v-else class="text-slate-500">—</span>
              </td>
              <td>
                <span class="status-pill" :class="post.status">{{
                  post.status
                }}</span>
              </td>
              <td>
                <p class="text-sm text-white" v-if="post.published_at">
                  {{ formatDate(post.published_at) }}
                </p>
                <p class="text-xs text-slate-500">
                  {{ post.view_count || 0 }} views
                </p>
              </td>
              <td>
                <div class="table-actions">
                  <button
                    class="btn btn-text"
                    type="button"
                    @click="openEditForm(post)"
                  >
                    {{ $t("common.edit") }}
                  </button>
                  <button
                    v-if="post.status !== 'published'"
                    class="btn btn-text"
                    type="button"
                    @click="publishPost(post)"
                  >
                    {{ $t("blog.publish", "Publish") }}
                  </button>
                  <button
                    v-else
                    class="btn btn-text"
                    type="button"
                    @click="unpublishPost(post)"
                  >
                    {{ $t("blog.unpublish", "Unpublish") }}
                  </button>
                  <button
                    class="btn btn-text danger"
                    type="button"
                    @click="deletePost(post)"
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
        <span
          >Page {{ pagination.current_page }} / {{ pagination.last_page }}</span
        >
        <button
          class="btn btn-text"
          :disabled="pagination.current_page === pagination.last_page"
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
          <h3 class="text-xl font-semibold text-white">
            {{ $t("admin.blog_categories", "Blog categories") }}
          </h3>
        </div>
      </header>

      <form class="grid gap-3" @submit.prevent="submitCategory">
        <label v-if="shouldShowEnglish" class="form-field">
          <span>{{ $t("common.name", "Name") }} (EN)</span>
          <input v-model="categoryForm.name_en" type="text" required />
        </label>
        <label v-if="shouldShowVietnamese" class="form-field">
          <span>{{ $t("common.name", "Name") }} (VI)</span>
          <input
            v-model="categoryForm.name_vi"
            type="text"
            :required="shouldShowEnglish"
          />
        </label>
        <label class="form-field">
          <span>Slug</span>
          <input
            v-model="categoryForm.slug"
            type="text"
            placeholder="auto-generated"
          />
        </label>
        <label v-if="shouldShowEnglish" class="form-field">
          <span>{{ $t("common.description", "Description") }} (EN)</span>
          <textarea v-model="categoryForm.description_en" rows="2"></textarea>
        </label>
        <label v-if="shouldShowVietnamese" class="form-field">
          <span>{{ $t("common.description", "Description") }} (VI)</span>
          <textarea v-model="categoryForm.description_vi" rows="2"></textarea>
        </label>
        <button class="btn btn-primary" :disabled="categoryLoading">
          {{ categoryLoading ? $t("common.loading") : $t("common.add") }}
        </button>
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
              {{ category.name_vi || category.name_en || category.name }}
            </p>
            <small class="text-slate-400"
              >{{ category.posts_count || 0 }} posts</small
            >
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
                {{ $t("blog.title", "Title") }}
              </h3>
            </div>
            <button class="btn btn-text" type="button" @click="closeModal">
              ✕
            </button>
          </header>

          <form class="modal-body" @submit.prevent="submitPost">
            <section class="modal-section">
              <h4>{{ $t("common.general", "General information") }}</h4>
              <div class="grid grid-2">
                <label class="form-field">
                  <span>{{ $t("common.category", "Category") }}</span>
                  <select v-model="form.category_id" required>
                    <option value="">
                      {{ $t("common.select_option", "Select option") }}
                    </option>
                    <option
                      v-for="category in categories"
                      :key="category.id"
                      :value="category.id"
                    >
                      {{
                        category.name_vi || category.name_en || category.name
                      }}
                    </option>
                  </select>
                </label>
                <label class="form-field">
                  <span>{{ $t("common.status", "Status") }}</span>
                  <select v-model="form.status">
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                  </select>
                </label>
              </div>
              <label class="form-field">
                <span>{{ $t("blog.featured_image", "Featured image") }}</span>
                <div class="image-picker">
                  <div v-if="form.featured_image" class="image-chip">
                    <img :src="form.featured_image" alt="Featured" />
                    <button type="button" @click="form.featured_image = ''">
                      ×
                    </button>
                  </div>
                  <button
                    type="button"
                    class="btn btn-secondary"
                    @click="showMediaModal = true"
                  >
                    {{
                      form.featured_image
                        ? $t("media.change_image", "Change image")
                        : $t("media.select_image", "Select image")
                    }}
                  </button>
                </div>
              </label>
              <label class="form-field">
                <span>{{ $t("blog.tags", "Tags") }}</span>
                <input
                  v-model="form.tagsText"
                  type="text"
                  placeholder="travel, guide"
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
                    >{{ $t("blog.title", "Title") }} ({{
                      localeLabels[locale]
                    }})</span
                  >
                  <input
                    v-model="form.translations[locale].title"
                    type="text"
                    @input="handleTitleInput(locale)"
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
                  <textarea
                    v-model="form.translations[locale].excerpt"
                    rows="2"
                  ></textarea>
                </label>
                <label class="form-field">
                  <span>{{ $t("common.content", "Content") }}</span>
                  <RichTextEditor
                    v-model="form.translations[locale].content"
                    :placeholder="$t('common.content')"
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
                      placeholder="happy island tour, vietnam"
                    />
                  </label>
                </div>
              </div>
            </section>

            <button class="btn btn-primary w-full" :disabled="savingPost">
              {{
                savingPost
                  ? $t("common.loading")
                  : formMode === "create"
                  ? $t("blog.create_post", "Create post")
                  : $t("common.update")
              }}
            </button>
            <p v-if="postMessage" class="text-xs text-emerald-300">
              {{ postMessage }}
            </p>
            <p v-if="postError" class="text-xs text-rose-300">
              {{ postError }}
            </p>
          </form>
        </section>
      </div>
    </transition>

    <MediaLibraryModal
      v-model="showMediaModal"
      :multiple="false"
      @select="handleFeaturedImage"
    />
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import dayjs from "dayjs";
import api from "@/services/api";
import MediaLibraryModal from "@/components/admin/MediaLibraryModal.vue";
import RichTextEditor from "@/components/common/RichTextEditor.vue";
import { useI18n } from "vue-i18n";
import { useSettingsStore } from "@/stores/settings";

const { t } = useI18n();

const posts = ref([]);
const postLoading = ref(false);
const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});
const filters = reactive({
  status: "",
  category_id: "",
});
const searchTerm = ref("");

const categories = ref([]);
const categoryLoading = ref(false);
const categoryForm = reactive({
  name_en: "",
  name_vi: "",
  slug: "",
  description_en: "",
  description_vi: "",
});
const categoryMessage = ref("");
const categoryError = ref("");

const settingsStore = useSettingsStore();
const allLocales = ["en", "vi"];
const availableLocales = computed(() =>
  settingsStore.bilingualEnabled ? ["vi", "en"] : ["en"]
);
const localeLabels = {
  en: "English",
  vi: "Tiếng Việt",
};

const modalOpen = ref(false);
const formMode = ref("create");
const activeLocale = ref("vi");
const editingPostId = ref(null);
const postMessage = ref("");
const postError = ref("");
const savingPost = ref(false);
const showMediaModal = ref(false);

const shouldShowVietnamese = computed(() => settingsStore.bilingualEnabled);
const shouldShowEnglish = computed(() => true);

const resolveDefaultLocale = () => {
  const preferred = settingsStore.defaultLanguage || "en";
  return availableLocales.value.includes(preferred)
    ? preferred
    : availableLocales.value[0];
};

watch(availableLocales, (next) => {
  if (!next.includes(activeLocale.value)) {
    activeLocale.value = resolveDefaultLocale();
  }
});

const createEmptyTranslation = () => ({
  title: "",
  slug: "",
  excerpt: "",
  content: "",
  metaTitle: "",
  metaDescription: "",
  metaKeywords: "",
});

const getDefaultForm = () => ({
  translations: allLocales.reduce((acc, locale) => {
    acc[locale] = createEmptyTranslation();
    return acc;
  }, {}),
  category_id: "",
  status: "draft",
  featured_image: "",
  tagsText: "",
});

const form = ref(getDefaultForm());

const fetchPosts = async (page = 1) => {
  postLoading.value = true;
  postError.value = "";
  try {
    const { data } = await api.get("/admin/blog/posts", {
      params: {
        page,
        status: filters.status || undefined,
        category_id: filters.category_id || undefined,
        search: searchTerm.value || undefined,
      },
    });

    posts.value = data.data;
    pagination.current_page = data.current_page;
    pagination.last_page = data.last_page;
    pagination.per_page = data.per_page;
    pagination.total = data.total;
  } catch (error) {
    postError.value = error.response?.data?.message || "Unable to load posts";
  } finally {
    postLoading.value = false;
  }
};

const fetchCategories = async () => {
  try {
    const { data } = await api.get("/admin/blog/categories");
    categories.value = data;
  } catch (error) {
    console.error("Failed to load categories", error);
  }
};

const applyFilters = () => {
  pagination.current_page = 1;
  fetchPosts(1);
};

const searchPosts = () => {
  pagination.current_page = 1;
  fetchPosts(1);
};

const changePage = (page) => {
  if (page < 1 || page > pagination.last_page) return;
  fetchPosts(page);
};

const openCreateForm = () => {
  formMode.value = "create";
  editingPostId.value = null;
  form.value = getDefaultForm();
  postMessage.value = "";
  postError.value = "";
  activeLocale.value = resolveDefaultLocale();
  modalOpen.value = true;
};

const openEditForm = (post) => {
  formMode.value = "edit";
  editingPostId.value = post.id;
  form.value = {
    translations: allLocales.reduce((acc, locale) => {
      acc[locale] = {
        title: post[`title_${locale}`] ?? post.title ?? "",
        slug: post[`slug_${locale}`] ?? "",
        excerpt: post[`excerpt_${locale}`] ?? post.excerpt ?? "",
        content: post[`content_${locale}`] ?? post.content ?? "",
        metaTitle: post[`meta_title_${locale}`] ?? "",
        metaDescription: post[`meta_description_${locale}`] ?? "",
        metaKeywords: post[`meta_keywords_${locale}`] ?? "",
      };
      return acc;
    }, {}),
    category_id: post.category_id ?? "",
    status: post.status ?? "draft",
    featured_image: post.featured_image ?? "",
    tagsText: Array.isArray(post.tags) ? post.tags.join(", ") : "",
  };

  postMessage.value = "";
  postError.value = "";
  activeLocale.value = resolveDefaultLocale();
  modalOpen.value = true;
};

const resolveTranslation = (locale) => {
  const current = form.value.translations[locale] || createEmptyTranslation();
  if (!settingsStore.bilingualEnabled && locale === "vi") {
    const fallback = form.value.translations.en || createEmptyTranslation();
    return {
      ...current,
      title: current.title || fallback.title,
      slug: current.slug || fallback.slug,
      excerpt: current.excerpt || fallback.excerpt,
      content: current.content || fallback.content,
      metaTitle: current.metaTitle || fallback.metaTitle,
      metaDescription: current.metaDescription || fallback.metaDescription,
      metaKeywords: current.metaKeywords || fallback.metaKeywords,
    };
  }
  return current;
};

const buildPayload = () => {
  const payload = {
    category_id: form.value.category_id,
    status: form.value.status,
    featured_image: form.value.featured_image || null,
    tags: form.value.tagsText
      ? form.value.tagsText
          .split(",")
          .map((tag) => tag.trim())
          .filter(Boolean)
      : [],
  };

  allLocales.forEach((locale) => {
    const translation = resolveTranslation(locale);
    payload[`title_${locale}`] = translation.title;
    payload[`slug_${locale}`] = translation.slug || null;
    payload[`excerpt_${locale}`] = translation.excerpt || null;
    payload[`content_${locale}`] = translation.content;
    payload[`meta_title_${locale}`] = translation.metaTitle || null;
    payload[`meta_description_${locale}`] = translation.metaDescription || null;
    payload[`meta_keywords_${locale}`] = translation.metaKeywords || null;
  });

  return payload;
};

const submitPost = async () => {
  savingPost.value = true;
  postMessage.value = "";
  postError.value = "";

  const payload = buildPayload();

  try {
    if (formMode.value === "create") {
      await api.post("/admin/blog/posts", payload);
      postMessage.value = t("blog.create_success", "Post created successfully");
    } else {
      await api.put(`/admin/blog/posts/${editingPostId.value}`, payload);
      postMessage.value = t("blog.update_success", "Post updated successfully");
    }

    modalOpen.value = false;
    fetchPosts(pagination.current_page);
  } catch (error) {
    postError.value = error.response?.data?.message || "Unable to save post";
  } finally {
    savingPost.value = false;
  }
};

const deletePost = async (post) => {
  if (!window.confirm("Delete this post?")) return;
  try {
    await api.delete(`/admin/blog/posts/${post.id}`);
    fetchPosts(pagination.current_page);
  } catch (error) {
    postError.value = error.response?.data?.message || "Unable to delete post";
  }
};

const publishPost = async (post) => {
  try {
    await api.post(`/admin/blog/posts/${post.id}/publish`);
    fetchPosts(pagination.current_page);
  } catch (error) {
    postError.value = error.response?.data?.message || "Unable to publish post";
  }
};

const unpublishPost = async (post) => {
  try {
    await api.post(`/admin/blog/posts/${post.id}/unpublish`);
    fetchPosts(pagination.current_page);
  } catch (error) {
    postError.value =
      error.response?.data?.message || "Unable to unpublish post";
  }
};

const submitCategory = async () => {
  categoryLoading.value = true;
  categoryMessage.value = "";
  categoryError.value = "";

  try {
    if (!settingsStore.bilingualEnabled) {
      categoryForm.name_vi = categoryForm.name_vi || categoryForm.name_en;
      categoryForm.description_vi =
        categoryForm.description_vi || categoryForm.description_en;
    }
    const { data } = await api.post("/admin/blog/categories", categoryForm);
    categories.value = [data.category, ...categories.value];
    categoryMessage.value = t("blog.category_created", "Category created");
    categoryForm.name_en = "";
    categoryForm.name_vi = "";
    categoryForm.slug = "";
    categoryForm.description_en = "";
    categoryForm.description_vi = "";
  } catch (error) {
    categoryError.value =
      error.response?.data?.message || "Unable to create category";
  } finally {
    categoryLoading.value = false;
  }
};

const closeModal = () => {
  modalOpen.value = false;
};

const handleFeaturedImage = (mediaItems) => {
  if (!mediaItems?.length) return;
  form.value.featured_image = mediaItems[0].url;
};

const handleTitleInput = (locale) => {
  const translation = form.value.translations[locale];
  if (!translation.slug) {
    translation.slug = slugify(translation.title);
  }
};

const slugify = (value = "") =>
  (value || "")
    .toString()
    .normalize("NFD")
    .replace(/[^\u0000-\u007E]/g, "")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/(^-|-$)+/g, "");

const formatDate = (date) => dayjs(date).format("DD MMM YYYY");

onMounted(() => {
  settingsStore.fetchSettings();
  fetchPosts();
  fetchCategories();
});
</script>

<style scoped>
.admin-blog {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.glass-card {
  border-radius: 1.5rem;
  padding: 2rem;
  background: rgba(15, 23, 42, 0.9);
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

.filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.filter-card {
  border-radius: 1rem;
  padding: 1rem 1.25rem;
  background: rgba(15, 23, 42, 0.55);
  border: 1px solid rgba(148, 163, 184, 0.2);
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-card label {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #94a3b8;
}

.filter-card select {
  border-radius: 0.75rem;
  border: 1px solid rgba(148, 163, 184, 0.35);
  background: rgba(15, 23, 42, 0.4);
  padding: 0.65rem 0.9rem;
  color: #e2e8f0;
}

.search-card {
  grid-column: span 2;
}

@media (max-width: 900px) {
  .search-card {
    grid-column: span 1;
  }
}

.search-field {
  display: flex;
  gap: 0.5rem;
}

.search-field input {
  border-radius: 0.75rem;
  border: 1px solid rgba(148, 163, 184, 0.35);
  background: rgba(15, 23, 42, 0.4);
  padding: 0.45rem 0.9rem;
  color: #e2e8f0;
}

.table-wrapper {
  overflow-x: auto;
}

.table-wrapper table {
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

.tag-row {
  margin-top: 0.5rem;
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
}

.tag-chip {
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  background: rgba(59, 130, 246, 0.15);
  font-size: 0.75rem;
  color: #bfdbfe;
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

.status-pill {
  padding: 0.2rem 0.8rem;
  border-radius: 999px;
  font-size: 0.75rem;
  text-transform: capitalize;
}

.status-pill.draft {
  background: rgba(251, 191, 36, 0.2);
  color: #fde68a;
}

.status-pill.published {
  background: rgba(34, 197, 94, 0.2);
  color: #86efac;
}

.status-pill.archived {
  background: rgba(148, 163, 184, 0.25);
  color: #cbd5f5;
}

.table-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
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
  gap: 0.75rem;
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

.locale-tabs {
  display: inline-flex;
  background: rgba(15, 23, 42, 0.6);
  border-radius: 999px;
  padding: 0.25rem;
  border: 1px solid rgba(148, 163, 184, 0.25);
}

.tab-btn {
  border: none;
  background: transparent;
  color: #94a3b8;
  padding: 0.35rem 1rem;
  border-radius: 999px;
  font-size: 0.85rem;
  cursor: pointer;
}

.tab-btn.active {
  background: #2563eb;
  color: #fff;
}

.locale-panel {
  background: rgba(15, 23, 42, 0.35);
  border-radius: 1rem;
  padding: 1.25rem;
  border: 1px solid rgba(148, 163, 184, 0.25);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.grid-2 {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
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

.seo-grid {
  display: grid;
  gap: 1rem;
}

@media (min-width: 768px) {
  .seo-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

.image-picker {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.image-chip {
  position: relative;
  width: 96px;
  height: 72px;
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
  border: none;
  background: rgba(15, 23, 42, 0.7);
  color: #fff;
  border-radius: 999px;
  width: 20px;
  height: 20px;
  cursor: pointer;
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
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
  .admin-blog {
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
