<template>
  <Teleport to="body">
    <div v-if="modelValue" class="modal-overlay" @click.self="$emit('update:modelValue', false)">
      <div class="modal-box" role="dialog" aria-modal="true" aria-label="Thêm mục menu">

        <!-- Header -->
        <div class="modal-header">
          <div>
            <h3 class="modal-title flex items-center gap-1.5">
              <span class="material-icons-outlined text-indigo-400">{{ editMode ? 'edit' : 'add_box' }}</span>
              <span>{{ editMode ? 'Chỉnh sửa mục menu' : 'Thêm mục menu' }}</span>
            </h3>
            <p class="modal-sub">{{ parentItem ? `Submenu của: "${parentItem.label}"` : 'Menu cấp 1' }}</p>
          </div>
          <button class="close-btn" @click="$emit('update:modelValue', false)">✕</button>
        </div>

        <!-- Label input -->
        <div class="field-group">
          <label class="field-label">Nhãn hiển thị <span class="required">*</span></label>
          <div class="icon-label-row">
            <div class="icon-preview-box" :title="form.icon ? `Icon preview: ${form.icon}` : 'Chưa có icon'">
              <span v-if="form.icon" class="material-icons-outlined text-[1.2rem]">{{ form.icon }}</span>
              <span v-else class="material-icons-outlined text-[1.2rem]">image</span>
            </div>
            <input
              class="field-input icon-name-input"
              v-model="form.icon"
              placeholder="home, info..."
              title="Tên Material Icon (VD: home, info, explore...)"
            />
            <input
              class="field-input flex-1"
              v-model="form.label"
              placeholder="VD: Phòng nghỉ, Về chúng tôi..."
              @keydown.enter="tryConfirm"
            />
          </div>
        </div>

        <!-- Tab: chọn loại link -->
        <div class="tab-bar">
          <button
            class="tab-btn flex items-center gap-1"
            :class="{ active: activeTab === 'internal' }"
            @click="activeTab = 'internal'"
          >
            <span class="material-icons-outlined text-sm">link</span>
            <span>Trang có sẵn</span>
          </button>
          <button
            class="tab-btn flex items-center gap-1"
            :class="{ active: activeTab === 'external' }"
            @click="activeTab = 'external'"
          >
            <span class="material-icons-outlined text-sm">language</span>
            <span>Link tùy chỉnh</span>
          </button>
        </div>

        <!-- TAB 1: Internal routes -->
        <div v-if="activeTab === 'internal'" class="tab-content">
          <!-- Search -->
          <div class="search-wrap">
            <span class="search-icon material-icons-outlined">search</span>
            <input
              class="search-input"
              v-model="search"
              placeholder="Tìm trang..."
              ref="searchInputRef"
            />
            <button v-if="search" class="clear-search" @click="search = ''">✕</button>
          </div>

          <!-- Group filter -->
          <div class="group-filter">
            <button
              v-for="g in groups"
              :key="g.key"
              class="group-chip flex items-center gap-1"
              :class="{ active: activeGroup === g.key }"
              @click="activeGroup = g.key"
            >
              <span class="material-icons-outlined text-xs">{{ g.icon }}</span>
              <span>{{ g.label }}</span>
            </button>
          </div>

          <!-- Route list -->
          <div class="route-list" v-if="filteredRoutes.length">
            <button
              v-for="route in filteredRoutes"
              :key="route.name"
              class="route-row"
              :class="{ selected: form.route_name === route.name }"
              @click="selectRoute(route)"
            >
              <div class="route-info">
                <span class="route-label-vi">{{ route.label_vi }}</span>
                <span class="route-label-en">{{ route.label_en }}</span>
              </div>
              <code class="route-path">{{ route.path }}</code>
              <span v-if="form.route_name === route.name" class="material-icons-outlined check-mark text-emerald-400">check_circle</span>
            </button>
          </div>
          <div v-else class="empty-routes">
            Không tìm thấy route phù hợp
          </div>

          <!-- Loading state -->
          <div v-if="loadingRoutes" class="loading-routes flex items-center justify-center gap-1.5">
            <span class="material-icons-outlined animate-spin text-indigo-400">sync</span>
            <span>Đang tải danh sách trang...</span>
          </div>
        </div>

        <!-- TAB 2: External / custom URL -->
        <div v-if="activeTab === 'external'" class="tab-content">
          <div class="field-group">
            <label class="field-label">URL</label>
            <input
              class="field-input"
              v-model="form.url"
              placeholder="https://... hoặc /duong-dan-tuy-chinh"
              @keydown.enter="tryConfirm"
            />
            <p class="field-hint">Nhập đường dẫn tương đối (bắt đầu bằng /) hoặc URL đầy đủ.</p>
          </div>
          <div class="field-group">
            <label class="field-label">Mở trong</label>
            <div class="radio-group">
              <label class="radio-option">
                <input type="radio" v-model="form.target" value="_self" /> Cùng tab
              </label>
              <label class="radio-option flex items-center gap-1">
                <input type="radio" v-model="form.target" value="_blank" />
                <span>Tab mới</span>
                <span class="material-icons-outlined text-sm">open_in_new</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
          <label class="visibility-check">
            <input type="checkbox" v-model="form.is_visible" />
            Hiển thị ngay
          </label>
          <div class="footer-actions">
            <button class="btn-cancel" @click="$emit('update:modelValue', false)">Hủy</button>
            <button
              class="btn-confirm"
              :disabled="!isValid"
              @click="tryConfirm"
            >
              {{ editMode ? 'Lưu thay đổi' : 'Thêm vào menu' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import { fetchAvailableRoutes } from '@/services/navMenuService';

const props = defineProps({
  modelValue: Boolean,
  parentItem: { type: Object, default: null },
  initialData: { type: Object, default: null }, // for edit mode
});

const emit = defineEmits(['update:modelValue', 'confirm']);

// ---- Form state ----
const defaultForm = () => ({
  label: '',
  label_en: '',
  icon: '',
  url: '',
  route_name: '',
  target: '_self',
  is_visible: true,
});
const form = ref(defaultForm());
const editMode = computed(() => !!props.initialData?.id);

// ---- Tabs ----
const activeTab = ref('internal');

// ---- Route list ----
const allRoutes = ref([]);
const loadingRoutes = ref(false);
const search = ref('');
const searchInputRef = ref(null);
const activeGroup = ref('all');

const groups = [
  { key: 'all', label: 'Tất cả', icon: 'apps' },
  { key: 'public', label: 'Công khai', icon: 'lock_open' },
  { key: 'auth', label: 'Xác thực', icon: 'lock' },
  { key: 'admin', label: 'Admin', icon: 'settings' },
];

const filteredRoutes = computed(() => {
  let routes = allRoutes.value;
  if (activeGroup.value !== 'all') {
    routes = routes.filter((r) => r.group === activeGroup.value);
  }
  if (search.value.trim()) {
    const q = search.value.trim().toLowerCase();
    routes = routes.filter(
      (r) =>
        r.label_vi.toLowerCase().includes(q) ||
        r.label_en.toLowerCase().includes(q) ||
        r.path.toLowerCase().includes(q)
    );
  }
  return routes;
});

const isValid = computed(() => {
  if (!form.value.label.trim()) return false;
  if (activeTab.value === 'internal') return !!form.value.route_name || !!form.value.url;
  return !!form.value.url.trim();
});

// ---- Load routes ----
async function loadRoutes() {
  if (allRoutes.value.length > 0) return;
  loadingRoutes.value = true;
  try {
    const res = await fetchAvailableRoutes();
    allRoutes.value = res.data?.all ?? [];
  } catch {
    allRoutes.value = [];
  } finally {
    loadingRoutes.value = false;
  }
}

// ---- Select a route ----
function selectRoute(route) {
  form.value.url = route.path;
  form.value.route_name = route.name;
  if (!form.value.label) {
    form.value.label = route.label_vi;
    form.value.label_en = route.label_en;
  }
}

// ---- Confirm ----
function tryConfirm() {
  if (!isValid.value) return;
  emit('confirm', { ...form.value });
  emit('update:modelValue', false);
}

// ---- Watchers ----
watch(
  () => props.modelValue,
  async (open) => {
    if (open) {
      // Reset or populate form
      if (props.initialData) {
        form.value = { ...defaultForm(), ...props.initialData };
        activeTab.value = props.initialData.url?.startsWith('http') ? 'external' : 'internal';
      } else {
        form.value = defaultForm();
        activeTab.value = 'internal';
        search.value = '';
        activeGroup.value = 'all';
      }
      await loadRoutes();
      await nextTick();
      searchInputRef.value?.focus();
    }
  }
);
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.75);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9000;
  padding: 1rem;
  animation: fadeIn 0.15s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to   { opacity: 1; }
}

.modal-box {
  background: linear-gradient(160deg, #0f172a, #1e293b);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 1.25rem;
  width: 100%;
  max-width: 560px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 40px 80px rgba(0, 0, 0, 0.6);
  animation: slideUp 0.2s ease;
}

@keyframes slideUp {
  from { transform: translateY(16px); opacity: 0; }
  to   { transform: translateY(0);    opacity: 1; }
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 1.25rem 1.5rem 1rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.12);
}

.modal-title {
  font-size: 1.1rem;
  font-weight: 600;
  color: #f1f5f9;
  margin: 0;
}

.modal-sub {
  font-size: 0.75rem;
  color: #64748b;
  margin: 2px 0 0;
}

.close-btn {
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  font-size: 1rem;
  padding: 0.2rem 0.5rem;
  border-radius: 0.35rem;
  transition: all 0.15s;
}

.close-btn:hover { background: rgba(148, 163, 184, 0.1); color: #e2e8f0; }

/* Fields */
.field-group {
  padding: 0.75rem 1.5rem 0;
}

.field-label {
  display: block;
  font-size: 0.75rem;
  color: #94a3b8;
  font-weight: 500;
  margin-bottom: 0.35rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.required { color: #f87171; }

.icon-label-row {
  display: flex;
  gap: 0.5rem;
}

.icon-preview-box {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.5rem;
  color: #94a3b8;
  flex-shrink: 0;
}

.icon-name-input {
  width: 140px;
  flex-shrink: 0;
}

.field-input {
  width: 100%;
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.5rem;
  color: #e2e8f0;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  outline: none;
  transition: border-color 0.2s;
}

.field-input:focus, .icon-input:focus {
  border-color: rgba(99, 102, 241, 0.6);
}

.flex-1 { flex: 1; }

.field-hint {
  font-size: 0.7rem;
  color: #475569;
  margin: 0.35rem 0 0;
}

/* Tabs */
.tab-bar {
  display: flex;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem 0;
}

.tab-btn {
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.2);
  background: transparent;
  color: #64748b;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
}

.tab-btn.active {
  background: rgba(99, 102, 241, 0.2);
  border-color: rgba(99, 102, 241, 0.5);
  color: #a5b4fc;
}

/* Tab content */
.tab-content {
  flex: 1;
  overflow-y: auto;
  padding: 0.75rem 1.5rem;
}

.search-wrap {
  position: relative;
  margin-bottom: 0.75rem;
}

.search-icon {
  position: absolute;
  left: 0.6rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 0.85rem;
}

.search-input {
  width: 100%;
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.5rem;
  color: #e2e8f0;
  padding: 0.45rem 2rem 0.45rem 2rem;
  font-size: 0.875rem;
  outline: none;
  box-sizing: border-box;
}

.search-input:focus { border-color: rgba(99, 102, 241, 0.5); }

.clear-search {
  position: absolute;
  right: 0.6rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #64748b;
  cursor: pointer;
  font-size: 0.75rem;
}

.group-filter {
  display: flex;
  gap: 0.35rem;
  flex-wrap: wrap;
  margin-bottom: 0.75rem;
}

.group-chip {
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  border: 1px solid rgba(148, 163, 184, 0.18);
  background: transparent;
  color: #64748b;
  font-size: 0.72rem;
  cursor: pointer;
  transition: all 0.15s;
}

.group-chip.active {
  background: rgba(56, 189, 248, 0.15);
  border-color: rgba(56, 189, 248, 0.4);
  color: #38bdf8;
}

.route-list {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.route-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 0.75rem;
  border-radius: 0.6rem;
  border: 1px solid transparent;
  background: rgba(30, 41, 59, 0.6);
  cursor: pointer;
  text-align: left;
  transition: all 0.15s;
  width: 100%;
}

.route-row:hover {
  background: rgba(99, 102, 241, 0.1);
  border-color: rgba(99, 102, 241, 0.25);
}

.route-row.selected {
  background: rgba(99, 102, 241, 0.2);
  border-color: rgba(99, 102, 241, 0.5);
}

.route-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.route-label-vi {
  font-size: 0.85rem;
  font-weight: 500;
  color: #e2e8f0;
}

.route-label-en {
  font-size: 0.7rem;
  color: #64748b;
}

.route-path {
  font-size: 0.72rem;
  color: #38bdf8;
  background: rgba(56, 189, 248, 0.08);
  padding: 2px 6px;
  border-radius: 4px;
  font-family: monospace;
  flex-shrink: 0;
}

.check-mark {
  color: #34d399;
  font-size: 0.85rem;
  flex-shrink: 0;
}

.empty-routes, .loading-routes {
  text-align: center;
  color: #475569;
  font-size: 0.8rem;
  padding: 1.5rem;
}

/* Radio */
.radio-group {
  display: flex;
  gap: 1.5rem;
}

.radio-option {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  color: #94a3b8;
  font-size: 0.875rem;
  cursor: pointer;
}

/* Footer */
.modal-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  border-top: 1px solid rgba(148, 163, 184, 0.12);
  background: rgba(2, 6, 23, 0.3);
}

.visibility-check {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.8rem;
  color: #94a3b8;
  cursor: pointer;
}

.footer-actions {
  display: flex;
  gap: 0.6rem;
}

.btn-cancel, .btn-confirm {
  padding: 0.45rem 1.1rem;
  border-radius: 0.6rem;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-cancel {
  background: rgba(148, 163, 184, 0.1);
  color: #94a3b8;
}

.btn-cancel:hover { background: rgba(148, 163, 184, 0.2); }

.btn-confirm {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
}

.btn-confirm:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(99, 102, 241, 0.45);
}

.btn-confirm:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
</style>
