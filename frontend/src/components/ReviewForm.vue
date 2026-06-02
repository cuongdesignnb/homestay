<template>
  <div class="review-form-section">
    <div class="section-header">
      <div>
        <h3>{{ $t("reviews.leave_review", "Để lại đánh giá") }}</h3>
        <p class="hint">
          {{ $t("reviews.share_experience", "Chia sẻ trải nghiệm của bạn") }}
        </p>
      </div>
    </div>

    <form v-if="!submitted" class="review-form" @submit.prevent="submitReview">
      <div class="form-row">
        <label class="form-field">
          <span>{{ $t("reviews.your_name", "Họ tên") }} <em>*</em></span>
          <input
            v-model="form.guest_name"
            type="text"
            required
            :placeholder="$t('reviews.name_placeholder', 'Nhập họ tên của bạn')"
          />
        </label>
        <label class="form-field">
          <span>{{ $t("reviews.email", "Email") }}</span>
          <input
            v-model="form.guest_email"
            type="email"
            :placeholder="
              $t('reviews.email_placeholder', 'Email (không bắt buộc)')
            "
          />
        </label>
      </div>

      <div class="form-field">
        <span>{{ $t("reviews.rating", "Đánh giá") }} <em>*</em></span>
        <div class="star-rating">
          <button
            v-for="star in 5"
            :key="star"
            type="button"
            class="star-btn"
            :class="{ active: star <= form.rating, hover: star <= hoverRating }"
            @mouseenter="hoverRating = star"
            @mouseleave="hoverRating = 0"
            @click="form.rating = star"
          >
            ★
          </button>
          <span class="rating-text">{{ ratingText }}</span>
        </div>
      </div>

      <div class="form-field">
        <span>{{ $t("reviews.content", "Nội dung đánh giá") }} <em>*</em></span>
        <textarea
          v-model="form.content"
          rows="4"
          required
          minlength="10"
          :placeholder="
            $t(
              'reviews.content_placeholder',
              'Chia sẻ chi tiết trải nghiệm của bạn (ít nhất 10 ký tự)...'
            )
          "
        />
      </div>

      <div class="form-field">
        <span>{{ $t("reviews.photos", "Ảnh đính kèm") }}</span>
        <div class="image-upload">
          <label class="upload-btn">
            <input
              type="file"
              accept="image/*"
              multiple
              @change="handleImageSelect"
              :disabled="previewImages.length >= 5"
            />
            <span>📷 {{ $t("reviews.add_photos", "Thêm ảnh") }}</span>
          </label>
          <span class="upload-hint">{{
            $t("reviews.max_photos", "Tối đa 5 ảnh, mỗi ảnh không quá 5MB")
          }}</span>
        </div>

        <div v-if="previewImages.length" class="image-preview-grid">
          <div
            v-for="(img, idx) in previewImages"
            :key="idx"
            class="preview-item"
          >
            <img :src="img.url" :alt="`Preview ${idx + 1}`" />
            <button type="button" class="remove-btn" @click="removeImage(idx)">
              ×
            </button>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button
          type="submit"
          class="btn-submit"
          :disabled="submitting || !isValid"
        >
          <span v-if="submitting">{{ $t("common.loading") }}</span>
          <span v-else>{{ $t("reviews.submit", "Gửi đánh giá") }}</span>
        </button>
      </div>

      <p v-if="error" class="error-msg">{{ error }}</p>
    </form>

    <div v-else class="success-message">
      <div class="success-icon">✓</div>
      <h4>{{ $t("reviews.thank_you", "Cảm ơn bạn!") }}</h4>
      <p>
        {{
          $t(
            "reviews.pending_approval",
            "Đánh giá của bạn đã được gửi và đang chờ duyệt. Sau khi được duyệt, đánh giá sẽ hiển thị công khai."
          )
        }}
      </p>
      <button class="btn-another" @click="resetForm">
        {{ $t("reviews.write_another", "Viết đánh giá khác") }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import api from "@/services/api";

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (v) => ["room", "tour"].includes(v),
  },
  itemId: {
    type: [Number, String],
    required: true,
  },
});

const emit = defineEmits(["submitted"]);

const form = ref({
  guest_name: "",
  guest_email: "",
  rating: 5,
  content: "",
});

const imageFiles = ref([]);
const previewImages = ref([]);
const hoverRating = ref(0);
const submitting = ref(false);
const submitted = ref(false);
const error = ref("");

const ratingTexts = {
  1: "Rất tệ",
  2: "Tệ",
  3: "Bình thường",
  4: "Tốt",
  5: "Tuyệt vời",
};

const ratingText = computed(() => {
  const rating = hoverRating.value || form.value.rating;
  return ratingTexts[rating] || "";
});

const isValid = computed(() => {
  return (
    form.value.guest_name.trim() &&
    form.value.content.trim().length >= 10 &&
    form.value.rating >= 1 &&
    form.value.rating <= 5
  );
});

const handleImageSelect = (e) => {
  const files = Array.from(e.target.files);
  const remaining = 5 - previewImages.value.length;

  files.slice(0, remaining).forEach((file) => {
    if (file.size > 5 * 1024 * 1024) {
      error.value = "Ảnh không được vượt quá 5MB";
      return;
    }

    imageFiles.value.push(file);
    const reader = new FileReader();
    reader.onload = (ev) => {
      previewImages.value.push({
        url: ev.target.result,
        file,
      });
    };
    reader.readAsDataURL(file);
  });

  e.target.value = "";
};

const removeImage = (index) => {
  previewImages.value.splice(index, 1);
  imageFiles.value.splice(index, 1);
};

const submitReview = async () => {
  if (!isValid.value) return;

  error.value = "";
  submitting.value = true;

  try {
    const formData = new FormData();
    formData.append("guest_name", form.value.guest_name);
    formData.append("guest_email", form.value.guest_email || "");
    formData.append("rating", form.value.rating);
    formData.append("content", form.value.content);

    imageFiles.value.forEach((file, idx) => {
      formData.append(`images[${idx}]`, file);
    });

    const endpoint =
      props.type === "room"
        ? `/rooms/${props.itemId}/guest-reviews`
        : `/tours/${props.itemId}/guest-reviews`;

    await api.post(endpoint, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    submitted.value = true;
    emit("submitted");
  } catch (err) {
    error.value =
      err.response?.data?.message ||
      "Không thể gửi đánh giá. Vui lòng thử lại.";
  } finally {
    submitting.value = false;
  }
};

const resetForm = () => {
  form.value = {
    guest_name: "",
    guest_email: "",
    rating: 5,
    content: "",
  };
  imageFiles.value = [];
  previewImages.value = [];
  submitted.value = false;
  error.value = "";
};
</script>

<style scoped>
.review-form-section {
  background: white;
  border-radius: 1.5rem;
  padding: 2rem;
  box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08);
}

.section-header {
  margin-bottom: 1.5rem;
}

.section-header h3 {
  font-size: 1.4rem;
  color: #0f172a;
  margin: 0;
}

.section-header .hint {
  color: #64748b;
  margin-top: 0.25rem;
}

.review-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-field span {
  font-weight: 600;
  color: #334155;
  font-size: 0.9rem;
}

.form-field span em {
  color: #ef4444;
  font-style: normal;
}

.form-field input,
.form-field textarea {
  padding: 0.75rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.75rem;
  font-size: 1rem;
  transition: all 0.2s ease;
  background: #f8fafc;
}

.form-field input:focus,
.form-field textarea:focus {
  outline: none;
  border-color: #6366f1;
  background: white;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.star-rating {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.star-btn {
  background: none;
  border: none;
  font-size: 2rem;
  color: #cbd5e1;
  cursor: pointer;
  padding: 0;
  transition: all 0.15s ease;
}

.star-btn.active,
.star-btn.hover {
  color: #fbbf24;
  transform: scale(1.1);
}

.rating-text {
  margin-left: 1rem;
  color: #64748b;
  font-size: 0.95rem;
}

.image-upload {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.upload-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.2rem;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  border-radius: 0.75rem;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.upload-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
}

.upload-btn input {
  display: none;
}

.upload-hint {
  color: #94a3b8;
  font-size: 0.85rem;
}

.image-preview-grid {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-top: 0.75rem;
}

.preview-item {
  position: relative;
  width: 80px;
  height: 80px;
  border-radius: 0.5rem;
  overflow: hidden;
}

.preview-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.preview-item .remove-btn {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  border: none;
  cursor: pointer;
  font-size: 14px;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.form-actions {
  margin-top: 0.5rem;
}

.btn-submit {
  padding: 0.9rem 2rem;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error-msg {
  color: #ef4444;
  background: #fef2f2;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  margin-top: 0.5rem;
}

.success-message {
  text-align: center;
  padding: 2rem;
}

.success-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #10b981, #34d399);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin: 0 auto 1rem;
}

.success-message h4 {
  font-size: 1.5rem;
  color: #0f172a;
  margin-bottom: 0.5rem;
}

.success-message p {
  color: #64748b;
  max-width: 400px;
  margin: 0 auto 1.5rem;
}

.btn-another {
  padding: 0.6rem 1.5rem;
  background: #f1f5f9;
  color: #475569;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
}

.btn-another:hover {
  background: #e2e8f0;
}
</style>
