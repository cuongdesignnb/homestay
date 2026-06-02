<template>
  <div class="admin-boat-tours">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Boat Tours Management</h1>
        <p class="text-gray-600">Manage your boat tours and water adventures</p>
      </div>
      <button
        @click="showAddModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center"
      >
        <svg
          class="w-5 h-5 mr-2"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 4v16m8-8H4"
          />
        </svg>
        Add New Boat Tour
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
      <div class="flex flex-col md:flex-row gap-4 items-center">
        <div class="flex-1">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search boat tours..."
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>
        <div class="flex gap-2">
          <select
            v-model="statusFilter"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
          >
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
          <button
            @click="loadBoatTours"
            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg"
          >
            Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div
        class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"
      ></div>
      <p class="mt-2 text-gray-600">Loading boat tours...</p>
    </div>

    <!-- Tours Table -->
    <div v-else class="bg-white rounded-lg shadow-sm overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Tour
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Price
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Duration
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Max Participants
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Status
            </th>
            <th
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Rating
            </th>
            <th
              class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="tour in boatTours" :key="tour.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="h-10 w-10 flex-shrink-0">
                  <img
                    v-if="tour.image_gallery && tour.image_gallery.length > 0"
                    :src="tour.image_gallery[0]"
                    :alt="tour.name"
                    class="h-10 w-10 rounded-lg object-cover"
                  />
                  <div
                    v-else
                    class="h-10 w-10 bg-gray-200 rounded-lg flex items-center justify-center"
                  >
                    <svg
                      class="w-6 h-6 text-gray-400"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ tour.name }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ tour.departure_location }}
                  </div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">
                ${{ tour.price }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ tour.duration }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">
                {{ tour.max_participants }} people
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  tour.status === 'active'
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800',
                ]"
              >
                {{ tour.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div v-if="tour.total_reviews > 0" class="flex items-center">
                <div class="flex text-yellow-400 mr-1">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                    />
                  </svg>
                </div>
                <span class="text-sm text-gray-600"
                  >{{ tour.average_rating.toFixed(1) }} ({{
                    tour.total_reviews
                  }})</span
                >
              </div>
              <div v-else class="text-sm text-gray-400">No reviews</div>
            </td>
            <td
              class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
            >
              <div class="flex items-center justify-end space-x-2">
                <button
                  @click="viewTour(tour)"
                  class="text-green-600 hover:text-green-900"
                  title="View on Frontend"
                >
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                    />
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                    />
                  </svg>
                </button>
                <button
                  @click="editTour(tour)"
                  class="text-blue-600 hover:text-blue-900"
                  title="Edit"
                >
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                    />
                  </svg>
                </button>
                <button
                  @click="deleteTour(tour)"
                  class="text-red-600 hover:text-red-900"
                  title="Delete"
                >
                  <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="boatTours.length === 0" class="text-center py-12">
        <svg
          class="w-12 h-12 mx-auto text-gray-400 mb-4"
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
        <h3 class="text-lg font-medium text-gray-900 mb-2">
          No boat tours found
        </h3>
        <p class="text-gray-600">
          Get started by creating your first boat tour.
        </p>
      </div>
    </div>

    <!-- Pagination -->
    <div
      v-if="pagination && pagination.last_page > 1"
      class="mt-6 flex justify-center"
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

    <!-- Add/Edit Modal -->
    <BoatTourModal
      v-if="showAddModal || showEditModal"
      :show="showAddModal || showEditModal"
      :tour="editingTour"
      @close="closeModal"
      @saved="onTourSaved"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import api from "@/services/api";
import BoatTourModal from "@/components/admin/BoatTourModal.vue";

const boatTours = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const statusFilter = ref("");
const pagination = ref(null);
const currentPage = ref(1);

const showAddModal = ref(false);
const showEditModal = ref(false);
const editingTour = ref(null);

const loadBoatTours = async (page = 1) => {
  try {
    loading.value = true;
    const params = {
      page,
      ...(searchQuery.value && { search: searchQuery.value }),
      ...(statusFilter.value && { status: statusFilter.value }),
    };

    const response = await api.get("/admin/boat-tours", { params });

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

const viewTour = (tour) => {
  // Open boat tour detail page in new tab
  const frontendUrl = window.location.origin.replace(":8765", ":5174"); // Replace backend port with frontend port
  const detailUrl = `${frontendUrl}/boat-tours/${tour.id}`;
  window.open(detailUrl, "_blank");
};

const editTour = (tour) => {
  editingTour.value = tour;
  showEditModal.value = true;
};

const deleteTour = async (tour) => {
  if (!confirm(`Are you sure you want to delete "${tour.name}"?`)) {
    return;
  }

  try {
    const response = await api.delete(`/admin/boat-tours/${tour.id}`);

    if (response.data.success) {
      await loadBoatTours(currentPage.value);
      alert("Boat tour deleted successfully!");
    }
  } catch (error) {
    console.error("Failed to delete boat tour:", error);
    alert("Failed to delete boat tour. Please try again.");
  }
};

const closeModal = () => {
  showAddModal.value = false;
  showEditModal.value = false;
  editingTour.value = null;
};

const onTourSaved = () => {
  closeModal();
  loadBoatTours(currentPage.value);
};

onMounted(() => {
  loadBoatTours();
});
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
