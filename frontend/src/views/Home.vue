<template>
  <!-- Landing Page mode -->
  <YoloOceanCamp
    v-if="settingsStore.homepageLandingEnabled"
    :banner-image="settingsStore.homepageBannerImage"
    :wheel-image="settingsStore.homepageWheelImage"
    :cloud-image1="settingsStore.homepageCloudImage1"
    :cloud-image2="settingsStore.homepageCloudImage2"
    :cloud-image3="settingsStore.homepageCloudImage3"
    :cta-text="
      settingsStore.homepageCtaText ||
      $t('home.landing_cta', 'Đặt ngay và tiết kiệm 10%')
    "
    :cta-sub="
      settingsStore.homepageCtaSub ||
      $t('home.landing_cta_sub', 'Chỉ khả dụng trên Website')
    "
    :tagline="
      settingsStore.homepageTagline ||
      $t(
        'home.landing_tagline',
        'Chỉ cần đặt chỗ trước - Không cần thẻ tín dụng - Thanh toán khi tham gia tour',
      )
    "
    :cta-link="settingsStore.homepageCtaLink"
    :sig-line1="settingsStore.homepageSignatureLine1"
    :sig-line2="settingsStore.homepageSignatureLine2"
  />

  <!-- Default Home Page -->
  <div v-else class="home">
    <!-- Hero Section -->
    <section class="relative overflow-hidden hero-section">
      <div
        class="absolute inset-0 opacity-80 bg-[radial-gradient(circle_at_top,#3b82f6,transparent_55%)]"
      ></div>
      <div class="absolute inset-0 hero-noise"></div>
      <div class="relative container mx-auto px-4 py-20 lg:py-28 text-white">
        <div class="grid lg:grid-cols-[1.1fr_0.9fr] gap-10 items-center">
          <div class="fade-up">
            <span class="pill mb-6 inline-flex"
              >✨
              {{
                settingsStore.heroTagline ||
                $t("home.tagline", "Retreat in style")
              }}</span
            >
            <h1
              class="text-4xl md:text-5xl font-semibold leading-tight mb-6 hero-title"
            >
              {{ settingsStore.heroTitle || $t("home.hero_title") }}
            </h1>
            <p class="text-lg text-slate-100/80 mb-8 max-w-2xl">
              {{ settingsStore.heroSubtitle || $t("home.hero_subtitle") }}
            </p>
            <div class="flex flex-wrap gap-4 fade-up fade-delay-1">
              <router-link
                to="/tours"
                class="btn btn-primary shadow-lg shadow-blue-500/30"
              >
                {{ $t("home.explore_tours", "Khám phá tour") }}
              </router-link>
              <router-link
                to="/rooms"
                class="btn btn-secondary border border-white/30 text-white bg-transparent"
              >
                {{ $t("home.search_button") }}
              </router-link>
            </div>
            <div
              class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-12 fade-up fade-delay-2"
            >
              <div
                v-for="stat in heroHighlights"
                :key="stat.label"
                class="glass-tile text-center"
              >
                <div class="text-3xl font-semibold">{{ stat.value }}</div>
                <p class="text-sm text-slate-100/80">{{ stat.label }}</p>
              </div>
            </div>
          </div>
          <div class="space-y-8 fade-up fade-delay-3">
            <div class="hero-gallery">
              <div class="hero-gallery-main">
                <img
                  :src="heroImages[0]"
                  alt="seaside happy island tour"
                  loading="lazy"
                />
                <div class="hero-gallery-chip">
                  <span>🌅</span>
                  {{
                    settingsStore.heroGalleryCaption ||
                    $t("home.nature_escape", "Ẩn mình bên vịnh xanh")
                  }}
                </div>
              </div>
              <div class="hero-gallery-stack">
                <img
                  v-for="(image, galleryIndex) in heroImages.slice(1)"
                  :key="galleryIndex"
                  :src="image"
                  :alt="`happy-island-tour-${galleryIndex}`"
                  loading="lazy"
                />
              </div>
              <div class="hero-gallery-floating">
                <p class="text-lg font-semibold">
                  {{
                    settingsStore.heroFloatingTitle ||
                    $t(
                      "home.signature_retreat",
                      "Trải nghiệm signature retreat",
                    )
                  }}
                </p>
                <span class="text-sm text-slate-200">{{
                  settingsStore.heroFloatingSubtitle ||
                  $t("home.memories", "+320 khoảnh khắc được lưu giữ")
                }}</span>
              </div>
            </div>
            <div class="glass-panel p-6 md:p-8 space-y-6">
              <div class="flex items-center gap-3">
                <span class="badge">{{
                  $t("home.curated", "Handpicked")
                }}</span>
                <span class="text-sm text-slate-300">{{
                  $t("home.luxury_rooms", "Luxury rooms & tours")
                }}</span>
              </div>
              <div class="space-y-5">
                <div
                  v-for="perk in heroPerks"
                  :key="perk.title"
                  class="flex items-start gap-4"
                >
                  <div class="perk-icon">{{ perk.icon }}</div>
                  <div>
                    <p class="font-semibold text-lg">{{ perk.title }}</p>
                    <p class="text-sm text-slate-300">{{ perk.subtitle }}</p>
                  </div>
                </div>
              </div>
              <div
                class="flex items-center gap-4 pt-4 border-t border-white/10"
              >
                <img
                  src="https://i.pravatar.cc/64?img=49"
                  alt="guests"
                  class="w-12 h-12 rounded-full border border-white/30"
                />
                <div>
                  <p class="font-semibold">
                    {{
                      settingsStore.heroTrustTitle ||
                      $t("home.trust_title", "5k+ khách hài lòng")
                    }}
                  </p>
                  <p class="text-sm text-slate-300">
                    {{
                      settingsStore.heroTrustSubtitle ||
                      $t("home.trust_sub", "Được đánh giá 4.9/5 trên toàn cầu")
                    }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Popular Tours -->
    <section class="py-16 section-wrap alt">
      <div class="container mx-auto px-4">
        <div class="section-head">
          <div>
            <p class="section-pill">
              {{ $t("home.tour_tag", "Signature journeys") }}
            </p>
            <h2 class="section-title">{{ $t("home.popular_tours") }}</h2>
          </div>
          <router-link to="/tours" class="btn btn-secondary">{{
            $t("home.view_all_tours", "Xem tour")
          }}</router-link>
        </div>
        <div v-if="loadingTours" class="spinner"></div>
        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            v-for="(tour, tourIndex) in tours"
            :key="tour.id"
            class="card tour-card fade-up"
            :style="{ animationDelay: `${tourIndex * 0.12}s` }"
          >
            <div class="relative">
              <img
                :src="tour.images?.[0] || '/placeholder-tour.jpg'"
                :alt="tour.name"
                class="w-full h-56 object-cover"
              />
              <span class="tag">{{
                $t("tours.duration", { days: tour.duration_days || 3 })
              }}</span>
            </div>
            <div class="p-5 space-y-3">
              <h3 class="text-2xl font-semibold text-slate-900">
                {{ tour.name }}
              </h3>
              <p class="text-slate-500 line-clamp-3">
                {{ truncateText(tour.description, 100) }}
              </p>
              <div class="flex items-center justify-between pt-2">
                <span class="text-blue-600 font-bold">{{
                  formatPrice(tour.price_per_person)
                }}</span>
                <router-link
                  :to="`/tours/${tour.id}`"
                  class="text-blue-600 font-semibold flex items-center gap-1"
                >
                  {{ $t("tours.book_tour") }}
                  <span aria-hidden="true">→</span>
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Promo Banner 1 -->
    <section v-if="settingsStore.banner1.active" class="py-8 section-wrap alt">
      <div class="container mx-auto px-4">
        <PromoBanner
          :banner="{
            ...settingsStore.banner1,
            link: settingsStore.banner1.link || '/tours',
          }"
          variant="gradient"
          :tag="$t('home.tour_tag', 'Signature journeys')"
          :button-text="$t('home.explore_tours', 'Explore tours')"
        />
      </div>
    </section>

    <!-- Featured Rooms -->
    <section class="py-16 section-wrap">
      <div class="container mx-auto px-4">
        <div class="section-head">
          <div>
            <p class="section-pill">
              {{ $t("home.rooms_tag", "Stay & relax") }}
            </p>
            <h2 class="section-title">{{ $t("home.featured_rooms") }}</h2>
          </div>
          <router-link to="/rooms" class="btn btn-secondary">{{
            $t("home.view_all_rooms", "Xem tất cả phòng")
          }}</router-link>
        </div>
        <div v-if="loading" class="spinner"></div>
        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            v-for="(room, roomIndex) in rooms"
            :key="room.id"
            class="card room-card fade-up"
            :style="{ animationDelay: `${roomIndex * 0.12}s` }"
          >
            <div class="relative">
              <img
                :src="room.images?.[0] || '/placeholder-room.jpg'"
                :alt="room.name"
                class="w-full h-56 object-cover"
              />
              <span class="price-pill">{{
                formatPrice(room.price_per_night)
              }}</span>
              <div
                v-if="room.occupancy"
                class="occupancy-flag"
                :class="
                  room.occupancy.is_currently_occupied
                    ? 'busy'
                    : room.occupancy.next_check_in
                      ? 'soon'
                      : 'free'
                "
              >
                {{ occupancyFlag(room) }}
              </div>
            </div>
            <div class="p-5 space-y-3">
              <h3 class="text-2xl font-semibold text-slate-900">
                {{ room.name }}
              </h3>
              <p class="text-slate-500 line-clamp-3">
                {{ truncateText(room.description, 100) }}
              </p>
              <div class="flex items-center justify-between pt-2">
                <span class="text-sm text-slate-400"
                  >{{ room.capacity }} {{ $t("rooms.guests", "khách") }}</span
                >
                <router-link
                  :to="`/rooms/${room.id}`"
                  class="text-blue-600 font-semibold flex items-center gap-1"
                >
                  {{ $t("rooms.view_details") }}
                  <span aria-hidden="true">→</span>
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Car Rentals -->
    <section class="py-16 section-wrap">
      <div class="container mx-auto px-4">
        <div class="section-head">
          <div>
            <p class="section-pill">
              {{ $t("car_rentals.subtitle", "Find the right vehicle") }}
            </p>
            <h2 class="section-title">
              {{ $t("car_rentals.title", "Car Rentals") }}
            </h2>
          </div>
          <router-link to="/car-rentals" class="btn btn-secondary">
            {{ $t("car_rentals.view_all", "View all cars") }}
          </router-link>
        </div>
        <div v-if="loadingCarRentals" class="spinner"></div>
        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            v-for="(car, carIndex) in carRentals"
            :key="car.id"
            class="card tour-card fade-up"
            :style="{ animationDelay: `${carIndex * 0.12}s` }"
          >
            <div class="relative">
              <img
                :src="car.images?.[0] || '/placeholder-car.jpg'"
                :alt="car.name"
                class="w-full h-56 object-cover"
              />
              <span class="tag bg-slate-900">{{ car.type || "car" }}</span>
              <div
                class="absolute top-3 right-3 bg-white rounded-full px-3 py-1 text-sm font-semibold text-slate-900"
              >
                {{ formatPrice(car.price_per_day) }}/day
              </div>
            </div>
            <div class="p-5 space-y-3">
              <h3 class="text-2xl font-semibold text-slate-900">
                {{ car.name }}
              </h3>
              <p class="text-slate-500 line-clamp-3">
                {{
                  truncateText(car.short_description || car.description, 100)
                }}
              </p>
              <div class="text-sm text-gray-600 mb-3">
                <div class="flex items-center mb-1">
                  <svg
                    class="w-4 h-4 mr-2"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                      clip-rule="evenodd"
                    />
                  </svg>
                  {{ car.location || "Cat Ba" }}
                </div>
                <div class="flex items-center">
                  <svg
                    class="w-4 h-4 mr-2"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"
                    />
                  </svg>
                  {{ car.seats }} seats · {{ car.transmission || "auto" }}
                </div>
              </div>
              <div class="flex gap-2 pt-2">
                <router-link
                  :to="`/car-rentals/${car.id}`"
                  class="flex-1 bg-slate-900 hover:bg-slate-800 text-white text-center py-2 px-4 rounded-lg transition-colors text-sm"
                >
                  View Details
                </router-link>
                <a
                  :href="
                    car.contact_whatsapp
                      ? `https://wa.me/${car.contact_whatsapp}`
                      : '#'
                  "
                  target="_blank"
                  class="bg-emerald-600 hover:bg-emerald-700 text-white py-2 px-3 rounded-lg transition-colors text-sm"
                  :class="{
                    'opacity-60 pointer-events-none': !car.contact_whatsapp,
                  }"
                  title="Contact via WhatsApp"
                >
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path
                      d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"
                    />
                  </svg>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Promo Banner 2 -->
    <section v-if="settingsStore.banner2.active" class="py-8 section-wrap">
      <div class="container mx-auto px-4">
        <PromoBanner
          :banner="settingsStore.banner2"
          variant="default"
          :tag="$t('home.discover', 'Khám phá')"
          :button-text="$t('home.explore', 'Khám phá ngay')"
        />
      </div>
    </section>

    <!-- Latest Blog Posts -->
    <section class="py-16 section-wrap">
      <div class="container mx-auto px-4">
        <div class="section-head">
          <div>
            <p class="section-pill">
              {{ $t("home.blog_tag", "Stories & tips") }}
            </p>
            <h2 class="section-title">{{ $t("home.latest_blog") }}</h2>
          </div>
        </div>
        <div v-if="loadingBlog" class="spinner"></div>
        <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div
            v-for="(post, blogIndex) in blogPosts"
            :key="blogIndex"
            class="card blog-card"
          >
            <img
              :src="post.featured_image || '/placeholder-blog.jpg'"
              :alt="post.title"
              class="w-full h-48 object-cover"
            />
            <div class="p-5 space-y-3">
              <p class="text-sm uppercase tracking-[0.2em] text-slate-400">
                {{ post.category?.name || "Travel" }}
              </p>
              <h3 class="text-2xl font-semibold text-slate-900">
                {{ post.title }}
              </h3>
              <p class="text-slate-500 line-clamp-3">{{ post.excerpt }}</p>
              <router-link
                :to="`/blog/${post.slug}`"
                class="text-blue-600 font-semibold flex items-center gap-1"
              >
                {{ $t("blog.read_more") }} <span aria-hidden="true">→</span>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import { useSettingsStore } from "@/stores/settings";
import PromoBanner from "@/components/PromoBanner.vue";
import YoloOceanCamp from "@/views/YoloOceanCamp.vue";

const settingsStore = useSettingsStore();
const rooms = ref([]);
const tours = ref([]);
const carRentals = ref([]);
const blogPosts = ref([]);
const loading = ref(true);
const loadingTours = ref(true);
const loadingCarRentals = ref(true);
const loadingBlog = ref(true);
const { locale, t } = useI18n({ useScope: "global" });

// Use settings store for hero content
const heroHighlights = computed(() => settingsStore.heroStats);
const heroPerks = computed(() => settingsStore.heroPerks);
const heroImages = computed(() => settingsStore.heroImages);

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

const truncateText = (text, length = 100) => {
  const stripped = stripHtml(text);
  return stripped.length > length
    ? stripped.substring(0, length) + "..."
    : stripped;
};

const occupancyFlag = (room) => {
  if (!room.occupancy) return "";
  if (room.occupancy.is_currently_occupied) {
    return t("rooms.occupied_now", "Đang có khách");
  }
  if (room.occupancy.next_check_in) {
    return `${t("rooms.next_booking_short", "Khách tiếp theo")}: ${
      room.occupancy.next_check_in
    }`;
  }
  return t("rooms.available_now", "Sẵn sàng nhận khách");
};

const loadHomeData = async () => {
  loading.value = true;
  loadingTours.value = true;
  loadingCarRentals.value = true;
  loadingBlog.value = true;
  try {
    const [roomsRes, toursRes, carRentalsRes, blogRes] = await Promise.all([
      api.get("/rooms", { params: { per_page: 3 } }),
      api.get("/tours", { params: { per_page: 3 } }),
      api.get("/car-rentals/featured"),
      api.get("/blog/posts", { params: { per_page: 3 } }),
    ]);

    rooms.value = roomsRes.data.data;
    tours.value = toursRes.data.data;
    carRentals.value = carRentalsRes.data.success
      ? carRentalsRes.data.data.slice(0, 3)
      : [];
    blogPosts.value = blogRes.data.data;
  } catch (error) {
    console.error("Error loading home data:", error);
  } finally {
    loading.value = false;
    loadingTours.value = false;
    loadingCarRentals.value = false;
    loadingBlog.value = false;
  }
};

onMounted(() => {
  loadHomeData();
  settingsStore.fetchSettings();
});
watch(locale, () => {
  loadHomeData();
  settingsStore.refreshSettings();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.hero-section {
  background: radial-gradient(circle at top, #0f172a, #020617 65%);
}

.hero-title {
  text-shadow: 0 20px 45px rgba(15, 23, 42, 0.35);
}

.hero-noise {
  background-image: url("https://www.transparenttextures.com/patterns/asfalt-dark.png");
  opacity: 0.15;
}

.hero-gallery {
  position: relative;
  display: grid;
  gap: 1rem;
}

.hero-gallery-main {
  position: relative;
  border-radius: 1.75rem;
  overflow: hidden;
  height: 320px;
  box-shadow: 0 35px 70px rgba(2, 6, 23, 0.6);
}

.hero-gallery-main img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transform: scale(1.05);
  transition: transform 6s ease;
}

.hero-gallery-main:hover img {
  transform: scale(1.1);
}

.hero-gallery-chip {
  position: absolute;
  left: 1.25rem;
  bottom: 1.25rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1.1rem;
  border-radius: 999px;
  background: rgba(2, 6, 23, 0.75);
  backdrop-filter: blur(12px);
  font-weight: 600;
}

.hero-gallery-stack {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
  margin-left: auto;
  width: 80%;
}

.hero-gallery-stack img {
  border-radius: 1.25rem;
  width: 100%;
  height: 140px;
  object-fit: cover;
  box-shadow: 0 20px 40px rgba(2, 6, 23, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.hero-gallery-floating {
  position: absolute;
  right: 1.75rem;
  bottom: -1.75rem;
  background: rgba(15, 23, 42, 0.9);
  border-radius: 1rem;
  padding: 1.25rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  border: 1px solid rgba(255, 255, 255, 0.15);
  box-shadow: 0 25px 50px rgba(2, 6, 23, 0.55);
}

.glass-panel {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.18);
  backdrop-filter: blur(24px);
  box-shadow: 0 25px 50px rgba(2, 6, 23, 0.45);
}

.glass-tile {
  background: rgba(15, 23, 42, 0.35);
  border-radius: 1rem;
  padding: 1rem;
  backdrop-filter: blur(18px);
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.perk-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.15);
  display: grid;
  place-items: center;
  font-size: 1.25rem;
}

.badge {
  padding: 0.35rem 0.9rem;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  font-size: 0.8rem;
  letter-spacing: 0.05em;
}

.section-wrap {
  background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
}

.section-wrap.alt {
  background: linear-gradient(180deg, #f1f5f9 0%, #e0f2fe 100%);
}

.section-head {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 2.5rem;
}

.section-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.85rem;
  color: #6366f1;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.room-card .price-pill,
.tour-card .tag {
  position: absolute;
  top: 1rem;
  left: 1rem;
  padding: 0.35rem 0.9rem;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
  background: rgba(15, 23, 42, 0.8);
  color: #fff;
}

.occupancy-flag {
  position: absolute;
  left: 1rem;
  bottom: 1rem;
  padding: 0.25rem 0.85rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #fff;
  backdrop-filter: blur(8px);
}

.occupancy-flag.busy {
  background: rgba(239, 68, 68, 0.85);
}

.occupancy-flag.soon {
  background: rgba(251, 191, 36, 0.92);
  color: #1f2937;
}

.occupancy-flag.free {
  background: rgba(16, 185, 129, 0.85);
}

.blog-card {
  border: 1px solid rgba(99, 102, 241, 0.08);
}

@media (max-width: 768px) {
  .hero-section {
    text-align: center;
  }

  .glass-panel {
    order: -1;
  }

  .hero-gallery-main {
    height: 240px;
  }

  .hero-gallery-stack {
    width: 100%;
  }

  .hero-gallery-floating {
    position: static;
    margin-top: 0.5rem;
  }
}

@media (max-width: 480px) {
  .hero-section .container {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .hero-gallery-main {
    height: 200px;
  }

  .hero-gallery-stack {
    grid-template-columns: 1fr;
  }

  .hero-gallery-chip {
    font-size: 0.75rem;
    padding: 0.5rem 0.85rem;
  }

  .section-head {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .section-head .btn {
    width: 100%;
    justify-content: center;
  }
}
</style>
