<template>
  <div class="tour-detail-page">
    <div v-if="loading" class="detail-loading">
      <div class="skeleton hero"></div>
      <div class="skeleton text-row"></div>
      <div class="skeleton text-row short"></div>
    </div>

    <div v-else-if="tour" class="detail-wrapper">
      <!-- SEO Breadcrumb -->
      <div class="breadcrumb-container">
        <Breadcrumb :items="breadcrumbItems" />
      </div>

      <!-- Header -->
      <div class="detail-header">
        <button class="ghost-btn" @click="$router.back()">
          ← {{ $t("common.back") }}
        </button>
        <div class="header-meta">
          <span v-if="tour.tour_category?.name" class="chip category">{{
            tour.tour_category.name
          }}</span>
          <span class="chip difficulty" :class="tour.difficulty_level">{{
            tour.difficulty_level
          }}</span>
          <span class="chip views">👁️ {{ tour.view_count || 0 }}</span>
        </div>
      </div>

      <!-- Hero Section with Slider -->
      <section class="tour-hero">
        <div
          class="slider-wrapper"
          @mouseenter="pauseSlider"
          @mouseleave="resumeSlider"
        >
          <div v-if="!galleryImages.length" class="hero-fallback">
            <div class="fallback-icon">🏔️</div>
            <p>{{ $t("tours.no_images", "No images yet") }}</p>
          </div>
          <div v-else class="hero-slides">
            <article v-if="currentImage" class="hero-slide active">
              <img :src="currentImage" :alt="tour.name" />
              <div class="slide-gradient"></div>
              <div class="slide-count">
                {{ currentSlide + 1 }}/{{ galleryImages.length }}
              </div>
            </article>
            <button
              v-if="galleryImages.length > 1"
              class="slider-nav prev"
              @click="prevSlide"
            >
              ‹
            </button>
            <button
              v-if="galleryImages.length > 1"
              class="slider-nav next"
              @click="nextSlide"
            >
              ›
            </button>
          </div>
        </div>

        <div class="hero-summary">
          <div class="tour-badge">
            <span class="duration-pill"
              >⏱️ {{ tour.duration }}
              {{ tour.duration_unit || $t("tours.days") }}</span
            >
          </div>
          <h1>{{ tour.name }}</h1>
          <p v-if="heroSummary" class="lead">{{ heroSummary }}</p>

          <div class="metrics-grid">
            <div>
              <span class="icon">📍</span>
              <div>
                <small>{{ $t("tours.departure", "Departure") }}</small>
                <p>{{ tour.departure_location || "N/A" }}</p>
              </div>
            </div>
            <div>
              <span class="icon">👥</span>
              <div>
                <small>{{ $t("tours.group_size", "Group size") }}</small>
                <p>{{ tour.min_participants }}-{{ tour.max_participants }}</p>
              </div>
            </div>
            <div>
              <span class="icon">💪</span>
              <div>
                <small>{{ $t("tours.difficulty", "Difficulty") }}</small>
                <p>{{ tour.difficulty_level }}</p>
              </div>
            </div>
          </div>

          <div class="rating-row">
            <div class="rating-pill">
              ⭐ {{ tour.average_rating?.toFixed(1) || "N/A" }}
            </div>
            <p>
              {{ tour.reviews_count || 0 }} {{ $t("tours.reviews", "reviews") }}
            </p>
          </div>
        </div>
      </section>

      <!-- Thumbnail Strip -->
      <div v-if="galleryImages.length > 1" class="thumb-strip">
        <button
          v-for="(image, index) in galleryImages"
          :key="`thumb-${index}`"
          class="thumb"
          :class="{ active: index === currentSlide }"
          @click="goToSlide(index)"
        >
          <img :src="image" :alt="`Thumbnail ${index + 1}`" />
        </button>
      </div>

      <!-- Content Grid -->
      <section class="detail-grid">
        <article class="info-panel">
          <!-- Description -->
          <div class="panel-card">
            <h2>{{ $t("common.description", "Description") }}</h2>
            <div class="body-text" v-html="formattedDescription"></div>
          </div>

          <!-- Itinerary -->
          <div v-if="tour.itinerary?.length" class="panel-card">
            <div class="itinerary-header">
              <div>
                <h2>{{ $t("tours.itinerary", "Itinerary") }}</h2>
                <p class="itinerary-subtitle">{{ $t("tours.itinerary_hint", "Day by day activities") }}</p>
              </div>
              <span class="steps-badge">
                🗺️ {{ tour.itinerary.length }} {{ $t("tours.steps", "steps") }}
              </span>
            </div>
            <div class="itinerary-timeline">
              <div
                v-for="(item, index) in tour.itinerary"
                :key="index"
                class="timeline-item"
              >
                <div class="timeline-marker">
                  <div class="step-number">{{ index + 1 }}</div>
                  <div v-if="index < tour.itinerary.length - 1" class="timeline-line"></div>
                </div>
                <div class="step-content">
                  <p>{{ item }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Includes / Excludes -->
          <div class="panel-card includes-excludes">
            <div class="ie-grid">
              <div class="ie-column includes">
                <h3>✓ {{ $t("tours.includes", "Includes") }}</h3>
                <ul>
                  <li v-for="item in tour.includes" :key="item">{{ item }}</li>
                </ul>
              </div>
              <div class="ie-column excludes">
                <h3>✗ {{ $t("tours.excludes", "Excludes") }}</h3>
                <ul>
                  <li v-for="item in tour.excludes" :key="item">{{ item }}</li>
                </ul>
              </div>
            </div>
          </div>

          <!-- Reviews -->
          <div class="panel-card">
            <div class="panel-head">
              <h2>{{ $t("tours.reviews", "Reviews") }}</h2>
              <p>
                {{ tour.reviews_count || 0 }}
                {{ $t("tours.reviews_total", "verified travelers") }}
              </p>
            </div>
            <div v-if="tour.reviews?.length" class="reviews-grid">
              <article
                v-for="review in tour.reviews"
                :key="review.id"
                class="review-card"
              >
                <header>
                  <p>{{ review.user?.name || "Traveler" }}</p>
                  <span>⭐ {{ review.rating }}</span>
                </header>
                <p>{{ review.comment }}</p>
              </article>
            </div>
            <p v-else class="muted">
              {{ $t("tours.no_reviews", "No reviews yet") }}
            </p>
          </div>

          <!-- Guest Reviews Section -->
          <ReviewsList ref="reviewsListRef" type="tour" :item-id="tour.id" />

          <!-- Review Form -->
          <ReviewForm
            type="tour"
            :item-id="tour.id"
            @submitted="onReviewSubmitted"
          />
        </article>

        <!-- Booking Panel -->
        <aside class="booking-panel">
          <div class="price-row">
            <div v-if="selectedVariant && currentTier">
              <p class="per-person">
                /{{ currentTier.pricing_type === 'flat' ? $t("tours.flat") : $t("tours.per_person") }}
              </p>
              <p class="price">{{ formatPrice(effectivePrice) }}</p>
            </div>
            <div v-else-if="!selectedVariant">
              <p class="per-person">/{{ $t("tours.per_person") }}</p>
              <p class="price">{{ formatPrice(effectivePrice) }}</p>
            </div>
            <div v-else>
              <p class="error-text">{{ $t("tours.no_tier_found") }}</p>
            </div>

            <div v-if="!selectedVariant && tour.discount_price" class="discount-highlight">
              <span class="discount-badge">
                -{{ Math.round((1 - tour.discount_price / tour.price_per_person) * 100) }}%
              </span>
              <p class="original-price">
                {{ formatPrice(tour.price_per_person) }}
              </p>
            </div>
            <div v-else-if="selectedVariant && currentTier && currentTier.discount_price" class="discount-highlight">
              <span class="discount-badge">
                -{{ Math.round((1 - parseFloat(currentTier.discount_price) / parseFloat(currentTier.price)) * 100) }}%
              </span>
              <p class="original-price">
                {{ formatPrice(parseFloat(currentTier.price)) }}
              </p>
            </div>
          </div>

          <form @submit.prevent="handleBooking" class="booking-form">
            <label>
              <span>{{ $t("tours.tour_date", "Tour Date") }}</span>
              <input
                v-model="bookingForm.tour_date"
                :min="today"
                :lang="inputLang"
                type="date"
                required
              />
            </label>

            <!-- Variant Selector -->
            <div v-if="tour.variants && tour.variants.length" class="variants-section">
              <span class="section-label">{{ $t("tours.variant", "Tour Variant") }}</span>
              <div class="variants-list">
                <button
                  type="button"
                  v-for="variant in tour.variants"
                  :key="variant.id"
                  class="variant-card"
                  :class="{ active: selectedVariantId === variant.id }"
                  @click="selectVariant(variant.id)"
                >
                  <div class="variant-info">
                    <span class="variant-name">{{ variant.name }}</span>
                    <span v-if="variant.description" class="variant-desc">{{ variant.description }}</span>
                  </div>
                  <span class="variant-price" v-if="getVariantMinPrice(variant)">
                    {{ $t("tours.from") }} {{ formatPrice(getVariantMinPrice(variant)) }}
                  </span>
                </button>
              </div>
            </div>

            <label>
              <span>{{ $t("tours.participants", "Participants") }}</span>
              <input
                v-model.number="bookingForm.participants"
                type="number"
                :min="minAllowedParticipants"
                :max="maxAllowedParticipants"
                required
              />
              <small class="hint"
                >Min: {{ minAllowedParticipants }}, Max:
                {{ maxAllowedParticipants }}</small
              >
            </label>

            <!-- Optional Addons -->
            <div v-if="tour.addons && tour.addons.length" class="addons-container">
              <span class="addons-title">{{ $t("tours.addons", "Optional Addons") }}</span>
              <div class="addons-list">
                <label
                  v-for="addon in tour.addons"
                  :key="addon.id"
                  class="addon-checkbox-label"
                  :class="{ checked: selectedAddonIds.includes(addon.id) }"
                >
                  <input
                    type="checkbox"
                    :value="addon.id"
                    v-model="selectedAddonIds"
                  />
                  <div class="addon-details">
                    <span class="addon-name">{{ addon.name }}</span>
                    <span class="addon-price">
                      +{{ formatPrice(addon.price) }}
                      <small class="addon-unit">
                        /{{ addon.pricing_type === 'per_person' ? $t("tours.per_person") : $t("tours.flat") }}
                      </small>
                    </span>
                  </div>
                </label>
              </div>
            </div>

            <!-- Tier Warning -->
            <div v-if="selectedVariantId && !currentTier" class="tier-warning">
              {{ $t("tours.no_tier_found") }}
            </div>

            <div v-if="totalPrice" class="totals">
              <div class="total-row">
                <span>
                  {{ $t("tours.tour", "Tour") }}
                  <span v-if="selectedVariant">({{ selectedVariant.name }})</span>
                  <small v-if="selectedVariant && currentTier && currentTier.pricing_type === 'flat'">
                    ({{ $t("tours.flat") }})
                  </small>
                  <small v-else>
                    ({{ bookingForm.participants }} × {{ formatPrice(effectivePrice) }})
                  </small>
                </span>
                <span>{{ formatPrice(tourBaseCost) }}</span>
              </div>

              <div v-if="addonsCost > 0" class="total-row">
                <span>{{ $t("tours.addons_cost", "Addons total") }}</span>
                <span>{{ formatPrice(addonsCost) }}</span>
              </div>

              <div class="grand">
                <span>{{ $t("common.total", "Total") }}</span>
                <span>{{ formatPrice(totalPrice) }}</span>
              </div>
            </div>

            <button type="submit" class="cta" :disabled="isBookingDisabled">
              {{
                bookingLoading ? $t("common.loading") : $t("tours.book_tour")
              }}
            </button>
          </form>

          <p v-if="bookingError" class="error-msg">{{ bookingError }}</p>
        </aside>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import api from "@/services/api";
import dayjs from "dayjs";
import { useI18n } from "vue-i18n";
import ReviewForm from "@/components/ReviewForm.vue";
import ReviewsList from "@/components/ReviewsList.vue";
import Breadcrumb from "@/components/Breadcrumb.vue";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { locale, t } = useI18n({ useScope: "global" });
const inputLang = computed(() => (locale.value === "vi" ? "vi" : "en"));

const tour = ref(null);
const loading = ref(true);
const bookingLoading = ref(false);
const bookingError = ref("");
const reviewsListRef = ref(null);

const onReviewSubmitted = () => {
  // Reviews will refresh when approved by admin
};

const today = computed(() => dayjs().format("YYYY-MM-DD"));

const bookingForm = ref({
  tour_date: "",
  participants: 1,
});

const selectedVariantId = ref(null);
const selectedAddonIds = ref([]);

const selectedVariant = computed(() => {
  if (!tour.value || !tour.value.variants) return null;
  return tour.value.variants.find(v => v.id === selectedVariantId.value) || null;
});

const currentTier = computed(() => {
  if (!selectedVariant.value || !selectedVariant.value.price_tiers) return null;
  const p = bookingForm.value.participants;
  return selectedVariant.value.price_tiers.find(tier => 
    p >= tier.min_participants && p <= tier.max_participants
  ) || null;
});

const minAllowedParticipants = computed(() => {
  if (selectedVariant.value) {
    return selectedVariant.value.min_participants || 1;
  }
  return tour.value?.min_participants || 1;
});

const maxAllowedParticipants = computed(() => {
  if (selectedVariant.value) {
    return selectedVariant.value.max_participants || 999;
  }
  return tour.value?.max_participants || 999;
});

const onVariantChange = () => {
  const min = minAllowedParticipants.value;
  const max = maxAllowedParticipants.value;
  if (bookingForm.value.participants < min) {
    bookingForm.value.participants = min;
  } else if (bookingForm.value.participants > max) {
    bookingForm.value.participants = max;
  }
};

const selectVariant = (variantId) => {
  selectedVariantId.value = variantId;
  onVariantChange();
};

const getVariantMinPrice = (variant) => {
  if (!variant.price_tiers || !variant.price_tiers.length) return 0;
  let min = Infinity;
  variant.price_tiers.forEach(tier => {
    const price = parseFloat(tier.discount_price ?? tier.price);
    if (price < min) {
      min = price;
    }
  });
  return min === Infinity ? 0 : min;
};

const effectivePrice = computed(() => {
  if (!tour.value) return 0;
  if (selectedVariant.value) {
    if (currentTier.value) {
      return parseFloat(currentTier.value.discount_price ?? currentTier.value.price);
    }
    return 0;
  }
  return parseFloat(tour.value.discount_price ?? tour.value.price_per_person);
});

const tourBaseCost = computed(() => {
  if (!tour.value) return 0;
  if (selectedVariant.value) {
    if (currentTier.value) {
      if (currentTier.value.pricing_type === 'per_person') {
        return effectivePrice.value * bookingForm.value.participants;
      } else {
        return effectivePrice.value; // flat rate
      }
    }
    return 0;
  }
  return effectivePrice.value * bookingForm.value.participants;
});

const addonsCost = computed(() => {
  if (!tour.value || !tour.value.addons) return 0;
  let cost = 0;
  selectedAddonIds.value.forEach(id => {
    const addon = tour.value.addons.find(a => a.id === id);
    if (addon) {
      const price = parseFloat(addon.price);
      if (addon.pricing_type === 'per_person') {
        cost += price * bookingForm.value.participants;
      } else {
        cost += price;
      }
    }
  });
  return cost;
});

const totalPrice = computed(() => {
  if (!tour.value) return 0;
  if (selectedVariant.value && !currentTier.value) {
    return 0;
  }
  return tourBaseCost.value + addonsCost.value;
});

const isBookingDisabled = computed(() => {
  if (bookingLoading.value) return true;
  if (!tour.value) return true;
  if (selectedVariant.value && !currentTier.value) return true;
  return false;
});

// Gallery images
const galleryImages = computed(() => {
  if (!tour.value) return [];
  const ordered = [];
  const seen = new Set();
  const sources = [];

  if (tour.value.cover_image) sources.push(tour.value.cover_image);
  if (Array.isArray(tour.value.images)) sources.push(...tour.value.images);

  const albumItems = tour.value.media_album?.media_items || [];
  if (albumItems.length) sources.push(...albumItems.map((item) => item.url));

  sources.forEach((image) => {
    if (!image || seen.has(image)) return;
    seen.add(image);
    ordered.push(image);
  });
  return ordered;
});

const currentSlide = ref(0);
const sliderTimer = ref(null);

const currentImage = computed(() => {
  const images = galleryImages.value;
  if (!images.length) return null;
  return images[Math.min(currentSlide.value, images.length - 1)];
});

const stopSlider = () => {
  if (sliderTimer.value) {
    clearInterval(sliderTimer.value);
    sliderTimer.value = null;
  }
};

const startSlider = () => {
  stopSlider();
  if (galleryImages.value.length <= 1) return;
  sliderTimer.value = setInterval(() => nextSlide(), 5000);
};

const pauseSlider = () => stopSlider();
const resumeSlider = () => startSlider();

const nextSlide = () => {
  if (!galleryImages.value.length) return;
  currentSlide.value = (currentSlide.value + 1) % galleryImages.value.length;
};

const prevSlide = () => {
  if (!galleryImages.value.length) return;
  currentSlide.value =
    (currentSlide.value - 1 + galleryImages.value.length) %
    galleryImages.value.length;
};

const goToSlide = (index) => {
  if (index >= 0 && index < galleryImages.value.length)
    currentSlide.value = index;
};

const formatPrice = (price) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
    maximumFractionDigits: 0,
  }).format(price);
};

const breadcrumbItems = computed(() => [
  { label: t("nav.home"), path: "/" },
  { label: t("tours.title"), path: "/tours" },
  {
    label: tour.value?.name || t("tours.tour", "Tour"),
    path: `/tours/${tour.value?.id}`,
  },
]);

const heroSummary = computed(() => {
  if (!tour.value) return "";
  const raw = tour.value.meta_description || tour.value.description || "";
  return raw
    .replace(/<[^>]+>/g, " ")
    .replace(/\s+/g, " ")
    .trim()
    .slice(0, 200);
});

const formattedDescription = computed(() => {
  return (tour.value?.description || "").replace(/\n/g, "<br />");
});

const handleBooking = async () => {
  if (!authStore.isAuthenticated) {
    router.push("/login");
    return;
  }

  bookingLoading.value = true;
  bookingError.value = "";

  try {
    await api.post("/tour-bookings", {
      tour_id: tour.value.id,
      tour_date: bookingForm.value.tour_date,
      participants: bookingForm.value.participants,
      contact_name: authStore.user.name,
      contact_email: authStore.user.email,
      contact_phone: authStore.user.phone || "",
      tour_variant_id: selectedVariantId.value,
      addon_ids: selectedAddonIds.value,
    });
    router.push("/bookings");
  } catch (error) {
    bookingError.value = error.response?.data?.message || "Booking failed";
  } finally {
    bookingLoading.value = false;
  }
};

const loadTour = async () => {
  loading.value = true;
  try {
    const response = await api.get(`/tours/${route.params.id}`);
    const data = response.data;
    
    // Map active_variants and active_addons to variants and addons for frontend compatibility
    if (data.active_variants) {
      data.variants = data.active_variants;
    }
    if (data.active_addons) {
      data.addons = data.active_addons;
    }
    
    tour.value = data;
    
    if (tour.value.variants && tour.value.variants.length) {
      const defaultVariant = tour.value.variants.find(v => v.is_default) || tour.value.variants[0];
      selectedVariantId.value = defaultVariant.id;
    } else {
      selectedVariantId.value = null;
    }
    
    selectedAddonIds.value = [];
    bookingForm.value.participants = minAllowedParticipants.value || tour.value.min_participants || 1;
  } catch (error) {
    console.error("Error loading tour:", error);
    if (!tour.value) router.push("/tours");
  } finally {
    loading.value = false;
  }
};

watch(locale, () => loadTour());

watch(galleryImages, (images) => {
  currentSlide.value = 0;
  if (images.length > 1) startSlider();
  else stopSlider();
});

onMounted(() => {
  loadTour();
  if (galleryImages.value.length > 1) startSlider();
});

onBeforeUnmount(() => stopSlider());
</script>

<style scoped>
.tour-detail-page {
  min-height: 100vh;
  background: radial-gradient(
      circle at top,
      rgba(34, 197, 94, 0.06),
      transparent 50%
    ),
    #f8fafc;
  padding: 3rem 1rem;
}

.detail-loading {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  gap: 1rem;
}

.skeleton {
  border-radius: 1rem;
  background: linear-gradient(120deg, #e2e8f0 0%, #f8fafc 40%, #e2e8f0 80%);
  background-size: 200% 100%;
  animation: shimmer 1.2s infinite;
}

.skeleton.hero {
  height: 360px;
}
.skeleton.text-row {
  height: 42px;
}
.skeleton.text-row.short {
  width: 60%;
}

@keyframes shimmer {
  100% {
    background-position-x: -200%;
  }
}

.detail-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.ghost-btn {
  border: none;
  background: transparent;
  color: #16a34a;
  font-weight: 600;
  cursor: pointer;
  padding: 0.4rem 0.8rem;
  border-radius: 999px;
  transition: background 0.2s ease;
}

.ghost-btn:hover {
  background: rgba(22, 163, 74, 0.1);
}

.header-meta {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.chip {
  padding: 0.25rem 0.85rem;
  border-radius: 999px;
  font-size: 0.8rem;
  background: rgba(15, 23, 42, 0.06);
  text-transform: capitalize;
}

.chip.category {
  background: linear-gradient(
    135deg,
    rgba(34, 197, 94, 0.15),
    rgba(16, 185, 129, 0.15)
  );
  color: #047857;
}

.chip.difficulty.easy {
  background: rgba(34, 197, 94, 0.15);
  color: #047857;
}

.chip.difficulty.moderate {
  background: rgba(251, 191, 36, 0.2);
  color: #92400e;
}

.chip.difficulty.hard {
  background: rgba(239, 68, 68, 0.15);
  color: #b91c1c;
}

.chip.views {
  background: rgba(59, 130, 246, 0.12);
  color: #1d4ed8;
}

.chip.subtle {
  background: rgba(148, 163, 184, 0.2);
  color: #475569;
}

/* Hero Section */
.tour-hero {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  align-items: stretch;
}

@media (max-width: 900px) {
  .tour-hero {
    grid-template-columns: 1fr;
  }
}

.slider-wrapper {
  position: relative;
  border-radius: 1.5rem;
  overflow: hidden;
  min-height: 400px;
  background: linear-gradient(135deg, #064e3b, #065f46);
}

.hero-fallback {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.7);
  gap: 1rem;
}

.fallback-icon {
  font-size: 4rem;
}

.hero-slides {
  position: relative;
  width: 100%;
  height: 400px;
}

.hero-slide {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: 1.5rem;
  overflow: hidden;
  background: #064e3b;
}

.hero-slide img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 8s ease;
}

.hero-slide.active img {
  transform: scale(1.08);
}

.slide-gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.02), rgba(0, 0, 0, 0.5));
  z-index: 1;
}

.slide-count {
  position: absolute;
  bottom: 1rem;
  right: 1rem;
  background: rgba(15, 23, 42, 0.75);
  color: white;
  padding: 0.3rem 0.9rem;
  border-radius: 999px;
  font-size: 0.8rem;
  z-index: 2;
}

.slider-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.9);
  color: #0f172a;
  border: none;
  width: 44px;
  height: 44px;
  border-radius: 999px;
  cursor: pointer;
  font-size: 1.6rem;
  z-index: 3;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.slider-nav:hover {
  transform: translateY(-50%) scale(1.1);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.slider-nav.prev {
  left: 1rem;
}
.slider-nav.next {
  right: 1rem;
}

/* Hero Summary */
.hero-summary {
  background: white;
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 25px 60px rgba(15, 23, 42, 0.08);
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.tour-badge {
  display: flex;
  gap: 0.5rem;
}

.duration-pill {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  padding: 0.4rem 1rem;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
}

.hero-summary h1 {
  font-size: clamp(1.75rem, 3vw, 2.5rem);
  color: #0f172a;
  margin: 0;
  line-height: 1.2;
}

.lead {
  color: #475569;
  line-height: 1.6;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.metrics-grid > div {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: rgba(15, 23, 42, 0.03);
  padding: 0.85rem;
  border-radius: 1rem;
}

.metrics-grid .icon {
  font-size: 1.5rem;
}

.metrics-grid small {
  display: block;
  color: #64748b;
  font-size: 0.75rem;
}

.metrics-grid p {
  margin: 0;
  font-weight: 600;
  color: #0f172a;
}

.rating-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: #475569;
}

.rating-pill {
  background: rgba(250, 204, 21, 0.2);
  color: #b45309;
  padding: 0.4rem 1rem;
  border-radius: 999px;
  font-weight: 600;
}

/* Thumbnail Strip */
.thumb-strip {
  display: flex;
  gap: 0.75rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.thumb {
  flex-shrink: 0;
  width: 100px;
  height: 72px;
  border-radius: 0.75rem;
  border: 2px solid transparent;
  overflow: hidden;
  padding: 0;
  background: none;
  cursor: pointer;
  transition: border-color 0.2s ease, transform 0.2s ease;
}

.thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumb.active {
  border-color: #22c55e;
}

.thumb:hover {
  transform: translateY(-2px);
}

/* Detail Grid */
.detail-grid {
  display: grid;
  grid-template-columns: 1.4fr 0.6fr;
  gap: 2rem;
}

@media (max-width: 900px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }
}

.info-panel {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.panel-card {
  background: white;
  border-radius: 1.5rem;
  padding: 1.75rem;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.06);
}

.panel-card h2 {
  margin: 0 0 1rem;
  font-size: 1.35rem;
  color: #0f172a;
}

.panel-head {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  margin-bottom: 1rem;
}

.panel-head p {
  color: #94a3b8;
  margin-top: 0.25rem;
}

.body-text {
  color: #475569;
  line-height: 1.75;
}

/* Itinerary */
.itinerary-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  gap: 1rem;
  flex-wrap: wrap;
}

.itinerary-header h2 {
  margin: 0;
  font-size: 1.35rem;
  color: #0f172a;
}

.itinerary-subtitle {
  color: #94a3b8;
  margin-top: 0.25rem;
  font-size: 0.9rem;
}

.steps-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.45rem 1rem;
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.1));
  color: #047857;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
  white-space: nowrap;
}

.itinerary-timeline {
  display: flex;
  flex-direction: column;
}

.timeline-item {
  display: flex;
  gap: 1rem;
}

.timeline-marker {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex-shrink: 0;
}

.timeline-line {
  width: 2px;
  flex: 1;
  min-height: 16px;
  background: linear-gradient(180deg, #22c55e, #bbf7d0);
  margin: 4px 0;
}

.step-number {
  flex-shrink: 0;
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
  border-radius: 999px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.9rem;
}

.step-content {
  flex: 1;
  background: #f1f5f9;
  border-radius: 12px;
  padding: 0.75rem 1rem;
  margin-bottom: 0.5rem;
}

.step-content p {
  margin: 0;
  color: #334155;
  line-height: 1.6;
}

/* Includes / Excludes */
.ie-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

@media (max-width: 600px) {
  .ie-grid {
    grid-template-columns: 1fr;
  }
}

.ie-column h3 {
  margin: 0 0 0.75rem;
  font-size: 1.1rem;
}

.ie-column.includes h3 {
  color: #16a34a;
}

.ie-column.excludes h3 {
  color: #dc2626;
}

.ie-column ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.ie-column ul li {
  color: #475569;
  padding-left: 1.25rem;
  position: relative;
}

.ie-column.includes ul li::before {
  content: "✓";
  position: absolute;
  left: 0;
  color: #16a34a;
}

.ie-column.excludes ul li::before {
  content: "✗";
  position: absolute;
  left: 0;
  color: #dc2626;
}

/* Reviews */
.reviews-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.review-card {
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 1rem;
  padding: 1rem;
  color: #475569;
}

.review-card header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  color: #0f172a;
  font-weight: 600;
}

.muted {
  color: #94a3b8;
  font-style: italic;
}

/* Booking Panel */
.booking-panel {
  background: linear-gradient(135deg, #064e3b, #065f46);
  color: white;
  border-radius: 1.5rem;
  padding: 2rem;
  position: sticky;
  top: 2rem;
  align-self: flex-start;
  box-shadow: 0 30px 60px rgba(6, 78, 59, 0.4);
}

.price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  gap: 1rem;
}

.per-person {
  font-size: 0.85rem;
  opacity: 0.7;
  margin: 0;
}

.price {
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
}

.discount-highlight {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.35rem;
}

.discount-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.4rem 0.85rem;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border-radius: 999px;
  font-size: 0.95rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
  animation: discount-pulse 2s ease-in-out infinite;
}

@keyframes discount-pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.original-price {
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.7);
  text-decoration: line-through;
  text-decoration-color: rgba(239, 68, 68, 0.8);
  text-decoration-thickness: 2px;
  margin: 0;
}

.booking-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.booking-form label {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.booking-form label span {
  font-size: 0.85rem;
  opacity: 0.9;
}

.booking-form input {
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  border: 1px solid rgba(255, 255, 255, 0.25);
  background: rgba(255, 255, 255, 0.1);
  color: white;
  font-size: 1rem;
}

.booking-form input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.booking-form .hint {
  font-size: 0.75rem;
  opacity: 0.7;
}

.totals {
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  padding-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.totals > div {
  display: flex;
  justify-content: space-between;
  font-size: 0.9rem;
  opacity: 0.9;
}

.totals .grand {
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  padding-top: 0.75rem;
  margin-top: 0.5rem;
  font-size: 1.15rem;
  font-weight: 700;
  opacity: 1;
}

.cta {
  width: 100%;
  padding: 1rem;
  border: none;
  border-radius: 999px;
  background: white;
  color: #065f46;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.cta:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.cta:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-msg {
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  background: rgba(239, 68, 68, 0.2);
  border-radius: 0.75rem;
  color: #fecaca;
  font-size: 0.9rem;
}

.booking-select {
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  border: 1px solid rgba(255, 255, 255, 0.25);
  background: rgba(255, 255, 255, 0.1);
  color: white;
  font-size: 1rem;
  outline: none;
  cursor: pointer;
  width: 100%;
}

.booking-select option {
  background: #064e3b;
  color: white;
}

.addons-container {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.addons-title {
  font-size: 0.85rem;
  opacity: 0.9;
  font-weight: 600;
}

.addons-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-height: 200px;
  overflow-y: auto;
  padding-right: 0.25rem;
}

/* Scrollbar styling for list */
.addons-list::-webkit-scrollbar {
  width: 4px;
}
.addons-list::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 999px;
}

.addon-checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: rgba(255, 255, 255, 0.08);
  padding: 0.65rem 0.85rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background 0.2s ease, border-color 0.2s ease;
  border: 1px solid transparent;
}

.addon-checkbox-label:hover {
  background: rgba(255, 255, 255, 0.15);
}

.addon-checkbox-label input[type="checkbox"] {
  accent-color: #22c55e;
  width: 1.1rem;
  height: 1.1rem;
  cursor: pointer;
}

.addon-details {
  display: flex;
  justify-content: space-between;
  width: 100%;
  font-size: 0.9rem;
  align-items: center;
}

.addon-name {
  font-weight: 500;
}

.addon-price {
  font-weight: 600;
  opacity: 0.9;
}

.addon-unit {
  font-size: 0.75rem;
  opacity: 0.7;
}

/* Redesigned Variants Selection */
.variants-section {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  margin-bottom: 0.5rem;
}

.section-label {
  font-size: 0.85rem;
  opacity: 0.95;
  font-weight: 600;
  letter-spacing: 0.03em;
}

.variants-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.variant-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 0.75rem;
  padding: 1rem 1.25rem;
  color: white;
  cursor: pointer;
  text-align: left;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  width: 100%;
}

.variant-card:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.4);
  transform: translateY(-1px);
}

.variant-card.active {
  background: rgba(255, 255, 255, 0.2);
  border-color: #22c55e;
  box-shadow: 0 4px 15px rgba(34, 197, 94, 0.2);
}

.variant-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  flex: 1;
  padding-right: 1rem;
}

.variant-name {
  font-weight: 600;
  font-size: 0.95rem;
  line-height: 1.4;
}

.variant-desc {
  font-size: 0.8rem;
  opacity: 0.75;
  line-height: 1.4;
}

.variant-price {
  font-weight: 700;
  font-size: 0.95rem;
  color: #22c55e;
  white-space: nowrap;
}

.variant-card.active .variant-price {
  color: #4ade80;
}

/* Checked Addon styling */
.addon-checkbox-label.checked {
  background: rgba(34, 197, 94, 0.15);
  border-color: rgba(34, 197, 94, 0.4);
}

.tier-warning {
  margin-top: 0.5rem;
  padding: 0.75rem 1rem;
  background: rgba(245, 158, 11, 0.2);
  border-radius: 0.75rem;
  color: #fef3c7;
  font-size: 0.9rem;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.error-text {
  color: #fecaca;
  font-size: 0.9rem;
  font-weight: 500;
}

@media (max-width: 768px) {
  .tour-detail-page {
    padding: 1.5rem 0.75rem;
  }

  .slider-wrapper {
    border-radius: 1rem;
    min-height: 280px;
  }

  .hero-slides {
    height: 280px;
  }

  .hero-summary {
    padding: 1.25rem;
  }
}

@media (max-width: 480px) {
  .tour-detail-page {
    padding: 1rem 0.5rem;
  }

  .detail-wrapper {
    gap: 1rem;
  }

  .panel-card {
    padding: 1rem;
    border-radius: 1rem;
  }

  .booking-panel {
    padding: 1.25rem;
    border-radius: 1rem;
  }

  .price {
    font-size: 1.75rem;
  }

  .slider-wrapper {
    border-radius: 0.75rem;
    min-height: 220px;
  }

  .hero-slides {
    height: 220px;
  }

  h1 {
    font-size: 1.5rem;
  }
}
</style>
