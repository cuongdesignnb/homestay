<template>
  <div class="nav-manager">
    <!-- Left panel: Menu list -->
    <aside class="panel-left">
      <div class="panel-header">
        <h2 class="panel-title flex items-center gap-1.5">
          <span class="material-icons-outlined text-slate-400 text-lg">folder</span>
          <span>Bộ menu</span>
        </h2>
        <button class="btn-add" @click="openCreateMenu">+ Tạo mới</button>
      </div>

      <div v-if="loadingMenus" class="panel-loading flex items-center justify-center gap-1.5">
        <span class="material-icons-outlined animate-spin text-indigo-400 text-lg">sync</span>
        <span>Đang tải...</span>
      </div>
      <div v-else-if="menus.length === 0" class="panel-empty">
        Chưa có bộ menu nào.<br />Hãy tạo menu đầu tiên!
      </div>

      <div v-else class="menu-list">
        <button
          v-for="menu in menus"
          :key="menu.id"
          class="menu-entry"
          :class="{ active: selectedMenuId === menu.id }"
          @click="selectMenu(menu.id)"
        >
          <div class="menu-entry-info">
            <span class="menu-entry-label">{{ menu.label }}</span>
            <code class="menu-entry-name">{{ menu.name }}</code>
          </div>
          <div class="menu-entry-meta">
            <span class="count-badge">{{ menu.items_count }} mục</span>
            <span
              class="status-dot"
              :class="menu.is_active ? 'active' : 'inactive'"
              :title="menu.is_active ? 'Đang hoạt động' : 'Tắt'"
            ></span>
          </div>
        </button>
      </div>
    </aside>

    <!-- Right panel: Tree editor -->
    <section class="panel-right">
      <!-- No menu selected -->
      <div v-if="!selectedMenuId" class="empty-state">
        <span class="material-icons-outlined text-slate-500 text-5xl mb-2">folder_open</span>
        <p class="empty-title">Chọn một bộ menu để chỉnh sửa</p>
        <p class="empty-sub">hoặc tạo bộ menu mới từ danh sách bên trái</p>
      </div>

      <!-- Editor -->
      <template v-else>
        <div class="editor-header">
          <div>
            <h2 class="editor-title">
              {{ currentMenu?.label }}
              <span class="editor-name-badge">{{ currentMenu?.name }}</span>
            </h2>
            <p class="editor-sub">Kéo thả để sắp xếp · Nhấp đúp label để đổi tên</p>
          </div>
          <div class="editor-actions">
            <button class="btn-outline flex items-center gap-1" @click="openRoutePickerForRoot">
              <span class="material-icons-outlined text-base">add</span>
              <span>Thêm mục</span>
            </button>
            <button
              class="btn-save flex items-center gap-1"
              :disabled="saving || !isDirty"
              @click="saveTree"
            >
              <span v-if="saving" class="flex items-center gap-1">
                <span class="material-icons-outlined animate-spin text-base">sync</span>
                <span>Đang lưu...</span>
              </span>
              <span v-else class="flex items-center gap-1">
                <span class="material-icons-outlined text-base">save</span>
                <span>Lưu cấu trúc</span>
              </span>
            </button>
            <button class="btn-danger-sm flex items-center gap-1" @click="confirmDeleteMenu">
              <span class="material-icons-outlined text-base">delete</span>
              <span>Xóa menu</span>
            </button>
          </div>
        </div>

        <!-- Dirty indicator -->
        <div v-if="isDirty" class="dirty-bar flex items-center justify-center gap-1.5">
          <span class="material-icons-outlined text-amber-500 text-base">warning</span>
          <span>Có thay đổi chưa lưu — nhấn "Lưu cấu trúc" để áp dụng</span>
        </div>

        <!-- Tree drag & drop -->
        <div v-if="loadingTree" class="panel-loading flex items-center justify-center gap-1.5">
          <span class="material-icons-outlined animate-spin text-indigo-400 text-lg">sync</span>
          <span>Đang tải cấu trúc...</span>
        </div>
        <div v-else class="tree-container">
          <VueDraggable
            v-model="treeItems"
            :group="{ name: 'menu-items', pull: true, put: true }"
            handle=".drag-handle"
            ghost-class="drag-ghost"
            chosen-class="drag-chosen"
            :animation="200"
            @update="markDirty"
            @add="markDirty"
            class="root-list"
          >
            <MenuItemCard
              v-for="element in treeItems"
              :key="element.id"
              :item="element"
              :depth="0"
              :editing-id="editingId"
              @update:item="onItemUpdate"
              @delete-item="deleteItem"
              @edit-item="openEditItem"
              @add-child="openRoutePickerForChild"
              @toggle-visibility="toggleVisibility"
              @toggle-target="toggleTarget"
              @label-updated="handleLabelUpdate"
            />
          </VueDraggable>
          <div v-if="treeItems.length === 0 && !loadingTree" class="empty-tree">
            <span class="material-icons-outlined text-slate-500 text-3xl mb-1">inbox</span>
            <p>Chưa có mục nào</p>
            <button class="btn-add-first flex items-center gap-1" @click="openRoutePickerForRoot">
              <span class="material-icons-outlined text-base">add</span>
              <span>Thêm mục đầu tiên</span>
            </button>
          </div>
        </div>
      </template>
    </section>

    <!-- Route Picker Modal -->
    <RoutePickerModal
      v-model="showRoutePicker"
      :parent-item="pickerParent"
      :initial-data="editingItem"
      @confirm="onPickerConfirm"
    />

    <!-- Create Menu Modal -->
    <Teleport to="body">
      <div v-if="showCreateMenu" class="modal-overlay" @click.self="showCreateMenu = false">
        <div class="create-modal">
          <h3 class="create-modal-title flex items-center gap-1.5">
            <span class="material-icons-outlined text-indigo-400 text-xl">add_circle_outline</span>
            <span>Tạo bộ menu mới</span>
          </h3>
          <div class="field-group">
            <label class="field-label">Nhãn <span class="required">*</span></label>
            <input
              class="field-input"
              v-model="newMenu.label"
              placeholder="VD: Menu chính, Menu chân trang..."
              @keydown.enter="createMenu"
            />
          </div>
          <div class="field-group">
            <label class="field-label">Tên định danh <span class="required">*</span></label>
            <input
              class="field-input mono"
              v-model="newMenu.name"
              placeholder="VD: header, footer, mobile..."
              @keydown.enter="createMenu"
            />
            <p class="field-hint">Chỉ dùng chữ thường, số và dấu gạch ngang. Dùng để gọi API: /api/nav-menus/<strong>{{ newMenu.name || 'ten' }}</strong></p>
          </div>
          <div class="create-modal-footer">
            <button class="btn-cancel" @click="showCreateMenu = false">Hủy</button>
            <button
              class="btn-confirm"
              :disabled="!newMenu.label || !newMenu.name"
              @click="createMenu"
            >
              Tạo menu
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Confirm delete dialog -->
    <Teleport to="body">
      <div v-if="showConfirmDelete" class="modal-overlay" @click.self="showConfirmDelete = false">
        <div class="confirm-modal">
          <div class="confirm-text flex items-start gap-2">
            <span class="material-icons-outlined text-rose-500 text-xl flex-shrink-0">delete_forever</span>
            <div>
              <span>Xóa bộ menu <strong>"{{ currentMenu?.label }}"</strong>?</span><br />
              <small class="text-slate-400">Tất cả {{ treeItems.length }} mục sẽ bị xóa vĩnh viễn.</small>
            </div>
          </div>
          <div class="confirm-actions">
            <button class="btn-cancel" @click="showConfirmDelete = false">Hủy</button>
            <button class="btn-danger" @click="deleteMenu">Xóa</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Toast notification -->
    <Teleport to="body">
      <div v-if="toast.show" class="toast" :class="toast.type">
        {{ toast.message }}
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';
import MenuItemCard from '@/components/admin/MenuItemCard.vue';
import RoutePickerModal from '@/components/admin/RoutePickerModal.vue';
import {
  fetchNavMenus,
  fetchNavMenu,
  createNavMenu,
  deleteNavMenu,
  saveMenuTree,
} from '@/services/navMenuService';

// ============================================================
// STATE
// ============================================================
const menus = ref([]);
const loadingMenus = ref(false);
const selectedMenuId = ref(null);
const currentMenu = ref(null);
const treeItems = ref([]);
const loadingTree = ref(false);
const saving = ref(false);
const isDirty = ref(false);
const editingId = ref(null);

const showRoutePicker = ref(false);
const pickerParent = ref(null);
const editingItem = ref(null);

const showCreateMenu = ref(false);
const showConfirmDelete = ref(false);
const newMenu = ref({ name: '', label: '' });

const toast = ref({ show: false, message: '', type: 'success' });

// ============================================================
// LOAD MENUS
// ============================================================
async function loadMenus() {
  loadingMenus.value = true;
  try {
    const res = await fetchNavMenus();
    menus.value = res.data?.data ?? [];
  } catch {
    showToast('Không thể tải danh sách menu', 'error');
  } finally {
    loadingMenus.value = false;
  }
}

async function selectMenu(id) {
  if (isDirty.value && selectedMenuId.value !== id) {
    const ok = confirm('Có thay đổi chưa lưu. Bỏ qua và chuyển menu?');
    if (!ok) return;
  }
  selectedMenuId.value = id;
  currentMenu.value = menus.value.find((m) => m.id === id);
  await loadTree(id);
}

async function loadTree(id) {
  loadingTree.value = true;
  isDirty.value = false;
  try {
    const res = await fetchNavMenu(id);
    treeItems.value = res.data?.data?.items ?? [];
  } catch {
    showToast('Không thể tải cấu trúc menu', 'error');
  } finally {
    loadingTree.value = false;
  }
}

// ============================================================
// CREATE / DELETE MENU
// ============================================================
function openCreateMenu() {
  newMenu.value = { name: '', label: '' };
  showCreateMenu.value = true;
}

async function createMenu() {
  if (!newMenu.value.label || !newMenu.value.name) return;
  try {
    await createNavMenu({ ...newMenu.value, is_active: true });
    showCreateMenu.value = false;
    showToast('Đã tạo bộ menu!');
    await loadMenus();
  } catch (e) {
    showToast(e?.response?.data?.message || 'Tạo menu thất bại', 'error');
  }
}

function confirmDeleteMenu() {
  showConfirmDelete.value = true;
}

async function deleteMenu() {
  try {
    await deleteNavMenu(selectedMenuId.value);
    showConfirmDelete.value = false;
    selectedMenuId.value = null;
    currentMenu.value = null;
    treeItems.value = [];
    isDirty.value = false;
    showToast('Đã xóa bộ menu');
    await loadMenus();
  } catch {
    showToast('Xóa menu thất bại', 'error');
  }
}

// ============================================================
// TREE MANIPULATION
// ============================================================
function markDirty() {
  isDirty.value = true;
}

function onItemUpdate(updatedItem) {
  const idx = treeItems.value.findIndex((i) => i.id === updatedItem.id);
  if (idx !== -1) treeItems.value[idx] = updatedItem;
  markDirty();
}

function handleLabelUpdate({ id, label, editing }) {
  if (editing === true) {
    editingId.value = id;
    return;
  }
  editingId.value = null;
  if (label !== undefined) {
    // Update recursively
    updateLabelInTree(treeItems.value, id, label);
    markDirty();
  }
}

function updateLabelInTree(items, id, label) {
  for (const item of items) {
    if (item.id === id) { item.label = label; return; }
    if (item.children?.length) updateLabelInTree(item.children, id, label);
  }
}

function toggleVisibility(item) {
  item.is_visible = !item.is_visible;
  markDirty();
}

function toggleTarget(item) {
  item.target = item.target === '_blank' ? '_self' : '_blank';
  markDirty();
}

function deleteItem(item) {
  if (!confirm(`Xóa mục "${item.label}"?`)) return;
  removeFromTree(treeItems.value, item.id);
  markDirty();
}

function removeFromTree(items, id) {
  const idx = items.findIndex((i) => i.id === id);
  if (idx !== -1) { items.splice(idx, 1); return; }
  for (const item of items) {
    if (item.children?.length) removeFromTree(item.children, id);
  }
}

// ============================================================
// ROUTE PICKER
// ============================================================
function openRoutePickerForRoot() {
  pickerParent.value = null;
  editingItem.value = null;
  showRoutePicker.value = true;
}

function openRoutePickerForChild(parentItem) {
  pickerParent.value = parentItem;
  editingItem.value = null;
  showRoutePicker.value = true;
}

function openEditItem(item) {
  editingItem.value = { ...item };
  pickerParent.value = null;
  showRoutePicker.value = true;
}

function onPickerConfirm(formData) {
  if (editingItem.value?.id) {
    // Edit mode — find and update
    updateItemInTree(treeItems.value, editingItem.value.id, formData);
  } else {
    // Add new
    const newItem = {
      id: Date.now(), // temporary ID until saved
      parent_id: pickerParent.value?.id ?? null,
      label: formData.label,
      label_en: formData.label_en || '',
      url: formData.url || '',
      route_name: formData.route_name || '',
      icon: formData.icon || '',
      target: formData.target || '_self',
      is_visible: formData.is_visible ?? true,
      sort_order: 0,
      children: [],
    };

    if (pickerParent.value) {
      const parent = findInTree(treeItems.value, pickerParent.value.id);
      if (parent) {
        parent.children = parent.children || [];
        newItem.sort_order = parent.children.length;
        parent.children.push(newItem);
      }
    } else {
      newItem.sort_order = treeItems.value.length;
      treeItems.value.push(newItem);
    }
  }
  markDirty();
  editingItem.value = null;
  pickerParent.value = null;
}

function findInTree(items, id) {
  for (const item of items) {
    if (item.id === id) return item;
    const found = item.children?.length ? findInTree(item.children, id) : null;
    if (found) return found;
  }
  return null;
}

function updateItemInTree(items, id, formData) {
  for (const item of items) {
    if (item.id === id) {
      Object.assign(item, formData);
      return;
    }
    if (item.children?.length) updateItemInTree(item.children, id, formData);
  }
}

// ============================================================
// SAVE TREE
// ============================================================
async function saveTree() {
  saving.value = true;
  try {
    // Flatten tree to sorted list with parent_id + sort_order
    const flat = [];
    flattenTree(treeItems.value, null, flat);
    await saveMenuTree(selectedMenuId.value, flat);
    isDirty.value = false;
    showToast('✅ Đã lưu cấu trúc menu!');
    // Reload to get real IDs from server (temp IDs replaced)
    await loadTree(selectedMenuId.value);
    await loadMenus();
  } catch (e) {
    showToast(e?.response?.data?.message || 'Lưu thất bại', 'error');
  } finally {
    saving.value = false;
  }
}

function flattenTree(items, parentId, result, startOrder = 0) {
  items.forEach((item, idx) => {
    result.push({
      id: item.id,
      parent_id: parentId,
      sort_order: startOrder + idx,
      label: item.label,
      label_en: item.label_en,
      url: item.url,
      route_name: item.route_name,
      icon: item.icon,
      target: item.target,
      is_visible: item.is_visible,
    });
    if (item.children?.length) {
      flattenTree(item.children, item.id, result, 0);
    }
  });
}

// ============================================================
// TOAST
// ============================================================
function showToast(message, type = 'success') {
  toast.value = { show: true, message, type };
  setTimeout(() => { toast.value.show = false; }, 3000);
}

// ============================================================
// INIT
// ============================================================
loadMenus();
</script>

<style scoped>
.nav-manager {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 1.5rem;
  height: calc(100vh - 160px);
  min-height: 500px;
}

/* ---- Left panel ---- */
.panel-left {
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(148, 163, 184, 0.15);
  border-radius: 1rem;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.12);
}

.panel-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: #e2e8f0;
  margin: 0;
}

.btn-add {
  padding: 0.3rem 0.75rem;
  border-radius: 0.5rem;
  border: 1px solid rgba(99, 102, 241, 0.4);
  background: rgba(99, 102, 241, 0.15);
  color: #a5b4fc;
  font-size: 0.78rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-add:hover {
  background: rgba(99, 102, 241, 0.28);
}

.panel-loading, .panel-empty {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  color: #475569;
  font-size: 0.85rem;
  padding: 1rem;
  line-height: 1.6;
}

.menu-list {
  flex: 1;
  overflow-y: auto;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.menu-entry {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  padding: 0.65rem 0.85rem;
  border-radius: 0.7rem;
  border: 1px solid transparent;
  background: rgba(30, 41, 59, 0.5);
  cursor: pointer;
  text-align: left;
  width: 100%;
  transition: all 0.2s;
}

.menu-entry:hover { border-color: rgba(148, 163, 184, 0.2); }
.menu-entry.active {
  background: rgba(99, 102, 241, 0.18);
  border-color: rgba(99, 102, 241, 0.45);
}

.menu-entry-info { display: flex; flex-direction: column; min-width: 0; }
.menu-entry-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #e2e8f0;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.menu-entry-name {
  font-size: 0.68rem;
  color: #38bdf8;
  font-family: monospace;
}

.menu-entry-meta { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
.count-badge {
  font-size: 0.68rem;
  color: #64748b;
  background: rgba(100, 116, 139, 0.12);
  padding: 1px 5px;
  border-radius: 4px;
}
.status-dot {
  width: 7px; height: 7px; border-radius: 50%;
}
.status-dot.active { background: #34d399; box-shadow: 0 0 4px #34d399; }
.status-dot.inactive { background: #475569; }

/* ---- Right panel ---- */
.panel-right {
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(148, 163, 184, 0.15);
  border-radius: 1rem;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.empty-state {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: #475569;
}
.empty-icon { font-size: 3rem; }
.empty-title { font-size: 1rem; font-weight: 500; color: #64748b; }
.empty-sub { font-size: 0.8rem; }

.editor-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.12);
  flex-wrap: wrap;
}

.editor-title {
  font-size: 1.05rem;
  font-weight: 600;
  color: #f1f5f9;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.editor-name-badge {
  font-size: 0.7rem;
  font-family: monospace;
  color: #38bdf8;
  background: rgba(56, 189, 248, 0.08);
  padding: 2px 6px;
  border-radius: 4px;
  font-weight: 400;
}

.editor-sub {
  font-size: 0.72rem;
  color: #475569;
  margin: 2px 0 0;
}

.editor-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.btn-outline {
  padding: 0.4rem 0.9rem;
  border-radius: 0.55rem;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: rgba(30, 41, 59, 0.6);
  color: #94a3b8;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-outline:hover { border-color: rgba(99, 102, 241, 0.4); color: #a5b4fc; }

.btn-save {
  padding: 0.4rem 1rem;
  border-radius: 0.55rem;
  border: none;
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.btn-save:hover:not(:disabled) { transform: translateY(-1px); }
.btn-save:disabled { opacity: 0.45; cursor: not-allowed; }

.btn-danger-sm {
  padding: 0.4rem 0.75rem;
  border-radius: 0.55rem;
  border: 1px solid rgba(239, 68, 68, 0.25);
  background: rgba(239, 68, 68, 0.1);
  color: #f87171;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-danger-sm:hover { background: rgba(239, 68, 68, 0.2); }

.dirty-bar {
  background: rgba(251, 191, 36, 0.1);
  border-bottom: 1px solid rgba(251, 191, 36, 0.2);
  color: #fbbf24;
  font-size: 0.75rem;
  padding: 0.45rem 1.5rem;
  text-align: center;
}

.tree-container {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 1.25rem;
}

.root-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  min-height: 60px;
}

.empty-tree {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  padding: 2rem;
  color: #475569;
  font-size: 0.875rem;
}

.btn-add-first {
  padding: 0.5rem 1.25rem;
  border-radius: 0.6rem;
  border: 1px dashed rgba(99, 102, 241, 0.4);
  background: rgba(99, 102, 241, 0.08);
  color: #a5b4fc;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-add-first:hover { background: rgba(99, 102, 241, 0.15); }

/* Ghost/chosen for drag */
.drag-ghost { opacity: 0.25; background: rgba(99, 102, 241, 0.15) !important; }
.drag-chosen { box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.5) !important; }

/* ---- Create / Confirm modals ---- */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.75);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 8000;
  padding: 1rem;
}

.create-modal, .confirm-modal {
  background: linear-gradient(160deg, #0f172a, #1e293b);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 1rem;
  padding: 1.75rem;
  width: 100%;
  max-width: 440px;
  box-shadow: 0 30px 60px rgba(0,0,0,0.5);
}

.create-modal-title {
  font-size: 1rem;
  font-weight: 600;
  color: #f1f5f9;
  margin: 0 0 1.25rem;
}

.field-group { margin-bottom: 1rem; }
.field-label { display: block; font-size: 0.75rem; color: #94a3b8; font-weight: 500; margin-bottom: 0.35rem; text-transform: uppercase; letter-spacing: 0.04em; }
.required { color: #f87171; }
.field-input {
  width: 100%;
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 0.5rem;
  color: #e2e8f0;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.2s;
}
.field-input:focus { border-color: rgba(99, 102, 241, 0.5); }
.mono { font-family: monospace; }
.field-hint { font-size: 0.7rem; color: #475569; margin: 0.35rem 0 0; line-height: 1.5; }

.create-modal-footer, .confirm-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  margin-top: 1.25rem;
}

.confirm-text {
  color: #cbd5e1;
  font-size: 0.9rem;
  line-height: 1.6;
  margin: 0 0 1.25rem;
}

.btn-cancel, .btn-confirm, .btn-danger {
  padding: 0.45rem 1.1rem;
  border-radius: 0.55rem;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}
.btn-cancel { background: rgba(148, 163, 184, 0.1); color: #94a3b8; }
.btn-cancel:hover { background: rgba(148, 163, 184, 0.18); }
.btn-confirm {
  background: linear-gradient(135deg, #6366f1, #8b5cf6);
  color: white;
}
.btn-confirm:disabled { opacity: 0.4; cursor: not-allowed; }
.btn-danger { background: rgba(239, 68, 68, 0.85); color: white; }
.btn-danger:hover { background: rgb(239, 68, 68); }

/* ---- Toast ---- */
.toast {
  position: fixed;
  bottom: 1.5rem;
  right: 1.5rem;
  z-index: 9999;
  padding: 0.75rem 1.25rem;
  border-radius: 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
  backdrop-filter: blur(12px);
  animation: slideInRight 0.25s ease;
  box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}
.toast.success {
  background: rgba(16, 185, 129, 0.2);
  border: 1px solid rgba(16, 185, 129, 0.35);
  color: #6ee7b7;
}
.toast.error {
  background: rgba(239, 68, 68, 0.2);
  border: 1px solid rgba(239, 68, 68, 0.35);
  color: #fca5a5;
}

@keyframes slideInRight {
  from { transform: translateX(60px); opacity: 0; }
  to   { transform: translateX(0);    opacity: 1; }
}

@media (max-width: 900px) {
  .nav-manager { grid-template-columns: 1fr; height: auto; }
  .panel-left { max-height: 260px; }
}
</style>
