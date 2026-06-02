<template>
  <div class="about-page">
    <!-- Breadcrumb -->
    <div class="breadcrumb-section">
      <div class="container">
        <nav class="breadcrumb">
          <router-link to="/" class="breadcrumb__link">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
            </svg>
            {{ $t("nav.home") }}
          </router-link>
          <span class="breadcrumb__separator">/</span>
          <span class="breadcrumb__current">{{ $t("nav.about") }}</span>
        </nav>
      </div>
    </div>

    <!-- Hero Section -->
    <section class="about-hero">
      <div class="about-hero__bg">
        <img
          v-if="settingsStore.aboutImage"
          :src="settingsStore.aboutImage"
          alt="About us"
        />
        <div class="about-hero__gradient"></div>
      </div>
      <div class="container">
        <div class="about-hero__content">
          <span class="about-hero__tag">{{
            $t("about.welcome_tag", "Chào mừng")
          }}</span>
          <h1 class="about-hero__title">
            {{ settingsStore.aboutTitle || $t("about.title") }}
          </h1>
          <p class="about-hero__subtitle">
            {{ settingsStore.aboutSubtitle || $t("about.subtitle") }}
          </p>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <section class="about-content">
      <div class="container">
        <div class="about-content__grid">
          <!-- Text Content -->
          <div class="about-content__text">
            <div
              class="about-content__body"
              v-html="settingsStore.aboutContent || defaultContent"
            ></div>
          </div>

          <!-- Features -->
          <div class="about-content__features">
            <div
              v-for="(feature, index) in features"
              :key="index"
              class="feature-card"
              :style="{ '--delay': index * 0.1 + 's' }"
            >
              <div class="feature-card__icon">
                <component :is="getFeatureIcon(feature.icon)" />
              </div>
              <h3 class="feature-card__title">{{ feature.title }}</h3>
              <p class="feature-card__desc">{{ feature.desc }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="about-stats">
      <div class="container">
        <div class="stats-grid">
          <div class="stat-item">
            <span class="stat-item__number">10+</span>
            <span class="stat-item__label">{{
              $t("about.years_experience", "Năm kinh nghiệm")
            }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-item__number">5000+</span>
            <span class="stat-item__label">{{
              $t("about.happy_guests", "Khách hài lòng")
            }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-item__number">50+</span>
            <span class="stat-item__label">{{
              $t("about.rooms_tours", "Phòng & Tours")
            }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-item__number">4.9</span>
            <span class="stat-item__label">{{
              $t("about.rating", "Đánh giá trung bình")
            }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact CTA -->
    <section class="about-cta">
      <div class="container">
        <div class="cta-card">
          <div class="cta-card__content">
            <h2>
              {{ $t("about.cta_title", "Sẵn sàng cho kỳ nghỉ của bạn?") }}
            </h2>
            <p>
              {{
                $t(
                  "about.cta_subtitle",
                  "Liên hệ với chúng tôi ngay hôm nay để được tư vấn và đặt phòng."
                )
              }}
            </p>
          </div>
          <div class="cta-card__actions">
            <router-link to="/rooms" class="cta-btn cta-btn--primary">
              {{ $t("about.view_rooms", "Xem phòng") }}
            </router-link>
            <a
              :href="aboutPhoneHref"
              target="_blank"
              rel="noopener"
              class="cta-btn cta-btn--outline"
            >
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"
                />
              </svg>
              {{ settingsStore.phone || $t("about.call_us", "Gọi ngay") }}
            </a>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, h } from "vue";
import { useI18n } from "vue-i18n";
import { useSettingsStore } from "@/stores/settings";

const { t } = useI18n();
const settingsStore = useSettingsStore();

onMounted(() => {
  settingsStore.fetchSettings();
});

const normalizeWhatsAppNumber = (raw) => {
  if (!raw) return "";
  return String(raw).replace(/\D/g, "");
};

const aboutPhoneHref = computed(() => {
  const wa = normalizeWhatsAppNumber(settingsStore.contactWhatsapp);
  if (wa) return `https://wa.me/${wa}`;
  const phone = settingsStore.phone;
  return phone ? `tel:${phone}` : "#";
});

const features = computed(() => {
  const storeFeatures = settingsStore.aboutFeatures;
  if (storeFeatures && storeFeatures.length > 0) {
    return storeFeatures;
  }
  return [
    {
      icon: "star",
      title: t("about.feature_1_title", "Đẳng cấp 5 sao"),
      desc: t("about.feature_1_desc", "Tiêu chuẩn phục vụ cao cấp"),
    },
    {
      icon: "leaf",
      title: t("about.feature_2_title", "Thiên nhiên tươi đẹp"),
      desc: t("about.feature_2_desc", "Không gian xanh mát, trong lành"),
    },
    {
      icon: "heart",
      title: t("about.feature_3_title", "Tận tâm phục vụ"),
      desc: t("about.feature_3_desc", "Đội ngũ chuyên nghiệp 24/7"),
    },
    {
      icon: "shield",
      title: t("about.feature_4_title", "An toàn & Bảo mật"),
      desc: t("about.feature_4_desc", "Thanh toán an toàn, thông tin bảo mật"),
    },
  ];
});

const defaultContent = computed(() =>
  t(
    "about.default_content",
    `
  <p>Chào mừng bạn đến với Happy Island Tour - nơi nghỉ dưỡng lý tưởng kết hợp giữa sự thoải mái hiện đại và vẻ đẹp thiên nhiên hoang sơ.</p>
  <p>Với hơn 10 năm kinh nghiệm trong ngành du lịch và lưu trú, chúng tôi tự hào mang đến cho du khách những trải nghiệm nghỉ dưỡng đẳng cấp với giá cả hợp lý.</p>
`
  )
);

// Icon components
const StarIcon = {
  render() {
    return h(
      "svg",
      {
        viewBox: "0 0 24 24",
        fill: "none",
        stroke: "currentColor",
        "stroke-width": "2",
      },
      [
        h("polygon", {
          points:
            "12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2",
        }),
      ]
    );
  },
};

const LeafIcon = {
  render() {
    return h(
      "svg",
      {
        viewBox: "0 0 24 24",
        fill: "none",
        stroke: "currentColor",
        "stroke-width": "2",
      },
      [
        h("path", {
          d: "M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z",
        }),
        h("path", { d: "M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12" }),
      ]
    );
  },
};

const HeartIcon = {
  render() {
    return h(
      "svg",
      {
        viewBox: "0 0 24 24",
        fill: "none",
        stroke: "currentColor",
        "stroke-width": "2",
      },
      [
        h("path", {
          d: "M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z",
        }),
      ]
    );
  },
};

const ShieldIcon = {
  render() {
    return h(
      "svg",
      {
        viewBox: "0 0 24 24",
        fill: "none",
        stroke: "currentColor",
        "stroke-width": "2",
      },
      [h("path", { d: "M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" })]
    );
  },
};

const getFeatureIcon = (iconName) => {
  const icons = {
    star: StarIcon,
    leaf: LeafIcon,
    heart: HeartIcon,
    shield: ShieldIcon,
  };
  return icons[iconName] || StarIcon;
};
</script>

<style scoped>
.about-page {
  min-height: 100vh;
  background: #f8fafc;
}

/* Breadcrumb */
.breadcrumb-section {
  background: white;
  border-bottom: 1px solid #e2e8f0;
  padding: 1rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.9rem;
}

.breadcrumb__link {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #64748b;
  text-decoration: none;
  transition: color 0.2s;
}

.breadcrumb__link:hover {
  color: #2563eb;
}

.breadcrumb__link svg {
  width: 16px;
  height: 16px;
}

.breadcrumb__separator {
  color: #cbd5e1;
}

.breadcrumb__current {
  color: #1e293b;
  font-weight: 500;
}

/* Hero */
.about-hero {
  position: relative;
  min-height: 400px;
  display: flex;
  align-items: center;
  overflow: hidden;
}

.about-hero__bg {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
}

.about-hero__bg img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.4;
}

.about-hero__gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to bottom,
    rgba(15, 23, 42, 0.3),
    rgba(15, 23, 42, 0.8)
  );
}

.about-hero__content {
  position: relative;
  z-index: 1;
  text-align: center;
  color: white;
  padding: 4rem 0;
  max-width: 700px;
  margin: 0 auto;
}

.about-hero__tag {
  display: inline-block;
  padding: 0.5rem 1.25rem;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 50px;
  font-size: 0.875rem;
  font-weight: 500;
  letter-spacing: 0.05em;
  margin-bottom: 1.5rem;
}

.about-hero__title {
  font-size: clamp(2rem, 5vw, 3.5rem);
  font-weight: 700;
  line-height: 1.2;
  margin-bottom: 1rem;
}

.about-hero__subtitle {
  font-size: 1.125rem;
  opacity: 0.9;
  line-height: 1.6;
}

/* Content */
.about-content {
  padding: 5rem 0;
}

.about-content__grid {
  display: grid;
  gap: 4rem;
}

@media (min-width: 1024px) {
  .about-content__grid {
    grid-template-columns: 1.2fr 1fr;
    gap: 5rem;
  }
}

.about-content__text {
  font-size: 1.1rem;
  line-height: 1.8;
  color: #334155;
}

.about-content__body :deep(p) {
  margin-bottom: 1.5rem;
}

.about-content__body :deep(h2),
.about-content__body :deep(h3) {
  color: #1e293b;
  margin: 2rem 0 1rem;
}

.about-content__features {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

@media (max-width: 640px) {
  .about-content__features {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .about-content__text {
    font-size: 1rem;
  }

  .feature-card {
    padding: 1.25rem;
  }
}

.feature-card {
  background: white;
  padding: 1.75rem;
  border-radius: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  transition: all 0.3s ease;
  animation: fadeInUp 0.6s ease forwards;
  animation-delay: var(--delay);
  opacity: 0;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
}

.feature-card__icon {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  margin-bottom: 1rem;
}

.feature-card__icon svg {
  width: 24px;
  height: 24px;
}

.feature-card__title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.feature-card__desc {
  font-size: 0.9rem;
  color: #64748b;
  line-height: 1.5;
}

/* Stats */
.about-stats {
  background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
  padding: 4rem 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 2rem;
}

@media (min-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

.stat-item {
  text-align: center;
  color: white;
}

.stat-item__number {
  display: block;
  font-size: clamp(2.5rem, 5vw, 3.5rem);
  font-weight: 700;
  background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.5rem;
}

.stat-item__label {
  font-size: 0.95rem;
  opacity: 0.8;
}

/* CTA */
.about-cta {
  padding: 5rem 0;
}

.cta-card {
  background: white;
  border-radius: 1.5rem;
  padding: 3rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 2rem;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
}

@media (min-width: 768px) {
  .cta-card {
    flex-direction: row;
    text-align: left;
    justify-content: space-between;
  }
}

.cta-card__content h2 {
  font-size: 1.75rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.5rem;
}

.cta-card__content p {
  color: #64748b;
  font-size: 1.05rem;
}

.cta-card__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  justify-content: center;
}

.cta-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  border-radius: 50px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.cta-btn svg {
  width: 18px;
  height: 18px;
}

.cta-btn--primary {
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  color: white;
  box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
}

.cta-btn--primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
}

.cta-btn--outline {
  background: transparent;
  color: #1e293b;
  border: 2px solid #e2e8f0;
}

.cta-btn--outline:hover {
  border-color: #2563eb;
  color: #2563eb;
  background: rgba(37, 99, 235, 0.05);
}
</style>
