<template>
  <div class="restaurant-page">
    <!-- Hero Section -->
    <section class="hero-section" :style="bannerStyle">
      <div class="hero-overlay">
        <div class="hero-content">
          <h1>{{ restaurantName }}</h1>
          <p class="tagline">{{ intro || $t("restaurant.tagline") }}</p>

          <!-- Info Cards -->
          <div class="hero-info-cards">
            <div class="info-card">
              <div class="info-icon">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
              </div>
              <div class="info-text">
                <span class="label">{{
                  $t("restaurant.hours") || "Giờ mở cửa"
                }}</span>
                <span class="value">{{ openingHours }}</span>
              </div>
            </div>

            <div class="info-card">
              <div class="info-icon">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <path
                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"
                  ></path>
                </svg>
              </div>
              <div class="info-text">
                <span class="label">{{
                  $t("restaurant.phone") || "Điện thoại"
                }}</span>
                <span class="value">{{ phone }}</span>
              </div>
            </div>

            <div class="info-card">
              <div class="info-icon">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <path
                    d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"
                  ></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
              </div>
              <div class="info-text">
                <span class="label">{{
                  $t("restaurant.location") || "Địa chỉ"
                }}</span>
                <span class="value">Happy Island Tour</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Category Navigation (Sticky) -->
    <nav class="category-nav" :class="{ sticky: isSticky }">
      <div class="container">
        <div class="nav-scroll">
          <button
            v-for="category in categories"
            :key="category.id"
            :class="['nav-tab', { active: activeCategory === category.id }]"
            @click="scrollToCategory(category.id)"
          >
            {{ getLocalizedName(category) }}
          </button>
        </div>
      </div>
    </nav>

    <!-- Menu Content -->
    <section class="menu-section">
      <div class="container">
        <!-- Loading State -->
        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>{{ $t("restaurant.loading") || "Đang tải thực đơn..." }}</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="categories.length === 0" class="empty-state">
          <div class="empty-icon">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="64"
              height="64"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"></path>
              <path d="M7 2v20"></path>
              <path
                d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"
              ></path>
            </svg>
          </div>
          <h3>
            {{
              $t("restaurant.menu_coming_soon") ||
              "Thực đơn sẽ sớm được cập nhật"
            }}
          </h3>
        </div>

        <!-- Categories & Items -->
        <div v-else class="menu-categories">
          <div
            v-for="(category, index) in categories"
            :key="category.id"
            :id="'category-' + category.id"
            class="category-section"
          >
            <!-- Category Header -->
            <div
              class="category-header"
              :style="getCategoryHeaderStyle(category, index)"
            >
              <div class="category-header-overlay">
                <div class="category-header-content">
                  <h2>{{ getLocalizedName(category) }}</h2>
                  <p
                    v-if="getLocalizedDescription(category)"
                    class="category-desc"
                  >
                    {{ getLocalizedDescription(category) }}
                  </p>
                  <span class="item-count"
                    >{{ category.items?.length || 0 }}
                    {{ $t("restaurant.items") || "món" }}</span
                  >
                </div>
              </div>
            </div>

            <!-- Menu Items Grid -->
            <div class="items-grid">
              <div
                v-for="item in category.items"
                :key="item.id"
                class="menu-item"
                :class="{ featured: item.is_featured }"
              >
                <!-- Featured Badge -->
                <div v-if="item.is_featured" class="featured-badge">
                  {{ $t("restaurant.special") || "Đặc biệt" }}
                </div>

                <!-- Item Image -->
                <div v-if="item.image" class="item-image">
                  <img
                    :src="item.image"
                    :alt="getLocalizedName(item)"
                    loading="lazy"
                  />
                </div>

                <!-- Item Info -->
                <div class="item-info">
                  <div class="item-header">
                    <h3 class="item-name">{{ getLocalizedName(item) }}</h3>
                    <div class="item-price-wrap">
                      <span class="item-price">{{
                        formatPrice(item.price)
                      }}</span>
                      <span v-if="getLocalizedUnit(item)" class="item-unit"
                        >/{{ getLocalizedUnit(item) }}</span
                      >
                    </div>
                  </div>

                  <p v-if="getLocalizedNote(item)" class="item-note">
                    {{ getLocalizedNote(item) }}
                  </p>

                  <span v-if="!item.is_available" class="status-unavailable">
                    {{ $t("restaurant.sold_out") || "Tạm hết" }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2>{{ $t("restaurant.cta_title") || "Đặt bàn ngay hôm nay" }}</h2>
          <p>
            {{
              $t("restaurant.cta_subtitle") ||
              "Liên hệ với chúng tôi để đặt bàn và thưởng thức những món ăn tuyệt vời"
            }}
          </p>
          <a :href="'tel:' + phone" class="btn-cta">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <path
                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"
              ></path>
            </svg>
            {{ $t("restaurant.call_now") || "Gọi ngay" }}: {{ phone }}
          </a>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api";

const { locale } = useI18n();

const categories = ref([]);
const settings = ref({});
const loading = ref(true);
const activeCategory = ref(null);
const isSticky = ref(false);

// Gradient themes for categories without images
const categoryGradients = [
  "linear-gradient(135deg, #667eea 0%, #764ba2 100%)",
  "linear-gradient(135deg, #f093fb 0%, #f5576c 100%)",
  "linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)",
  "linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)",
  "linear-gradient(135deg, #fa709a 0%, #fee140 100%)",
  "linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%)",
];

// Computed properties for settings
const restaurantName = computed(() => {
  if (locale.value === "en" && settings.value.restaurant_name?.value_en) {
    return settings.value.restaurant_name.value_en;
  }
  return settings.value.restaurant_name?.value_vi || "Nhà hàng Happy Island Tour";
});

const intro = computed(() => {
  if (locale.value === "en" && settings.value.restaurant_intro?.value_en) {
    return settings.value.restaurant_intro.value_en;
  }
  return settings.value.restaurant_intro?.value_vi || "";
});

const openingHours = computed(() => {
  if (
    locale.value === "en" &&
    settings.value.restaurant_opening_hours?.value_en
  ) {
    return settings.value.restaurant_opening_hours.value_en;
  }
  return settings.value.restaurant_opening_hours?.value_vi || "7:00 - 22:00";
});

const phone = computed(() => {
  return settings.value.restaurant_phone?.value_vi || "+84 123 456 789";
});

const bannerStyle = computed(() => {
  const banner = settings.value.restaurant_banner?.value_vi;
  if (banner) {
    return { backgroundImage: `url(${banner})` };
  }
  return {};
});

// Helper functions
const getCategoryHeaderStyle = (category, index) => {
  if (category.image) {
    return { backgroundImage: `url(${category.image})` };
  }
  return { background: categoryGradients[index % categoryGradients.length] };
};

const getLocalizedName = (item) => {
  if (locale.value === "en" && item.name_en) {
    return item.name_en;
  }
  return item.name;
};

const getLocalizedDescription = (item) => {
  if (locale.value === "en" && item.description_en) {
    return item.description_en;
  }
  return item.description;
};

const getLocalizedNote = (item) => {
  if (locale.value === "en" && item.note_en) {
    return item.note_en;
  }
  return item.note;
};

const getLocalizedUnit = (item) => {
  if (locale.value === "en" && item.unit_en) {
    return item.unit_en;
  }
  return item.unit;
};

const formatPrice = (price) => {
  return new Intl.NumberFormat("vi-VN").format(price) + "đ";
};

const scrollToCategory = (categoryId) => {
  activeCategory.value = categoryId;
  const element = document.getElementById(`category-${categoryId}`);
  if (element) {
    const offset = 80;
    const top = element.offsetTop - offset;
    window.scrollTo({ top, behavior: "smooth" });
  }
};

const handleScroll = () => {
  isSticky.value = window.scrollY > 400;

  // Update active category based on scroll position
  for (const category of categories.value) {
    const element = document.getElementById(`category-${category.id}`);
    if (element) {
      const rect = element.getBoundingClientRect();
      if (rect.top <= 150 && rect.bottom >= 150) {
        activeCategory.value = category.id;
        break;
      }
    }
  }
};

const fetchMenu = async () => {
  loading.value = true;
  try {
    const [menuResponse, settingsResponse] = await Promise.all([
      api.get("/menu"),
      api.get("/settings/group/restaurant"),
    ]);

    categories.value = menuResponse.data.data || [];

    const rawSettings = settingsResponse.data.data || [];
    if (Array.isArray(rawSettings)) {
      rawSettings.forEach((s) => {
        if (s?.key) settings.value[s.key] = s;
      });
    } else if (rawSettings && typeof rawSettings === "object") {
      // API currently returns { key: localizedValue } for group endpoints
      Object.entries(rawSettings).forEach(([key, value]) => {
        settings.value[key] = {
          key,
          value,
          value_vi: value,
          value_en: value,
        };
      });
    }

    if (categories.value.length > 0) {
      activeCategory.value = categories.value[0].id;
    }
  } catch (error) {
    console.error("Error fetching menu:", error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchMenu();
  window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
});
</script>

<style scoped>
.restaurant-page {
  min-height: 100vh;
  background: #f8f9fa;
}

/* ===== CONTAINER ===== */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

/* ===== HERO SECTION ===== */
.hero-section {
  position: relative;
  min-height: 550px;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to bottom,
    rgba(0, 0, 0, 0.5) 0%,
    rgba(0, 0, 0, 0.7) 100%
  );
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
}

.hero-content {
  text-align: center;
  max-width: 900px;
  width: 100%;
}

.hero-content h1 {
  color: white;
  font-size: 52px;
  font-weight: 700;
  margin: 0 0 16px 0;
  text-shadow: 2px 4px 20px rgba(0, 0, 0, 0.4);
}

.hero-content .tagline {
  color: rgba(255, 255, 255, 0.9);
  font-size: 20px;
  margin: 0 0 40px 0;
  line-height: 1.6;
}

/* Hero Info Cards */
.hero-info-cards {
  display: flex;
  gap: 20px;
  justify-content: center;
  flex-wrap: wrap;
}

.hero-info-cards .info-card {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  padding: 16px 24px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.hero-info-cards .info-icon {
  color: white;
  opacity: 0.9;
}

.hero-info-cards .info-text {
  display: flex;
  flex-direction: column;
  text-align: left;
}

.hero-info-cards .info-text .label {
  color: rgba(255, 255, 255, 0.7);
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.hero-info-cards .info-text .value {
  color: white;
  font-size: 16px;
  font-weight: 600;
}

/* ===== CATEGORY NAVIGATION ===== */
.category-nav {
  background: white;
  padding: 16px 0;
  border-bottom: 1px solid #eee;
  transition: all 0.3s ease;
}

.category-nav.sticky {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 100;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.nav-scroll {
  display: flex;
  gap: 12px;
  overflow-x: auto;
  padding: 4px;
  scrollbar-width: none;
}

.nav-scroll::-webkit-scrollbar {
  display: none;
}

.nav-tab {
  padding: 12px 24px;
  background: #f5f5f5;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
  font-size: 15px;
  font-weight: 500;
  color: #555;
}

.nav-tab:hover {
  background: #eee;
  color: #333;
}

.nav-tab.active {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
}

/* ===== MENU SECTION ===== */
.menu-section {
  padding: 60px 0 80px;
}

/* Loading State */
.loading-state {
  text-align: center;
  padding: 80px 20px;
  color: #666;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #eee;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 80px 20px;
  color: #888;
}

.empty-icon {
  color: #ccc;
  margin-bottom: 20px;
}

.empty-state h3 {
  margin: 0;
  font-weight: 500;
  color: #666;
}

/* ===== CATEGORY SECTION ===== */
.category-section {
  margin-bottom: 60px;
}

.category-section:last-child {
  margin-bottom: 0;
}

/* Category Header */
.category-header {
  position: relative;
  border-radius: 16px;
  overflow: hidden;
  min-height: 180px;
  background-size: cover;
  background-position: center;
  margin-bottom: 32px;
}

.category-header-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    135deg,
    rgba(0, 0, 0, 0.6) 0%,
    rgba(0, 0, 0, 0.3) 100%
  );
  display: flex;
  align-items: center;
  padding: 32px 40px;
}

.category-header-content {
  color: white;
}

.category-header h2 {
  font-size: 32px;
  font-weight: 700;
  margin: 0 0 12px 0;
  text-shadow: 1px 2px 8px rgba(0, 0, 0, 0.3);
}

.category-desc {
  font-size: 16px;
  margin: 0 0 16px 0;
  max-width: 500px;
  opacity: 0.95;
  line-height: 1.5;
}

.item-count {
  display: inline-block;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
}

/* ===== MENU ITEMS GRID ===== */
.items-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 24px;
}

.menu-item {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
  position: relative;
}

.menu-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
}

.menu-item.featured {
  border: 2px solid #f5a623;
}

/* Featured Badge */
.featured-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: linear-gradient(135deg, #f5a623, #f7931e);
  color: white;
  padding: 6px 14px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  z-index: 5;
  box-shadow: 0 2px 8px rgba(245, 166, 35, 0.4);
}

/* Item Image */
.item-image {
  height: 180px;
  overflow: hidden;
}

.item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.menu-item:hover .item-image img {
  transform: scale(1.05);
}

/* Item Info */
.item-info {
  padding: 20px;
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  margin-bottom: 10px;
}

.item-name {
  margin: 0;
  font-size: 17px;
  font-weight: 600;
  color: #333;
  line-height: 1.4;
  flex: 1;
}

.item-price-wrap {
  text-align: right;
  white-space: nowrap;
}

.item-price {
  font-size: 18px;
  font-weight: 700;
  color: #e74c3c;
}

.item-unit {
  font-size: 13px;
  color: #999;
  margin-left: 2px;
}

.item-note {
  color: #666;
  font-size: 14px;
  margin: 0 0 12px 0;
  line-height: 1.5;
}

.status-unavailable {
  display: inline-block;
  background: #ffebee;
  color: #c62828;
  font-size: 12px;
  padding: 4px 12px;
  border-radius: 4px;
  font-weight: 500;
}

/* ===== CTA SECTION ===== */
.cta-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 80px 20px;
}

.cta-content {
  text-align: center;
  max-width: 600px;
  margin: 0 auto;
}

.cta-content h2 {
  color: white;
  font-size: 32px;
  font-weight: 700;
  margin: 0 0 16px 0;
}

.cta-content p {
  color: rgba(255, 255, 255, 0.9);
  font-size: 18px;
  margin: 0 0 32px 0;
  line-height: 1.6;
}

.btn-cta {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: white;
  color: #667eea;
  padding: 16px 32px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.btn-cta:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
  .hero-content h1 {
    font-size: 42px;
  }

  .category-header h2 {
    font-size: 28px;
  }
}

@media (max-width: 768px) {
  .hero-section {
    min-height: 450px;
  }

  .hero-content h1 {
    font-size: 32px;
  }

  .hero-content .tagline {
    font-size: 16px;
    margin-bottom: 30px;
  }

  .hero-info-cards {
    flex-direction: column;
    align-items: center;
    gap: 12px;
  }

  .hero-info-cards .info-card {
    width: 100%;
    max-width: 300px;
  }

  .category-header {
    min-height: 140px;
    border-radius: 12px;
  }

  .category-header-overlay {
    padding: 24px;
  }

  .category-header h2 {
    font-size: 24px;
  }

  .category-desc {
    font-size: 14px;
  }

  .items-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .menu-section {
    padding: 40px 0 60px;
  }

  .category-section {
    margin-bottom: 40px;
  }

  .cta-content h2 {
    font-size: 26px;
  }

  .cta-content p {
    font-size: 16px;
  }

  .cta-section {
    padding: 60px 20px;
  }
}

@media (max-width: 480px) {
  .hero-content h1 {
    font-size: 28px;
  }

  .nav-tab {
    padding: 10px 18px;
    font-size: 14px;
  }

  .item-image {
    height: 160px;
  }

  .item-info {
    padding: 16px;
  }

  .item-name {
    font-size: 16px;
  }

  .item-price {
    font-size: 16px;
  }
}
</style>
