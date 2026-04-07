<template>
  <Head title="Sambutan" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Sambutan</h2>
      </div>
    </template>

    <div class="max-w-7xl mx-auto px-4 py-6">
      <button
        @click="showCreate = true"
        class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded text-sm mb-5"
      >
        +Sambutan
      </button>

      <!-- Filter -->
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <input
            v-model="search"
            type="text"
            placeholder="Cari judul..."
            class="px-3 py-2 border rounded text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white"
          />
          <select
            v-model="tahun"
            class="px-3 py-2 border rounded text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white"
          >
            <option value="">Semua Tahun</option>
            <option v-for="y in years" :key="y" :value="String(y)">{{ y }}</option>
          </select>
          <button @click="reset" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 text-sm rounded">
            Reset
          </button>
        </div>
      </div>

      <!-- Tip -->
      <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
        Tip: klik folder untuk mengunggah file
        <strong>(Naskah, Tapping, Presentasi, Terjemahan)</strong>
      </p>

      <!-- Grid "folder" -->
      <div
        v-if="sambutans.data?.length"
        class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
      >
        <div
          v-for="s in sambutans.data"
          :key="s.id"
          class="relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow hover:shadow-md cursor-pointer"
          @click="goToDetail(s.slug)"
        >
          <!-- three-dots menu -->
          <button
            class="absolute right-2 top-2 p-1.5 rounded hover:bg-gray-100 dark:hover:bg-gray-700"
            @click.stop="toggleMenu(s.id)"
            aria-label="More"
          >
            <MoreVertical class="w-5 h-5 text-gray-600 dark:text-gray-300" />
          </button>

          <!-- dropdown -->
          <div
            v-if="menuOpenId === s.id"
            class="absolute right-2 top-10 z-20 w-40 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-lg overflow-hidden"
            @click.stop
          >
            <button
              class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
              @click="openRename(s)"
            >
              Rename
            </button>
            <button
              class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30"
              @click="removeSambutan(s)"
            >
              Hapus
            </button>
          </div>

          <h3 class="font-semibold text-gray-900 dark:text-white line-clamp-2">
            {{ s.judul }}
          </h3>

          <p class="text-xs text-gray-500 mt-1">🗓 {{ formatYear(s.tanggal_terbit) }}</p>


          <!-- 👤 Dibuat oleh -->
          <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">
            👤 Dibuat oleh: <span class="font-medium">{{ s.user?.name || '—' }}</span>
          </p>

          <p class="text-xs mt-1">
            <span :class="s.is_public ? 'text-green-600' : 'text-gray-500'">
              {{ s.is_public ? 'Publik' : 'Private' }}
            </span>
          </p>

          <div class="mt-3">
            <span class="inline-flex items-center gap-1 text-xs text-orange-600">
              ➜ Kelola File
            </span>
          </div>
        </div>
      </div>

      <div v-else class="text-sm text-gray-600 dark:text-gray-300">Belum ada data.</div>

      <!-- Pagination -->
      <div v-if="sambutans.links?.length > 3" class="mt-6 flex flex-wrap gap-1 justify-center">
        <Link
          v-for="(link,i) in sambutans.links"
          :key="i"
          :href="link.url || '#'"
          v-html="link.label"
          class="px-3 py-1 rounded text-sm"
          :class="link.active
            ? 'bg-orange-600 text-white'
            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'"
          preserve-state
          preserve-scroll
        />
      </div>
    </div>

    <!-- Modal Create -->
    <div v-if="showCreate" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow max-w-md w-full p-5">
        <h3 class="text-lg font-semibold mb-3">Buat Folder Sambutan</h3>

        <form @submit.prevent="submit">
          <label class="block text-sm mb-1">Judul</label>
          <input
            v-model="form.judul"
            class="w-full mb-3 px-3 py-2 border rounded text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white"
          />

          <label class="block text-sm mb-1">Tahun</label>
          <select
            v-model="form.tahun_terbit"
            class="w-full mb-3 px-3 py-2 border rounded text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white"
          >
            <option v-for="y in yearsForm" :key="y" :value="String(y)">{{ y }}</option>
          </select>


          <label class="block text-sm mb-1">Deskripsi (opsional)</label>
          <textarea
            v-model="form.deskripsi"
            rows="3"
            class="w-full mb-3 px-3 py-2 border rounded text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white"
          ></textarea>

          <label class="inline-flex items-center gap-2 mb-4">
            <input type="checkbox" v-model="form.is_public" class="form-checkbox text-orange-600" />
            <span class="text-sm">Publik</span>
          </label>

          <div class="flex justify-end gap-2">
            <button type="button" @click="showCreate = false" class="px-4 py-2 text-sm">Batal</button>
            <button
              type="submit"
              class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded text-sm"
              :disabled="form.processing"
            >
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Rename -->
    <div v-if="showRename" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" @click="showRename = false">
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow max-w-md w-full p-5" @click.stop>
        <h3 class="text-lg font-semibold mb-3">Rename Sambutan</h3>
        <form @submit.prevent="submitRename">
          <label class="block text-sm mb-1">Judul</label>
          <input
            v-model="renameJudul"
            class="w-full mb-3 px-3 py-2 border rounded text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white"
          />
          <div class="flex justify-end gap-2">
            <button type="button" @click="showRename = false" class="px-4 py-2 text-sm">Batal</button>
            <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded text-sm">
              Simpan
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { toast } from 'vue3-toastify'
import { MoreVertical } from 'lucide-vue-next'

const props = defineProps({
  sambutans: Object,
  filters: Object,
})

const yearsForm = computed(() => {
  const y = new Date().getFullYear()
  return Array.from({ length: 11 }, (_, i) => y - i) // 0..10
})

// tampilkan hanya tahun
function formatYear(t) {
  if (!t) return '-'
  return new Date(t).getFullYear()
}

const showCreate = ref(false)
const search = ref(props.filters?.search ?? '')
const tahun  = ref(props.filters?.tahun  ?? '')

const years = computed(() => {
  const y = new Date().getFullYear()
  return Array.from({ length: 6 }, (_, i) => y - i)
})

function formatDate(t) {
  if (!t) return '-'
  const d = new Date(t)
  const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']
  return `${String(d.getDate()).padStart(2,'0')} ${months[d.getMonth()]} ${d.getFullYear()}`
}

function showUrl(slug) {
  if (!slug) return '#'
  try { return route('sambutan.show', slug) } catch (e) { return `/sambutan/${slug}` }
}

function goToDetail(slug) {
  router.visit(showUrl(slug), { preserveScroll: true, preserveState: true })
}

function apply() {
  router.get(route('sambutan.index'), { search: search.value, tahun: tahun.value }, {
    preserveState: true, replace: true, preserveScroll: true,
  })
}
function reset() { search.value = ''; tahun.value = ''; apply() }
watch([search, tahun], apply)

const form = useForm({
  judul: '',
  tahun_terbit: String(new Date().getFullYear()),
  deskripsi: '',
  is_public: false,
})
function submit() {
  form.post(route('sambutan.store'), {
    onSuccess: () => { showCreate.value = false; form.reset() }
  })
}

const sambutans = computed(() => props.sambutans ?? { data: [], links: [] })

/* =========================
   Dropdown ⋮, Rename, Hapus
   ========================= */
const menuOpenId = ref(null)
const showRename = ref(false)
const renameTargetId = ref(null)
const renameJudul = ref('')

function toggleMenu(id) {
  menuOpenId.value = (menuOpenId.value === id) ? null : id
}
function closeMenu() { menuOpenId.value = null }

function onDocClick(e) {
  // close dropdown saat klik di luar
  // (menu punya @click.stop)
  if (menuOpenId.value) closeMenu()
}
onMounted(() => document.addEventListener('click', onDocClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))

function openRename(s) {
  closeMenu()
  renameTargetId.value = s.id
  renameJudul.value = s.judul || ''
  showRename.value = true
}

async function submitRename() {
  if (!renameTargetId.value || !renameJudul.value.trim()) return
  try {
    await axios.patch(route('sambutan.update', renameTargetId.value), {
      judul: renameJudul.value.trim(),
    }, { headers: { Accept: 'application/json' } })

    showRename.value = false
    toast.success('Berhasil mengganti judul sambutan')
    // reload supaya data (judul & slug) terbaru
    router.reload({ preserveScroll: true, preserveState: true })
  } catch (e) {
    console.error(e)
    toast.error('Gagal mengganti judul')
  }
}

async function removeSambutan(s) {
  closeMenu()
  if (!confirm(`Hapus sambutan "${s.judul}" beserta semua file di dalamnya?`)) return
  try {
    await axios.delete(route('sambutan.destroy', s.id), { headers: { Accept: 'application/json' } })
    toast.success('Sambutan berhasil dihapus')
    router.reload({ preserveScroll: true, preserveState: true })
  } catch (e) {
    console.error(e)
    toast.error('Gagal menghapus sambutan')
  }
}
</script>
