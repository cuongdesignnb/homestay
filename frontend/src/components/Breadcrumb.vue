<template>
  <nav class="breadcrumb-nav" aria-label="Breadcrumb">
    <ol
      class="breadcrumb-list"
      itemscope
      itemtype="https://schema.org/BreadcrumbList"
    >
      <li
        v-for="(item, index) in items"
        :key="index"
        class="breadcrumb-item"
        itemprop="itemListElement"
        itemscope
        itemtype="https://schema.org/ListItem"
      >
        <router-link
          v-if="index < items.length - 1"
          :to="item.path"
          class="breadcrumb-link"
          itemprop="item"
        >
          <span itemprop="name">{{ item.label }}</span>
        </router-link>
        <span v-else class="breadcrumb-current" itemprop="item">
          <span itemprop="name">{{ item.label }}</span>
        </span>
        <meta itemprop="position" :content="index + 1" />
        <span v-if="index < items.length - 1" class="breadcrumb-separator">
          <svg
            width="16"
            height="16"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path d="M9 18l6-6-6-6" />
          </svg>
        </span>
      </li>
    </ol>
  </nav>
</template>

<script setup>
import { computed } from "vue";
import { useI18n } from "vue-i18n";

const props = defineProps({
  items: {
    type: Array,
    required: true,
    // Each item: { label: string, path: string }
  },
});

const { t } = useI18n();
</script>

<style scoped>
.breadcrumb-nav {
  padding: 1rem 0;
  margin-bottom: 1.5rem;
}

.breadcrumb-list {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.25rem;
  list-style: none;
  margin: 0;
  padding: 0;
  font-size: 0.9rem;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.breadcrumb-link {
  color: #6366f1;
  text-decoration: none;
  transition: all 0.2s ease;
  padding: 0.25rem 0.5rem;
  border-radius: 0.375rem;
}

.breadcrumb-link:hover {
  color: #4f46e5;
  background: rgba(99, 102, 241, 0.1);
}

.breadcrumb-current {
  color: #64748b;
  font-weight: 500;
  padding: 0.25rem 0.5rem;
}

.breadcrumb-separator {
  color: #cbd5e1;
  display: flex;
  align-items: center;
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .breadcrumb-link {
    color: #818cf8;
  }

  .breadcrumb-link:hover {
    color: #a5b4fc;
    background: rgba(99, 102, 241, 0.2);
  }

  .breadcrumb-current {
    color: #94a3b8;
  }

  .breadcrumb-separator {
    color: #475569;
  }
}
</style>
