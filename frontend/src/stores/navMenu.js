import { defineStore } from 'pinia';
import { ref } from 'vue';
import { fetchPublicMenu } from '@/services/navMenuService';

export const useNavMenuStore = defineStore('navMenu', () => {
  /** Cache: { menuName -> { items, loaded } } */
  const cache = ref({});
  const loading = ref(false);
  const error = ref(null);

  /**
   * Lấy menu theo tên — có cache để tránh gọi API nhiều lần
   */
  async function fetchMenu(name) {
    if (cache.value[name]?.loaded) return cache.value[name].items;

    loading.value = true;
    error.value = null;
    try {
      const res = await fetchPublicMenu(name);
      const items = res.data?.data?.items ?? [];
      cache.value[name] = { items, loaded: true };
      return items;
    } catch (e) {
      error.value = e?.response?.data?.message || 'Không thể tải menu';
      return [];
    } finally {
      loading.value = false;
    }
  }

  /** Lấy items đã cache (không gọi API) */
  function getMenu(name) {
    return cache.value[name]?.items ?? [];
  }

  /** Xóa cache để force reload */
  function invalidate(name) {
    if (name) {
      delete cache.value[name];
    } else {
      cache.value = {};
    }
  }

  return { loading, error, fetchMenu, getMenu, invalidate };
});
