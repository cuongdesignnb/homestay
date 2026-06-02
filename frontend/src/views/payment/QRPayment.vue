<template>
  <div class="payment-qr-page">
    <div class="container">
      <!-- Header -->
      <div class="payment-header">
        <div class="header-icon">
          <span class="material-icons-outlined">qr_code_2</span>
        </div>
        <h1>{{ $t("payment.scan_to_pay") }}</h1>
        <p class="subtitle">{{ $t("payment.scan_qr_description") }}</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>{{ $t("common.loading") }}</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <span class="material-icons-outlined error-icon">error_outline</span>
        <p>{{ error }}</p>
        <button @click="$router.push('/')" class="btn-primary">
          {{ $t("common.back") }}
        </button>
      </div>

      <!-- Payment Content -->
      <div v-else-if="paymentInfo" class="payment-content">
        <!-- QR Code Section -->
        <div class="qr-section">
          <div class="qr-card">
            <div class="qr-image-wrapper">
              <img
                :src="paymentInfo.qr_code_url"
                :alt="$t('payment.qr_code')"
                class="qr-image"
                @error="handleQRError"
              />
            </div>

            <div class="bank-info">
              <div class="bank-logo">
                <img :src="getBankLogo()" :alt="paymentInfo.bank_name" />
              </div>
              <div class="bank-details">
                <p class="bank-name">{{ paymentInfo.bank_name }}</p>
                <p class="account-name">{{ paymentInfo.account_name }}</p>
              </div>
            </div>
          </div>

          <!-- Timer -->
          <div class="timer-section" v-if="timeLeft > 0">
            <span class="material-icons-outlined">schedule</span>
            <span
              >{{ $t("payment.expires_in") }}:
              <strong>{{ formatTime(timeLeft) }}</strong></span
            >
          </div>
          <div class="timer-section expired" v-else>
            <span class="material-icons-outlined">timer_off</span>
            <span>{{ $t("payment.payment_expired") }}</span>
          </div>
        </div>

        <!-- Payment Details -->
        <div class="details-section">
          <h3>{{ $t("payment.transfer_info") }}</h3>

          <div class="detail-item">
            <label>{{ $t("payment.bank_name") }}</label>
            <div class="detail-value">
              <span>{{ paymentInfo.bank_name }}</span>
            </div>
          </div>

          <div class="detail-item">
            <label>{{ $t("payment.account_number") }}</label>
            <div
              class="detail-value copyable"
              @click="copyToClipboard(paymentInfo.account_number)"
            >
              <span>{{ paymentInfo.account_number }}</span>
              <span class="material-icons-outlined copy-icon"
                >content_copy</span
              >
            </div>
          </div>

          <div class="detail-item">
            <label>{{ $t("payment.account_name") }}</label>
            <div class="detail-value">
              <span>{{ paymentInfo.account_name }}</span>
            </div>
          </div>

          <div class="detail-item highlight">
            <label>{{ $t("payment.amount") }}</label>
            <div
              class="detail-value copyable"
              @click="copyToClipboard(paymentInfo.amount.toString())"
            >
              <span class="amount">{{ paymentInfo.amount_formatted }}</span>
              <span class="material-icons-outlined copy-icon"
                >content_copy</span
              >
            </div>
          </div>

          <div class="detail-item highlight">
            <label>{{ $t("payment.transfer_content") }}</label>
            <div
              class="detail-value copyable"
              @click="copyToClipboard(paymentInfo.transfer_content)"
            >
              <span class="transfer-content">{{
                paymentInfo.transfer_content
              }}</span>
              <span class="material-icons-outlined copy-icon"
                >content_copy</span
              >
            </div>
          </div>

          <div class="warning-box">
            <span class="material-icons-outlined">warning</span>
            <p>{{ $t("payment.transfer_content_warning") }}</p>
          </div>
        </div>

        <!-- Booking Summary -->
        <div class="booking-summary">
          <h3>{{ $t("payment.booking_info") }}</h3>
          <div class="summary-item">
            <span>{{ $t("booking.booking_number") }}:</span>
            <strong>{{ paymentInfo.booking_number }}</strong>
          </div>
          <div class="summary-item" v-if="booking">
            <span>{{ $t("booking.room") }}:</span>
            <strong>{{ booking.room?.name }}</strong>
          </div>
          <div class="summary-item" v-if="booking">
            <span>{{ $t("booking.check_in") }}:</span>
            <strong>{{ formatDate(booking.check_in) }}</strong>
          </div>
          <div class="summary-item" v-if="booking">
            <span>{{ $t("booking.check_out") }}:</span>
            <strong>{{ formatDate(booking.check_out) }}</strong>
          </div>
        </div>

        <!-- Actions -->
        <div class="actions">
          <button
            @click="checkPaymentStatus"
            :disabled="checking"
            class="btn-check"
          >
            <span class="material-icons-outlined">refresh</span>
            {{ checking ? $t("payment.checking") : $t("payment.check_status") }}
          </button>

          <button @click="goToBookingConfirmation" class="btn-secondary">
            {{ $t("payment.paid_already") }}
          </button>
        </div>

        <!-- Status Message -->
        <div v-if="statusMessage" :class="['status-message', statusType]">
          <span class="material-icons-outlined">{{
            statusType === "success" ? "check_circle" : "info"
          }}</span>
          {{ statusMessage }}
        </div>
      </div>

      <!-- Copy Toast -->
      <div v-if="showCopyToast" class="copy-toast">
        <span class="material-icons-outlined">check</span>
        {{ $t("payment.copied") }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import dayjs from "dayjs";

const route = useRoute();
const router = useRouter();
const { t } = useI18n();

const loading = ref(true);
const error = ref("");
const paymentInfo = ref(null);
const booking = ref(null);
const checking = ref(false);
const statusMessage = ref("");
const statusType = ref("");
const showCopyToast = ref(false);
const timeLeft = ref(0);

let timerInterval = null;
let statusCheckInterval = null;

// Bank logos mapping
const bankLogos = {
  MB: "https://img.vietqr.io/image/MB-logo.png",
  VCB: "https://img.vietqr.io/image/VCB-logo.png",
  TCB: "https://img.vietqr.io/image/TCB-logo.png",
  BIDV: "https://img.vietqr.io/image/BIDV-logo.png",
  VPB: "https://img.vietqr.io/image/VPB-logo.png",
  ACB: "https://img.vietqr.io/image/ACB-logo.png",
};

const getBankLogo = () => {
  return (
    bankLogos[paymentInfo.value?.bank_code] ||
    "https://img.vietqr.io/image/MB-logo.png"
  );
};

const formatDate = (date) => {
  return dayjs(date).format("DD/MM/YYYY");
};

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, "0")}`;
};

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text);
    showCopyToast.value = true;
    setTimeout(() => {
      showCopyToast.value = false;
    }, 2000);
  } catch (err) {
    console.error("Failed to copy:", err);
  }
};

const handleQRError = (event) => {
  event.target.src = "https://placehold.co/300x300/e2e8f0/64748b?text=QR+Code";
};

const checkPaymentStatus = async () => {
  // Check if payment ID exists
  if (!paymentInfo.value?.id) {
    statusMessage.value = t("payment.no_payment_info");
    statusType.value = "error";
    return;
  }

  checking.value = true;
  statusMessage.value = "";

  try {
    const response = await api.get(`/payments/${paymentInfo.value.id}/status`);

    if (response.data.status === "completed") {
      statusType.value = "success";
      statusMessage.value = t("payment.payment_confirmed");

      // Redirect to confirmation page after 2 seconds
      setTimeout(() => {
        goToBookingConfirmation();
      }, 2000);
    } else {
      statusType.value = "info";
      statusMessage.value = t("payment.payment_pending");
    }
  } catch (err) {
    console.error("Payment status check error:", err);
    statusMessage.value = t("payment.check_failed");
    statusType.value = "error";
  } finally {
    checking.value = false;
  }
};

const goToBookingConfirmation = () => {
  router.push({
    name: "BookingConfirmation",
    params: { bookingNumber: paymentInfo.value.booking_number },
    query: { email: booking.value?.guest_email },
  });
};

const startTimer = () => {
  if (paymentInfo.value?.expires_at) {
    const expiresAt = dayjs(paymentInfo.value.expires_at);

    timerInterval = setInterval(() => {
      const now = dayjs();
      const diff = expiresAt.diff(now, "second");
      timeLeft.value = Math.max(0, diff);

      if (timeLeft.value <= 0) {
        clearInterval(timerInterval);
      }
    }, 1000);
  }
};

const startStatusCheck = () => {
  // Auto check payment status every 10 seconds
  statusCheckInterval = setInterval(async () => {
    // Skip if no payment ID or already checking
    if (!paymentInfo.value?.id || checking.value) {
      return;
    }

    try {
      const response = await api.get(
        `/payments/${paymentInfo.value.id}/status`
      );
      if (response.data.status === "completed") {
        clearInterval(statusCheckInterval);
        statusType.value = "success";
        statusMessage.value = t("payment.payment_confirmed");
        setTimeout(() => {
          goToBookingConfirmation();
        }, 2000);
      }
    } catch (err) {
      // Silent fail - don't log to avoid spam
      console.debug("Payment status check failed:", err.message);
    }
  }, 10000);
};

onMounted(async () => {
  // Get payment info from route state or query
  const state = history.state;

  if (state?.payment && state?.booking) {
    paymentInfo.value = state.payment;
    booking.value = state.booking;
    loading.value = false;
    startTimer();
    startStatusCheck();
  } else if (route.query.booking_number) {
    // Try to fetch from API
    try {
      const response = await api.get(
        `/bookings/lookup/${route.query.booking_number}`,
        {
          params: { guest_email: route.query.email },
        }
      );
      booking.value = response.data;

      // Get payment info
      if (response.data.payment) {
        // Reconstruct payment info
        const payment = response.data.payment;
        paymentInfo.value = {
          id: payment.id,
          booking_number: response.data.booking_number,
          amount: payment.amount,
          amount_formatted:
            new Intl.NumberFormat("vi-VN").format(payment.amount) + " VND",
          // These would need to come from config/API
          qr_code_url: payment.qr_code_url,
          bank_name: payment.bank_name,
          bank_code: payment.bank_code,
          account_number: payment.account_number,
          account_name: payment.account_name,
          transfer_content: payment.transfer_content,
        };
        startTimer();
        startStatusCheck();
      }
      loading.value = false;
    } catch (err) {
      error.value = t("payment.booking_not_found");
      loading.value = false;
    }
  } else {
    error.value = t("payment.no_payment_info");
    loading.value = false;
  }
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
  if (statusCheckInterval) clearInterval(statusCheckInterval);
});
</script>

<style scoped>
.payment-qr-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 1rem;
}

.container {
  max-width: 500px;
  margin: 0 auto;
}

.payment-header {
  text-align: center;
  color: white;
  margin-bottom: 2rem;
}

.header-icon {
  width: 80px;
  height: 80px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1rem;
}

.header-icon .material-icons-outlined {
  font-size: 40px;
}

.payment-header h1 {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.subtitle {
  opacity: 0.9;
  font-size: 0.95rem;
}

.loading-state,
.error-state {
  background: white;
  border-radius: 1rem;
  padding: 3rem;
  text-align: center;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.error-icon {
  font-size: 48px;
  color: #ef4444;
  margin-bottom: 1rem;
}

.payment-content {
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.qr-section {
  background: #f8fafc;
  padding: 2rem;
  text-align: center;
}

.qr-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.qr-image-wrapper {
  margin-bottom: 1rem;
}

.qr-image {
  width: 100%;
  max-width: 280px;
  height: auto;
  border-radius: 0.5rem;
}

.bank-info {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.bank-logo img {
  height: 32px;
  width: auto;
}

.bank-details {
  text-align: left;
}

.bank-name {
  font-weight: 600;
  color: #1e293b;
  margin: 0;
}

.account-name {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0;
}

.timer-section {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 1rem;
  padding: 0.75rem;
  background: #fef3c7;
  border-radius: 0.5rem;
  color: #92400e;
  font-size: 0.9rem;
}

.timer-section.expired {
  background: #fee2e2;
  color: #991b1b;
}

.timer-section .material-icons-outlined {
  font-size: 20px;
}

.details-section {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.details-section h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.detail-item:last-of-type {
  border-bottom: none;
}

.detail-item label {
  font-size: 0.875rem;
  color: #64748b;
}

.detail-value {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  color: #1e293b;
}

.detail-value.copyable {
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
  transition: background 0.2s;
}

.detail-value.copyable:hover {
  background: #f1f5f9;
}

.copy-icon {
  font-size: 18px;
  color: #94a3b8;
}

.detail-item.highlight {
  background: #f0fdf4;
  margin: 0.5rem -1.5rem;
  padding: 1rem 1.5rem;
  border: none;
}

.detail-item.highlight .detail-value {
  color: #16a34a;
}

.amount {
  font-size: 1.25rem;
  font-weight: 700;
}

.transfer-content {
  font-family: monospace;
  font-size: 1.1rem;
  letter-spacing: 0.05em;
}

.warning-box {
  display: flex;
  gap: 0.75rem;
  padding: 1rem;
  background: #fef3c7;
  border-radius: 0.5rem;
  margin-top: 1rem;
}

.warning-box .material-icons-outlined {
  color: #d97706;
  flex-shrink: 0;
}

.warning-box p {
  font-size: 0.875rem;
  color: #92400e;
  margin: 0;
  line-height: 1.5;
}

.booking-summary {
  padding: 1.5rem;
  background: #f8fafc;
}

.booking-summary h3 {
  font-size: 1rem;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 1rem;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  font-size: 0.9rem;
}

.summary-item span {
  color: #64748b;
}

.summary-item strong {
  color: #1e293b;
}

.actions {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.btn-check {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.btn-check:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-check:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.btn-secondary {
  width: 100%;
  padding: 1rem;
  background: transparent;
  color: #64748b;
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background: #f8fafc;
  color: #1e293b;
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
}

.status-message {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem;
  margin: 0 1.5rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
}

.status-message.success {
  background: #dcfce7;
  color: #16a34a;
}

.status-message.info {
  background: #e0f2fe;
  color: #0284c7;
}

.status-message.error {
  background: #fee2e2;
  color: #dc2626;
}

.copy-toast {
  position: fixed;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  background: #1e293b;
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 2rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateX(-50%) translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
}

@media (max-width: 480px) {
  .payment-qr-page {
    padding: 1rem;
  }

  .qr-section,
  .details-section,
  .actions {
    padding: 1rem;
  }

  .detail-item.highlight {
    margin: 0.5rem -1rem;
    padding: 1rem;
  }
}
</style>
