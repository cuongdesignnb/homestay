import axios from "axios";

const normalizeLocale = (raw) => {
  if (!raw) return "en";
  const lower = String(raw).toLowerCase();
  if (lower.startsWith("en")) return "en";
  if (lower.startsWith("vi")) return "vi";
  return "en";
};

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || "http://localhost:8765/api",
  headers: {
    Accept: "application/json",
  },
});

// Request interceptor
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    const locale = normalizeLocale(localStorage.getItem("locale"));
    if (locale) {
      config.headers["Accept-Language"] = locale;

      const method = (config.method || "get").toLowerCase();
      if (method === "get") {
        config.params = config.params || {};
        if (config.params.lang === undefined) {
          config.params.lang = locale;
        }
      }
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Don't redirect if on payment pages (QR payment doesn't require auth)
      const currentPath = window.location.pathname;
      const publicPaths = [
        "/payment/qr",
        "/payment/result",
        "/payment/callback",
        "/booking-confirmation",
      ];
      const isPublicPath = publicPaths.some((path) =>
        currentPath.startsWith(path)
      );

      if (!isPublicPath) {
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        window.location.href = "/login";
      }
    }
    return Promise.reject(error);
  }
);

export default api;
