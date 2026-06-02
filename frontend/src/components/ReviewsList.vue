<template>
  <div class="reviews-list-section">
    <div class="section-header">
      <div>
        <h3>{{ $t("reviews.guest_reviews", "Đánh giá từ khách") }}</h3>
        <p v-if="stats" class="stats">
          <span class="avg-rating"
            >⭐ {{ stats.average?.toFixed(1) || "N/A" }}</span
          >
          <span class="count"
            >{{ stats.total || 0 }}
            {{ $t("reviews.reviews", "đánh giá") }}</span
          >
        </p>
      </div>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="skeleton" v-for="i in 3" :key="i"></div>
    </div>

    <div v-else-if="!reviews.length" class="empty-state">
      <p>
        {{
          $t(
            "reviews.no_reviews",
            "Chưa có đánh giá nào. Hãy là người đầu tiên!"
          )
        }}
      </p>
    </div>

    <div v-else class="reviews-grid">
      <article v-for="review in reviews" :key="review.id" class="review-card">
        <header class="review-header">
          <div class="reviewer-info">
            <div class="avatar">
              {{ review.guest_name.charAt(0).toUpperCase() }}
            </div>
            <div>
              <h4>{{ review.guest_name }}</h4>
              <time>{{
                formatDate(review.approved_at || review.created_at)
              }}</time>
            </div>
          </div>
          <div class="rating-badge">
            <span class="stars"
              >{{ "★".repeat(review.rating)
              }}{{ "☆".repeat(5 - review.rating) }}</span
            >
          </div>
        </header>

        <p class="review-content">{{ review.content }}</p>

        <div v-if="review.images?.length" class="review-images">
          <img
            v-for="(img, idx) in review.images"
            :key="idx"
            :src="img"
            :alt="`Review image ${idx + 1}`"
            @click="openLightbox(review.images, idx)"
          />
        </div>
      </article>
    </div>

    <div v-if="hasMore" class="load-more">
      <button @click="loadMore" :disabled="loadingMore">
        {{
          loadingMore
            ? $t("common.loading")
            : $t("reviews.load_more", "Xem thêm đánh giá")
        }}
      </button>
    </div>

    <!-- Lightbox -->
    <teleport to="body">
      <div v-if="lightbox.open" class="lightbox-overlay" @click="closeLightbox">
        <button class="lightbox-close" @click="closeLightbox">×</button>
        <button
          v-if="lightbox.images.length > 1"
          class="lightbox-nav prev"
          @click.stop="prevImage"
        >
          ‹
        </button>
        <img :src="lightbox.images[lightbox.index]" @click.stop />
        <button
          v-if="lightbox.images.length > 1"
          class="lightbox-nav next"
          @click.stop="nextImage"
        >
          ›
        </button>
      </div>
    </teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import api from "@/services/api";
import dayjs from "dayjs";

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (v) => ["room", "tour"].includes(v),
  },
  itemId: {
    type: [Number, String],
    required: true,
  },
});

const reviews = ref([]);
const loading = ref(true);
const loadingMore = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);
const stats = ref(null);

const hasMore = computed(() => currentPage.value < totalPages.value);

const lightbox = ref({
  open: false,
  images: [],
  index: 0,
});

const loadReviews = async (page = 1) => {
  if (page === 1) {
    loading.value = true;
  } else {
    loadingMore.value = true;
  }

  try {
    const endpoint =
      props.type === "room"
        ? `/rooms/${props.itemId}/guest-reviews`
        : `/tours/${props.itemId}/guest-reviews`;

    const { data } = await api.get(endpoint, {
      params: { page, per_page: 10 },
    });

    if (page === 1) {
      reviews.value = data.data;
    } else {
      reviews.value.push(...data.data);
    }

    currentPage.value = data.current_page;
    totalPages.value = data.last_page;

    // Calculate stats
    if (reviews.value.length) {
      const total = data.total;
      const avgRating =
        reviews.value.reduce((sum, r) => sum + r.rating, 0) /
        reviews.value.length;
      stats.value = { total, average: avgRating };
    }
  } catch (error) {
    console.error("Failed to load reviews:", error);
  } finally {
    loading.value = false;
    loadingMore.value = false;
  }
};

const loadMore = () => {
  if (hasMore.value && !loadingMore.value) {
    loadReviews(currentPage.value + 1);
  }
};

const formatDate = (date) => {
  return date ? dayjs(date).format("DD/MM/YYYY") : "";
};

const openLightbox = (images, index) => {
  lightbox.value = { open: true, images, index };
};

const closeLightbox = () => {
  lightbox.value.open = false;
};

const prevImage = () => {
  lightbox.value.index =
    (lightbox.value.index - 1 + lightbox.value.images.length) %
    lightbox.value.images.length;
};

const nextImage = () => {
  lightbox.value.index =
    (lightbox.value.index + 1) % lightbox.value.images.length;
};

const refresh = () => {
  currentPage.value = 1;
  loadReviews(1);
};

defineExpose({ refresh });

watch(
  () => props.itemId,
  () => {
    refresh();
  }
);

onMounted(() => {
  loadReviews();
});
</script>

<style scoped>
.reviews-list-section {
  background: white;
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-header h3 {
  font-size: 1.4rem;
  color: #0f172a;
  margin: 0;
}

.stats {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 0.5rem;
}

.avg-rating {
  font-size: 1.1rem;
  font-weight: 600;
  color: #f59e0b;
}

.count {
  color: #64748b;
}

.loading-state {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.skeleton {
  height: 120px;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 1rem;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  color: #64748b;
}

.reviews-grid {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.review-card {
  border: 1px solid #e2e8f0;
  border-radius: 1rem;
  padding: 1.25rem;
  transition: all 0.2s ease;
}

.review-card:hover {
  border-color: #cbd5e1;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.reviewer-info {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 1.1rem;
}

.reviewer-info h4 {
  margin: 0;
  font-size: 1rem;
  color: #0f172a;
}

.reviewer-info time {
  font-size: 0.85rem;
  color: #94a3b8;
}

.rating-badge .stars {
  color: #fbbf24;
  font-size: 1rem;
  letter-spacing: 1px;
}

.review-content {
  color: #475569;
  line-height: 1.7;
  margin: 0;
}

.review-images {
  display: flex;
  gap: 0.5rem;
  margin-top: 1rem;
  flex-wrap: wrap;
}

.review-images img {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.review-images img:hover {
  transform: scale(1.05);
}

.load-more {
  text-align: center;
  margin-top: 1.5rem;
}

.load-more button {
  padding: 0.75rem 2rem;
  background: #f1f5f9;
  color: #475569;
  border: none;
  border-radius: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.load-more button:hover:not(:disabled) {
  background: #e2e8f0;
}

.load-more button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Lightbox */
.lightbox-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.lightbox-overlay img {
  max-width: 90vw;
  max-height: 90vh;
  object-fit: contain;
  border-radius: 0.5rem;
}

.lightbox-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 44px;
  height: 44px;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: none;
  border-radius: 50%;
  font-size: 2rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.lightbox-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 50px;
  height: 50px;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: none;
  border-radius: 50%;
  font-size: 2rem;
  cursor: pointer;
}

.lightbox-nav.prev {
  left: 1rem;
}

.lightbox-nav.next {
  right: 1rem;
}
</style>
