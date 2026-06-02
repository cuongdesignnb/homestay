import { createI18n } from "vue-i18n";

// Import translations
import en from "./en.json";
import vi from "./vi.json";

const normalizeLocale = (raw) => {
  if (!raw) return "en";
  const lower = String(raw).toLowerCase();
  if (lower.startsWith("en")) return "en";
  if (lower.startsWith("vi")) return "vi";
  return "en";
};

// Create i18n instance
const i18n = createI18n({
  legacy: false,
  locale: normalizeLocale(localStorage.getItem("locale")),
  fallbackLocale: "en",
  messages: {
    en,
    vi,
  },
});

export default i18n;
