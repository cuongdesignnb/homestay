<template>
  <div class="booking-lookup-page">
    <div class="container">
      <h1>{{ $t("booking.lookup_title") }}</h1>
      <p class="subtitle">{{ $t("booking.lookup_subtitle") }}</p>

      <div class="lookup-form">
        <div class="form-group">
          <label for="bookingNumber">{{ $t("booking.booking_number") }}</label>
          <input
            type="text"
            id="bookingNumber"
            v-model="bookingNumber"
            :placeholder="$t('booking.booking_number_placeholder')"
            @keyup.enter="lookupBooking"
          />
        </div>

        <div class="form-group">
          <label for="email">{{ $t("booking.email") }}</label>
          <input
            type="email"
            id="email"
            v-model="email"
            :placeholder="$t('booking.email_placeholder')"
            @keyup.enter="lookupBooking"
          />
        </div>

        <button @click="lookupBooking" :disabled="loading" class="btn-primary">
          <span v-if="loading">{{ $t("common.loading") }}</span>
          <span v-else>{{ $t("booking.lookup_button") }}</span>
        </button>

        <p v-if="error" class="error-message">{{ error }}</p>
      </div>

      <!-- Booking Result -->
      <div v-if="booking" class="booking-result">
        <div class="booking-header">
          <h2>{{ $t("booking.booking_details") }}</h2>
          <span :class="['status-badge', booking.status]">
            {{ $t(`booking.status_${booking.status}`) }}
          </span>
        </div>

        <div class="booking-info">
          <div class="info-row">
            <span class="label">{{ $t("booking.booking_number") }}:</span>
            <span class="value">{{ booking.booking_number }}</span>
          </div>

          <div class="info-row" v-if="booking.room">
            <span class="label">{{ $t("booking.room") }}:</span>
            <span class="value">{{ booking.room.name }}</span>
          </div>

          <div class="info-row">
            <span class="label">{{ $t("booking.check_in") }}:</span>
            <span class="value">{{ formatDate(booking.check_in) }}</span>
          </div>

          <div class="info-row">
            <span class="label">{{ $t("booking.check_out") }}:</span>
            <span class="value">{{ formatDate(booking.check_out) }}</span>
          </div>

          <div class="info-row">
            <span class="label">{{ $t("booking.guests") }}:</span>
            <span class="value">{{ booking.guests }}</span>
          </div>

          <div class="info-row">
            <span class="label">{{ $t("booking.total_amount") }}:</span>
            <span class="value price">{{
              formatPrice(booking.final_amount)
            }}</span>
          </div>

          <div class="info-row">
            <span class="label">{{ $t("booking.payment_status") }}:</span>
            <span :class="['payment-badge', booking.payment_status]">
              {{ $t(`booking.payment_status_${booking.payment_status}`) }}
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="booking-actions" v-if="booking.status === 'pending'">
          <button
            @click="cancelBooking"
            :disabled="cancelling"
            class="btn-danger"
          >
            {{
              cancelling ? $t("common.loading") : $t("booking.cancel_booking")
            }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useI18n } from "vue-i18n";
import dayjs from "dayjs";
import api from "@/services/api";

const { t } = useI18n();

const bookingNumber = ref("");
const email = ref("");
const loading = ref(false);
const error = ref("");
const booking = ref(null);
const cancelling = ref(false);

const formatDate = (date) => dayjs(date).format("DD/MM/YYYY");
const formatPrice = (price) =>
  new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(
    price
  );

const lookupBooking = async () => {
  if (!bookingNumber.value || !email.value) {
    error.value = t("bookingLookup.fillAllFields");
    return;
  }

  loading.value = true;
  error.value = "";
  booking.value = null;

  try {
    const response = await api.get(`/bookings/lookup/${bookingNumber.value}`, {
      params: { guest_email: email.value },
    });
    booking.value = response.data.data || response.data;
  } catch (err) {
    error.value = err.response?.data?.message || t("booking.lookup_error");
  } finally {
    loading.value = false;
  }
};

const cancelBooking = async () => {
  if (!confirm(t("booking.cancel_confirm"))) return;

  cancelling.value = true;
  try {
    await api.put(`/bookings/cancel/${bookingNumber.value}`, {
      email: email.value,
    });
    // Refresh booking info
    await lookupBooking();
  } catch (err) {
    error.value = err.response?.data?.message || t("booking.cancel_error");
  } finally {
    cancelling.value = false;
  }
};
</script>

<style scoped>
.booking-lookup-page {
  padding: 40px 20px;
  min-height: 60vh;
}

.container {
  max-width: 600px;
  margin: 0 auto;
}

h1 {
  text-align: center;
  margin-bottom: 10px;
  color: #2c3e50;
}

.subtitle {
  text-align: center;
  color: #666;
  margin-bottom: 30px;
}

.lookup-form {
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #333;
}

.form-group input {
  width: 100%;
  padding: 12px 16px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 16px;
  transition: border-color 0.3s;
}

.form-group input:focus {
  outline: none;
  border-color: #3498db;
}

.btn-primary {
  width: 100%;
  padding: 14px;
  background: #3498db;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-primary:hover:not(:disabled) {
  background: #2980b9;
}

.btn-primary:disabled {
  background: #bdc3c7;
  cursor: not-allowed;
}

.error-message {
  color: #e74c3c;
  text-align: center;
  margin-top: 15px;
}

.booking-result {
  margin-top: 30px;
  background: #fff;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
}

.booking-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 1px solid #eee;
}

.booking-header h2 {
  margin: 0;
  color: #2c3e50;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 14px;
  font-weight: 500;
}

.status-badge.pending {
  background: #fff3cd;
  color: #856404;
}

.status-badge.confirmed {
  background: #d4edda;
  color: #155724;
}

.status-badge.cancelled {
  background: #f8d7da;
  color: #721c24;
}

.booking-info {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #f5f5f5;
}

.info-row .label {
  color: #666;
}

.info-row .value {
  font-weight: 500;
  color: #333;
}

.info-row .value.price {
  color: #e74c3c;
  font-size: 18px;
}

.payment-badge {
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 13px;
}

.payment-badge.unpaid {
  background: #fff3cd;
  color: #856404;
}

.payment-badge.paid {
  background: #d4edda;
  color: #155724;
}

.booking-actions {
  margin-top: 25px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.btn-danger {
  padding: 12px 24px;
  background: #e74c3c;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-danger:hover:not(:disabled) {
  background: #c0392b;
}

.btn-danger:disabled {
  background: #bdc3c7;
  cursor: not-allowed;
}
</style>
