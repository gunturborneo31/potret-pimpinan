<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, onMounted, watch } from 'vue'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

const isDarkMode = ref(false)
const search = ref('')
const tahun = ref('')
const bulan = ref('')
const tanggal = ref('')
const pejabat_hadir = ref('')

const pejabatOptions = [
  { value: '', label: 'Semua' },
  { value: 'Gubernur', label: 'Gubernur' },
  { value: 'Wakil Gubernur', label: 'Wakil Gubernur' },
  { value: 'Sekretaris Daerah', label: 'Sekretaris Daerah' },
]

const props = defineProps({
  folders: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

onMounted(() => {
  isDarkMode.value = localStorage.getItem('theme') === 'dark'
  document.documentElement.classList.toggle('dark', isDarkMode.value)

  search.value = props.filters.search || ''
  tahun.value = props.filters.tahun || ''
  bulan.value = props.filters.bulan || ''
  tanggal.value = props.filters.tanggal || ''
  pejabat_hadir.value = props.filters.pejabat_hadir || ''
})

function toggleDarkMode() {
  isDarkMode.value = !isDarkMode.value
  localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light')
  document.documentElement.classList.toggle('dark', isDarkMode.value)
}

function resetFilter() {
  search.value = ''
  tahun.value = ''
  bulan.value = ''
  tanggal.value = ''
  pejabat_hadir.value = ''
  applyFilter()
}

function applyFilter() {
  router.get(route('kegiatanpublic.list'), {
    search: search.value,
    tahun: tahun.value,
    bulan: bulan.value,
    tanggal: tanggal.value,
    pejabat_hadir: pejabat_hadir.value,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

function formatTanggal(tanggal) {
  if (!tanggal) return '-'
  const bulan = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ]
  const d = new Date(tanggal)
  const day = d.getDate().toString().padStart(2, '0')
  const month = bulan[d.getMonth()]
  const year = d.getFullYear()
  return `${day} ${month} ${year}`
}

function truncate(text, length = 40) {
  return text?.length > length ? text.slice(0, length) + '...' : text
}

// list tahun dinamis 2026 - 2021
const years = Array.from({ length: 6 }, (_, i) => 2026 - i)
const months = [
  'Januari','Februari','Maret','April','Mei','Juni',
  'Juli','Agustus','September','Oktober','November','Desember'
]

// biar realtime -> pantau perubahan semua filter
watch([search, tahun, bulan, tanggal, pejabat_hadir], applyFilter)
</script>

<template>
  <Head title="List Kegiatan - Layanan Dokumentasi Pimpinan Pemerintah Kabupaten Mahakam Ulu" />

  <div class="min-h-screen bg-white dark:bg-gray-900 text-gray-800 dark:text-white">
    <!-- Navbar -->
     <nav class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white px-4 py-4 shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-3">
          <a href="/" class="flex items-center gap-2">
            <img src="/img/logo-only.png" alt="Logo" class="h-8 w-8 object-contain" />
            <span class="font-bold text-xl">Layanan Dokumentasi Pimpinan</span>
          </a>
          <div class="flex items-center gap-4 text-sm">
            <a href="/" class="hover:underline">Beranda</a>
            <a href="/list-kegiatan" class="text-orange-600 hover:underline">Kegiatan</a>
            <!-- <a href="/list-berita" class="hover:underline">Berita</a> -->
                <a href="login" class="hover:underline text-sm">Login</a>
    <a href="register" class="hover:underline text-sm">Register</a>
            <button @click="toggleDarkMode" class="border px-3 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
              {{ isDarkMode ? '☀️' : '🌙' }}
            </button>
          </div>
        </div>
      </nav>

    <!-- Main Content -->
    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

          <!-- Header List -->
          <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-orange-600">Daftar Semua Kegiatan</h2>
            <p class="text-gray-600 dark:text-gray-300 mt-2">
              Semua kegiatan yang tersedia, ajukan  
              <Link :href="route('permohonan.create')" class="text-orange-600 hover:underline break-words">Permohonan</Link> jika kegiatan tidak ada
            </p>
          </div>

          <!-- Filter -->
<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
  <input v-model="search" type="text" placeholder="Cari nama kegiatan..."
    class="border dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded w-full" />

  <input v-model="tanggal" type="date"
    class="border dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded w-full" />

  <select v-model="bulan"
    class="border dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded w-full">
    <option value="">Semua Bulan</option>
    <option v-for="(m, i) in months" :key="i" :value="i+1">{{ m }}</option>
  </select>

  <select v-model="tahun"
    class="border dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 rounded w-full">
    <option value="">Semua Tahun</option>
    <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
  </select>

  <Multiselect
    v-model="pejabat_hadir"
    :options="pejabatOptions"
    placeholder="Pejabat Hadir"
    :canClear="true"
    class="border dark:border-gray-600 dark:bg-gray-700 dark:text-gray-700 rounded w-full"
  />
</div>

<!-- Reset button -->
<div class="mb-8 text-right">
  <button @click="resetFilter"
    class="bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-4 py-2 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition">
    Reset Filter
  </button>
</div>

<!-- Grid Card Folder -->
<div>
  <div v-if="folders.data.length === 0" class="text-center py-12 text-gray-600 dark:text-gray-300">
    ❌ Tidak ada kegiatan yang ditemukan
  </div>

  <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
    <div v-for="folder in folders.data" :key="folder.id"
      class="bg-white dark:bg-gray-800 rounded-2xl shadow hover:shadow-lg transition p-6 text-left border border-transparent dark:border-gray-700">
      <h3 class="font-bold text-lg mb-2">
        <Link :href="route('kegiatanpublic.folders.show', folder.slug)" class="text-orange-600 hover:underline break-words">
          📁 {{ truncate(folder.judul) }}
        </Link>
      </h3>
      <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">📅 {{ formatTanggal(folder.tanggal_kegiatan) }}</p>
      <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">👤 Dihadiri: {{ folder.pejabat_hadir ?? '-' }}</p>
      <p class="text-sm text-gray-600 dark:text-gray-300">📄 Total File: {{ folder.files?.length ?? 0 }}</p>
    </div>
  </div>
</div>

          <!-- Pagination -->
          <div class="mt-6 flex justify-center">
            <nav v-if="folders.links.length > 3" class="inline-flex rounded-md shadow-sm" aria-label="Pagination">
              <template v-for="(link, i) in folders.links" :key="i">
                <Link v-if="link.url" :href="link.url" v-html="link.label" class="px-3 py-2 border text-sm font-medium"
                  :class="[ link.active
                      ? 'bg-orange-500 text-white border-orange-500'
                      : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700',
                      i === 0 ? 'rounded-l-md' : '',
                      i === folders.links.length - 1 ? 'rounded-r-md' : ''
                  ]" />
                <span v-else v-html="link.label"
                  class="px-3 py-2 border text-sm font-medium text-gray-400 bg-gray-100 dark:bg-gray-700" />
              </template>
            </nav>
          </div>

        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-100 dark:bg-gray-800 text-center py-6 text-sm text-gray-600 dark:text-gray-300 mt-12 px-4">
      &copy; 2025 Layanan Dokumentasi Pimpinan by Biro Administrasi Pimpinan Kabupaten Mahakam Ulu
    </footer>
  </div>
</template>
