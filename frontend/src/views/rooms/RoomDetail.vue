<template>
  <div class="room-detail-page">
    <div v-if="loading" class="detail-loading">
      <div class="skeleton hero"></div>
      <div class="skeleton text-row"></div>
      <div class="skeleton text-row short"></div>
    </div>

    <div v-else-if="room" class="detail-wrapper">
      <!-- SEO Breadcrumb -->
      <div class="breadcrumb-container">
        <Breadcrumb :items="breadcrumbItems" />
      </div>

      <div class="detail-header">
        <button class="ghost-btn" @click="$router.back()">
          ← {{ $t("common.back") }}
        </button>
        <div class="header-meta">
          <span v-if="room.room_category?.name" class="chip">{{
            room.room_category.name
          }}</span>
          <span class="chip status" :class="room.status">{{
            room.status
          }}</span>
          <span class="chip views">👁️ {{ room.view_count || 0 }}</span>
        </div>
      </div>

      <section class="room-hero">
        <div
          class="slider-wrapper"
          @mouseenter="pauseSlider"
          @mouseleave="resumeSlider"
        >
          <div v-if="!galleryImages.length" class="hero-fallback">
            <p>{{ $t("rooms.no_images", "No images yet") }}</p>
          </div>
          <div v-else class="hero-slides">
            <article v-if="currentImage" class="hero-slide active">
              <img :src="currentImage" :alt="slideAlt(currentSlide)" />
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
          <p class="eyebrow">{{ room.type || $t("rooms.room", "Room") }}</p>
          <h1>{{ room.name }}</h1>
          <p v-if="heroSummary" class="lead">{{ heroSummary }}</p>

          <div class="metrics-grid">
            <div>
              <span>{{ room.capacity }}</span>
              <small>{{ $t("rooms.capacity") }}</small>
            </div>
            <div>
              <span>{{ room.beds }}</span>
              <small>{{ $t("rooms.beds") }}</small>
            </div>
            <div>
              <span>{{ room.bathrooms }}</span>
              <small>{{ $t("rooms.bathrooms") }}</small>
            </div>
            <div>
              <span>{{ room.size }} m²</span>
              <small>{{ $t("rooms.size", "Diện tích") }}</small>
            </div>
          </div>

          <div class="rating-row">
            <div class="rating-pill">
              ⭐ {{ room.average_rating?.toFixed(1) || "N/A" }}
            </div>
            <p>{{ room.reviews_count || 0 }} {{ $t("rooms.reviews") }}</p>
          </div>

          <div v-if="room.occupancy" class="occupancy-card">
            <p v-if="room.occupancy.is_currently_occupied">
              {{ $t("rooms.currently_occupied", "Currently occupied until") }}
              <strong>{{
                formatDate(room.occupancy.current_stay?.check_out)
              }}</strong>
            </p>
            <p v-else-if="room.occupancy.next_check_in">
              {{ $t("rooms.next_available", "Next check-in") }}
              <strong>{{ formatDate(room.occupancy.next_check_in) }}</strong>
            </p>
            <p v-else>{{ $t("rooms.ready_to_book", "Ready to book now") }}</p>
          </div>
        </div>
      </section>

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

      <section class="detail-grid">
        <article class="info-panel">
          <div class="panel-card">
            <h2>{{ $t("common.description", "Description") }}</h2>
            <p class="body-text" v-html="formattedDescription"></p>
          </div>

          <div class="panel-card">
            <div class="panel-head">
              <div>
                <h2>{{ $t("rooms.amenities") }}</h2>
                <p>
                  {{
                    $t(
                      "rooms.amenities_hint",
                      "Everything included with this stay"
                    )
                  }}
                </p>
              </div>
              <span class="chip subtle"
                >{{ room.amenities?.length || 0 }} items</span
              >
            </div>
            <div class="amenities-grid">
              <div
                v-for="amenity in highlightedAmenities"
                :key="amenity"
                class="amenity-item"
              >
                <span>✓</span>
                <p>{{ amenity }}</p>
              </div>
            </div>
            <div v-if="remainingAmenities.length" class="extra-amenities">
              <details>
                <summary>
                  {{ $t("rooms.show_more", "Show more amenities") }}
                </summary>
                <div class="extra-grid">
                  <p v-for="item in remainingAmenities" :key="item">
                    {{ item }}
                  </p>
                </div>
              </details>
            </div>
          </div>

          <div class="panel-card">
            <div class="panel-head">
              <h2>{{ $t("rooms.reviews") }}</h2>
              <p>
                {{ room.reviews_count || 0 }}
                {{ $t("rooms.reviews_total", "verified stays") }}
              </p>
            </div>
            <div v-if="room.reviews?.length" class="reviews-grid">
              <article
                v-for="review in room.reviews"
                :key="review.id"
                class="review-card"
              >
                <header>
                  <p>{{ review.user?.name || "Guest" }}</p>
                  <span>⭐ {{ review.rating }}</span>
                </header>
                <p>{{ review.comment }}</p>
              </article>
            </div>
            <p v-else class="muted">
              {{ $t("rooms.no_reviews", "No reviews yet") }}
            </p>
          </div>

          <!-- Guest Reviews Section -->
          <ReviewsList ref="reviewsListRef" type="room" :item-id="room.id" />

          <!-- Review Form -->
          <ReviewForm
            type="room"
            :item-id="room.id"
            @submitted="onReviewSubmitted"
          />
        </article>

        <aside class="booking-panel">
          <div class="price-row">
            <div>
              <p class="per-night">/{{ $t("rooms.per_night") }}</p>
              <p class="price">{{ formatPrice(roomPrice) }}</p>
            </div>
            <div v-if="room.discount_price" class="discount">
              <span class="chip subtle">{{ $t("rooms.save", "Save") }}</span>
              <p>{{ formatPrice(room.price_per_night) }}</p>
            </div>
          </div>

          <form @submit.prevent="handleBooking" class="booking-form">
            <div class="date-time-row">
              <label class="date-field">
                <span>{{ $t("rooms.check_in") }}</span>
                <input
                  v-model="bookingForm.check_in"
                  :min="today"
                  :lang="inputLang"
                  type="date"
                  required
                />
              </label>
              <label class="time-field">
                <span>{{ $t("rooms.time", "Giờ") }}</span>
                <select v-model="bookingForm.check_in_time">
                  <option
                    v-for="time in checkInTimes"
                    :key="time"
                    :value="time"
                  >
                    {{ time }}
                  </option>
                </select>
              </label>
            </div>
            <div class="date-time-row">
              <label class="date-field">
                <span>{{ $t("rooms.check_out") }}</span>
                <input
                  v-model="bookingForm.check_out"
                  :min="bookingForm.check_in || today"
                  :lang="inputLang"
                  type="date"
                  required
                />
              </label>
              <label class="time-field">
                <span>{{ $t("rooms.time", "Giờ") }}</span>
                <select v-model="bookingForm.check_out_time">
                  <option
                    v-for="time in checkOutTimes"
                    :key="time"
                    :value="time"
                  >
                    {{ time }}
                  </option>
                </select>
              </label>
            </div>
            <label>
              <span>{{ $t("rooms.guests") }}</span>
              <input
                v-model.number="bookingForm.guests"
                type="number"
                min="1"
                :max="room.capacity"
                required
              />
            </label>

            <div v-if="totalPrice" class="totals">
              <div>
                <span>{{ nights }} nights</span>
                <span>{{ formatPrice(roomPrice * nights) }}</span>
              </div>
              <div>
                <span>Tax (10%)</span>
                <span>{{ formatPrice(roomPrice * nights * 0.1) }}</span>
              </div>
              <div class="grand">
                <span>Total</span>
                <span>{{ formatPrice(totalPrice) }}</span>
              </div>
            </div>

            <button type="submit" class="cta" :disabled="bookingLoading">
              {{ bookingLoading ? $t("common.loading") : $t("rooms.book_now") }}
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

const room = ref(null);
const loading = ref(true);
const bookingLoading = ref(false);
const reviewsListRef = ref(null);

const onReviewSubmitted = () => {
  // Reviews will refresh when approved by admin
};
const bookingError = ref("");

const today = computed(() => dayjs().format("YYYY-MM-DD"));

// Time options for check-in (typically 14:00 - 22:00)
const checkInTimes = [
  "14:00",
  "14:30",
  "15:00",
  "15:30",
  "16:00",
  "16:30",
  "17:00",
  "17:30",
  "18:00",
  "18:30",
  "19:00",
  "19:30",
  "20:00",
  "20:30",
  "21:00",
  "21:30",
  "22:00",
];

// Time options for check-out (typically 06:00 - 12:00)
const checkOutTimes = [
  "06:00",
  "06:30",
  "07:00",
  "07:30",
  "08:00",
  "08:30",
  "09:00",
  "09:30",
  "10:00",
  "10:30",
  "11:00",
  "11:30",
  "12:00",
];

const bookingForm = ref({
  check_in: "",
  check_in_time: "14:00",
  check_out: "",
  check_out_time: "12:00",
  guests: 1,
});

const nights = computed(() => {
  if (!bookingForm.value.check_in || !bookingForm.value.check_out) return 0;
  return dayjs(bookingForm.value.check_out).diff(
    dayjs(bookingForm.value.check_in),
    "day"
  );
});

const roomPrice = computed(
  () => room.value?.discount_price || room.value?.price_per_night || 0
);

const totalPrice = computed(() => {
  if (nights.value <= 0) return 0;
  const subtotal = roomPrice.value * nights.value;
  const tax = subtotal * 0.1;
  return subtotal + tax;
});

const galleryImages = computed(() => {
  if (!room.value) return [];
  const ordered = [];
  const seen = new Set();
  const sources = [];

  if (room.value.cover_image) {
    sources.push(room.value.cover_image);
  }

  if (Array.isArray(room.value.images) && room.value.images.length) {
    sources.push(...room.value.images);
  }

  const albumItems = room.value.media_album?.media_items || [];
  if (albumItems.length) {
    sources.push(...albumItems.map((item) => item.url));
  }

  sources.forEach((image) => {
    if (!image || seen.has(image)) {
      return;
    }
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
  const index = Math.min(currentSlide.value, images.length - 1);
  return images[index];
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
  sliderTimer.value = setInterval(() => {
    nextSlide();
  }, 5000);
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
  if (index >= 0 && index < galleryImages.value.length) {
    currentSlide.value = index;
  }
};

const formatPrice = (price) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(price);
};

const formatDate = (date) => {
  return date ? dayjs(date).format("DD/MM/YYYY") : "";
};

const breadcrumbItems = computed(() => [
  { label: t("nav.home"), path: "/" },
  { label: t("rooms.title"), path: "/rooms" },
  {
    label: room.value?.name || t("rooms.room"),
    path: `/rooms/${room.value?.id}`,
  },
]);

const heroSummary = computed(() => {
  if (!room.value) return "";
  const raw = room.value.meta_description || room.value.description || "";
  const stripped = raw
    .replace(/<[^>]+>/g, " ")
    .replace(/\s+/g, " ")
    .trim();
  return stripped.slice(0, 200);
});

const formattedDescription = computed(() => {
  return (room.value?.description || "").replace(/\n/g, "<br />");
});

const slideAlt = (index) => {
  const base = room.value?.name || t("rooms.room", "Room");
  return `${base} – slide ${index + 1}`;
};

const highlightedAmenities = computed(() => {
  return (room.value?.amenities || []).slice(0, 8);
});

const remainingAmenities = computed(() => {
  return (room.value?.amenities || []).slice(8);
});

const handleBooking = () => {
  if (
    !bookingForm.value.check_in ||
    !bookingForm.value.check_out ||
    nights.value <= 0
  ) {
    bookingError.value = "Please select valid dates";
    return;
  }

  bookingError.value = "";

  // Redirect to booking form page (works for both guest and authenticated users)
  router.push({
    name: "BookingForm",
    query: {
      roomId: room.value.id,
      checkIn: bookingForm.value.check_in,
      checkInTime: bookingForm.value.check_in_time,
      checkOut: bookingForm.value.check_out,
      checkOutTime: bookingForm.value.check_out_time,
      guests: bookingForm.value.guests,
    },
  });
};

const loadRoom = async () => {
  loading.value = true;
  try {
    const response = await api.get(`/rooms/${route.params.id}`);
    room.value = response.data;
  } catch (error) {
    console.error("Error loading room:", error);
    if (!room.value) {
      router.push("/rooms");
    }
  } finally {
    loading.value = false;
  }
};

watch(locale, () => {
  loadRoom();
});

watch(galleryImages, (images) => {
  currentSlide.value = 0;
  if (images.length > 1) {
    startSlider();
  } else {
    stopSlider();
  }
});

onMounted(() => {
  loadRoom();
  if (galleryImages.value.length > 1) {
    startSlider();
  }
});

onBeforeUnmount(() => {
  stopSlider();
});
</script>

<style scoped>
.room-detail-page {
  min-height: 100vh;
  background: radial-gradient(
      circle at top,
      rgba(79, 70, 229, 0.08),
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
}

.ghost-btn {
  border: none;
  background: transparent;
  color: #2563eb;
  font-weight: 600;
  cursor: pointer;
  padding: 0.4rem 0.8rem;
  border-radius: 999px;
  transition: background 0.2s ease;
}

.ghost-btn:hover {
  background: rgba(37, 99, 235, 0.1);
}

.header-meta {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.chip {
  padding: 0.2rem 0.8rem;
  border-radius: 999px;
  font-size: 0.8rem;
  background: rgba(15, 23, 42, 0.06);
  text-transform: capitalize;
}

.chip.views {
  background: rgba(59, 130, 246, 0.15);
  color: #1d4ed8;
}

.chip.subtle {
  background: rgba(148, 163, 184, 0.2);
  color: #475569;
}

.chip.status.available {
  background: rgba(34, 197, 94, 0.18);
  color: #047857;
}

.chip.status.unavailable {
  background: rgba(248, 113, 113, 0.2);
  color: #b91c1c;
}

.chip.status.maintenance {
  background: rgba(251, 191, 36, 0.25);
  color: #92400e;
}

.chip.payment.confirmed {
  background: rgba(34, 197, 94, 0.18);
  color: #047857;
}

.chip.payment.pending {
  background: rgba(251, 191, 36, 0.25);
  color: #92400e;
}

.chip.payment.cancelled {
  background: rgba(248, 113, 113, 0.25);
  color: #b91c1c;
}

.room-hero {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  align-items: stretch;
}

@media (max-width: 900px) {
  .room-hero {
    grid-template-columns: 1fr;
  }
}

.slider-wrapper {
  position: relative;
  border-radius: 1.5rem;
  overflow: hidden;
  min-height: 400px;
  background: linear-gradient(135deg, #1e3a5f, #0f172a);
}

.hero-fallback {
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.6);
  gap: 1rem;
}

.hero-fallback::before {
  content: "🏠";
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
  background: #020617;
}

.hero-slide img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 7s ease;
}

.hero-slide.active img {
  transform: scale(1.05);
}

.slide-gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.05), rgba(0, 0, 0, 0.65));
  z-index: 1;
}

.slide-count {
  position: absolute;
  bottom: 1rem;
  right: 1rem;
  background: rgba(15, 23, 42, 0.75);
  color: white;
  padding: 0.3rem 0.8rem;
  border-radius: 999px;
  font-size: 0.8rem;
  z-index: 2;
}

.hero-fade-enter-active,
.hero-fade-leave-active {
  transition: opacity 0.6s ease;
}

.hero-fade-enter-from,
.hero-fade-leave-to {
  opacity: 0;
}

.slider-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(15, 23, 42, 0.7);
  color: white;
  border: none;
  width: 42px;
  height: 42px;
  border-radius: 999px;
  cursor: pointer;
  font-size: 1.5rem;
  z-index: 3;
}

.slider-nav.prev {
  left: 1rem;
}

.slider-nav.next {
  right: 1rem;
}

.hero-summary {
  background: white;
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 20px 60px rgba(15, 23, 42, 0.08);
}

.eyebrow {
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  color: #94a3b8;
}

.hero-summary h1 {
  font-size: clamp(2rem, 3vw, 3rem);
  margin: 0.5rem 0 1rem;
  color: #0f172a;
}

.lead {
  color: #475569;
  margin-bottom: 1.5rem;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 0.75rem;
  margin-bottom: 1.5rem;
}

.metrics-grid div {
  background: rgba(15, 23, 42, 0.04);
  border-radius: 1rem;
  padding: 0.85rem;
  text-align: center;
  color: #0f172a;
}

.metrics-grid span {
  display: block;
  font-weight: 600;
  font-size: 1.1rem;
}

.metrics-grid small {
  color: #64748b;
}

.rating-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
  color: #475569;
}

.rating-pill {
  background: rgba(250, 204, 21, 0.2);
  color: #b45309;
  padding: 0.4rem 0.9rem;
  border-radius: 999px;
  font-weight: 600;
}

.occupancy-card {
  background: rgba(59, 130, 246, 0.08);
  border-radius: 1rem;
  padding: 1rem;
  color: #1d4ed8;
}

.thumb-strip {
  display: flex;
  gap: 0.75rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.thumb {
  width: 96px;
  height: 72px;
  border-radius: 0.8rem;
  border: 2px solid transparent;
  overflow: hidden;
  padding: 0;
  background: none;
  cursor: pointer;
}

.thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.thumb.active {
  border-color: #6366f1;
}

.detail-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.4fr) minmax(280px, 0.8fr);
  gap: 2rem;
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
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.panel-card h2 {
  margin: 0;
  font-size: 1.4rem;
  color: #0f172a;
}

.panel-head {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}

.panel-head p {
  color: #94a3b8;
  margin-top: 0.3rem;
}

.body-text {
  color: #475569;
  line-height: 1.7;
}

.amenities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 0.85rem;
}

.amenity-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #0f172a;
}

.amenity-item span {
  color: #16a34a;
}

.extra-amenities details {
  cursor: pointer;
  color: #2563eb;
}

.extra-grid {
  margin-top: 0.75rem;
  columns: 2;
  gap: 1rem;
  color: #475569;
}

.reviews-grid {
  display: grid;
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

.booking-panel {
  background: #0f172a;
  color: white;
  border-radius: 1.5rem;
  padding: 2rem;
  position: sticky;
  top: 2rem;
  align-self: flex-start;
  box-shadow: 0 25px 60px rgba(15, 23, 42, 0.6);
}

.price-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 1.5rem;
}

.per-night {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
}

.price {
  font-size: 2.4rem;
  font-weight: 700;
}

.discount p {
  color: rgba(255, 255, 255, 0.7);
  text-decoration: line-through;
}

.booking-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.booking-form label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.9rem;
}

.booking-form input {
  border-radius: 0.9rem;
  border: none;
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.booking-form input:focus {
  outline: 2px solid rgba(96, 165, 250, 0.8);
}

.totals {
  margin: 0.5rem 0 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.totals div {
  display: flex;
  justify-content: space-between;
  color: rgba(255, 255, 255, 0.85);
}

.totals .grand {
  font-weight: 700;
  font-size: 1.1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  padding-top: 0.5rem;
}

.date-time-row {
  display: flex;
  gap: 0.5rem;
}

.date-time-row .date-field {
  flex: 1;
}

.date-time-row .time-field {
  width: 90px;
}

.date-time-row select {
  width: 100%;
  padding: 0.65rem 0.5rem;
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 0.75rem;
  background: rgba(255, 255, 255, 0.1);
  color: white;
  font-size: 0.9rem;
  cursor: pointer;
}

.date-time-row select option {
  background: #1e293b;
  color: white;
}

.cta {
  border: none;
  border-radius: 999px;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  padding: 0.9rem 1rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
}

.cta:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-msg {
  margin-top: 1rem;
  background: rgba(248, 113, 113, 0.2);
  color: #fecaca;
  padding: 0.75rem;
  border-radius: 0.75rem;
}

@media (max-width: 768px) {
  .metrics-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .booking-panel {
    position: static;
  }

  .room-detail-page {
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
  .room-detail-page {
    padding: 1rem 0.5rem;
  }

  .detail-wrapper {
    gap: 1rem;
  }

  .booking-panel {
    padding: 1.25rem;
    border-radius: 1rem;
  }

  .panel-card {
    padding: 1rem;
    border-radius: 1rem;
  }

  .price {
    font-size: 1.75rem;
  }

  .date-time-row {
    flex-direction: column;
  }

  .date-time-row .time-field {
    width: 100%;
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
