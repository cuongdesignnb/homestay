<template>
  <div class="admin-tours">
    <section class="glass-card table-card">
      <header class="panel-head">
        <div>
          <p class="text-sm text-slate-400">{{ $t("admin.tours_manage") }}</p>
          <h2 class="text-2xl font-semibold text-white">
            {{ $t("admin.manage_tours") }}
          </h2>
        </div>
        <div class="panel-actions">
          <select
            v-model="filters.tour_category_id"
            class="filter-select"
            @change="applyFilter"
          >
            <option value="">{{ $t("common.all", "All") }}</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
          <select
            v-model="filters.status"
            class="filter-select"
            @change="applyFilter"
          >
            <option value="">{{ $t("common.status", "Status") }}</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
          <button class="btn btn-primary" @click="openCreateForm">
            + {{ $t("admin.add_new", "Add new") }}
          </button>
        </div>
      </header>

      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>{{ $t("tours.title") }}</th>
              <th>{{ $t("common.category", "Category") }}</th>
              <th>{{ $t("tours.duration") }}</th>
              <th>{{ $t("tours.per_person") }}</th>
              <th>{{ $t("common.status", "Status") }}</th>
              <th>{{ $t("common.actions", "Actions") }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="tourLoading">
              <td colspan="6" class="text-center text-slate-400 py-6">
                {{ $t("common.loading") }}
              </td>
            </tr>
            <tr v-else-if="!tours.length">
              <td colspan="6" class="text-center text-slate-400 py-6">
                {{ $t("common.no_data") }}
              </td>
            </tr>
            <tr v-for="tour in tours" :key="tour.id">
              <td>
                <p class="font-semibold text-white">
                  {{ tour.name_en || tour.name }}
                </p>
                <small class="text-slate-400">{{
                  tour.departure_location
                }}</small>
              </td>
              <td>
                <span v-if="tour.tour_category" class="badge">{{
                  tour.tour_category.name
                }}</span>
                <span v-else class="text-slate-500">—</span>
              </td>
              <td>{{ formatDuration(tour.duration, tour.duration_unit) }}</td>
              <td>{{ formatCurrency(tour.price_per_person) }}</td>
              <td>
                <span class="status-pill" :class="tour.status">{{
                  tour.status
                }}</span>
              </td>
              <td>
                <div class="table-actions">
                  <button class="btn btn-text" @click="openEditForm(tour)">
                    {{ $t("common.edit") }}
                  </button>
                  <button
                    class="btn btn-text danger"
                    @click="deleteTour(tour.id)"
                  >
                    {{ $t("common.delete") }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <footer
        class="table-footer"
        v-if="pagination.total > pagination.per_page"
      >
        <button
          class="btn btn-text"
          :disabled="pagination.current_page === 1"
          @click="changePage(pagination.current_page - 1)"
        >
          ‹
        </button>
        <span>Page {{ pagination.current_page }} / {{ totalPages }}</span>
        <button
          class="btn btn-text"
          :disabled="pagination.current_page === totalPages"
          @click="changePage(pagination.current_page + 1)"
        >
          ›
        </button>
      </footer>
    </section>

    <section class="glass-card categories-card">
      <header class="panel-head">
        <div>
          <p class="text-sm text-slate-400">
            {{ $t("common.category", "Category") }}
          </p>
          <h3 class="text-xl font-semibold text-white">Tour Categories</h3>
        </div>
      </header>

      <form class="grid gap-3" @submit.prevent="submitCategory">
        <label class="form-field">
          <span>{{ $t("common.name", "Name") }}</span>
          <input v-model="categoryForm.name" type="text" required />
        </label>
        <label class="form-field">
          <span>{{ $t("common.description", "Description") }}</span>
          <textarea v-model="categoryForm.description" rows="3"></textarea>
        </label>
        <label class="form-checkbox">
          <input v-model="categoryForm.is_active" type="checkbox" />
          <span>{{ $t("common.active", "Active") }}</span>
        </label>
        <div class="flex gap-2">
          <button class="btn btn-primary w-full" :disabled="categoryLoading">
            {{ editingCategoryId ? $t("common.update") : $t("common.add") }}
          </button>
          <button
            v-if="editingCategoryId"
            class="btn btn-secondary"
            type="button"
            @click="resetCategoryForm"
          >
            {{ $t("common.cancel") }}
          </button>
        </div>
        <p v-if="categoryMessage" class="text-xs text-emerald-300">
          {{ categoryMessage }}
        </p>
        <p v-if="categoryError" class="text-xs text-rose-300">
          {{ categoryError }}
        </p>
      </form>

      <ul class="category-list">
        <li v-for="category in categories" :key="category.id">
          <div>
            <p class="font-semibold text-white">{{ category.name }}</p>
            <small class="text-slate-400"
              >{{ category.tours_count }} tours</small
            >
          </div>
          <div class="category-actions">
            <span class="badge" :class="{ inactive: !category.is_active }">
              {{
                category.is_active
                  ? $t("common.active", "Active")
                  : $t("common.inactive", "Inactive")
              }}
            </span>
            <button class="btn btn-text" @click="editCategory(category)">
              {{ $t("common.edit") }}
            </button>
            <button
              class="btn btn-text danger"
              @click="deleteCategory(category.id)"
            >
              {{ $t("common.delete") }}
            </button>
          </div>
        </li>
      </ul>
    </section>

    <transition name="fade-scale">
      <div v-if="modalOpen" class="modal-overlay" @click.self="closeModal">
        <section class="modal-panel">
          <header class="modal-header">
            <div>
              <p class="text-sm text-slate-400">
                {{
                  formMode === "create"
                    ? $t("admin.add_new", "Add new")
                    : $t("common.edit")
                }}
              </p>
              <h3 class="text-xl font-semibold text-white">
                {{ $t("tours.title") }}
              </h3>
            </div>
            <button class="btn btn-text" type="button" @click="closeModal">
              ✕
            </button>
          </header>

          <form class="modal-body" @submit.prevent="submitTour">
            <section class="modal-section">
              <h4>{{ $t("common.general", "General information") }}</h4>
              <div class="grid grid-2">
                <label class="form-field">
                  <span>{{ $t("common.category", "Category") }}</span>
                  <select v-model="form.tour_category_id">
                    <option value="">{{ $t("common.none", "None") }}</option>
                    <option
                      v-for="category in categories"
                      :key="category.id"
                      :value="category.id"
                    >
                      {{ category.name }}
                    </option>
                  </select>
                </label>
                <label class="form-field">
                  <span>{{ $t("tours.duration") }}</span>
                  <input
                    v-model.number="form.duration"
                    type="number"
                    min="1"
                    required
                  />
                </label>
                <label class="form-field">
                  <span>{{ $t("tours.duration") }} unit</span>
                  <select v-model="form.duration_unit">
                    <option value="days">Days</option>
                    <option value="hours">Hours</option>
                  </select>
                </label>
                <label class="form-field">
                  <span>{{ $t("tours.per_person") }}</span>
                  <input
                    v-model.number="form.price_per_person"
                    type="number"
                    min="0"
                    required
                  />
                </label>
                <label class="form-field">
                  <span>Discount</span>
                  <input
                    v-model.number="form.discount_price"
                    type="number"
                    min="0"
                    step="0.01"
                  />
                </label>
                <label class="form-field">
                  <span>Max participants</span>
                  <input
                    v-model.number="form.max_participants"
                    type="number"
                    min="1"
                    required
                  />
                </label>
                <label class="form-field">
                  <span>Min participants</span>
                  <input
                    v-model.number="form.min_participants"
                    type="number"
                    min="1"
                    required
                  />
                </label>
                <label class="form-field">
                  <span>Difficulty</span>
                  <select v-model="form.difficulty_level">
                    <option value="easy">Easy</option>
                    <option value="moderate">Moderate</option>
                    <option value="challenging">Challenging</option>
                    <option value="difficult">Difficult</option>
                  </select>
                </label>
                <label class="form-field">
                  <span>{{ $t("common.status", "Status") }}</span>
                  <select v-model="form.status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </label>
                <label class="form-field">
                  <span>Sort Order</span>
                  <input
                    v-model.number="form.sort_order"
                    type="number"
                    min="0"
                    placeholder="Lower = higher priority"
                  />
                </label>
              </div>
              <label class="form-field">
                <span>Departure location</span>
                <input v-model="form.departure_location" type="text" required />
              </label>
              <label class="form-field">
                <span>{{ $t("tours.destination_type", "Destination Type") }}</span>
                <select v-model="form.destination_type">
                  <option value="">{{ $t("common.none", "None") }}</option>
                  <option value="jungle">🌿 {{ $t("tours.dest_jungle", "Jungle (Rừng)") }}</option>
                  <option value="sea">🌊 {{ $t("tours.dest_sea", "Sea (Biển)") }}</option>
                  <option value="fusion">🏝️ {{ $t("tours.dest_fusion", "Fusion (Rừng & Biển)") }}</option>
                  <option value="historical_culture">🏛️ {{ $t("tours.dest_historical", "Historical & Cultural (Lịch sử & Văn hóa)") }}</option>
                  <option value="experience">✨ {{ $t("tours.dest_experience", "Experience (Trải nghiệm)") }}</option>
                </select>
              </label>
              <label class="form-field">
                <span>Age restriction</span>
                <input
                  v-model="form.age_restriction"
                  type="text"
                  placeholder="12+"
                />
              </label>
            </section>

            <section class="modal-section">
              <div class="modal-section-head">
                <h4>{{ $t("common.content", "Content & SEO") }}</h4>
                <div class="locale-tabs">
                  <button
                    v-for="locale in availableLocales"
                    :key="locale"
                    type="button"
                    class="tab-btn"
                    :class="{ active: activeLocale === locale }"
                    @click="activeLocale = locale"
                  >
                    {{ localeLabels[locale] }}
                  </button>
                </div>
              </div>
              <div
                v-for="locale in availableLocales"
                :key="`locale-${locale}`"
                v-show="activeLocale === locale"
                class="locale-panel"
              >
                <label class="form-field">
                  <span
                    >{{ $t("tours.title") }} ({{ localeLabels[locale] }})</span
                  >
                  <input
                    v-model="form.translations[locale].name"
                    type="text"
                    required
                    @input="handleNameInput(locale)"
                  />
                </label>
                <label class="form-field">
                  <span>Slug</span>
                  <input
                    v-model="form.translations[locale].slug"
                    type="text"
                    placeholder="auto-generated"
                  />
                </label>
                <label class="form-field">
                  <span>{{ $t("common.description", "Description") }}</span>
                  <RichTextEditor
                    v-model="form.translations[locale].description"
                    :placeholder="$t('common.description')"
                  />
                </label>
                <div class="seo-grid">
                  <label class="form-field">
                    <span>Meta title</span>
                    <input
                      v-model="form.translations[locale].metaTitle"
                      type="text"
                    />
                  </label>
                  <label class="form-field">
                    <span>Meta description</span>
                    <textarea
                      v-model="form.translations[locale].metaDescription"
                      rows="2"
                    ></textarea>
                  </label>
                  <label class="form-field">
                    <span>Meta keywords</span>
                    <input
                      v-model="form.translations[locale].metaKeywords"
                      type="text"
                      placeholder="tour, travel"
                    />
                  </label>
                </div>
              </div>
            </section>

            <section class="modal-section">
              <h4>{{ $t("tours.includes") }} / {{ $t("tours.excludes") }}</h4>
              <div class="locale-tabs">
                <button
                  v-for="locale in availableLocales"
                  :key="'ie-' + locale"
                  type="button"
                  class="tab-btn"
                  :class="{ active: activeLocale === locale }"
                  @click="activeLocale = locale"
                >
                  {{ localeLabels[locale] }}
                </button>
              </div>
              <div
                v-for="locale in availableLocales"
                :key="`ie-${locale}`"
                v-show="activeLocale === locale"
                class="locale-panel"
              >
                <div class="grid grid-2">
                  <label class="form-field">
                    <span>{{ $t("tours.includes") }} ({{ localeLabels[locale] }})</span>
                    <input
                      v-model="form.translations[locale].includesText"
                      type="text"
                      :placeholder="locale === 'vi' ? 'Hướng dẫn viên, Bữa ăn' : 'Guide, Meals'"
                    />
                  </label>
                  <label class="form-field">
                    <span>{{ $t("tours.excludes") }} ({{ localeLabels[locale] }})</span>
                    <input
                      v-model="form.translations[locale].excludesText"
                      type="text"
                      :placeholder="locale === 'vi' ? 'Vé máy bay, Tip' : 'Flights, Tips'"
                    />
                  </label>
                </div>
                <label class="form-field">
                  <span>Itinerary ({{ localeLabels[locale] }})</span>
                  <textarea
                    v-model="form.translations[locale].itineraryText"
                    rows="3"
                    :placeholder="locale === 'vi' ? 'Ngày 1: Đón khách\nNgày 2: Khám phá' : 'Day 1: Arrival\nDay 2: Explore'"
                  ></textarea>
                </label>
              </div>
            </section>

            <section class="modal-section">
              <div class="form-field gap-3">
                <div class="modal-section-head">
                  <span>{{ $t("media.cover_image", "Ảnh đại diện") }}</span>
                  <div class="gallery-actions">
                    <button
                      type="button"
                      class="btn btn-secondary"
                      @click="showCoverModal = true"
                    >
                      {{
                        form.cover_image
                          ? $t("media.change_cover", "Đổi ảnh")
                          : $t("media.choose_cover", "Chọn ảnh đại diện")
                      }}
                    </button>
                  </div>
                </div>
                <div v-if="form.cover_image" class="cover-wrapper">
                  <img :src="form.cover_image" alt="Cover image" />
                  <div class="cover-actions">
                    <button
                      type="button"
                      class="btn btn-secondary"
                      @click="showCoverModal = true"
                    >
                      {{ $t("media.change_cover", "Đổi ảnh") }}
                    </button>
                    <button
                      type="button"
                      class="btn btn-text danger"
                      @click="clearCoverImage"
                    >
                      ✕
                    </button>
                  </div>
                </div>
                <p v-else class="text-slate-400 text-sm">
                  {{
                    $t(
                      "media.cover_image_hint",
                      "Chọn một ảnh đại diện nổi bật hiển thị đầu trang."
                    )
                  }}
                </p>
              </div>
            </section>

            <section class="modal-section">
              <div class="form-field gap-3">
                <div class="modal-section-head">
                  <span>{{ $t("media.gallery", "Gallery") }}</span>
                  <div class="gallery-actions">
                    <button
                      type="button"
                      class="btn btn-secondary"
                      @click="showAlbumModal = true"
                    >
                      {{ $t("media.choose_album", "Choose album") }}
                    </button>
                    <button
                      type="button"
                      class="btn btn-secondary"
                      @click="showMediaModal = true"
                    >
                      {{ $t("media.open_library", "Open media library") }}
                    </button>
                  </div>
                </div>
                <div v-if="selectedAlbum" class="album-summary">
                  <div>
                    <p class="album-name">{{ selectedAlbum.name }}</p>
                    <small
                      >{{ selectedAlbum.media_count }}
                      {{ $t("media.photos", "images") }}</small
                    >
                  </div>
                  <button
                    type="button"
                    class="btn btn-text"
                    @click="clearAlbum"
                  >
                    ✕
                  </button>
                </div>
                <div v-if="form.images.length" class="image-grid">
                  <div
                    v-for="(image, index) in form.images"
                    :key="image"
                    class="image-chip"
                    :class="{ active: form.cover_image === image }"
                  >
                    <img :src="image" alt="Tour image" />
                    <button
                      type="button"
                      class="chip-remove"
                      @click="removeImage(index)"
                    >
                      ×
                    </button>
                    <button
                      type="button"
                      class="chip-cover-btn"
                      :class="{ active: form.cover_image === image }"
                      @click="setCoverImage(image)"
                    >
                      {{
                        form.cover_image === image
                          ? $t("media.cover", "Ảnh chính")
                          : $t("media.set_cover", "Đặt làm ảnh chính")
                      }}
                    </button>
                  </div>
                </div>
                <p v-else class="text-slate-400 text-sm">
                  {{
                    $t(
                      "media.no_images_selected",
                      "Chưa có ảnh nào, hãy chọn từ thư viện hoặc album."
                    )
                  }}
                </p>
              </div>
            </section>

            <!-- Variants and Price Tiers Editor Section -->
            <section class="modal-section border-t border-slate-700 pt-6">
              <header class="flex justify-between items-center mb-4">
                <h4 class="text-lg font-semibold text-white">{{ $t("tours.variants_tiers_title", "Variants & Pricing Tiers") }}</h4>
                <button type="button" class="btn btn-secondary text-xs py-1 px-3" @click="addVariant">
                  + {{ $t("admin.add_new", "Add new") }}
                </button>
              </header>

              <div v-if="!form.variants || !form.variants.length" class="text-sm text-slate-400 italic">
                No variants configured. The standard base price will be used.
              </div>

              <div v-else class="grid gap-6">
                <div v-for="(variant, vIdx) in form.variants" :key="vIdx" class="p-4 rounded-lg bg-slate-800/50 border border-slate-700">
                  <header class="flex justify-between items-center mb-3">
                    <span class="text-sm font-semibold text-emerald-400">#{{ vIdx + 1 }} - {{ variant.name_en || 'Unnamed Variant' }}</span>
                    <button type="button" class="btn btn-text danger text-xs" @click="removeVariant(vIdx)">
                      ✕ {{ $t("common.delete", "Delete") }}
                    </button>
                  </header>

                  <div class="grid grid-2 gap-3 mb-4">
                    <label class="form-field">
                      <span>Name (EN) *</span>
                      <input v-model="variant.name_en" type="text" required />
                    </label>
                    <label class="form-field">
                      <span>Name (VI)</span>
                      <input v-model="variant.name_vi" type="text" />
                    </label>
                    <label class="form-field">
                      <span>Description (EN)</span>
                      <input v-model="variant.description_en" type="text" />
                    </label>
                    <label class="form-field">
                      <span>Description (VI)</span>
                      <input v-model="variant.description_vi" type="text" />
                    </label>
                    <label class="form-field">
                      <span>Min participants</span>
                      <input v-model.number="variant.min_participants" type="number" min="1" placeholder="Empty = No limit" />
                    </label>
                    <label class="form-field">
                      <span>Max participants</span>
                      <input v-model.number="variant.max_participants" type="number" min="1" placeholder="Empty = No limit" />
                    </label>
                    <label class="form-field">
                      <span>Status</span>
                      <select v-model="variant.status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                      </select>
                    </label>
                    <label class="form-checkbox pt-6">
                      <input v-model="variant.is_default" type="checkbox" @change="onVariantDefaultChange(vIdx)" />
                      <span>Default variant</span>
                    </label>
                  </div>

                  <!-- Tiers for this variant -->
                  <div class="mt-4 pl-4 border-l-2 border-emerald-500/50">
                    <header class="flex justify-between items-center mb-3">
                      <h5 class="text-sm font-semibold text-slate-300">Price Tiers *</h5>
                      <button type="button" class="btn btn-secondary text-xs py-0.5 px-2" @click="addPriceTier(vIdx)">
                        + Add price tier
                      </button>
                    </header>

                    <div v-if="!variant.price_tiers || !variant.price_tiers.length" class="text-xs text-slate-500 italic mb-2">
                      Please configure at least one active price tier.
                    </div>

                    <div v-else class="grid gap-3">
                      <div v-for="(tier, tIdx) in variant.price_tiers" :key="tIdx" class="grid grid-2 gap-2 p-2 rounded bg-slate-900/50 border border-slate-800">
                        <label class="form-field text-xs">
                          <span>Min participants *</span>
                          <input v-model.number="tier.min_participants" type="number" min="1" required />
                        </label>
                        <label class="form-field text-xs">
                          <span>Max participants *</span>
                          <input v-model.number="tier.max_participants" type="number" min="1" required />
                        </label>
                        <label class="form-field text-xs">
                          <span>Pricing type *</span>
                          <select v-model="tier.pricing_type">
                            <option value="per_person">Per person</option>
                            <option value="flat">Flat rate</option>
                          </select>
                        </label>
                        <label class="form-field text-xs">
                          <span>Price (VND) *</span>
                          <input v-model.number="tier.price" type="number" min="0" required />
                        </label>
                        <label class="form-field text-xs">
                          <span>Discount price (VND)</span>
                          <input v-model.number="tier.discount_price" type="number" min="0" />
                        </label>
                        <label class="form-field text-xs">
                          <span>Label (e.g. '1-4 guests')</span>
                          <input v-model="tier.label" type="text" />
                        </label>
                        <label class="form-field text-xs">
                          <span>Status</span>
                          <select v-model="tier.status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                          </select>
                        </label>
                        <div class="flex items-end justify-end">
                          <button type="button" class="btn btn-text danger text-xs py-2 px-3" @click="removePriceTier(vIdx, tIdx)">
                            ✕ Delete tier
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Addons Editor Section -->
            <section class="modal-section border-t border-slate-700 pt-6">
              <header class="flex justify-between items-center mb-4">
                <h4 class="text-lg font-semibold text-white">{{ $t("tours.addons_title", "Tour Addons") }}</h4>
                <button type="button" class="btn btn-secondary text-xs py-1 px-3" @click="addAddon">
                  + {{ $t("admin.add_new", "Add new") }}
                </button>
              </header>

              <div v-if="!form.addons || !form.addons.length" class="text-sm text-slate-400 italic">
                No addons configured for this tour.
              </div>

              <div v-else class="grid gap-4">
                <div v-for="(addon, aIdx) in form.addons" :key="aIdx" class="p-4 rounded-lg bg-slate-800/40 border border-slate-700">
                  <header class="flex justify-between items-center mb-3">
                    <span class="text-sm font-semibold text-sky-400">Addon #{{ aIdx + 1 }}</span>
                    <button type="button" class="btn btn-text danger text-xs" @click="removeAddon(aIdx)">
                      ✕ {{ $t("common.delete", "Delete") }}
                    </button>
                  </header>

                  <div class="grid grid-2 gap-3">
                    <label class="form-field">
                      <span>Name (EN) *</span>
                      <input v-model="addon.name_en" type="text" required />
                    </label>
                    <label class="form-field">
                      <span>Name (VI)</span>
                      <input v-model="addon.name_vi" type="text" />
                    </label>
                    <label class="form-field">
                      <span>Description (EN)</span>
                      <input v-model="addon.description_en" type="text" />
                    </label>
                    <label class="form-field">
                      <span>Description (VI)</span>
                      <input v-model="addon.description_vi" type="text" />
                    </label>
                    <label class="form-field">
                      <span>Price (VND) *</span>
                      <input v-model.number="addon.price" type="number" min="0" required />
                    </label>
                    <label class="form-field">
                      <span>Pricing structure *</span>
                      <select v-model="addon.pricing_type">
                        <option value="per_person">Per person (x guests)</option>
                        <option value="per_booking">Per booking (once)</option>
                      </select>
                    </label>
                    <label class="form-field">
                      <span>Status</span>
                      <select v-model="addon.status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                      </select>
                    </label>
                    <label class="form-field">
                      <span>Sort order</span>
                      <input v-model.number="addon.sort_order" type="number" min="0" />
                    </label>
                  </div>
                </div>
              </div>
            </section>

            <button class="btn btn-primary w-full" :disabled="savingTour">
              {{
                savingTour
                  ? $t("common.loading")
                  : formMode === "create"
                  ? $t("admin.create_tour", "Create tour")
                  : $t("common.update")
              }}
            </button>
            <p v-if="tourMessage" class="text-xs text-emerald-300">
              {{ tourMessage }}
            </p>
            <p v-if="tourError" class="text-xs text-rose-300">
              {{ tourError }}
            </p>
          </form>
        </section>
      </div>
    </transition>

    <MediaLibraryModal v-model="showMediaModal" @select="handleMediaInsert" />
    <MediaLibraryModal
      v-model="showCoverModal"
      :multiple="false"
      @select="handleCoverSelect"
    />
    <MediaAlbumModal v-model="showAlbumModal" @select="applyAlbumSelection" />
  </div>
</template>

<script setup>
import { computed, reactive, ref, onMounted, watch } from "vue";
import api from "@/services/api";
import MediaLibraryModal from "@/components/admin/MediaLibraryModal.vue";
import MediaAlbumModal from "@/components/admin/MediaAlbumModal.vue";
import RichTextEditor from "@/components/common/RichTextEditor.vue";
import { useSettingsStore } from "@/stores/settings";

const tours = ref([]);
const tourLoading = ref(false);
const pagination = reactive({ current_page: 1, per_page: 10, total: 0 });
const filters = reactive({ status: "", tour_category_id: "" });

const categories = ref([]);
const categoryForm = ref({ name: "", description: "", is_active: true });
const categoryLoading = ref(false);
const categoryMessage = ref("");
const categoryError = ref("");
const editingCategoryId = ref(null);

const modalOpen = ref(false);
const formMode = ref("create");
const editingTourId = ref(null);
const showMediaModal = ref(false);
const showCoverModal = ref(false);
const showAlbumModal = ref(false);
const selectedAlbum = ref(null);
const tourMessage = ref("");
const tourError = ref("");
const savingTour = ref(false);
const settingsStore = useSettingsStore();
const availableLocales = computed(() =>
  settingsStore.bilingualEnabled ? ["vi", "en"] : ["en"]
);
const localeLabels = {
  vi: "Tiếng Việt",
  en: "English",
};
const activeLocale = ref("en");
const galleryMeta = ref({});

const createEmptyTranslation = () => ({
  name: "",
  slug: "",
  description: "",
  metaTitle: "",
  metaDescription: "",
  metaKeywords: "",
  includesText: "",
  excludesText: "",
  itineraryText: "",
});

const totalPages = computed(() =>
  pagination.per_page ? Math.ceil(pagination.total / pagination.per_page) : 1
);

const allLocales = ["vi", "en"];

const getLocales = () => availableLocales.value;

const resolveDefaultLocale = () => {
  const preferred = settingsStore.defaultLanguage || "en";
  return getLocales().includes(preferred) ? preferred : getLocales()[0];
};

watch(availableLocales, (next) => {
  if (!next.includes(activeLocale.value)) {
    activeLocale.value = resolveDefaultLocale();
  }
  // Ensure form translations have all locale keys
  if (form.value?.translations) {
    next.forEach((locale) => {
      if (!form.value.translations[locale]) {
        form.value.translations[locale] = createEmptyTranslation();
      }
    });
  }
});

function getDefaultForm() {
  return {
    translations: allLocales.reduce((acc, locale) => {
      acc[locale] = createEmptyTranslation();
      return acc;
    }, {}),
    tour_category_id: "",
    duration: 3,
    duration_unit: "days",
    price_per_person: 950000,
    discount_price: null,
    max_participants: 12,
    min_participants: 2,
    difficulty_level: "easy",
    departure_location: "Happy Island Tour lobby",
    destination_type: "",
    age_restriction: "",
    status: "active",
    sort_order: null,
    cover_image: null,
    cover_media_id: null,
    images: [],
    media_album_id: null,
    variants: [],
    addons: [],
  };
}

const form = ref(getDefaultForm());

const addVariant = () => {
  if (!form.value.variants) form.value.variants = [];
  form.value.variants.push({
    name_en: "",
    name_vi: "",
    description_en: "",
    description_vi: "",
    status: "active",
    sort_order: form.value.variants.length,
    is_default: form.value.variants.length === 0,
    min_participants: null,
    max_participants: null,
    price_tiers: []
  });
};

const removeVariant = (index) => {
  form.value.variants.splice(index, 1);
  if (form.value.variants.length && !form.value.variants.some(v => v.is_default)) {
    form.value.variants[0].is_default = true;
  }
};

const onVariantDefaultChange = (index) => {
  if (form.value.variants[index].is_default) {
    form.value.variants.forEach((v, i) => {
      if (i !== index) v.is_default = false;
    });
  } else {
    const defaults = form.value.variants.filter(v => v.is_default);
    if (defaults.length === 0) {
      form.value.variants[index].is_default = true;
    }
  }
};

const addPriceTier = (variantIndex) => {
  if (!form.value.variants[variantIndex].price_tiers) {
    form.value.variants[variantIndex].price_tiers = [];
  }
  form.value.variants[variantIndex].price_tiers.push({
    min_participants: 1,
    max_participants: 10,
    pricing_type: "per_person",
    price: 950000,
    discount_price: null,
    label: "",
    sort_order: form.value.variants[variantIndex].price_tiers.length,
    status: "active"
  });
};

const removePriceTier = (variantIndex, tierIndex) => {
  form.value.variants[variantIndex].price_tiers.splice(tierIndex, 1);
};

const addAddon = () => {
  if (!form.value.addons) form.value.addons = [];
  form.value.addons.push({
    name_en: "",
    name_vi: "",
    description_en: "",
    description_vi: "",
    price: 150000,
    pricing_type: "per_person",
    sort_order: form.value.addons.length,
    status: "active"
  });
};

const removeAddon = (index) => {
  form.value.addons.splice(index, 1);
};

const hydrateTranslationsFromTour = (tour) =>
  allLocales.reduce((acc, locale) => {
    acc[locale] = {
      name: tour[`name_${locale}`] ?? tour.name ?? "",
      slug: tour[`slug_${locale}`] ?? "",
      description: tour[`description_${locale}`] ?? tour.description ?? "",
      metaTitle: tour[`meta_title_${locale}`] ?? "",
      metaDescription: tour[`meta_description_${locale}`] ?? "",
      metaKeywords: tour[`meta_keywords_${locale}`] ?? "",
      includesText: (tour[`includes_${locale}`] || tour.includes || []).join(", "),
      excludesText: (tour[`excludes_${locale}`] || tour.excludes || []).join(", "),
      itineraryText: (tour[`itinerary_${locale}`] || tour.itinerary || []).join("\n"),
    };
    return acc;
  }, {});

const slugify = (value = "") =>
  (value || "")
    .toString()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/(^-|-$)+/g, "");

const handleNameInput = (locale) => {
  const translation = form.value.translations[locale];
  if (!translation.slug) {
    translation.slug = slugify(translation.name);
  }
};

const formatCurrency = (value) => {
  if (value == null) return "—";
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
    maximumFractionDigits: 0,
  }).format(value);
};

const formatDuration = (duration, unit) => {
  if (!duration) return "—";
  return `${duration} ${unit}`;
};

const fetchTours = async (page = pagination.current_page) => {
  tourLoading.value = true;
  try {
    const params = new URLSearchParams({ page, per_page: pagination.per_page });
    if (filters.status) params.append("status", filters.status);
    if (filters.tour_category_id)
      params.append("tour_category_id", filters.tour_category_id);

    const response = await api.get(`/admin/tours?${params.toString()}`);
    tours.value = response.data.data || [];
    pagination.current_page = response.data.current_page;
    pagination.per_page = response.data.per_page;
    pagination.total = response.data.total;
  } catch (err) {
    tourError.value = err.response?.data?.message || "Failed to load tours";
  } finally {
    tourLoading.value = false;
  }
};

const fetchCategories = async () => {
  categoryLoading.value = true;
  try {
    const response = await api.get("/admin/tour-categories");
    categories.value = response.data || [];
  } catch (err) {
    categoryError.value =
      err.response?.data?.message || "Failed to load categories";
  } finally {
    categoryLoading.value = false;
  }
};

const openCreateForm = () => {
  formMode.value = "create";
  editingTourId.value = null;
  form.value = getDefaultForm();
  galleryMeta.value = {};
  tourMessage.value = "";
  tourError.value = "";
  selectedAlbum.value = null;
  activeLocale.value = resolveDefaultLocale();
  modalOpen.value = true;
};

const openEditForm = (tour) => {
  formMode.value = "edit";
  editingTourId.value = tour.id;
  galleryMeta.value = {};
  form.value = {
    translations: hydrateTranslationsFromTour(tour),
    tour_category_id: tour.tour_category_id || tour.tour_category?.id || "",
    duration: tour.duration || 1,
    duration_unit: tour.duration_unit || "days",
    price_per_person: tour.price_per_person || 0,
    discount_price: tour.discount_price,
    max_participants: tour.max_participants || 1,
    min_participants: tour.min_participants || 1,
    difficulty_level: tour.difficulty_level || "easy",
    departure_location: tour.departure_location || "",
    destination_type: tour.destination_type || "",
    age_restriction: tour.age_restriction || "",
    status: tour.status || "active",
    sort_order: tour.sort_order || null,
    images: tour.images || [],
    cover_image: tour.cover_image || (tour.images?.[0] ?? null),
    cover_media_id: tour.cover_media_id || null,
    media_album_id: tour.media_album_id || null,
    variants: (tour.variants || []).map(v => ({
      id: v.id,
      name_en: v.name_en || v.name || "",
      name_vi: v.name_vi || v.name || "",
      description_en: v.description_en || v.description || "",
      description_vi: v.description_vi || v.description || "",
      status: v.status || "active",
      sort_order: v.sort_order || 0,
      is_default: !!v.is_default,
      min_participants: v.min_participants || null,
      max_participants: v.max_participants || null,
      price_tiers: (v.price_tiers || []).map(pt => ({
        id: pt.id,
        min_participants: pt.min_participants,
        max_participants: pt.max_participants,
        pricing_type: pt.pricing_type || "per_person",
        price: pt.price,
        discount_price: pt.discount_price || null,
        label: pt.label || "",
        sort_order: pt.sort_order || 0,
        status: pt.status || "active"
      }))
    })),
    addons: (tour.addons || []).map(a => ({
      id: a.id,
      name_en: a.name_en || a.name || "",
      name_vi: a.name_vi || a.name || "",
      description_en: a.description_en || a.description || "",
      description_vi: a.description_vi || a.description || "",
      price: a.price,
      pricing_type: a.pricing_type || "per_person",
      sort_order: a.sort_order || 0,
      status: a.status || "active"
    }))
  };
  tourMessage.value = "";
  tourError.value = "";
  activeLocale.value = resolveDefaultLocale();
  modalOpen.value = true;

  if (form.value.media_album_id) {
    fetchAlbumDetails(form.value.media_album_id);
  } else {
    selectedAlbum.value = null;
  }

  if (!form.value.cover_image && form.value.images.length) {
    form.value.cover_image = form.value.images[0];
  }
};

const closeModal = () => {
  modalOpen.value = false;
  form.value = getDefaultForm();
  editingTourId.value = null;
  selectedAlbum.value = null;
  galleryMeta.value = {};
};

const payloadFromForm = () => {
  const payload = {
    tour_category_id: form.value.tour_category_id || null,
    duration: form.value.duration,
    duration_unit: form.value.duration_unit,
    price_per_person: form.value.price_per_person,
    discount_price: form.value.discount_price || null,
    max_participants: form.value.max_participants,
    min_participants: form.value.min_participants,
    difficulty_level: form.value.difficulty_level,
    departure_location: form.value.departure_location,
    destination_type: form.value.destination_type || null,
    age_restriction: form.value.age_restriction || null,
    status: form.value.status,
    sort_order: form.value.sort_order || null,
    images: form.value.images,
    cover_image: form.value.cover_image,
    cover_media_id: form.value.cover_media_id,
    media_album_id: form.value.media_album_id,
    variants: form.value.variants || [],
    addons: form.value.addons || [],
  };

  getLocales().forEach((locale) => {
    const translation = form.value.translations[locale];
    payload[`name_${locale}`] = translation.name;
    payload[`slug_${locale}`] = translation.slug || null;
    payload[`description_${locale}`] = translation.description;
    payload[`meta_title_${locale}`] = translation.metaTitle || null;
    payload[`meta_description_${locale}`] = translation.metaDescription || null;
    payload[`meta_keywords_${locale}`] = translation.metaKeywords || null;
    payload[`includes_${locale}`] = (translation.includesText || "")
      .split(",")
      .map((item) => item.trim())
      .filter(Boolean);
    payload[`excludes_${locale}`] = (translation.excludesText || "")
      .split(",")
      .map((item) => item.trim())
      .filter(Boolean);
    payload[`itinerary_${locale}`] = (translation.itineraryText || "")
      .split("\n")
      .map((line) => line.trim())
      .filter(Boolean);
  });

  // Set base columns from English as default
  const enT = form.value.translations["en"] || form.value.translations[getLocales()[0]];
  if (enT) {
    payload.includes = (enT.includesText || "")
      .split(",")
      .map((item) => item.trim())
      .filter(Boolean);
    payload.excludes = (enT.excludesText || "")
      .split(",")
      .map((item) => item.trim())
      .filter(Boolean);
    payload.itinerary = (enT.itineraryText || "")
      .split("\n")
      .map((line) => line.trim())
      .filter(Boolean);
  }

  return payload;
};

const submitTour = async () => {
  savingTour.value = true;
  tourMessage.value = "";
  tourError.value = "";
  try {
    if (formMode.value === "edit" && editingTourId.value) {
      await api.put(`/admin/tours/${editingTourId.value}`, payloadFromForm());
      tourMessage.value = "Tour updated successfully";
    } else {
      await api.post("/admin/tours", payloadFromForm());
      tourMessage.value = "Tour created successfully";
    }
    await fetchTours();
    closeModal();
  } catch (err) {
    tourError.value = err.response?.data?.message || "Unable to save tour";
  } finally {
    savingTour.value = false;
  }
};

const deleteTour = async (tourId) => {
  if (!confirm("Delete this tour?")) return;
  try {
    await api.delete(`/admin/tours/${tourId}`);
    await fetchTours();
  } catch (err) {
    tourError.value = err.response?.data?.message || "Unable to delete tour";
  }
};

const appendGalleryItems = (items) => {
  if (!items?.length) return;
  const meta = { ...galleryMeta.value };
  const existing = new Set(form.value.images);
  items.forEach((item) => {
    if (!item?.url) return;
    meta[item.url] = item.id;
    if (!existing.has(item.url)) {
      form.value.images.push(item.url);
      existing.add(item.url);
    }
  });
  galleryMeta.value = meta;
};

const ensureImageInGallery = (imageUrl) => {
  if (!imageUrl) return;
  const filtered = form.value.images.filter((url) => url !== imageUrl);
  form.value.images = [imageUrl, ...filtered];
};

const setCoverImage = (imageUrl) => {
  if (!imageUrl) {
    clearCoverImage();
    return;
  }
  ensureImageInGallery(imageUrl);
  form.value.cover_image = imageUrl;
  form.value.cover_media_id = galleryMeta.value[imageUrl] ?? null;
};

const clearCoverImage = () => {
  form.value.cover_image = null;
  form.value.cover_media_id = null;
};

const handleMediaInsert = (items) => {
  appendGalleryItems(items);
  form.value.media_album_id = null;
  selectedAlbum.value = null;
  if (!form.value.cover_image && form.value.images.length) {
    setCoverImage(form.value.images[0]);
  }
};

const handleCoverSelect = (items) => {
  const item = items?.[0];
  if (!item) return;
  appendGalleryItems([item]);
  form.value.media_album_id = null;
  selectedAlbum.value = null;
  setCoverImage(item.url);
};

const removeImage = (index) => {
  const removed = form.value.images[index];
  form.value.images = form.value.images.filter((_, idx) => idx !== index);
  if (removed) {
    const meta = { ...galleryMeta.value };
    delete meta[removed];
    galleryMeta.value = meta;
  }

  if (!form.value.images.length) {
    clearCoverImage();
    form.value.media_album_id = null;
    selectedAlbum.value = null;
    return;
  }

  if (removed && removed === form.value.cover_image) {
    setCoverImage(form.value.images[0]);
  }
};

const getAlbumCoverItem = (album) => {
  if (!album?.media_items?.length) return null;
  return (
    album.media_items.find((item) => item.id === album.cover_media_id) ||
    album.media_items[0]
  );
};

const mapAlbumMedia = (album, options = { replaceGallery: true }) => {
  if (!album) return;
  const replaceGallery = options?.replaceGallery ?? true;
  const nextMeta = replaceGallery ? {} : { ...galleryMeta.value };
  const urls = [];
  (album.media_items || []).forEach((item) => {
    nextMeta[item.url] = item.id;
    if (replaceGallery) {
      urls.push(item.url);
    }
  });

  galleryMeta.value = nextMeta;

  if (replaceGallery) {
    form.value.images = urls;
  }
};

const applyAlbumSelection = (album) => {
  if (!album) return;
  form.value.media_album_id = album.id;
  selectedAlbum.value = album;
  mapAlbumMedia(album, { replaceGallery: true });
  const coverItem = getAlbumCoverItem(album);
  if (coverItem) {
    setCoverImage(coverItem.url);
    form.value.cover_media_id = coverItem.id ?? form.value.cover_media_id;
  } else {
    clearCoverImage();
  }
};

const clearAlbum = () => {
  form.value.media_album_id = null;
  selectedAlbum.value = null;
};

const fetchAlbumDetails = async (albumId) => {
  try {
    const { data } = await api.get(`/admin/media-albums/${albumId}`);
    selectedAlbum.value = data;
    mapAlbumMedia(data, { replaceGallery: false });
    if (!form.value.cover_image) {
      const coverItem = getAlbumCoverItem(data);
      if (coverItem) {
        setCoverImage(coverItem.url);
      }
    }
  } catch (error) {
    console.error("Failed to fetch album", error);
  }
};

const changePage = (page) => {
  if (page < 1 || page > totalPages.value) return;
  fetchTours(page);
};

const applyFilter = () => {
  pagination.current_page = 1;
  fetchTours(1);
};

const submitCategory = async () => {
  categoryLoading.value = true;
  categoryMessage.value = "";
  categoryError.value = "";
  try {
    const payload = {
      name: categoryForm.value.name,
      description: categoryForm.value.description || null,
      is_active: categoryForm.value.is_active,
    };
    if (editingCategoryId.value) {
      await api.put(
        `/admin/tour-categories/${editingCategoryId.value}`,
        payload
      );
      categoryMessage.value = "Category updated";
    } else {
      await api.post("/admin/tour-categories", payload);
      categoryMessage.value = "Category created";
    }
    resetCategoryForm();
    await fetchCategories();
  } catch (err) {
    categoryError.value =
      err.response?.data?.message || "Unable to save category";
  } finally {
    categoryLoading.value = false;
  }
};

const editCategory = (category) => {
  editingCategoryId.value = category.id;
  categoryForm.value = {
    name: category.name,
    description: category.description || "",
    is_active: !!category.is_active,
  };
};

const resetCategoryForm = () => {
  editingCategoryId.value = null;
  categoryForm.value = { name: "", description: "", is_active: true };
};

const deleteCategory = async (categoryId) => {
  if (!confirm("Delete this category?")) return;
  try {
    await api.delete(`/admin/tour-categories/${categoryId}`);
    await fetchCategories();
  } catch (err) {
    categoryError.value =
      err.response?.data?.message || "Unable to delete category";
  }
};

onMounted(() => {
  settingsStore.fetchSettings();
  fetchTours();
  fetchCategories();
});
</script>

<style scoped>
.admin-tours {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  position: relative;
}

.glass-card {
  border-radius: 1.5rem;
  padding: 2rem;
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(148, 163, 184, 0.2);
  box-shadow: 0 20px 45px rgba(2, 6, 23, 0.55);
}

.table-card {
  width: 100%;
}

.panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}

.panel-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.filter-select {
  border-radius: 999px;
  padding: 0.5rem 1.25rem;
  background: rgba(15, 23, 42, 0.75);
  border: 1px solid rgba(148, 163, 184, 0.3);
  color: #e2e8f0;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 720px;
}

th,
td {
  padding: 0.85rem 0.5rem;
  text-align: left;
  border-bottom: 1px solid rgba(148, 163, 184, 0.15);
}

th {
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #94a3b8;
}

.badge {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  padding: 0.2rem 0.75rem;
  font-size: 0.75rem;
  background: rgba(59, 130, 246, 0.15);
  color: #93c5fd;
}

.badge.inactive {
  background: rgba(248, 113, 113, 0.15);
  color: #fecaca;
}

.status-pill {
  padding: 0.2rem 0.8rem;
  border-radius: 999px;
  font-size: 0.75rem;
  text-transform: capitalize;
}

.status-pill.active {
  background: rgba(34, 197, 94, 0.2);
  color: #86efac;
}

.status-pill.inactive {
  background: rgba(248, 113, 113, 0.2);
  color: #fecaca;
}

.table-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-text {
  background: transparent;
  border: none;
  color: #93c5fd;
  cursor: pointer;
  padding: 0.25rem 0.5rem;
}

.btn-text.danger {
  color: #fca5a5;
}

.table-footer {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 1rem;
  margin-top: 1rem;
  color: #94a3b8;
}

.categories-card {
  width: 100%;
}

.category-list {
  margin-top: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.category-list li {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(148, 163, 184, 0.15);
  padding-bottom: 1rem;
}

.category-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(2, 6, 23, 0.75);
  backdrop-filter: blur(6px);
  padding: 2rem;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  overflow-y: auto;
  z-index: 40;
}

.modal-panel {
  width: min(1080px, 100%);
  margin-top: 1rem;
  background: rgba(15, 23, 42, 0.96);
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 1.5rem;
  box-shadow: 0 30px 60px rgba(2, 6, 23, 0.65);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid rgba(148, 163, 184, 0.15);
}

.modal-body {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.modal-section {
  padding: 1.25rem;
  border-radius: 1rem;
  background: rgba(30, 41, 59, 0.55);
  border: 1px solid rgba(148, 163, 184, 0.15);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.modal-section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.gallery-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.album-summary {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 0.9rem;
  border: 1px solid rgba(59, 130, 246, 0.35);
  background: rgba(59, 130, 246, 0.1);
}

.album-name {
  font-weight: 600;
  color: #e2e8f0;
}

.cover-wrapper {
  position: relative;
  border-radius: 1rem;
  overflow: hidden;
  border: 1px solid rgba(148, 163, 184, 0.4);
  min-height: 220px;
}

.cover-wrapper img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.cover-actions {
  position: absolute;
  bottom: 0.75rem;
  left: 0.75rem;
  right: 0.75rem;
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
}

.locale-tabs {
  display: inline-flex;
  background: rgba(15, 23, 42, 0.6);
  border-radius: 999px;
  padding: 0.25rem;
  border: 1px solid rgba(148, 163, 184, 0.25);
}

.tab-btn {
  border: none;
  background: transparent;
  color: #94a3b8;
  padding: 0.35rem 1rem;
  border-radius: 999px;
  font-size: 0.85rem;
  cursor: pointer;
}

.tab-btn.active {
  background: #2563eb;
  color: #fff;
}

.admin-tours :deep(.ql-editor) {
  color: #e2e8f0;
}

.locale-panel {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.seo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 0.75rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  font-size: 0.9rem;
  color: #cbd5f5;
}

.form-field input,
.form-field textarea,
.form-field select {
  background: rgba(15, 23, 42, 0.65);
  border: 1px solid rgba(148, 163, 184, 0.3);
  border-radius: 0.75rem;
  padding: 0.75rem 1rem;
  color: #e2e8f0;
}

.form-checkbox {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  color: #cbd5f5;
}

.grid-2 {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
}

.image-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.image-chip {
  position: relative;
  width: 110px;
  height: 110px;
  border-radius: 0.75rem;
  overflow: hidden;
  border: 1px solid rgba(148, 163, 184, 0.3);
}

.image-chip.active {
  border-color: rgba(79, 70, 229, 0.9);
  box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.35);
}

.image-chip img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.chip-remove {
  position: absolute;
  top: 4px;
  right: 4px;
  background: rgba(15, 23, 42, 0.85);
  border: none;
  color: #f87171;
  font-size: 0.85rem;
  padding: 0.1rem 0.35rem;
  cursor: pointer;
  border-radius: 999px;
}

.chip-cover-btn {
  position: absolute;
  left: 6px;
  right: 6px;
  bottom: 6px;
  border: none;
  border-radius: 0.65rem;
  padding: 0.25rem 0.5rem;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: rgba(15, 23, 42, 0.75);
  color: #cbd5f5;
  cursor: pointer;
}

.chip-cover-btn.active {
  background: rgba(79, 70, 229, 0.9);
  color: #fff;
}

.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.97);
}

@media (max-width: 1024px) {
  .modal-overlay {
    padding: 1.5rem;
  }

  .modal-body {
    padding: 1.5rem;
  }
}

@media (max-width: 640px) {
  .modal-overlay {
    padding: 1rem;
  }

  .modal-body {
    padding: 1.25rem;
  }

  .modal-section {
    padding: 1rem;
  }

  .locale-tabs {
    width: 100%;
    justify-content: space-between;
  }
}

@media (min-width: 1024px) {
  .admin-tours {
    flex-direction: row;
  }

  .table-card {
    flex: 2;
  }

  .categories-card {
    flex: 1;
  }
}
</style>
