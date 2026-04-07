<script setup>
import { ref, watch, nextTick, onMounted, computed} from 'vue'

import { Head, useForm, router } from '@inertiajs/vue3'
import { LayoutGrid, List } from 'lucide-vue-next'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Card from './Partials/Card.vue'
import Listview from './Partials/List.vue'
import { toast } from 'vue3-toastify'
import axios from 'axios'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'


const props = defineProps({
  folders: Object,
  filters: Object,
  filtersData: Object,
  stats: Object,
})

const folders = ref([...props.folders.data])
const nextPageUrl = ref(
  props.folders?.next_page_url ?? props.folders?.links?.next ?? null
)
const isGrid = ref(localStorage.getItem('kegiatan-view') !== 'list')
const search = ref(props.filters.search || '')
const loading = ref(false)
const showModal = ref(false)
const inputRef = ref(null)
const selectUserRef = ref(null)
const isSecret = ref(false)
const secretLink = ref('')

const filters = ref({
  user_id: props.filters.user_id || '',
  is_favorite: props.filters.is_favorite || false,
  is_public: props.filters.is_public || false,
  tanggal: props.filters.tanggal || '',
  bulan: props.filters.bulan || '',
  tahun: props.filters.tahun || '',
  pejabat_hadir: props.filters.pejabat_hadir || '',
})

const pejabatOptions = [
  { value: '', label: 'Semua' },
  { value: 'Gubernur', label: 'Gubernur' },
  { value: 'Wakil Gubernur', label: 'Wakil Gubernur' },
  { value: 'Sekretaris Daerah', label: 'Sekretaris Daerah' },
]

const userOptions = computed(() =>
  filtersData.value.users.map(user => ({
    value: user.id,
    label: user.name,
  }))
)

const filtersData = ref({
  users: props.filters.users || [], // from controller
})

// Modal Tambah Folder
const form = useForm({ 
  judul: '', 
  tanggal_kegiatan: '',
  pejabat_hadir: '',
  is_public: false,   // default
  is_secret: false,
})

// Modal Rename Folder
const showRenameModal = ref(false)
const folderToRename = ref(null)
const renameForm = useForm({ 
  judul: '', 
  tanggal_kegiatan: '',
  pejabat_hadir: '' 
})

const folderToShare = ref(null)
const isPublic = ref(false)
const shareLink = ref('')
const showShareModal = ref(false)

function doSearch() {
  router.get(route('kegiatan.folders.index'), {
    search: search.value,
    user_id: filters.value.user_id,
    is_favorite: filters.value.is_favorite ? 1 : 0,
    is_public: filters.value.is_public ? 1 : 0,
    tanggal: filters.value.tanggal,
    bulan: filters.value.bulan,
    tahun: filters.value.tahun,
    pejabat_hadir: filters.value.pejabat_hadir,
  }, {
    preserveState: true,
    replace: true,
    onSuccess: (page) => {
      folders.value = [...page.props.folders.data]
      nextPageUrl.value = page.props.folders.next_page_url ?? page.props.folders.links?.next ?? null
    }
  })
}


function handleShare(folder) {
  console.log('folder yang dikirim ke handleShare:', folder)

  if (!folder || !folder.slug) {
    toast.error('Folder tidak valid!')
    return
  }

  folderToShare.value = folder
   form.is_public = folder.is_public === 1 || folder.is_public === true
  form.is_secret = folder.is_secret === 1 || folder.is_secret === true

  isPublic.value = folder.is_public
  shareLink.value = folder.is_public
    ? route('kegiatanpublic.folders.show', folder.slug)
    : ''

  isSecret.value = folder.is_secret
  secretLink.value = folder.is_secret
    ? route('kegiatan.folders.secret.link', folder.slug_secret)
    : ''

  showShareModal.value = true

  
}
const copySecretLink = () => {
  navigator.clipboard.writeText(secretLink.value)
  toast.success('Link secret berhasil disalin!')
}

async function toggleFolderPublic() {
  if (!folderToShare.value || !folderToShare.value.id) {
    toast.error('Folder tidak valid.')
    return
  }

  try {
    const res = await axios.patch(
      route('kegiatan.folders.togglePublic', folderToShare.value.id)
    )

    isPublic.value = res.data.is_public
    shareLink.value = res.data.is_public
      ? route('kegiatanpublic.folders.show', folderToShare.value.slug)
      : ''
    toast.success('Status folder berhasil diperbarui')
    refreshFolders()
  } catch (error) {
    console.error(error)
    toast.error('Gagal memperbarui status folder.')
  }
}

async function toggleFolderSecret() {
  if (!folderToShare.value || !folderToShare.value.id) {
    toast.error('Folder tidak valid.')
    return
  }

  try {
   const res = await axios.patch(
  route('kegiatan.folders.secret.toggle', folderToShare.value.id)
)

isSecret.value = res.data.is_secret
secretLink.value = res.data.is_secret
  ? route('kegiatan.folders.secret.link', res.data.slug_secret)
  : ''
    toast.success('Secret link berhasil diperbarui')
    refreshFolders()
  } catch (error) {
    console.error(error)
    toast.error('Gagal memperbarui secret link.')
  }
}


const toggleFavorite = async (folder) => {
  try {
    await axios.patch(route('kegiatan.folders.toggle-favorite', folder.id)); // FIXED
    folder.is_favorite = !folder.is_favorite;
    toast.success('Ditambahkan ke Favorite')
  } catch (error) {
    console.error('Gagal toggle favorite', error);
  }
};

function copyLink() {
  if (!shareLink.value) return

  navigator.clipboard.writeText(shareLink.value).then(() => {
    toast.success('Link berhasil disalin!')
  }).catch(() => {
    toast.error('Gagal menyalin link.')
  })
}

function handleDelete(folder) {
  if (!confirm(`Yakin ingin menghapus folder "${folder.judul}" beserta semua isinya?`)) return;

  router.delete(route('kegiatan.folders.destroy', folder.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Folder berhasil dihapus.')
      refreshFolders()
    },
    onError: () => toast.error('Gagal menghapus folder.')
  })
}

function resetFilters() {
  filters.value = {
    user_id: '',
    is_favorite: false,
    is_public: false,
    tanggal: '',
    bulan: '',
    tahun: '',
    pejabat_hadir: '',
  }

  search.value = ''

  nextTick(() => {
    const $select = window.$('#filterUser')
    if ($select && $select.length) {
      $select.val('').trigger('change')
    }
  })

  doSearch()
}



function refreshFolders() {
  axios.get(route('kegiatan.folders.index'), {
    params: { search: search.value },
    headers: { 'Accept': 'application/json' },
  }).then((res) => {
    folders.value = res.data.folders.data
    nextPageUrl.value = page.props.folders.next_page_url ?? page.props.folders.links?.next ?? null
  }).catch((e) => {
    console.error('Gagal memuat data folder:', e)
  })
}

function submit() {
  if (!form.judul.trim()) {
    toast.error('Judul folder tidak boleh kosong.')
    return
  }

  if (!form.tanggal_kegiatan) {
    toast.error('Tanggal kegiatan harus diisi.')
    return
  }

  if (!form.pejabat_hadir) {
    toast.error('Pejabat yang hadir harus dipilih.')
    return
  }

  form.post(route('kegiatan.folders.store'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Folder berhasil dibuat!')
      form.reset()
      showModal.value = false
      refreshFolders()
    },
    onError: () => toast.error('Terjadi kesalahan saat menyimpan.')
  })
}

function renameFolder() {
  if (!folderToRename.value || !folderToRename.value.id) {
    toast.error('Folder tidak valid untuk diubah.')
    return
  }

  if (!renameForm.judul || !renameForm.tanggal_kegiatan || !renameForm.pejabat_hadir) {
    toast.error('Semua kolom harus diisi!')
    return
  }

  renameForm.put(route('kegiatan.folders.update', folderToRename.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Folder berhasil diperbarui!')
      showRenameModal.value = false
      folderToRename.value = null
      refreshFolders()
    },
    onError: () => toast.error('Gagal memperbarui folder.')
  })
}

function loadMore() {
  if (!nextPageUrl.value || loading.value) return
  loading.value = true
  axios.get(nextPageUrl.value)
    .then(res => {
      folders.value.push(...res.data.folders.data)
      nextPageUrl.value = res.data.folders.links.next
    })
    .catch(console.error)
    .finally(() => loading.value = false)
}


watch(search, debounce(doSearch, 500))

watch(filters, () => {
  doSearch()
}, { deep: true })

watch(isGrid, val => {
  localStorage.setItem('kegiatan-view', val ? 'grid' : 'list')
})

watch(showModal, val => {
  if (val) nextTick(() => inputRef.value?.focus())
})

function debounce(fn, delay) {
  let timeout
  return (...args) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => fn(...args), delay)
  }
}

function handleRename(folder) {
   folderToRename.value = folder
  renameForm.judul = folder.judul
  renameForm.tanggal_kegiatan = folder.tanggal_kegiatan
  renameForm.pejabat_hadir = folder.pejabat_hadir
  showRenameModal.value = true
}


/* === Tambahan: fungsi goToPage untuk tombol pagination (biarkan ada) === */
function goToPage(url) {
  if (!url) return
  router.get(url, {}, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onSuccess: (page) => {
      folders.value = [...page.props.folders.data]
      nextPageUrl.value = page.props.folders.next_page_url ?? page.props.folders.links?.next ?? null
    }
  })
}

/* ========================= */
/* === Tambahan: Lazy Load ===
   - Halaman awal 50 item
   - Berikutnya tiap load 15 item
   - Tanpa mengubah baris kode yang ada
/* ========================= */

// Import tambahan (tanpa mengubah import awal)
import { onUnmounted } from 'vue'

// Konstanta untuk ukuran muat
const INITIAL_PER_PAGE = 50
const NEXT_PER_PAGE = 15

// Sentinel & observer untuk infinite scroll
const sentinel = ref(null)
let io = null

// Helper: pastikan URL next menyertakan per_page yang kita mau
function buildNextUrl(url, perPage) {
  if (!url) return null
  try {
    const u = new URL(url, window.location.origin)
    u.searchParams.set('per_page', String(perPage))
    return u.toString()
  } catch {
    return url
  }
}

// Muat awal 50 item (JSON) tanpa menyentuh fungsi doSearch()
async function initLargeFirstLoad() {
  try {
    loading.value = true
    const res = await axios.get(route('kegiatan.folders.index'), {
      params: {
        search: search.value,
        user_id: filters.value.user_id,
        is_favorite: filters.value.is_favorite ? 1 : 0,
        is_public: filters.value.is_public ? 1 : 0,
        tanggal: filters.value.tanggal,
        bulan: filters.value.bulan,
        tahun: filters.value.tahun,
        per_page: INITIAL_PER_PAGE,
      },
      headers: { Accept: 'application/json' },
    })
    folders.value = res.data.folders.data
    nextPageUrl.value = res.data.folders.next_page_url ?? res.data.folders.links?.next ?? null
  } catch (e) {
    console.error('Gagal initial load:', e)
  } finally {
    loading.value = false
  }
}

// Load berikutnya 15 item per panggilan
async function loadMoreLazy() {
  if (!nextPageUrl.value || loading.value) return
  loading.value = true
  try {
    const url = buildNextUrl(nextPageUrl.value, NEXT_PER_PAGE)
    const res = await axios.get(url, { headers: { Accept: 'application/json' } })
    folders.value.push(...res.data.folders.data)
    nextPageUrl.value = res.data.folders.next_page_url ?? res.data.folders.links?.next ?? null
  } catch (e) {
    console.error('Gagal load more:', e)
  } finally {
    loading.value = false
  }
}

// Pasang observer saat mount
onMounted(() => {
  // initial load 50
  initLargeFirstLoad()

  // buat intersection observer yang memanggil loadMoreLazy
  io = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        loadMoreLazy()
      }
    })
  }, { root: null, rootMargin: '0px', threshold: 0.1 })

  // mulai observe sentinel
  nextTick(() => {
    if (sentinel.value) io.observe(sentinel.value)
  })
})

onUnmounted(() => {
  if (io && sentinel.value) io.unobserve(sentinel.value)
})
</script>

<template>
  <Head title="Kegiatan" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Kegiatan</h2>
      </div>
    </template>
<!-- Modal Share Folder -->
<div v-if="showShareModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-md">
    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Pengaturan Share Folder</h3>
<div class="flex items-center justify-between mb-4">
  <label class="text-gray-700 dark:text-gray-200 font-medium">Status Publik</label>
  <input
    type="checkbox"
    v-model="form.is_public"
    @change="toggleFolderPublic"
    class="form-checkbox h-5 w-5 text-orange-500"
  />
</div>

<div class="mt-4">
  <label class="flex items-center justify-between mb-4">
  <label class="text-gray-700 dark:text-gray-200 font-medium">Aktifkan Secret Link</label>

    <input 
      type="checkbox" 
      v-model="form.is_secret" 
      @change="toggleFolderSecret"
      class="form-checkbox h-5 w-5 text-orange-500"
    />
  </label>

  <div v-if="form.is_secret && form.secret_url" class="mt-2">
    <p class="text-sm text-gray-500">Secret URL:</p>
    <input 
      type="text" 
      readonly 
      class="w-full p-2 border rounded" 
      :value="form.secret_url" 
    />
  </div>
</div>


    <div v-if="isPublic">
      <label class="text-sm text-gray-600 dark:text-gray-300">Link Publik:</label>
      <div class="flex items-center gap-2 mt-1">
        <input
          :value="shareLink"
          class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm"
          readonly
        />
        <button @click="copyLink" class="text-sm text-blue-500 hover:underline">Copy</button>
      </div>
    </div>

    <div v-if="isSecret" class="mt-3">
  <label class="text-sm text-gray-600 dark:text-gray-300">Link Rahasia:</label>
  <div class="flex items-center gap-2 mt-1">
    <input
      :value="secretLink"
      class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm"
      readonly
    />
    <button @click="copySecretLink" class="text-sm text-blue-500 hover:underline">Copy</button>
  </div>
</div>

    <div class="flex justify-end gap-2 mt-6">
      <button @click="showShareModal = false" class="px-4 py-2 text-gray-600 dark:text-gray-300">Tutup</button>
    </div>
  </div>
</div>


    <!-- Modal Tambah Folder -->
     <!-- Modal Tambah Folder -->
<div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-md">
    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Tambah Folder Kegiatan</h3>

    <!-- Judul -->
    <input
      type="text"
      ref="inputRef"
      v-model="form.judul"
      placeholder="Judul folder"
      class="w-full border border-gray-300 dark:border-gray-600 rounded px-4 py-2 dark:bg-gray-900 dark:text-white mb-4"
      @keyup.enter="submit"
    />

    <!-- Tanggal Kegiatan -->
    <input
      type="date"
      v-model="form.tanggal_kegiatan"
      class="w-full border border-gray-300 dark:border-gray-600 rounded px-4 py-2 dark:bg-gray-900 dark:text-white mb-4"
    />

    <!-- Pejabat Hadir -->
    <select
      v-model="form.pejabat_hadir"
      class="w-full border border-gray-300 dark:border-gray-600 rounded px-4 py-2 dark:bg-gray-900 dark:text-white mb-4"
    >
      <option value="">-- Pilih Pejabat yang Hadir --</option>
      <option value="Gubernur">Gubernur</option>
      <option value="Wakil Gubernur">Wakil Gubernur</option>
      <option value="Sekretaris Daerah">Sekretaris Daerah</option>
    </select>

    <!-- Tombol -->
    <div class="flex justify-end gap-2">
      <button @click="showModal = false" class="px-4 py-2 text-gray-600 dark:text-gray-300">Batal</button>
      <button @click="submit" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded">Simpan</button>
    </div>
  </div>
</div>


    <!-- Modal Rename Folder -->
    <div v-if="showRenameModal" class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center">
      <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Ubah Nama Folder</h3>
        <input
          type="text"
          v-model="renameForm.judul"
          placeholder="Judul baru"
          class="w-full border border-gray-300 dark:border-gray-600 rounded px-4 py-2 dark:bg-gray-900 dark:text-white mb-4"
           @keyup.enter="renameFolder"
        />
<!-- Input Tanggal Kegiatan -->
<input
  type="date"
  v-model="renameForm.tanggal_kegiatan"
  class="w-full border border-gray-300 dark:border-gray-600 rounded px-4 py-2 dark:bg-gray-900 dark:text-white mb-4"
/>

<!-- Select Pejabat Hadir -->
<select
  v-model="renameForm.pejabat_hadir"
  class="w-full border border-gray-300 dark:border-gray-600 rounded px-4 py-2 dark:bg-gray-900 dark:text-white mb-4"
>
  <option value="">Pilih Pejabat yang Hadir</option>
  <option value="Gubernur">Gubernur</option>
  <option value="Wakil Gubernur">Wakil Gubernur</option>
  <option value="Sekretaris Daerah">Sekretaris Daerah</option>
</select>

        <div class="flex justify-end gap-2">
          <button @click="showRenameModal = false" class="px-4 py-2 text-gray-600 dark:text-gray-300">Batal</button>
          <button @click="renameFolder" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded">Simpan</button>
        </div>
      </div>
    </div>

    <!-- Konten Utama -->
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Statistik Folder -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 text-center border border-gray-200 dark:border-gray-700">
          <p class="text-2xl font-bold text-orange-500">{{ props.stats?.total ?? 0 }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Semua Folder</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 text-center border border-gray-200 dark:border-gray-700">
          <p class="text-2xl font-bold text-blue-500">{{ props.stats?.gubernur ?? 0 }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Gubernur</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 text-center border border-gray-200 dark:border-gray-700">
          <p class="text-2xl font-bold text-green-500">{{ props.stats?.wakil_gubernur ?? 0 }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Wakil Gubernur</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 text-center border border-gray-200 dark:border-gray-700">
          <p class="text-2xl font-bold text-purple-500">{{ props.stats?.sekretaris_daerah ?? 0 }}</p>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sekretaris Daerah</p>
        </div>
      </div>

      <!-- Header control -->
     <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
  <!-- Bagian kiri (bisa tempat filter, judul, dsb) -->
  <div>
    <!-- Bisa kosong jika tidak ada konten kiri -->
  </div>

  <!-- Tombol Tambah Folder di kanan -->
  <div class="text-right">
    <button
      @click="showModal = true"
      class="bg-orange-500 hover:bg-orange-600 text-white text-sm px-4 py-2 rounded"
    >
      +Folder
    </button>
  </div>

     <!-- Container luar: kanan atas -->
<div class="w-full flex justify-end mb-4">
  <div class="flex flex-col gap-2 md:flex-row md:flex-wrap md:items-center md:justify-end">
    <!-- Baris 1: Search, Pejabat Hadir, User, Favorite, Publik -->
    <div class="flex flex-wrap items-center gap-2 justify-end">
      <input
        type="text"
        v-model="search"
        placeholder="Cari folder..."
        class="px-3 py-1 border rounded text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
      />

      <div style="min-width: 180px;">
        <Multiselect
          v-model="filters.pejabat_hadir"
          :options="pejabatOptions"
          placeholder="Pejabat Hadir"
          :canClear="true"
          class="text-sm"
        />
      </div>

      <label class="flex items-center text-sm gap-1 text-gray-700 dark:text-gray-200">
        <input type="checkbox" v-model="filters.is_favorite" class="form-checkbox text-orange-500" />
        Favorite
      </label>

      <label class="flex items-center text-sm gap-1 text-gray-700 dark:text-gray-200">
        <input type="checkbox" v-model="filters.is_public" class="form-checkbox text-orange-500" />
        Publik
      </label>
    </div>

    <!-- Baris 2: Tanggal, Bulan, Tahun, Reset, Toggle View -->
    <div class="flex flex-wrap items-center gap-2 justify-end">
      <!-- Tanggal -->
      <input
        type="date"
        v-model="filters.tanggal"
        class="px-3 py-1 text-sm border rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
      />

      <!-- Bulan -->
      <select
        v-model="filters.bulan"
        class="px-3 py-1 text-sm border rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
      >
        <option value="">Semua Bulan</option>
        <option v-for="m in 12" :key="m" :value="m.toString().padStart(2, '0')">
          {{ new Date(0, m - 1).toLocaleString('id-ID', { month: 'long' }) }}
        </option>
      </select>

      <!-- Tahun -->
      <select
        v-model="filters.tahun"
        class="px-3 py-1 text-sm border rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
      >
        <option value="">Semua Tahun</option>
        <option v-for="year in Array.from({ length: 5 }, (_, i) => new Date().getFullYear() - i)" :key="year" :value="year">
          {{ year }}
        </option>
      </select>

      <!-- Reset -->
      <button
        @click="resetFilters"
        class="text-sm bg-gray-200 dark:bg-gray-800 hover:bg-orange-500 hover:text-white text-gray-800 dark:text-white px-3 py-1 rounded border border-gray-300 dark:border-gray-600"
      >
        Reset Filter
      </button>

      <!-- Tampilan Grid/List -->
      <button @click="isGrid = true"
        :class="['p-2 rounded', isGrid ? 'bg-orange-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-white']"
        title="Tampilan Grid">
        <LayoutGrid class="w-5 h-5" />
      </button>
      <button @click="isGrid = false"
        :class="['p-2 rounded', !isGrid ? 'bg-orange-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-white']"
        title="Tampilan List">
        <List class="w-5 h-5" />
      </button>
    </div>
      <Multiselect
    v-model="filters.user_id"
    :options="userOptions"
    placeholder="Pilih User"
    searchable
    clearable
    :canClear="true"
    class="px-3 py-1 text-sm border rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-700"
  />

  </div>
</div>

      </div>

      <!-- Folder List -->
      <div v-if="folders.length">
          <div v-if="isGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">

<Card
  v-for="folder in folders"
  :key="folder.id"
  :folder="folder"
  :auth="$page.props.auth"
  @rename="handleRename(folder)"
  @delete="handleDelete"
  @shareSettings="handleShare"
  @toggleFavorite="toggleFavorite"
/>

        </div>
        <div v-else class="space-y-2">
<Listview
  v-for="folder in folders"
  :key="folder.id"
  :folder="folder"
  :auth="$page.props.auth"
  @rename="handleRename(folder)"
  @delete="handleDelete"
  @shareSettings="handleShare"
  @toggleFavorite="toggleFavorite"
/>

        </div>

   
  <div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div v-for="folder in folders.data" :key="folder.id" class="card">
        <!-- Card Content -->
        <h3 class="font-bold">{{ folder.title }}</h3>
        <p>{{ folder.description }}</p>
      </div>
    </div>

    <!-- Pagination -->
      <div class="flex justify-center mt-6" v-if="Array.isArray(props.folders.links) && props.folders.links.length > 3">
      <nav class="inline-flex space-x-1">
     <button
  v-for="(link, index) in (Array.isArray(props.folders.links) ? props.folders.links : [])"
  :key="index"
          @click="goToPage(link.url)"
          v-html="link.label"
          :class="[
            'px-3 py-1 rounded text-sm',
            link.active
              ? 'bg-orange-500 text-white'
              : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600',
            !link.url && 'opacity-50 cursor-not-allowed'
          ]"
          :disabled="!link.url"
        />
      </nav>
    </div>
  </div>

      </div>

      <div v-else class="text-gray-600 dark:text-gray-300">Belum ada folder kegiatan yang cocok.</div>

      <!-- === Tambahan: sentinel untuk lazy load === -->
      <div ref="sentinel" class="h-10 flex items-center justify-center">
        <span v-if="loading" class="text-sm text-gray-500">Memuat…</span>
      </div>
      <!-- === /Tambahan === -->

    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.multiselect-option{
   color: #000 !important;
}
</style>
