<template>
  <div class="payment-callback py-12">
    <div class="container mx-auto px-4 max-w-2xl text-center">
      <div v-if="loading" class="spinner"></div>
      <div v-else-if="success" class="card p-8">
        <div class="text-6xl mb-4">✓</div>
        <h1 class="text-3xl font-bold text-green-600 mb-4">
          Payment Successful!
        </h1>
        <p class="text-gray-600 mb-6">Your booking has been confirmed.</p>
        <router-link to="/bookings" class="btn btn-primary">
          View My Bookings
        </router-link>
      </div>
      <div v-else class="card p-8">
        <div class="text-6xl mb-4">✗</div>
        <h1 class="text-3xl font-bold text-red-600 mb-4">Payment Failed</h1>
        <p class="text-gray-600 mb-6">
          There was an issue processing your payment.
        </p>
        <router-link to="/" class="btn btn-secondary">
          Return to Home
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import api from "@/services/api";

const route = useRoute();
const loading = ref(true);
const success = ref(false);

onMounted(async () => {
  try {
    const response = await api.post("/payments/callback", {
      transaction_id: route.query.transaction_id,
      status: route.query.status,
    });

    success.value = response.data.payment.status === "completed";
  } catch (error) {
    success.value = false;
  } finally {
    loading.value = false;
  }
});
</script>
