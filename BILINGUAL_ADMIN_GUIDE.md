# Hướng dẫn xử lý các component admin có song ngữ

## Tổng quan

Khi tính năng bilingual bị tắt trong admin settings, chúng ta cần đảm bảo:
1. **Giao diện**: Chỉ hiển thị fields tiếng Anh, ẩn fields tiếng Việt
2. **Dữ liệu**: Vẫn lưu cả hai ngôn ngữ trong database để khi bật lại vẫn hoạt động
3. **Logic**: Tự động copy dữ liệu từ tiếng Anh sang tiếng Việt nếu tiếng Việt trống

## Cách áp dụng

### 1. Import composable trong component:

```javascript
import { useBilingualForm } from "@/composables/useBilingualForm";

// Trong script setup
const { 
  shouldShowVietnamese, 
  shouldShowEnglish, 
  getFieldLabel, 
  prepareBilingualData 
} = useBilingualForm();
```

### 2. Cập nhật template:

**Trước:**
```vue
<div class="form-row">
  <div class="form-group">
    <label>Tên (Tiếng Việt) *</label>
    <input type="text" v-model="form.name" required />
  </div>
  <div class="form-group">
    <label>Tên (English)</label>
    <input type="text" v-model="form.name_en" />
  </div>
</div>
```

**Sau:**
```vue
<div class="form-row">
  <div v-if="shouldShowVietnamese" class="form-group">
    <label>{{ getFieldLabel('Tên', true) }} *</label>
    <input type="text" v-model="form.name" :required="!shouldShowEnglish" />
  </div>
  <div v-if="shouldShowEnglish" class="form-group">
    <label>{{ getFieldLabel('Tên', false) }} *</label>
    <input type="text" v-model="form.name_en" required />
  </div>
</div>
```

### 3. Cập nhật hàm save:

**Trước:**
```javascript
const saveItem = async () => {
  await api.post("/admin/items", form.value);
};
```

**Sau:**
```javascript
const saveItem = async () => {
  const formData = prepareBilingualData(form.value);
  await api.post("/admin/items", formData);
};
```

## Các component cần cập nhật:

### Đã cập nhật:
- ✅ MenuCategories.vue (demo)

### Cần cập nhật:
- ⏳ MenuItems.vue
- ⏳ Rooms.vue  
- ⏳ Tours.vue
- ⏳ RestaurantSettings.vue
- ⏳ Settings.vue (các phần có _vi/_en fields)
- ⏳ Blog.vue (nếu có)

## Logic hoạt động:

### Khi bilingual ENABLED:
- Hiển thị cả 2 fields (Tiếng Việt + English)
- Labels có suffix "(Tiếng Việt)" và "(English)"
- Yêu cầu ít nhất 1 trong 2 fields có giá trị

### Khi bilingual DISABLED:
- Chỉ hiển thị field English  
- Label không có suffix
- Yêu cầu field English bắt buộc
- Tự động copy English → Vietnamese khi save (nếu Vietnamese trống)

## Tương thích ngược:

- ✅ Khi bật lại bilingual, tất cả dữ liệu cũ vẫn hiển thị đúng
- ✅ Database schema không thay đổi
- ✅ API endpoints không thay đổi
- ✅ Chỉ thay đổi UI và logic frontend

## Testing checklist:

1. **Tắt bilingual** → Chỉ thấy English fields
2. **Tạo item mới** → Lưu thành công, tự động điền Vietnamese 
3. **Bật lại bilingual** → Thấy cả 2 fields với dữ liệu đầy đủ
4. **Edit item cũ** → Hiển thị đúng dữ liệu cả 2 ngôn ngữ
5. **Save item đã có** → Không mất dữ liệu

## Lưu ý quan trọng:

- **Không xóa** fields Vietnamese khỏi form.value
- **Không thay đổi** database schema
- **Luôn gửi** cả 2 ngôn ngữ lên API
- **Chỉ thay đổi** giao diện hiển thị