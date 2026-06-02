<template>
  <div class="admin-reviews-page">
    <header class="page-header">
      <div>
        <h1>Quản lý đánh giá</h1>
        <p class="subtitle">Duyệt và quản lý đánh giá từ khách hàng</p>
      </div>
    </header>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card pending" @click="filterStatus = 'pending'">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
          <p class="stat-value">{{ stats.pending || 0 }}</p>
          <p class="stat-label">Chờ duyệt</p>
        </div>
      </div>
      <div class="stat-card approved" @click="filterStatus = 'approved'">
        <div class="stat-icon">✓</div>
        <div class="stat-info">
          <p class="stat-value">{{ stats.approved || 0 }}</p>
          <p class="stat-label">Đã duyệt</p>
        </div>
      </div>
      <div class="stat-card rejected" @click="filterStatus = 'rejected'">
        <div class="stat-icon">✗</div>
        <div class="stat-info">
          <p class="stat-value">{{ stats.rejected || 0 }}</p>
          <p class="stat-label">Từ chối</p>
        </div>
      </div>
      <div class="stat-card total" @click="filterStatus = 'all'">
        <div class="stat-icon">📊</div>
        <div class="stat-info">
          <p class="stat-value">{{ stats.total || 0 }}</p>
          <p class="stat-label">Tổng cộng</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-bar">
      <div class="filter-group">
        <select v-model="filterStatus" @change="loadReviews">
          <option value="all">Tất cả trạng thái</option>
          <option value="pending">Chờ duyệt</option>
          <option value="approved">Đã duyệt</option>
          <option value="rejected">Từ chối</option>
        </select>
        <select v-model="filterType" @change="loadReviews">
          <option value="">Tất cả loại</option>
          <option value="room">Đánh giá phòng</option>
          <option value="tour">Đánh giá tour</option>
          <option value="boat-tour">Đánh giá boat tour</option>
        </select>
      </div>
      <div class="search-group">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Tìm theo tên, email, nội dung..."
          @keyup.enter="loadReviews"
        />
        <button class="btn-search" @click="loadReviews">🔍</button>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div v-if="selectedIds.length" class="bulk-actions">
      <span>Đã chọn {{ selectedIds.length }} đánh giá</span>
      <button class="btn btn-approve" @click="bulkAction('approve')">
        ✓ Duyệt tất cả
      </button>
      <button class="btn btn-reject" @click="bulkAction('reject')">
        ✗ Từ chối tất cả
      </button>
      <button class="btn btn-delete" @click="bulkAction('delete')">
        🗑️ Xóa tất cả
      </button>
    </div>

    <!-- Reviews Table -->
    <div class="table-container">
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Đang tải...</p>
      </div>

      <table v-else-if="reviews.length" class="reviews-table">
        <thead>
          <tr>
            <th class="col-check">
              <input
                type="checkbox"
                @change="toggleSelectAll"
                :checked="isAllSelected"
              />
            </th>
            <th>Khách hàng</th>
            <th>Đánh giá</th>
            <th>Đối tượng</th>
            <th>Trạng thái</th>
            <th>Ngày tạo</th>
            <th>Hành động</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="review in reviews"
            :key="review.id"
            :class="{ selected: selectedIds.includes(review.id) }"
          >
            <td class="col-check">
              <input type="checkbox" :value="review.id" v-model="selectedIds" />
            </td>
            <td class="col-guest">
              <div class="guest-info">
                <div class="avatar">
                  {{ review.guest_name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <p class="guest-name">{{ review.guest_name }}</p>
                  <p class="guest-email">
                    {{ review.guest_email || "Không có email" }}
                  </p>
                </div>
              </div>
            </td>
            <td class="col-review">
              <div class="review-preview">
                <div class="stars">
                  {{ "★".repeat(review.rating)
                  }}{{ "☆".repeat(5 - review.rating) }}
                </div>
                <p class="content-preview">
                  {{ review.content.substring(0, 100)
                  }}{{ review.content.length > 100 ? "..." : "" }}
                </p>
                <div v-if="review.images?.length" class="image-count">
                  📷 {{ review.images.length }} ảnh
                </div>
              </div>
            </td>
            <td class="col-target">
              <span
                class="target-badge"
                :class="getReviewableClass(review.reviewable_type)"
              >
                {{ getReviewableLabel(review.reviewable_type) }}
              </span>
              <p class="target-name">{{ review.reviewable_name }}</p>
            </td>
            <td class="col-status">
              <span class="status-badge" :class="review.status">
                {{ statusLabels[review.status] }}
              </span>
            </td>
            <td class="col-date">
              {{ formatDate(review.created_at) }}
            </td>
            <td class="col-actions">
              <div class="action-buttons">
                <button
                  v-if="review.status === 'pending'"
                  class="btn-icon approve"
                  @click="approveReview(review)"
                  title="Duyệt"
                >
                  ✓
                </button>
                <button
                  v-if="review.status === 'pending'"
                  class="btn-icon reject"
                  @click="rejectReview(review)"
                  title="Từ chối"
                >
                  ✗
                </button>
                <button
                  class="btn-icon view"
                  @click="viewReview(review)"
                  title="Xem chi tiết"
                >
                  👁️
                </button>
                <button
                  class="btn-icon delete"
                  @click="deleteReview(review)"
                  title="Xóa"
                >
                  🗑️
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else class="empty-state">
        <p>Không có đánh giá nào</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="pagination">
      <button :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
        ← Trước
      </button>
      <span>Trang {{ currentPage }} / {{ totalPages }}</span>
      <button
        :disabled="currentPage === totalPages"
        @click="goToPage(currentPage + 1)"
      >
        Sau →
      </button>
    </div>

    <!-- Review Detail Modal -->
    <teleport to="body">
      <div
        v-if="selectedReview"
        class="modal-overlay"
        @click.self="selectedReview = null"
      >
        <div class="review-modal">
          <header class="modal-header">
            <h2>Chi tiết đánh giá</h2>
            <button class="btn-close" @click="selectedReview = null">×</button>
          </header>

          <div class="modal-body">
            <div class="review-detail-grid">
              <div class="detail-section">
                <h3>Thông tin khách</h3>
                <p><strong>Họ tên:</strong> {{ selectedReview.guest_name }}</p>
                <p>
                  <strong>Email:</strong>
                  {{ selectedReview.guest_email || "Không có" }}
                </p>
                <p>
                  <strong>Điện thoại:</strong>
                  {{ selectedReview.guest_phone || "Không có" }}
                </p>
              </div>

              <div class="detail-section">
                <h3>Đánh giá</h3>
                <p class="rating-display">
                  <span class="stars-lg"
                    >{{ "★".repeat(selectedReview.rating)
                    }}{{ "☆".repeat(5 - selectedReview.rating) }}</span
                  >
                  <span class="rating-text">{{ selectedReview.rating }}/5</span>
                </p>
                <p class="content-full">{{ selectedReview.content }}</p>
              </div>

              <div v-if="selectedReview.images?.length" class="detail-section">
                <h3>Ảnh đính kèm</h3>
                <div class="images-grid">
                  <img
                    v-for="(img, idx) in selectedReview.images"
                    :key="idx"
                    :src="img"
                    @click="openFullImage(img)"
                  />
                </div>
              </div>

              <div class="detail-section">
                <h3>Thông tin hệ thống</h3>
                <p>
                  <strong>Đối tượng:</strong>
                  {{ selectedReview.reviewable_name }}
                </p>
                <p>
                  <strong>Trạng thái:</strong>
                  <span class="status-badge" :class="selectedReview.status">{{
                    statusLabels[selectedReview.status]
                  }}</span>
                </p>
                <p>
                  <strong>Ngày tạo:</strong>
                  {{ formatDateTime(selectedReview.created_at) }}
                </p>
                <p v-if="selectedReview.approved_at">
                  <strong>Ngày duyệt:</strong>
                  {{ formatDateTime(selectedReview.approved_at) }}
                </p>
                <p v-if="selectedReview.admin_note">
                  <strong>Ghi chú admin:</strong>
                  {{ selectedReview.admin_note }}
                </p>
              </div>
            </div>

            <div
              v-if="selectedReview.status === 'pending'"
              class="modal-actions"
            >
              <div class="admin-note-field">
                <label>Ghi chú (tùy chọn)</label>
                <textarea
                  v-model="adminNote"
                  rows="2"
                  placeholder="Ghi chú nội bộ..."
                ></textarea>
              </div>
              <div class="action-buttons-row">
                <button
                  class="btn btn-approve-lg"
                  @click="approveSelectedReview"
                >
                  ✓ Duyệt đánh giá
                </button>
                <button class="btn btn-reject-lg" @click="rejectSelectedReview">
                  ✗ Từ chối
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </teleport>

    <!-- Full Image Modal -->
    <teleport to="body">
      <div v-if="fullImage" class="lightbox" @click="fullImage = null">
        <img :src="fullImage" />
      </div>
    </teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import api from "@/services/api";
import dayjs from "dayjs";

const reviews = ref([]);
const stats = ref({});
const loading = ref(true);
const currentPage = ref(1);
const totalPages = ref(1);
const selectedIds = ref([]);
const selectedReview = ref(null);
const adminNote = ref("");
const fullImage = ref(null);

const filterStatus = ref("all");
const filterType = ref("");
const searchQuery = ref("");

const statusLabels = {
  pending: "Chờ duyệt",
  approved: "Đã duyệt",
  rejected: "Từ chối",
};

const isAllSelected = computed(() => {
  return (
    reviews.value.length > 0 &&
    selectedIds.value.length === reviews.value.length
  );
});

const loadStats = async () => {
  try {
    const { data } = await api.get("/admin/guest-reviews/stats");
    stats.value = data;
  } catch (error) {
    console.error("Failed to load stats:", error);
  }
};

const loadReviews = async (page = 1) => {
  loading.value = true;
  try {
    const { data } = await api.get("/admin/guest-reviews", {
      params: {
        page,
        per_page: 15,
        status: filterStatus.value !== "all" ? filterStatus.value : undefined,
        type: filterType.value || undefined,
        search: searchQuery.value || undefined,
      },
    });
    reviews.value = data.data;
    currentPage.value = data.current_page;
    totalPages.value = data.last_page;
    selectedIds.value = [];
  } catch (error) {
    console.error("Failed to load reviews:", error);
  } finally {
    loading.value = false;
  }
};

const goToPage = (page) => {
  loadReviews(page);
};

const toggleSelectAll = (e) => {
  if (e.target.checked) {
    selectedIds.value = reviews.value.map((r) => r.id);
  } else {
    selectedIds.value = [];
  }
};

const viewReview = (review) => {
  selectedReview.value = review;
  adminNote.value = "";
};

const approveReview = async (review) => {
  if (!confirm("Duyệt đánh giá này?")) return;
  try {
    await api.post(`/admin/guest-reviews/${review.id}/approve`);
    await loadReviews(currentPage.value);
    await loadStats();
  } catch (error) {
    alert("Không thể duyệt đánh giá");
  }
};

const rejectReview = async (review) => {
  if (!confirm("Từ chối đánh giá này?")) return;
  try {
    await api.post(`/admin/guest-reviews/${review.id}/reject`);
    await loadReviews(currentPage.value);
    await loadStats();
  } catch (error) {
    alert("Không thể từ chối đánh giá");
  }
};

const deleteReview = async (review) => {
  if (!confirm("Xóa đánh giá này? Hành động không thể hoàn tác.")) return;
  try {
    await api.delete(`/admin/guest-reviews/${review.id}`);
    await loadReviews(currentPage.value);
    await loadStats();
  } catch (error) {
    alert("Không thể xóa đánh giá");
  }
};

const approveSelectedReview = async () => {
  try {
    await api.post(`/admin/guest-reviews/${selectedReview.value.id}/approve`, {
      admin_note: adminNote.value || undefined,
    });
    selectedReview.value = null;
    await loadReviews(currentPage.value);
    await loadStats();
  } catch (error) {
    alert("Không thể duyệt đánh giá");
  }
};

const rejectSelectedReview = async () => {
  try {
    await api.post(`/admin/guest-reviews/${selectedReview.value.id}/reject`, {
      admin_note: adminNote.value || undefined,
    });
    selectedReview.value = null;
    await loadReviews(currentPage.value);
    await loadStats();
  } catch (error) {
    alert("Không thể từ chối đánh giá");
  }
};

const bulkAction = async (action) => {
  const actionLabels = {
    approve: "duyệt",
    reject: "từ chối",
    delete: "xóa",
  };
  if (
    !confirm(
      `${
        actionLabels[action].charAt(0).toUpperCase() +
        actionLabels[action].slice(1)
      } ${selectedIds.value.length} đánh giá đã chọn?`
    )
  )
    return;

  try {
    await api.post("/admin/guest-reviews/bulk", {
      ids: selectedIds.value,
      action,
    });
    await loadReviews(currentPage.value);
    await loadStats();
  } catch (error) {
    alert("Không thể thực hiện thao tác");
  }
};

const openFullImage = (img) => {
  fullImage.value = img;
};

const formatDate = (date) => dayjs(date).format("DD/MM/YYYY");
const formatDateTime = (date) => dayjs(date).format("DD/MM/YYYY HH:mm");

// Helper functions for reviewable types
const getReviewableLabel = (type) => {
  if (type.includes("Room")) return "🏠 Phòng";
  if (type.includes("Tour") && !type.includes("BoatTour")) return "🏔️ Tour";
  if (type.includes("BoatTour")) return "🚤 Boat Tour";
  return "❓ Khác";
};

const getReviewableClass = (type) => {
  if (type.includes("Room")) return "room";
  if (type.includes("Tour") && !type.includes("BoatTour")) return "tour";
  if (type.includes("BoatTour")) return "boat-tour";
  return "other";
};

onMounted(() => {
  loadStats();
  loadReviews();
});
</script>

<style scoped>
.admin-reviews-page {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h1 {
  font-size: 1.75rem;
  color: #0f172a;
  margin: 0;
}

.subtitle {
  color: #64748b;
  margin-top: 0.25rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: white;
  border-radius: 1rem;
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 2px solid transparent;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.1);
}

.stat-card.pending {
  border-color: #fbbf24;
}
.stat-card.approved {
  border-color: #10b981;
}
.stat-card.rejected {
  border-color: #ef4444;
}
.stat-card.total {
  border-color: #6366f1;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-card.pending .stat-icon {
  background: rgba(251, 191, 36, 0.2);
}
.stat-card.approved .stat-icon {
  background: rgba(16, 185, 129, 0.2);
}
.stat-card.rejected .stat-icon {
  background: rgba(239, 68, 68, 0.2);
}
.stat-card.total .stat-icon {
  background: rgba(99, 102, 241, 0.2);
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.stat-label {
  color: #64748b;
  margin: 0;
}

.filters-bar {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  gap: 0.75rem;
}

.filter-group select {
  padding: 0.6rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  background: white;
  min-width: 160px;
}

.search-group {
  display: flex;
  gap: 0.5rem;
}

.search-group input {
  padding: 0.6rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  min-width: 250px;
}

.btn-search {
  padding: 0.6rem 1rem;
  background: #6366f1;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
}

.bulk-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f1f5f9;
  border-radius: 0.75rem;
  margin-bottom: 1rem;
}

.bulk-actions span {
  font-weight: 600;
  color: #475569;
}

.bulk-actions .btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-weight: 500;
}

.btn-approve {
  background: #10b981;
  color: white;
}
.btn-reject {
  background: #f59e0b;
  color: white;
}
.btn-delete {
  background: #ef4444;
  color: white;
}

.table-container {
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
}

.reviews-table {
  width: 100%;
  border-collapse: collapse;
}

.reviews-table th,
.reviews-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #f1f5f9;
}

.reviews-table th {
  background: #f8fafc;
  font-weight: 600;
  color: #475569;
}

.reviews-table tr:hover {
  background: #fafbfc;
}

.reviews-table tr.selected {
  background: rgba(99, 102, 241, 0.05);
}

.col-check {
  width: 40px;
}

.guest-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

.guest-name {
  font-weight: 600;
  color: #0f172a;
  margin: 0;
}

.guest-email {
  font-size: 0.85rem;
  color: #94a3b8;
  margin: 0;
}

.review-preview .stars {
  color: #fbbf24;
  margin-bottom: 0.25rem;
}

.content-preview {
  font-size: 0.9rem;
  color: #475569;
  margin: 0;
}

.image-count {
  font-size: 0.8rem;
  color: #6366f1;
  margin-top: 0.25rem;
}

.target-badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 0.25rem;
  font-size: 0.8rem;
  margin-bottom: 0.25rem;
}

.target-badge.room {
  background: rgba(59, 130, 246, 0.1);
  color: #2563eb;
}

.target-badge.tour {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.target-badge.boat-tour {
  background: rgba(99, 102, 241, 0.1);
  color: #6366f1;
}

.target-name {
  font-size: 0.9rem;
  color: #475569;
  margin: 0;
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 500;
}

.status-badge.pending {
  background: rgba(251, 191, 36, 0.2);
  color: #b45309;
}

.status-badge.approved {
  background: rgba(16, 185, 129, 0.2);
  color: #047857;
}

.status-badge.rejected {
  background: rgba(239, 68, 68, 0.2);
  color: #b91c1c;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.2s ease;
}

.btn-icon.approve {
  background: rgba(16, 185, 129, 0.1);
  color: #059669;
}

.btn-icon.reject {
  background: rgba(251, 191, 36, 0.1);
  color: #b45309;
}

.btn-icon.view {
  background: rgba(99, 102, 241, 0.1);
  color: #4f46e5;
}

.btn-icon.delete {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.btn-icon:hover {
  transform: scale(1.1);
}

.loading-state,
.empty-state {
  padding: 3rem;
  text-align: center;
  color: #64748b;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e2e8f0;
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1.5rem;
}

.pagination button {
  padding: 0.5rem 1rem;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 0.5rem;
  cursor: pointer;
}

.pagination button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.review-modal {
  background: white;
  border-radius: 1rem;
  width: 100%;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
}

.btn-close {
  width: 36px;
  height: 36px;
  border: none;
  background: #f1f5f9;
  border-radius: 50%;
  font-size: 1.5rem;
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
}

.review-detail-grid {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.detail-section h3 {
  font-size: 1rem;
  color: #64748b;
  margin: 0 0 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.detail-section p {
  margin: 0.5rem 0;
  color: #334155;
}

.rating-display {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.stars-lg {
  font-size: 1.5rem;
  color: #fbbf24;
}

.rating-text {
  font-weight: 600;
  color: #475569;
}

.content-full {
  line-height: 1.7;
  color: #475569;
}

.images-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 0.75rem;
}

.images-grid img {
  width: 100%;
  height: 100px;
  object-fit: cover;
  border-radius: 0.5rem;
  cursor: pointer;
}

.modal-actions {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.admin-note-field {
  margin-bottom: 1rem;
}

.admin-note-field label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #475569;
}

.admin-note-field textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  resize: vertical;
}

.action-buttons-row {
  display: flex;
  gap: 1rem;
}

.btn-approve-lg,
.btn-reject-lg {
  flex: 1;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.5rem;
  font-weight: 600;
  cursor: pointer;
}

.btn-approve-lg {
  background: #10b981;
  color: white;
}

.btn-reject-lg {
  background: #f59e0b;
  color: white;
}

/* Lightbox */
.lightbox {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  cursor: zoom-out;
}

.lightbox img {
  max-width: 90vw;
  max-height: 90vh;
  object-fit: contain;
}
</style>
