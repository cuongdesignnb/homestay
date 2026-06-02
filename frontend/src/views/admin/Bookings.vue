<template>
  <div class="min-h-screen">
    <!-- Header with Stats -->
    <div
      class="bg-gradient-to-br from-slate-800 to-slate-700 rounded-2xl p-6 mb-6 text-white"
    >
      <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold flex items-center gap-3 mb-2">
            <span class="material-icons-outlined text-3xl">calendar_month</span>
            Quản lý Đặt chỗ
          </h1>
          <p class="text-slate-300">
            Theo dõi và điều phối booking phòng & tour
          </p>
        </div>
        <div class="flex gap-3">
          <button
            @click="loadBookings"
            :disabled="loading"
            class="flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg transition-all border border-white/20"
          >
            <span class="material-icons-outlined text-xl">refresh</span>
            Làm mới
          </button>
          <button
            @click="exportCsv"
            :disabled="loading || exporting"
            class="flex items-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 rounded-lg transition-all font-medium"
          >
            <span class="material-icons-outlined text-xl">download</span>
            {{ exporting ? "Đang xuất..." : "Xuất CSV" }}
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div
          class="flex items-center gap-4 bg-white/10 backdrop-blur rounded-xl p-4 border border-white/10"
        >
          <div
            class="w-12 h-12 rounded-xl bg-blue-500/30 flex items-center justify-center"
          >
            <span class="material-icons-outlined text-2xl">event_note</span>
          </div>
          <div>
            <div class="text-2xl font-bold">{{ stats.total }}</div>
            <div class="text-sm text-slate-300">Tổng booking</div>
          </div>
        </div>
        <div
          class="flex items-center gap-4 bg-white/10 backdrop-blur rounded-xl p-4 border border-white/10"
        >
          <div
            class="w-12 h-12 rounded-xl bg-amber-500/30 flex items-center justify-center"
          >
            <span class="material-icons-outlined text-2xl"
              >pending_actions</span
            >
          </div>
          <div>
            <div class="text-2xl font-bold">{{ stats.pending }}</div>
            <div class="text-sm text-slate-300">Chờ xác nhận</div>
          </div>
        </div>
        <div
          class="flex items-center gap-4 bg-white/10 backdrop-blur rounded-xl p-4 border border-white/10"
        >
          <div
            class="w-12 h-12 rounded-xl bg-emerald-500/30 flex items-center justify-center"
          >
            <span class="material-icons-outlined text-2xl">check_circle</span>
          </div>
          <div>
            <div class="text-2xl font-bold">{{ stats.confirmed }}</div>
            <div class="text-sm text-slate-300">Đã xác nhận</div>
          </div>
        </div>
        <div
          class="flex items-center gap-4 bg-white/10 backdrop-blur rounded-xl p-4 border border-white/10"
        >
          <div
            class="w-12 h-12 rounded-xl bg-purple-500/30 flex items-center justify-center"
          >
            <span class="material-icons-outlined text-2xl">payments</span>
          </div>
          <div>
            <div class="text-2xl font-bold">
              {{ formatCurrency(stats.revenue) }}
            </div>
            <div class="text-sm text-slate-300">Doanh thu tháng</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Controls Bar -->
    <div
      class="bg-white rounded-xl p-4 mb-6 border border-slate-200 flex flex-wrap justify-between items-center gap-4"
    >
      <!-- View Toggle -->
      <div class="flex bg-slate-100 rounded-lg p-1">
        <button
          @click="viewMode = 'list'"
          :class="[
            'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all',
            viewMode === 'list'
              ? 'bg-white text-slate-900 shadow'
              : 'text-slate-500 hover:text-slate-700',
          ]"
        >
          <span class="material-icons-outlined text-xl">view_list</span>
          Danh sách
        </button>
        <button
          @click="viewMode = 'calendar'"
          :class="[
            'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all',
            viewMode === 'calendar'
              ? 'bg-white text-slate-900 shadow'
              : 'text-slate-500 hover:text-slate-700',
          ]"
        >
          <span class="material-icons-outlined text-xl"
            >calendar_view_month</span
          >
          Lịch
        </button>
        <button
          @click="viewMode = 'timeline'"
          :class="[
            'flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all',
            viewMode === 'timeline'
              ? 'bg-white text-slate-900 shadow'
              : 'text-slate-500 hover:text-slate-700',
          ]"
        >
          <span class="material-icons-outlined text-xl">view_timeline</span>
          Timeline
        </button>
      </div>

      <!-- Filters -->
      <div class="flex items-center gap-3 flex-wrap">
        <select
          v-model="filters.type"
          class="px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white"
        >
          <option value="all">Tất cả loại</option>
          <option value="room">🏠 Phòng</option>
          <option value="tour">🎒 Tour</option>
        </select>
        <select
          v-model="filters.status"
          class="px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white"
        >
          <option value="all">Mọi trạng thái</option>
          <option value="pending">⏳ Chờ xác nhận</option>
          <option value="confirmed">✅ Đã xác nhận</option>
          <option value="checked_in">🏨 Đã nhận phòng</option>
          <option value="completed">🎉 Hoàn thành</option>
          <option value="cancelled">❌ Đã hủy</option>
        </select>
        <select
          v-model="filters.paymentStatus"
          class="px-3 py-2 border border-slate-200 rounded-lg text-sm bg-white"
        >
          <option value="all">Thanh toán</option>
          <option value="paid">💰 Đã thanh toán</option>
          <option value="pending">⏳ Chờ thanh toán</option>
        </select>
        <div
          class="flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg"
        >
          <span class="material-icons-outlined text-slate-400 text-xl"
            >search</span
          >
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Tìm mã booking, tên khách..."
            class="bg-transparent border-none outline-none text-sm w-48"
          />
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div
      v-if="loading"
      class="flex flex-col items-center justify-center py-16 text-slate-500"
    >
      <div
        class="w-10 h-10 border-3 border-slate-200 border-t-blue-500 rounded-full animate-spin mb-4"
      ></div>
      <p>Đang tải dữ liệu...</p>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="filteredBookings.length === 0 && viewMode === 'list'"
      class="flex flex-col items-center justify-center py-16 bg-white rounded-xl border border-slate-200"
    >
      <span class="material-icons-outlined text-6xl text-slate-300 mb-4"
        >event_busy</span
      >
      <h3 class="text-lg font-semibold text-slate-700 mb-2">
        Không có booking nào
      </h3>
      <p class="text-slate-500">Thử thay đổi bộ lọc để xem thêm kết quả</p>
    </div>

    <!-- List View -->
    <div
      v-else-if="viewMode === 'list'"
      class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4"
    >
      <div
        v-for="booking in filteredBookings"
        :key="booking.id"
        @click="openBookingDetail(booking)"
        class="bg-white rounded-xl border border-slate-200 overflow-hidden cursor-pointer transition-all hover:border-blue-400 hover:shadow-lg hover:shadow-blue-100 hover:-translate-y-0.5 group relative"
      >
        <!-- Card Header -->
        <div
          class="flex justify-between items-center px-4 py-3 bg-slate-50 border-b border-slate-100"
        >
          <div
            :class="[
              'flex items-center gap-2 text-xs font-semibold px-3 py-1.5 rounded-lg',
              booking.type === 'room'
                ? 'bg-blue-100 text-blue-700'
                : 'bg-emerald-100 text-emerald-700',
            ]"
          >
            <span class="material-icons-outlined text-base">{{
              booking.type === "room" ? "hotel" : "hiking"
            }}</span>
            {{ booking.type === "room" ? "Phòng" : "Tour" }}
          </div>
          <span class="font-mono text-sm text-slate-500"
            >#{{ booking.bookingNumber }}</span
          >
        </div>

        <!-- Card Body -->
        <div class="p-4">
          <h3 class="font-semibold text-slate-900 mb-3 text-lg">
            {{ booking.spaceName }}
          </h3>

          <!-- Guest Info -->
          <div class="flex gap-3 p-3 bg-slate-50 rounded-lg mb-4">
            <span class="material-icons-outlined text-slate-400">person</span>
            <div>
              <p class="font-medium text-slate-800">{{ booking.guestName }}</p>
              <p class="text-sm text-slate-500">{{ booking.contact }}</p>
            </div>
          </div>

          <!-- Date Range -->
          <div class="flex items-center gap-2">
            <div class="flex-1 flex items-center gap-2">
              <span class="material-icons-outlined text-slate-400 text-lg"
                >login</span
              >
              <div>
                <div class="text-xs text-slate-400 uppercase">Check-in</div>
                <div class="font-medium text-slate-700 text-sm">
                  {{ formatDate(booking.startDate) }}
                </div>
              </div>
            </div>
            <span class="text-slate-300 text-xl">→</span>
            <div class="flex-1 flex items-center gap-2">
              <span class="material-icons-outlined text-slate-400 text-lg"
                >logout</span
              >
              <div>
                <div class="text-xs text-slate-400 uppercase">Check-out</div>
                <div class="font-medium text-slate-700 text-sm">
                  {{ formatDate(booking.endDate) }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Card Footer -->
        <div
          class="flex justify-between items-center px-4 py-3 bg-slate-50 border-t border-slate-100"
        >
          <div class="text-lg font-bold text-teal-600">
            {{ formatCurrency(booking.amount) }}
          </div>
          <div class="flex gap-2">
            <span
              :class="[
                'px-2 py-1 rounded-full text-xs font-semibold',
                getStatusClass(booking.status),
              ]"
            >
              {{ getStatusLabel(booking.status) }}
            </span>
            <span
              :class="[
                'px-2 py-1 rounded-full text-xs font-semibold',
                booking.paymentStatus === 'paid'
                  ? 'bg-emerald-100 text-emerald-700'
                  : 'bg-amber-100 text-amber-700',
              ]"
            >
              {{ booking.paymentStatus === "paid" ? "✓ Đã TT" : "◷ Chờ TT" }}
            </span>
          </div>
        </div>

        <!-- Hover Actions -->
        <div
          class="absolute top-3 right-3 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity"
        >
          <button
            v-if="booking.status === 'pending'"
            @click.stop="updateStatus(booking, 'confirmed')"
            class="w-8 h-8 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg flex items-center justify-center shadow-lg"
            title="Xác nhận"
          >
            <span class="material-icons-outlined text-lg">check</span>
          </button>
          <button
            v-if="booking.status === 'confirmed'"
            @click.stop="updateStatus(booking, 'checked_in')"
            class="w-8 h-8 bg-blue-500 hover:bg-blue-600 text-white rounded-lg flex items-center justify-center shadow-lg"
            title="Check-in"
          >
            <span class="material-icons-outlined text-lg">door_front</span>
          </button>
          <button
            v-if="['pending', 'confirmed'].includes(booking.status)"
            @click.stop="updateStatus(booking, 'cancelled')"
            class="w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center justify-center shadow-lg"
            title="Hủy"
          >
            <span class="material-icons-outlined text-lg">close</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Calendar View -->
    <div v-else-if="viewMode === 'calendar'" class="calendar-container">
      <!-- Calendar Header -->
      <div class="calendar-header">
        <div class="calendar-nav">
          <button @click="prevMonth" class="nav-btn">
            <span class="material-icons-outlined">chevron_left</span>
          </button>
          <h2 class="calendar-title">{{ currentMonthLabel }}</h2>
          <button @click="nextMonth" class="nav-btn">
            <span class="material-icons-outlined">chevron_right</span>
          </button>
        </div>
        <button @click="goToToday" class="today-btn">
          <span class="material-icons-outlined">today</span>
          Hôm nay
        </button>
      </div>

      <!-- Weekday Headers -->
      <div class="weekday-row">
        <div
          v-for="(day, i) in weekdays"
          :key="day"
          :class="['weekday-cell', { weekend: i === 0 || i === 6 }]"
        >
          {{ day }}
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="calendar-grid">
        <div
          v-for="(day, index) in calendarDays"
          :key="index"
          :class="[
            'calendar-cell',
            {
              'other-month': !day.isCurrentMonth,
              'is-today': day.isToday,
              'has-bookings': day.bookings.length > 0,
            },
          ]"
        >
          <div class="cell-header">
            <span :class="['day-number', { 'today-badge': day.isToday }]">{{
              day.date
            }}</span>
            <span v-if="day.bookings.length" class="booking-count">{{
              day.bookings.length
            }}</span>
          </div>
          <div class="cell-bookings">
            <div
              v-for="booking in day.bookings.slice(0, 2)"
              :key="booking.id"
              @click="openBookingDetail(booking)"
              :class="['booking-item', booking.type, booking.status]"
              :title="`${booking.spaceName} - ${booking.guestName}`"
            >
              <span class="booking-icon">{{
                booking.type === "room" ? "🏠" : "🎒"
              }}</span>
              <span class="booking-name">{{ booking.spaceName }}</span>
            </div>
            <div
              v-if="day.bookings.length > 2"
              class="more-count"
              @click="showDayBookings(day)"
            >
              +{{ day.bookings.length - 2 }} khác
            </div>
          </div>
        </div>
      </div>

      <!-- Legend -->
      <div class="calendar-legend">
        <div class="legend-title">Chú thích:</div>
        <div class="legend-items">
          <div class="legend-item">
            <span class="legend-dot room"></span>
            <span>Phòng</span>
          </div>
          <div class="legend-item">
            <span class="legend-dot tour"></span>
            <span>Tour</span>
          </div>
          <div class="legend-divider"></div>
          <div class="legend-item">
            <span class="legend-dot pending"></span>
            <span>Chờ xác nhận</span>
          </div>
          <div class="legend-item">
            <span class="legend-dot confirmed"></span>
            <span>Đã xác nhận</span>
          </div>
          <div class="legend-item">
            <span class="legend-dot checked_in"></span>
            <span>Đã check-in</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Timeline View -->
    <div
      v-else-if="viewMode === 'timeline'"
      class="bg-white rounded-xl border border-slate-200 overflow-hidden"
    >
      <div class="p-4 border-b border-slate-200 bg-slate-50">
        <h2 class="text-xl font-bold text-slate-800">Lịch trình 30 ngày tới</h2>
        <p class="text-sm text-slate-500 mt-1">Theo dõi các booking sắp tới</p>
      </div>

      <div class="divide-y divide-slate-100">
        <div v-for="(group, date) in timelineGroups" :key="date" class="flex">
          <!-- Date Column -->
          <div class="w-32 shrink-0 p-4 bg-slate-50 border-r border-slate-100">
            <div class="text-2xl font-bold text-slate-800">
              {{ formatTimelineDay(date) }}
            </div>
            <div class="text-sm text-slate-500">
              {{ formatTimelineMonth(date) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">
              {{ formatTimelineWeekday(date) }}
            </div>
          </div>

          <!-- Bookings Column -->
          <div class="flex-1 p-4 space-y-3">
            <div
              v-for="booking in group"
              :key="booking.id"
              @click="openBookingDetail(booking)"
              class="flex items-center gap-4 p-3 rounded-lg bg-slate-50 hover:bg-slate-100 cursor-pointer transition-colors"
            >
              <div
                :class="[
                  'w-10 h-10 rounded-lg flex items-center justify-center',
                  booking.type === 'room'
                    ? 'bg-blue-100 text-blue-600'
                    : 'bg-emerald-100 text-emerald-600',
                ]"
              >
                <span class="material-icons-outlined">{{
                  booking.type === "room" ? "hotel" : "hiking"
                }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-medium text-slate-800 truncate">
                  {{ booking.spaceName }}
                </div>
                <div class="text-sm text-slate-500">
                  {{ booking.guestName }}
                </div>
              </div>
              <div class="text-right">
                <div class="font-semibold text-teal-600">
                  {{ formatCurrency(booking.amount) }}
                </div>
                <span
                  :class="[
                    'inline-block px-2 py-0.5 rounded text-xs font-medium',
                    getStatusClass(booking.status),
                  ]"
                >
                  {{ getStatusLabel(booking.status) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div
          v-if="Object.keys(timelineGroups).length === 0"
          class="p-8 text-center text-slate-500"
        >
          <span class="material-icons-outlined text-4xl text-slate-300 mb-2"
            >event_busy</span
          >
          <p>Không có booking nào trong 30 ngày tới</p>
        </div>
      </div>
    </div>

    <!-- Booking Detail Modal -->
    <Teleport to="body">
      <div
        v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="showModal = false"
      >
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
        <div
          class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-auto"
        >
          <!-- Modal Header -->
          <div
            class="sticky top-0 flex items-center justify-between p-4 border-b border-slate-200 bg-white"
          >
            <h3 class="text-lg font-bold text-slate-800">Chi tiết Booking</h3>
            <button
              @click="showModal = false"
              class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100"
            >
              <span class="material-icons-outlined">close</span>
            </button>
          </div>

          <div v-if="selectedBooking" class="p-6 space-y-6">
            <!-- Booking Info -->
            <div class="flex items-center gap-4">
              <div
                :class="[
                  'w-14 h-14 rounded-xl flex items-center justify-center',
                  selectedBooking.type === 'room'
                    ? 'bg-blue-100 text-blue-600'
                    : 'bg-emerald-100 text-emerald-600',
                ]"
              >
                <span class="material-icons-outlined text-2xl">{{
                  selectedBooking.type === "room" ? "hotel" : "hiking"
                }}</span>
              </div>
              <div>
                <h4 class="text-lg font-bold text-slate-800">
                  {{ selectedBooking.spaceName }}
                </h4>
                <p class="text-sm text-slate-500 font-mono">
                  #{{ selectedBooking.bookingNumber }}
                </p>
              </div>
            </div>

            <!-- Guest Info -->
            <div class="p-4 bg-slate-50 rounded-xl">
              <h5 class="text-sm font-semibold text-slate-600 uppercase mb-3">
                Thông tin khách
              </h5>
              <div class="space-y-2">
                <div class="flex items-center gap-3">
                  <span class="material-icons-outlined text-slate-400"
                    >person</span
                  >
                  <span class="font-medium">{{
                    selectedBooking.guestName
                  }}</span>
                </div>
                <div class="flex items-center gap-3">
                  <span class="material-icons-outlined text-slate-400"
                    >email</span
                  >
                  <span>{{ selectedBooking.contact }}</span>
                </div>
                <div
                  v-if="selectedBooking.phone"
                  class="flex items-center gap-3"
                >
                  <span class="material-icons-outlined text-slate-400"
                    >phone</span
                  >
                  <span>{{ selectedBooking.phone }}</span>
                </div>
              </div>

              <!-- Participants / Guests -->
              <div
                v-if="selectedBooking.type === 'tour' && selectedBooking.participants"
                class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-200"
              >
                <span class="material-icons-outlined text-slate-400">group</span>
                <div>
                  <span class="font-medium">{{ selectedBooking.participants }} người</span>
                  <span v-if="selectedBooking.pricePerPerson" class="text-sm text-slate-500 ml-2">
                    ({{ formatCurrency(selectedBooking.pricePerPerson) }}/người)
                  </span>
                </div>
              </div>
              <div
                v-else-if="selectedBooking.type === 'room' && selectedBooking.guests"
                class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-200"
              >
                <span class="material-icons-outlined text-slate-400">group</span>
                <span class="font-medium">{{ selectedBooking.guests }} khách</span>
              </div>
            </div>

            <!-- Dates & Amount -->
            <div class="grid grid-cols-2 gap-4">
              <div class="p-4 bg-slate-50 rounded-xl">
                <div class="text-sm text-slate-500 mb-1">Check-in</div>
                <div class="font-semibold text-slate-800">
                  {{ formatDate(selectedBooking.startDate) }}
                </div>
              </div>
              <div class="p-4 bg-slate-50 rounded-xl">
                <div class="text-sm text-slate-500 mb-1">Check-out</div>
                <div class="font-semibold text-slate-800">
                  {{ formatDate(selectedBooking.endDate) }}
                </div>
              </div>
            </div>

            <div class="p-4 bg-teal-50 rounded-xl">
              <div class="text-sm text-teal-600 mb-1">Tổng tiền</div>
              <div class="text-2xl font-bold text-teal-700">
                {{ formatCurrency(selectedBooking.amount) }}
              </div>
            </div>

            <!-- Status Actions -->
            <div>
              <h5 class="text-sm font-semibold text-slate-600 uppercase mb-3">
                Cập nhật trạng thái
              </h5>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="status in availableStatuses"
                  :key="status.value"
                  @click="updateStatus(selectedBooking, status.value)"
                  :disabled="selectedBooking.status === status.value"
                  :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition-all flex items-center gap-2',
                    selectedBooking.status === status.value
                      ? 'bg-slate-200 text-slate-500 cursor-not-allowed'
                      : 'bg-slate-100 hover:bg-slate-200 text-slate-700',
                  ]"
                >
                  <span class="material-icons-outlined text-lg">{{
                    status.icon
                  }}</span>
                  {{ status.label }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import dayjs from "dayjs";
import api from "@/services/api";

// State
const loading = ref(true);
const exporting = ref(false);
const viewMode = ref("list");
const searchQuery = ref("");
const showModal = ref(false);
const selectedBooking = ref(null);
const currentMonth = ref(dayjs());

const filters = ref({
  type: "all",
  status: "all",
  paymentStatus: "all",
});

const bookings = ref([]);

const stats = computed(() => {
  const total = bookings.value.length;
  const pending = bookings.value.filter((b) => b.status === "pending").length;
  const confirmed = bookings.value.filter(
    (b) => b.status === "confirmed"
  ).length;
  const revenue = bookings.value
    .filter((b) => b.paymentStatus === "paid")
    .reduce((sum, b) => sum + parseFloat(b.amount || 0), 0);
  return { total, pending, confirmed, revenue };
});

const filteredBookings = computed(() => {
  return bookings.value.filter((booking) => {
    if (filters.value.type !== "all" && booking.type !== filters.value.type)
      return false;
    if (
      filters.value.status !== "all" &&
      booking.status !== filters.value.status
    )
      return false;
    if (
      filters.value.paymentStatus !== "all" &&
      booking.paymentStatus !== filters.value.paymentStatus
    )
      return false;
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase();
      const searchable =
        `${booking.bookingNumber} ${booking.guestName} ${booking.spaceName} ${booking.contact}`.toLowerCase();
      if (!searchable.includes(q)) return false;
    }
    return true;
  });
});

// Calendar
const weekdays = ["CN", "T2", "T3", "T4", "T5", "T6", "T7"];

const currentMonthLabel = computed(() => {
  return currentMonth.value.format("MMMM YYYY");
});

const calendarDays = computed(() => {
  const start = currentMonth.value.startOf("month").startOf("week");
  const end = currentMonth.value.endOf("month").endOf("week");
  const days = [];
  let day = start;

  while (day.isBefore(end) || day.isSame(end, "day")) {
    const dateStr = day.format("YYYY-MM-DD");
    const dayBookings = bookings.value.filter((b) => {
      const checkIn = dayjs(b.startDate);
      const checkOut = dayjs(b.endDate);
      return (
        (day.isSame(checkIn, "day") || day.isAfter(checkIn)) &&
        (day.isSame(checkOut, "day") || day.isBefore(checkOut))
      );
    });

    days.push({
      date: day.date(),
      fullDate: dateStr,
      isCurrentMonth: day.month() === currentMonth.value.month(),
      isToday: day.isSame(dayjs(), "day"),
      bookings: dayBookings,
    });
    day = day.add(1, "day");
  }
  return days;
});

const prevMonth = () => {
  currentMonth.value = currentMonth.value.subtract(1, "month");
};
const nextMonth = () => {
  currentMonth.value = currentMonth.value.add(1, "month");
};
const goToToday = () => {
  currentMonth.value = dayjs();
};

const showDayBookings = (day) => {
  // If there are bookings, open the first one's detail
  if (day.bookings.length > 0) {
    openBookingDetail(day.bookings[0]);
  }
};

// Timeline
const timelineGroups = computed(() => {
  const today = dayjs();
  const next30Days = today.add(30, "day");
  const groups = {};

  const upcoming = bookings.value.filter((b) => {
    const checkIn = dayjs(b.startDate);
    return (
      checkIn.isAfter(today.subtract(1, "day")) && checkIn.isBefore(next30Days)
    );
  });

  upcoming.forEach((booking) => {
    const dateKey = dayjs(booking.startDate).format("YYYY-MM-DD");
    if (!groups[dateKey]) groups[dateKey] = [];
    groups[dateKey].push(booking);
  });

  // Sort by date
  const sorted = {};
  Object.keys(groups)
    .sort()
    .forEach((key) => {
      sorted[key] = groups[key];
    });
  return sorted;
});

const formatTimelineDay = (date) => dayjs(date).format("DD");
const formatTimelineMonth = (date) => dayjs(date).format("MMM");
const formatTimelineWeekday = (date) => dayjs(date).format("dddd");

// Status
const availableStatuses = [
  { value: "pending", label: "Chờ xác nhận", icon: "pending_actions" },
  { value: "confirmed", label: "Xác nhận", icon: "check_circle" },
  { value: "checked_in", label: "Đã nhận phòng", icon: "door_front" },
  { value: "completed", label: "Hoàn thành", icon: "task_alt" },
  { value: "cancelled", label: "Hủy", icon: "cancel" },
];

const getStatusLabel = (status) => {
  const labels = {
    pending: "Chờ xác nhận",
    confirmed: "Đã xác nhận",
    checked_in: "Đã nhận phòng",
    completed: "Hoàn thành",
    cancelled: "Đã hủy",
  };
  return labels[status] || status;
};

const getStatusClass = (status) => {
  const classes = {
    pending: "bg-amber-100 text-amber-700",
    confirmed: "bg-emerald-100 text-emerald-700",
    checked_in: "bg-blue-100 text-blue-700",
    completed: "bg-emerald-100 text-emerald-800",
    cancelled: "bg-red-100 text-red-700",
  };
  return classes[status] || "bg-slate-100 text-slate-700";
};

// Helpers
const formatDate = (date) => dayjs(date).format("DD/MM/YYYY");
const formatCurrency = (amount) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount || 0);
};

const openBookingDetail = (booking) => {
  selectedBooking.value = booking;
  showModal.value = true;
};

// API
const loadBookings = async () => {
  loading.value = true;
  try {
    // Load both room and tour bookings
    const [roomRes, tourRes] = await Promise.all([
      api.get("/admin/bookings"),
      api.get("/admin/tour-bookings"),
    ]);

    const roomBookings = (roomRes.data.data || roomRes.data || []).map((b) => ({
      id: `room-${b.id}`,
      type: "room",
      bookingNumber: b.booking_number || `BK${b.id}`,
      spaceName: b.room?.name || "Phòng",
      guestName: b.user?.name || b.guest_name || "Khách",
      contact: b.user?.email || b.guest_email || "",
      phone: b.user?.phone || b.guest_phone || "",
      startDate: b.check_in,
      endDate: b.check_out,
      guests: b.guests || 1,
      amount: b.total_price || b.total_amount || b.final_amount,
      status: b.status,
      paymentStatus: b.payment_status,
      raw: b,
    }));

    const tourBookings = (tourRes.data.data || tourRes.data || []).map((b) => ({
      id: `tour-${b.id}`,
      type: "tour",
      bookingNumber: b.booking_number || `TB${b.id}`,
      spaceName: b.tour?.name || "Tour",
      guestName: b.user?.name || b.contact_name || b.guest_name || "Khách",
      contact: b.user?.email || b.contact_email || b.guest_email || "",
      phone: b.user?.phone || b.contact_phone || b.guest_phone || "",
      startDate: b.tour_date || b.start_date,
      endDate: b.tour_date || b.end_date,
      participants: b.participants || 1,
      pricePerPerson: b.price_per_person || 0,
      amount: b.total_price || b.total_amount || b.final_amount,
      status: b.status,
      paymentStatus: b.payment_status,
      raw: b,
    }));

    bookings.value = [...roomBookings, ...tourBookings].sort(
      (a, b) => dayjs(b.startDate).valueOf() - dayjs(a.startDate).valueOf()
    );
  } catch (error) {
    console.error("Failed to load bookings:", error);
  } finally {
    loading.value = false;
  }
};

const updateStatus = async (booking, newStatus) => {
  try {
    const endpoint =
      booking.type === "room"
        ? `/admin/bookings/${booking.raw.id}/status`
        : `/admin/tour-bookings/${booking.raw.id}/status`;

    await api.put(endpoint, { status: newStatus });
    booking.status = newStatus;
    if (selectedBooking.value?.id === booking.id) {
      selectedBooking.value.status = newStatus;
    }
  } catch (error) {
    console.error("Failed to update status:", error);
    alert("Không thể cập nhật trạng thái");
  }
};

const exportCsv = async () => {
  exporting.value = true;
  try {
    // Simple CSV export
    const headers = [
      "Mã booking",
      "Loại",
      "Tên",
      "Khách",
      "Check-in",
      "Check-out",
      "Số tiền",
      "Trạng thái",
      "Thanh toán",
    ];
    const rows = filteredBookings.value.map((b) => [
      b.bookingNumber,
      b.type === "room" ? "Phòng" : "Tour",
      b.spaceName,
      b.guestName,
      b.startDate,
      b.endDate,
      b.amount,
      getStatusLabel(b.status),
      b.paymentStatus === "paid" ? "Đã TT" : "Chờ TT",
    ]);

    const csv = [headers, ...rows].map((r) => r.join(",")).join("\n");
    const blob = new Blob(["\ufeff" + csv], {
      type: "text/csv;charset=utf-8;",
    });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = `bookings-${dayjs().format("YYYY-MM-DD")}.csv`;
    link.click();
    URL.revokeObjectURL(url);
  } catch (error) {
    console.error("Export failed:", error);
  } finally {
    exporting.value = false;
  }
};

onMounted(() => {
  loadBookings();
});
</script>

<style scoped>
.material-icons-outlined {
  font-family: "Material Icons Outlined";
  font-weight: normal;
  font-style: normal;
  font-size: 24px;
  line-height: 1;
  letter-spacing: normal;
  text-transform: none;
  display: inline-block;
  white-space: nowrap;
  word-wrap: normal;
  direction: ltr;
  -webkit-font-smoothing: antialiased;
}

/* Spinner animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
.animate-spin {
  animation: spin 1s linear infinite;
}

/* Border width utility */
.border-3 {
  border-width: 3px;
}

/* ===== CALENDAR STYLES ===== */
.calendar-container {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  overflow: hidden;
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
  color: white;
}

.calendar-nav {
  display: flex;
  align-items: center;
  gap: 16px;
}

.nav-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 10px;
  color: white;
  cursor: pointer;
  transition: all 0.2s;
}

.nav-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.05);
}

.calendar-title {
  font-size: 1.5rem;
  font-weight: 700;
  min-width: 200px;
  text-align: center;
}

.today-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: white;
  color: #1e40af;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.today-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.today-btn .material-icons-outlined {
  font-size: 20px;
}

/* Weekday Row */
.weekday-row {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
}

.weekday-cell {
  padding: 14px 8px;
  text-align: center;
  font-weight: 600;
  font-size: 0.875rem;
  color: #475569;
}

.weekday-cell.weekend {
  color: #ef4444;
}

/* Calendar Grid */
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
}

.calendar-cell {
  min-height: 120px;
  padding: 8px;
  border-right: 1px solid #e2e8f0;
  border-bottom: 1px solid #e2e8f0;
  background: white;
  transition: all 0.2s;
}

.calendar-cell:nth-child(7n) {
  border-right: none;
}

.calendar-cell:hover {
  background: #f8fafc;
}

.calendar-cell.other-month {
  background: #f1f5f9;
}

.calendar-cell.other-month .day-number {
  color: #94a3b8;
}

.calendar-cell.is-today {
  background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
}

.calendar-cell.has-bookings {
  background: #fefce8;
}

.calendar-cell.is-today.has-bookings {
  background: linear-gradient(135deg, #dbeafe 0%, #fef9c3 100%);
}

.cell-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.day-number {
  font-size: 0.9rem;
  font-weight: 600;
  color: #334155;
}

.today-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: white;
  border-radius: 50%;
  font-weight: 700;
}

.booking-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  height: 20px;
  padding: 0 6px;
  background: #3b82f6;
  color: white;
  border-radius: 10px;
  font-size: 0.7rem;
  font-weight: 600;
}

/* Booking Items */
.cell-bookings {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.booking-item {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border-left: 3px solid;
}

.booking-item:hover {
  transform: translateX(2px);
}

/* Room booking */
.booking-item.room {
  background: #dbeafe;
  color: #1e40af;
  border-left-color: #2563eb;
}

.booking-item.room:hover {
  background: #bfdbfe;
}

/* Tour booking */
.booking-item.tour {
  background: #d1fae5;
  color: #065f46;
  border-left-color: #10b981;
}

.booking-item.tour:hover {
  background: #a7f3d0;
}

/* Status variants */
.booking-item.pending {
  opacity: 0.8;
  border-left-style: dashed;
}

.booking-item.confirmed {
  border-left-width: 4px;
}

.booking-item.checked_in {
  border-left-width: 4px;
  font-weight: 600;
}

.booking-item.cancelled {
  opacity: 0.5;
  text-decoration: line-through;
}

.booking-icon {
  font-size: 0.85rem;
}

.booking-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.more-count {
  font-size: 0.7rem;
  color: #6366f1;
  font-weight: 600;
  padding: 2px 8px;
  background: #e0e7ff;
  border-radius: 4px;
  cursor: pointer;
  text-align: center;
}

.more-count:hover {
  background: #c7d2fe;
}

/* Legend */
.calendar-legend {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 24px;
  background: #f8fafc;
  border-top: 2px solid #e2e8f0;
  flex-wrap: wrap;
}

.legend-title {
  font-weight: 600;
  color: #475569;
  font-size: 0.875rem;
}

.legend-items {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8rem;
  color: #64748b;
}

.legend-dot {
  width: 12px;
  height: 12px;
  border-radius: 4px;
}

.legend-dot.room {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.legend-dot.tour {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.legend-dot.pending {
  background: #fbbf24;
}

.legend-dot.confirmed {
  background: #22c55e;
}

.legend-dot.checked_in {
  background: #6366f1;
}

.legend-divider {
  width: 1px;
  height: 20px;
  background: #cbd5e1;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .calendar-header {
    flex-direction: column;
    gap: 12px;
  }

  .calendar-cell {
    min-height: 80px;
    padding: 4px;
  }

  .booking-item {
    padding: 2px 4px;
    font-size: 0.65rem;
  }

  .booking-icon {
    display: none;
  }
}
</style>
