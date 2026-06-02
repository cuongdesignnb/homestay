<template>
  <div class="booking-confirmation-page">
    <div class="container">
      <div v-if="loading" class="loading">
        <div class="spinner"></div>
        <p>{{ $t("booking.loading_confirmation") }}</p>
      </div>

      <div v-else-if="booking" class="confirmation-content">
        <!-- Success Header -->
        <div class="success-header">
          <div class="success-icon">✅</div>
          <h1>{{ $t("booking.confirmation_title") }}</h1>
          <p class="confirmation-message">
            {{ $t("booking.confirmation_message") }}
          </p>
        </div>

        <!-- Booking Details -->
        <div class="booking-details-card">
          <div class="booking-header">
            <h2>{{ $t("booking.booking_details") }}</h2>
            <div class="booking-number">
              <span class="label">{{ $t("booking.booking_number") }}:</span>
              <span class="value">{{ booking.booking_number }}</span>
            </div>
          </div>

          <div class="booking-content">
            <!-- Room Information -->
            <div class="detail-section">
              <h3>{{ $t("booking.room_information") }}</h3>
              <div class="room-summary">
                <img
                  :src="
                    booking.room?.featured_image ||
                    'https://placehold.co/150x100/e2e8f0/64748b?text=Room'
                  "
                  :alt="booking.room?.name"
                  class="room-image"
                />
                <div class="room-info">
                  <h4>{{ booking.room?.name }}</h4>
                  <p class="room-type">{{ booking.room?.type }}</p>
                </div>
              </div>
            </div>

            <!-- Stay Details -->
            <div class="detail-section">
              <h3>{{ $t("booking.stay_details") }}</h3>
              <div class="stay-grid">
                <div class="stay-item">
                  <span class="label">{{ $t("booking.check_in") }}:</span>
                  <span class="value">
                    {{ formatDate(booking.check_in) }}
                    {{ booking.check_in_time }}
                  </span>
                </div>
                <div class="stay-item">
                  <span class="label">{{ $t("booking.check_out") }}:</span>
                  <span class="value">
                    {{ formatDate(booking.check_out) }}
                    {{ booking.check_out_time }}
                  </span>
                </div>
                <div class="stay-item">
                  <span class="label">{{ $t("booking.guests") }}:</span>
                  <span class="value"
                    >{{ booking.guests }} {{ $t("booking.guest_plural") }}</span
                  >
                </div>
                <div class="stay-item">
                  <span class="label">{{ $t("booking.nights") }}:</span>
                  <span class="value"
                    >{{ booking.total_nights }}
                    {{ $t("booking.night_plural") }}</span
                  >
                </div>
              </div>
            </div>

            <!-- Guest Information -->
            <div class="detail-section">
              <h3>{{ $t("booking.guest_information") }}</h3>
              <div class="guest-grid">
                <div class="guest-item">
                  <span class="label">{{ $t("booking.full_name") }}:</span>
                  <span class="value">{{ booking.guest_name }}</span>
                </div>
                <div class="guest-item">
                  <span class="label">{{ $t("booking.email") }}:</span>
                  <span class="value">{{ booking.guest_email }}</span>
                </div>
                <div class="guest-item">
                  <span class="label">{{ $t("booking.phone") }}:</span>
                  <span class="value">{{ booking.guest_phone }}</span>
                </div>
              </div>
              <div v-if="booking.special_requests" class="special-requests">
                <span class="label">{{ $t("booking.special_requests") }}:</span>
                <p class="value">{{ booking.special_requests }}</p>
              </div>
            </div>

            <!-- Payment Information -->
            <div class="detail-section">
              <h3>{{ $t("booking.payment_information") }}</h3>
              <div class="payment-summary">
                <div class="payment-row">
                  <span
                    >{{ booking.room_price?.toLocaleString() }} VND x
                    {{ booking.total_nights }}
                    {{ $t("booking.night_plural") }}</span
                  >
                  <span>{{ booking.total_amount?.toLocaleString() }} VND</span>
                </div>
                <div class="payment-row">
                  <span>{{ $t("booking.tax_service") }} (10%)</span>
                  <span>{{ booking.tax_amount?.toLocaleString() }} VND</span>
                </div>
                <div class="payment-row total">
                  <span>{{ $t("booking.total") }}</span>
                  <span>{{ booking.final_amount?.toLocaleString() }} VND</span>
                </div>
                <div class="payment-status">
                  <span class="label">{{ $t("booking.payment_method") }}:</span>
                  <span class="value">
                    <span
                      v-if="booking.payment_method === 'sepay'"
                      class="method-tag sepay"
                    >
                      {{ $t("booking.online_payment") }}
                    </span>
                    <span v-else class="method-tag checkin">
                      {{ $t("booking.pay_at_checkin") }}
                    </span>
                  </span>
                </div>
                <div class="payment-status">
                  <span class="label">{{ $t("booking.status") }}:</span>
                  <span class="value">
                    <span class="status-tag" :class="booking.status">
                      {{ $t(`booking.status_${booking.status}`) }}
                    </span>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="action-buttons">
          <button @click="goHome" class="btn-secondary">
            {{ $t("booking.back_to_home") }}
          </button>
          <button @click="openBookingLookup" class="btn-primary">
            {{ $t("booking.manage_booking") }}
          </button>
        </div>

        <!-- Important Notes -->
        <div class="important-notes">
          <h3>{{ $t("booking.important_notes") }}</h3>
          <ul>
            <li>{{ $t("booking.note_save_booking_number") }}</li>
            <li>{{ $t("booking.note_confirmation_email") }}</li>
            <li v-if="booking.payment_method === 'pay_at_checkin'">
              {{ $t("booking.note_payment_checkin") }}
            </li>
            <li>{{ $t("booking.note_cancellation_policy") }}</li>
          </ul>
        </div>
      </div>

      <div v-else class="error-state">
        <div class="error-icon">❌</div>
        <h2>{{ $t("booking.not_found") }}</h2>
        <p>{{ error || $t("booking.not_found_message") }}</p>
        <button @click="goHome" class="btn-primary">
          {{ $t("booking.back_to_home") }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import guestBookingService from "@/services/guestBooking";
import dayjs from "dayjs";

const route = useRoute();
const router = useRouter();
const { t } = useI18n();

const booking = ref(null);
const loading = ref(true);
const error = ref("");

onMounted(async () => {
  const bookingNumber = route.params.bookingNumber;
  const email = route.query.email;

  if (!bookingNumber || !email) {
    error.value = "Missing booking information";
    loading.value = false;
    return;
  }

  try {
    const response = await guestBookingService.lookupBooking(
      bookingNumber,
      email
    );
    booking.value = response.data;
  } catch (err) {
    error.value = err.response?.data?.message || "Failed to load booking";
  } finally {
    loading.value = false;
  }
});

const formatDate = (date) => {
  return dayjs(date).format("DD/MM/YYYY");
};

const goHome = () => {
  router.push("/");
};

const openBookingLookup = () => {
  router.push("/booking-lookup");
};
</script>

<style scoped>
.booking-confirmation-page {
  min-height: 100vh;
  background: #f8fafc;
  padding: 2rem 0;
}

.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 1rem;
}

.loading {
  text-align: center;
  padding: 4rem 2rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.success-header {
  text-align: center;
  margin-bottom: 2rem;
}

.success-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.success-header h1 {
  margin: 0 0 1rem 0;
  color: #059669;
  font-size: 2rem;
  font-weight: 700;
}

.confirmation-message {
  font-size: 1.125rem;
  color: #64748b;
  margin: 0;
}

.booking-details-card {
  background: white;
  border-radius: 1rem;
  padding: 2rem;
  margin-bottom: 2rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e2e8f0;
}

.booking-header h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.booking-number .label {
  color: #64748b;
  font-size: 0.9rem;
}

.booking-number .value {
  font-weight: 600;
  font-family: monospace;
  font-size: 1.1rem;
  color: #3b82f6;
}

.detail-section {
  margin-bottom: 2rem;
}

.detail-section h3 {
  margin: 0 0 1rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
}

.room-summary {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.room-image {
  width: 100px;
  height: 75px;
  object-fit: cover;
  border-radius: 0.5rem;
}

.room-info h4 {
  margin: 0 0 0.25rem 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.room-type {
  color: #64748b;
  margin: 0;
  font-size: 0.9rem;
}

.stay-grid,
.guest-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.stay-item,
.guest-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.stay-item .label,
.guest-item .label {
  color: #64748b;
  font-size: 0.875rem;
  font-weight: 500;
}

.stay-item .value,
.guest-item .value {
  font-weight: 600;
}

.special-requests {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.special-requests .label {
  color: #64748b;
  font-size: 0.875rem;
  font-weight: 500;
}

.special-requests .value {
  margin: 0.5rem 0 0 0;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 0.5rem;
  font-style: italic;
}

.payment-summary {
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 0.75rem;
}

.payment-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.payment-row.total {
  border-top: 1px solid #d1d5db;
  padding-top: 0.75rem;
  margin-top: 0.75rem;
  font-weight: 600;
  font-size: 1rem;
}

.payment-status {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #e2e8f0;
}

.method-tag {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.8rem;
  font-weight: 500;
}

.method-tag.sepay {
  background: #dbeafe;
  color: #1d4ed8;
}

.method-tag.checkin {
  background: #fef3c7;
  color: #92400e;
}

.status-tag {
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.8rem;
  font-weight: 500;
}

.status-tag.pending {
  background: #fef3c7;
  color: #92400e;
}

.status-tag.confirmed {
  background: #d1fae5;
  color: #065f46;
}

.status-tag.cancelled {
  background: #fee2e2;
  color: #991b1b;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  justify-content: center;
}

.btn-primary,
.btn-secondary {
  padding: 0.75rem 2rem;
  border-radius: 0.5rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: #3b82f6;
  color: white;
  border: none;
}

.btn-primary:hover {
  background: #2563eb;
}

.btn-secondary {
  background: white;
  color: #374151;
  border: 1px solid #d1d5db;
}

.btn-secondary:hover {
  background: #f9fafb;
}

.important-notes {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.important-notes h3 {
  margin: 0 0 1rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: #374151;
}

.important-notes ul {
  margin: 0;
  padding-left: 1.25rem;
}

.important-notes li {
  margin-bottom: 0.5rem;
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.5;
}

.error-state {
  text-align: center;
  padding: 4rem 2rem;
}

.error-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.error-state h2 {
  margin: 0 0 1rem 0;
  color: #dc2626;
  font-size: 1.5rem;
}

.error-state p {
  color: #64748b;
  margin-bottom: 2rem;
}

@media (max-width: 768px) {
  .booking-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .room-summary {
    flex-direction: column;
    text-align: center;
  }

  .room-image {
    width: 150px;
    height: 100px;
    align-self: center;
  }

  .action-buttons {
    flex-direction: column;
  }
}
</style>
