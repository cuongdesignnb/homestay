<template>
  <div class="car-rentals-page">
    <section class="hero">
      <div class="container">
        <div class="hero-content">
          <h1>{{ $t("car_rentals.title", "Car Rentals") }}</h1>
          <p>{{ $t("car_rentals.subtitle", "Find the right vehicle for your trip") }}</p>
        </div>
      </div>
    </section>

    <section class="filters-section">
      <div class="container">
        <div class="filters-card">
          <div class="filters-grid compact">
            <div class="filter-field span-2">
              <label>{{ $t("car_rentals.search", "Search") }}</label>
              <input
                v-model="filters.search"
                type="text"
                :placeholder="$t('car_rentals.search_placeholder', 'Search by name, brand, model...')"
              />
            </div>
            <div class="filter-field">
              <label>{{ $t("car_rentals.location", "Location") }}</label>
              <input v-model="filters.location" type="text" placeholder="Cat Ba" />
            </div>
            <div class="filter-field">
              <label>{{ $t("car_rentals.type", "Type") }}</label>
              <select v-model="filters.type">
                <option value="">{{ $t("car_rentals.any", "Any") }}</option>
                <option value="motorbike">{{ $t("car_rentals.motorbike", "Motorbike") }}</option>
                <option value="sedan">Sedan</option>
                <option value="suv">SUV</option>
                <option value="mpv">MPV</option>
                <option value="hatchback">Hatchback</option>
                <option value="pickup">Pickup</option>
                <option value="van">Van</option>
                <option value="luxury">Luxury</option>
              </select>
            </div>
            <div class="filter-field">
              <label>{{ $t("car_rentals.transmission", "Transmission") }}</label>
              <select v-model="filters.transmission">
                <option value="">{{ $t("car_rentals.any", "Any") }}</option>
                <option value="automatic">Automatic</option>
                <option value="manual">Manual</option>
              </select>
            </div>
            <div class="filter-field">
              <label>{{ $t("car_rentals.min_price", "Min price") }}</label>
              <input v-model.number="filters.min_price" type="number" min="0" placeholder="500000" />
            </div>
            <div class="filter-field">
              <label>{{ $t("car_rentals.max_price", "Max price") }}</label>
              <input v-model.number="filters.max_price" type="number" min="0" placeholder="1500000" />
            </div>
            <div class="filter-field">
              <label>{{ $t("car_rentals.sort", "Sort") }}</label>
              <select v-model="filters.sort_by">
                <option value="created_at">{{ $t("car_rentals.sort_newest", "Newest") }}</option>
                <option value="price_per_day">{{ $t("car_rentals.sort_price", "Price") }}</option>
                <option value="average_rating">{{ $t("car_rentals.sort_rating", "Rating") }}</option>
                <option value="seats">{{ $t("car_rentals.sort_seats", "Seats") }}</option>
              </select>
            </div>
            <div class="filter-field">
              <label>{{ $t("car_rentals.sort_dir", "Order") }}</label>
              <select v-model="filters.sort_dir">
                <option value="desc">{{ $t("car_rentals.desc", "High to low") }}</option>
                <option value="asc">{{ $t("car_rentals.asc", "Low to high") }}</option>
              </select>
            </div>
            <div class="filter-field">
              <label>{{ $t("car_rentals.available", "Availability") }}</label>
              <select v-model="filters.available">
                <option value="">{{ $t("car_rentals.any", "Any") }}</option>
                <option value="true">{{ $t("car_rentals.available_now", "Available") }}</option>
                <option value="false">{{ $t("car_rentals.unavailable", "Unavailable") }}</option>
              </select>
            </div>
          </div>

          <div class="filters-actions">
            <button class="btn-secondary" @click="resetFilters">
              {{ $t("car_rentals.reset", "Reset") }}
            </button>
            <button class="btn-primary" @click="loadCars(1)">
              {{ $t("car_rentals.apply", "Apply filters") }}
            </button>
          </div>
        </div>
      </div>
    </section>

    <section class="results-section">
      <div class="container">
        <div v-if="loading" class="loading">
          <div class="spinner"></div>
          <p>{{ $t("car_rentals.loading", "Loading cars...") }}</p>
        </div>

        <div v-else-if="cars.length" class="grid">
          <article v-for="car in cars" :key="car.id" class="card">
            <div class="card-media">
              <img :src="car.cover_image || car.images?.[0] || '/placeholder-car.jpg'" :alt="car.name" />
              <span class="price">{{ formatPrice(car.price_per_day) }}/day</span>
              <span class="badge" :class="car.is_available ? 'available' : 'unavailable'">
                {{ car.is_available ? $t("car_rentals.available_now", "Available") : $t("car_rentals.unavailable", "Unavailable") }}
              </span>
            </div>
            <div class="card-body">
              <h3>{{ car.name }}</h3>
              <p class="muted">{{ car.short_description || car.description }}</p>
              <div class="meta">
                <span>{{ car.location || "Cat Ba" }}</span>
                <span>{{ car.seats }} seats</span>
                <span>{{ car.transmission || "auto" }}</span>
              </div>
              <div class="rating">
                <span>★ {{ Number(car.average_rating || 0).toFixed(1) }}</span>
                <span class="muted">({{ car.total_reviews || 0 }})</span>
              </div>
              <router-link :to="`/car-rentals/${car.id}`" class="btn-primary">
                {{ $t("car_rentals.view_details", "View details") }}
              </router-link>
            </div>
          </article>
        </div>

        <div v-else class="empty">
          <h3>{{ $t("car_rentals.empty", "No cars found") }}</h3>
          <p>{{ $t("car_rentals.try_adjust", "Try adjusting your filters") }}</p>
        </div>

        <div v-if="pagination && pagination.last_page > 1" class="pagination">
          <button
            :disabled="pagination.current_page === 1"
            @click="changePage(pagination.current_page - 1)"
          >
            {{ $t("car_rentals.prev", "Previous") }}
          </button>
          <button
            v-for="page in pageRange"
            :key="page"
            :class="{ active: page === pagination.current_page }"
            @click="changePage(page)"
          >
            {{ page }}
          </button>
          <button
            :disabled="pagination.current_page === pagination.last_page"
            @click="changePage(pagination.current_page + 1)"
          >
            {{ $t("car_rentals.next", "Next") }}
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api";

const cars = ref([]);
const loading = ref(false);
const pagination = ref(null);

const { locale } = useI18n({ useScope: "global" });

const filters = ref({
  search: "",
  location: "",
  type: "",
  brand: "",
  transmission: "",
  fuel_type: "",
  min_price: "",
  max_price: "",
  sort_by: "created_at",
  sort_dir: "desc",
  available: "",
  features: [],
});

const formatPrice = (price) =>
  new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(price || 0);

const buildParams = (page = 1) => {
  const params = { page };
  Object.entries(filters.value).forEach(([key, value]) => {
    if (value === "" || value === null || value === undefined) return;
    if (Array.isArray(value) && value.length === 0) return;
    params[key] = Array.isArray(value) ? value.join(",") : value;
  });
  return params;
};

const loadCars = async (page = 1) => {
  try {
    loading.value = true;
    const response = await api.get("/car-rentals", { params: buildParams(page) });
    if (response.data.success) {
      cars.value = response.data.data.data;
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        per_page: response.data.data.per_page,
        total: response.data.data.total,
      };
    }
  } catch (error) {
    console.error("Failed to load car rentals:", error);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (!pagination.value) return;
  if (page < 1 || page > pagination.value.last_page) return;
  loadCars(page);
};

const pageRange = computed(() => {
  if (!pagination.value) return [];
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  const start = Math.max(1, current - 2);
  const end = Math.min(last, current + 2);
  return Array.from({ length: end - start + 1 }, (_, i) => start + i);
});

const resetFilters = () => {
  filters.value = {
    search: "",
    location: "",
    type: "",
    brand: "",
    transmission: "",
    fuel_type: "",
    min_price: "",
    max_price: "",
    sort_by: "created_at",
    sort_dir: "desc",
    available: "",
    features: [],
  };
  loadCars(1);
};

onMounted(() => {
  loadCars();
});

watch(locale, () => {
  loadCars(1);
});
</script>

<style scoped>
.car-rentals-page {
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
  padding: 3rem 0;
}

.hero-content h1 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.filters-section {
  margin-top: -2rem;
  padding-bottom: 2rem;
}

.filters-card {
  background: white;
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
}

.filters-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
}

.filters-grid.compact {
  row-gap: 0.9rem;
}

.filter-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-field span,
.filter-field label {
  font-weight: 600;
  font-size: 0.9rem;
  color: #0f172a;
}

.filter-field input,
.filter-field select {
  border: 1px solid #cbd5f5;
  border-radius: 0.75rem;
  padding: 0.7rem 0.9rem;
  font-size: 0.95rem;
}

.filter-field.span-2 {
  grid-column: span 2;
}

.features {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.filters-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1.5rem;
}

.btn-primary,
.btn-secondary {
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary {
  background: #0f172a;
  color: white;
}

.btn-secondary {
  background: #e2e8f0;
  color: #0f172a;
}

.results-section {
  padding: 2rem 0 4rem;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 1.5rem;
}

.card {
  background: white;
  border-radius: 1.25rem;
  overflow: hidden;
  box-shadow: 0 15px 30px rgba(15, 23, 42, 0.08);
  display: flex;
  flex-direction: column;
}

.card-media {
  position: relative;
  height: 180px;
  background: #e2e8f0;
}

.card-media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-media .price {
  position: absolute;
  top: 12px;
  right: 12px;
  background: white;
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
}

.card-media .badge {
  position: absolute;
  bottom: 12px;
  left: 12px;
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge.available {
  background: #dcfce7;
  color: #15803d;
}

.badge.unavailable {
  background: #fee2e2;
  color: #b91c1c;
}

.card-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  flex: 1;
}

.card-body h3 {
  font-size: 1.25rem;
  font-weight: 700;
}

.card-body .muted {
  color: #64748b;
  font-size: 0.9rem;
}

.meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  font-size: 0.85rem;
  color: #475569;
}

.rating {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
}

.empty {
  text-align: center;
  padding: 3rem 0;
}

.loading {
  text-align: center;
  padding: 3rem 0;
}

.spinner {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border: 3px solid #e2e8f0;
  border-top-color: #0f172a;
  margin: 0 auto 1rem;
  animation: spin 0.8s linear infinite;
}

.pagination {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 2rem;
}

.pagination button {
  padding: 0.5rem 0.9rem;
  border-radius: 0.6rem;
  border: 1px solid #cbd5f5;
  background: white;
}

.pagination button.active {
  background: #0f172a;
  color: white;
  border-color: #0f172a;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 1024px) {
  .filters-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .filter-field.span-2 {
    grid-column: span 2;
  }
}

@media (max-width: 640px) {
  .filters-grid {
    grid-template-columns: 1fr;
  }

  .filter-field.span-2 {
    grid-column: span 1;
  }

  .filters-actions {
    flex-direction: column;
  }
}
</style>
