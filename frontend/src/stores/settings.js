import { defineStore } from "pinia";
import api from "@/services/api";
import i18n from "@/locales";

export const useSettingsStore = defineStore("settings", {
  state: () => ({
    settings: {}, // Raw settings grouped by group
    flatSettings: {}, // Flattened key-value pairs
    loading: false,
    loaded: false,
    error: null,
    locale: i18n.global.locale.value || "vi",
  }),

  getters: {
    // Get current locale
    currentLocale() {
      return this.locale || "vi";
    },

    // General
    siteName() {
      return this.getSettingValue("site_name") || "Happy Island Tour";
    },
    siteTagline() {
      return this.getSettingValue("site_tagline") || "";
    },
    logo() {
      return this.getSettingValue("logo_url") || "";
    },
    favicon() {
      return this.getSettingValue("favicon_url") || "";
    },

    // SEO
    metaTitle() {
      return this.getSettingValue("meta_title") || "";
    },
    metaDescription() {
      return this.getSettingValue("meta_description") || "";
    },
    metaKeywords() {
      return this.getSettingValue("meta_keywords") || "";
    },
    ogImage() {
      return this.getSettingValue("og_image") || "";
    },

    // Contact
    contactEmail() {
      return this.getSettingValue("contact_email") || "";
    },
    contactPhone() {
      return this.getSettingValue("contact_phone") || "";
    },
    contactHotline() {
      return this.getSettingValue("contact_hotline") || "";
    },
    contactZalo() {
      return this.getSettingValue("contact_zalo") || "";
    },
    contactWhatsapp() {
      return this.getSettingValue("contact_whatsapp") || "";
    },
    contactAddress() {
      return this.getSettingValue("contact_address") || "";
    },
    googleMapsUrl() {
      return this.getSettingValue("google_maps_url") || "";
    },

    // Social
    socialFacebook() {
      return this.getSettingValue("social_facebook") || "";
    },
    socialInstagram() {
      return this.getSettingValue("social_instagram") || "";
    },
    socialYoutube() {
      return this.getSettingValue("social_youtube") || "";
    },
    socialTiktok() {
      return this.getSettingValue("social_tiktok") || "";
    },
    socialMessenger() {
      return this.getSettingValue("social_messenger") || "";
    },
    socialZalo() {
      return this.getSettingValue("social_zalo") || "";
    },

    // Banners
    banner1Enabled() {
      const val = this.getSettingValue("banner1_enabled");
      return val === "true" || val === true || val === "1";
    },
    banner1() {
      return {
        enabled: this.banner1Enabled,
        active: this.banner1Enabled, // Alias for backward compatibility
        variant: this.getSettingValue("banner1_variant") || "default",
        title: this.getSettingValue("banner1_title") || "",
        subtitle: this.getSettingValue("banner1_subtitle") || "",
        cta: this.getSettingValue("banner1_cta") || "",
        link: this.getSettingValue("banner1_link") || "/rooms",
        image: this.getSettingValue("banner1_image") || "",
      };
    },
    banner2Enabled() {
      const val = this.getSettingValue("banner2_enabled");
      return val === "true" || val === true || val === "1";
    },
    banner2() {
      return {
        enabled: this.banner2Enabled,
        active: this.banner2Enabled, // Alias for backward compatibility
        variant: this.getSettingValue("banner2_variant") || "gradient",
        title: this.getSettingValue("banner2_title") || "",
        subtitle: this.getSettingValue("banner2_subtitle") || "",
        cta: this.getSettingValue("banner2_cta") || "",
        link: this.getSettingValue("banner2_link") || "/tours",
        image: this.getSettingValue("banner2_image") || "",
      };
    },

    // Hero Section
    heroTagline() {
      return this.getSettingValue("hero_tagline") || "";
    },
    heroTitle() {
      return this.getSettingValue("hero_title") || "";
    },
    heroSubtitle() {
      return this.getSettingValue("hero_subtitle") || "";
    },
    heroGalleryCaption() {
      return this.getSettingValue("hero_gallery_caption") || "";
    },
    heroFloatingTitle() {
      return this.getSettingValue("hero_floating_title") || "";
    },
    heroFloatingSubtitle() {
      return this.getSettingValue("hero_floating_subtitle") || "";
    },
    heroImages() {
      return [
        this.getSettingValue("hero_image_1") ||
          "https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1200&q=80",
        this.getSettingValue("hero_image_2") ||
          "https://images.unsplash.com/photo-1439130490301-25e322d88054?auto=format&fit=crop&w=600&q=80",
        this.getSettingValue("hero_image_3") ||
          "https://images.unsplash.com/photo-1505692794400-7fbb8246d57c?auto=format&fit=crop&w=500&q=80",
      ];
    },
    heroStats() {
      return [
        {
          value: this.getSettingValue("hero_stat1_value") || "50+",
          label:
            this.getSettingValue("hero_stat1_label") ||
            "Happy Island Tours curated",
        },
        {
          value: this.getSettingValue("hero_stat2_value") || "24",
          label: this.getSettingValue("hero_stat2_label") || "Destinations",
        },
        {
          value: this.getSettingValue("hero_stat3_value") || "4.9/5",
          label: this.getSettingValue("hero_stat3_label") || "Guest reviews",
        },
        {
          value: this.getSettingValue("hero_stat4_value") || "12+",
          label: this.getSettingValue("hero_stat4_label") || "Tours ready",
        },
      ];
    },
    heroPerks() {
      return [
        {
          icon: this.getSettingValue("hero_perk1_icon") || "🌿",
          title: this.getSettingValue("hero_perk1_title") || "Eco-luxury stays",
          subtitle:
            this.getSettingValue("hero_perk1_subtitle") ||
            "Không gian xanh, tiện nghi cao cấp",
        },
        {
          icon: this.getSettingValue("hero_perk2_icon") || "🧭",
          title:
            this.getSettingValue("hero_perk2_title") || "Local expert tours",
          subtitle:
            this.getSettingValue("hero_perk2_subtitle") ||
            "Khám phá bản sắc địa phương",
        },
        {
          icon: this.getSettingValue("hero_perk3_icon") || "💳",
          title: this.getSettingValue("hero_perk3_title") || "Secure payments",
          subtitle:
            this.getSettingValue("hero_perk3_subtitle") ||
            "SePay & PayPal được chứng nhận",
        },
      ];
    },
    heroTrustTitle() {
      return this.getSettingValue("hero_trust_title") || "5k+ khách hài lòng";
    },
    heroTrustSubtitle() {
      return (
        this.getSettingValue("hero_trust_subtitle") ||
        "Được đánh giá 4.9/5 trên toàn cầu"
      );
    },

    // About page
    aboutHeroTitle() {
      return this.getSettingValue("about_hero_title") || "";
    },
    aboutHeroSubtitle() {
      return this.getSettingValue("about_hero_subtitle") || "";
    },
    aboutHeroImage() {
      return this.getSettingValue("about_hero_image") || "";
    },
    aboutStoryTitle() {
      return this.getSettingValue("about_story_title") || "";
    },
    aboutStoryContent() {
      return this.getSettingValue("about_story_content") || "";
    },
    aboutStoryImage() {
      return this.getSettingValue("about_story_image") || "";
    },
    aboutYearsExperience() {
      return parseInt(this.getSettingValue("about_years_experience")) || 0;
    },
    aboutHappyGuests() {
      return parseInt(this.getSettingValue("about_happy_guests")) || 0;
    },
    aboutRoomsTours() {
      return parseInt(this.getSettingValue("about_rooms_tours")) || 0;
    },
    aboutRating() {
      return parseFloat(this.getSettingValue("about_rating")) || 4.9;
    },

    // Aliases for backward compatibility
    aboutTitle() {
      return this.aboutHeroTitle;
    },
    aboutSubtitle() {
      return this.aboutHeroSubtitle;
    },
    aboutImage() {
      return this.aboutHeroImage;
    },
    aboutContent() {
      return this.aboutStoryContent;
    },
    aboutFeatures() {
      // Parse JSON if stored, or return empty array
      const val = this.getSettingValue("about_features");
      if (val && typeof val === "string") {
        try {
          return JSON.parse(val);
        } catch (e) {
          return [];
        }
      }
      return val || [];
    },
    phone() {
      return this.contactPhone;
    },

    // Homepage landing page
    homepageLandingEnabled() {
      const val = this.getSettingValue("homepage_landing_enabled");
      return val === "true" || val === true || val === "1";
    },
    homepageBannerImage() {
      return this.getSettingValue("homepage_banner_image") || "";
    },
    homepageWheelImage() {
      return this.getSettingValue("homepage_wheel_image") || "";
    },
    homepageCloudImage1() {
      return this.getSettingValue("homepage_cloud_image_1") || "";
    },
    homepageCloudImage2() {
      return this.getSettingValue("homepage_cloud_image_2") || "";
    },
    homepageCloudImage3() {
      return this.getSettingValue("homepage_cloud_image_3") || "";
    },
    homepageCtaText() {
      return this.getSettingValue("homepage_cta_text") || "";
    },
    homepageCtaSub() {
      return this.getSettingValue("homepage_cta_sub") || "";
    },
    homepageTagline() {
      return this.getSettingValue("homepage_tagline") || "";
    },
    homepageCtaLink() {
      return this.getSettingValue("homepage_cta_link") || "/tours";
    },
    homepageSignatureLine1() {
      return this.getSettingValue("homepage_signature_line1") || "You Only";
    },
    homepageSignatureLine2() {
      return this.getSettingValue("homepage_signature_line2") || "Live Once";
    },

    // Language settings
    bilingualEnabled() {
      // Read raw value directly - this is a locale-independent boolean setting
      const setting = this.flatSettings["enable_bilingual"];
      if (!setting) return false;
      const val = setting.value_vi || setting.value_en || "";
      return val === "true" || val === true || val === "1" || val === 1;
    },

    defaultLanguage() {
      return this.getSettingValue("default_language") || "en";
    },

    // Feature toggles
    featureRoomsEnabled() {
      const setting = this.flatSettings["feature_rooms_enabled"];
      if (!setting) return true; // Default to true when setting doesn't exist
      const val = setting.value_vi || setting.value_en || "";
      if (val === "" || val === undefined || val === null) return true;
      return val === "true" || val === true || val === "1" || val === 1;
    },
    featureCarRentalsEnabled() {
      const setting = this.flatSettings["feature_car_rentals_enabled"];
      if (!setting) return true;
      const val = setting.value_vi || setting.value_en || "";
      if (val === "" || val === undefined || val === null) return true;
      return val === "true" || val === true || val === "1" || val === 1;
    },

    availableLanguages() {
      const val = this.getSettingValue("available_languages");
      if (val && typeof val === "string") {
        try {
          return JSON.parse(val);
        } catch (e) {
          return ["en", "vi"];
        }
      }
      return val || ["en", "vi"];
    },
  },

  actions: {
    setLocale(locale) {
      this.locale = locale || "vi";
    },
    // Get setting value by key with locale awareness
    getSettingValue(key) {
      const setting = this.flatSettings[key];
      if (!setting) return "";

      const locale = this.currentLocale;
      if (locale === "en" && setting.value_en) {
        return setting.value_en;
      }
      return setting.value_vi || "";
    },

    // Getter function to be used in components
    getSetting(key, defaultValue = "") {
      return this.getSettingValue(key) || defaultValue;
    },

    async fetchSettings() {
      if (this.loaded && Object.keys(this.flatSettings).length > 0) {
        return this.settings;
      }

      this.loading = true;
      this.error = null;

      try {
        const response = await api.get("/settings");
        const data = response.data.data || {};

        this.settings = data;

        // Flatten settings for easy access
        this.flatSettings = {};
        Object.keys(data).forEach((group) => {
          if (Array.isArray(data[group])) {
            data[group].forEach((setting) => {
              this.flatSettings[setting.key] = setting;
            });
          } else if (typeof data[group] === "object") {
            Object.keys(data[group]).forEach((key) => {
              if (
                typeof data[group][key] === "object" &&
                data[group][key] !== null
              ) {
                this.flatSettings[key] = data[group][key];
              } else {
                this.flatSettings[key] = {
                  value_vi: data[group][key],
                  value_en: data[group][key],
                };
              }
            });
          }
        });

        this.loaded = true;
        return this.settings;
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to load settings";
        console.error("Failed to fetch settings:", error);
        return {};
      } finally {
        this.loading = false;
      }
    },

    async refreshSettings() {
      this.loaded = false;
      this.flatSettings = {};
      return this.fetchSettings();
    },
  },
});
