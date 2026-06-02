<template>
  <div class="menu-item-card" :class="{ 'has-children': item.children?.length }">
    <!-- Card header: drag handle + info + actions -->
    <div class="item-row">
      <!-- Drag handle -->
      <span class="drag-handle material-icons-outlined" title="Kéo để sắp xếp">drag_indicator</span>

      <!-- Icon & Label -->
      <div class="item-info">
        <span v-if="item.icon" class="material-icons-outlined item-icon text-slate-400">{{ item.icon }}</span>
        <div class="item-text">
          <template v-if="editingId === item.id">
            <input
              class="inline-input"
              v-model="editLabel"
              @keydown.enter="saveLabel"
              @keydown.esc="cancelEdit"
              @blur="saveLabel"
              ref="labelInput"
              placeholder="Nhãn menu..."
            />
          </template>
          <template v-else>
            <span class="item-label" @dblclick="startEdit" :title="'Nhấp đúp để sửa'">
              {{ item.label }}
            </span>
            <span v-if="item.url" class="item-url">{{ item.url }}</span>
          </template>
        </div>
      </div>

      <!-- Status & actions -->
      <div class="item-actions">
        <!-- Visibility toggle -->
        <button
          class="action-btn flex items-center justify-center"
          :class="item.is_visible ? 'vis-on' : 'vis-off'"
          @click="$emit('toggle-visibility', item)"
          :title="item.is_visible ? 'Đang hiển thị (nhấn để ẩn)' : 'Đang ẩn (nhấn để hiển thị)'"
        >
          <span class="material-icons-outlined text-base">{{ item.is_visible ? 'visibility' : 'visibility_off' }}</span>
        </button>

        <!-- Target toggle -->
        <button
          class="action-btn target-btn flex items-center justify-center"
          @click="$emit('toggle-target', item)"
          :title="item.target === '_blank' ? 'Mở tab mới' : 'Mở cùng tab'"
        >
          <span class="material-icons-outlined text-base">{{ item.target === '_blank' ? 'open_in_new' : 'arrow_forward' }}</span>
        </button>

        <!-- Add child -->
        <button
          v-if="depth < 2"
          class="action-btn add-child-btn flex items-center justify-center"
          @click="$emit('add-child', item)"
          title="Thêm submenu"
        >
          <span class="material-icons-outlined text-base">add</span>
        </button>

        <!-- Edit link -->
        <button
          class="action-btn edit-btn flex items-center justify-center"
          @click="$emit('edit-item', item)"
          title="Chỉnh sửa link"
        >
          <span class="material-icons-outlined text-base">edit</span>
        </button>

        <!-- Delete -->
        <button
          class="action-btn delete-btn flex items-center justify-center"
          @click="$emit('delete-item', item)"
          title="Xóa"
        >
          <span class="material-icons-outlined text-base">delete</span>
        </button>
      </div>
    </div>

    <!-- Nested children with draggable -->
    <div v-if="item.children?.length > 0 || allowDrop" class="children-wrap">
      <VueDraggable
        v-model="localChildren"
        :group="{ name: 'menu-items', pull: true, put: true }"
        handle=".drag-handle"
        ghost-class="drag-ghost"
        chosen-class="drag-chosen"
        :animation="180"
        @update="onChildChange"
        @add="onChildChange"
        class="children-list"
        :class="{ 'drop-zone': localChildren.length === 0 }"
      >
        <MenuItemCard
          v-for="element in localChildren"
          :key="element.id"
          :item="element"
          :depth="depth + 1"
          :editing-id="editingId"
          @update:item="bubbleUpdate"
          @delete-item="$emit('delete-item', $event)"
          @edit-item="$emit('edit-item', $event)"
          @add-child="$emit('add-child', $event)"
          @toggle-visibility="$emit('toggle-visibility', $event)"
          @toggle-target="$emit('toggle-target', $event)"
          @label-updated="$emit('label-updated', $event)"
        />
      </VueDraggable>
      <div v-if="localChildren.length === 0" class="drop-hint">Kéo thả vào đây để tạo submenu</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue';
import { VueDraggable } from 'vue-draggable-plus';

const props = defineProps({
  item: { type: Object, required: true },
  depth: { type: Number, default: 0 },
  editingId: { type: [Number, null], default: null },
  allowDrop: { type: Boolean, default: false },
});

const emit = defineEmits([
  'update:item',
  'delete-item',
  'edit-item',
  'add-child',
  'toggle-visibility',
  'toggle-target',
  'label-updated',
]);

// Local children for two-way binding with VueDraggable
const localChildren = ref([...(props.item.children || [])]);
watch(() => props.item.children, (val) => {
  localChildren.value = [...(val || [])];
}, { deep: true });

// Inline label editing
const editLabel = ref('');
const labelInput = ref(null);

function startEdit() {
  editLabel.value = props.item.label;
  emit('label-updated', { id: props.item.id, editing: true });
  nextTick(() => labelInput.value?.focus());
}

function saveLabel() {
  if (editLabel.value.trim()) {
    emit('label-updated', { id: props.item.id, label: editLabel.value.trim(), editing: false });
  } else {
    cancelEdit();
  }
}

function cancelEdit() {
  emit('label-updated', { id: props.item.id, editing: false });
}

function onChildChange() {
  emit('update:item', { ...props.item, children: localChildren.value });
}

function bubbleUpdate(updatedChild) {
  const idx = localChildren.value.findIndex((c) => c.id === updatedChild.id);
  if (idx !== -1) localChildren.value[idx] = updatedChild;
  emit('update:item', { ...props.item, children: localChildren.value });
}
</script>

<style scoped>
.menu-item-card {
  background: rgba(30, 41, 59, 0.7);
  border: 1px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.75rem;
  overflow: hidden;
  transition: border-color 0.2s;
}

.menu-item-card:hover {
  border-color: rgba(99, 102, 241, 0.4);
}

.item-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 0.85rem;
  min-height: 48px;
}

.drag-handle {
  cursor: grab;
  color: #64748b;
  font-size: 1.1rem;
  user-select: none;
  flex-shrink: 0;
  padding: 0 4px;
  transition: color 0.15s;
}

.drag-handle:hover {
  color: #94a3b8;
}

.item-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
  min-width: 0;
}

.item-icon {
  font-size: 1.1rem;
  flex-shrink: 0;
}

.item-text {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.item-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #e2e8f0;
  cursor: pointer;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.item-url {
  font-size: 0.7rem;
  color: #64748b;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.inline-input {
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(99, 102, 241, 0.5);
  border-radius: 0.35rem;
  color: #e2e8f0;
  padding: 2px 8px;
  font-size: 0.875rem;
  outline: none;
  width: 100%;
}

.item-actions {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  flex-shrink: 0;
}

.action-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.2rem 0.35rem;
  border-radius: 0.35rem;
  font-size: 0.85rem;
  transition: background 0.15s;
  opacity: 0.6;
  color: #e2e8f0;
}

.action-btn:hover {
  background: rgba(148, 163, 184, 0.15);
  opacity: 1;
}

.vis-off { opacity: 0.35; }
.delete-btn:hover { background: rgba(239, 68, 68, 0.2) !important; }
.add-child-btn:hover { background: rgba(34, 197, 94, 0.15) !important; }

.children-wrap {
  border-top: 1px solid rgba(148, 163, 184, 0.1);
  background: rgba(2, 6, 23, 0.3);
  padding: 0.5rem 0.5rem 0.5rem 1.5rem;
}

.children-list {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  min-height: 36px;
}

.drop-zone {
  border: 1px dashed rgba(99, 102, 241, 0.3);
  border-radius: 0.5rem;
  padding: 0.5rem;
}

.drop-hint {
  text-align: center;
  color: #475569;
  font-size: 0.75rem;
  padding: 6px;
}

.drag-ghost {
  opacity: 0.3;
  background: rgba(99, 102, 241, 0.2) !important;
}

.drag-chosen {
  border-color: rgba(99, 102, 241, 0.6) !important;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25);
}
</style>
