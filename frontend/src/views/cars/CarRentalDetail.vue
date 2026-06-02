<template>
  <div class="car-detail">
    <section class="hero" v-if="car">
      <div class="container">
        <div class="hero-content">
          <p class="breadcrumb">
            <router-link to="/car-rentals">{{ $t("car_rentals.title", "Car Rentals") }}</router-link>
            <span>›</span>
            <span>{{ car.name }}</span>
          </p>
          <h1>{{ car.name }}</h1>
          <div class="hero-meta">
            <span>{{ car.location || "Cat Ba" }}</span>
            <span>{{ car.seats }} seats</span>
            <span>{{ car.transmission || "auto" }}</span>
            <span>{{ car.fuel_type || "petrol" }}</span>
          </div>
        </div>
      </div>
    </section>

    <section class="content" v-if="car">
      <div class="container">
        <div class="layout">
          <div class="gallery">
            <img :src="car.cover_image || car.images?.[0] || '/placeholder-car.jpg'" :alt="car.name" />
            <div class="thumbs" v-if="car.images?.length > 1">
              <img
                v-for="(image, index) in car.images"
                :key="index"
                :src="image"
                :alt="`${car.name}-${index}`"
              />
            </div>
          </div>

          <div class="info">
            <div class="price">
              <span>{{ formatPrice(car.price_per_day) }}</span>
              <small>/day</small>
            </div>
            <div class="availability" :class="car.is_available ? 'available' : 'unavailable'">
              {{ car.is_available ? $t("car_rentals.available_now", "Available") : $t("car_rentals.unavailable", "Unavailable") }}
            </div>

            <div class="specs">
              <div><strong>{{ $t("car_rentals.brand", "Brand") }}:</strong> {{ car.brand || '-' }}</div>
              <div><strong>{{ $t("car_rentals.type", "Type") }}:</strong> {{ car.type || '-' }}</div>
              <div><strong>{{ $t("car_rentals.seats", "Seats") }}:</strong> {{ car.seats }}</div>
              <div><strong>{{ $t("car_rentals.transmission", "Transmission") }}:</strong> {{ car.transmission || '-' }}</div>
              <div><strong>{{ $t("car_rentals.fuel", "Fuel") }}:</strong> {{ car.fuel_type || '-' }}</div>
            </div>

            <div class="description">
              <h3>{{ $t("car_rentals.description", "Description") }}</h3>
              <p>{{ car.description || car.short_description }}</p>
            </div>

            <div class="features" v-if="car.features?.length">
              <h3>{{ $t("car_rentals.features", "Features") }}</h3>
              <ul>
                <li v-for="feature in car.features" :key="feature">{{ formatFeature(feature) }}</li>
              </ul>
            </div>

            <div class="actions">
              <a
                v-if="car.contact_whatsapp"
                :href="`https://wa.me/${car.contact_whatsapp}`"
                target="_blank"
                class="btn-primary"
              >
                {{ $t("car_rentals.contact_whatsapp", "Contact via WhatsApp") }}
              </a>
              <a v-if="car.contact_phone" :href="`tel:${car.contact_phone}`" class="btn-secondary">
                {{ $t("car_rentals.call_now", "Call now") }}
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section v-else class="empty">
      <div class="container">
        <p>{{ $t("car_rentals.loading", "Loading...") }}</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";
import api from "@/services/api";

const route = useRoute();
const car = ref(null);
const { locale } = useI18n({ useScope: "global" });

const formatPrice = (price) =>
  new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(price || 0);

const formatFeature = (feature) => {
  const map = {
    air_conditioning: "Air conditioning",
    gps: "GPS",
    bluetooth: "Bluetooth",
    rear_camera: "Rear camera",
    cruise_control: "Cruise control",
    parking_sensors: "Parking sensors",
    sunroof: "Sunroof",
  };
  return map[feature] || feature;
};

const loadCar = async () => {
  try {
    const response = await api.get(`/car-rentals/${route.params.id}`);
    if (response.data.success) {
      car.value = response.data.data;
    }
  } catch (error) {
    console.error("Failed to load car rental:", error);
  }
};

onMounted(() => {
  loadCar();
});

watch(locale, () => {
  loadCar();
});
</script>

<style scoped>
.car-detail {
  background: #f8fafc;
  min-height: 100vh;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

.hero {
  background: linear-gradient(135deg, #0f172a, #1e293b);
  color: white;
  padding: 2.5rem 0;
}

.hero-content h1 {
  font-size: 2.2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.hero-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  font-size: 0.95rem;
  color: #e2e8f0;
}

.breadcrumb {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  font-size: 0.9rem;
  color: #cbd5f5;
}

.breadcrumb a {
  color: #cbd5f5;
}

.content {
  padding: 2.5rem 0 4rem;
}

.layout {
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  gap: 2rem;
}

.gallery img {
  width: 100%;
  border-radius: 1.5rem;
  object-fit: cover;
  max-height: 420px;
}

.thumbs {
  display: flex;
  gap: 0.75rem;
  margin-top: 1rem;
}

.thumbs img {
  width: 80px;
  height: 60px;
  border-radius: 0.75rem;
  object-fit: cover;
}

.info {
  background: white;
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
}

.price {
  font-size: 2rem;
  font-weight: 700;
  color: #0f172a;
}

.price small {
  font-size: 1rem;
  color: #64748b;
}

.availability {
  margin-top: 0.5rem;
  display: inline-flex;
  padding: 0.3rem 0.8rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
}

.available {
  background: #dcfce7;
  color: #15803d;
}

.unavailable {
  background: #fee2e2;
  color: #b91c1c;
}

.specs {
  margin-top: 1.5rem;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
  font-size: 0.95rem;
  color: #0f172a;
}

.description,
.features {
  margin-top: 1.5rem;
}

.features ul {
  padding-left: 1rem;
  margin-top: 0.5rem;
  color: #475569;
}

.actions {
  margin-top: 2rem;
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.btn-primary,
.btn-secondary {
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 600;
  text-decoration: none;
}

.btn-primary {
  background: #0f172a;
  color: white;
}

.btn-secondary {
  background: #e2e8f0;
  color: #0f172a;
}

@media (max-width: 1024px) {
  .layout {
    grid-template-columns: 1fr;
  }
}
</style>
