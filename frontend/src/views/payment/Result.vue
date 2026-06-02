<template>
  <div class="payment-result py-16">
    <div class="container mx-auto px-4 max-w-2xl">
      <div class="card text-center p-10" :class="statusClass">
        <div class="status-icon mb-4" :class="statusClass">
          {{ statusIcon }}
        </div>
        <h1 class="text-3xl font-bold mb-4">{{ statusTitle }}</h1>
        <p class="text-gray-500 mb-6">{{ statusMessage }}</p>

        <!-- Chi tiết cho thanh toán khi nhận phòng -->
        <div
          v-if="isPayAtCheckin"
          class="bg-amber-50 text-amber-800 p-4 rounded-xl mb-6 text-left"
        >
          <h3 class="font-semibold mb-2 flex items-center gap-2">
            <span class="material-icons-outlined">info</span>
            {{ $t("payment.payment_info", "Thông tin thanh toán") }}
          </h3>
          <ul class="space-y-1 text-sm">
            <li>
              •
              {{
                $t(
                  "payment.pay_cash_or_transfer",
                  "Có thể thanh toán bằng tiền mặt hoặc chuyển khoản"
                )
              }}
            </li>
            <li>
              •
              {{
                $t(
                  "payment.pay_at_reception",
                  "Thanh toán tại quầy lễ tân khi check-in"
                )
              }}
            </li>
            <li>
              • {{ $t("payment.bring_id", "Vui lòng mang theo CCCD/Passport") }}
            </li>
          </ul>
        </div>

        <div v-if="bookingId" class="text-sm text-gray-400 mb-4">
          {{ $t("payment.booking_id", "Mã đặt phòng") }}:
          <strong class="text-gray-600">#{{ bookingId }}</strong>
        </div>

        <div class="flex flex-col gap-3">
          <router-link class="btn btn-primary" to="/bookings">
            {{ $t("common.view_bookings", "Xem đặt phòng của tôi") }}
          </router-link>
          <router-link class="btn btn-secondary" to="/rooms">
            {{ $t("common.continue_exploring", "Tiếp tục khám phá") }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";

const route = useRoute();
const { t } = useI18n();

const status = computed(() =>
  (route.query.status || "pending").toString().toLowerCase()
);
const paymentMethod = computed(() => route.query.payment_method || "");
const bookingId = computed(() => route.query.booking_id || "");

const statusMappings = computed(() => ({
  success: {
    icon: "✓",
    title: t("payment.success_title", "Thanh toán thành công!"),
    message: t(
      "payment.success_message",
      "Booking của bạn đã được xác nhận. Email xác nhận đã được gửi đến bạn."
    ),
    class: "success",
  },
  error: {
    icon: "✗",
    title: t("payment.error_title", "Thanh toán thất bại"),
    message: t(
      "payment.error_message",
      "Đã có lỗi xảy ra trong quá trình thanh toán. Vui lòng thử lại."
    ),
    class: "error",
  },
  cancel: {
    icon: "⚑",
    title: t("payment.cancel_title", "Đã hủy thanh toán"),
    message: t(
      "payment.cancel_message",
      "Thanh toán đã bị hủy. Booking của bạn vẫn đang chờ thanh toán."
    ),
    class: "warning",
  },
  default: {
    icon: "⧗",
    title: t("payment.pending_title", "Đang chờ thanh toán"),
    message: t(
      "payment.pending_message",
      "Chúng tôi đang chờ xác nhận từ cổng thanh toán. Vui lòng đợi một lát."
    ),
    class: "pending",
  },
}));

// Trường hợp đặt phòng với thanh toán khi nhận phòng
const isPayAtCheckin = computed(() => paymentMethod.value === "pay_at_checkin");

const current = computed(() => {
  if (isPayAtCheckin.value) {
    return {
      icon: "🏠",
      title: t("payment.booking_confirmed_title", "Đặt phòng thành công!"),
      message: t(
        "payment.pay_at_checkin_message",
        "Booking của bạn đã được ghi nhận. Vui lòng thanh toán khi đến nhận phòng."
      ),
      class: "success",
    };
  }
  return statusMappings.value[status.value] || statusMappings.value.default;
});

const statusIcon = computed(() => current.value.icon);
const statusTitle = computed(() => current.value.title);
const statusMessage = computed(() => current.value.message);
const statusClass = computed(() => current.value.class);
</script>

<style scoped>
.card {
  border-radius: 1.5rem;
  background: #fff;
  box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
}

.status-icon {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  margin: 0 auto;
}

.status-icon.success {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
}

.status-icon.error {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
}

.status-icon.warning {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
}

.status-icon.pending {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
}

.card.success {
  border: 1px solid rgba(34, 197, 94, 0.3);
}

.card.error {
  border: 1px solid rgba(248, 113, 113, 0.3);
}

.card.warning {
  border: 1px solid rgba(251, 191, 36, 0.3);
}

.card.pending {
  border: 1px solid rgba(59, 130, 246, 0.3);
}
</style>
