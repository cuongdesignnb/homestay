<template>
  <div class="payment-checkout py-12">
    <div class="container mx-auto px-4 max-w-6xl">
      <button class="text-blue-500 hover:underline mb-6" @click="router.back()">
        ← {{ $t("common.back") }}
      </button>

      <div v-if="loading" class="flex justify-center py-12">
        <div class="spinner" />
      </div>
      <div v-else-if="!room" class="card p-8 text-center">
        <p class="text-lg text-gray-500">
          {{ $t("common.no_data", "No data yet") }}
        </p>
        <router-link to="/rooms" class="btn btn-secondary mt-4">
          {{ $t("common.back") }}
        </router-link>
      </div>
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <section class="lg:col-span-2 space-y-6">
          <div class="card p-6">
            <h2 class="text-2xl font-semibold mb-4">
              {{ $t("rooms.guest_information", "Guest information") }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <label class="form-field">
                <span>{{ $t("common.name", "Name") }}</span>
                <input v-model="guestForm.name" type="text" required />
              </label>
              <label class="form-field">
                <span>{{ $t("common.email", "Email") }}</span>
                <input v-model="guestForm.email" type="email" required />
              </label>
              <label class="form-field">
                <span>{{ $t("common.phone", "Phone") }}</span>
                <input v-model="guestForm.phone" type="text" required />
              </label>
              <label class="form-field md:col-span-2">
                <span>{{
                  $t("common.special_requests", "Special requests")
                }}</span>
                <textarea
                  v-model="guestForm.specialRequests"
                  rows="3"
                  :placeholder="
                    $t(
                      'rooms.special_requests_placeholder',
                      'Allergies, arrival time...'
                    )
                  "
                ></textarea>
              </label>
            </div>
          </div>

          <div class="card p-6">
            <h2 class="text-2xl font-semibold mb-4">
              {{ $t("common.payment_method", "Payment method") }}
            </h2>
            <div class="space-y-4">
              <label
                class="payment-option"
                :class="{ active: paymentMethod === 'pay_at_checkin' }"
              >
                <input
                  v-model="paymentMethod"
                  type="radio"
                  value="pay_at_checkin"
                />
                <div class="payment-option-icon cash">
                  <span class="material-icons-outlined">payments</span>
                </div>
                <div class="flex-1">
                  <p class="font-semibold">
                    {{
                      $t("common.pay_at_checkin", "Thanh toán khi nhận phòng")
                    }}
                  </p>
                  <small class="text-gray-500">{{
                    $t(
                      "common.pay_at_checkin_desc",
                      "Thanh toán bằng tiền mặt hoặc chuyển khoản khi đến nhận phòng"
                    )
                  }}</small>
                </div>
              </label>
              <label
                class="payment-option"
                :class="{ active: paymentMethod === 'sepay' }"
              >
                <input v-model="paymentMethod" type="radio" value="sepay" />
                <div class="payment-option-icon qr">
                  <span class="material-icons-outlined">qr_code_2</span>
                </div>
                <div class="flex-1">
                  <p class="font-semibold">
                    {{ $t("common.pay_online", "Thanh toán online qua QR") }}
                  </p>
                  <small class="text-gray-500">{{
                    $t(
                      "common.pay_with_qr",
                      "Chuyển khoản ngân hàng qua mã QR SePay"
                    )
                  }}</small>
                </div>
              </label>
              <div
                v-if="paymentMethod === 'pay_at_checkin'"
                class="bg-amber-50 text-amber-800 p-4 rounded-lg text-sm"
              >
                <p class="flex items-center gap-2">
                  <span class="material-icons-outlined text-lg">info</span>
                  {{
                    $t(
                      "common.pay_at_checkin_note",
                      "Booking của bạn sẽ được giữ chỗ. Vui lòng thanh toán khi đến nhận phòng."
                    )
                  }}
                </p>
              </div>
              <div
                v-else
                class="bg-blue-50 text-blue-800 p-4 rounded-lg text-sm"
              >
                <p class="flex items-center gap-2">
                  <span class="material-icons-outlined text-lg">lock</span>
                  {{
                    $t(
                      "common.payment_note",
                      "Bạn sẽ được chuyển đến SePay để hoàn tất thanh toán an toàn."
                    )
                  }}
                </p>
              </div>
            </div>
          </div>

          <div class="card p-6">
            <h2 class="text-2xl font-semibold mb-4">
              {{ $t("common.confirmation", "Confirmation") }}
            </h2>
            <p class="text-gray-500 mb-4">
              {{
                $t(
                  "common.confirmation_note",
                  "Please review your details before continuing."
                )
              }}
            </p>
            <button
              class="btn btn-primary w-full"
              :disabled="processing"
              @click="submitPayment"
            >
              <span
                v-if="processing"
                class="flex items-center justify-center gap-2"
              >
                <span class="spinner-sm"></span>
                {{ $t("common.loading") }}
              </span>
              <span v-else-if="paymentMethod === 'pay_at_checkin'">
                {{ $t("common.confirm_booking", "Xác nhận đặt phòng") }}
              </span>
              <span v-else>
                {{ $t("common.proceed_to_payment", "Thanh toán ngay") }}
              </span>
            </button>
            <p v-if="errorMessage" class="text-red-500 text-sm mt-3">
              {{ errorMessage }}
            </p>
          </div>
        </section>

        <aside class="card p-6 space-y-4">
          <div>
            <h3 class="text-xl font-semibold mb-2">{{ room.name }}</h3>
            <p class="text-gray-500">{{ staySummary }}</p>
          </div>
          <div class="border-t pt-4 space-y-2">
            <div class="flex justify-between text-sm">
              <span>{{ nights }} × {{ formatPrice(roomPrice) }}</span>
              <span>{{ formatPrice(subtotal) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span>{{ $t("common.tax", "Tax (10%)") }}</span>
              <span>{{ formatPrice(taxAmount) }}</span>
            </div>
            <div
              class="flex justify-between text-lg font-semibold border-t pt-2"
            >
              <span>{{ $t("common.total") }}</span>
              <span>{{ formatPrice(totalPrice) }}</span>
            </div>
          </div>
          <div class="bg-blue-50 text-blue-800 p-4 rounded-lg text-sm">
            {{
              $t(
                "common.payment_security",
                "Your booking will be confirmed automatically once the payment is successful."
              )
            }}
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import dayjs from "dayjs";
import api from "@/services/api";
import { useAuthStore } from "@/stores/auth";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const room = ref(null);
const loading = ref(true);
const processing = ref(false);
const errorMessage = ref("");
const paymentMethod = ref("pay_at_checkin");
const createdBooking = ref(null);

const bookingParams = reactive({
  roomId: route.query.roomId ? Number(route.query.roomId) : null,
  checkIn: route.query.checkIn,
  checkInTime: route.query.checkInTime || "14:00",
  checkOut: route.query.checkOut,
  checkOutTime: route.query.checkOutTime || "12:00",
  guests: route.query.guests ? Number(route.query.guests) : 1,
});

const guestForm = reactive({
  name: authStore.user?.name || "",
  email: authStore.user?.email || "",
  phone: authStore.user?.phone || "",
  specialRequests: "",
});

const nights = computed(() => {
  if (!bookingParams.checkIn || !bookingParams.checkOut) return 0;
  return dayjs(bookingParams.checkOut).diff(
    dayjs(bookingParams.checkIn),
    "day"
  );
});

const roomPrice = computed(
  () => room.value?.discount_price || room.value?.price_per_night || 0
);
const subtotal = computed(() => roomPrice.value * nights.value);
const taxAmount = computed(() => subtotal.value * 0.1);
const totalPrice = computed(() => subtotal.value + taxAmount.value);

const staySummary = computed(() => {
  if (!bookingParams.checkIn || !bookingParams.checkOut) return "";
  const start = dayjs(bookingParams.checkIn).format("DD MMM YYYY");
  const end = dayjs(bookingParams.checkOut).format("DD MMM YYYY");
  return `${start} (${bookingParams.checkInTime}) → ${end} (${bookingParams.checkOutTime}) · ${bookingParams.guests} guests`;
});

const formatPrice = (amount) =>
  new Intl.NumberFormat("vi-VN", { style: "currency", currency: "VND" }).format(
    amount || 0
  );

const validateParams = () => {
  if (!bookingParams.roomId || nights.value <= 0) {
    router.push("/rooms");
    return false;
  }
  return true;
};

const submitPayment = async () => {
  if (processing.value) return;
  if (!guestForm.name || !guestForm.email || !guestForm.phone) {
    errorMessage.value = "Please fill in all required information.";
    return;
  }

  processing.value = true;
  errorMessage.value = "";

  try {
    if (!createdBooking.value) {
      const bookingResponse = await api.post("/bookings", {
        room_id: bookingParams.roomId,
        check_in: bookingParams.checkIn,
        check_in_time: bookingParams.checkInTime,
        check_out: bookingParams.checkOut,
        check_out_time: bookingParams.checkOutTime,
        guests: bookingParams.guests,
        guest_name: guestForm.name,
        guest_email: guestForm.email,
        guest_phone: guestForm.phone,
        special_requests: guestForm.specialRequests || null,
        payment_method: paymentMethod.value,
      });
      createdBooking.value = bookingResponse.data.booking;
    }

    // Thanh toán khi nhận phòng - chỉ tạo booking và chuyển đến trang thành công
    if (paymentMethod.value === "pay_at_checkin") {
      router.push({
        path: "/payment/result",
        query: {
          booking_id: createdBooking.value.id,
          payment_method: "pay_at_checkin",
          status: "success",
        },
      });
      return;
    }

    // Thanh toán online qua SePay
    if (paymentMethod.value === "sepay") {
      const paymentResponse = await api.post("/payments/sepay", {
        booking_id: createdBooking.value.id,
        amount: createdBooking.value.final_amount || totalPrice.value,
      });

      if (paymentResponse.data.checkout_url) {
        window.location.href = paymentResponse.data.checkout_url;
      } else {
        errorMessage.value = "Unable to start SePay session. Please try again.";
      }
    }
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || "Unable to process payment.";
  } finally {
    processing.value = false;
  }
};

onMounted(async () => {
  if (!validateParams()) return;
  try {
    const response = await api.get(`/rooms/${bookingParams.roomId}`);
    room.value = response.data;
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || "Unable to load room information.";
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.card {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  color: #0f172a;
}

.form-field input,
.form-field textarea {
  border: 1px solid rgba(15, 23, 42, 0.15);
  border-radius: 0.75rem;
  padding: 0.75rem 1rem;
}

.payment-option {
  display: flex;
  gap: 1rem;
  border: 2px solid rgba(15, 23, 42, 0.1);
  border-radius: 1rem;
  padding: 1rem;
  align-items: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.payment-option:hover {
  border-color: rgba(15, 23, 42, 0.25);
  background: rgba(15, 23, 42, 0.02);
}

.payment-option.active {
  border-color: #2563eb;
  background: rgba(37, 99, 235, 0.05);
}

.payment-option input {
  accent-color: #2563eb;
  width: 18px;
  height: 18px;
}

.payment-option-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.payment-option-icon.cash {
  background: linear-gradient(135deg, #22c55e, #16a34a);
  color: white;
}

.payment-option-icon.qr {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
}

.payment-option-icon .material-icons-outlined {
  font-size: 24px;
}

.spinner-sm {
  width: 16px;
  height: 16px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
