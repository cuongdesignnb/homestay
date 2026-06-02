<template>
  <div class="yolo-page" @mousemove="handleParallax">
    <!-- Background rays -->
    <div class="rays-bg"></div>

    <!-- Background floating clouds (subtle atmosphere) -->
    <div class="clouds-layer">
      <div class="cloud-bg cloud-bg-1">
        <svg
          viewBox="0 0 300 120"
          fill="white"
          xmlns="http://www.w3.org/2000/svg"
        >
          <ellipse cx="150" cy="80" rx="130" ry="40" />
          <ellipse cx="100" cy="60" rx="80" ry="50" />
          <ellipse cx="200" cy="55" rx="70" ry="45" />
          <ellipse cx="150" cy="50" rx="90" ry="45" />
        </svg>
      </div>
      <div class="cloud-bg cloud-bg-2">
        <svg
          viewBox="0 0 250 100"
          fill="white"
          xmlns="http://www.w3.org/2000/svg"
        >
          <ellipse cx="125" cy="65" rx="110" ry="35" />
          <ellipse cx="80" cy="50" rx="65" ry="40" />
          <ellipse cx="170" cy="45" rx="60" ry="38" />
          <ellipse cx="125" cy="42" rx="75" ry="38" />
        </svg>
      </div>
    </div>

    <!-- Palm leaves -->
    <div class="palm palm-left">
      <svg viewBox="0 0 200 250" xmlns="http://www.w3.org/2000/svg">
        <g transform="rotate(-20, 100, 0)">
          <path
            d="M100,0 Q60,80 10,120 Q70,90 100,50 Q130,90 190,120 Q140,80 100,0Z"
            fill="#228B22"
            opacity="0.9"
          />
          <path
            d="M100,20 Q65,90 20,140 Q75,105 100,70 Q125,105 180,140 Q135,90 100,20Z"
            fill="#2E8B57"
            opacity="0.85"
          />
          <path
            d="M100,40 Q70,100 30,155 Q80,115 100,85 Q120,115 170,155 Q130,100 100,40Z"
            fill="#32CD32"
            opacity="0.7"
          />
        </g>
      </svg>
    </div>
    <div class="palm palm-right">
      <svg viewBox="0 0 200 250" xmlns="http://www.w3.org/2000/svg">
        <g transform="rotate(20, 100, 0)">
          <path
            d="M100,0 Q60,80 10,120 Q70,90 100,50 Q130,90 190,120 Q140,80 100,0Z"
            fill="#228B22"
            opacity="0.9"
          />
          <path
            d="M100,20 Q65,90 20,140 Q75,105 100,70 Q125,105 180,140 Q135,90 100,20Z"
            fill="#2E8B57"
            opacity="0.85"
          />
          <path
            d="M100,40 Q70,100 30,155 Q80,115 100,85 Q120,115 170,155 Q130,100 100,40Z"
            fill="#32CD32"
            opacity="0.7"
          />
        </g>
      </svg>
    </div>

    <!-- Logo top-left -->
    <div class="yolo-logo">
      <svg viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
        <circle
          cx="30"
          cy="30"
          r="28"
          fill="none"
          stroke="#E8722A"
          stroke-width="2"
        />
        <text
          x="30"
          y="36"
          text-anchor="middle"
          font-size="18"
          font-weight="bold"
          fill="#E8722A"
        >
          Y
        </text>
      </svg>
    </div>

    <!-- Flag top-right -->
    <div class="flag-icon">
      <svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
        <circle cx="20" cy="20" r="18" fill="#DA251D" />
        <polygon
          points="20,8 23,16 31,16 25,21 27,29 20,24 13,29 15,21 9,16 17,16"
          fill="#FFFF00"
        />
      </svg>
    </div>

    <!-- 3 Floating clouds - travel across full width -->
    <div class="floating-clouds">
      <img
        v-for="(cloud, idx) in cloudPositions"
        :key="'cloud-' + idx"
        :src="cloud.src"
        class="floating-cloud"
        :class="['fc-' + (idx + 1), cloud.animClass]"
        :style="{ top: cloud.top }"
        alt="cloud"
        v-show="cloud.src"
      />
    </div>

    <!-- Main content -->
    <div class="yolo-content">
      <!-- Left side - Banner image + CTA below -->
      <div class="yolo-text-section">
        <!-- Banner image - z-index ABOVE clouds -->
        <img
          :src="bannerYolo"
          alt="YOLO Ocean Camp"
          class="banner-bg-img"
          :class="{ 'banner-visible': bannerVisible }"
        />

        <!-- CTA group below image with fade-in animation -->
        <div class="cta-group" :class="{ 'cta-visible': ctaVisible }">
          <div class="yolo-cta">
            <button class="cta-button" @click="goToLink">
              {{ ctaText }}
            </button>
            <p class="cta-sub">{{ ctaSub }}</p>
          </div>
          <p class="yolo-tagline">
            {{ tagline }}
          </p>
        </div>
      </div>

      <!-- Right side - Rotating Wheel Image - z-index BELOW clouds -->
      <div class="yolo-wheel-section">
        <div class="wheel-container">
          <!-- Rotating wheel image -->
          <img :src="bannerWheel" alt="Tour Wheel" class="wheel-image" />

          <!-- "You Only Live Once" signature text overlay on top of wheel -->
          <div class="signature-text">
            <div class="sig-container" :class="{ animate: signatureVisible }">
              <span
                v-for="(char, ci) in signatureLine1"
                :key="'s1-' + ci"
                class="sig-char sig-char-1"
                :style="{ animationDelay: ci * 0.07 + 's' }"
                >{{ char === " " ? "\u00A0" : char }}</span
              >
            </div>
            <div class="sig-container" :class="{ animate: signatureVisible }">
              <span
                v-for="(char, ci) in signatureLine2"
                :key="'s2-' + ci"
                class="sig-char sig-char-2"
                :style="{
                  animationDelay:
                    signatureLine1.length * 0.07 + ci * 0.07 + 0.3 + 's',
                }"
                >{{ char === " " ? "\u00A0" : char }}</span
              >
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Vietnamese flag decoration (small) -->
    <div class="vn-flag-small">
      <svg viewBox="0 0 30 20" xmlns="http://www.w3.org/2000/svg">
        <rect width="30" height="20" fill="#DA251D" />
        <polygon
          points="15,3 17.5,9.5 24,9.5 18.8,13.5 21,20 15,16 9,20 11.2,13.5 6,9.5 12.5,9.5"
          fill="#FFFF00"
        />
      </svg>
    </div>

    <!-- Most Selected Tours Slider -->
    <div v-if="popularTours.length" class="popular-section">
      <div class="popular-header">
        <div class="popular-badge">🔥 Most Selected</div>
        <h2 class="popular-title">Popular Tours</h2>
        <p class="popular-subtitle">Những tour được khách yêu thích nhất</p>
      </div>

      <div class="slider-wrapper">
        <button class="slider-arrow slider-arrow-left" @click="scrollSlider(-1)" aria-label="Previous">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        </button>

        <div class="slider-track" ref="sliderTrack">
          <router-link
            v-for="tour in popularTours"
            :key="tour.id"
            :to="`/tours/${tour.id}`"
            class="tour-slide"
          >
            <div class="slide-image">
              <img :src="tour.cover_image || tour.images?.[0] || '/placeholder-tour.jpg'" :alt="tour.name" loading="lazy" />
              <div class="slide-rating" v-if="tour.average_rating">
                ⭐ {{ Number(tour.average_rating).toFixed(1) }}
              </div>
              <div class="slide-bookings" v-if="tour.bookings_count">
                {{ tour.bookings_count }} booked
              </div>
            </div>
            <div class="slide-info">
              <h3 class="slide-name">{{ tour.name }}</h3>
              <div class="slide-meta">
                <span class="slide-price">{{ formatTourPrice(tour.discount_price || tour.price_per_person) }}</span>
                <span class="slide-duration" v-if="tour.duration">{{ tour.duration }} {{ tour.duration_unit === 'hours' ? 'hrs' : 'days' }}</span>
              </div>
            </div>
          </router-link>
        </div>

        <button class="slider-arrow slider-arrow-right" @click="scrollSlider(1)" aria-label="Next">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
      </div>

      <div class="popular-footer">
        <router-link to="/tours" class="more-tour-btn">
          More Tour
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M5 12h14"/><polyline points="12 5 19 12 12 19"/></svg>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from "vue";
import { useRouter } from "vue-router";
import api from "@/services/api";

// Dynamically load assets using glob - won't break build if files don't exist
const assetModules = import.meta.glob("../assets/banner_yolo.webp", {
  eager: true,
  import: "default",
});
const wheelModules = import.meta.glob("../assets/banner_rotate_circle.webp", {
  eager: true,
  import: "default",
});
const defaultBannerYolo = Object.values(assetModules)[0] || "";
const defaultBannerWheel = Object.values(wheelModules)[0] || "";

const props = defineProps({
  bannerImage: { type: String, default: "" },
  wheelImage: { type: String, default: "" },
  cloudImage1: { type: String, default: "" },
  cloudImage2: { type: String, default: "" },
  cloudImage3: { type: String, default: "" },
  ctaText: { type: String, default: "Đặt ngay và tiết kiệm 10%" },
  ctaSub: { type: String, default: "Chỉ khả dụng trên Website" },
  tagline: {
    type: String,
    default:
      "Chỉ cần đặt chỗ trước - Không cần thẻ tín dụng - Thanh toán khi tham gia tour",
  },
  ctaLink: { type: String, default: "/tours" },
  sigLine1: { type: String, default: "You Only" },
  sigLine2: { type: String, default: "Live Once" },
});

const router = useRouter();
const signatureVisible = ref(false);
const ctaVisible = ref(false);
const bannerVisible = ref(false);

// Use props or fallback to default assets
const bannerYolo = computed(() => props.bannerImage || defaultBannerYolo);
const bannerWheel = computed(() => props.wheelImage || defaultBannerWheel);

// Cloud images from props
const cloudSources = computed(() =>
  [props.cloudImage1, props.cloudImage2, props.cloudImage3].filter(Boolean),
);

// Cloud position slots - 3 vertical positions with animation class names
const cloudSlots = [
  { top: "12%", animClass: "cloud-anim-a" },
  { top: "42%", animClass: "cloud-anim-b" },
  { top: "72%", animClass: "cloud-anim-c" },
];

// Reactive cloud positions - each cloud gets a random slot
const cloudPositions = reactive([]);
let shuffleInterval = null;

function initClouds() {
  cloudPositions.length = 0;
  const sources = cloudSources.value;
  if (!sources.length) return;

  // Shuffle slots for initial assignment
  const shuffled = [...cloudSlots].sort(() => Math.random() - 0.5);
  sources.forEach((src, i) => {
    const slot = shuffled[i % shuffled.length];
    cloudPositions.push({
      src,
      top: slot.top,
      animClass: slot.animClass,
    });
  });
}

function shuffleCloudPositions() {
  if (cloudPositions.length < 2) return;
  // Pick two random clouds and swap their vertical positions & animation
  const idxA = Math.floor(Math.random() * cloudPositions.length);
  let idxB = Math.floor(Math.random() * cloudPositions.length);
  while (idxB === idxA)
    idxB = Math.floor(Math.random() * cloudPositions.length);

  const topA = cloudPositions[idxA].top;
  const animA = cloudPositions[idxA].animClass;
  cloudPositions[idxA].top = cloudPositions[idxB].top;
  cloudPositions[idxA].animClass = cloudPositions[idxB].animClass;
  cloudPositions[idxB].top = topA;
  cloudPositions[idxB].animClass = animA;
}

// Signature text split into characters
const signatureLine1 = computed(() => props.sigLine1.split(""));
const signatureLine2 = computed(() => props.sigLine2.split(""));

function goToLink() {
  router.push(props.ctaLink);
}

function handleParallax(e) {
  // Parallax handled by CSS animations
}

// --- Popular Tours Slider ---
const popularTours = ref([]);
const sliderTrack = ref(null);

const formatTourPrice = (price) => {
  if (!price) return '';
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND', maximumFractionDigits: 0 }).format(price);
};

const fetchPopularTours = async () => {
  try {
    const res = await api.get('/tours/popular', { params: { limit: 8 } });
    popularTours.value = res.data.data || [];
  } catch (e) {
    console.error('Failed to load popular tours:', e);
  }
};

const scrollSlider = (direction) => {
  if (!sliderTrack.value) return;
  const cardWidth = sliderTrack.value.querySelector('.tour-slide')?.offsetWidth || 300;
  sliderTrack.value.scrollBy({ left: direction * (cardWidth + 16), behavior: 'smooth' });
};

let signatureTimeout = null;
let ctaTimeout = null;
let bannerTimeout = null;
onMounted(() => {
  initClouds();
  fetchPopularTours();
  // Shuffle cloud positions every 8-12 seconds
  shuffleInterval = setInterval(
    () => {
      shuffleCloudPositions();
    },
    8000 + Math.random() * 4000,
  );

  bannerTimeout = setTimeout(() => {
    bannerVisible.value = true;
  }, 100);
  ctaTimeout = setTimeout(() => {
    ctaVisible.value = true;
  }, 700);
  signatureTimeout = setTimeout(() => {
    signatureVisible.value = true;
  }, 1400);
});

onBeforeUnmount(() => {
  if (signatureTimeout) clearTimeout(signatureTimeout);
  if (ctaTimeout) clearTimeout(ctaTimeout);
  if (bannerTimeout) clearTimeout(bannerTimeout);
  if (shuffleInterval) clearInterval(shuffleInterval);
});
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&display=swap");

/* ================================
   BASE & BACKGROUND
   ================================ */
.yolo-page {
  position: relative;
  width: 100%;
  min-height: 100vh;
  overflow: hidden;
  background: linear-gradient(180deg, #e8f4fd 0%, #b8dff0 30%, #87ceeb 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-top: -72px;
  padding-top: 72px;
}

/* Sun rays background */
.rays-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: repeating-conic-gradient(
    from 0deg at 50% 50%,
    rgba(255, 255, 255, 0.12) 0deg 4deg,
    transparent 4deg 10deg
  );
  pointer-events: none;
  z-index: 1;
  animation: raysRotate 120s linear infinite;
}

@keyframes raysRotate {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* ================================
   CLOUDS
   ================================ */
/* ================================
   BACKGROUND CLOUDS (subtle atmosphere)
   ================================ */
.clouds-layer {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 2;
}

.cloud-bg {
  position: absolute;
  opacity: 0.35;
}

.cloud-bg-1 {
  top: 3%;
  left: -10%;
  width: 280px;
  animation: cloudDrift 60s linear infinite;
}

.cloud-bg-2 {
  top: 70%;
  left: -15%;
  width: 220px;
  animation: cloudDrift 75s linear infinite;
  animation-delay: -25s;
}

@keyframes cloudDrift {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(calc(100vw + 100%));
  }
}

/* ================================
   FLOATING CLOUDS - travel across full page width
   Clouds go UNDER left image, OVER right wheel
   ================================ */
.floating-clouds {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 6;
}

.floating-cloud {
  position: absolute;
  left: 0;
  width: 320px;
  height: auto;
  transition: top 3s ease-in-out;
}

/* 3 animation paths using translateX to sweep across the full viewport */
.cloud-anim-a {
  animation: cloudSweepA 40s linear infinite;
}

.cloud-anim-b {
  animation: cloudSweepB 50s linear infinite;
  animation-delay: -14s;
}

.cloud-anim-c {
  animation: cloudSweepC 45s linear infinite;
  animation-delay: -28s;
}

/* Path A: left → right → left (full sweep) */
@keyframes cloudSweepA {
  0% {
    transform: translateX(-25vw) translateY(0);
  }
  50% {
    transform: translateX(100vw) translateY(-12px);
  }
  100% {
    transform: translateX(-25vw) translateY(0);
  }
}

/* Path B: right → left → right (reverse sweep) */
@keyframes cloudSweepB {
  0% {
    transform: translateX(100vw) translateY(0);
  }
  50% {
    transform: translateX(-25vw) translateY(10px);
  }
  100% {
    transform: translateX(100vw) translateY(0);
  }
}

/* Path C: middle → right → left → middle (figure-8 path) */
@keyframes cloudSweepC {
  0% {
    transform: translateX(35vw) translateY(0);
  }
  25% {
    transform: translateX(100vw) translateY(-8px);
  }
  50% {
    transform: translateX(50vw) translateY(6px);
  }
  75% {
    transform: translateX(-25vw) translateY(-5px);
  }
  100% {
    transform: translateX(35vw) translateY(0);
  }
}

.fc-1 {
  width: 360px;
}

.fc-2 {
  width: 320px;
}

.fc-3 {
  width: 280px;
}

/* ================================
   DECORATIONS
   ================================ */
.palm {
  position: absolute;
  z-index: 3;
  pointer-events: none;
}

.palm-left {
  top: -20px;
  left: -20px;
  width: 180px;
  transform: rotate(-10deg);
  animation: palmSway 6s ease-in-out infinite;
}

.palm-right {
  top: -20px;
  right: -20px;
  width: 180px;
  transform: rotate(10deg) scaleX(-1);
  animation: palmSway 7s ease-in-out infinite reverse;
}

@keyframes palmSway {
  0%,
  100% {
    transform: rotate(-10deg);
  }
  50% {
    transform: rotate(-5deg);
  }
}

.yolo-logo {
  position: absolute;
  top: 20px;
  left: 30px;
  z-index: 10;
  width: 50px;
  cursor: pointer;
  transition: transform 0.3s;
}

.yolo-logo:hover {
  transform: scale(1.1);
}

.flag-icon {
  position: absolute;
  top: 20px;
  right: 30px;
  z-index: 10;
  width: 40px;
  cursor: pointer;
  transition: transform 0.3s;
}

.flag-icon:hover {
  transform: scale(1.1);
}

/* ================================
   MAIN CONTENT LAYOUT
   ================================ */
.yolo-content {
  position: relative;
  /* No z-index here — children must interleave with .floating-clouds (z:6) */
  display: flex;
  align-items: center;
  justify-content: space-between;
  max-width: 1400px;
  width: 100%;
  padding: 40px 60px;
  gap: 40px;
}

/* ================================
   LEFT SECTION - above clouds (z-index 7)
   ================================ */
.yolo-text-section {
  flex: 1;
  position: relative;
  z-index: 7;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.banner-bg-img {
  width: 100%;
  max-width: 650px;
  height: auto;
  border-radius: 16px;
  display: block;
  opacity: 0;
  transform: translateY(-30px);
  transition:
    opacity 0.8s ease-out,
    transform 0.8s ease-out;
}

.banner-bg-img.banner-visible {
  opacity: 1;
  transform: translateY(0);
}

/* CTA group below banner image */
.cta-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 24px;
  opacity: 0;
  transform: translateY(-20px);
  transition:
    opacity 0.8s ease-out,
    transform 0.8s ease-out;
  pointer-events: none;
}

.cta-group.cta-visible {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}

.yolo-title h1 {
  margin: 0;
  line-height: 0.95;
  font-family: "Arial Black", "Impact", sans-serif;
  text-transform: uppercase;
  letter-spacing: -2px;
}

.title-yolo {
  font-size: 6rem;
  color: #1b4b8a;
  text-shadow:
    3px 3px 0 #ffb347,
    -1px -1px 0 rgba(255, 255, 255, 0.5);
  animation: titleSlideIn 0.8s ease-out;
}

.title-ocean {
  font-size: 7rem;
  background: linear-gradient(180deg, #87ceeb 0%, #1b6cb0 50%, #1b4b8a 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  filter: drop-shadow(3px 3px 0 rgba(27, 75, 138, 0.3));
  animation: titleSlideIn 0.8s ease-out 0.2s both;
}

.title-camp {
  font-size: 7.5rem;
  color: #1b4b8a;
  text-shadow:
    4px 4px 0 rgba(27, 75, 138, 0.2),
    -1px -1px 0 rgba(255, 255, 255, 0.3);
  animation: titleSlideIn 0.8s ease-out 0.4s both;
}

@keyframes titleSlideIn {
  from {
    opacity: 0;
    transform: translateX(-40px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.sparkle {
  position: absolute;
  font-size: 1.5rem;
  color: #ffd700;
  animation: sparkleAnim 2s ease-in-out infinite;
  z-index: 2;
}

.sparkle-1 {
  top: 10px;
  left: 320px;
  animation-delay: 0s;
}
.sparkle-2 {
  top: 80px;
  left: 400px;
  animation-delay: 0.7s;
}
.sparkle-3 {
  top: 160px;
  left: 20px;
  animation-delay: 1.4s;
  font-size: 1rem;
}

@keyframes sparkleAnim {
  0%,
  100% {
    opacity: 0.3;
    transform: scale(0.8) rotate(0deg);
  }
  50% {
    opacity: 1;
    transform: scale(1.2) rotate(180deg);
  }
}

.yolo-cta {
  margin-top: 35px;
  text-align: center;
  z-index: 1;
}

.cta-button {
  background: #1b6cb0;
  color: white;
  border: none;
  padding: 16px 40px;
  font-size: 1.15rem;
  font-weight: 700;
  border-radius: 50px;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 20px rgba(27, 108, 176, 0.4);
  letter-spacing: 0.5px;
}

.cta-button:hover {
  background: #154e80;
  transform: translateY(-2px);
  box-shadow: 0 6px 25px rgba(27, 108, 176, 0.5);
}

.cta-button:active {
  transform: translateY(0);
}

.cta-sub {
  margin-top: 8px;
  font-size: 0.85rem;
  color: #555;
  font-style: italic;
}

.yolo-tagline {
  margin-top: 25px;
  font-size: 0.95rem;
  color: #444;
  letter-spacing: 0.3px;
  z-index: 1;
}

/* ================================
   RIGHT SECTION - WHEEL
   ================================ */
/* ================================
   RIGHT SECTION - below clouds (z-index 4)
   ================================ */
.yolo-wheel-section {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  z-index: 4;
}

.wheel-container {
  position: relative;
  width: 520px;
  height: 520px;
}

.wheel-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 50%;
  animation: wheelSpin 25s linear infinite;
  filter: drop-shadow(0 8px 30px rgba(0, 0, 0, 0.2));
}

@keyframes wheelSpin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* ================================
   SIGNATURE TEXT - "You Only Live Once"
   ================================ */
.signature-text {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 20;
  pointer-events: none;
  text-align: center;
  width: 350px;
}

.sig-container {
  display: flex;
  justify-content: center;
  flex-wrap: nowrap;
}

.sig-char {
  font-family: "Dancing Script", cursive;
  color: white;
  text-shadow:
    0 2px 8px rgba(0, 0, 0, 0.35),
    0 0 20px rgba(255, 255, 255, 0.2);
  opacity: 0;
  display: inline-block;
  transform: translateY(10px) scale(0.5);
  transition: none;
}

.sig-char-1 {
  font-size: 2.8rem;
  font-weight: 700;
}

.sig-char-2 {
  font-size: 3.2rem;
  font-weight: 700;
}

/* When .animate is added, each char animates in sequence */
.sig-container.animate .sig-char {
  animation: charReveal 0.5s ease-out forwards;
}

@keyframes charReveal {
  0% {
    opacity: 0;
    transform: translateY(15px) scale(0.3) rotate(-10deg);
    filter: blur(4px);
  }
  60% {
    opacity: 1;
    transform: translateY(-3px) scale(1.1) rotate(2deg);
    filter: blur(0px);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1) rotate(0deg);
    filter: blur(0px);
  }
}

/* ================================
   SMALL DECORATIVE FLAG
   ================================ */
.vn-flag-small {
  position: absolute;
  top: 45%;
  right: 52%;
  width: 25px;
  z-index: 6;
  animation: flagWave 3s ease-in-out infinite;
  pointer-events: none;
}

@keyframes flagWave {
  0%,
  100% {
    transform: rotate(-5deg);
  }
  50% {
    transform: rotate(5deg);
  }
}

/* ================================
   RESPONSIVE
   ================================ */
@media (max-width: 1200px) {
  .yolo-content {
    flex-direction: column;
    padding: 80px 30px 40px;
    text-align: center;
  }

  .yolo-text-section {
    align-items: center;
  }

  .title-yolo {
    font-size: 4rem;
  }
  .title-ocean {
    font-size: 5rem;
  }
  .title-camp {
    font-size: 5.5rem;
  }

  .wheel-container {
    width: 380px;
    height: 380px;
  }
  .signature-text {
    width: 300px;
  }
  .banner-bg-img {
    max-width: 480px;
  }
  .sig-char-1 {
    font-size: 2.2rem;
  }
  .sig-char-2 {
    font-size: 2.6rem;
  }
  .floating-cloud {
    width: 260px !important;
  }
}

@media (max-width: 768px) {
  .title-yolo {
    font-size: 3rem;
  }
  .title-ocean {
    font-size: 3.5rem;
  }
  .title-camp {
    font-size: 4rem;
  }

  .wheel-container {
    width: 320px;
    height: 320px;
  }
  .signature-text {
    width: 240px;
  }
  .sig-char-1 {
    font-size: 1.8rem;
  }
  .sig-char-2 {
    font-size: 2rem;
  }
  .floating-cloud {
    width: 200px !important;
  }

  .palm-left,
  .palm-right {
    width: 120px;
  }
  .cta-button {
    padding: 12px 30px;
    font-size: 1rem;
  }
}

@media (max-width: 480px) {
  .title-yolo {
    font-size: 2.2rem;
  }
  .title-ocean {
    font-size: 2.8rem;
  }
  .title-camp {
    font-size: 3rem;
  }

  .wheel-container {
    width: 260px;
    height: 260px;
  }
  .banner-bg-img {
    max-width: 300px;
  }
  .signature-text {
    width: 200px;
  }
  .sig-char-1 {
    font-size: 1.4rem;
  }
  .sig-char-2 {
    font-size: 1.6rem;
  }
  .floating-cloud {
    width: 160px !important;
  }

  .yolo-content {
    padding: 70px 15px 30px;
  }
}

/* ================================
   POPULAR TOURS SLIDER
   ================================ */
.popular-section {
  position: relative;
  z-index: 8;
  width: 100%;
  max-width: 1400px;
  padding: 0 40px 60px;
  margin: 0 auto;
}

.popular-header {
  text-align: center;
  margin-bottom: 28px;
}

.popular-badge {
  display: inline-block;
  padding: 6px 18px;
  border-radius: 999px;
  background: rgba(255, 179, 71, 0.25);
  color: #b45309;
  font-weight: 700;
  font-size: 0.85rem;
  letter-spacing: 0.04em;
  margin-bottom: 10px;
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 179, 71, 0.35);
}

.popular-title {
  font-size: 2rem;
  font-weight: 800;
  color: #1b4b8a;
  margin: 0 0 6px;
  text-shadow: 0 2px 8px rgba(27, 75, 138, 0.15);
}

.popular-subtitle {
  font-size: 0.95rem;
  color: #555;
  margin: 0;
}

.slider-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  gap: 8px;
}

.slider-track {
  display: flex;
  gap: 16px;
  overflow-x: auto;
  scroll-snap-type: x mandatory;
  -webkit-overflow-scrolling: touch;
  padding: 8px 4px 16px;
  scrollbar-width: none;
}

.slider-track::-webkit-scrollbar {
  display: none;
}

.slider-arrow {
  flex-shrink: 0;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: none;
  background: rgba(27, 75, 138, 0.15);
  backdrop-filter: blur(8px);
  color: #1b4b8a;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.25s;
}

.slider-arrow:hover {
  background: rgba(27, 75, 138, 0.3);
  transform: scale(1.1);
}

.slider-arrow svg {
  width: 20px;
  height: 20px;
}

.tour-slide {
  flex: 0 0 260px;
  scroll-snap-align: start;
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(16px);
  border-radius: 18px;
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  border: 1px solid rgba(255, 255, 255, 0.7);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
  transition: transform 0.3s, box-shadow 0.3s;
}

.tour-slide:hover {
  transform: translateY(-6px);
  box-shadow: 0 16px 48px rgba(0, 0, 0, 0.15);
}

.slide-image {
  position: relative;
  height: 160px;
  overflow: hidden;
}

.slide-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.5s;
}

.tour-slide:hover .slide-image img {
  transform: scale(1.08);
}

.slide-rating {
  position: absolute;
  top: 10px;
  left: 10px;
  padding: 3px 10px;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(6px);
  color: #fff;
  font-size: 0.75rem;
  font-weight: 600;
}

.slide-bookings {
  position: absolute;
  top: 10px;
  right: 10px;
  padding: 3px 10px;
  border-radius: 999px;
  background: rgba(27, 108, 176, 0.75);
  backdrop-filter: blur(6px);
  color: #fff;
  font-size: 0.7rem;
  font-weight: 600;
}

.slide-info {
  padding: 14px 16px 16px;
}

.slide-name {
  font-size: 1rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 8px;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.slide-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.slide-price {
  font-size: 0.95rem;
  font-weight: 800;
  color: #1b6cb0;
}

.slide-duration {
  font-size: 0.75rem;
  color: #64748b;
  padding: 2px 8px;
  border-radius: 6px;
  background: rgba(100, 116, 139, 0.1);
}

.popular-footer {
  text-align: center;
  margin-top: 28px;
}

.more-tour-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 36px;
  border-radius: 999px;
  background: linear-gradient(135deg, #1b6cb0, #1b4b8a);
  color: #fff;
  font-weight: 700;
  font-size: 1.05rem;
  text-decoration: none;
  letter-spacing: 0.3px;
  box-shadow: 0 6px 24px rgba(27, 108, 176, 0.35);
  transition: all 0.3s;
}

.more-tour-btn:hover {
  background: linear-gradient(135deg, #154e80, #0f3460);
  transform: translateY(-2px);
  box-shadow: 0 10px 32px rgba(27, 108, 176, 0.45);
}

@media (max-width: 1200px) {
  .popular-section {
    padding: 0 24px 50px;
  }
  .tour-slide {
    flex: 0 0 240px;
  }
}

@media (max-width: 768px) {
  .popular-section {
    padding: 0 16px 40px;
  }
  .popular-title {
    font-size: 1.5rem;
  }
  .slider-arrow {
    display: none;
  }
  .tour-slide {
    flex: 0 0 220px;
  }
  .slide-image {
    height: 140px;
  }
}

@media (max-width: 480px) {
  .popular-section {
    padding: 0 12px 30px;
  }
  .tour-slide {
    flex: 0 0 200px;
  }
  .slide-image {
    height: 120px;
  }
  .more-tour-btn {
    padding: 12px 28px;
    font-size: 0.95rem;
  }
}
</style>
