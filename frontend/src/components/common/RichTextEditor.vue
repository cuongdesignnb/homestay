<template>
  <QuillEditor
    v-model:content="content"
    content-type="html"
    theme="snow"
    :placeholder="placeholder"
  />
</template>

<script setup>
import { ref, watch } from "vue";
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  placeholder: {
    type: String,
    default: "",
  },
});

const emit = defineEmits(["update:modelValue"]);
const content = ref(props.modelValue || "");

watch(
  () => props.modelValue,
  (value) => {
    if (value !== content.value) {
      content.value = value || "";
    }
  }
);

watch(content, (value) => {
  emit("update:modelValue", value);
});
</script>

<style scoped>
:global(.ql-editor) {
  min-height: 160px;
}
</style>
