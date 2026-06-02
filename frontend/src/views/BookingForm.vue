<template>
  <div class="booking-form-page">
    <div class="container">
      <div class="booking-header">
        <button class="back-btn" @click="$router.back()">
          ← {{ $t("common.back") }}
        </button>
        <h1>{{ $t("booking.complete_booking") }}</h1>
      </div>

      <div class="booking-layout">
        <!-- Booking Summary -->
        <div class="booking-summary">
          <div v-if="room" class="room-card">
            <div class="room-image">
              <img
                :src="getRoomImage()"
                :alt="room.name"
                @error="handleImageError"
              />
            </div>
            <div class="room-info">
              <h3>{{ room.name }}</h3>
              <p class="room-type">{{ room.type }}</p>

              <!-- Editable Booking Details -->
              <div class="booking-details-edit">
                <h4>{{ $t("booking.booking_details") }}</h4>

                <div class="date-time-row">
                  <div class="form-group half">
                    <label>{{ $t("booking.check_in_date") }}</label>
                    <input
                      type="date"
                      v-model="editableBooking.checkIn"
                      :min="minDate"
                      :lang="inputLang"
                      @change="checkAvailability"
                    />
                  </div>
                  <div class="form-group half">
                    <label>{{ $t("booking.check_in_time") }}</label>
                    <select v-model="editableBooking.checkInTime">
                      <option value="12:00">12:00</option>
                      <option value="13:00">13:00</option>
                      <option value="14:00">14:00</option>
                      <option value="15:00">15:00</option>
                      <option value="16:00">16:00</option>
                      <option value="17:00">17:00</option>
                    </select>
                  </div>
                </div>

                <div class="date-time-row">
                  <div class="form-group half">
                    <label>{{ $t("booking.check_out_date") }}</label>
                    <input
                      type="date"
                      v-model="editableBooking.checkOut"
                      :min="minCheckoutDate"
                      :lang="inputLang"
                      @change="checkAvailability"
                    />
                  </div>
                  <div class="form-group half">
                    <label>{{ $t("booking.check_out_time") }}</label>
                    <select v-model="editableBooking.checkOutTime">
                      <option value="10:00">10:00</option>
                      <option value="11:00">11:00</option>
                      <option value="12:00">12:00</option>
                      <option value="13:00">13:00</option>
                    </select>
                  </div>
                </div>

                <div class="form-group">
                  <label>{{ $t("booking.guests") }}</label>
                  <select v-model="editableBooking.guests">
                    <option v-for="i in room.capacity" :key="i" :value="i">
                      {{ i }}
                      {{
                        i > 1
                          ? $t("booking.guest_plural")
                          : $t("booking.guest_singular")
                      }}
                    </option>
                  </select>
                </div>

                <div
                  v-if="availabilityMessage"
                  class="availability-message"
                  :class="availabilityStatus"
                >
                  {{ availabilityMessage }}
                </div>

                <div class="booking-summary-info">
                  <div class="detail-row">
                    <span>{{ $t("booking.nights") }}:</span>
                    <span
                      >{{ calculatedNights }}
                      {{ $t("booking.night_plural") }}</span
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="price-breakdown">
            <h4>{{ $t("booking.price_breakdown") }}</h4>
            <div class="price-row">
              <span
                >{{ roomPrice.toLocaleString() }} VND x {{ calculatedNights }}
                {{ $t("booking.night_plural") }}</span
              >
              <span>{{ subtotal.toLocaleString() }} VND</span>
            </div>
            <div class="price-row">
              <span>{{ $t("booking.tax_service") }} (10%)</span>
              <span>{{ tax.toLocaleString() }} VND</span>
            </div>
            <div class="price-row total">
              <span>{{ $t("booking.total") }}</span>
              <span>{{ totalPrice.toLocaleString() }} VND</span>
            </div>
          </div>
        </div>

        <!-- Booking Form -->
        <div class="booking-form-container">
          <form @submit.prevent="submitBooking" class="guest-form">
            <div class="form-section">
              <h3>{{ $t("booking.guest_information") }}</h3>

              <div class="form-group">
                <label for="guest_name">{{ $t("booking.full_name") }} *</label>
                <input
                  id="guest_name"
                  v-model="form.guest_name"
                  type="text"
                  required
                  :placeholder="$t('booking.full_name_placeholder')"
                />
              </div>

              <div class="form-group">
                <label for="guest_email">{{ $t("booking.email") }} *</label>
                <input
                  id="guest_email"
                  v-model="form.guest_email"
                  type="email"
                  required
                  :placeholder="$t('booking.email_placeholder')"
                />
              </div>

              <div class="form-group">
                <label for="guest_phone">{{ $t("booking.phone") }} *</label>
                <input
                  id="guest_phone"
                  v-model="form.guest_phone"
                  type="tel"
                  required
                  :placeholder="$t('booking.phone_placeholder')"
                />
              </div>

              <div class="form-group">
                <label for="special_requests">{{
                  $t("booking.special_requests")
                }}</label>
                <textarea
                  id="special_requests"
                  v-model="form.special_requests"
                  rows="3"
                  :placeholder="$t('booking.special_requests_placeholder')"
                ></textarea>
              </div>
            </div>

            <div class="form-section">
              <h3>{{ $t("booking.payment_method") }}</h3>

              <div class="payment-options">
                <label class="payment-option">
                  <input
                    type="radio"
                    v-model="form.payment_method"
                    value="sepay"
                  />
                  <div class="option-content">
                    <span class="option-title">{{
                      $t("booking.online_payment")
                    }}</span>
                    <span class="option-desc">{{
                      $t("booking.sepay_description")
                    }}</span>
                  </div>
                </label>

                <label class="payment-option">
                  <input
                    type="radio"
                    v-model="form.payment_method"
                    value="pay_at_checkin"
                  />
                  <div class="option-content">
                    <span class="option-title">{{
                      $t("booking.pay_at_checkin")
                    }}</span>
                    <span class="option-desc">{{
                      $t("booking.pay_at_checkin_description")
                    }}</span>
                  </div>
                </label>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary" :disabled="loading">
                {{
                  loading
                    ? $t("common.processing")
                    : $t("booking.confirm_booking")
                }}
              </button>
            </div>

            <p v-if="error" class="error-message">{{ error }}</p>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import { useAuthStore } from "@/stores/auth";
import api from "@/services/api";
import guestBookingService from "@/services/guestBooking";
import dayjs from "dayjs";

const route = useRoute();
const router = useRouter();
const { t, locale } = useI18n({ useScope: "global" });
const authStore = useAuthStore();

const room = ref(null);
const loading = ref(false);
const error = ref("");

// Get booking data from query params
const bookingData = computed(() => ({
  roomId: parseInt(route.query.roomId),
  checkIn: route.query.checkIn,
  checkInTime: route.query.checkInTime || "14:00",
  checkOut: route.query.checkOut,
  checkOutTime: route.query.checkOutTime || "12:00",
  guests: parseInt(route.query.guests) || 1,
}));

// Editable booking data
const editableBooking = ref({
  checkIn: "",
  checkInTime: "14:00",
  checkOut: "",
  checkOutTime: "12:00",
  guests: 1,
});

// Availability checking
const availabilityMessage = ref("");
const availabilityStatus = ref(""); // 'success', 'error', 'warning'
const checkingAvailability = ref(false);

// Form data
const form = ref({
  guest_name: "",
  guest_email: "",
  guest_phone: "",
  special_requests: "",
  payment_method: "sepay",
});

// Calculations
const nights = computed(() => {
  if (!bookingData.value.checkIn || !bookingData.value.checkOut) return 0;
  return dayjs(bookingData.value.checkOut).diff(
    dayjs(bookingData.value.checkIn),
    "day"
  );
});

const calculatedNights = computed(() => {
  if (!editableBooking.value.checkIn || !editableBooking.value.checkOut)
    return 0;
  return dayjs(editableBooking.value.checkOut).diff(
    dayjs(editableBooking.value.checkIn),
    "day"
  );
});

const minDate = computed(() => dayjs().format("YYYY-MM-DD"));
const inputLang = computed(() => (locale.value === "vi" ? "vi" : "en"));
const minCheckoutDate = computed(() => {
  if (!editableBooking.value.checkIn)
    return dayjs().add(1, "day").format("YYYY-MM-DD");
  return dayjs(editableBooking.value.checkIn)
    .add(1, "day")
    .format("YYYY-MM-DD");
});

const roomPrice = computed(() => {
  return room.value?.discount_price || room.value?.price_per_night || 0;
});

const subtotal = computed(() => {
  return roomPrice.value * calculatedNights.value;
});

const tax = computed(() => {
  return subtotal.value * 0.1;
});

const totalPrice = computed(() => {
  return subtotal.value + tax.value;
});

// Image handling
const getRoomImage = () => {
  if (room.value?.featured_image) {
    return room.value.featured_image.startsWith("http")
      ? room.value.featured_image
      : `${import.meta.env.VITE_API_URL}/${room.value.featured_image}`;
  }
  return "https://placehold.co/300x200/e2e8f0/64748b?text=No+Image";
};

const handleImageError = (event) => {
  event.target.src = "https://placehold.co/300x200/e2e8f0/64748b?text=No+Image";
};

// Availability checking
const checkAvailability = async () => {
  if (
    !editableBooking.value.checkIn ||
    !editableBooking.value.checkOut ||
    !room.value
  ) {
    availabilityMessage.value = "";
    return;
  }

  if (calculatedNights.value <= 0) {
    availabilityMessage.value = t("booking.invalid_dates");
    availabilityStatus.value = "error";
    return;
  }

  checkingAvailability.value = true;
  try {
    const response = await api.post("/rooms/check-availability", {
      room_id: room.value.id,
      check_in: editableBooking.value.checkIn,
      check_out: editableBooking.value.checkOut,
    });

    if (response.data.available) {
      availabilityMessage.value = t("booking.available");
      availabilityStatus.value = "success";
    } else {
      availabilityMessage.value = t("booking.not_available");
      availabilityStatus.value = "error";
    }
  } catch (err) {
    availabilityMessage.value = t("booking.availability_check_failed");
    availabilityStatus.value = "warning";
  } finally {
    checkingAvailability.value = false;
  }
};

// Pre-fill form if user is authenticated
onMounted(async () => {
  // Initialize editable booking data from query params
  editableBooking.value = {
    checkIn: bookingData.value.checkIn,
    checkInTime: bookingData.value.checkInTime,
    checkOut: bookingData.value.checkOut,
    checkOutTime: bookingData.value.checkOutTime,
    guests: bookingData.value.guests,
  };

  // Load room data
  try {
    const response = await api.get(`/rooms/${bookingData.value.roomId}`);
    room.value = response.data;

    // Check initial availability
    await checkAvailability();
  } catch (err) {
    error.value = "Failed to load room information";
    return;
  }

  // Pre-fill user data if authenticated
  if (authStore.isAuthenticated && authStore.user) {
    form.value.guest_name = authStore.user.name || "";
    form.value.guest_email = authStore.user.email || "";
  }
});

const formatDate = (date) => {
  return dayjs(date).format("DD/MM/YYYY");
};

const submitBooking = async () => {
  // Check availability first
  if (availabilityStatus.value === "error") {
    error.value = t("booking.please_select_available_dates");
    return;
  }

  loading.value = true;
  error.value = "";

  try {
    const bookingPayload = {
      room_id: bookingData.value.roomId,
      check_in: editableBooking.value.checkIn,
      check_in_time: editableBooking.value.checkInTime,
      check_out: editableBooking.value.checkOut,
      check_out_time: editableBooking.value.checkOutTime,
      guests: editableBooking.value.guests,
      guest_name: form.value.guest_name,
      guest_email: form.value.guest_email,
      guest_phone: form.value.guest_phone,
      special_requests: form.value.special_requests,
      payment_method: form.value.payment_method,
    };

    const response = await guestBookingService.createBooking(bookingPayload);
    const booking = response.data.booking;

    if (
      form.value.payment_method === "sepay" &&
      response.data.payment?.qr_code_url
    ) {
      // Navigate to QR Payment page with payment data
      router.push({
        name: "QRPayment",
        state: {
          payment: response.data.payment,
          booking: booking,
        },
      });
    } else {
      // Redirect to booking confirmation
      router.push({
        name: "BookingConfirmation",
        params: { bookingNumber: booking.booking_number },
        query: { email: form.value.guest_email },
      });
    }
  } catch (err) {
    console.error("Booking error:", err);
    error.value = err.response?.data?.message || "Failed to create booking";
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.booking-form-page {
  min-height: 100vh;
  background: #f8fafc;
  padding: 2rem 0;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

.booking-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.back-btn {
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s;
}

.back-btn:hover {
  background: #f1f5f9;
}

.booking-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  align-items: start;
}

.booking-summary {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.room-card {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.room-card img {
  width: 100px;
  height: 80px;
  object-fit: cover;
  border-radius: 0.5rem;
}

.room-info h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.room-type {
  color: #64748b;
  margin: 0 0 1rem 0;
  font-size: 0.9rem;
}

.booking-details-edit {
  background: #f8fafc;
  padding: 1rem;
  border-radius: 0.5rem;
  border: 1px solid #e2e8f0;
}

.booking-details-edit h4 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
}

.date-time-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.form-group.half {
  margin-bottom: 0;
}

.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.9rem;
  background: white;
  cursor: pointer;
}

.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.availability-message {
  padding: 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.9rem;
  font-weight: 500;
  margin-bottom: 1rem;
}

.availability-message.success {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #a7f3d0;
}

.availability-message.error {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fca5a5;
}

.availability-message.warning {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fcd34d;
}

.booking-summary-info {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.price-breakdown h4 {
  margin: 0 0 1rem 0;
  font-size: 1rem;
  font-weight: 600;
}

.price-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.price-row.total {
  border-top: 1px solid #e2e8f0;
  padding-top: 0.5rem;
  margin-top: 1rem;
  font-weight: 600;
  font-size: 1rem;
}

.booking-form-container {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.form-section {
  margin-bottom: 2rem;
}

.form-section h3 {
  margin: 0 0 1rem 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  font-size: 0.9rem;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.9rem;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.payment-options {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.payment-option {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
}

.payment-option:hover {
  border-color: #3b82f6;
}

.payment-option input[type="radio"] {
  width: auto;
  margin: 0;
}

.option-content {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.option-title {
  font-weight: 500;
  font-size: 0.9rem;
}

.option-desc {
  color: #64748b;
  font-size: 0.8rem;
}

.form-actions {
  margin-top: 2rem;
}

.btn-primary {
  width: 100%;
  padding: 0.875rem 1.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 500;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary:hover:not(:disabled) {
  background: #2563eb;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-message {
  color: #dc2626;
  margin-top: 1rem;
  padding: 0.75rem;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 0.5rem;
  font-size: 0.9rem;
}

@media (max-width: 768px) {
  .booking-layout {
    grid-template-columns: 1fr;
  }

  .room-card {
    flex-direction: column;
  }

  .room-card img {
    width: 100%;
    height: 150px;
  }
}

@media (max-width: 480px) {
  .booking-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .booking-summary,
  .booking-form-container {
    padding: 1.25rem;
  }

  .date-time-row {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }

  .price-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.35rem;
  }
}
</style>
