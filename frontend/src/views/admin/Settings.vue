<template>
  <div class="settings-shell">
    <!-- Header -->
    <header class="settings-header">
      <div>
        <h1 class="text-3xl font-bold text-white">
          {{ $t("admin.settings", "Cài đặt") }}
        </h1>
        <p class="text-slate-400 mt-1">
          {{
            $t(
              "admin.settings_desc",
              "Quản lý thông tin website, SEO và nội dung",
            )
          }}
        </p>
      </div>
      <button @click="saveSettings" :disabled="saving" class="btn-save">
        <span v-if="saving" class="spinner"></span>
        {{
          saving
            ? $t("common.saving", "Đang lưu...")
            : $t("common.save", "Lưu thay đổi")
        }}
      </button>
    </header>

    <!-- Tabs -->
    <nav class="settings-tabs">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        :class="['tab-btn', { active: activeTab === tab.key }]"
        @click="activeTab = tab.key"
      >
        <span class="material-icons-outlined tab-icon text-lg mr-1.5 align-middle">{{ tab.icon }}</span>
        <span class="align-middle">{{ tab.label }}</span>
      </button>
    </nav>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner-lg"></div>
      <p>{{ $t("common.loading", "Đang tải...") }}</p>
    </div>

    <!-- Settings Content -->
    <div v-else class="settings-content">
      <!-- General Settings -->
      <section v-show="activeTab === 'general'" class="settings-section">
        <div class="section-header">
          <h2>{{ $t("admin.general_settings", "Cài đặt chung") }}</h2>
          <p>
            {{
              $t("admin.general_settings_desc", "Thông tin cơ bản của website")
            }}
          </p>
        </div>

        <div class="settings-grid">
          <div class="setting-group full-width">
            <label>{{ $t("admin.site_name", "Tên website") }}</label>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input
                  type="text"
                  v-model="settings.site_name_vi"
                  :placeholder="$t('admin.site_name_vi', 'Tên tiếng Việt')"
                />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input
                  type="text"
                  v-model="settings.site_name_en"
                  :placeholder="$t('admin.site_name_en', 'English name')"
                />
              </div>
            </div>
          </div>

          <div class="setting-group full-width">
            <label>{{ $t("admin.site_tagline", "Slogan") }}</label>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input
                  type="text"
                  v-model="settings.site_tagline_vi"
                  :placeholder="$t('admin.tagline_vi', 'Slogan tiếng Việt')"
                />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input
                  type="text"
                  v-model="settings.site_tagline_en"
                  :placeholder="$t('admin.tagline_en', 'English tagline')"
                />
              </div>
            </div>
          </div>

          <div class="setting-group">
            <label>{{ $t("admin.logo_url", "Logo") }}</label>
            <div class="image-picker" @click="openMediaPicker('logo_url')">
              <div v-if="settings.logo_url" class="image-preview">
                <img :src="settings.logo_url" alt="Logo" />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.logo_url = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">🖼️</span>
                <span>{{ $t("admin.select_image", "Chọn hình ảnh") }}</span>
              </div>
            </div>
          </div>

          <div class="setting-group">
            <label>{{ $t("admin.favicon_url", "Favicon") }}</label>
            <div
              class="image-picker small"
              @click="openMediaPicker('favicon_url')"
            >
              <div v-if="settings.favicon_url" class="image-preview">
                <img :src="settings.favicon_url" alt="Favicon" />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.favicon_url = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">🔖</span>
                <span>{{ $t("admin.select_image", "Chọn hình ảnh") }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Language Settings -->
      <section v-show="activeTab === 'language'" class="settings-section">
        <div class="section-header">
          <h2>{{ $t("admin.language_settings", "Cài đặt ngôn ngữ") }}</h2>
          <p>
            {{
              $t(
                "admin.language_settings_desc",
                "Quản lý hiển thị ngôn ngữ trên website",
              )
            }}
          </p>
        </div>

        <div class="settings-grid">
          <div class="setting-group full-width">
            <label>{{ $t("admin.bilingual_mode", "Chế độ song ngữ") }}</label>
            <div class="toggle-group">
              <label class="toggle-switch">
                <input
                  type="checkbox"
                  v-model="settings.enable_bilingual"
                  @change="handleBilingualToggle"
                />
                <span class="toggle-slider"></span>
                <span class="toggle-label">
                  {{ settings.enable_bilingual ? "Bật" : "Tắt" }}
                </span>
              </label>
              <p class="setting-description">
                {{
                  settings.enable_bilingual
                    ? "Website hiển thị song ngữ Việt - Anh với nút chuyển đổi"
                    : "Website chỉ hiển thị tiếng Anh duy nhất"
                }}
              </p>
            </div>
          </div>

          <div v-if="settings.enable_bilingual" class="setting-group">
            <label>{{
              $t("admin.default_language", "Ngôn ngữ mặc định")
            }}</label>
            <select v-model="settings.default_language" class="form-select">
              <option value="vi">🇻🇳 Tiếng Việt</option>
              <option value="en">🇬🇧 English</option>
            </select>
          </div>

          <div class="setting-group full-width">
            <div class="info-card">
              <div class="info-icon">ℹ️</div>
              <div class="info-content">
                <h4>Thông tin về cài đặt ngôn ngữ</h4>
                <ul>
                  <li>
                    <strong>Bật song ngữ:</strong> Hiển thị nút chuyển đổi
                    VI/EN, nội dung hiện theo ngôn ngữ được chọn
                  </li>
                  <li>
                    <strong>Tắt song ngữ:</strong> Ẩn nút chuyển đổi, website
                    chỉ hiển thị tiếng Anh
                  </li>
                  <li>
                    <strong>Ngôn ngữ mặc định:</strong> Ngôn ngữ hiển thị khi
                    người dùng truy cập lần đầu
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Feature Toggles -->
      <section v-show="activeTab === 'features'" class="settings-section">
        <div class="section-header">
          <h2>Bật / Tắt chức năng</h2>
          <p>Ẩn hoặc hiện các module trên website và admin panel</p>
        </div>

        <div class="settings-grid">
          <div class="setting-group full-width">
            <label>🏡 Chức năng Đặt phòng</label>
            <div class="toggle-group">
              <label class="toggle-switch">
                <input
                  type="checkbox"
                  v-model="settings.feature_rooms_enabled"
                />
                <span class="toggle-slider"></span>
                <span class="toggle-label">
                  {{ settings.feature_rooms_enabled ? 'Bật — Hiển thị mục Phòng' : 'Tắt — Ẩn mục Phòng' }}
                </span>
              </label>
              <p class="setting-description">
                {{
                  settings.feature_rooms_enabled
                    ? 'Menu Phòng hiển thị trên website và admin. Khách có thể xem và đặt phòng.'
                    : 'Menu Phòng bị ẩn hoàn toàn trên website và admin.'
                }}
              </p>
            </div>
          </div>

          <div class="setting-group full-width">
            <label>🚗 Chức năng Cho thuê xe</label>
            <div class="toggle-group">
              <label class="toggle-switch">
                <input
                  type="checkbox"
                  v-model="settings.feature_car_rentals_enabled"
                />
                <span class="toggle-slider"></span>
                <span class="toggle-label">
                  {{ settings.feature_car_rentals_enabled ? 'Bật — Hiển thị mục Cho thuê xe' : 'Tắt — Ẩn mục Cho thuê xe' }}
                </span>
              </label>
              <p class="setting-description">
                {{
                  settings.feature_car_rentals_enabled
                    ? 'Menu Cho thuê xe hiển thị trên website và admin. Khách có thể xem và đặt xe.'
                    : 'Menu Cho thuê xe bị ẩn hoàn toàn trên website và admin.'
                }}
              </p>
            </div>
          </div>

          <div class="setting-group full-width">
            <div class="info-card">
              <div class="info-icon">ℹ️</div>
              <div class="info-content">
                <h4>Lưu ý</h4>
                <ul>
                  <li>Khi <strong>tắt</strong> một chức năng, menu tương ứng sẽ bị ẩn khỏi cả frontend (website) và admin sidebar.</li>
                  <li>Dữ liệu (phòng, xe) vẫn được lưu trong hệ thống, chỉ ẩn giao diện.</li>
                  <li>Bật lại bất kỳ lúc nào để hiển thị trở lại.</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- SEO Settings -->
      <section v-show="activeTab === 'seo'" class="settings-section">
        <div class="section-header">
          <h2>{{ $t("admin.seo_settings", "Cài đặt SEO") }}</h2>
          <p>
            {{
              $t(
                "admin.seo_settings_desc",
                "Tối ưu hiển thị trên công cụ tìm kiếm",
              )
            }}
          </p>
        </div>

        <div class="settings-grid">
          <div class="setting-group full-width">
            <label>{{ $t("admin.meta_title", "Meta Title") }}</label>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input
                  type="text"
                  v-model="settings.meta_title_vi"
                  maxlength="60"
                />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input
                  type="text"
                  v-model="settings.meta_title_en"
                  maxlength="60"
                />
              </div>
            </div>
            <small class="char-count"
              >{{ settings.meta_title_vi?.length || 0 }}/60</small
            >
          </div>

          <div class="setting-group full-width">
            <label>{{
              $t("admin.meta_description", "Meta Description")
            }}</label>
            <div class="input-lang-group vertical">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <textarea
                  v-model="settings.meta_description_vi"
                  maxlength="160"
                  rows="2"
                ></textarea>
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <textarea
                  v-model="settings.meta_description_en"
                  maxlength="160"
                  rows="2"
                ></textarea>
              </div>
            </div>
            <small class="char-count"
              >{{ settings.meta_description_vi?.length || 0 }}/160</small
            >
          </div>

          <div class="setting-group full-width">
            <label>{{ $t("admin.meta_keywords", "Meta Keywords") }}</label>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input
                  type="text"
                  v-model="settings.meta_keywords_vi"
                  :placeholder="
                    $t(
                      'admin.keywords_placeholder',
                      'từ khóa 1, từ khóa 2, ...',
                    )
                  "
                />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input
                  type="text"
                  v-model="settings.meta_keywords_en"
                  placeholder="keyword1, keyword2, ..."
                />
              </div>
            </div>
          </div>

          <div class="setting-group">
            <label>{{ $t("admin.og_image", "OG Image") }}</label>
            <div class="image-picker wide" @click="openMediaPicker('og_image')">
              <div v-if="settings.og_image" class="image-preview">
                <img :src="settings.og_image" alt="OG Image" />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.og_image = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">🖼️</span>
                <span>{{
                  $t("admin.select_og_image", "Chọn ảnh OG (1200x630)")
                }}</span>
              </div>
            </div>
          </div>

          <div class="setting-group">
            <label>{{
              $t("admin.google_analytics", "Google Analytics ID")
            }}</label>
            <input
              type="text"
              v-model="settings.google_analytics_id"
              placeholder="G-XXXXXXXXXX"
            />
          </div>
        </div>
      </section>

      <!-- Contact Settings -->
      <section v-show="activeTab === 'contact'" class="settings-section">
        <div class="section-header">
          <h2>{{ $t("admin.contact_settings", "Thông tin liên hệ") }}</h2>
          <p>
            {{
              $t(
                "admin.contact_settings_desc",
                "Địa chỉ, số điện thoại và email",
              )
            }}
          </p>
        </div>

        <div class="settings-grid">
          <div class="setting-group">
            <label>{{ $t("admin.email", "Email") }}</label>
            <input type="email" v-model="settings.contact_email" />
          </div>

          <div class="setting-group">
            <label>{{ $t("admin.phone", "Số điện thoại") }}</label>
            <input type="tel" v-model="settings.contact_phone" />
          </div>

          <div class="setting-group">
            <label>{{ $t("admin.hotline", "Hotline") }}</label>
            <input type="tel" v-model="settings.contact_hotline" />
          </div>

          <div class="setting-group">
            <label>{{ $t("admin.zalo", "Số Zalo") }}</label>
            <input type="tel" v-model="settings.contact_zalo" />
          </div>

          <div class="setting-group">
            <label>{{ $t("admin.whatsapp", "Số WhatsApp") }}</label>
            <input
              type="tel"
              v-model="settings.contact_whatsapp"
              placeholder="84123456789 (bao gồm mã quốc gia, không có dấu +)"
            />
            <small class="text-gray-500 mt-1 block">
              Nhập số điện thoại bao gồm mã quốc gia (ví dụ: 84123456789 cho số
              Việt Nam)
            </small>
          </div>

          <div class="setting-group full-width">
            <label>{{ $t("admin.address", "Địa chỉ") }}</label>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input type="text" v-model="settings.contact_address_vi" />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input type="text" v-model="settings.contact_address_en" />
              </div>
            </div>
          </div>

          <div class="setting-group full-width">
            <label>{{
              $t("admin.google_maps", "Google Maps Embed URL")
            }}</label>
            <input
              type="url"
              v-model="settings.google_maps_url"
              placeholder="https://www.google.com/maps/embed?..."
            />
          </div>
        </div>

        <div class="section-divider"></div>

        <div class="section-header compact">
          <h3>{{ $t("admin.contact_actions", "Nút liên hệ nổi") }}</h3>
          <p>
            {{
              $t(
                "admin.contact_actions_desc",
                "Bật/tắt và chọn icon hiển thị cho các nút liên hệ nhanh",
              )
            }}
          </p>
        </div>

        <div class="settings-grid">
          <div class="setting-group">
            <label>{{ $t("admin.phone", "Số điện thoại") }}</label>
            <div class="toggle-row">
              <input
                type="checkbox"
                v-model="settings.contact_action_phone_enabled"
              />
              <span>{{ $t("admin.enabled", "Hiển thị") }}</span>
            </div>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input
                  type="text"
                  v-model="settings.contact_action_phone_label_vi"
                  placeholder="Gọi điện"
                />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input
                  type="text"
                  v-model="settings.contact_action_phone_label_en"
                  placeholder="Call"
                />
              </div>
            </div>
            <div
              class="image-picker"
              @click="openMediaPicker('contact_action_phone_icon')"
            >
              <div
                v-if="settings.contact_action_phone_icon"
                class="image-preview"
              >
                <img
                  :src="settings.contact_action_phone_icon"
                  alt="Phone Icon"
                />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.contact_action_phone_icon = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">📞</span>
                <span>{{ $t("admin.select_icon", "Chọn icon") }}</span>
              </div>
            </div>
          </div>

          <div class="setting-group">
            <label>{{ $t("admin.hotline", "Hotline") }}</label>
            <div class="toggle-row">
              <input
                type="checkbox"
                v-model="settings.contact_action_hotline_enabled"
              />
              <span>{{ $t("admin.enabled", "Hiển thị") }}</span>
            </div>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input
                  type="text"
                  v-model="settings.contact_action_hotline_label_vi"
                  placeholder="Hotline"
                />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input
                  type="text"
                  v-model="settings.contact_action_hotline_label_en"
                  placeholder="Hotline"
                />
              </div>
            </div>
            <div
              class="image-picker"
              @click="openMediaPicker('contact_action_hotline_icon')"
            >
              <div
                v-if="settings.contact_action_hotline_icon"
                class="image-preview"
              >
                <img
                  :src="settings.contact_action_hotline_icon"
                  alt="Hotline Icon"
                />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.contact_action_hotline_icon = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">☎️</span>
                <span>{{ $t("admin.select_icon", "Chọn icon") }}</span>
              </div>
            </div>
          </div>

          <div class="setting-group">
            <label>Zalo</label>
            <div class="toggle-row">
              <input
                type="checkbox"
                v-model="settings.contact_action_zalo_enabled"
              />
              <span>{{ $t("admin.enabled", "Hiển thị") }}</span>
            </div>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input
                  type="text"
                  v-model="settings.contact_action_zalo_label_vi"
                  placeholder="Zalo"
                />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input
                  type="text"
                  v-model="settings.contact_action_zalo_label_en"
                  placeholder="Zalo"
                />
              </div>
            </div>
            <div
              class="image-picker"
              @click="openMediaPicker('contact_action_zalo_icon')"
            >
              <div
                v-if="settings.contact_action_zalo_icon"
                class="image-preview"
              >
                <img :src="settings.contact_action_zalo_icon" alt="Zalo Icon" />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.contact_action_zalo_icon = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">💬</span>
                <span>{{ $t("admin.select_icon", "Chọn icon") }}</span>
              </div>
            </div>
          </div>

          <div class="setting-group">
            <label>WhatsApp</label>
            <div class="toggle-row">
              <input
                type="checkbox"
                v-model="settings.contact_action_whatsapp_enabled"
              />
              <span>{{ $t("admin.enabled", "Hiển thị") }}</span>
            </div>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input
                  type="text"
                  v-model="settings.contact_action_whatsapp_label_vi"
                  placeholder="WhatsApp"
                />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input
                  type="text"
                  v-model="settings.contact_action_whatsapp_label_en"
                  placeholder="WhatsApp"
                />
              </div>
            </div>
            <div
              class="image-picker"
              @click="openMediaPicker('contact_action_whatsapp_icon')"
            >
              <div
                v-if="settings.contact_action_whatsapp_icon"
                class="image-preview"
              >
                <img
                  :src="settings.contact_action_whatsapp_icon"
                  alt="WhatsApp Icon"
                />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.contact_action_whatsapp_icon = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">🟢</span>
                <span>{{ $t("admin.select_icon", "Chọn icon") }}</span>
              </div>
            </div>
          </div>

          <div class="setting-group">
            <label>Messenger</label>
            <div class="toggle-row">
              <input
                type="checkbox"
                v-model="settings.contact_action_messenger_enabled"
              />
              <span>{{ $t("admin.enabled", "Hiển thị") }}</span>
            </div>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input
                  type="text"
                  v-model="settings.contact_action_messenger_label_vi"
                  placeholder="Messenger"
                />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input
                  type="text"
                  v-model="settings.contact_action_messenger_label_en"
                  placeholder="Messenger"
                />
              </div>
            </div>
            <div
              class="image-picker"
              @click="openMediaPicker('contact_action_messenger_icon')"
            >
              <div
                v-if="settings.contact_action_messenger_icon"
                class="image-preview"
              >
                <img
                  :src="settings.contact_action_messenger_icon"
                  alt="Messenger Icon"
                />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.contact_action_messenger_icon = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">💬</span>
                <span>{{ $t("admin.select_icon", "Chọn icon") }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Social Settings -->
      <section v-show="activeTab === 'social'" class="settings-section">
        <div class="section-header">
          <h2>{{ $t("admin.social_settings", "Mạng xã hội") }}</h2>
          <p>
            {{
              $t(
                "admin.social_settings_desc",
                "Liên kết đến các trang mạng xã hội",
              )
            }}
          </p>
        </div>

        <div class="settings-grid">
          <div class="setting-group">
            <label>
              <span class="social-icon facebook">f</span>
              Facebook
            </label>
            <input
              type="url"
              v-model="settings.social_facebook"
              placeholder="https://facebook.com/..."
            />
          </div>

          <div class="setting-group">
            <label>
              <span class="social-icon instagram">📷</span>
              Instagram
            </label>
            <input
              type="url"
              v-model="settings.social_instagram"
              placeholder="https://instagram.com/..."
            />
          </div>

          <div class="setting-group">
            <label>
              <span class="social-icon youtube">▶</span>
              YouTube
            </label>
            <input
              type="url"
              v-model="settings.social_youtube"
              placeholder="https://youtube.com/..."
            />
          </div>

          <div class="setting-group">
            <label>
              <span class="social-icon tiktok">♪</span>
              TikTok
            </label>
            <input
              type="url"
              v-model="settings.social_tiktok"
              placeholder="https://tiktok.com/..."
            />
          </div>

          <div class="setting-group">
            <label>
              <span class="social-icon messenger">💬</span>
              Messenger Link
            </label>
            <input
              type="url"
              v-model="settings.social_messenger"
              placeholder="https://m.me/..."
            />
          </div>

          <div class="setting-group">
            <label>
              <span class="social-icon zalo">Z</span>
              Zalo Link
            </label>
            <input
              type="url"
              v-model="settings.social_zalo"
              placeholder="https://zalo.me/..."
            />
          </div>
        </div>
      </section>

      <!-- Hero Section Settings -->
      <section v-show="activeTab === 'hero'" class="settings-section">
        <div class="section-header">
          <h2>{{ $t("admin.hero_settings", "Hero Section") }}</h2>
          <p>
            {{
              $t(
                "admin.hero_settings_desc",
                "Nội dung hiển thị ở phần Hero trên trang chủ",
              )
            }}
          </p>
        </div>

        <!-- Hero Text Content -->
        <div class="banner-config">
          <h3>📝 Nội dung chính</h3>
          <div class="settings-grid">
            <div class="setting-group full-width">
              <label>Tagline (dòng nhỏ phía trên title)</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_tagline_vi"
                    placeholder="Retreat in effortless style"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_tagline_en"
                    placeholder="Retreat in effortless style"
                  />
                </div>
              </div>
            </div>

            <div class="setting-group full-width">
              <label>Tiêu đề chính (Hero Title)</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_title_vi"
                    placeholder="Chào mừng đến với Happy Island Tour"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_title_en"
                    placeholder="Welcome to Happy Island Tour"
                  />
                </div>
              </div>
            </div>

            <div class="setting-group full-width">
              <label>Phụ đề (Hero Subtitle)</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_subtitle_vi"
                    placeholder="Trải nghiệm thiên nhiên..."
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_subtitle_en"
                    placeholder="Experience comfort..."
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Hero Gallery Images -->
        <div class="banner-config">
          <h3>🖼️ Hình ảnh Gallery</h3>
          <div class="settings-grid">
            <div class="setting-group">
              <label>Ảnh chính (lớn)</label>
              <div
                class="image-picker"
                @click="openMediaPicker('hero_image_1')"
              >
                <div v-if="settings.hero_image_1" class="image-preview">
                  <img :src="settings.hero_image_1" alt="Hero 1" />
                  <button
                    type="button"
                    class="remove-btn"
                    @click.stop="settings.hero_image_1 = ''"
                  >
                    ✕
                  </button>
                </div>
                <div v-else class="image-placeholder">
                  <span class="placeholder-icon">🖼️</span>
                  <span>Chọn ảnh chính</span>
                </div>
              </div>
            </div>

            <div class="setting-group">
              <label>Ảnh phụ 1</label>
              <div
                class="image-picker"
                @click="openMediaPicker('hero_image_2')"
              >
                <div v-if="settings.hero_image_2" class="image-preview">
                  <img :src="settings.hero_image_2" alt="Hero 2" />
                  <button
                    type="button"
                    class="remove-btn"
                    @click.stop="settings.hero_image_2 = ''"
                  >
                    ✕
                  </button>
                </div>
                <div v-else class="image-placeholder">
                  <span class="placeholder-icon">🖼️</span>
                  <span>Chọn ảnh</span>
                </div>
              </div>
            </div>

            <div class="setting-group">
              <label>Ảnh phụ 2</label>
              <div
                class="image-picker"
                @click="openMediaPicker('hero_image_3')"
              >
                <div v-if="settings.hero_image_3" class="image-preview">
                  <img :src="settings.hero_image_3" alt="Hero 3" />
                  <button
                    type="button"
                    class="remove-btn"
                    @click.stop="settings.hero_image_3 = ''"
                  >
                    ✕
                  </button>
                </div>
                <div v-else class="image-placeholder">
                  <span class="placeholder-icon">🖼️</span>
                  <span>Chọn ảnh</span>
                </div>
              </div>
            </div>

            <div class="setting-group full-width">
              <label>Caption ảnh chính</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_gallery_caption_vi"
                    placeholder="Ẩn mình bên vịnh xanh"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_gallery_caption_en"
                    placeholder="Hidden by the blue bay"
                  />
                </div>
              </div>
            </div>

            <div class="setting-group full-width">
              <label>Floating card - Tiêu đề</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_floating_title_vi"
                    placeholder="Trải nghiệm signature retreat"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_floating_title_en"
                    placeholder="Experience signature retreat"
                  />
                </div>
              </div>
            </div>

            <div class="setting-group full-width">
              <label>Floating card - Phụ đề</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_floating_subtitle_vi"
                    placeholder="+320 khoảnh khắc được lưu giữ"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_floating_subtitle_en"
                    placeholder="+320 moments captured"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Hero Stats -->
        <div class="banner-config">
          <h3>📊 Thống kê (Highlights)</h3>
          <div class="settings-grid">
            <div class="setting-group">
              <label>Stat 1 - Giá trị</label>
              <input
                type="text"
                v-model="settings.hero_stat1_value"
                placeholder="50+"
              />
            </div>
            <div class="setting-group">
              <label>Stat 1 - Nhãn</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_stat1_label_vi"
                    placeholder="Happy Island Tour được tuyển chọn"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_stat1_label_en"
                    placeholder="Happy Island Tours curated"
                  />
                </div>
              </div>
            </div>

            <div class="setting-group">
              <label>Stat 2 - Giá trị</label>
              <input
                type="text"
                v-model="settings.hero_stat2_value"
                placeholder="24"
              />
            </div>
            <div class="setting-group">
              <label>Stat 2 - Nhãn</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_stat2_label_vi"
                    placeholder="Điểm đến"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_stat2_label_en"
                    placeholder="Destinations"
                  />
                </div>
              </div>
            </div>

            <div class="setting-group">
              <label>Stat 3 - Giá trị</label>
              <input
                type="text"
                v-model="settings.hero_stat3_value"
                placeholder="4.9/5"
              />
            </div>
            <div class="setting-group">
              <label>Stat 3 - Nhãn</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_stat3_label_vi"
                    placeholder="Đánh giá khách"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_stat3_label_en"
                    placeholder="Guest reviews"
                  />
                </div>
              </div>
            </div>

            <div class="setting-group">
              <label>Stat 4 - Giá trị</label>
              <input
                type="text"
                v-model="settings.hero_stat4_value"
                placeholder="12+"
              />
            </div>
            <div class="setting-group">
              <label>Stat 4 - Nhãn</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_stat4_label_vi"
                    placeholder="Tours sẵn sàng"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_stat4_label_en"
                    placeholder="Tours ready"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Hero Perks -->
        <div class="banner-config">
          <h3>✨ Điểm nổi bật (Perks)</h3>
          <div class="settings-grid">
            <div class="setting-group">
              <label>Perk 1 - Icon (emoji)</label>
              <input
                type="text"
                v-model="settings.hero_perk1_icon"
                placeholder="🌿"
                maxlength="4"
              />
            </div>
            <div class="setting-group">
              <label>Perk 1 - Tiêu đề</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_perk1_title_vi"
                    placeholder="Eco-luxury stays"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_perk1_title_en"
                    placeholder="Eco-luxury stays"
                  />
                </div>
              </div>
            </div>
            <div class="setting-group full-width">
              <label>Perk 1 - Mô tả</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_perk1_subtitle_vi"
                    placeholder="Không gian xanh, tiện nghi cao cấp"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_perk1_subtitle_en"
                    placeholder="Green spaces, premium amenities"
                  />
                </div>
              </div>
            </div>

            <div class="setting-group">
              <label>Perk 2 - Icon (emoji)</label>
              <input
                type="text"
                v-model="settings.hero_perk2_icon"
                placeholder="🧭"
                maxlength="4"
              />
            </div>
            <div class="setting-group">
              <label>Perk 2 - Tiêu đề</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_perk2_title_vi"
                    placeholder="Local expert tours"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_perk2_title_en"
                    placeholder="Local expert tours"
                  />
                </div>
              </div>
            </div>
            <div class="setting-group full-width">
              <label>Perk 2 - Mô tả</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_perk2_subtitle_vi"
                    placeholder="Khám phá bản sắc địa phương"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_perk2_subtitle_en"
                    placeholder="Discover local culture"
                  />
                </div>
              </div>
            </div>

            <div class="setting-group">
              <label>Perk 3 - Icon (emoji)</label>
              <input
                type="text"
                v-model="settings.hero_perk3_icon"
                placeholder="💳"
                maxlength="4"
              />
            </div>
            <div class="setting-group">
              <label>Perk 3 - Tiêu đề</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_perk3_title_vi"
                    placeholder="Secure payments"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_perk3_title_en"
                    placeholder="Secure payments"
                  />
                </div>
              </div>
            </div>
            <div class="setting-group full-width">
              <label>Perk 3 - Mô tả</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_perk3_subtitle_vi"
                    placeholder="SePay & PayPal được chứng nhận"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_perk3_subtitle_en"
                    placeholder="Certified SePay & PayPal"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Trust Section -->
        <div class="banner-config">
          <h3>🤝 Phần Trust (niềm tin)</h3>
          <div class="settings-grid">
            <div class="setting-group full-width">
              <label>Tiêu đề Trust</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_trust_title_vi"
                    placeholder="5k+ khách hài lòng"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_trust_title_en"
                    placeholder="5k+ happy guests"
                  />
                </div>
              </div>
            </div>

            <div class="setting-group full-width">
              <label>Phụ đề Trust</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input
                    type="text"
                    v-model="settings.hero_trust_subtitle_vi"
                    placeholder="Được đánh giá 4.9/5 trên toàn cầu"
                  />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input
                    type="text"
                    v-model="settings.hero_trust_subtitle_en"
                    placeholder="Rated 4.9/5 worldwide"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Banner Settings -->
      <section v-show="activeTab === 'banners'" class="settings-section">
        <div class="section-header">
          <h2>{{ $t("admin.banner_settings", "Banner quảng cáo") }}</h2>
          <p>
            {{
              $t(
                "admin.banner_settings_desc",
                "Cài đặt các banner hiển thị trên trang chủ",
              )
            }}
          </p>
        </div>

        <!-- Banner 1 -->
        <div class="banner-config">
          <h3>
            Banner 1 - {{ $t("admin.promo_banner", "Banner khuyến mãi") }}
          </h3>

          <div class="settings-grid">
            <div class="setting-group">
              <label>{{ $t("admin.banner_enabled", "Hiển thị") }}</label>
              <label class="switch">
                <input type="checkbox" v-model="settings.banner1_enabled" />
                <span class="slider"></span>
              </label>
            </div>

            <div class="setting-group">
              <label>{{ $t("admin.banner_variant", "Kiểu hiển thị") }}</label>
              <select v-model="settings.banner1_variant">
                <option value="default">Default</option>
                <option value="gradient">Gradient</option>
                <option value="dark">Dark</option>
              </select>
            </div>

            <div class="setting-group full-width">
              <label>{{ $t("admin.banner_title", "Tiêu đề") }}</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input type="text" v-model="settings.banner1_title_vi" />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input type="text" v-model="settings.banner1_title_en" />
                </div>
              </div>
            </div>

            <div class="setting-group full-width">
              <label>{{ $t("admin.banner_subtitle", "Phụ đề") }}</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input type="text" v-model="settings.banner1_subtitle_vi" />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input type="text" v-model="settings.banner1_subtitle_en" />
                </div>
              </div>
            </div>

            <div class="setting-group">
              <label>{{ $t("admin.banner_cta", "Nút CTA") }}</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input type="text" v-model="settings.banner1_cta_vi" />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input type="text" v-model="settings.banner1_cta_en" />
                </div>
              </div>
            </div>

            <div class="setting-group">
              <label>{{ $t("admin.banner_link", "Link CTA") }}</label>
              <input
                type="text"
                v-model="settings.banner1_link"
                placeholder="/rooms"
              />
            </div>

            <div class="setting-group full-width">
              <label>{{ $t("admin.banner_image", "Hình ảnh Banner") }}</label>
              <div
                class="image-picker banner"
                @click="openMediaPicker('banner1_image')"
              >
                <div v-if="settings.banner1_image" class="image-preview">
                  <img :src="settings.banner1_image" alt="Banner 1" />
                  <button
                    type="button"
                    class="remove-btn"
                    @click.stop="settings.banner1_image = ''"
                  >
                    ✕
                  </button>
                </div>
                <div v-else class="image-placeholder">
                  <span class="placeholder-icon">🖼️</span>
                  <span>{{
                    $t(
                      "admin.select_banner_image",
                      "Chọn ảnh banner (1200x400)",
                    )
                  }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Banner 2 -->
        <div class="banner-config">
          <h3>Banner 2 - {{ $t("admin.tour_banner", "Banner Tour") }}</h3>

          <div class="settings-grid">
            <div class="setting-group">
              <label>{{ $t("admin.banner_enabled", "Hiển thị") }}</label>
              <label class="switch">
                <input type="checkbox" v-model="settings.banner2_enabled" />
                <span class="slider"></span>
              </label>
            </div>

            <div class="setting-group">
              <label>{{ $t("admin.banner_variant", "Kiểu hiển thị") }}</label>
              <select v-model="settings.banner2_variant">
                <option value="default">Default</option>
                <option value="gradient">Gradient</option>
                <option value="dark">Dark</option>
              </select>
            </div>

            <div class="setting-group full-width">
              <label>{{ $t("admin.banner_title", "Tiêu đề") }}</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input type="text" v-model="settings.banner2_title_vi" />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input type="text" v-model="settings.banner2_title_en" />
                </div>
              </div>
            </div>

            <div class="setting-group full-width">
              <label>{{ $t("admin.banner_subtitle", "Phụ đề") }}</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input type="text" v-model="settings.banner2_subtitle_vi" />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input type="text" v-model="settings.banner2_subtitle_en" />
                </div>
              </div>
            </div>

            <div class="setting-group">
              <label>{{ $t("admin.banner_cta", "Nút CTA") }}</label>
              <div class="input-lang-group">
                <div class="input-with-flag">
                  <span class="flag">🇻🇳</span>
                  <input type="text" v-model="settings.banner2_cta_vi" />
                </div>
                <div class="input-with-flag">
                  <span class="flag">🇬🇧</span>
                  <input type="text" v-model="settings.banner2_cta_en" />
                </div>
              </div>
            </div>

            <div class="setting-group">
              <label>{{ $t("admin.banner_link", "Link CTA") }}</label>
              <input
                type="text"
                v-model="settings.banner2_link"
                placeholder="/tours"
              />
            </div>

            <div class="setting-group full-width">
              <label>{{ $t("admin.banner_image", "Hình ảnh Banner") }}</label>
              <div
                class="image-picker banner"
                @click="openMediaPicker('banner2_image')"
              >
                <div v-if="settings.banner2_image" class="image-preview">
                  <img :src="settings.banner2_image" alt="Banner 2" />
                  <button
                    type="button"
                    class="remove-btn"
                    @click.stop="settings.banner2_image = ''"
                  >
                    ✕
                  </button>
                </div>
                <div v-else class="image-placeholder">
                  <span class="placeholder-icon">🖼️</span>
                  <span>{{
                    $t(
                      "admin.select_banner_image",
                      "Chọn ảnh banner (1200x400)",
                    )
                  }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- About Page Settings -->
      <section v-show="activeTab === 'about'" class="settings-section">
        <div class="section-header">
          <h2>{{ $t("admin.about_settings", "Trang giới thiệu") }}</h2>
          <p>
            {{
              $t(
                "admin.about_settings_desc",
                "Nội dung hiển thị trên trang Giới thiệu",
              )
            }}
          </p>
        </div>

        <div class="settings-grid">
          <div class="setting-group full-width">
            <label>{{ $t("admin.about_hero_title", "Tiêu đề Hero") }}</label>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input type="text" v-model="settings.about_hero_title_vi" />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input type="text" v-model="settings.about_hero_title_en" />
              </div>
            </div>
          </div>

          <div class="setting-group full-width">
            <label>{{ $t("admin.about_hero_subtitle", "Phụ đề Hero") }}</label>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input type="text" v-model="settings.about_hero_subtitle_vi" />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input type="text" v-model="settings.about_hero_subtitle_en" />
              </div>
            </div>
          </div>

          <div class="setting-group full-width">
            <label>{{ $t("admin.about_hero_image", "Hình ảnh Hero") }}</label>
            <div
              class="image-picker banner"
              @click="openMediaPicker('about_hero_image')"
            >
              <div v-if="settings.about_hero_image" class="image-preview">
                <img :src="settings.about_hero_image" alt="About Hero" />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.about_hero_image = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">🖼️</span>
                <span>{{
                  $t("admin.select_hero_image", "Chọn ảnh Hero")
                }}</span>
              </div>
            </div>
          </div>

          <div class="setting-group full-width">
            <label>{{
              $t("admin.about_story_title", "Tiêu đề câu chuyện")
            }}</label>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input type="text" v-model="settings.about_story_title_vi" />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input type="text" v-model="settings.about_story_title_en" />
              </div>
            </div>
          </div>

          <div class="setting-group full-width">
            <label>{{
              $t("admin.about_story_content", "Nội dung câu chuyện")
            }}</label>
            <div class="input-lang-group vertical">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <textarea
                  v-model="settings.about_story_content_vi"
                  rows="5"
                ></textarea>
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <textarea
                  v-model="settings.about_story_content_en"
                  rows="5"
                ></textarea>
              </div>
            </div>
          </div>

          <div class="setting-group full-width">
            <label>{{
              $t("admin.about_story_image", "Hình ảnh câu chuyện")
            }}</label>
            <div
              class="image-picker"
              @click="openMediaPicker('about_story_image')"
            >
              <div v-if="settings.about_story_image" class="image-preview">
                <img :src="settings.about_story_image" alt="About Story" />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.about_story_image = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">🖼️</span>
                <span>{{
                  $t("admin.select_story_image", "Chọn ảnh câu chuyện")
                }}</span>
              </div>
            </div>
          </div>

          <div class="setting-group">
            <label>{{ $t("admin.about_years", "Số năm kinh nghiệm") }}</label>
            <input
              type="number"
              v-model="settings.about_years_experience"
              min="0"
            />
          </div>

          <div class="setting-group">
            <label>{{ $t("admin.about_guests", "Số khách hài lòng") }}</label>
            <input
              type="number"
              v-model="settings.about_happy_guests"
              min="0"
            />
          </div>

          <div class="setting-group">
            <label>{{
              $t("admin.about_rooms_tours", "Số phòng & tours")
            }}</label>
            <input type="number" v-model="settings.about_rooms_tours" min="0" />
          </div>

          <div class="setting-group">
            <label>{{ $t("admin.about_rating", "Đánh giá trung bình") }}</label>
            <input
              type="number"
              v-model="settings.about_rating"
              min="0"
              max="5"
              step="0.1"
            />
          </div>
        </div>
      </section>

      <!-- Homepage Landing Settings -->
      <section v-show="activeTab === 'homepage'" class="settings-section">
        <div class="section-header">
          <h2>Cài đặt trang chủ Landing</h2>
          <p>
            Bật/tắt giao diện Landing Page (YOLO Ocean Camp) thay cho trang chủ
            mặc định
          </p>
        </div>

        <div class="settings-grid">
          <!-- Toggle bật/tắt -->
          <div class="setting-group full-width">
            <label>Chế độ Landing Page</label>
            <div class="toggle-group">
              <label class="toggle-switch">
                <input
                  type="checkbox"
                  v-model="settings.homepage_landing_enabled"
                />
                <span class="toggle-slider"></span>
                <span class="toggle-label">
                  {{
                    settings.homepage_landing_enabled
                      ? "Bật — Hiển thị Landing Page"
                      : "Tắt — Hiển thị trang chủ mặc định"
                  }}
                </span>
              </label>
              <p class="setting-description">
                {{
                  settings.homepage_landing_enabled
                    ? "Trang chủ sẽ hiển thị giao diện Landing Page với wheel xoay, mây bay và hiệu ứng chữ ký."
                    : "Trang chủ hiển thị giao diện mặc định với Hero Section, phòng, tour, blog..."
                }}
              </p>
            </div>
          </div>

          <!-- Ảnh banner bên trái -->
          <div class="setting-group">
            <label>Ảnh banner (bên trái)</label>
            <div
              class="image-picker"
              @click="openMediaPicker('homepage_banner_image')"
            >
              <div v-if="settings.homepage_banner_image" class="image-preview">
                <img :src="settings.homepage_banner_image" alt="Banner" />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.homepage_banner_image = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">🖼️</span>
                <span>Chọn ảnh banner</span>
              </div>
            </div>
            <p class="setting-description">
              Ảnh hiển thị bên trái trang landing. Nếu để trống sẽ dùng ảnh mặc
              định.
            </p>
          </div>

          <!-- Ảnh wheel bên phải -->
          <div class="setting-group">
            <label>Ảnh wheel xoay (bên phải)</label>
            <div
              class="image-picker"
              @click="openMediaPicker('homepage_wheel_image')"
            >
              <div v-if="settings.homepage_wheel_image" class="image-preview">
                <img :src="settings.homepage_wheel_image" alt="Wheel" />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.homepage_wheel_image = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">🎡</span>
                <span>Chọn ảnh wheel</span>
              </div>
            </div>
            <p class="setting-description">
              Ảnh tròn sẽ xoay liên tục. Nếu để trống sẽ dùng ảnh mặc định.
            </p>
          </div>

          <!-- Ảnh mây 1 -->
          <div class="setting-group">
            <label>Ảnh mây 1</label>
            <div
              class="image-picker"
              @click="openMediaPicker('homepage_cloud_image_1')"
            >
              <div v-if="settings.homepage_cloud_image_1" class="image-preview">
                <img :src="settings.homepage_cloud_image_1" alt="Cloud 1" />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.homepage_cloud_image_1 = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">☁️</span>
                <span>Chọn ảnh mây 1</span>
              </div>
            </div>
          </div>

          <!-- Ảnh mây 2 -->
          <div class="setting-group">
            <label>Ảnh mây 2</label>
            <div
              class="image-picker"
              @click="openMediaPicker('homepage_cloud_image_2')"
            >
              <div v-if="settings.homepage_cloud_image_2" class="image-preview">
                <img :src="settings.homepage_cloud_image_2" alt="Cloud 2" />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.homepage_cloud_image_2 = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">☁️</span>
                <span>Chọn ảnh mây 2</span>
              </div>
            </div>
          </div>

          <!-- Ảnh mây 3 -->
          <div class="setting-group">
            <label>Ảnh mây 3</label>
            <div
              class="image-picker"
              @click="openMediaPicker('homepage_cloud_image_3')"
            >
              <div v-if="settings.homepage_cloud_image_3" class="image-preview">
                <img :src="settings.homepage_cloud_image_3" alt="Cloud 3" />
                <button
                  type="button"
                  class="remove-btn"
                  @click.stop="settings.homepage_cloud_image_3 = ''"
                >
                  ✕
                </button>
              </div>
              <div v-else class="image-placeholder">
                <span class="placeholder-icon">☁️</span>
                <span>Chọn ảnh mây 3</span>
              </div>
            </div>
            <p class="setting-description">
              Upload 3 ảnh mây. Mây sẽ bay ngang qua từ trái sang phải và ngược
              lại, tự đổi vị trí cho nhau.
            </p>
          </div>

          <!-- CTA Text -->
          <div class="setting-group full-width">
            <label>Nút CTA</label>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input
                  type="text"
                  v-model="settings.homepage_cta_text_vi"
                  placeholder="VD: Đặt ngay và tiết kiệm 10%"
                />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input
                  type="text"
                  v-model="settings.homepage_cta_text_en"
                  placeholder="E.g: Book now & save 10%"
                />
              </div>
            </div>
          </div>

          <!-- CTA Sub text -->
          <div class="setting-group full-width">
            <label>Chú thích dưới nút CTA</label>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input
                  type="text"
                  v-model="settings.homepage_cta_sub_vi"
                  placeholder="VD: Chỉ khả dụng trên Website"
                />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input
                  type="text"
                  v-model="settings.homepage_cta_sub_en"
                  placeholder="E.g: Only available on Website"
                />
              </div>
            </div>
          </div>

          <!-- Tagline -->
          <div class="setting-group full-width">
            <label>Tagline (dòng mô tả dưới cùng)</label>
            <div class="input-lang-group">
              <div class="input-with-flag">
                <span class="flag">🇻🇳</span>
                <input
                  type="text"
                  v-model="settings.homepage_tagline_vi"
                  placeholder="VD: Chỉ cần đặt chỗ trước - Không cần thẻ tín dụng..."
                />
              </div>
              <div class="input-with-flag">
                <span class="flag">🇬🇧</span>
                <input
                  type="text"
                  v-model="settings.homepage_tagline_en"
                  placeholder="E.g: Just reserve - No credit card needed..."
                />
              </div>
            </div>
          </div>

          <!-- CTA Link -->
          <div class="setting-group">
            <label>Link nút CTA</label>
            <input
              type="text"
              v-model="settings.homepage_cta_link"
              placeholder="/tours"
            />
            <p class="setting-description">
              Đường dẫn khi bấm nút CTA. VD: /tours, /rooms
            </p>
          </div>

          <!-- Signature Line 1 -->
          <div class="setting-group">
            <label>Chữ ký dòng 1</label>
            <input
              type="text"
              v-model="settings.homepage_signature_line1"
              placeholder="You Only"
            />
            <p class="setting-description">
              Hiển thị trên wheel với hiệu ứng chữ ký
            </p>
          </div>

          <!-- Signature Line 2 -->
          <div class="setting-group">
            <label>Chữ ký dòng 2</label>
            <input
              type="text"
              v-model="settings.homepage_signature_line2"
              placeholder="Live Once"
            />
          </div>
        </div>
      </section>

      <!-- Email Settings -->
      <section v-show="activeTab === 'email'" class="settings-section">
        <div class="section-header">
          <h2>Cấu hình Email & SMTP</h2>
          <p>Cấu hình máy chủ gửi thư SMTP để hệ thống tự động gửi thông báo đặt chỗ đến Admin và Khách hàng.</p>
        </div>

        <div class="settings-grid">
          <div class="setting-group full-width">
            <label>Nhận thông báo qua Email</label>
            <div class="toggle-group">
              <label class="toggle-switch">
                <input
                  type="checkbox"
                  v-model="settings.mail_enable_notifications"
                />
                <span class="toggle-slider"></span>
                <span class="toggle-label">
                  {{ settings.mail_enable_notifications ? 'Bật — Tự động gửi email khi có đặt chỗ mới' : 'Tắt — Không gửi email thông báo' }}
                </span>
              </label>
              <p class="setting-description">
                Khi được bật, hệ thống sẽ gửi email xác nhận cho khách hàng và email thông báo cho quản trị viên khi có phòng đặt, tour đặt hoặc đơn hàng dụng cụ mới.
              </p>
            </div>
          </div>

          <template v-if="settings.mail_enable_notifications">
            <div class="setting-group">
              <label>SMTP Host</label>
              <input
                type="text"
                v-model="settings.mail_host"
                placeholder="VD: smtp.gmail.com hoặc smtp.mailtrap.io"
              />
            </div>

            <div class="setting-group">
              <label>SMTP Port</label>
              <input
                type="number"
                v-model.number="settings.mail_port"
                placeholder="VD: 587, 465, 25"
              />
            </div>

            <div class="setting-group">
              <label>Tài khoản SMTP (Username)</label>
              <input
                type="text"
                v-model="settings.mail_username"
                placeholder="Địa chỉ email hoặc tên tài khoản"
              />
            </div>

            <div class="setting-group">
              <label>Mật khẩu SMTP (Password)</label>
              <input
                type="password"
                v-model="settings.mail_password"
                placeholder="Mật khẩu tài khoản hoặc mật khẩu ứng dụng"
              />
            </div>

            <div class="setting-group">
              <label>Giao thức mã hóa (Encryption)</label>
              <select v-model="settings.mail_encryption" class="form-select">
                <option value="tls">TLS (Cổng 587 / Khuyên dùng)</option>
                <option value="ssl">SSL (Cổng 465)</option>
                <option value="none">Không mã hóa (Cổng 25 / Không an toàn)</option>
              </select>
            </div>

            <div class="setting-group">
              <label>Email gửi đi (From Address)</label>
              <input
                type="email"
                v-model="settings.mail_from_address"
                placeholder="VD: no-reply@website.com"
              />
            </div>

            <div class="setting-group">
              <label>Tên người gửi (From Name)</label>
              <input
                type="text"
                v-model="settings.mail_from_name"
                placeholder="VD: Happy Island Tour"
              />
            </div>

            <div class="setting-group">
              <label>Email nhận thông báo (Receive Address)</label>
              <input
                type="email"
                v-model="settings.mail_receive_address"
                placeholder="Địa chỉ email nhận thông báo của Admin"
              />
            </div>

            <div class="setting-group full-width">
              <div class="test-email-card">
                <div class="test-email-header">
                  <span class="material-icons-outlined text-indigo-400">send</span>
                  <h4>Kiểm tra kết nối gửi thư</h4>
                </div>
                <p class="text-sm text-slate-400">Kiểm tra xem thông tin cấu hình SMTP của bạn đã chính xác và có thể kết nối thành công tới máy chủ hay chưa.</p>
                <div class="test-email-actions">
                  <button
                    type="button"
                    class="btn-secondary"
                    :disabled="testingSmtp || !isSmtpConfigured"
                    @click="testSmtpConnection"
                  >
                    <span v-if="testingSmtp" class="material-icons-outlined animate-spin text-sm mr-1">sync</span>
                    <span v-else class="material-icons-outlined text-sm mr-1">settings_ethernet</span>
                    <span>{{ testingSmtp ? 'Đang kiểm tra...' : 'Kiểm tra cấu hình (Gửi mail test)' }}</span>
                  </button>
                </div>
              </div>
            </div>
          </template>
        </div>
      </section>
    </div>

    <!-- Toast Notification -->
    <transition name="toast">
      <div v-if="toast.show" :class="['toast', toast.type]">
        <span class="toast-icon">{{
          toast.type === "success" ? "✓" : "✕"
        }}</span>
        {{ toast.message }}
      </div>
    </transition>

    <!-- Media Library Modal -->
    <MediaLibraryModal
      v-model="showMediaModal"
      :multiple="false"
      @select="handleMediaSelect"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import MediaLibraryModal from "@/components/admin/MediaLibraryModal.vue";

const { t, locale } = useI18n();

const activeTab = ref("general");
const loading = ref(true);
const saving = ref(false);
const showMediaModal = ref(false);
const currentMediaField = ref("");

const tabs = [
  { key: "general", label: "Cài đặt chung", icon: "settings" },
  { key: "language", label: "Ngôn ngữ", icon: "language" },
  { key: "features", label: "Chức năng", icon: "widgets" },
  { key: "seo", label: "SEO", icon: "search" },
  { key: "contact", label: "Liên hệ", icon: "contact_page" },
  { key: "social", label: "Mạng xã hội", icon: "share" },
  { key: "hero", label: "Hero Section", icon: "home" },
  { key: "banners", label: "Banners", icon: "view_carousel" },
  { key: "about", label: "Giới thiệu", icon: "info" },
  { key: "homepage", label: "Trang chủ", icon: "web" },
  { key: "email", label: "Cấu hình Email", icon: "email" },
];

const settings = reactive({
  // General
  site_name_vi: "",
  site_name_en: "",
  site_tagline_vi: "",
  site_tagline_en: "",
  logo_url: "",
  favicon_url: "",

  // Language
  enable_bilingual: true,
  default_language: "vi",
  available_languages: ["vi", "en"],

  // SEO
  meta_title_vi: "",
  meta_title_en: "",
  meta_description_vi: "",
  meta_description_en: "",
  meta_keywords_vi: "",
  meta_keywords_en: "",
  og_image: "",
  google_analytics_id: "",

  // Contact
  contact_email: "",
  contact_phone: "",
  contact_hotline: "",
  contact_zalo: "",
  contact_whatsapp: "",
  contact_address_vi: "",
  contact_address_en: "",
  google_maps_url: "",
  contact_action_phone_enabled: true,
  contact_action_phone_label_vi: "",
  contact_action_phone_label_en: "",
  contact_action_phone_icon: "",
  contact_action_hotline_enabled: true,
  contact_action_hotline_label_vi: "",
  contact_action_hotline_label_en: "",
  contact_action_hotline_icon: "",
  contact_action_zalo_enabled: true,
  contact_action_zalo_label_vi: "",
  contact_action_zalo_label_en: "",
  contact_action_zalo_icon: "",
  contact_action_whatsapp_enabled: true,
  contact_action_whatsapp_label_vi: "",
  contact_action_whatsapp_label_en: "",
  contact_action_whatsapp_icon: "",
  contact_action_messenger_enabled: true,
  contact_action_messenger_label_vi: "",
  contact_action_messenger_label_en: "",
  contact_action_messenger_icon: "",

  // Social
  social_facebook: "",
  social_instagram: "",
  social_youtube: "",
  social_tiktok: "",
  social_messenger: "",
  social_zalo: "",

  // Banner 1
  banner1_enabled: true,
  banner1_variant: "default",
  banner1_title_vi: "",
  banner1_title_en: "",
  banner1_subtitle_vi: "",
  banner1_subtitle_en: "",
  banner1_cta_vi: "",
  banner1_cta_en: "",
  banner1_link: "",
  banner1_image: "",

  // Banner 2
  banner2_enabled: true,
  banner2_variant: "gradient",
  banner2_title_vi: "",
  banner2_title_en: "",
  banner2_subtitle_vi: "",
  banner2_subtitle_en: "",
  banner2_cta_vi: "",
  banner2_cta_en: "",
  banner2_link: "",
  banner2_image: "",

  // Hero Section
  hero_tagline_vi: "",
  hero_tagline_en: "",
  hero_title_vi: "",
  hero_title_en: "",
  hero_subtitle_vi: "",
  hero_subtitle_en: "",
  hero_image_1: "",
  hero_image_2: "",
  hero_image_3: "",
  hero_gallery_caption_vi: "",
  hero_gallery_caption_en: "",
  hero_floating_title_vi: "",
  hero_floating_title_en: "",
  hero_floating_subtitle_vi: "",
  hero_floating_subtitle_en: "",
  hero_stat1_value: "",
  hero_stat1_label_vi: "",
  hero_stat1_label_en: "",
  hero_stat2_value: "",
  hero_stat2_label_vi: "",
  hero_stat2_label_en: "",
  hero_stat3_value: "",
  hero_stat3_label_vi: "",
  hero_stat3_label_en: "",
  hero_stat4_value: "",
  hero_stat4_label_vi: "",
  hero_stat4_label_en: "",
  hero_perk1_icon: "🌿",
  hero_perk1_title_vi: "",
  hero_perk1_title_en: "",
  hero_perk1_subtitle_vi: "",
  hero_perk1_subtitle_en: "",
  hero_perk2_icon: "🧭",
  hero_perk2_title_vi: "",
  hero_perk2_title_en: "",
  hero_perk2_subtitle_vi: "",
  hero_perk2_subtitle_en: "",
  hero_perk3_icon: "💳",
  hero_perk3_title_vi: "",
  hero_perk3_title_en: "",
  hero_perk3_subtitle_vi: "",
  hero_perk3_subtitle_en: "",
  hero_trust_title_vi: "",
  hero_trust_title_en: "",
  hero_trust_subtitle_vi: "",
  hero_trust_subtitle_en: "",

  // Homepage Landing
  homepage_landing_enabled: false,
  homepage_banner_image: "",
  homepage_wheel_image: "",
  homepage_cloud_image_1: "",
  homepage_cloud_image_2: "",
  homepage_cloud_image_3: "",
  homepage_cta_text_vi: "",
  homepage_cta_text_en: "",
  homepage_cta_sub_vi: "",
  homepage_cta_sub_en: "",
  homepage_tagline_vi: "",
  homepage_tagline_en: "",
  homepage_cta_link: "/tours",
  homepage_signature_line1: "You Only",
  homepage_signature_line2: "Live Once",

  // About
  about_hero_title_vi: "",
  about_hero_title_en: "",
  about_hero_subtitle_vi: "",
  about_hero_subtitle_en: "",
  about_hero_image: "",
  about_story_title_vi: "",
  about_story_title_en: "",
  about_story_content_vi: "",
  about_story_content_en: "",
  about_story_image: "",
  about_years_experience: 0,
  about_happy_guests: 0,
  about_rooms_tours: 0,
  about_rating: 4.9,

  // Feature toggles
  feature_rooms_enabled: true,
  feature_car_rentals_enabled: true,

  // Email SMTP
  mail_enable_notifications: false,
  mail_host: "",
  mail_port: 587,
  mail_username: "",
  mail_password: "",
  mail_encryption: "tls",
  mail_from_address: "",
  mail_from_name: "Happy Island Tour",
  mail_receive_address: "",
});

const toast = reactive({
  show: false,
  type: "success",
  message: "",
});

const showToast = (type, message) => {
  toast.type = type;
  toast.message = message;
  toast.show = true;
  setTimeout(() => {
    toast.show = false;
  }, 3000);
};

const fetchSettings = async () => {
  try {
    loading.value = true;
    const response = await api.get("/admin/settings");

    if (response.data.success && response.data.data) {
      const groupedData = response.data.data;

      // Flatten grouped data into array
      const allSettings = [];
      Object.keys(groupedData).forEach((group) => {
        groupedData[group].forEach((setting) => {
          allSettings.push(setting);
        });
      });

      // Map API response to local settings
      allSettings.forEach((setting) => {
        // Handle bilingual fields - check if this is a bilingual key
        const baseKey = setting.key;

        // Set both _vi and _en values if they exist in local settings
        if (`${baseKey}_vi` in settings) {
          settings[`${baseKey}_vi`] = setting.value_vi || "";
        }
        if (`${baseKey}_en` in settings) {
          settings[`${baseKey}_en`] = setting.value_en || "";
        }

        // Handle non-bilingual fields (use value_vi as default)
        if (baseKey in settings) {
          if (setting.type === "boolean") {
            settings[baseKey] =
              setting.value_vi === "true" || setting.value_vi === true;
          } else if (setting.type === "integer") {
            settings[baseKey] = parseInt(setting.value_vi) || 0;
          } else if (setting.type === "float") {
            settings[baseKey] = parseFloat(setting.value_vi) || 0;
          } else {
            settings[baseKey] = setting.value_vi || "";
          }
        }
      });
    }
  } catch (error) {
    console.error("Failed to fetch settings:", error);
    showToast("error", t("admin.settings_load_error", "Không thể tải cài đặt"));
  } finally {
    loading.value = false;
  }
};

const handleBilingualToggle = () => {
  if (!settings.enable_bilingual) {
    // When bilingual is disabled, set default to English
    settings.default_language = "en";
  }

  showToast(
    "info",
    settings.enable_bilingual
      ? "Đã bật chế độ song ngữ. Website sẽ hiển thị nút chuyển đổi ngôn ngữ."
      : "Đã tắt chế độ song ngữ. Website chỉ hiển thị tiếng Anh.",
  );
};

const saveSettings = async () => {
  try {
    saving.value = true;

    // Transform settings to API format - using key as property name
    const settingsObject = {};
    const processedKeys = new Set();

    // Process all settings
    Object.keys(settings).forEach((key) => {
      // Skip _vi/_en suffixed keys - they'll be handled separately
      if (key.endsWith("_vi") || key.endsWith("_en")) {
        const baseKey = key.replace(/_vi$|_en$/, "");

        // Only process each baseKey once
        if (!processedKeys.has(baseKey)) {
          processedKeys.add(baseKey);
          settingsObject[baseKey] = {
            value_vi: settings[`${baseKey}_vi`] || "",
            value_en: settings[`${baseKey}_en`] || "",
            group: getGroupForKey(baseKey),
            type: "string",
          };
        }
      } else {
        // Non-bilingual settings - skip if already processed as bilingual
        if (!processedKeys.has(key)) {
          let type = "string";
          let value = settings[key];

          if (typeof value === "boolean") {
            type = "boolean";
            value = value ? "true" : "false";
          } else if (typeof value === "number") {
            type = Number.isInteger(value) ? "integer" : "float";
            value = String(value);
          }

          settingsObject[key] = {
            value_vi: value,
            value_en: value,
            group: getGroupForKey(key),
            type: type,
          };
        }
      }
    });

    await api.post("/admin/settings/bulk", { settings: settingsObject });
    showToast(
      "success",
      t("admin.settings_saved", "Đã lưu cài đặt thành công"),
    );
  } catch (error) {
    console.error("Failed to save settings:", error);
    showToast("error", t("admin.settings_save_error", "Không thể lưu cài đặt"));
  } finally {
    saving.value = false;
  }
};

const getGroupForKey = (key) => {
  if (
    key.startsWith("site_") ||
    key.startsWith("logo") ||
    key.startsWith("favicon")
  )
    return "general";
  if (
    key === "enable_bilingual" ||
    key.startsWith("default_language") ||
    key.startsWith("available_languages")
  )
    return "language";
  if (
    key.startsWith("meta_") ||
    key.startsWith("og_") ||
    key.startsWith("google_analytics")
  )
    return "seo";
  if (key.startsWith("contact_")) return "contact";
  if (key.startsWith("social_")) return "social";
  if (key.startsWith("banner")) return "banners";
  if (key.startsWith("about_")) return "about";
  if (key.startsWith("homepage_")) return "homepage";
  if (key.startsWith("hero_")) return "hero";
  if (key.startsWith("feature_")) return "features";
  if (key.startsWith("mail_")) return "email";
  return "general";
};

const isPublicSetting = (key) => {
  // Most settings are public except analytics
  return !key.includes("analytics");
};

// Media picker functions
const openMediaPicker = (field) => {
  currentMediaField.value = field;
  showMediaModal.value = true;
};

const handleMediaSelect = (selectedMedia) => {
  if (selectedMedia && selectedMedia.length > 0) {
    const media = selectedMedia[0];
    settings[currentMediaField.value] = media.url;
  }
  showMediaModal.value = false;
};

const testingSmtp = ref(false);

const isSmtpConfigured = computed(() => {
  return (
    settings.mail_host &&
    settings.mail_port &&
    settings.mail_username &&
    settings.mail_password &&
    settings.mail_from_address &&
    settings.mail_receive_address
  );
});

const testSmtpConnection = async () => {
  if (!isSmtpConfigured.value) return;

  try {
    testingSmtp.value = true;
    const response = await api.post("/admin/settings/test-email", {
      mail_host: settings.mail_host,
      mail_port: settings.mail_port,
      mail_username: settings.mail_username,
      mail_password: settings.mail_password,
      mail_encryption: settings.mail_encryption,
      mail_from_address: settings.mail_from_address,
      mail_from_name: settings.mail_from_name || "Happy Island Tour",
      mail_receive_address: settings.mail_receive_address,
    });

    if (response.data.success) {
      showToast("success", response.data.message || "Gửi email thử nghiệm thành công!");
    } else {
      showToast("error", response.data.message || "Kiểm tra cấu hình thất bại.");
    }
  } catch (error) {
    console.error("SMTP Test Error:", error);
    const msg = error.response?.data?.message || "Không thể kết nối đến máy chủ SMTP.";
    showToast("error", msg);
  } finally {
    testingSmtp.value = false;
  }
};

onMounted(() => {
  fetchSettings();
});
</script>

<style scoped>
.settings-shell {
  min-height: 100vh;
  padding: 2rem;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
}

.settings-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.btn-save {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-save:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
}

.btn-save:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.settings-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  margin-bottom: 2rem;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 1rem;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.6rem 1rem;
  background: transparent;
  color: #94a3b8;
  border: none;
  border-radius: 0.75rem;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.tab-btn:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.tab-btn.active {
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  color: white;
}

.tab-icon {
  font-size: 1rem;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  color: #94a3b8;
  gap: 1rem;
}

.settings-content {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 1.5rem;
  padding: 2rem;
}

.settings-section {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.section-header {
  margin-bottom: 2rem;
}

.section-header h2 {
  color: white;
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.section-header p {
  color: #94a3b8;
}

.settings-grid {
  display: grid;

  .section-header.compact {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.35rem;
    margin-top: 1rem;
  }

  .section-divider {
    height: 1px;
    background: rgba(148, 163, 184, 0.3);
    margin: 2rem 0 1.5rem;
  }

  .toggle-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0.5rem 0 0.75rem;
  }
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.setting-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.setting-group.full-width {
  grid-column: span 2;
}

.setting-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #e2e8f0;
  font-weight: 500;
  font-size: 0.9rem;
}

.setting-group input,
.setting-group select,
.setting-group textarea {
  padding: 0.75rem 1rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.75rem;
  color: white;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.setting-group input:focus,
.setting-group select:focus,
.setting-group textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.setting-group textarea {
  resize: vertical;
  min-height: 100px;
}

.input-lang-group {
  display: flex;
  gap: 1rem;
}

.input-lang-group.vertical {
  flex-direction: column;
}

.input-with-flag {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.75rem;
  transition: all 0.3s ease;
}

.input-with-flag:focus-within {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.input-with-flag .flag {
  font-size: 1.2rem;
}

.input-with-flag input,
.input-with-flag textarea {
  flex: 1;
  padding: 0.75rem 0;
  background: transparent;
  border: none;
  color: white;
}

.input-with-flag input:focus,
.input-with-flag textarea:focus {
  outline: none;
  box-shadow: none;
}

.char-count {
  color: #64748b;
  font-size: 0.8rem;
  text-align: right;
}

/* Image Picker */
.image-picker {
  cursor: pointer;
  border: 2px dashed rgba(255, 255, 255, 0.2);
  border-radius: 1rem;
  overflow: hidden;
  transition: all 0.3s ease;
  height: 150px;
}

.image-picker:hover {
  border-color: #3b82f6;
  background: rgba(59, 130, 246, 0.05);
}

.image-picker.small {
  height: 80px;
  width: 80px;
}

.image-picker.wide {
  height: 180px;
}

.image-picker.banner {
  height: 200px;
}

.image-preview {
  position: relative;
  width: 100%;
  height: 100%;
}

.image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-preview .remove-btn {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(239, 68, 68, 0.9);
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  opacity: 0;
}

.image-picker:hover .remove-btn {
  opacity: 1;
}

.image-preview .remove-btn:hover {
  background: #dc2626;
  transform: scale(1.1);
}

.image-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  gap: 0.5rem;
  color: #64748b;
  font-size: 0.9rem;
}

.image-placeholder .placeholder-icon {
  font-size: 2rem;
  opacity: 0.6;
}

.image-picker.small .image-placeholder {
  gap: 0.25rem;
}

.image-picker.small .image-placeholder .placeholder-icon {
  font-size: 1.5rem;
}

.image-picker.small .image-placeholder span:last-child {
  display: none;
}

/* Social Icons */
.social-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: bold;
}

.social-icon.facebook {
  background: #1877f2;
  color: white;
}

.social-icon.instagram {
  background: linear-gradient(
    45deg,
    #f09433,
    #e6683c,
    #dc2743,
    #cc2366,
    #bc1888
  );
  color: white;
}

.social-icon.youtube {
  background: #ff0000;
  color: white;
}

.social-icon.tiktok {
  background: #000;
  color: white;
}

.social-icon.messenger {
  background: linear-gradient(135deg, #00b2ff, #006aff);
  color: white;
}

.social-icon.zalo {
  background: #0068ff;
  color: white;
}

/* Switch Toggle */
.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 28px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 28px;
  transition: 0.3s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 22px;
  width: 22px;
  left: 3px;
  bottom: 3px;
  background: white;
  border-radius: 50%;
  transition: 0.3s;
}

input:checked + .slider {
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
}

input:checked + .slider:before {
  transform: translateX(22px);
}

/* Toggle Switch for Language Settings */
.toggle-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.toggle-switch {
  display: flex;
  align-items: center;
  gap: 1rem;
  cursor: pointer;
}

.toggle-switch input[type="checkbox"] {
  display: none;
}

.toggle-slider {
  position: relative;
  width: 52px;
  height: 28px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 14px;
  transition: all 0.3s ease;
}

.toggle-slider:before {
  content: "";
  position: absolute;
  top: 2px;
  left: 2px;
  width: 24px;
  height: 24px;
  background: white;
  border-radius: 50%;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked + .toggle-slider {
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.toggle-switch input:checked + .toggle-slider:before {
  transform: translateX(24px);
}

.toggle-label {
  color: white;
  font-weight: 500;
}

.setting-description {
  color: #94a3b8;
  font-size: 0.9rem;
  line-height: 1.4;
  margin: 0;
}

/* Info Card */
.info-card {
  display: flex;
  gap: 1rem;
  padding: 1.5rem;
  background: rgba(59, 130, 246, 0.1);
  border: 1px solid rgba(59, 130, 246, 0.2);
  border-radius: 12px;
}

.info-icon {
  flex-shrink: 0;
  font-size: 1.5rem;
}

.info-content h4 {
  color: white;
  font-size: 1rem;
  margin: 0 0 0.75rem 0;
}

.info-content ul {
  margin: 0;
  padding-left: 1.25rem;
  color: #cbd5e1;
}

.info-content li {
  margin-bottom: 0.5rem;
  line-height: 1.5;
}

.info-content strong {
  color: #e2e8f0;
}

/* Banner Config */
.banner-config {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
}

.banner-config h3 {
  color: white;
  font-size: 1.1rem;
  margin-bottom: 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

/* Spinner */
.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.spinner-lg {
  width: 40px;
  height: 40px;
  border: 3px solid rgba(255, 255, 255, 0.1);
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Toast */
.toast {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 500;
  z-index: 1000;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.toast.success {
  background: linear-gradient(135deg, #059669 0%, #10b981 100%);
  color: white;
}

.toast.error {
  background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
  color: white;
}

.toast-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Responsive */
@media (max-width: 768px) {
  .settings-shell {
    padding: 1rem;
  }

  .settings-header {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }

  .settings-tabs {
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  .tab-btn {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
  }

  .settings-grid {
    grid-template-columns: 1fr;
  }

  .setting-group.full-width {
    grid-column: span 1;
  }

  .input-lang-group {
    flex-direction: column;
  }

  .settings-content {
    padding: 1rem;
  }
}

/* Email Config Test Widget */
.test-email-card {
  background: rgba(30, 41, 59, 0.4);
  border: 1px solid rgba(148, 163, 184, 0.15);
  border-radius: 0.75rem;
  padding: 1.5rem;
  margin-top: 1rem;
}

.test-email-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.test-email-header h4 {
  font-size: 1rem;
  font-weight: 600;
  color: #f1f5f9;
  margin: 0;
}

.test-email-actions {
  margin-top: 1rem;
}

.btn-secondary {
  background: rgba(148, 163, 184, 0.1);
  color: #e2e8f0;
  border: 1px solid rgba(148, 163, 184, 0.2);
  padding: 0.55rem 1.25rem;
  font-weight: 500;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  display: inline-flex;
  align-items: center;
}

.btn-secondary:hover:not(:disabled) {
  background: rgba(148, 163, 184, 0.2);
  border-color: rgba(99, 102, 241, 0.4);
}

.btn-secondary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.mr-1 {
  margin-right: 0.25rem;
}
</style>
