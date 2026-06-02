import api from './api';

// =============================================
// Nav Menu Service — Admin + Public
// =============================================

/** Public: Lấy menu theo tên (VD: 'header') */
export const fetchPublicMenu = (name) => api.get(`/nav-menus/${name}`);

// ----------- Admin endpoints -----------

/** Danh sách tất cả bộ menu */
export const fetchNavMenus = () => api.get('/admin/nav-menus');

/** Chi tiết 1 bộ menu kèm cây items */
export const fetchNavMenu = (id) => api.get(`/admin/nav-menus/${id}`);

/** Tạo bộ menu mới */
export const createNavMenu = (data) => api.post('/admin/nav-menus', data);

/** Cập nhật thông tin bộ menu */
export const updateNavMenu = (id, data) => api.put(`/admin/nav-menus/${id}`, data);

/** Xóa bộ menu */
export const deleteNavMenu = (id) => api.delete(`/admin/nav-menus/${id}`);

/** Lưu cấu trúc cây sau khi drag & drop */
export const saveMenuTree = (id, items) =>
  api.put(`/admin/nav-menus/${id}/tree`, { items });

/** Thêm 1 item mới vào bộ menu */
export const addMenuItem = (menuId, data) =>
  api.post(`/admin/nav-menus/${menuId}/items`, data);

/** Lấy danh sách route gợi ý */
export const fetchAvailableRoutes = () =>
  api.get('/admin/nav-menus/available-routes');
