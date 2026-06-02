<template>
  <div class="admin-orders">
    <section class="glass-card">
      <header class="panel-head">
        <div>
          <p class="text-sm text-slate-400">{{ $t('equipment.orders_subtitle') }}</p>
          <h2 class="text-2xl font-semibold text-white">{{ $t('equipment.orders_title') }}</h2>
        </div>
        <div class="panel-actions">
          <input
            v-model="filters.search"
            type="text"
            class="filter-input"
            :placeholder="$t('equipment.search_order_placeholder')"
            @input="debouncedSearch"
          />
          <select v-model="filters.status" class="filter-select" @change="applyFilter">
            <option value="">{{ $t('common.all') }} {{ $t('common.status') }}</option>
            <option value="pending">{{ $t('equipment.pending') }}</option>
            <option value="confirmed">{{ $t('equipment.confirmed') }}</option>
            <option value="completed">{{ $t('equipment.completed') }}</option>
            <option value="cancelled">{{ $t('equipment.cancelled') }}</option>
          </select>
          <select v-model="filters.order_type" class="filter-select" @change="applyFilter">
            <option value="">{{ $t('common.all') }} {{ $t('equipment.order_type') }}</option>
            <option value="rental">{{ $t('equipment.rental') }}</option>
            <option value="purchase">{{ $t('equipment.purchase') }}</option>
          </select>
        </div>
      </header>

      <!-- Stats cards -->
      <div class="stats-row" v-if="!loading">
        <div class="stat-card pending">
          <span class="stat-icon">⏳</span>
          <div>
            <p class="stat-value">{{ statsCount('pending') }}</p>
            <p class="stat-label">{{ $t('equipment.pending') }}</p>
          </div>
        </div>
        <div class="stat-card confirmed">
          <span class="stat-icon">✅</span>
          <div>
            <p class="stat-value">{{ statsCount('confirmed') }}</p>
            <p class="stat-label">{{ $t('equipment.confirmed') }}</p>
          </div>
        </div>
        <div class="stat-card completed">
          <span class="stat-icon">🎉</span>
          <div>
            <p class="stat-value">{{ statsCount('completed') }}</p>
            <p class="stat-label">{{ $t('equipment.completed') }}</p>
          </div>
        </div>
        <div class="stat-card cancelled">
          <span class="stat-icon">❌</span>
          <div>
            <p class="stat-value">{{ statsCount('cancelled') }}</p>
            <p class="stat-label">{{ $t('equipment.cancelled') }}</p>
          </div>
        </div>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>{{ $t('equipment.order_number') }}</th>
              <th>{{ $t('equipment.customer') }}</th>
              <th>{{ $t('equipment.order_type') }}</th>
              <th>{{ $t('equipment.items') }}</th>
              <th>{{ $t('common.total') }}</th>
              <th>{{ $t('equipment.order_status') }}</th>
              <th>{{ $t('equipment.date') }}</th>
              <th>{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="8" class="text-center text-slate-400 py-6">
                {{ $t('common.loading') }}
              </td>
            </tr>
            <tr v-else-if="!orders.length">
              <td colspan="8" class="text-center text-slate-400 py-6">
                {{ $t('equipment.no_orders') }}
              </td>
            </tr>
            <tr v-for="order in orders" :key="order.id">
              <td>
                <span class="order-number">{{ order.order_number }}</span>
              </td>
              <td>
                <div>
                  <p class="font-semibold text-white">{{ order.customer_name }}</p>
                  <small class="text-slate-400">{{ order.customer_phone }}</small>
                </div>
              </td>
              <td>
                <span class="type-badge" :class="order.order_type">
                  {{ order.order_type === 'rental' ? $t('equipment.rental') : $t('equipment.purchase') }}
                </span>
              </td>
              <td>
                <span class="text-slate-300">
                  {{ (order.items || []).length }} {{ $t('equipment.items') }}
                </span>
              </td>
              <td>
                <span class="font-semibold text-white">{{ formatCurrency(order.total_amount) }}</span>
              </td>
              <td>
                <select
                  :value="order.status"
                  class="status-select"
                  :class="order.status"
                  @change="updateOrderStatus(order.id, $event.target.value)"
                >
                  <option value="pending">{{ $t('equipment.pending') }}</option>
                  <option value="confirmed">{{ $t('equipment.confirmed') }}</option>
                  <option value="completed">{{ $t('equipment.completed') }}</option>
                  <option value="cancelled">{{ $t('equipment.cancelled') }}</option>
                </select>
              </td>
              <td>
                <span class="text-slate-400 text-sm">{{ formatDate(order.created_at) }}</span>
              </td>
              <td>
                <div class="table-actions">
                  <button class="btn-text" @click="viewOrder(order)">
                    👁️
                  </button>
                  <button class="btn-text danger" @click="deleteOrder(order.id)">
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <footer class="table-footer" v-if="pagination.total > pagination.per_page">
        <button
          class="btn btn-text"
          :disabled="pagination.current_page === 1"
          @click="changePage(pagination.current_page - 1)"
        >
          ‹
        </button>
        <span>Page {{ pagination.current_page }} / {{ totalPages }}</span>
        <button
          class="btn btn-text"
          :disabled="pagination.current_page === totalPages"
          @click="changePage(pagination.current_page + 1)"
        >
          ›
        </button>
      </footer>
    </section>

    <!-- Order Detail Modal -->
    <transition name="fade-scale">
      <div v-if="detailModal" class="modal-overlay" @click.self="detailModal = false">
        <section class="modal-panel" v-if="selectedOrder">
          <header class="modal-header">
            <div>
              <p class="text-sm text-slate-400">{{ $t('equipment.order_detail') }}</p>
              <h3 class="text-xl font-semibold text-white">{{ selectedOrder.order_number }}</h3>
            </div>
            <button class="btn btn-text" @click="detailModal = false">✕</button>
          </header>

          <div class="order-info-grid">
            <div class="info-block">
              <h4 class="info-title">{{ $t('equipment.customer') }}</h4>
              <p class="text-white">{{ selectedOrder.customer_name }}</p>
              <p class="text-slate-400 text-sm">{{ selectedOrder.customer_phone }}</p>
              <p class="text-slate-400 text-sm" v-if="selectedOrder.customer_email">{{ selectedOrder.customer_email }}</p>
            </div>
            <div class="info-block">
              <h4 class="info-title">{{ $t('equipment.order_info') }}</h4>
              <p class="text-slate-300">
                <span class="type-badge" :class="selectedOrder.order_type">
                  {{ selectedOrder.order_type === 'rental' ? $t('equipment.rental') : $t('equipment.purchase') }}
                </span>
              </p>
              <p class="text-slate-400 text-sm" v-if="selectedOrder.order_type === 'rental'">
                {{ $t('equipment.rental_period') }}: {{ formatDate(selectedOrder.rental_start_date) }}
                → {{ formatDate(selectedOrder.rental_end_date) }}
                ({{ selectedOrder.rental_days }} {{ $t('equipment.days') }})
              </p>
            </div>
          </div>

          <div class="order-items-section">
            <h4 class="info-title mb-3">{{ $t('equipment.order_items') }}</h4>
            <table class="items-table">
              <thead>
                <tr>
                  <th>{{ $t('common.name') }}</th>
                  <th>{{ $t('equipment.quantity') }}</th>
                  <th>{{ $t('equipment.unit_price') }}</th>
                  <th>{{ $t('equipment.rental_days') }}</th>
                  <th>{{ $t('common.total') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, idx) in (selectedOrder.items || [])" :key="idx">
                  <td class="text-white">{{ item.name || item.name_en || '—' }}</td>
                  <td>{{ item.quantity }}</td>
                  <td>{{ formatCurrency(item.unit_price) }}</td>
                  <td>{{ item.rental_days || '—' }}</td>
                  <td class="text-white font-semibold">{{ formatCurrency(item.line_total) }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="4" class="text-right font-semibold text-slate-300">{{ $t('common.total') }}:</td>
                  <td class="text-white font-bold text-lg">{{ formatCurrency(selectedOrder.total_amount) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="order-notes" v-if="selectedOrder.notes">
            <h4 class="info-title">{{ $t('equipment.notes') }}</h4>
            <p class="text-slate-300">{{ selectedOrder.notes }}</p>
          </div>
        </section>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import api from "@/services/api";

const orders = ref([]);
const loading = ref(false);
const detailModal = ref(false);
const selectedOrder = ref(null);
const pagination = ref({
  current_page: 1,
  per_page: 15,
  total: 0,
  last_page: 1,
});
const filters = ref({
  search: "",
  status: "",
  order_type: "",
});
let searchTimeout;

const totalPages = computed(() => pagination.value.last_page || 1);

const statsCount = (status) => {
  return orders.value.filter(o => o.status === status).length;
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
    maximumFractionDigits: 0,
  }).format(Number(value || 0));
};

const formatDate = (dateStr) => {
  if (!dateStr) return "—";
  return new Date(dateStr).toLocaleDateString("vi-VN", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
  });
};

const fetchOrders = async (page = 1) => {
  loading.value = true;
  try {
    const params = new URLSearchParams();
    params.set("page", page);
    params.set("per_page", pagination.value.per_page);
    if (filters.value.search) params.set("search", filters.value.search);
    if (filters.value.status) params.set("status", filters.value.status);
    if (filters.value.order_type) params.set("order_type", filters.value.order_type);

    const { data } = await api.get(`/admin/equipment-orders?${params.toString()}`);
    orders.value = data.data || [];
    pagination.value = {
      current_page: data.current_page,
      per_page: data.per_page,
      total: data.total,
      last_page: data.last_page,
    };
  } catch {
    orders.value = [];
  } finally {
    loading.value = false;
  }
};

const applyFilter = () => fetchOrders(1);
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => fetchOrders(1), 350);
};

const changePage = (page) => {
  if (page < 1 || page > totalPages.value) return;
  fetchOrders(page);
};

const viewOrder = (order) => {
  selectedOrder.value = order;
  detailModal.value = true;
};

const updateOrderStatus = async (id, status) => {
  try {
    await api.put(`/admin/equipment-orders/${id}/status`, { status });
    fetchOrders(pagination.value.current_page || 1);
  } catch {
    // no-op
  }
};

const deleteOrder = async (id) => {
  if (!window.confirm("Delete this order?")) return;
  try {
    await api.delete(`/admin/equipment-orders/${id}`);
    fetchOrders(pagination.value.current_page || 1);
  } catch {
    // no-op
  }
};

onMounted(() => {
  fetchOrders();
});
</script>

<style scoped>
.glass-card {
  border-radius: 1.5rem;
  padding: 2rem;
  background: rgba(15, 23, 42, 0.55);
  border: 1px solid rgba(148, 163, 184, 0.15);
  backdrop-filter: blur(12px);
}

.panel-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.panel-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  align-items: center;
}

.filter-input,
.filter-select {
  padding: 0.5rem 0.85rem;
  border-radius: 1rem;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: rgba(15, 23, 42, 0.65);
  color: #e2e8f0;
  font-size: 0.875rem;
}

.filter-input::placeholder {
  color: #64748b;
}

/* Stats */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  border-radius: 1.25rem;
  border: 1px solid rgba(148, 163, 184, 0.12);
  background: rgba(15, 23, 42, 0.45);
}

.stat-card.pending { border-color: rgba(251, 191, 36, 0.3); }
.stat-card.confirmed { border-color: rgba(52, 211, 153, 0.3); }
.stat-card.completed { border-color: rgba(99, 102, 241, 0.3); }
.stat-card.cancelled { border-color: rgba(244, 63, 94, 0.3); }

.stat-icon { font-size: 1.75rem; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: #fff; }
.stat-label { font-size: 0.75rem; color: #94a3b8; }

/* Table */
.table-wrapper {
  overflow-x: auto;
  border-radius: 1rem;
  border: 1px solid rgba(148, 163, 184, 0.12);
}

table {
  width: 100%;
  border-collapse: collapse;
}

thead th {
  padding: 0.75rem 1rem;
  text-align: left;
  font-weight: 600;
  color: #94a3b8;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: rgba(15, 23, 42, 0.45);
  border-bottom: 1px solid rgba(148, 163, 184, 0.12);
}

tbody td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.07);
  font-size: 0.875rem;
}

tbody tr:hover {
  background: rgba(99, 102, 241, 0.06);
}

.order-number {
  font-family: monospace;
  color: #818cf8;
  font-weight: 600;
  font-size: 0.85rem;
}

.type-badge {
  display: inline-block;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.type-badge.rental {
  background: rgba(56, 189, 248, 0.2);
  color: #38bdf8;
}

.type-badge.purchase {
  background: rgba(168, 85, 247, 0.2);
  color: #a855f7;
}

.status-select {
  padding: 0.35rem 0.65rem;
  border-radius: 0.75rem;
  border: 1px solid rgba(148, 163, 184, 0.2);
  background: rgba(15, 23, 42, 0.6);
  color: #e2e8f0;
  font-size: 0.8rem;
  cursor: pointer;
}

.status-select.pending { border-color: rgba(251, 191, 36, 0.4); color: #fbbf24; }
.status-select.confirmed { border-color: rgba(52, 211, 153, 0.4); color: #34d399; }
.status-select.completed { border-color: rgba(99, 102, 241, 0.4); color: #818cf8; }
.status-select.cancelled { border-color: rgba(244, 63, 94, 0.4); color: #f43f5e; }

.table-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-text {
  background: none;
  border: none;
  color: #818cf8;
  cursor: pointer;
  font-size: 0.85rem;
  padding: 0.25rem 0.5rem;
  border-radius: 0.5rem;
  transition: background 0.2s;
}

.btn-text:hover {
  background: rgba(99, 102, 241, 0.1);
}

.btn-text.danger { color: #f43f5e; }
.btn-text.danger:hover { background: rgba(244, 63, 94, 0.1); }

.table-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  padding: 1rem 0 0;
  color: #94a3b8;
  font-size: 0.875rem;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(0, 0, 0, 0.65);
  backdrop-filter: blur(6px);
}

.modal-panel {
  width: min(700px, 94vw);
  max-height: 90vh;
  overflow-y: auto;
  border-radius: 1.75rem;
  padding: 2.5rem;
  background: linear-gradient(180deg, #0f172a, #020617);
  border: 1px solid rgba(148, 163, 184, 0.18);
  box-shadow: 0 35px 70px rgba(0, 0, 0, 0.6);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.order-info-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.info-block {
  padding: 1rem;
  border-radius: 1rem;
  background: rgba(148, 163, 184, 0.06);
  border: 1px solid rgba(148, 163, 184, 0.1);
}

.info-title {
  font-size: 0.7rem;
  text-transform: uppercase;
  color: #94a3b8;
  letter-spacing: 0.06em;
  margin-bottom: 0.5rem;
}

.order-items-section {
  margin-bottom: 1.5rem;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  border-radius: 1rem;
  overflow: hidden;
  border: 1px solid rgba(148, 163, 184, 0.12);
}

.items-table thead th {
  padding: 0.6rem 0.85rem;
  font-size: 0.7rem;
  text-transform: uppercase;
  color: #94a3b8;
  background: rgba(15, 23, 42, 0.45);
  text-align: left;
}

.items-table tbody td {
  padding: 0.6rem 0.85rem;
  font-size: 0.85rem;
  color: #cbd5e1;
  border-top: 1px solid rgba(148, 163, 184, 0.07);
}

.items-table tfoot td {
  padding: 0.85rem;
  border-top: 1px solid rgba(148, 163, 184, 0.15);
}

.order-notes {
  padding: 1rem;
  border-radius: 1rem;
  background: rgba(148, 163, 184, 0.06);
  border: 1px solid rgba(148, 163, 184, 0.1);
}

/* Transitions */
.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: all 0.25s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.96);
}

@media (max-width: 768px) {
  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }

  .order-info-grid {
    grid-template-columns: 1fr;
  }
}
</style>
