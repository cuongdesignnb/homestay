<template>
  <div class="tours-page">
    <!-- Hero Section -->
    <section class="page-hero">
      <div class="hero-bg"></div>
      <div class="hero-content">
        <div class="container">
          <Breadcrumb :items="breadcrumbItems" />
          <p class="hero-eyebrow">
            {{ $t("home.tour_tag", "Iconic Journeys") }}
          </p>
          <h1 class="hero-title">{{ $t("tours.title") }}</h1>
          <p class="hero-subtitle">
            {{
              $t(
                "tours.subtitle",
                "Explore curated adventures and create unforgettable memories"
              )
            }}
          </p>
        </div>
      </div>
    </section>

    <div class="container">
      <!-- Filter Section -->
      <section class="filter-section">
        <div class="filter-card">
          <div class="filter-grid">
            <div class="filter-field">
              <label>
                <span class="field-icon">📍</span>
                {{ $t("tours.destination", "Điểm đến") }}
              </label>
              <select v-model="filters.destination_type" @change="loadTours()">
                <option value="">{{ $t("common.all", "Tất cả") }}</option>
                <option value="jungle">🌿 {{ $t("tours.dest_jungle", "Jungle (Rừng)") }}</option>
                <option value="sea">🌊 {{ $t("tours.dest_sea", "Sea (Biển)") }}</option>
                <option value="fusion">🏝️ {{ $t("tours.dest_fusion", "Fusion (Rừng & Biển)") }}</option>
                <option value="historical_culture">🏛️ {{ $t("tours.dest_historical", "Historical & Cultural (Lịch sử & Văn hóa)") }}</option>
                <option value="experience">✨ {{ $t("tours.dest_experience", "Experience (Trải nghiệm)") }}</option>
              </select>
              <input
                v-model="filters.destination"
                type="text"
                class="destination-search-input"
                :placeholder="$t('tours.search_destination', 'Hoặc nhập tìm kiếm...')"
                @input="debouncedSearch"
              />
            </div>
            <div class="filter-field">
              <label>
                <span class="field-icon">⏱️</span>
                {{ $t("tours.duration") }}
              </label>
              <select v-model="filters.duration" @change="loadTours()">
                <option value="">{{ $t("common.all", "All") }}</option>
                <optgroup :label="$t('tours.hours', 'Hours')">
                  <option value="hours:1-3">1-3 {{ $t("tours.hours", "hours") }}</option>
                  <option value="hours:4-6">4-6 {{ $t("tours.hours", "hours") }}</option>
                  <option value="hours:7+">7+ {{ $t("tours.hours", "hours") }}</option>
                </optgroup>
                <optgroup :label="$t('tours.days', 'Days')">
                  <option value="days:1">1 {{ $t("tours.day", "day") }}</option>
                  <option value="days:1-3">1-3 {{ $t("tours.days") }}</option>
                  <option value="days:4-7">4-7 {{ $t("tours.days") }}</option>
                  <option value="days:8+">8+ {{ $t("tours.days") }}</option>
                </optgroup>
              </select>
            </div>
            <div class="filter-field">
              <label>
                <span class="field-icon">💪</span>
                {{ $t("tours.difficulty") }}
              </label>
              <select v-model="filters.difficulty" @change="loadTours()">
                <option value="">{{ $t("common.all", "All") }}</option>
                <option value="easy">🟢 {{ $t("tours.easy", "Easy") }}</option>
                <option value="moderate">
                  🟡 {{ $t("tours.moderate", "Moderate") }}
                </option>
                <option value="challenging">
                  🟠 {{ $t("tours.challenging", "Challenging") }}
                </option>
                <option value="difficult">
                  🔴 {{ $t("tours.difficult", "Difficult") }}
                </option>
              </select>
            </div>
            <div class="filter-field">
              <label>
                <span class="field-icon">💰</span>
                {{ $t("tours.price_range", "Price Range") }}
              </label>
              <select v-model="filters.price_range" @change="loadTours()">
                <option value="">{{ $t("common.all", "All") }}</option>
                <option value="budget">
                  {{ $t("tours.budget", "Budget") }} (&lt; 500K)
                </option>
                <option value="mid">
                  {{ $t("tours.mid_range", "Mid-range") }} (500K - 2M)
                </option>
                <option value="luxury">
                  {{ $t("tours.luxury", "Luxury") }} (&gt; 2M)
                </option>
              </select>
            </div>
            <button @click="loadTours" class="search-btn">
              <span class="btn-icon">🔍</span>
              {{ $t("common.search") }}
            </button>
          </div>
          <!-- Active Filters -->
          <div v-if="hasActiveFilters" class="active-filters">
            <span
              v-if="filters.destination_type"
              class="filter-chip"
              @click="filters.destination_type = ''; loadTours()"
            >
              📍 {{ destinationTypeLabel(filters.destination_type) }} ✕
            </span>
            <span
              v-if="filters.destination"
              class="filter-chip"
              @click="filters.destination = ''; loadTours()"
            >
              🔍 {{ filters.destination }} ✕
            </span>
            <span
              v-if="filters.duration"
              class="filter-chip"
              @click="filters.duration = ''; loadTours()"
            >
              ⏱️ {{ filters.duration.includes(':') ? filters.duration.split(':')[1] : filters.duration }} {{ filters.duration.startsWith('hours') ? $t("tours.hours", "hours") : $t("tours.days") }} ✕
            </span>
            <span
              v-if="filters.difficulty"
              class="filter-chip"
              @click="filters.difficulty = ''; loadTours()"
            >
              💪 {{ filters.difficulty }} ✕
            </span>
            <span
              v-if="filters.price_range"
              class="filter-chip"
              @click="filters.price_range = ''; loadTours()"
            >
              💰 {{ filters.price_range }} ✕
            </span>
            <button class="clear-all-btn" @click="resetFilters">
              {{ $t("common.clear_all", "Clear all") }}
            </button>
          </div>
        </div>
      </section>

      <!-- Results Section -->
      <section class="results-section">
        <div class="results-header">
          <div class="results-info">
            <h2>{{ $t("tours.available", "Available Tours") }}</h2>
            <p v-if="!loading">
              {{ tours.length }} {{ $t("tours.found", "tours found") }}
            </p>
          </div>
          <div class="view-toggles">
            <button
              class="view-btn"
              :class="{ active: viewMode === 'grid' }"
              @click="viewMode = 'grid'"
            >
              <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <rect x="3" y="3" width="7" height="7" />
                <rect x="14" y="3" width="7" height="7" />
                <rect x="3" y="14" width="7" height="7" />
                <rect x="14" y="14" width="7" height="7" />
              </svg>
            </button>
            <button
              class="view-btn"
              :class="{ active: viewMode === 'list' }"
              @click="viewMode = 'list'"
            >
              <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <line x1="3" y1="6" x2="21" y2="6" />
                <line x1="3" y1="12" x2="21" y2="12" />
                <line x1="3" y1="18" x2="21" y2="18" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="loading-grid">
          <div v-for="n in 6" :key="n" class="skeleton-card">
            <div class="skeleton-image"></div>
            <div class="skeleton-content">
              <div class="skeleton-line title"></div>
              <div class="skeleton-line"></div>
              <div class="skeleton-line short"></div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="tours.length === 0" class="empty-state">
          <div class="empty-icon">🏔️</div>
          <h3>{{ $t("tours.no_tours", "No tours available") }}</h3>
          <p>{{ $t("tours.try_different", "Try adjusting your filters") }}</p>
          <button @click="resetFilters" class="reset-btn">
            {{ $t("common.reset", "Reset Filters") }}
          </button>
        </div>

        <!-- Grid View -->
        <div v-else-if="viewMode === 'grid'" class="tours-grid">
          <article v-for="tour in tours" :key="tour.id" class="tour-card">
            <div class="card-image">
              <img
                :src="
                  tour.cover_image || tour.images?.[0] || '/placeholder.jpg'
                "
                :alt="tour.name"
                loading="lazy"
              />
              <div class="card-overlay">
                <span class="duration-badge">
                  <svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 6v6l4 2" />
                  </svg>
                  {{ tour.duration }}
                  {{ tour.duration_unit || $t("tours.days") }}
                </span>
              </div>
              <div class="card-badges">
                <span v-if="tour.discount_price" class="badge sale">
                  -{{
                    Math.round(
                      (1 - tour.discount_price / tour.price_per_person) * 100
                    )
                  }}%
                </span>
                <span v-if="tour.is_featured" class="badge featured"
                  >⭐ Featured</span
                >
              </div>
              <button
                class="wishlist-btn"
                @click.prevent="toggleWishlist(tour)"
              >
                <svg
                  width="20"
                  height="20"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path
                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                  />
                </svg>
              </button>
            </div>

            <div class="card-content">
              <div class="card-header">
                <span v-if="tour.tour_category?.name" class="category-tag">{{
                  tour.tour_category.name
                }}</span>
                <div class="rating">
                  <span class="star">⭐</span>
                  <span class="score">{{
                    tour.average_rating?.toFixed(1) || "New"
                  }}</span>
                  <span class="count">({{ tour.reviews_count || 0 }})</span>
                </div>
              </div>

              <h3 class="card-title">
                <router-link :to="`/tours/${tour.id}`">{{
                  tour.name
                }}</router-link>
              </h3>

              <p class="card-description">
                {{ truncate(tour.description, 80) }}
              </p>

              <div class="card-meta">
                <div class="meta-item">
                  <svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                    <circle cx="12" cy="10" r="3" />
                  </svg>
                  <span>{{ tour.departure_location || "N/A" }}</span>
                </div>
                <div class="meta-item">
                  <svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                  </svg>
                  <span
                    >{{ tour.min_participants }}-{{
                      tour.max_participants
                    }}</span
                  >
                </div>
                <div
                  class="meta-item difficulty"
                  :class="tour.difficulty_level"
                >
                  <span class="difficulty-dot"></span>
                  <span>{{ tour.difficulty_level }}</span>
                </div>
              </div>

              <div class="card-footer">
                <div class="price-info">
                  <!-- If it's from dynamic pricing -->
                  <template v-if="tour.display_price_type === 'from'">
                    <span class="price-prefix">{{ $t("tours.from") }} </span>
                    <span class="current-price">{{ formatPrice(tour.display_price) }}</span>
                  </template>
                  <!-- If it is flat rate pricing -->
                  <template v-else-if="tour.display_price_type === 'flat'">
                    <span class="current-price">{{ formatPrice(tour.display_price) }}</span>
                    <span class="price-unit">/{{ $t("tours.flat") }}</span>
                  </template>
                  <!-- Standard per person or fallback -->
                  <template v-else>
                    <span v-if="tour.discount_price" class="original-price">
                      {{ formatPrice(tour.price_per_person) }}
                    </span>
                    <span class="current-price">
                      {{ formatPrice(tour.display_price || tour.discount_price || tour.price_per_person) }}
                    </span>
                    <span class="price-unit">/{{ $t("tours.per_person") }}</span>
                  </template>
                </div>
                <router-link :to="`/tours/${tour.id}`" class="book-btn">
                  {{ $t("tours.book_tour") }}
                  <svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path d="M5 12h14M12 5l7 7-7 7" />
                  </svg>
                </router-link>
              </div>
            </div>
          </article>
        </div>

        <!-- List View -->
        <div v-else class="tours-list">
          <article v-for="tour in tours" :key="tour.id" class="tour-list-item">
            <div class="list-image">
              <img
                :src="
                  tour.cover_image || tour.images?.[0] || '/placeholder.jpg'
                "
                :alt="tour.name"
                loading="lazy"
              />
              <span class="duration-badge">
                ⏱️ {{ tour.duration }}
                {{ tour.duration_unit || $t("tours.days") }}
              </span>
              <div class="list-badges">
                <span v-if="tour.discount_price" class="badge sale">
                  -{{
                    Math.round(
                      (1 - tour.discount_price / tour.price_per_person) * 100
                    )
                  }}%
                </span>
              </div>
            </div>

            <div class="list-content">
              <div class="list-header">
                <div>
                  <span v-if="tour.tour_category?.name" class="category-tag">{{
                    tour.tour_category.name
                  }}</span>
                  <h3>
                    <router-link :to="`/tours/${tour.id}`">{{
                      tour.name
                    }}</router-link>
                  </h3>
                </div>
                <div class="rating">
                  <span class="star">⭐</span>
                  <span class="score">{{
                    tour.average_rating?.toFixed(1) || "New"
                  }}</span>
                  <span class="count">({{ tour.reviews_count || 0 }})</span>
                </div>
              </div>

              <p class="list-description">
                {{ truncate(tour.description, 150) }}
              </p>

              <div class="list-meta">
                <span>📍 {{ tour.departure_location }}</span>
                <span
                  >👥 {{ tour.min_participants }}-{{ tour.max_participants }}
                  {{ $t("tours.people", "people") }}</span
                >
                <span class="difficulty" :class="tour.difficulty_level">
                  <span class="difficulty-dot"></span>
                  {{ tour.difficulty_level }}
                </span>
              </div>

              <div v-if="tour.includes?.length" class="list-includes">
                <span
                  v-for="item in tour.includes.slice(0, 3)"
                  :key="item"
                  class="include-chip"
                >
                  ✓ {{ item }}
                </span>
                <span v-if="tour.includes.length > 3" class="more-includes">
                  +{{ tour.includes.length - 3 }}
                </span>
              </div>
            </div>

            <div class="list-action">
              <div class="price-block">
                <!-- If it's from dynamic pricing -->
                <template v-if="tour.display_price_type === 'from'">
                  <span class="prefix">{{ $t("tours.from") }} </span>
                  <span class="current">{{ formatPrice(tour.display_price) }}</span>
                </template>
                <!-- If it is flat rate pricing -->
                <template v-else-if="tour.display_price_type === 'flat'">
                  <span class="current">{{ formatPrice(tour.display_price) }}</span>
                  <span class="unit">/{{ $t("tours.flat") }}</span>
                </template>
                <!-- Standard per person or fallback -->
                <template v-else>
                  <span v-if="tour.discount_price" class="original">
                    {{ formatPrice(tour.price_per_person) }}
                  </span>
                  <span class="current">
                    {{ formatPrice(tour.display_price || tour.discount_price || tour.price_per_person) }}
                  </span>
                  <span class="unit">/{{ $t("tours.per_person") }}</span>
                </template>
              </div>
              <router-link :to="`/tours/${tour.id}`" class="action-btn">
                {{ $t("tours.book_tour") }}
              </router-link>
            </div>
          </article>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="pagination">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="page-btn prev"
          >
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M15 18l-6-6 6-6" />
            </svg>
            {{ $t("common.previous") }}
          </button>

          <div class="page-numbers">
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="changePage(page)"
              class="page-num"
              :class="{ active: page === pagination.current_page }"
            >
              {{ page }}
            </button>
          </div>

          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="page-btn next"
          >
            {{ $t("common.next") }}
            <svg
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M9 18l6-6-6-6" />
            </svg>
          </button>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import Breadcrumb from "@/components/Breadcrumb.vue";

const { locale, t } = useI18n();

const tours = ref([]);
const loading = ref(true);
const viewMode = ref("grid");
const filters = ref({
  destination_type: "",
  destination: "",
  duration: "",
  difficulty: "",
  price_range: "",
});

const destinationTypeOptions = {
  jungle: 'Jungle (Rừng)',
  sea: 'Sea (Biển)',
  fusion: 'Fusion (Rừng & Biển)',
  historical_culture: 'Historical & Cultural (Lịch sử & Văn hóa)',
  experience: 'Experience (Trải nghiệm)',
};

const destinationTypeLabel = (value) => {
  return destinationTypeOptions[value] || value;
};
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

const breadcrumbItems = computed(() => [
  { label: t("nav.home"), path: "/" },
  { label: t("tours.title"), path: "/tours" },
]);

const visiblePages = computed(() => {
  const pages = [];
  const total = pagination.value.last_page;
  const current = pagination.value.current_page;

  let start = Math.max(1, current - 2);
  let end = Math.min(total, current + 2);

  if (end - start < 4) {
    if (start === 1) end = Math.min(5, total);
    else start = Math.max(1, total - 4);
  }

  for (let i = start; i <= end; i++) pages.push(i);
  return pages;
});

const formatPrice = (price) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(price);
};

const stripHtml = (html) => {
  if (!html) return "";
  return html
    .replace(/<[^>]*>/g, "")
    .replace(/&nbsp;/g, " ")
    .trim();
};

const truncate = (text, length) => {
  if (!text) return "";
  const stripped = stripHtml(text);
  return stripped.length > length
    ? stripped.substring(0, length) + "..."
    : stripped;
};

const toggleWishlist = (tour) => {
  console.log("Toggle wishlist:", tour.id);
};

let debounceTimer = null;
const debouncedSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => loadTours(), 400);
};

const hasActiveFilters = computed(() => {
  return (
    filters.value.destination_type ||
    filters.value.destination ||
    filters.value.duration ||
    filters.value.difficulty ||
    filters.value.price_range
  );
});

const loadTours = async (page = 1) => {
  loading.value = true;
  try {
    // Only send non-empty filter values
    const params = { page };
    Object.entries(filters.value).forEach(([key, val]) => {
      if (val) params[key] = val;
    });
    const response = await api.get("/tours", { params });
    tours.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page || 1,
      last_page: response.data.last_page || 1,
      total: response.data.total || 0,
    };
  } catch (error) {
    console.error("Error loading tours:", error);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadTours(page);
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

const resetFilters = () => {
  filters.value = {
    destination_type: "",
    destination: "",
    duration: "",
    difficulty: "",
    price_range: "",
  };
  loadTours();
};

onMounted(() => {
  loadTours();
});

watch(locale, () => {
  loadTours();
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
  background: linear-gradient(135deg, #065f46 0%, #047857 50%, #059669 100%);
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

.hero-content :deep(.breadcrumb-nav) {
  margin-bottom: 1.5rem;
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
  color: #a7f3d0;
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

/* Container */
.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

/* Filter Section */
.filter-section {
  margin-top: -3rem;
  margin-bottom: 3rem;
  position: relative;
  z-index: 10;
}

.filter-card {
  background: #fff;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr) auto;
  gap: 1rem;
  align-items: end;
}

.filter-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-field label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.field-icon {
  font-size: 1rem;
}

.destination-search-input {
  margin-top: 0.375rem;
  font-size: 0.85rem !important;
  padding: 0.625rem 0.75rem !important;
  background: #f8fafc;
  border: 1px dashed #cbd5e1 !important;
}

.destination-search-input::placeholder {
  color: #94a3b8;
  font-style: italic;
}

.filter-field input,
.filter-field select {
  padding: 0.875rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.2s;
}

.filter-field input:focus,
.filter-field select:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.search-btn {
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #10b981, #059669);
  color: #fff;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
  white-space: nowrap;
}

.search-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

/* Active Filters */
.active-filters {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.filter-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.4rem 0.85rem;
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.1));
  color: #047857;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  text-transform: capitalize;
}

.filter-chip:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.clear-all-btn {
  padding: 0.35rem 0.75rem;
  background: transparent;
  color: #64748b;
  border: 1px solid #cbd5e1;
  border-radius: 999px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
}

.clear-all-btn:hover {
  background: #fee2e2;
  color: #dc2626;
  border-color: #fca5a5;
}

/* Results Section */
.results-section {
  padding-bottom: 4rem;
}

.results-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.results-info h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.25rem;
}

.results-info p {
  color: #64748b;
  font-size: 0.9rem;
}

.view-toggles {
  display: flex;
  gap: 0.5rem;
  background: #f1f5f9;
  padding: 0.25rem;
  border-radius: 0.5rem;
}

.view-btn {
  padding: 0.5rem;
  border: none;
  background: transparent;
  border-radius: 0.375rem;
  cursor: pointer;
  color: #64748b;
  transition: all 0.2s;
}

.view-btn.active {
  background: #fff;
  color: #10b981;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Loading State */
.loading-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.skeleton-card {
  background: #fff;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.skeleton-image {
  height: 220px;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-content {
  padding: 1.25rem;
}

.skeleton-line {
  height: 1rem;
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 0.25rem;
  margin-bottom: 0.75rem;
}

.skeleton-line.title {
  height: 1.25rem;
  width: 70%;
}

.skeleton-line.short {
  width: 40%;
}

@keyframes shimmer {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: #f8fafc;
  border-radius: 1rem;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.5rem;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #64748b;
  margin-bottom: 1.5rem;
}

.reset-btn {
  padding: 0.75rem 1.5rem;
  background: #10b981;
  color: #fff;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.reset-btn:hover {
  background: #059669;
}

/* Tour Grid */
.tours-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.tour-card {
  background: #fff;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.tour-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.card-image {
  position: relative;
  height: 220px;
  overflow: hidden;
}

.card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s ease;
}

.tour-card:hover .card-image img {
  transform: scale(1.05);
}

.card-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 1rem;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.6), transparent);
}

.duration-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  background: rgba(255, 255, 255, 0.95);
  color: #1e293b;
  border-radius: 2rem;
  font-size: 0.8rem;
  font-weight: 600;
}

.card-badges {
  position: absolute;
  top: 1rem;
  left: 1rem;
  display: flex;
  gap: 0.5rem;
}

.badge {
  padding: 0.375rem 0.75rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge.sale {
  background: #ef4444;
  color: #fff;
}

.badge.featured {
  background: #f59e0b;
  color: #fff;
}

.wishlist-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 36px;
  height: 36px;
  background: rgba(255, 255, 255, 0.9);
  border: none;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  color: #64748b;
}

.wishlist-btn:hover {
  background: #fff;
  color: #ef4444;
  transform: scale(1.1);
}

.card-content {
  padding: 1.25rem;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.category-tag {
  font-size: 0.75rem;
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-weight: 500;
}

.rating {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.875rem;
}

.rating .star {
  font-size: 1rem;
}

.rating .score {
  font-weight: 600;
  color: #1e293b;
}

.rating .count {
  color: #64748b;
}

.card-title {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.card-title a {
  color: #1e293b;
  text-decoration: none;
  transition: color 0.2s;
}

.card-title a:hover {
  color: #10b981;
}

.card-description {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.5;
  margin-bottom: 1rem;
}

.card-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.8rem;
  color: #64748b;
}

.meta-item svg {
  color: #10b981;
}

.meta-item.difficulty {
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.difficulty-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.difficulty.easy .difficulty-dot {
  background: #22c55e;
}
.difficulty.moderate .difficulty-dot {
  background: #f59e0b;
}
.difficulty.challenging .difficulty-dot {
  background: #ef4444;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #f1f5f9;
}

.price-info {
  display: flex;
  align-items: baseline;
  gap: 0.5rem;
}

.original-price {
  font-size: 0.875rem;
  color: #94a3b8;
  text-decoration: line-through;
}

.current-price {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}

.price-unit {
  font-size: 0.8rem;
  color: #64748b;
}

.book-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1rem;
  background: linear-gradient(135deg, #10b981, #059669);
  color: #fff;
  text-decoration: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.2s;
}

.book-btn:hover {
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

/* List View */
.tours-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.tour-list-item {
  display: grid;
  grid-template-columns: 300px 1fr auto;
  gap: 1.5rem;
  background: #fff;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.tour-list-item:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.list-image {
  position: relative;
  height: 220px;
}

.list-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.list-image .duration-badge {
  position: absolute;
  bottom: 1rem;
  left: 1rem;
}

.list-badges {
  position: absolute;
  top: 1rem;
  left: 1rem;
}

.list-content {
  padding: 1.25rem 0;
  display: flex;
  flex-direction: column;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.list-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-top: 0.5rem;
}

.list-header h3 a {
  color: #1e293b;
  text-decoration: none;
}

.list-header h3 a:hover {
  color: #10b981;
}

.list-description {
  font-size: 0.9rem;
  color: #64748b;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.list-meta {
  display: flex;
  gap: 1.5rem;
  font-size: 0.875rem;
  color: #475569;
  margin-bottom: 0.75rem;
}

.list-meta .difficulty {
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.list-includes {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: auto;
}

.include-chip {
  padding: 0.25rem 0.5rem;
  background: #dcfce7;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  color: #16a34a;
}

.more-includes {
  padding: 0.25rem 0.5rem;
  background: #e2e8f0;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 500;
}

.list-action {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-end;
  gap: 1rem;
  border-left: 1px solid #f1f5f9;
  min-width: 180px;
}

.price-block {
  text-align: right;
}

.price-block .original {
  font-size: 0.875rem;
  color: #94a3b8;
  text-decoration: line-through;
  display: block;
}

.price-block .current {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
}

.price-block .unit {
  font-size: 0.8rem;
  color: #64748b;
}

.action-btn {
  width: 100%;
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #10b981, #059669);
  color: #fff;
  text-decoration: none;
  border-radius: 0.5rem;
  font-weight: 600;
  text-align: center;
  transition: all 0.2s;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  margin-top: 3rem;
}

.page-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
  border-color: #10b981;
  color: #10b981;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 0.25rem;
}

.page-num {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-weight: 500;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s;
}

.page-num:hover {
  border-color: #10b981;
  color: #10b981;
}

.page-num.active {
  background: #10b981;
  border-color: #10b981;
  color: #fff;
}

/* Responsive */
@media (max-width: 1024px) {
  .filter-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .tours-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .loading-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .tour-list-item {
    grid-template-columns: 220px 1fr auto;
  }
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 2rem;
  }

  .filter-grid {
    grid-template-columns: 1fr;
  }

  .search-btn {
    justify-content: center;
  }

  .tours-grid {
    grid-template-columns: 1fr;
  }

  .loading-grid {
    grid-template-columns: 1fr;
  }

  .tour-list-item {
    grid-template-columns: 1fr;
  }

  .list-image {
    height: 180px;
  }

  .list-content {
    padding: 1rem 1.25rem;
  }

  .list-action {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    border-left: none;
    border-top: 1px solid #f1f5f9;
    padding: 1rem 1.25rem;
  }

  .pagination {
    flex-wrap: wrap;
  }

  .page-numbers {
    order: -1;
    width: 100%;
    justify-content: center;
    margin-bottom: 0.5rem;
  }
}

@media (max-width: 480px) {
  .filter-card {
    padding: 1rem;
  }

  .list-action {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }

  .pagination {
    gap: 0.5rem;
  }
}
</style>
