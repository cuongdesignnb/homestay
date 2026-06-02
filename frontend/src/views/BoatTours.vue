<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Filters Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <div class="flex flex-col md:flex-row gap-4 items-center">
          <div class="flex-1">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search boat tours..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div class="flex gap-2">
            <select
              v-model="sortBy"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Sort by</option>
              <option value="asc">Price: Low to High</option>
              <option value="desc">Price: High to Low</option>
            </select>
            <button
              @click="loadBoatTours"
              class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
            >
              Search
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div
          class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"
        ></div>
        <p class="mt-4 text-gray-600">Loading boat tours...</p>
      </div>

      <!-- Tours Grid -->
      <div
        v-else-if="boatTours.length > 0"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
      >
        <div
          v-for="tour in boatTours"
          :key="tour.id"
          class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden"
        >
          <!-- Tour Image -->
          <div class="h-48 bg-gray-200 relative overflow-hidden">
            <img
              v-if="tour.image_gallery && tour.image_gallery.length > 0"
              :src="tour.image_gallery[0]"
              :alt="tour.name"
              class="w-full h-full object-cover"
            />
            <div
              v-else
              class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-400 to-blue-600 text-white"
            >
              <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                <path
                  fill-rule="evenodd"
                  d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
            <div
              class="absolute top-4 right-4 bg-white rounded-full px-3 py-1 text-sm font-semibold text-blue-600"
            >
              ${{ tour.price }}
            </div>
          </div>

          <!-- Tour Info -->
          <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-2">
              {{ tour.name }}
            </h3>
            <p class="text-gray-600 mb-4 line-clamp-2">
              {{ tour.short_description }}
            </p>

            <div class="space-y-2 mb-4">
              <div class="flex items-center text-sm text-gray-600">
                <svg
                  class="w-4 h-4 mr-2"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                    clip-rule="evenodd"
                  />
                </svg>
                Duration: {{ tour.duration }}
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <svg
                  class="w-4 h-4 mr-2"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                    clip-rule="evenodd"
                  />
                </svg>
                From: {{ tour.departure_location }}
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <svg
                  class="w-4 h-4 mr-2"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"
                  />
                </svg>
                Max: {{ tour.max_participants }} people
              </div>
            </div>

            <!-- Rating -->
            <div v-if="tour.total_reviews > 0" class="flex items-center mb-4">
              <div class="flex text-yellow-400">
                <svg
                  v-for="star in 5"
                  :key="star"
                  class="w-4 h-4"
                  :class="
                    star <= Math.round(Number(tour.average_rating || 0))
                      ? 'text-yellow-400'
                      : 'text-gray-300'
                  "
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                  />
                </svg>
              </div>
              <span class="ml-2 text-sm text-gray-600"
                >{{ Number(tour.average_rating || 0).toFixed(1) }} ({{
                  tour.total_reviews
                }}
                reviews)</span
              >
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
              <router-link
                :to="`/boat-tours/${tour.id}`"
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-lg transition-colors"
              >
                View Details
              </router-link>
              <a
                :href="tour.whats_app_link"
                target="_blank"
                class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg transition-colors"
              >
                WhatsApp
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <div class="mb-4">
          <svg
            class="w-16 h-16 mx-auto text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
            />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">
          No boat tours found
        </h3>
        <p class="text-gray-600">
          Try adjusting your search criteria or check back later.
        </p>
      </div>

      <!-- Pagination -->
      <div
        v-if="pagination && pagination.last_page > 1"
        class="mt-12 flex justify-center"
      >
        <nav class="flex space-x-2">
          <button
            v-if="pagination.current_page > 1"
            @click="changePage(pagination.current_page - 1)"
            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Previous
          </button>
          <button
            v-for="page in getPageRange()"
            :key="page"
            @click="changePage(page)"
            :class="[
              'px-3 py-2 border rounded-lg',
              page === pagination.current_page
                ? 'bg-blue-600 text-white border-blue-600'
                : 'border-gray-300 hover:bg-gray-50',
            ]"
          >
            {{ page }}
          </button>
          <button
            v-if="pagination.current_page < pagination.last_page"
            @click="changePage(pagination.current_page + 1)"
            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Next
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import api from "@/services/api";

const boatTours = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const sortBy = ref("");
const pagination = ref(null);
const currentPage = ref(1);

const loadBoatTours = async (page = 1) => {
  try {
    loading.value = true;
    const params = {
      page,
      ...(searchQuery.value && { search: searchQuery.value }),
      ...(sortBy.value && { sort_by_price: sortBy.value }),
    };

    const response = await api.get("/boat-tours", { params });

    if (response.data.success) {
      boatTours.value = response.data.data.data;
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        per_page: response.data.data.per_page,
        total: response.data.data.total,
      };
      currentPage.value = page;
    }
  } catch (error) {
    console.error("Failed to load boat tours:", error);
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    loadBoatTours(page);
  }
};

const getPageRange = () => {
  if (!pagination.value) return [];

  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  const range = [];

  const start = Math.max(1, current - 2);
  const end = Math.min(last, current + 2);

  for (let i = start; i <= end; i++) {
    range.push(i);
  }

  return range;
};

onMounted(() => {
  loadBoatTours();
});
</script>
