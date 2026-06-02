<template>
  <div class="dashboard-shell space-y-10">
    <section class="grid gap-6 lg:grid-cols-4 md:grid-cols-2">
      <article v-for="card in kpiCards" :key="card.label" class="kpi-card">
        <div class="kpi-icon">{{ card.icon }}</div>
        <p class="text-sm uppercase tracking-[0.35em] text-slate-400">
          {{ card.label }}
        </p>
        <div class="flex items-end justify-between">
          <h3 class="text-4xl font-semibold text-white">{{ card.value }}</h3>
          <span
            :class="[
              'delta-pill',
              card.delta.startsWith('-') ? 'negative' : 'positive',
            ]"
            >{{ card.delta }}</span
          >
        </div>
        <small class="text-xs text-slate-400">{{ card.caption }}</small>
      </article>
    </section>

    <section class="grid gap-8 lg:grid-cols-3">
      <article class="gradient-panel lg:col-span-2">
        <header class="panel-head">
          <div>
            <p class="text-sm text-slate-300">
              {{ $t("admin.revenue_trend", "Doanh thu 30 ngày") }}
            </p>
            <h2 class="text-3xl font-semibold text-white">
              {{ formatCurrency(revenueTotal) }}
            </h2>
          </div>
          <span
            :class="[
              'delta-pill',
              revenueDelta.startsWith('-') ? 'negative' : 'positive',
            ]"
            >{{ revenueDelta }}</span
          >
        </header>
        <div class="trend-chart">
          <div class="trend-line">
            <span
              v-for="(point, index) in revenueTrend"
              :key="index"
              :style="{ height: `${point}%` }"
            ></span>
          </div>
          <ul class="trend-legend">
            <li
              v-for="(label, index) in ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4']"
              :key="label"
            >
              <span></span>{{ label }}
            </li>
          </ul>
        </div>
      </article>

      <article class="glass-card">
        <header class="panel-head">
          <div>
            <p class="text-sm text-slate-400">
              {{ $t("admin.recent_bookings", "Booking gần đây") }}
            </p>
            <h3 class="text-xl font-semibold text-white">
              {{ recentBookings.length }} {{ $t("admin.records", "lượt") }}
            </h3>
          </div>
          <router-link to="/admin/bookings" class="text-sm text-blue-300">{{
            $t("admin.view_all", "Xem tất cả")
          }}</router-link>
        </header>
        <ul v-if="recentBookings.length" class="space-y-4">
          <li
            v-for="booking in recentBookings"
            :key="booking.id"
            class="booking-item"
          >
            <div>
              <p class="font-medium text-white">{{ booking.guest }}</p>
              <small class="text-slate-400"
                >{{ booking.room }} · {{ booking.nights }} đêm</small
              >
            </div>
            <div class="text-right">
              <p class="text-sm text-slate-300">{{ booking.date }}</p>
              <span class="status-pill" :class="booking.status">{{
                booking.status
              }}</span>
            </div>
          </li>
        </ul>
        <div v-else class="text-center py-8 text-slate-400">
          <p>Chưa có booking nào</p>
        </div>
      </article>
    </section>

    <section class="grid gap-8 xl:grid-cols-3">
      <article class="glass-card">
        <header class="panel-head">
          <div>
            <p class="text-sm text-slate-400">
              {{ $t("admin.occupancy", "Tỉ lệ lấp phòng") }}
            </p>
            <h3 class="text-2xl font-semibold text-white">
              {{ occupancy.average }}%
            </h3>
          </div>
        </header>
        <ul v-if="occupancy.rooms && occupancy.rooms.length" class="space-y-4">
          <li
            v-for="item in occupancy.rooms"
            :key="item.id || item.label"
            class="flex items-center justify-between"
          >
            <div>
              <p class="text-white font-medium">{{ item.label }}</p>
              <small class="text-slate-400">{{ item.segment }}</small>
            </div>
            <div class="occupancy-visual">
              <div class="occupancy-bar">
                <span :style="{ width: `${item.value}%` }"></span>
              </div>
              <strong>{{ item.value }}%</strong>
            </div>
          </li>
        </ul>
        <div v-else class="text-center py-8 text-slate-400">
          <p>Chưa có dữ liệu phòng</p>
        </div>
      </article>

      <article class="glass-card">
        <header class="panel-head">
          <div>
            <p class="text-sm text-slate-400">
              {{ $t("admin.top_rooms", "Phòng được đặt nhiều") }}
            </p>
            <h3 class="text-2xl font-semibold text-white">
              {{ topRooms.length }} {{ $t("admin.rooms", "phòng") }}
            </h3>
          </div>
        </header>
        <ul v-if="topRooms.length" class="space-y-4">
          <li
            v-for="(room, index) in topRooms"
            :key="room.id"
            class="team-item"
          >
            <div
              class="avatar"
              :style="{ backgroundColor: getRoomColor(index) }"
            >
              {{ index + 1 }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-white font-medium truncate">{{ room.name }}</p>
              <small class="text-slate-400"
                >{{ room.bookings_count }} đặt phòng</small
              >
            </div>
            <span class="text-blue-300 font-semibold">{{
              formatCurrency(room.total_revenue)
            }}</span>
          </li>
        </ul>
        <div v-else class="text-center py-8 text-slate-400">
          <p>Chưa có dữ liệu</p>
        </div>
      </article>

      <article class="glass-card">
        <header class="panel-head">
          <div>
            <p class="text-sm text-slate-400">
              {{ $t("admin.quick_stats", "Thống kê nhanh") }}
            </p>
            <h3 class="text-2xl font-semibold text-white">
              {{ $t("admin.overview", "Tổng quan") }}
            </h3>
          </div>
        </header>
        <ul class="space-y-3">
          <li class="task-item">
            <label>
              <div
                class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center text-blue-400"
              >
                <span class="material-icons-outlined text-lg">bed</span>
              </div>
              <div>
                <p class="text-white font-medium">Tổng số phòng</p>
                <small class="text-slate-400">Phòng trong hệ thống</small>
              </div>
            </label>
            <span class="text-xl font-semibold text-white">{{
              stats.total_rooms || 0
            }}</span>
          </li>
          <li class="task-item">
            <label>
              <div
                class="w-8 h-8 rounded-lg bg-green-500/20 flex items-center justify-center text-green-400"
              >
                <span class="material-icons-outlined text-lg">hiking</span>
              </div>
              <div>
                <p class="text-white font-medium">Tổng tour</p>
                <small class="text-slate-400">Tour trong hệ thống</small>
              </div>
            </label>
            <span class="text-xl font-semibold text-white">{{
              stats.total_tours || 0
            }}</span>
          </li>
          <li class="task-item">
            <label>
              <div
                class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center text-purple-400"
              >
                <span class="material-icons-outlined text-lg">people</span>
              </div>
              <div>
                <p class="text-white font-medium">Người dùng</p>
                <small class="text-slate-400">Tài khoản đăng ký</small>
              </div>
            </label>
            <span class="text-xl font-semibold text-white">{{
              stats.total_users || 0
            }}</span>
          </li>
          <li class="task-item">
            <label>
              <div
                class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center text-amber-400"
              >
                <span class="material-icons-outlined text-lg"
                  >event_available</span
                >
              </div>
              <div>
                <p class="text-white font-medium">Đặt phòng hôm nay</p>
                <small class="text-slate-400">Booking mới</small>
              </div>
            </label>
            <span class="text-xl font-semibold text-white">{{
              stats.today_bookings || 0
            }}</span>
          </li>
        </ul>
      </article>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import api from "@/services/api";
import { useI18n } from "vue-i18n";

const { t } = useI18n();
const stats = ref({});
const loading = ref(true);

const formatCurrency = (value) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
    maximumFractionDigits: 0,
  }).format(value || 0);
};

const kpiCards = computed(() => [
  {
    label: "Bookings",
    value: stats.value.total_bookings ?? 0,
    delta: stats.value.bookings_delta ?? "+0%",
    caption: `${stats.value.bookings_7_days ?? 0} trong 7 ngày`,
    icon: "📦",
  },
  {
    label: "Rooms",
    value: stats.value.total_rooms ?? 0,
    delta: stats.value.rooms_delta ?? "+0%",
    caption: "Đang hoạt động",
    icon: "🏡",
  },
  {
    label: "Tours",
    value: stats.value.total_tours ?? 0,
    delta: stats.value.tours_delta ?? "+0%",
    caption: "Hoạt động",
    icon: "🧭",
  },
  {
    label: "Users",
    value: stats.value.total_users ?? 0,
    delta: stats.value.users_delta ?? "+0%",
    caption: `${stats.value.new_users_7_days ?? 0} khách mới`,
    icon: "👥",
  },
]);

const revenueTrend = computed(
  () => stats.value.revenue_trend || [25, 40, 55, 65, 58, 72, 90, 84, 92]
);
const revenueTotal = computed(() => stats.value.revenue_30_days ?? 0);
const revenueDelta = computed(() => stats.value.revenue_delta ?? "+0%");

const recentBookings = computed(() => stats.value.recent_bookings || []);

const occupancy = computed(
  () =>
    stats.value.occupancy || {
      average: 0,
      rooms: [],
    }
);

const topRooms = computed(() => stats.value.top_rooms || []);

const getRoomColor = (index) => {
  const colors = ["#38bdf8", "#22c55e", "#f97316", "#a855f7", "#ec4899"];
  return colors[index % colors.length];
};

const pendingTasks = computed(() => {
  const tasks = stats.value.pending_tasks || {};
  const result = [];

  if (tasks.bookings > 0) {
    result.push({
      title: `${tasks.bookings} booking chờ xác nhận`,
      detail: "Cần duyệt ngay",
      priority: "high",
      done: false,
    });
  }

  if (tasks.payments > 0) {
    result.push({
      title: `${tasks.payments} thanh toán đang chờ`,
      detail: "Kiểm tra thanh toán",
      priority: "high",
      done: false,
    });
  }

  if (tasks.reviews > 0) {
    result.push({
      title: `${tasks.reviews} đánh giá chờ duyệt`,
      detail: "Đánh giá khách hàng",
      priority: "medium",
      done: false,
    });
  }

  if (result.length === 0) {
    result.push({
      title: "Không có việc cần làm",
      detail: "Tất cả đã hoàn thành",
      priority: "low",
      done: true,
    });
  }

  return result;
});

onMounted(async () => {
  try {
    loading.value = true;
    const response = await api.get("/admin/dashboard/stats");
    stats.value = response.data;
  } catch (error) {
    console.error("Error loading stats:", error);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.dashboard-shell {
  color: #e2e8f0;
}

.kpi-card {
  padding: 1.75rem;
  border-radius: 1.5rem;
  background: rgba(15, 23, 42, 0.65);
  border: 1px solid rgba(148, 163, 184, 0.25);
  box-shadow: 0 25px 50px rgba(2, 6, 23, 0.5);
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.kpi-icon {
  font-size: 1.5rem;
}

.delta-pill {
  padding: 0.2rem 0.9rem;
  border-radius: 999px;
  font-size: 0.85rem;
}

.delta-pill.positive {
  background: rgba(34, 197, 94, 0.15);
  color: #4ade80;
}

.delta-pill.negative {
  background: rgba(248, 113, 113, 0.15);
  color: #fca5a5;
}

.gradient-panel {
  border-radius: 1.75rem;
  padding: 2rem;
  background: radial-gradient(
    circle at top,
    rgba(56, 189, 248, 0.35),
    rgba(15, 23, 42, 0.95)
  );
  border: 1px solid rgba(56, 189, 248, 0.35);
  box-shadow: 0 40px 80px rgba(14, 165, 233, 0.15);
}

.panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.trend-chart {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.trend-line {
  display: grid;
  grid-template-columns: repeat(9, minmax(0, 1fr));
  gap: 0.75rem;
  height: 160px;
  align-items: flex-end;
}

.trend-line span {
  display: block;
  background: linear-gradient(180deg, #38bdf8, transparent);
  border-radius: 999px;
}

.trend-legend {
  display: flex;
  gap: 1.25rem;
  font-size: 0.85rem;
  color: #cbd5f5;
}

.trend-legend span {
  width: 12px;
  height: 12px;
  border-radius: 999px;
  background: #38bdf8;
  display: inline-flex;
  margin-right: 0.4rem;
}

.glass-card {
  border-radius: 1.5rem;
  padding: 2rem;
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(148, 163, 184, 0.2);
  box-shadow: 0 25px 60px rgba(2, 6, 23, 0.55);
}

.booking-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.15);
}

.booking-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.status-pill {
  display: inline-flex;
  padding: 0.15rem 0.8rem;
  border-radius: 999px;
  font-size: 0.75rem;
  text-transform: capitalize;
}

.status-pill.confirmed {
  background: rgba(34, 197, 94, 0.15);
  color: #86efac;
}

.status-pill.pending {
  background: rgba(249, 115, 22, 0.15);
  color: #fdba74;
}

.status-pill.done {
  background: rgba(56, 189, 248, 0.15);
  color: #67e8f9;
}

.status-pill.in-progress {
  background: rgba(99, 102, 241, 0.15);
  color: #a5b4fc;
}

.occupancy-bar {
  width: 140px;
  height: 6px;
  background: rgba(148, 163, 184, 0.25);
  border-radius: 999px;
  position: relative;
}

.occupancy-bar span {
  position: absolute;
  inset: 0;
  border-radius: 999px;
  background: linear-gradient(90deg, #38bdf8, #a5b4fc);
}

.occupancy-visual {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.team-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.avatar {
  width: 42px;
  height: 42px;
  border-radius: 999px;
  display: grid;
  place-items: center;
  font-weight: 600;
  color: #0f172a;
}

.task-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem;
  border-radius: 1rem;
  background: rgba(15, 23, 42, 0.6);
}

.task-item label {
  display: flex;
  gap: 0.85rem;
  cursor: pointer;
}

.task-item input {
  accent-color: #38bdf8;
}

.priority {
  font-size: 0.75rem;
  padding: 0.25rem 0.8rem;
  border-radius: 999px;
  text-transform: capitalize;
}

.priority.high {
  background: rgba(244, 63, 94, 0.2);
  color: #fda4af;
}

.priority.medium {
  background: rgba(249, 115, 22, 0.2);
  color: #fdba74;
}

.priority.low {
  background: rgba(34, 197, 94, 0.15);
  color: #a7f3d0;
}

@media (max-width: 768px) {
  .panel-head {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
