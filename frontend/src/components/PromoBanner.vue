<template>
  <section v-if="banner.active" class="promo-banner" :class="variant">
    <div class="promo-banner__bg">
      <img v-if="banner.image" :src="banner.image" :alt="banner.title" />
      <div class="promo-banner__gradient"></div>
    </div>
    <div class="promo-banner__container">
      <div class="promo-banner__content">
        <span v-if="tag" class="promo-banner__tag">{{ tag }}</span>
        <h2 class="promo-banner__title">{{ banner.title }}</h2>
        <p class="promo-banner__subtitle">{{ banner.subtitle }}</p>
        <router-link
          v-if="banner.link"
          :to="banner.link"
          class="promo-banner__btn"
        >
          {{ buttonText || $t("common.learn_more", "Tìm hiểu thêm") }}
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <line x1="5" y1="12" x2="19" y2="12" />
            <polyline points="12 5 19 12 12 19" />
          </svg>
        </router-link>
      </div>
      <div v-if="showDecoration" class="promo-banner__decoration">
        <div class="promo-banner__circle promo-banner__circle--1"></div>
        <div class="promo-banner__circle promo-banner__circle--2"></div>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  banner: {
    type: Object,
    required: true,
    default: () => ({
      image: "",
      title: "",
      subtitle: "",
      link: "",
      active: false,
    }),
  },
  variant: {
    type: String,
    default: "default", // default, gradient, minimal
  },
  tag: {
    type: String,
    default: "",
  },
  buttonText: {
    type: String,
    default: "",
  },
  showDecoration: {
    type: Boolean,
    default: true,
  },
});
</script>

<style scoped>
.promo-banner {
  position: relative;
  overflow: hidden;
  border-radius: 1.5rem;
  min-height: 280px;
  display: flex;
  align-items: center;
}

.promo-banner__bg {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
}

.promo-banner__bg img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.5;
}

.promo-banner__gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    90deg,
    rgba(15, 23, 42, 0.9) 0%,
    rgba(15, 23, 42, 0.4) 100%
  );
}

.promo-banner__container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 3rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.promo-banner__content {
  max-width: 550px;
  color: white;
}

.promo-banner__tag {
  display: inline-block;
  padding: 0.4rem 1rem;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(10px);
  border-radius: 50px;
  font-size: 0.8rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  margin-bottom: 1rem;
}

.promo-banner__title {
  font-size: clamp(1.5rem, 4vw, 2.25rem);
  font-weight: 700;
  line-height: 1.2;
  margin-bottom: 0.75rem;
}

.promo-banner__subtitle {
  font-size: 1.05rem;
  opacity: 0.85;
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.promo-banner__btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  background: white;
  color: #1e293b;
  text-decoration: none;
  font-weight: 600;
  border-radius: 50px;
  transition: all 0.3s ease;
}

.promo-banner__btn svg {
  width: 18px;
  height: 18px;
  transition: transform 0.3s ease;
}

.promo-banner__btn:hover {
  transform: translateX(5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.promo-banner__btn:hover svg {
  transform: translateX(3px);
}

/* Decoration */
.promo-banner__decoration {
  position: absolute;
  right: 5%;
  top: 50%;
  transform: translateY(-50%);
}

.promo-banner__circle {
  position: absolute;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.2);
}

.promo-banner__circle--1 {
  width: 200px;
  height: 200px;
  top: -100px;
  right: 0;
  animation: pulse 4s ease-in-out infinite;
}

.promo-banner__circle--2 {
  width: 150px;
  height: 150px;
  top: -30px;
  right: 80px;
  animation: pulse 4s ease-in-out infinite 0.5s;
}

@keyframes pulse {
  0%,
  100% {
    opacity: 0.3;
    transform: scale(1);
  }
  50% {
    opacity: 0.6;
    transform: scale(1.05);
  }
}

/* Gradient variant */
.promo-banner.gradient .promo-banner__bg {
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
}

.promo-banner.gradient .promo-banner__bg img {
  opacity: 0.3;
}

.promo-banner.gradient .promo-banner__gradient {
  background: linear-gradient(
    90deg,
    rgba(37, 99, 235, 0.8) 0%,
    rgba(124, 58, 237, 0.5) 100%
  );
}

/* Minimal variant */
.promo-banner.minimal {
  min-height: 200px;
  background: white;
  border: 1px solid #e2e8f0;
}

.promo-banner.minimal .promo-banner__bg {
  display: none;
}

.promo-banner.minimal .promo-banner__content {
  color: #1e293b;
}

.promo-banner.minimal .promo-banner__tag {
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  color: white;
}

.promo-banner.minimal .promo-banner__subtitle {
  color: #64748b;
}

.promo-banner.minimal .promo-banner__btn {
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  color: white;
}

@media (max-width: 768px) {
  .promo-banner__container {
    padding: 2rem 1.5rem;
  }

  .promo-banner__decoration {
    display: none;
  }
}
</style>
