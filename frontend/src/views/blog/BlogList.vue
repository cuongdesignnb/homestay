<template>
  <div class="blog-page">
    <!-- Hero Section -->
    <section class="page-hero">
      <div class="hero-bg"></div>
      <div class="hero-content">
        <div class="container">
          <Breadcrumb :items="breadcrumbItems" />
          <p class="hero-eyebrow">
            {{ $t("home.blog_tag", "Stories & Tips") }}
          </p>
          <h1 class="hero-title">{{ $t("blog.title") }}</h1>
          <p class="hero-subtitle">
            {{
              $t(
                "blog.subtitle",
                "Discover travel tips, local stories, and inspiration for your next adventure"
              )
            }}
          </p>
        </div>
      </div>
    </section>

    <div class="container main-content">
      <div v-if="loading" class="spinner"></div>
      <div v-else class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Blog Posts -->
        <div class="md:col-span-3">
          <div v-if="posts.length === 0" class="text-center py-12">
            <p class="text-gray-500">No blog posts available</p>
          </div>
          <div v-else class="space-y-6">
            <div
              v-for="post in posts"
              :key="post.id"
              class="card hover:shadow-lg transition"
            >
              <div class="md:flex">
                <img
                  :src="post.featured_image || '/placeholder.jpg'"
                  :alt="post.title"
                  class="w-full md:w-64 h-48 object-cover"
                />
                <div class="p-6 flex-1">
                  <div class="text-sm text-gray-500 mb-2">
                    {{ formatDate(post.published_at) }} •
                    {{ post.category?.name }}
                  </div>
                  <h2 class="text-2xl font-semibold mb-3">{{ post.title }}</h2>
                  <p class="text-gray-600 mb-4 line-clamp-3">
                    {{ post.excerpt }}
                  </p>
                  <router-link
                    :to="`/blog/${post.slug}`"
                    class="text-blue-600 hover:underline font-medium"
                  >
                    {{ $t("blog.read_more") }} →
                  </router-link>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div
            v-if="pagination.last_page > 1"
            class="flex justify-center mt-8 gap-2"
          >
            <button
              @click="loadPosts(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-4 py-2 border rounded hover:bg-gray-100 disabled:opacity-50"
            >
              {{ $t("common.previous") }}
            </button>
            <span class="px-4 py-2">
              {{ pagination.current_page }} / {{ pagination.last_page }}
            </span>
            <button
              @click="loadPosts(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-4 py-2 border rounded hover:bg-gray-100 disabled:opacity-50"
            >
              {{ $t("common.next") }}
            </button>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="md:col-span-1">
          <!-- Categories -->
          <div class="card p-6 mb-6">
            <h3 class="text-xl font-semibold mb-4">
              {{ $t("blog.categories") }}
            </h3>
            <ul class="space-y-2">
              <li v-for="category in categories" :key="category.id">
                <button
                  @click="filterByCategory(category.slug)"
                  class="text-blue-600 hover:underline"
                >
                  {{ category.name }} ({{ category.posts_count }})
                </button>
              </li>
            </ul>
          </div>

          <!-- Recent Posts -->
          <div class="card p-6">
            <h3 class="text-xl font-semibold mb-4">
              {{ $t("blog.recent_posts") }}
            </h3>
            <ul class="space-y-3">
              <li v-for="post in recentPosts" :key="post.id">
                <router-link
                  :to="`/blog/${post.slug}`"
                  class="text-blue-600 hover:underline text-sm"
                >
                  {{ post.title }}
                </router-link>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import dayjs from "dayjs";
import Breadcrumb from "@/components/Breadcrumb.vue";

const { t } = useI18n();

const posts = ref([]);
const categories = ref([]);
const recentPosts = ref([]);
const loading = ref(true);
const pagination = ref({ current_page: 1, last_page: 1 });

const breadcrumbItems = computed(() => [
  { label: t("nav.home"), path: "/" },
  { label: t("blog.title"), path: "/blog" },
]);

const formatDate = (date) => {
  return dayjs(date).format("MMM DD, YYYY");
};

const loadPosts = async (page = 1) => {
  loading.value = true;
  try {
    const response = await api.get("/blog/posts", { params: { page } });
    posts.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
    };
  } catch (error) {
    console.error("Error loading posts:", error);
  } finally {
    loading.value = false;
  }
};

const filterByCategory = async (slug) => {
  loading.value = true;
  try {
    const response = await api.get("/blog/posts", {
      params: { category: slug },
    });
    posts.value = response.data.data;
  } catch (error) {
    console.error("Error filtering posts:", error);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  try {
    const [postsRes, categoriesRes] = await Promise.all([
      api.get("/blog/posts"),
      api.get("/blog/categories"),
    ]);

    posts.value = postsRes.data.data;
    pagination.value = {
      current_page: postsRes.data.current_page,
      last_page: postsRes.data.last_page,
    };
    categories.value = categoriesRes.data;
    recentPosts.value = postsRes.data.data.slice(0, 5);
  } catch (error) {
    console.error("Error loading blog data:", error);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
/* Page Hero */
.page-hero {
  position: relative;
  padding: 6rem 0 4rem;
  margin-bottom: 2rem;
  overflow: hidden;
}

.hero-bg {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 50%, #a78bfa 100%);
  z-index: 0;
}

.hero-bg::before {
  content: "";
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.hero-content {
  position: relative;
  z-index: 1;
}

.hero-content .container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.hero-content :deep(.breadcrumb-link) {
  color: rgba(255, 255, 255, 0.7);
}

.hero-content :deep(.breadcrumb-link:hover) {
  color: #fff;
  background: rgba(255, 255, 255, 0.1);
}

.hero-content :deep(.breadcrumb-current) {
  color: #fff;
}

.hero-content :deep(.breadcrumb-separator) {
  color: rgba(255, 255, 255, 0.4);
}

.hero-eyebrow {
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  color: #ddd6fe;
  font-weight: 600;
  margin-bottom: 0.75rem;
}

.hero-title {
  font-size: 3rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 1rem;
  line-height: 1.2;
}

.hero-subtitle {
  font-size: 1.125rem;
  color: rgba(255, 255, 255, 0.8);
  max-width: 600px;
}

.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.main-content {
  padding-bottom: 4rem;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 2rem;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 1rem;
  }

  .hero-subtitle {
    font-size: 1rem;
  }

  .posts-grid {
    gap: 1rem;
  }
}
</style>
