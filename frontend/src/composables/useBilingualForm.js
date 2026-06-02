import { computed } from "vue";
import { useSettingsStore } from "@/stores/settings";

/**
 * Composable để xử lý forms có song ngữ
 * Khi bilingual bị tắt, chỉ hiển thị field tiếng Anh
 */
export function useBilingualForm() {
  const settingsStore = useSettingsStore();

  // Kiểm tra xem có nên hiển thị fields tiếng Việt không
  const shouldShowVietnamese = computed(() => {
    return settingsStore.bilingualEnabled;
  });

  // Kiểm tra xem có nên hiển thị fields tiếng Anh không
  const shouldShowEnglish = computed(() => {
    return true; // Luôn hiển thị tiếng Anh
  });

  // Lấy label cho field dựa trên trạng thái bilingual
  const getFieldLabel = (baseLabel, isVietnamese = false) => {
    if (!settingsStore.bilingualEnabled) {
      return baseLabel; // Không hiển thị suffix khi tắt bilingual
    }
    return isVietnamese
      ? `${baseLabel} (Tiếng Việt)`
      : `${baseLabel} (English)`;
  };

  // Validate form - đảm bảo ít nhất field English có giá trị khi bilingual tắt
  const validateBilingualField = (viValue, enValue, fieldName) => {
    if (!settingsStore.bilingualEnabled) {
      // Khi tắt bilingual, chỉ cần field tiếng Anh
      if (!enValue || enValue.trim() === "") {
        return `${fieldName} is required`;
      }
    } else {
      // Khi bật bilingual, cần ít nhất một trong hai
      if (
        (!viValue || viValue.trim() === "") &&
        (!enValue || enValue.trim() === "")
      ) {
        return `${fieldName} (at least one language) is required`;
      }
    }
    return null;
  };

  // Tự động copy giá trị từ tiếng Anh sang tiếng Việt khi bilingual tắt
  const syncEnglishToVietnamese = (formData, fieldName) => {
    if (!settingsStore.bilingualEnabled) {
      const enField = `${fieldName}_en`;
      const viField = `${fieldName}_vi`;
      if (formData[enField] && !formData[viField]) {
        formData[viField] = formData[enField];
      }
    }
  };

  // Xử lý dữ liệu form trước khi submit
  const prepareBilingualData = (formData) => {
    const prepared = { ...formData };

    if (!settingsStore.bilingualEnabled) {
      // Khi tắt bilingual, copy English sang Vietnamese nếu Vietnamese trống
      Object.keys(prepared).forEach((key) => {
        if (key.endsWith("_en")) {
          const baseField = key.replace("_en", "");
          const viField = `${baseField}_vi`;
          if (
            viField in prepared &&
            (!prepared[viField] || prepared[viField].trim() === "")
          ) {
            prepared[viField] = prepared[key];
          }
        }
      });
    }

    return prepared;
  };

  return {
    shouldShowVietnamese,
    shouldShowEnglish,
    getFieldLabel,
    validateBilingualField,
    syncEnglishToVietnamese,
    prepareBilingualData,
  };
}
