<template>
  <div class="blog-post-page">
    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="container">
        <div class="skeleton-header"></div>
        <div class="skeleton-image"></div>
        <div class="skeleton-content">
          <div class="skeleton-line"></div>
          <div class="skeleton-line"></div>
          <div class="skeleton-line short"></div>
        </div>
      </div>
    </div>

    <article v-else-if="post" class="post-article">
      <!-- Hero Header -->
      <header class="post-hero">
        <div class="hero-bg" :style="heroStyle"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
          <div class="container">
            <Breadcrumb :items="breadcrumbItems" />

            <div class="post-meta">
              <span v-if="post.category" class="category-tag">{{
                post.category.name
              }}</span>
              <span class="date">{{ formatDate(post.published_at) }}</span>
              <span class="read-time"
                >{{ readTime }} {{ $t("blog.min_read", "min read") }}</span
              >
            </div>

            <h1 class="post-title">{{ post.title }}</h1>

            <div class="author-info">
              <div class="author-avatar">
                {{ authorInitial }}
              </div>
              <div class="author-details">
                <p class="author-name">{{ post.author?.name || "Admin" }}</p>
                <p class="author-stats">
                  <span
                    >👁️ {{ post.view_count }}
                    {{ $t("blog.views", "views") }}</span
                  >
                </p>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <div class="post-content-wrapper">
        <div class="container">
          <div class="content-grid">
            <!-- Main Content -->
            <div class="main-content">
              <div class="prose" v-html="formatContent(post.content)"></div>

              <!-- Tags -->
              <div v-if="post.tags?.length" class="tags-section">
                <h4>{{ $t("blog.tags", "Tags") }}</h4>
                <div class="tags-list">
                  <span v-for="tag in post.tags" :key="tag" class="tag">
                    #{{ tag }}
                  </span>
                </div>
              </div>

              <!-- Share Section -->
              <div class="share-section">
                <h4>{{ $t("blog.share", "Share this article") }}</h4>
                <div class="share-buttons">
                  <button @click="shareOnFacebook" class="share-btn facebook">
                    <svg
                      width="20"
                      height="20"
                      viewBox="0 0 24 24"
                      fill="currentColor"
                    >
                      <path
                        d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"
                      />
                    </svg>
                    Facebook
                  </button>
                  <button @click="shareOnTwitter" class="share-btn twitter">
                    <svg
                      width="20"
                      height="20"
                      viewBox="0 0 24 24"
                      fill="currentColor"
                    >
                      <path
                        d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"
                      />
                    </svg>
                    Twitter
                  </button>
                  <button @click="copyLink" class="share-btn copy">
                    <svg
                      width="20"
                      height="20"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                    >
                      <rect x="9" y="9" width="13" height="13" rx="2" ry="2" />
                      <path
                        d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"
                      />
                    </svg>
                    {{
                      copied
                        ? $t("blog.copied", "Copied!")
                        : $t("blog.copy_link", "Copy Link")
                    }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Sidebar -->
            <aside class="sidebar">
              <div class="sidebar-card author-card">
                <div class="author-avatar large">{{ authorInitial }}</div>
                <h4>{{ post.author?.name || "Admin" }}</h4>
                <p>
                  {{
                    $t(
                      "blog.author_bio",
                      "Content creator and travel enthusiast sharing stories from around the world."
                    )
                  }}
                </p>
              </div>

              <div class="sidebar-card">
                <h4>{{ $t("blog.related_posts", "Related Posts") }}</h4>
                <div class="related-list">
                  <router-link
                    v-for="related in relatedPosts"
                    :key="related.id"
                    :to="`/blog/${related.slug}`"
                    class="related-item"
                  >
                    <img
                      :src="related.featured_image || '/placeholder.jpg'"
                      :alt="related.title"
                    />
                    <div>
                      <p class="related-title">{{ related.title }}</p>
                      <span class="related-date">{{
                        formatDate(related.published_at)
                      }}</span>
                    </div>
                  </router-link>
                </div>
              </div>
            </aside>
          </div>
        </div>
      </div>

      <!-- More Posts Section -->
      <section class="more-posts">
        <div class="container">
          <h2>{{ $t("blog.more_articles", "More Articles") }}</h2>
          <div class="posts-grid">
            <router-link
              v-for="post in morePosts"
              :key="post.id"
              :to="`/blog/${post.slug}`"
              class="post-card"
            >
              <div class="card-image">
                <img
                  :src="post.featured_image || '/placeholder.jpg'"
                  :alt="post.title"
                  loading="lazy"
                />
              </div>
              <div class="card-content">
                <span class="card-category">{{ post.category?.name }}</span>
                <h3>{{ post.title }}</h3>
                <p>{{ truncate(post.excerpt, 80) }}</p>
                <span class="card-date">{{
                  formatDate(post.published_at)
                }}</span>
              </div>
            </router-link>
          </div>
        </div>
      </section>
    </article>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import dayjs from "dayjs";
import Breadcrumb from "@/components/Breadcrumb.vue";

const route = useRoute();
const router = useRouter();
const { t } = useI18n();

const post = ref(null);
const relatedPosts = ref([]);
const morePosts = ref([]);
const loading = ref(true);
const copied = ref(false);

const breadcrumbItems = computed(() => [
  { label: t("nav.home"), path: "/" },
  { label: t("blog.title"), path: "/blog" },
  { label: post.value?.title || "Article", path: `/blog/${post.value?.slug}` },
]);

const heroStyle = computed(() => ({
  backgroundImage: post.value?.featured_image
    ? `url(${post.value.featured_image})`
    : "none",
}));

const authorInitial = computed(() => {
  const name = post.value?.author?.name || "A";
  return name.charAt(0).toUpperCase();
});

const readTime = computed(() => {
  if (!post.value?.content) return 1;
  const words = post.value.content.replace(/<[^>]+>/g, "").split(/\s+/).length;
  return Math.max(1, Math.ceil(words / 200));
});

const formatDate = (date) => dayjs(date).format("MMM DD, YYYY");

const formatContent = (content) => {
  if (!content) return "";
  return content.replace(/\n/g, "<br>");
};

const truncate = (text, length) => {
  if (!text) return "";
  return text.length > length ? text.substring(0, length) + "..." : text;
};

const shareOnFacebook = () => {
  const url = encodeURIComponent(window.location.href);
  window.open(
    `https://www.facebook.com/sharer/sharer.php?u=${url}`,
    "_blank",
    "width=600,height=400"
  );
};

const shareOnTwitter = () => {
  const url = encodeURIComponent(window.location.href);
  const text = encodeURIComponent(post.value?.title || "");
  window.open(
    `https://twitter.com/intent/tweet?url=${url}&text=${text}`,
    "_blank",
    "width=600,height=400"
  );
};

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(window.location.href);
    copied.value = true;
    setTimeout(() => {
      copied.value = false;
    }, 2000);
  } catch (e) {
    console.error("Failed to copy:", e);
  }
};

const loadPost = async () => {
  loading.value = true;
  try {
    const [postRes, postsRes] = await Promise.all([
      api.get(`/blog/posts/${route.params.slug}`),
      api.get("/blog/posts?per_page=6"),
    ]);

    post.value = postRes.data;
    const allPosts = postsRes.data.data.filter(
      (p) => p.slug !== post.value.slug
    );
    relatedPosts.value = allPosts.slice(0, 3);
    morePosts.value = allPosts.slice(0, 4);
  } catch (error) {
    console.error("Error loading post:", error);
    router.push("/blog");
  } finally {
    loading.value = false;
  }
};

onMounted(loadPost);
watch(() => route.params.slug, loadPost);
</script>

<style scoped>
/* Loading State */
.loading-container {
  padding: 4rem 0;
}

.skeleton-header {
  height: 300px;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 1rem;
  margin-bottom: 2rem;
}

.skeleton-image {
  height: 400px;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 1rem;
  margin-bottom: 2rem;
}

.skeleton-content .skeleton-line {
  height: 1rem;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 0.25rem;
  margin-bottom: 0.75rem;
}

.skeleton-line.short {
  width: 60%;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Container */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

/* Post Hero */
.post-hero {
  position: relative;
  padding: 8rem 0 4rem;
  min-height: 500px;
  display: flex;
  align-items: flex-end;
}

.hero-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  background-color: #1e293b;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to top,
    rgba(15, 23, 42, 0.95) 0%,
    rgba(15, 23, 42, 0.6) 50%,
    rgba(15, 23, 42, 0.3) 100%
  );
}

.hero-content {
  position: relative;
  z-index: 1;
  width: 100%;
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

.post-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1.5rem;
}

.category-tag {
  background: #7c3aed;
  color: #fff;
  padding: 0.375rem 0.875rem;
  border-radius: 2rem;
  font-size: 0.8rem;
  font-weight: 600;
}

.date,
.read-time {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
}

.post-title {
  font-size: 3rem;
  font-weight: 700;
  color: #fff;
  line-height: 1.2;
  margin-bottom: 2rem;
  max-width: 900px;
}

.author-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.author-avatar {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #7c3aed, #a78bfa);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 700;
  font-size: 1.25rem;
}

.author-avatar.large {
  width: 64px;
  height: 64px;
  font-size: 1.5rem;
  margin-bottom: 1rem;
}

.author-name {
  color: #fff;
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.author-stats {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.875rem;
}

/* Content Wrapper */
.post-content-wrapper {
  padding: 4rem 0;
  background: #fff;
}

.content-grid {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 3rem;
}

/* Main Content */
.main-content {
  max-width: 100%;
}

.prose {
  font-size: 1.125rem;
  line-height: 1.8;
  color: #334155;
}

.prose :deep(p) {
  margin-bottom: 1.5rem;
}

.prose :deep(h2) {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin: 2.5rem 0 1rem;
}

.prose :deep(h3) {
  font-size: 1.375rem;
  font-weight: 600;
  color: #1e293b;
  margin: 2rem 0 0.75rem;
}

.prose :deep(img) {
  max-width: 100%;
  border-radius: 0.75rem;
  margin: 1.5rem 0;
}

.prose :deep(blockquote) {
  border-left: 4px solid #7c3aed;
  padding-left: 1.5rem;
  margin: 1.5rem 0;
  font-style: italic;
  color: #64748b;
}

/* Tags Section */
.tags-section {
  margin-top: 3rem;
  padding-top: 2rem;
  border-top: 1px solid #e2e8f0;
}

.tags-section h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 1rem;
}

.tags-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tag {
  padding: 0.5rem 1rem;
  background: #f1f5f9;
  color: #7c3aed;
  border-radius: 2rem;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s;
}

.tag:hover {
  background: #ede9fe;
}

/* Share Section */
.share-section {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e2e8f0;
}

.share-section h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 1rem;
}

.share-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.share-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.share-btn.facebook {
  background: #1877f2;
  color: #fff;
}

.share-btn.facebook:hover {
  background: #166fe5;
}

.share-btn.twitter {
  background: #1da1f2;
  color: #fff;
}

.share-btn.twitter:hover {
  background: #1a91da;
}

.share-btn.copy {
  background: #f1f5f9;
  color: #475569;
}

.share-btn.copy:hover {
  background: #e2e8f0;
}

/* Sidebar */
.sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.sidebar-card {
  background: #f8fafc;
  border-radius: 1rem;
  padding: 1.5rem;
}

.author-card {
  text-align: center;
}

.author-card h4 {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.author-card p {
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.6;
}

.sidebar-card > h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 1rem;
}

.related-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.related-item {
  display: flex;
  gap: 0.75rem;
  text-decoration: none;
  transition: all 0.2s;
}

.related-item:hover .related-title {
  color: #7c3aed;
}

.related-item img {
  width: 80px;
  height: 60px;
  object-fit: cover;
  border-radius: 0.5rem;
  flex-shrink: 0;
}

.related-title {
  font-size: 0.9rem;
  font-weight: 500;
  color: #1e293b;
  line-height: 1.4;
  margin-bottom: 0.25rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.related-date {
  font-size: 0.75rem;
  color: #64748b;
}

/* More Posts Section */
.more-posts {
  padding: 4rem 0;
  background: #f8fafc;
}

.more-posts h2 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 2rem;
}

.posts-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

.post-card {
  background: #fff;
  border-radius: 1rem;
  overflow: hidden;
  text-decoration: none;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.post-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.post-card .card-image {
  height: 160px;
  overflow: hidden;
}

.post-card .card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.post-card:hover .card-image img {
  transform: scale(1.05);
}

.post-card .card-content {
  padding: 1rem;
}

.card-category {
  font-size: 0.75rem;
  color: #7c3aed;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.post-card h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0.5rem 0;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.post-card p {
  font-size: 0.85rem;
  color: #64748b;
  line-height: 1.5;
  margin-bottom: 0.75rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.card-date {
  font-size: 0.75rem;
  color: #94a3b8;
}

/* Responsive */
@media (max-width: 1024px) {
  .content-grid {
    grid-template-columns: 1fr;
  }

  .sidebar {
    flex-direction: row;
    flex-wrap: wrap;
  }

  .sidebar-card {
    flex: 1;
    min-width: 280px;
  }

  .posts-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .post-title {
    font-size: 2rem;
  }

  .post-hero {
    padding: 6rem 0 3rem;
    min-height: 400px;
  }

  .sidebar {
    flex-direction: column;
  }

  .posts-grid {
    grid-template-columns: 1fr;
  }

  .share-buttons {
    flex-direction: column;
  }

  .share-btn {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .post-hero {
    padding: 5rem 0 2.5rem;
  }

  .post-title {
    font-size: 1.75rem;
  }

  .sidebar-card {
    min-width: 100%;
  }
}
</style>
