<template>
  <div v-if="actions.length" class="floating-actions">
    <a
      v-for="action in actions"
      :key="action.key"
      :href="action.href"
      class="floating-action"
      :class="action.variant"
      target="_blank"
      rel="noopener noreferrer"
      :title="action.label"
      :aria-label="action.label"
    >
      <span v-if="action.iconUrl" class="icon-image">
        <img :src="action.iconUrl" :alt="action.label" />
      </span>
      <span v-else class="material-icons-outlined icon-text">
        {{ action.icon || "chat" }}
      </span>
    </a>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useSettingsStore } from "@/stores/settings";
import { useI18n } from "vue-i18n";

const settingsStore = useSettingsStore();
const { t } = useI18n();

const isEnabled = (value) => value === true || value === "true" || value === "1";

const normalizePhone = (value) => (value || "").replace(/\s+/g, "");

const buildZaloLink = (numberOrUrl) => {
  if (!numberOrUrl) return "";
  if (numberOrUrl.startsWith("http")) return numberOrUrl;
  return `https://zalo.me/${numberOrUrl}`;
};

const buildWhatsAppLink = (numberOrUrl) => {
  if (!numberOrUrl) return "";
  if (numberOrUrl.startsWith("http")) return numberOrUrl;
  return `https://wa.me/${numberOrUrl}`;
};

const actions = computed(() => {
  const items = [];

  const phoneLabel =
    settingsStore.getSetting("contact_action_phone_label") ||
    t("contact.phone", "Phone");
  const hotlineLabel =
    settingsStore.getSetting("contact_action_hotline_label") ||
    t("contact.hotline", "Hotline");
  const zaloLabel =
    settingsStore.getSetting("contact_action_zalo_label") || "Zalo";
  const whatsappLabel =
    settingsStore.getSetting("contact_action_whatsapp_label") || "WhatsApp";
  const messengerLabel =
    settingsStore.getSetting("contact_action_messenger_label") || "Messenger";

  if (isEnabled(settingsStore.getSetting("contact_action_phone_enabled"))) {
    const phone = normalizePhone(settingsStore.contactPhone);
    if (phone) {
      items.push({
        key: "phone",
        label: phoneLabel,
        href: `tel:${phone}`,
        icon: "call",
        iconUrl: settingsStore.getSetting("contact_action_phone_icon"),
        variant: "phone",
      });
    }
  }

  if (isEnabled(settingsStore.getSetting("contact_action_hotline_enabled"))) {
    const hotline = normalizePhone(settingsStore.contactHotline);
    if (hotline) {
      items.push({
        key: "hotline",
        label: hotlineLabel,
        href: `tel:${hotline}`,
        icon: "phone_in_talk",
        iconUrl: settingsStore.getSetting("contact_action_hotline_icon"),
        variant: "hotline",
      });
    }
  }

  if (isEnabled(settingsStore.getSetting("contact_action_zalo_enabled"))) {
    const zaloValue = settingsStore.contactZalo || settingsStore.socialZalo;
    const zaloLink = buildZaloLink(zaloValue);
    if (zaloLink) {
      items.push({
        key: "zalo",
        label: zaloLabel,
        href: zaloLink,
        icon: "chat",
        iconUrl: settingsStore.getSetting("contact_action_zalo_icon"),
        variant: "zalo",
      });
    }
  }

  if (isEnabled(settingsStore.getSetting("contact_action_whatsapp_enabled"))) {
    const whatsappValue = settingsStore.contactWhatsapp;
    const whatsappLink = buildWhatsAppLink(whatsappValue);
    if (whatsappLink) {
      items.push({
        key: "whatsapp",
        label: whatsappLabel,
        href: whatsappLink,
        icon: "chat",
        iconUrl: settingsStore.getSetting("contact_action_whatsapp_icon"),
        variant: "whatsapp",
      });
    }
  }

  if (isEnabled(settingsStore.getSetting("contact_action_messenger_enabled"))) {
    const messengerLink = settingsStore.socialMessenger;
    if (messengerLink) {
      items.push({
        key: "messenger",
        label: messengerLabel,
        href: messengerLink,
        icon: "message",
        iconUrl: settingsStore.getSetting("contact_action_messenger_icon"),
        variant: "messenger",
      });
    }
  }

  return items;
});
</script>

<style scoped>
.floating-actions {
  position: fixed;
  right: 16px;
  top: 35%;
  display: flex;
  flex-direction: column;
  gap: 10px;
  z-index: 50;
}

.floating-action {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #0f172a;
  color: #fff;
  box-shadow: 0 12px 24px rgba(15, 23, 42, 0.2);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.floating-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 30px rgba(15, 23, 42, 0.25);
}

.icon-image img {
  width: 26px;
  height: 26px;
}

.icon-text {
  font-size: 24px;
}

.floating-action.phone {
  background: #16a34a;
}

.floating-action.hotline {
  background: #0ea5e9;
}

.floating-action.zalo {
  background: #2563eb;
}

.floating-action.whatsapp {
  background: #22c55e;
}

.floating-action.messenger {
  background: #3b82f6;
}

@media (max-width: 640px) {
  .floating-actions {
    right: 10px;
    top: auto;
    bottom: 18px;
  }
}
</style>
