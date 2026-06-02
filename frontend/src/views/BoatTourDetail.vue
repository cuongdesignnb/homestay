<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div
          class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"
        ></div>
        <p class="text-gray-600">Loading boat tour details...</p>
      </div>
    </div>

    <!-- Tour Details -->
    <div v-else-if="tour" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Breadcrumb -->
      <nav class="mb-8">
        <ol class="flex items-center space-x-2 text-sm">
          <li>
            <router-link to="/" class="text-blue-600 hover:text-blue-800"
              >Home</router-link
            >
          </li>
          <li>/</li>
          <li>
            <router-link
              to="/boat-tours"
              class="text-blue-600 hover:text-blue-800"
              >Boat Tours</router-link
            >
          </li>
          <li>/</li>
          <li class="text-gray-600">{{ tour.name }}</li>
        </ol>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
          <!-- Image Gallery -->
          <div class="mb-8">
            <div class="h-96 bg-gray-200 rounded-lg overflow-hidden mb-4">
              <img
                v-if="selectedImage"
                :src="selectedImage"
                :alt="tour.name"
                class="w-full h-full object-cover"
              />
              <div
                v-else
                class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-400 to-blue-600 text-white"
              >
                <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    fill-rule="evenodd"
                    d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h12a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8z"
                    clip-rule="evenodd"
                  />
                </svg>
              </div>
            </div>

            <!-- Image Thumbnails -->
            <div
              v-if="tour.image_gallery && tour.image_gallery.length > 1"
              class="grid grid-cols-4 gap-2"
            >
              <button
                v-for="(image, index) in tour.image_gallery"
                :key="index"
                @click="selectedImage = image"
                :class="[
                  'h-20 bg-gray-200 rounded-lg overflow-hidden border-2 transition-colors',
                  selectedImage === image
                    ? 'border-blue-600'
                    : 'border-transparent',
                ]"
              >
                <img
                  :src="image"
                  :alt="`Gallery ${index + 1}`"
                  class="w-full h-full object-cover"
                />
              </button>
            </div>
          </div>

          <!-- Tour Information -->
          <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">
              {{ tour.name }}
            </h1>

            <!-- Rating -->
            <div v-if="tour.total_reviews > 0" class="flex items-center mb-6">
              <div class="flex text-yellow-400 mr-2">
                <svg
                  v-for="star in 5"
                  :key="star"
                  class="w-5 h-5"
                  :class="
                    star <= Math.round(tour.average_rating || 0)
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
              <span class="font-semibold text-gray-900">{{
                tour.average_rating
                  ? Number(tour.average_rating).toFixed(1)
                  : "0.0"
              }}</span>
              <span class="ml-2 text-gray-600"
                >({{ tour.total_reviews }} reviews)</span
              >
            </div>

            <!-- Quick Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
              <div class="flex items-center">
                <svg
                  class="w-5 h-5 mr-3 text-blue-600"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                    clip-rule="evenodd"
                  />
                </svg>
                <span><strong>Duration:</strong> {{ tour.duration }}</span>
              </div>
              <div class="flex items-center">
                <svg
                  class="w-5 h-5 mr-3 text-blue-600"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                    clip-rule="evenodd"
                  />
                </svg>
                <span
                  ><strong>Departure:</strong>
                  {{ tour.departure_location }}</span
                >
              </div>
              <div class="flex items-center">
                <svg
                  class="w-5 h-5 mr-3 text-blue-600"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                    clip-rule="evenodd"
                  />
                </svg>
                <span
                  ><strong>Departure Time:</strong>
                  {{ tour.departure_time }}</span
                >
              </div>
              <div class="flex items-center">
                <svg
                  class="w-5 h-5 mr-3 text-blue-600"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"
                  />
                </svg>
                <span
                  ><strong>Max Participants:</strong>
                  {{ tour.max_participants }} people</span
                >
              </div>
            </div>

            <!-- Description -->
            <div class="mb-6">
              <h3 class="text-xl font-semibold mb-3">Description</h3>
              <p class="text-gray-700 leading-relaxed whitespace-pre-line">
                {{ tour.description }}
              </p>
            </div>

            <!-- Services -->
            <div
              v-if="tour.included_services || tour.excluded_services"
              class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6"
            >
              <!-- Included Services -->
              <div
                v-if="
                  tour.included_services && tour.included_services.length > 0
                "
              >
                <h3 class="text-lg font-semibold mb-3 text-green-600">
                  ✓ What's Included
                </h3>
                <ul class="space-y-2">
                  <li
                    v-for="service in tour.included_services"
                    :key="service"
                    class="flex items-center"
                  >
                    <svg
                      class="w-4 h-4 mr-2 text-green-600"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    {{ service }}
                  </li>
                </ul>
              </div>

              <!-- Excluded Services -->
              <div
                v-if="
                  tour.excluded_services && tour.excluded_services.length > 0
                "
              >
                <h3 class="text-lg font-semibold mb-3 text-red-600">
                  ✗ Not Included
                </h3>
                <ul class="space-y-2">
                  <li
                    v-for="service in tour.excluded_services"
                    :key="service"
                    class="flex items-center"
                  >
                    <svg
                      class="w-4 h-4 mr-2 text-red-600"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    {{ service }}
                  </li>
                </ul>
              </div>
            </div>

            <!-- Itinerary -->
            <div v-if="tour.itinerary" class="mb-6">
              <h3 class="text-xl font-semibold mb-3">Itinerary</h3>
              <p class="text-gray-700 leading-relaxed whitespace-pre-line">
                {{ tour.itinerary }}
              </p>
            </div>
          </div>

          <!-- Reviews Section -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-xl font-semibold mb-6">
              Reviews ({{ tour.total_reviews }})
            </h3>

            <!-- Add Review Form -->
            <div class="border-b pb-6 mb-6">
              <h4 class="text-lg font-medium mb-4">Write a Review</h4>
              <form @submit.prevent="submitReview" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Name *</label
                    >
                    <input
                      v-model="reviewForm.guest_name"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Email *</label
                    >
                    <input
                      v-model="reviewForm.guest_email"
                      type="email"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                      >Phone (Optional)</label
                    >
                    <input
                      v-model="reviewForm.guest_phone"
                      type="tel"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Rating *</label
                  >
                  <div class="flex space-x-1">
                    <button
                      v-for="star in 5"
                      :key="star"
                      type="button"
                      @click="reviewForm.rating = star"
                      :class="[
                        'text-2xl transition-colors',
                        star <= reviewForm.rating
                          ? 'text-yellow-400'
                          : 'text-gray-300 hover:text-yellow-300',
                      ]"
                    >
                      ★
                    </button>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Comment *</label
                  >
                  <textarea
                    v-model="reviewForm.content"
                    rows="4"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Share your experience with this boat tour..."
                  ></textarea>
                </div>

                <button
                  type="submit"
                  :disabled="submittingReview"
                  class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg transition-colors"
                >
                  {{ submittingReview ? "Submitting..." : "Submit Review" }}
                </button>
              </form>
            </div>

            <!-- Reviews List -->
            <div v-if="reviews.length > 0" class="space-y-6">
              <div
                v-for="review in reviews"
                :key="review.id"
                class="border-b border-gray-200 pb-6 last:border-b-0"
              >
                <div class="flex items-start justify-between mb-2">
                  <div>
                    <h5 class="font-medium text-gray-900">
                      {{ review.reviewer_name || review.user?.name }}
                    </h5>
                    <div class="flex text-yellow-400 mt-1">
                      <svg
                        v-for="star in 5"
                        :key="star"
                        class="w-4 h-4"
                        :class="
                          star <= review.rating
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
                  </div>
                  <span class="text-sm text-gray-500">{{
                    formatDate(review.created_at)
                  }}</span>
                </div>
                <p class="text-gray-700">{{ review.comment }}</p>
              </div>
            </div>

            <div v-else class="text-center text-gray-600 py-8">
              No reviews yet. Be the first to write a review!
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
          <!-- Booking Card -->
          <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
            <div class="text-center mb-6">
              <div class="text-3xl font-bold text-blue-600 mb-2">
                ${{ tour.price }}
                <span class="text-lg font-normal text-gray-600">/person</span>
              </div>
            </div>

            <div class="space-y-4 mb-6">
              <a
                :href="tour.whats_app_link"
                target="_blank"
                class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-3 px-4 rounded-lg transition-colors flex items-center justify-center"
              >
                <svg
                  class="w-5 h-5 mr-2"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.106"
                  />
                </svg>
                Book via WhatsApp
              </a>
            </div>

            <div class="border-t pt-4 text-sm text-gray-600">
              <p class="mb-2">Need help? Contact us:</p>
              <p class="font-medium">{{ tour.contact_whatsapp }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else class="flex items-center justify-center min-h-screen">
      <div class="text-center">
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
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">
          Boat Tour Not Found
        </h3>
        <p class="text-gray-600 mb-4">
          The requested boat tour could not be found.
        </p>
        <router-link
          to="/boat-tours"
          class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
        >
          Back to Boat Tours
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute } from "vue-router";
import api from "@/services/api";

const route = useRoute();
const tour = ref(null);
const loading = ref(false);
const reviews = ref([]);
const selectedImage = ref("");
const submittingReview = ref(false);

const reviewForm = ref({
  guest_name: "",
  guest_email: "",
  guest_phone: "",
  rating: 0,
  content: "",
});

const loadTour = async () => {
  try {
    loading.value = true;
    const response = await api.get(`/boat-tours/${route.params.id}`);

    if (response.data.success) {
      tour.value = response.data.data;
      reviews.value = response.data.data.reviews || [];

      // Set first image as selected
      if (tour.value.image_gallery && tour.value.image_gallery.length > 0) {
        selectedImage.value = tour.value.image_gallery[0];
      }

      // Generate WhatsApp link
      if (tour.value.contact_whatsapp) {
        const message = encodeURIComponent(
          `Hi! I'm interested in booking the ${tour.value.name} boat tour. Could you provide more details?`
        );
        tour.value.whats_app_link = `https://wa.me/${tour.value.contact_whatsapp}?text=${message}`;
      }
    }
  } catch (error) {
    console.error("Failed to load tour details:", error);
    tour.value = null;
  } finally {
    loading.value = false;
  }
};

const submitReview = async () => {
  try {
    submittingReview.value = true;
    console.log("Submitting review:", reviewForm.value);
    console.log("Tour ID:", route.params.id);

    const response = await api.post(
      `/boat-tours/${route.params.id}/reviews`,
      reviewForm.value
    );

    console.log("Review response:", response.data);

    if (response.data.success) {
      // Reset form
      reviewForm.value = {
        guest_name: "",
        guest_email: "",
        guest_phone: "",
        rating: 0,
        content: "",
      };

      // Refresh tour data to get updated reviews
      await loadTour();

      alert("Review submitted successfully and is pending approval!");
    }
  } catch (error) {
    console.error("Failed to submit review:", error);
    console.error("Error response:", error.response?.data);

    let errorMessage = "Failed to submit review. Please try again.";
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    } else if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat();
      errorMessage = "Validation errors:\n" + errors.join("\n");
    }

    alert(errorMessage);
  } finally {
    submittingReview.value = false;
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

onMounted(() => {
  loadTour();
});
</script>
