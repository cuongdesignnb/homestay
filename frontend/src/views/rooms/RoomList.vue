<template>
  <div class="rooms-page">
    <!-- Hero Section -->
    <section class="page-hero">
      <div class="hero-bg"></div>
      <div class="hero-content">
        <div class="container">
          <Breadcrumb :items="breadcrumbItems" />
          <p class="hero-eyebrow">
            {{ $t("home.curated", "Curated Collection") }}
          </p>
          <h1 class="hero-title">{{ $t("rooms.title") }}</h1>
          <p class="hero-subtitle">
            {{
              $t(
                "rooms.subtitle",
                "Discover your perfect stay with our handpicked selection of comfortable rooms"
              )
            }}
          </p>
        </div>
      </div>
    </section>

    <div class="container">
      <!-- Search & Filter Section -->
      <section class="search-section">
        <div class="search-card">
          <div class="search-grid">
            <div class="search-field">
              <label>
                <span class="field-icon">📅</span>
                {{ $t("rooms.check_in") }}
              </label>
              <input
                v-model="filters.check_in"
                type="date"
                :min="today"
                :lang="inputLang"
              />
            </div>
            <div class="search-field">
              <label>
                <span class="field-icon">📅</span>
                {{ $t("rooms.check_out") }}
              </label>
              <input
                v-model="filters.check_out"
                type="date"
                :min="filters.check_in || today"
                :lang="inputLang"
              />
            </div>
            <div class="search-field">
              <label>
                <span class="field-icon">👥</span>
                {{ $t("rooms.guests") }}
              </label>
              <input
                v-model.number="filters.capacity"
                type="number"
                min="1"
                max="20"
                placeholder="1"
              />
            </div>
            <div class="search-field">
              <label>
                <span class="field-icon">💰</span>
                {{ $t("rooms.price_range", "Price Range") }}
              </label>
              <select v-model="filters.price_range">
                <option value="">{{ $t("common.all", "All") }}</option>
                <option value="budget">
                  {{ $t("rooms.budget", "Budget") }} (&lt; 500k)
                </option>
                <option value="standard">
                  {{ $t("rooms.standard", "Standard") }} (500k - 1M)
                </option>
                <option value="premium">
                  {{ $t("rooms.premium", "Premium") }} (&gt; 1M)
                </option>
              </select>
            </div>
            <button @click="loadRooms" class="search-btn">
              <span class="btn-icon">🔍</span>
              {{ $t("rooms.search") }}
            </button>
          </div>
        </div>
      </section>

      <!-- Results Section -->
      <section class="results-section">
        <div class="results-header">
          <div class="results-info">
            <h2>{{ $t("rooms.available", "Available Rooms") }}</h2>
            <p v-if="!loading">
              {{ rooms.length }} {{ $t("rooms.found", "rooms found") }}
            </p>
          </div>
          <div class="view-toggles">
            <button
              class="view-btn"
              :class="{ active: viewMode === 'grid' }"
              @click="viewMode = 'grid'"
              :title="$t('common.grid_view', 'Grid View')"
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
              :title="$t('common.list_view', 'List View')"
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
        <div v-else-if="rooms.length === 0" class="empty-state">
          <div class="empty-icon">🏠</div>
          <h3>{{ $t("rooms.no_rooms") }}</h3>
          <p>
            {{
              $t("rooms.try_different", "Try adjusting your search criteria")
            }}
          </p>
          <button @click="resetFilters" class="reset-btn">
            {{ $t("common.reset", "Reset Filters") }}
          </button>
        </div>

        <!-- Grid View -->
        <div v-else-if="viewMode === 'grid'" class="rooms-grid">
          <article v-for="room in rooms" :key="room.id" class="room-card">
            <div class="card-image">
              <img
                :src="
                  room.cover_image || room.images?.[0] || '/placeholder.jpg'
                "
                :alt="room.name"
                loading="lazy"
              />
              <div class="card-badges">
                <span v-if="room.discount_price" class="badge sale">
                  -{{
                    Math.round(
                      (1 - room.discount_price / room.price_per_night) * 100
                    )
                  }}%
                </span>
                <span v-if="room.is_featured" class="badge featured"
                  >⭐ Featured</span
                >
              </div>
              <button
                class="wishlist-btn"
                @click.prevent="toggleWishlist(room)"
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
                <span v-if="room.room_category?.name" class="category-tag">{{
                  room.room_category.name
                }}</span>
                <div class="rating">
                  <span class="star">⭐</span>
                  <span class="score">{{
                    room.average_rating?.toFixed(1) || "New"
                  }}</span>
                  <span class="count">({{ room.reviews_count || 0 }})</span>
                </div>
              </div>

              <h3 class="card-title">
                <router-link :to="`/rooms/${room.id}`">{{
                  room.name
                }}</router-link>
              </h3>

              <p class="card-description">
                {{ truncate(room.description, 80) }}
              </p>

              <div class="card-features">
                <span
                  ><svg
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
                  {{ room.capacity }}</span
                >
                <span
                  ><svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <rect x="2" y="4" width="20" height="16" rx="2" />
                    <line x1="2" y1="10" x2="22" y2="10" />
                  </svg>
                  {{ room.beds }}</span
                >
                <span
                  ><svg
                    width="16"
                    height="16"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path d="M3 3v18h18" />
                    <rect x="7" y="13" width="4" height="8" />
                  </svg>
                  {{ room.size }}m²</span
                >
              </div>

              <div
                v-if="room.occupancy"
                class="occupancy-status"
                :class="getOccupancyClass(room)"
              >
                <span class="status-dot"></span>
                {{ getOccupancyText(room) }}
              </div>

              <div class="card-footer">
                <div class="price-info">
                  <span v-if="room.discount_price" class="original-price">{{
                    formatPrice(room.price_per_night)
                  }}</span>
                  <span class="current-price">{{
                    formatPrice(room.discount_price || room.price_per_night)
                  }}</span>
                  <span class="price-unit">/{{ $t("rooms.per_night") }}</span>
                </div>
                <router-link :to="`/rooms/${room.id}`" class="book-btn">
                  {{ $t("rooms.view_details") }}
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
        <div v-else class="rooms-list">
          <article v-for="room in rooms" :key="room.id" class="room-list-item">
            <div class="list-image">
              <img
                :src="
                  room.cover_image || room.images?.[0] || '/placeholder.jpg'
                "
                :alt="room.name"
                loading="lazy"
              />
              <div class="list-badges">
                <span v-if="room.discount_price" class="badge sale">
                  -{{
                    Math.round(
                      (1 - room.discount_price / room.price_per_night) * 100
                    )
                  }}%
                </span>
              </div>
            </div>

            <div class="list-content">
              <div class="list-header">
                <div>
                  <span v-if="room.room_category?.name" class="category-tag">{{
                    room.room_category.name
                  }}</span>
                  <h3>
                    <router-link :to="`/rooms/${room.id}`">{{
                      room.name
                    }}</router-link>
                  </h3>
                </div>
                <div class="rating">
                  <span class="star">⭐</span>
                  <span class="score">{{
                    room.average_rating?.toFixed(1) || "New"
                  }}</span>
                  <span class="count">({{ room.reviews_count || 0 }})</span>
                </div>
              </div>

              <p class="list-description">
                {{ truncate(room.description, 150) }}
              </p>

              <div class="list-features">
                <span>👥 {{ room.capacity }} {{ $t("rooms.guests") }}</span>
                <span>🛏️ {{ room.beds }} {{ $t("rooms.beds") }}</span>
                <span>🚿 {{ room.bathrooms }}</span>
                <span>📐 {{ room.size }}m²</span>
              </div>

              <div v-if="room.amenities?.length" class="list-amenities">
                <span
                  v-for="amenity in room.amenities.slice(0, 4)"
                  :key="amenity"
                  class="amenity-chip"
                >
                  {{ amenity }}
                </span>
                <span v-if="room.amenities.length > 4" class="more-amenities">
                  +{{ room.amenities.length - 4 }}
                </span>
              </div>
            </div>

            <div class="list-action">
              <div class="price-block">
                <span v-if="room.discount_price" class="original">{{
                  formatPrice(room.price_per_night)
                }}</span>
                <span class="current">{{
                  formatPrice(room.discount_price || room.price_per_night)
                }}</span>
                <span class="unit">/{{ $t("rooms.per_night") }}</span>
              </div>
              <router-link :to="`/rooms/${room.id}`" class="action-btn">
                {{ $t("rooms.book_now") }}
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

const { locale, t } = useI18n({ useScope: "global" });
const inputLang = computed(() => (locale.value === "vi" ? "vi" : "en"));

const rooms = ref([]);
const loading = ref(true);
const viewMode = ref("grid");
const filters = ref({
  check_in: "",
  check_out: "",
  capacity: 1,
  price_range: "",
});
const pagination = ref({
  current_page: 1,
  last_page: 1,
  total: 0,
});

const today = computed(() => new Date().toISOString().split("T")[0]);

const breadcrumbItems = computed(() => [
  { label: t("nav.home"), path: "/" },
  { label: t("rooms.title"), path: "/rooms" },
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

const getOccupancyClass = (room) => {
  if (!room.occupancy) return "";
  if (room.occupancy.is_currently_occupied) return "occupied";
  if (room.occupancy.next_check_in) return "upcoming";
  return "available";
};

const getOccupancyText = (room) => {
  if (!room.occupancy) return "";
  if (room.occupancy.is_currently_occupied)
    return t("rooms.currently_occupied");
  if (room.occupancy.next_check_in)
    return `${t("rooms.next_available")}: ${room.occupancy.next_check_in}`;
  return t("rooms.ready_to_book");
};

const toggleWishlist = (room) => {
  console.log("Toggle wishlist:", room.id);
};

const loadRooms = async (page = 1) => {
  loading.value = true;
  try {
    const params = { page, ...filters.value };
    const response = await api.get("/rooms", { params });
    rooms.value = response.data.data;
    pagination.value = {
      current_page: response.data.current_page,
      last_page: response.data.last_page,
      total: response.data.total,
    };
  } catch (error) {
    console.error("Error loading rooms:", error);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadRooms(page);
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
};

const resetFilters = () => {
  filters.value = { check_in: "", check_out: "", capacity: 1, price_range: "" };
  loadRooms();
};

onMounted(() => {
  loadRooms();
});

watch(locale, () => {
  loadRooms();
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
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
  z-index: 0;
}

.hero-bg::before {
  content: "";
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
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
  color: #94a3b8;
}

.hero-content :deep(.breadcrumb-link:hover) {
  color: #fff;
  background: rgba(255, 255, 255, 0.1);
}

.hero-content :deep(.breadcrumb-current) {
  color: #e2e8f0;
}

.hero-content :deep(.breadcrumb-separator) {
  color: #475569;
}

.hero-eyebrow {
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.15em;
  color: #6366f1;
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


  @media (max-width: 480px) {
    .search-card {
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
.hero-subtitle {
  font-size: 1.125rem;
  color: #94a3b8;
  max-width: 600px;
}

/* Container */
.container {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

/* Search Section */
.search-section {
  margin-top: -3rem;
  margin-bottom: 3rem;
  position: relative;
  z-index: 10;
}

.search-card {
  background: #fff;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.search-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr) auto;
  gap: 1rem;
  align-items: end;
}

.search-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.search-field label {
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

.search-field input,
.search-field select {
  padding: 0.875rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  font-size: 1rem;
  transition: all 0.2s;
}

.search-field input:focus,
.search-field select:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.search-btn {
  padding: 0.875rem 1.5rem;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
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
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
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
  color: #6366f1;
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
  height: 200px;
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
  background: #6366f1;
  color: #fff;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.reset-btn:hover {
  background: #4f46e5;
}

/* Room Grid */
.rooms-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.room-card {
  background: #fff;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.room-card:hover {
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

.room-card:hover .card-image img {
  transform: scale(1.05);
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
  color: #6366f1;
  background: rgba(99, 102, 241, 0.1);
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
  color: #6366f1;
}

.card-description {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.5;
  margin-bottom: 1rem;
}

.card-features {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
  color: #475569;
}

.card-features span {
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.card-features svg {
  color: #6366f1;
}

.occupancy-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.8rem;
  margin-bottom: 1rem;
}

.occupancy-status.available {
  background: #dcfce7;
  color: #16a34a;
}

.occupancy-status.upcoming {
  background: #fef3c7;
  color: #d97706;
}

.occupancy-status.occupied {
  background: #fee2e2;
  color: #dc2626;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
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
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  text-decoration: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  transition: all 0.2s;
}

.book-btn:hover {
  transform: translateX(4px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
}

/* List View */
.rooms-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.room-list-item {
  display: grid;
  grid-template-columns: 280px 1fr auto;
  gap: 1.5rem;
  background: #fff;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.room-list-item:hover {
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
}

.list-image {
  position: relative;
  height: 200px;
}

.list-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
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
  color: #6366f1;
}

.list-description {
  font-size: 0.9rem;
  color: #64748b;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.list-features {
  display: flex;
  gap: 1.5rem;
  font-size: 0.875rem;
  color: #475569;
  margin-bottom: 0.75rem;
}

.list-amenities {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: auto;
}

.amenity-chip {
  padding: 0.25rem 0.5rem;
  background: #f1f5f9;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  color: #475569;
}

.more-amenities {
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
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: #fff;
  text-decoration: none;
  border-radius: 0.5rem;
  font-weight: 600;
  text-align: center;
  transition: all 0.2s;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
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
  border-color: #6366f1;
  color: #6366f1;
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
  border-color: #6366f1;
  color: #6366f1;
}

.page-num.active {
  background: #6366f1;
  border-color: #6366f1;
  color: #fff;
}

/* Responsive */
@media (max-width: 1024px) {
  .search-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .rooms-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .loading-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .room-list-item {
    grid-template-columns: 200px 1fr auto;
  }
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 2rem;
  }

  .search-grid {
    grid-template-columns: 1fr;
  }

  .search-btn {
    justify-content: center;
  }

  .rooms-grid {
    grid-template-columns: 1fr;
  }

  .loading-grid {
    grid-template-columns: 1fr;
  }

  .room-list-item {
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
</style>
