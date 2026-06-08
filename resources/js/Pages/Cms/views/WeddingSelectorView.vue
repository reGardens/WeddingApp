<template>
  <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Wedding CMS</h1>
        <p class="mt-2 text-sm text-gray-600">
          Kelola undangan pernikahan Anda
        </p>
      </div>

      <!-- Error message from redirect -->
      <div v-if="errorMessage" class="mb-6">
        <ErrorMessage :message="errorMessage" />
      </div>

      <!-- Legacy data migration banner -->
      <div
        v-if="showLegacyBanner"
        class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4"
      >
        <div class="flex items-start gap-3">
          <span
            class="text-amber-500 text-lg leading-none flex-shrink-0"
            aria-hidden="true"
            >📦</span
          >
          <div class="flex-1">
            <h3 class="text-sm font-semibold text-amber-800">
              Data Lama Terdeteksi
            </h3>
            <p class="mt-1 text-sm text-amber-700">
              Ditemukan data pernikahan dari versi sebelumnya. Anda dapat
              memigrasikan data tersebut ke sistem baru.
            </p>

            <!-- Migration slug input form -->
            <div v-if="showMigrationForm" class="mt-3 space-y-3">
              <div>
                <label
                  for="migration-slug"
                  class="block text-sm font-medium text-amber-800"
                >
                  Slug untuk data lama
                </label>
                <input
                  id="migration-slug"
                  v-model="migrationSlug"
                  type="text"
                  placeholder="Contoh: budi-ani"
                  class="mt-1 block w-full rounded-md border border-amber-300 px-3 py-1.5 text-sm shadow-sm font-mono focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                  :class="{
                    'border-red-300 focus:border-red-500 focus:ring-red-500':
                      migrationSlugError,
                  }"
                  @input="onMigrationSlugInput"
                />
                <p v-if="migrationSlugError" class="mt-1 text-xs text-red-600">
                  {{ migrationSlugError }}
                </p>
                <p v-else class="mt-1 text-xs text-amber-600">
                  Huruf kecil, angka, dan tanda hubung. Minimal 3 karakter.
                </p>
              </div>

              <div
                v-if="migrationError"
                class="rounded-md bg-red-50 border border-red-200 p-3"
              >
                <p class="text-sm text-red-700">{{ migrationError }}</p>
              </div>

              <div
                v-if="migrationSuccess"
                class="rounded-md bg-green-50 border border-green-200 p-3"
              >
                <p class="text-sm text-green-700">{{ migrationSuccess }}</p>
              </div>

              <div class="flex items-center gap-2">
                <button
                  type="button"
                  :disabled="!canMigrate || migrating"
                  class="inline-flex items-center gap-1.5 rounded-md bg-amber-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  @click="executeMigration"
                >
                  <span v-if="migrating">Memigrasikan...</span>
                  <span v-else>Mulai Migrasi</span>
                </button>
                <button
                  type="button"
                  class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-amber-700 hover:text-amber-900 transition-colors"
                  @click="showMigrationForm = false"
                >
                  Batal
                </button>
              </div>
            </div>

            <!-- Show migration button when form is hidden -->
            <button
              v-else
              type="button"
              class="mt-3 inline-flex items-center gap-1.5 rounded-md bg-amber-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition-colors"
              @click="handleMigration"
            >
              Migrasikan Data
            </button>
          </div>
        </div>
      </div>

      <!-- Loading state -->
      <div v-if="loading" class="flex justify-center py-12">
        <LoadingIndicator text="Memuat daftar pernikahan..." />
      </div>

      <template v-else>
        <!-- Wedding list -->
        <div v-if="registry.length > 0" class="mb-8">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">
            Daftar Pernikahan
          </h2>
          <div class="grid gap-4 sm:grid-cols-2">
            <div
              v-for="wedding in registry"
              :key="wedding.id"
              class="relative rounded-lg border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition-shadow"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                  <h3 class="text-base font-semibold text-gray-900 truncate">
                    {{ wedding.label }}
                  </h3>
                  <p class="mt-1 text-sm text-gray-500 font-mono">
                    {{ wedding.slug }}
                  </p>
                  <p class="mt-1 text-xs text-gray-400">
                    Dibuat: {{ formatDate(wedding.createdAt) }}
                  </p>
                </div>
                <button
                  type="button"
                  class="ml-2 p-1.5 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors"
                  aria-label="Hapus pernikahan"
                  @click="confirmDelete(wedding)"
                >
                  <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                  </svg>
                </button>
              </div>
              <button
                type="button"
                class="mt-4 w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors"
                @click="selectWedding(wedding.slug)"
              >
                Kelola Undangan
              </button>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <div v-else class="text-center py-12 mb-8">
          <span class="text-4xl" aria-hidden="true">💒</span>
          <h3 class="mt-4 text-lg font-medium text-gray-900">
            Belum ada pernikahan
          </h3>
          <p class="mt-2 text-sm text-gray-500">
            Buat pernikahan pertama Anda untuk memulai.
          </p>
        </div>

        <!-- Create new wedding form -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">
            Buat Pernikahan Baru
          </h2>
          <form @submit.prevent="handleCreate" class="space-y-4">
            <div>
              <label
                for="wedding-label"
                class="block text-sm font-medium text-gray-700"
              >
                Nama Pernikahan
              </label>
              <input
                id="wedding-label"
                v-model="form.label"
                type="text"
                placeholder="Contoh: Pernikahan Budi & Ani"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                required
              />
            </div>
            <div>
              <label
                for="wedding-slug"
                class="block text-sm font-medium text-gray-700"
              >
                Slug (URL identifier)
              </label>
              <input
                id="wedding-slug"
                v-model="form.slug"
                type="text"
                placeholder="Contoh: budi-ani"
                class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm font-mono focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                :class="{
                  'border-red-300 focus:border-red-500 focus:ring-red-500':
                    slugError,
                }"
                required
                @input="onSlugInput"
              />
              <p v-if="slugError" class="mt-1 text-xs text-red-600">
                {{ slugError }}
              </p>
              <p v-else class="mt-1 text-xs text-gray-500">
                Huruf kecil, angka, dan tanda hubung. Minimal 3 karakter.
              </p>
            </div>

            <div
              v-if="createError"
              class="rounded-md bg-red-50 border border-red-200 p-3"
            >
              <p class="text-sm text-red-700">{{ createError }}</p>
            </div>

            <button
              type="submit"
              :disabled="!canCreate || creating"
              class="w-full rounded-lg bg-green-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="creating">Membuat...</span>
              <span v-else>Buat Pernikahan</span>
            </button>
          </form>
        </div>
      </template>

      <!-- Confirm delete dialog -->
      <ConfirmDialog
        :visible="deleteDialog.visible"
        title="Hapus Pernikahan"
        :message="deleteDialog.message"
        confirm-text="Hapus Permanen"
        cancel-text="Batal"
        @confirm="handleDelete"
        @cancel="deleteDialog.visible = false"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useStore } from "vuex";
import { useRouter, useRoute } from "vue-router";
import { validateSlug, normalizeSlug } from "@/utils/slugValidator";
import { hasLegacyData, migrateLegacyData } from "@/utils/legacyMigration";
import ConfirmDialog from "@/Pages/Cms/components/shared/ConfirmDialog.vue";
import ErrorMessage from "@/Pages/Cms/components/shared/ErrorMessage.vue";
import LoadingIndicator from "@/Pages/Cms/components/shared/LoadingIndicator.vue";

const store = useStore();
const router = useRouter();
const route = useRoute();

// State
const form = ref({ label: "", slug: "" });
const slugError = ref(null);
const createError = ref(null);
const creating = ref(false);
const showLegacyBanner = ref(false);
const showMigrationForm = ref(false);
const migrationSlug = ref("");
const migrationSlugError = ref(null);
const migrationError = ref(null);
const migrationSuccess = ref(null);
const migrating = ref(false);
const deleteDialog = ref({
  visible: false,
  message: "",
  slug: null,
});

// Computed
const loading = computed(() => store.state.wedding.loading);
const registry = computed(() => store.getters["wedding/registry"]);
const errorMessage = computed(() => {
  if (route.query.error === "not-found") {
    return "Pernikahan tidak ditemukan. Silakan pilih pernikahan yang tersedia atau buat baru.";
  }
  return null;
});
const canCreate = computed(() => {
  return form.value.label.trim() && form.value.slug.trim() && !slugError.value;
});
const canMigrate = computed(() => {
  return migrationSlug.value.trim() && !migrationSlugError.value;
});

// Methods
function onSlugInput() {
  const normalized = normalizeSlug(form.value.slug);
  form.value.slug = normalized;

  if (!normalized) {
    slugError.value = null;
    return;
  }

  const result = validateSlug(normalized);
  slugError.value = result.valid ? null : result.error;
}

function selectWedding(slug) {
  router.push(`/cms/${slug}/dashboard`);
}

function confirmDelete(wedding) {
  deleteDialog.value = {
    visible: true,
    message: `Apakah Anda yakin ingin menghapus "${wedding.label}" (${wedding.slug})? Semua data undangan termasuk tamu, RSVP, media, dan pengaturan akan dihapus secara permanen dan tidak dapat dikembalikan.`,
    slug: wedding.slug,
  };
}

async function handleDelete() {
  const slug = deleteDialog.value.slug;
  deleteDialog.value.visible = false;
  try {
    await store.dispatch("wedding/deleteWedding", slug);
  } catch (error) {
    // Error is handled by the store
  }
}

async function handleCreate() {
  if (!canCreate.value) return;

  creating.value = true;
  createError.value = null;

  try {
    await store.dispatch("wedding/createWedding", {
      slug: form.value.slug.trim(),
      label: form.value.label.trim(),
    });
    // Navigate to the new wedding's dashboard
    router.push(`/cms/${form.value.slug.trim()}/dashboard`);
    form.value = { label: "", slug: "" };
  } catch (error) {
    createError.value = error.message || "Gagal membuat pernikahan baru";
  } finally {
    creating.value = false;
  }
}

function formatDate(isoString) {
  try {
    return new Date(isoString).toLocaleDateString("id-ID", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });
  } catch {
    return isoString;
  }
}

function handleMigration() {
  showMigrationForm.value = true;
  migrationSlug.value = "";
  migrationSlugError.value = null;
  migrationError.value = null;
  migrationSuccess.value = null;
}

function onMigrationSlugInput() {
  const normalized = normalizeSlug(migrationSlug.value);
  migrationSlug.value = normalized;

  if (!normalized) {
    migrationSlugError.value = null;
    return;
  }

  const result = validateSlug(normalized);
  migrationSlugError.value = result.valid ? null : result.error;
}

async function executeMigration() {
  if (!canMigrate.value) return;

  migrating.value = true;
  migrationError.value = null;
  migrationSuccess.value = null;

  try {
    const result = await migrateLegacyData(migrationSlug.value.trim());

    if (result.success) {
      migrationSuccess.value = `Migrasi berhasil! ${result.migratedEntities.length} entitas dimigrasikan.`;
      // Refresh registry and navigate to the new wedding
      await store.dispatch("wedding/fetchRegistry");
      showLegacyBanner.value = false;
      showMigrationForm.value = false;
      router.push(`/cms/${migrationSlug.value.trim()}/dashboard`);
    } else {
      migrationError.value = result.error || "Migrasi gagal";
    }
  } catch (error) {
    migrationError.value = error.message || "Terjadi kesalahan saat migrasi";
  } finally {
    migrating.value = false;
  }
}

function detectLegacyData() {
  try {
    showLegacyBanner.value = hasLegacyData();
  } catch {
    showLegacyBanner.value = false;
  }
}

// Lifecycle
onMounted(async () => {
  await store.dispatch("wedding/fetchRegistry");
  detectLegacyData();
});
</script>
