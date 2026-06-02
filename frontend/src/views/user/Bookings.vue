<template>
  <div class="bookings-page">
    <!-- Hero Section -->
    <section class="page-hero">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <Breadcrumb :items="breadcrumbItems" />
        <h1>{{ $t("nav.bookings") }}</h1>
        <p class="hero-subtitle">
          {{
            $t("bookings.subtitle", "Manage your room and tour reservations")
          }}
        </p>
      </div>
    </section>

    <div class="container mx-auto px-4 py-12 max-w-6xl">
      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon rooms">🏠</div>
          <div class="stat-info">
            <span class="stat-value">{{ roomBookings.length }}</span>
            <span class="stat-label">{{
              $t("bookings.room_bookings", "Room Bookings")
            }}</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon tours">🎒</div>
          <div class="stat-info">
            <span class="stat-value">{{ tourBookings.length }}</span>
            <span class="stat-label">{{
              $t("bookings.tour_bookings", "Tour Bookings")
            }}</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon pending">⏳</div>
          <div class="stat-info">
            <span class="stat-value">{{ pendingCount }}</span>
            <span class="stat-label">{{
              $t("bookings.pending", "Pending")
            }}</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon completed">✓</div>
          <div class="stat-info">
            <span class="stat-value">{{ confirmedCount }}</span>
            <span class="stat-label">{{
              $t("bookings.confirmed", "Confirmed")
            }}</span>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="tabs-wrapper">
        <div class="tabs">
          <button
            @click="activeTab = 'rooms'"
            :class="['tab', { active: activeTab === 'rooms' }]"
          >
            <span class="tab-icon">🏠</span>
            {{ $t("bookings.room_bookings", "Room Bookings") }}
            <span class="tab-badge">{{ roomBookings.length }}</span>
          </button>
          <button
            @click="activeTab = 'tours'"
            :class="['tab', { active: activeTab === 'tours' }]"
          >
            <span class="tab-icon">🎒</span>
            {{ $t("bookings.tour_bookings", "Tour Bookings") }}
            <span class="tab-badge">{{ tourBookings.length }}</span>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner-lg"></div>
        <p>{{ $t("common.loading") }}</p>
      </div>

      <!-- Room Bookings -->
      <div v-else-if="activeTab === 'rooms'" class="bookings-list">
        <div v-if="roomBookings.length === 0" class="empty-state">
          <div class="empty-icon">🏠</div>
          <h3>{{ $t("bookings.no_room_bookings", "No room bookings yet") }}</h3>
          <p>
            {{
              $t(
                "bookings.explore_rooms",
                "Start exploring our beautiful rooms"
              )
            }}
          </p>
          <router-link to="/rooms" class="btn btn-primary">
            {{ $t("bookings.browse_rooms", "Browse Rooms") }}
          </router-link>
        </div>

        <TransitionGroup name="list" tag="div" class="bookings-grid">
          <article
            v-for="booking in roomBookings"
            :key="booking.id"
            class="booking-card"
          >
            <div class="booking-image">
              <img
                :src="getRoomImage(booking.room)"
                :alt="booking.room?.name"
              />
              <span :class="['status-badge', booking.status]">
                {{ getStatusLabel(booking.status) }}
              </span>
            </div>

            <div class="booking-content">
              <div class="booking-header">
                <h3>{{ booking.room?.name }}</h3>
                <span class="booking-number"
                  >#{{ booking.booking_number }}</span
                >
              </div>

              <div class="booking-details">
                <div class="detail-item">
                  <span class="detail-icon">📅</span>
                  <div>
                    <span class="detail-label">{{ $t("rooms.check_in") }}</span>
                    <span class="detail-value"
                      >{{ formatDate(booking.check_in) }}
                      <small>{{
                        booking.check_in_time || "14:00"
                      }}</small></span
                    >
                  </div>
                </div>
                <div class="detail-item">
                  <span class="detail-icon">📅</span>
                  <div>
                    <span class="detail-label">{{
                      $t("rooms.check_out")
                    }}</span>
                    <span class="detail-value"
                      >{{ formatDate(booking.check_out) }}
                      <small>{{
                        booking.check_out_time || "12:00"
                      }}</small></span
                    >
                  </div>
                </div>
                <div class="detail-item">
                  <span class="detail-icon">👥</span>
                  <div>
                    <span class="detail-label">{{ $t("rooms.guests") }}</span>
                    <span class="detail-value">{{ booking.guests }}</span>
                  </div>
                </div>
                <div class="detail-item">
                  <span class="detail-icon">⏰</span>
                  <div>
                    <span class="detail-label">{{
                      $t("bookings.nights", "Nights")
                    }}</span>
                    <span class="detail-value">{{
                      getNights(booking.check_in, booking.check_out)
                    }}</span>
                  </div>
                </div>
              </div>

              <div class="booking-footer">
                <div class="booking-price">
                  <span class="price-label">{{ $t("common.total") }}</span>
                  <span class="price-value">{{
                    formatPrice(booking.final_amount)
                  }}</span>
                  <span
                    v-if="booking.payment_method === 'pay_at_checkin'"
                    class="payment-method-badge pay-at-checkin"
                  >
                    {{ $t("common.pay_at_checkin", "Thanh toán tại quầy") }}
                  </span>
                  <span
                    v-else-if="booking.payment_status === 'paid'"
                    class="payment-method-badge paid"
                  >
                    {{ $t("common.paid", "Đã thanh toán") }}
                  </span>
                </div>
                <div class="booking-actions">
                  <router-link
                    :to="`/rooms/${booking.room_id}`"
                    class="btn btn-outline"
                  >
                    {{ $t("bookings.view_room", "View Room") }}
                  </router-link>
                  <button
                    v-if="booking.status === 'pending'"
                    @click="cancelBooking(booking.id)"
                    class="btn btn-danger"
                  >
                    {{ $t("common.cancel") }}
                  </button>
                </div>
              </div>
            </div>
          </article>
        </TransitionGroup>
      </div>

      <!-- Tour Bookings -->
      <div v-else-if="activeTab === 'tours'" class="bookings-list">
        <div v-if="tourBookings.length === 0" class="empty-state">
          <div class="empty-icon">🎒</div>
          <h3>{{ $t("bookings.no_tour_bookings", "No tour bookings yet") }}</h3>
          <p>
            {{
              $t(
                "bookings.explore_tours",
                "Discover amazing tours and adventures"
              )
            }}
          </p>
          <router-link to="/tours" class="btn btn-primary">
            {{ $t("bookings.browse_tours", "Browse Tours") }}
          </router-link>
        </div>

        <TransitionGroup name="list" tag="div" class="bookings-grid">
          <article
            v-for="booking in tourBookings"
            :key="booking.id"
            class="booking-card tour"
          >
            <div class="booking-image">
              <img
                :src="getTourImage(booking.tour)"
                :alt="booking.tour?.name"
              />
              <span :class="['status-badge', booking.status]">
                {{ getStatusLabel(booking.status) }}
              </span>
            </div>

            <div class="booking-content">
              <div class="booking-header">
                <h3>{{ booking.tour?.name }}</h3>
                <span class="booking-number"
                  >#{{ booking.booking_number }}</span
                >
              </div>

              <div class="booking-details">
                <div class="detail-item">
                  <span class="detail-icon">📅</span>
                  <div>
                    <span class="detail-label">{{
                      $t("tours.tour_date")
                    }}</span>
                    <span class="detail-value">{{
                      formatDate(booking.tour_date)
                    }}</span>
                  </div>
                </div>
                <div class="detail-item">
                  <span class="detail-icon">👥</span>
                  <div>
                    <span class="detail-label">{{
                      $t("tours.participants")
                    }}</span>
                    <span class="detail-value">{{ booking.participants }}</span>
                  </div>
                </div>
                <div class="detail-item">
                  <span class="detail-icon">⏱️</span>
                  <div>
                    <span class="detail-label">{{ $t("tours.duration") }}</span>
                    <span class="detail-value"
                      >{{ booking.tour?.duration }} {{ $t("tours.days") }}</span
                    >
                  </div>
                </div>
              </div>

              <div class="booking-footer">
                <div class="booking-price">
                  <span class="price-label">{{ $t("common.total") }}</span>
                  <span class="price-value">{{
                    formatPrice(booking.final_amount)
                  }}</span>
                </div>
                <div class="booking-actions">
                  <router-link
                    :to="`/tours/${booking.tour_id}`"
                    class="btn btn-outline"
                  >
                    {{ $t("bookings.view_tour", "View Tour") }}
                  </router-link>
                </div>
              </div>
            </div>
          </article>
        </TransitionGroup>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import dayjs from "dayjs";
import Breadcrumb from "@/components/Breadcrumb.vue";

const { t } = useI18n();

const activeTab = ref("tours");
const roomBookings = ref([]);
const tourBookings = ref([]);
const loading = ref(true);

const breadcrumbItems = computed(() => [
  { label: t("nav.home"), path: "/" },
  { label: t("nav.bookings"), path: "/bookings" },
]);

const pendingCount = computed(() => {
  const roomPending = roomBookings.value.filter(
    (b) => b.status === "pending"
  ).length;
  const tourPending = tourBookings.value.filter(
    (b) => b.status === "pending"
  ).length;
  return roomPending + tourPending;
});

const confirmedCount = computed(() => {
  const roomConfirmed = roomBookings.value.filter(
    (b) => b.status === "confirmed"
  ).length;
  const tourConfirmed = tourBookings.value.filter(
    (b) => b.status === "confirmed"
  ).length;
  return roomConfirmed + tourConfirmed;
});

const formatDate = (date) => {
  return dayjs(date).format("DD MMM YYYY");
};

const formatPrice = (price) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(price);
};

const getNights = (checkIn, checkOut) => {
  return dayjs(checkOut).diff(dayjs(checkIn), "day");
};

const getStatusLabel = (status) => {
  const labels = {
    pending: t("bookings.status_pending", "Pending"),
    confirmed: t("bookings.status_confirmed", "Confirmed"),
    cancelled: t("bookings.status_cancelled", "Cancelled"),
    completed: t("bookings.status_completed", "Completed"),
  };
  return labels[status] || status;
};

const getRoomImage = (room) => {
  if (!room) return "/images/room-placeholder.jpg";
  if (room.cover_image) return room.cover_image;
  if (room.images && room.images.length > 0) return room.images[0];
  return "/images/room-placeholder.jpg";
};

const getTourImage = (tour) => {
  if (!tour) return "/images/tour-placeholder.jpg";
  if (tour.cover_image) return tour.cover_image;
  if (tour.images && tour.images.length > 0) return tour.images[0];
  return "/images/tour-placeholder.jpg";
};

const cancelBooking = async (id) => {
  if (
    !confirm(
      t(
        "bookings.cancel_confirm",
        "Are you sure you want to cancel this booking?"
      )
    )
  )
    return;

  try {
    await api.put(`/bookings/${id}/cancel`);
    loadBookings();
  } catch (error) {
    alert(t("bookings.cancel_failed", "Failed to cancel booking"));
  }
};

const loadBookings = async () => {
  loading.value = true;
  try {
    const [roomsRes, toursRes] = await Promise.all([
      api.get("/bookings"),
      api.get("/tour-bookings"),
    ]);

    roomBookings.value = roomsRes.data.data;
    tourBookings.value = toursRes.data.data;
  } catch (error) {
    console.error("Error loading bookings:", error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadBookings();
});
</script>

<style scoped>
.bookings-page {
  min-height: 100vh;
  background: var(--bg-primary, #f8fafc);
}

/* Hero Section */
.page-hero {
  position: relative;
  background: linear-gradient(135deg, #0d9488 0%, #0891b2 50%, #0284c7 100%);
  padding: 4rem 1rem 3rem;
  margin-bottom: 0;
}

.hero-overlay {
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}

.hero-content {
  position: relative;
  max-width: 1200px;
  margin: 0 auto;
  text-align: center;
  color: white;
}

.hero-content h1 {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 1rem 0 0.5rem;
}

.hero-subtitle {
  font-size: 1.1rem;
  opacity: 0.9;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

@media (max-width: 900px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: white;
  padding: 1.25rem;
  border-radius: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-icon.rooms {
  background: #dbeafe;
}
.stat-icon.tours {
  background: #dcfce7;
}
.stat-icon.pending {
  background: #fef3c7;
}
.stat-icon.completed {
  background: #d1fae5;
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
}

.stat-label {
  font-size: 0.85rem;
  color: #64748b;
}

/* Tabs */
.tabs-wrapper {
  margin-bottom: 2rem;
}

.tabs {
  display: flex;
  gap: 0.5rem;
  background: white;
  padding: 0.5rem;
  border-radius: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
}

.tab {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 1rem;
  border: none;
  background: transparent;
  border-radius: 0.75rem;
  font-size: 1rem;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s;
}

.tab:hover {
  background: #f1f5f9;
}

.tab.active {
  background: linear-gradient(135deg, #0d9488, #0891b2);
  color: white;
}

.tab-icon {
  font-size: 1.25rem;
}

.tab-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.8rem;
  font-weight: 600;
}

.tab:not(.active) .tab-badge {
  background: #e2e8f0;
  color: #64748b;
}

.tab.active .tab-badge {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

/* Loading State */
.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding: 4rem;
  color: #64748b;
}

.spinner-lg {
  width: 48px;
  height: 48px;
  border: 4px solid #e2e8f0;
  border-top-color: #0d9488;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #1e293b;
}

.empty-state p {
  color: #64748b;
  margin-bottom: 1.5rem;
}

/* Bookings Grid */
.bookings-grid {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Booking Card */
.booking-card {
  display: grid;
  grid-template-columns: 280px 1fr;
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
  transition: all 0.3s;
}

.booking-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
}

@media (max-width: 768px) {
  .booking-card {
    grid-template-columns: 1fr;
  }
}

.booking-image {
  position: relative;
  aspect-ratio: 4/3;
  overflow: hidden;
}

.booking-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.status-badge {
  position: absolute;
  top: 1rem;
  left: 1rem;
  padding: 0.375rem 0.875rem;
  border-radius: 1rem;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge.pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.confirmed {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.cancelled {
  background: #fee2e2;
  color: #991b1b;
}

.status-badge.completed {
  background: #dbeafe;
  color: #1e40af;
}

.booking-content {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.booking-header h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e293b;
}

.booking-number {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
}

.booking-details {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  padding: 1rem 0;
  border-top: 1px solid #e2e8f0;
  border-bottom: 1px solid #e2e8f0;
}

@media (max-width: 640px) {
  .booking-details {
    grid-template-columns: repeat(2, 1fr);
  }
}

.detail-item {
  display: flex;
  gap: 0.75rem;
}

.detail-icon {
  font-size: 1.25rem;
}

.detail-label {
  display: block;
  font-size: 0.75rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-value {
  display: block;
  font-weight: 600;
  color: #1e293b;
}

.booking-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 1rem;
}

.booking-price {
  display: flex;
  flex-direction: column;
}

.price-label {
  font-size: 0.8rem;
  color: #64748b;
}

.price-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #0d9488;
}

.payment-method-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 9999px;
  margin-top: 0.5rem;
}

.payment-method-badge.pay-at-checkin {
  background: #fef3c7;
  color: #d97706;
}

.payment-method-badge.paid {
  background: #d1fae5;
  color: #059669;
}

.booking-actions {
  display: flex;
  gap: 0.75rem;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 1.5rem;
  font-size: 0.9rem;
  font-weight: 600;
  border-radius: 0.5rem;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  text-decoration: none;
}

.btn-primary {
  background: linear-gradient(135deg, #0d9488, #0891b2);
  color: white;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(13, 148, 136, 0.4);
}

.btn-outline {
  background: transparent;
  border: 2px solid #e2e8f0;
  color: #475569;
}

.btn-outline:hover {
  border-color: #0d9488;
  color: #0d9488;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

/* Transitions */
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Responsive */
@media (max-width: 640px) {
  .hero-content h1 {
    font-size: 2rem;
  }

  .booking-footer {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .booking-actions {
    width: 100%;
  }

  .booking-actions .btn {
    flex: 1;
  }
}
</style>
