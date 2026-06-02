<template>
  <div class="admin-shell admin">
    <aside class="admin-sidebar">
      <div class="sidebar-logo">
        <span class="logo-dot"></span>
        <div>
          <p class="text-sm uppercase tracking-[0.35em] text-slate-400">
            Happy Island Tour
          </p>
          <h1 class="text-xl font-semibold">Admin Suite</h1>
        </div>
      </div>
      <nav class="sidebar-nav">
        <button
          v-for="link in navLinks"
          :key="link.name"
          class="nav-link"
          :class="{ active: routeHash === link.name }"
          @click="goTo(link.path)"
        >
          <span class="material-icons-outlined nav-icon">{{ link.icon }}</span>
          <div>
            <p class="text-base font-medium">{{ link.label }}</p>
            <small class="text-xs text-slate-400">{{ link.description }}</small>
          </div>
        </button>
      </nav>
      <div class="sidebar-card">
        <p class="text-sm text-slate-400">Hôm nay</p>
        <p class="text-3xl font-semibold text-white">{{ highlight.stat }}</p>
        <p class="text-sm text-slate-300">{{ highlight.label }}</p>
        <button class="btn btn-secondary w-full mt-4" @click="goTo('/admin')">
          Xem báo cáo
        </button>
      </div>
    </aside>

    <div class="admin-main">
      <header class="admin-header">
        <div>
          <p class="text-sm text-slate-400">Xin chào, {{ userName }}</p>
          <h2 class="text-2xl font-semibold">{{ headerTitle }}</h2>
        </div>
        <div class="header-actions">
          <button class="chip pill flex items-center gap-1">
            <span class="material-icons-outlined text-amber-400 text-base">bolt</span>
            <span>{{ $t("admin.quick_actions", "Tác vụ nhanh") }}</span>
          </button>
          <button class="btn btn-primary" @click="logout">
            {{ $t("auth.logout") }}
          </button>
        </div>
      </header>
      <main class="admin-content">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useRoute, useRouter, RouterView } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useSettingsStore } from "@/stores/settings";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const settingsStore = useSettingsStore();

const allNavLinks = [
  {
    name: "dashboard",
    path: "/admin",
    label: "Tổng quan",
    description: "Thống kê & insight",
    icon: "dashboard",
  },
  {
    name: "rooms",
    path: "/admin/rooms",
    label: "Phòng",
    description: "Quản lý Happy Island Tour",
    icon: "hotel",
    feature: "rooms",
  },
  {
    name: "tours",
    path: "/admin/tours",
    label: "Tours",
    description: "Hành trình & lịch",
    icon: "explore",
  },
  {
    name: "car-rentals",
    path: "/admin/car-rentals",
    label: "Cho thuê xe",
    description: "Quản lý xe",
    icon: "directions_car",
    feature: "carRentals",
  },
  {
    name: "bookings",
    path: "/admin/bookings",
    label: "Đặt chỗ",
    description: "Theo dõi & duyệt",
    icon: "calendar_month",
  },
  {
    name: "blog",
    path: "/admin/blog",
    label: "Blog",
    description: "Tin tức & câu chuyện",
    icon: "article",
  },
  {
    name: "guest-reviews",
    path: "/admin/guest-reviews",
    label: "Đánh giá",
    description: "Kiểm duyệt review",
    icon: "reviews",
  },
  {
    name: "menu-categories",
    path: "/admin/menu-categories",
    label: "Thực đơn",
    description: "Danh mục & món ăn",
    icon: "restaurant_menu",
  },
  {
    name: "menu-items",
    path: "/admin/menu-items",
    label: "Món ăn",
    description: "Quản lý menu",
    icon: "restaurant",
  },
  {
    name: "restaurant-settings",
    path: "/admin/restaurant-settings",
    label: "Nhà hàng",
    description: "Cài đặt nhà hàng",
    icon: "storefront",
  },
  {
    name: "equipments",
    path: "/admin/equipments",
    label: "Cho thuê đồ",
    description: "Quản lý đồ dùng",
    icon: "pool",
  },
  {
    name: "equipment-orders",
    path: "/admin/equipment-orders",
    label: "Đơn thuê/mua",
    description: "Theo dõi đơn hàng",
    icon: "assignment",
  },
  {
    name: "nav-menus",
    path: "/admin/nav-menus",
    label: "Menu điều hướng",
    description: "Drag & drop cấu hình",
    icon: "menu_open",
  },
  {
    name: "settings",
    path: "/admin/settings",
    label: "Cài đặt",
    description: "Cấu hình website",
    icon: "settings",
  },
];

const navLinks = computed(() =>
  allNavLinks.filter((link) => {
    if (link.feature === "rooms") return settingsStore.featureRoomsEnabled;
    if (link.feature === "carRentals") return settingsStore.featureCarRentalsEnabled;
    return true;
  })
);

const highlight = {
  stat: "18+",
  label: "Booking mới hôm nay",
};

const findActiveLink = (path) => {
  const links = navLinks.value;
  if (path === "/admin") {
    return links.find((link) => link.path === "/admin");
  }

  // Check filtered links first, then all links for header title
  return links
    .filter((link) => link.path !== "/admin")
    .find((link) => path.startsWith(link.path))
    || allNavLinks
      .filter((link) => link.path !== "/admin")
      .find((link) => path.startsWith(link.path));
};

const routeHash = computed(
  () => findActiveLink(route.path)?.name || "dashboard"
);

const userName = computed(() => authStore.user?.name || "Admin");
const headerTitle = computed(
  () => findActiveLink(route.path)?.label || "Tổng quan"
);

const goTo = (path) => {
  router.push(path);
};

const logout = async () => {
  await authStore.logout();
  router.push("/login");
};

onMounted(() => {
  settingsStore.fetchSettings();
});
</script>

<style scoped>
.admin-shell {
  min-height: 100vh;
  display: grid;
  grid-template-columns: 320px 1fr;
  background: radial-gradient(circle at top left, #0f172a, #020617);
  color: #e2e8f0;
}

.admin-sidebar {
  padding: 2.5rem 2rem;
  border-right: 1px solid rgba(148, 163, 184, 0.15);
  background: rgba(2, 6, 23, 0.65);
  backdrop-filter: blur(24px);
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.sidebar-logo {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.logo-dot {
  width: 3rem;
  height: 3rem;
  border-radius: 1rem;
  background: conic-gradient(from 180deg, #38bdf8, #6366f1, #c084fc);
  box-shadow: 0 15px 35px rgba(99, 102, 241, 0.4);
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.nav-link {
  display: flex;
  gap: 0.85rem;
  padding: 0.75rem 1rem;
  border-radius: 1.25rem;
  background: rgba(148, 163, 184, 0.08);
  border: 1px solid transparent;
  text-align: left;
  transition: all 0.2s ease;
}

.nav-link:hover {
  border-color: rgba(148, 163, 184, 0.4);
}

.nav-link.active {
  background: rgba(99, 102, 241, 0.2);
  border-color: rgba(99, 102, 241, 0.45);
}

.nav-icon {
  font-size: 1.5rem;
}

.sidebar-card {
  margin-top: auto;
  padding: 1.5rem;
  border-radius: 1.5rem;
  border: 1px solid rgba(148, 163, 184, 0.2);
  background: linear-gradient(
    180deg,
    rgba(79, 70, 229, 0.45),
    rgba(2, 6, 23, 0.9)
  );
  box-shadow: 0 30px 60px rgba(15, 23, 42, 0.65);
}

.admin-main {
  display: flex;
  flex-direction: column;
  background: linear-gradient(180deg, #0b1120 0%, #111827 35%, #0f172a 100%);
}

.admin-header {
  padding: 2.5rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.15);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.chip {
  padding: 0.4rem 1rem;
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: rgba(15, 23, 42, 0.65);
}

.admin-content {
  padding: 2.5rem;
}

@media (max-width: 1024px) {
  .admin-shell {
    grid-template-columns: 1fr;
  }

  .admin-sidebar {
    position: sticky;
    top: 0;
    z-index: 20;
    flex-direction: row;
    overflow-x: auto;
    padding: 1.25rem;
  }

  .sidebar-logo,
  .sidebar-card {
    display: none;
  }

  .sidebar-nav {
    flex-direction: row;
  }

  .nav-link {
    border-radius: 1rem;
  }
}

@media (max-width: 640px) {
  .admin-header,
  .admin-content {
    padding: 1.5rem;
  }

  .header-actions {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
