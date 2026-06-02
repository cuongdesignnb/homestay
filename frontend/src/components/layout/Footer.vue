<template>
  <footer class="footer">
    <div class="footer__container">
      <div class="footer__grid">
        <!-- About -->
        <div class="footer__section footer__section--about">
          <div class="footer__logo">
            <div class="footer__logo-icon">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                <polyline points="9,22 9,12 15,12 15,22" />
              </svg>
            </div>
            <span class="footer__logo-text">{{
              settingsStore.siteName || "Happy Island Tour"
            }}</span>
          </div>
          <p class="footer__about-text">
            {{ settingsStore.siteTagline || $t("footer.about_description") }}
          </p>
          <div class="footer__social">
            <a
              v-if="settingsStore.socialFacebook"
              :href="settingsStore.socialFacebook"
              target="_blank"
              class="footer__social-link"
              aria-label="Facebook"
            >
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
                />
              </svg>
            </a>
            <a
              v-if="settingsStore.socialInstagram"
              :href="settingsStore.socialInstagram"
              target="_blank"
              class="footer__social-link"
              aria-label="Instagram"
            >
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                />
              </svg>
            </a>
            <a
              v-if="settingsStore.socialYoutube"
              :href="settingsStore.socialYoutube"
              target="_blank"
              class="footer__social-link"
              aria-label="YouTube"
            >
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"
                />
              </svg>
            </a>
            <a
              v-if="settingsStore.socialTiktok"
              :href="settingsStore.socialTiktok"
              target="_blank"
              class="footer__social-link"
              aria-label="TikTok"
            >
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"
                />
              </svg>
            </a>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="footer__section">
          <h3 class="footer__title">{{ $t("footer.quick_links") }}</h3>
          <ul class="footer__links">
            <li>
              <router-link to="/">{{ $t("nav.home") }}</router-link>
            </li>
            <li>
              <router-link to="/about">{{ $t("nav.about") }}</router-link>
            </li>
            <li>
              <router-link to="/rooms">{{ $t("nav.rooms") }}</router-link>
            </li>
            <li>
              <router-link to="/tours">{{ $t("nav.tours") }}</router-link>
            </li>
            <li>
              <router-link to="/blog">{{ $t("nav.blog") }}</router-link>
            </li>
          </ul>
        </div>

        <!-- Contact -->
        <div class="footer__section">
          <h3 class="footer__title">{{ $t("footer.contact_us") }}</h3>
          <ul class="footer__contact">
            <li v-if="settingsStore.contactAddress">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                <circle cx="12" cy="10" r="3" />
              </svg>
              <span>{{ settingsStore.contactAddress }}</span>
            </li>
            <li v-if="settingsStore.contactPhone">
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
              <a :href="'tel:' + settingsStore.contactPhone">{{
                settingsStore.contactPhone
              }}</a>
            </li>
            <li v-if="settingsStore.contactEmail">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"
                />
                <polyline points="22,6 12,13 2,6" />
              </svg>
              <a :href="'mailto:' + settingsStore.contactEmail">{{
                settingsStore.contactEmail
              }}</a>
            </li>
          </ul>
        </div>

        <!-- Connect -->
        <div class="footer__section">
          <h3 class="footer__title">{{ $t("footer.follow_us") }}</h3>
          <div class="footer__connect">
            <a
              v-if="whatsappHref"
              :href="whatsappHref"
              target="_blank"
              rel="noopener"
              class="footer__connect-btn footer__connect-btn--whatsapp"
            >
              <span class="footer__connect-icon">💚</span>
              <span>WhatsApp</span>
            </a>
            <a
              v-if="settingsStore.socialZalo"
              :href="settingsStore.socialZalo"
              target="_blank"
              class="footer__connect-btn footer__connect-btn--zalo"
            >
              <span class="footer__connect-icon">💬</span>
              <span>Zalo</span>
            </a>
            <a
              v-if="settingsStore.socialMessenger"
              :href="settingsStore.socialMessenger"
              target="_blank"
              class="footer__connect-btn footer__connect-btn--messenger"
            >
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.654V24l4.088-2.242c1.092.3 2.246.464 3.443.464 6.627 0 12-4.975 12-11.111S18.627 0 12 0zm1.191 14.963l-3.055-3.26-5.963 3.26L10.732 8l3.131 3.259L19.752 8l-6.561 6.963z"
                />
              </svg>
              <span>Messenger</span>
            </a>
          </div>
        </div>
      </div>

      <div class="footer__bottom">
        <p>
          © {{ new Date().getFullYear() }} {{ settingsStore.siteName }}.
          {{ $t("footer.copyright") }}
        </p>
        <p class="footer__made-with">
          {{ $t("footer.made_with") }} ❤️ {{ $t("footer.in_vietnam") }}
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useSettingsStore } from "@/stores/settings";

const settingsStore = useSettingsStore();

const normalizeWhatsAppNumber = (raw) => {
  if (!raw) return "";
  // Keep digits only, drop leading zeros/spaces/+ characters
  const digits = String(raw).replace(/\D/g, "");
  return digits;
};

const whatsappHref = computed(() => {
  const digits = normalizeWhatsAppNumber(settingsStore.contactWhatsapp);
  if (!digits) return "";
  return `https://wa.me/${digits}`;
});

onMounted(() => {
  settingsStore.fetchSettings();
});
</script>

<style scoped>
.footer {
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  color: white;
  padding: 4rem 0 0;
  margin-top: 4rem;
}

.footer__container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.footer__grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2.5rem;
}

@media (min-width: 768px) {
  .footer__grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1024px) {
  .footer__grid {
    grid-template-columns: 1.5fr 1fr 1fr 1fr;
  }
}

/* Logo Section */
.footer__section--about {
  max-width: 300px;
}

.footer__logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

.footer__logo-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.footer__logo-icon svg {
  width: 22px;
  height: 22px;
}

.footer__logo-text {
  font-size: 1.25rem;
  font-weight: 700;
}

.footer__about-text {
  color: #94a3b8;
  font-size: 0.95rem;
  line-height: 1.7;
  margin-bottom: 1.5rem;
}

/* Social Links */
.footer__social {
  display: flex;
  gap: 0.75rem;
}

.footer__social-link {
  width: 40px;
  height: 40px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  transition: all 0.3s ease;
}

.footer__social-link:hover {
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  color: white;
  transform: translateY(-3px);
}

.footer__social-link svg {
  width: 18px;
  height: 18px;
}

/* Section Titles */
.footer__title {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1.25rem;
  position: relative;
  padding-bottom: 0.75rem;
}

.footer__title::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 40px;
  height: 2px;
  background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
  border-radius: 2px;
}

/* Links */
.footer__links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer__links li {
  margin-bottom: 0.75rem;
}

.footer__links a {
  color: #94a3b8;
  text-decoration: none;
  font-size: 0.95rem;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.footer__links a:hover {
  color: white;
  transform: translateX(5px);
}

/* Contact */
.footer__contact {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer__contact li {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  margin-bottom: 1rem;
  color: #94a3b8;
  font-size: 0.95rem;
}

.footer__contact svg {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
  margin-top: 2px;
  color: #60a5fa;
}

.footer__contact a {
  color: #94a3b8;
  text-decoration: none;
  transition: color 0.2s;
}

.footer__contact a:hover {
  color: white;
}

/* Connect Buttons */
.footer__connect {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.footer__connect-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: 10px;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.footer__connect-btn--zalo {
  background: rgba(0, 136, 255, 0.15);
  color: #60a5fa;
  border: 1px solid rgba(0, 136, 255, 0.3);
}

.footer__connect-btn--zalo:hover {
  background: #0088ff;
  color: white;
  border-color: #0088ff;
}

.footer__connect-btn--messenger {
  background: rgba(168, 0, 255, 0.15);
  color: #c084fc;
  border: 1px solid rgba(168, 0, 255, 0.3);
}

.footer__connect-btn--messenger:hover {
  background: linear-gradient(135deg, #00b2ff 0%, #a800ff 100%);
  color: white;
  border-color: transparent;
}

.footer__connect-btn--whatsapp {
  background: rgba(34, 197, 94, 0.15);
  color: #86efac;
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.footer__connect-btn--whatsapp:hover {
  background: #22c55e;
  color: white;
  border-color: #22c55e;
}

.footer__connect-btn svg {
  width: 20px;
  height: 20px;
}

.footer__connect-icon {
  font-size: 1.2rem;
}

/* Bottom */
.footer__bottom {
  margin-top: 3rem;
  padding: 1.5rem 0;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
}

.footer__bottom p {
  color: #64748b;
  font-size: 0.9rem;
  margin: 0;
}
</style>
