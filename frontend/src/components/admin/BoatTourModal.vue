<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 overflow-y-auto"
    @click.self="$emit('close')"
  >
    <div
      class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0"
    >
      <!-- Overlay -->
      <div
        class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
      ></div>

      <!-- Modal -->
      <div
        class="inline-block w-full max-w-4xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg"
      >
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-medium text-gray-900">
            {{ isEditing ? "Edit Boat Tour" : "Add New Boat Tour" }}
          </h3>
          <button
            @click="$emit('close')"
            class="text-gray-400 hover:text-gray-600"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="saveTour" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <div class="space-y-4">
              <h4 class="text-md font-semibold text-gray-900">
                Basic Information
              </h4>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                  >Tour Name *</label
                >
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Enter tour name"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                  >Price ($) *</label
                >
                <input
                  v-model.number="form.price"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="0.00"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                  >Duration *</label
                >
                <input
                  v-model="form.duration"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="e.g., 3 hours, Half day"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                  >Max Participants *</label
                >
                <input
                  v-model.number="form.max_participants"
                  type="number"
                  min="1"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Enter maximum number of participants"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                  >Status</label
                >
                <select
                  v-model="form.status"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>

            <!-- Location & Contact -->
            <div class="space-y-4">
              <h4 class="text-md font-semibold text-gray-900">
                Location & Contact
              </h4>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                  >Departure Location *</label
                >
                <input
                  v-model="form.departure_location"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="Enter departure location"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                  >Departure Time *</label
                >
                <input
                  v-model="form.departure_time"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="e.g., 8:00 AM, 2:00 PM"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                  >WhatsApp Contact *</label
                >
                <input
                  v-model="form.contact_whatsapp"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  placeholder="e.g., +84901234567"
                />
                <p class="text-sm text-gray-500 mt-1">
                  Include country code (e.g., +84 for Vietnam)
                </p>
              </div>
            </div>
          </div>

          <!-- Descriptions -->
          <div class="space-y-4">
            <h4 class="text-md font-semibold text-gray-900">Descriptions</h4>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Short Description *</label
              >
              <textarea
                v-model="form.short_description"
                rows="2"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Brief overview of the tour (max 500 characters)"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Full Description *</label
              >
              <textarea
                v-model="form.description"
                rows="4"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Detailed description of the tour"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Itinerary</label
              >
              <textarea
                v-model="form.itinerary"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Detailed itinerary of the tour"
              ></textarea>
            </div>
          </div>

          <!-- Services -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Included Services</label
              >
              <div class="space-y-2">
                <div
                  v-for="(service, index) in form.included_services"
                  :key="'included-' + index"
                  class="flex gap-2"
                >
                  <input
                    v-model="form.included_services[index]"
                    type="text"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Enter service"
                  />
                  <button
                    type="button"
                    @click="removeIncludedService(index)"
                    class="text-red-600 hover:text-red-800"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                      />
                    </svg>
                  </button>
                </div>
                <button
                  type="button"
                  @click="addIncludedService"
                  class="w-full px-3 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600"
                >
                  + Add Included Service
                </button>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1"
                >Excluded Services</label
              >
              <div class="space-y-2">
                <div
                  v-for="(service, index) in form.excluded_services"
                  :key="'excluded-' + index"
                  class="flex gap-2"
                >
                  <input
                    v-model="form.excluded_services[index]"
                    type="text"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Enter service"
                  />
                  <button
                    type="button"
                    @click="removeExcludedService(index)"
                    class="text-red-600 hover:text-red-800"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                      />
                    </svg>
                  </button>
                </div>
                <button
                  type="button"
                  @click="addExcludedService"
                  class="w-full px-3 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600"
                >
                  + Add Excluded Service
                </button>
              </div>
            </div>
          </div>

          <!-- Image Gallery -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Image Gallery</label
            >
            <div class="space-y-4">
              <!-- Selected Images Display -->
              <div
                v-if="form.image_gallery && form.image_gallery.length > 0"
                class="grid grid-cols-2 md:grid-cols-4 gap-3"
              >
                <div
                  v-for="(image, index) in form.image_gallery"
                  :key="'image-' + index"
                  class="relative group"
                >
                  <img
                    :src="image"
                    :alt="`Gallery image ${index + 1}`"
                    class="w-full h-24 object-cover rounded-lg border"
                  />
                  <button
                    type="button"
                    @click="removeImage(index)"
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                  >
                    <svg
                      class="w-3 h-3"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                      />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Add Images Button -->
              <div class="flex gap-2">
                <button
                  type="button"
                  @click="showMediaModal = true"
                  class="flex-1 px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 transition-colors flex items-center justify-center gap-2"
                >
                  <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                  Choose from Media Library
                </button>
              </div>

              <p class="text-sm text-gray-500">
                Select images from the media library for the boat tour gallery.
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end gap-3 pt-6 border-t">
            <button
              type="button"
              @click="$emit('close')"
              class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400"
            >
              {{
                saving ? "Saving..." : isEditing ? "Update Tour" : "Create Tour"
              }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Media Library Modal -->
    <MediaLibraryModal
      v-model="showMediaModal"
      :multiple="true"
      @select="handleMediaSelect"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import api from "@/services/api";
import MediaLibraryModal from "@/components/admin/MediaLibraryModal.vue";

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  tour: {
    type: Object,
    default: null,
  },
});

const emit = defineEmits(["close", "saved"]);

const saving = ref(false);
const showMediaModal = ref(false);

const defaultForm = {
  name: "",
  description: "",
  short_description: "",
  price: 0,
  duration: "",
  departure_location: "",
  departure_time: "",
  max_participants: 1,
  contact_whatsapp: "",
  included_services: [],
  excluded_services: [],
  itinerary: "",
  image_gallery: [],
  status: "active",
};

const form = ref({ ...defaultForm });

const isEditing = computed(() => !!props.tour);

// Watch for tour prop changes
watch(
  () => props.tour,
  (newTour) => {
    if (newTour) {
      form.value = {
        name: newTour.name || "",
        description: newTour.description || "",
        short_description: newTour.short_description || "",
        price: newTour.price || 0,
        duration: newTour.duration || "",
        departure_location: newTour.departure_location || "",
        departure_time: newTour.departure_time || "",
        max_participants: newTour.max_participants || 1,
        contact_whatsapp: newTour.contact_whatsapp || "",
        included_services: newTour.included_services || [],
        excluded_services: newTour.excluded_services || [],
        itinerary: newTour.itinerary || "",
        image_gallery: newTour.image_gallery || [],
        status: newTour.status || "active",
      };
    } else {
      form.value = { ...defaultForm };
    }
  },
  { immediate: true }
);

// Watch for show prop changes
watch(
  () => props.show,
  (show) => {
    if (!show) {
      form.value = { ...defaultForm };
    }
  }
);

const addIncludedService = () => {
  form.value.included_services.push("");
};

const removeIncludedService = (index) => {
  form.value.included_services.splice(index, 1);
};

const addExcludedService = () => {
  form.value.excluded_services.push("");
};

const removeExcludedService = (index) => {
  form.value.excluded_services.splice(index, 1);
};

const handleMediaSelect = (items) => {
  if (!form.value.image_gallery) {
    form.value.image_gallery = [];
  }

  // Add selected media URLs to the gallery
  items.forEach((item) => {
    if (item.url && !form.value.image_gallery.includes(item.url)) {
      form.value.image_gallery.push(item.url);
    }
  });

  showMediaModal.value = false;
};

const removeImage = (index) => {
  form.value.image_gallery.splice(index, 1);
};

const saveTour = async () => {
  try {
    saving.value = true;

    // Clean up empty services
    const cleanForm = {
      ...form.value,
      included_services: form.value.included_services.filter((s) => s.trim()),
      excluded_services: form.value.excluded_services.filter((s) => s.trim()),
      // image_gallery is already clean from Media Library
    };

    let response;
    if (isEditing.value) {
      response = await api.put(`/admin/boat-tours/${props.tour.id}`, cleanForm);
    } else {
      response = await api.post("/admin/boat-tours", cleanForm);
    }

    if (response.data.success) {
      emit("saved");
      alert(
        `Boat tour ${isEditing.value ? "updated" : "created"} successfully!`
      );
    }
  } catch (error) {
    console.error("Failed to save boat tour:", error);
    let errorMessage = `Failed to ${
      isEditing.value ? "update" : "create"
    } boat tour.`;

    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat();
      errorMessage += "\n\nErrors:\n" + errors.join("\n");
    }

    alert(errorMessage);
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
/* Improve placeholder text visibility */
input::placeholder,
textarea::placeholder,
select::placeholder {
  color: #6b7280 !important; /* gray-500 */
  opacity: 0.8;
}

input:focus::placeholder,
textarea:focus::placeholder {
  color: #9ca3af !important; /* gray-400 when focused */
  opacity: 0.6;
}

/* Dark mode compatibility */
@media (prefers-color-scheme: dark) {
  input::placeholder,
  textarea::placeholder,
  select::placeholder {
    color: #9ca3af !important;
    opacity: 0.9;
  }
}
</style>
