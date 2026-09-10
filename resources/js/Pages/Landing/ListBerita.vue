<template>
  <Head title="Daftar Berita - Layanan Komunikasi dan Dokumentasi  Pimpinan" />

  <div :class="{ 'dark': isDarkMode }">
    <div class="min-h-screen bg-white dark:bg-gray-900 text-gray-800 dark:text-white">
      <!-- Navbar -->
      <nav class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white px-4 py-4 shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-3">
          <a href="/" class="flex items-center gap-2">
            <img src="/img/logo-only.png" alt="Logo" class="h-8 w-8 object-contain" />
            <span class="font-bold text-xl">Layanan Komunikasi dan Dokumentasi  Pimpinan</span>
          </a>
          <div class="flex items-center gap-4 text-sm">
            <a href="/" class="hover:underline">Beranda</a>
            <a href="/list-kegiatan" class="hover:underline">Kegiatan</a>
            <a href="/permohonan/create" class="hover:underline">Permohonan</a>
            <!-- <a href="/list-berita" class="text-orange-600 hover:underline">Berita</a> -->
                <a href="login" class="hover:underline text-sm">Login</a>
    <a href="register" class="hover:underline text-sm">Register</a>
            <button @click="toggleDarkMode" class="border px-3 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
              {{ isDarkMode ? '☀️' : '🌙' }}
            </button>
          </div>
        </div>
      </nav>

      <!-- Header -->
      <header class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-orange-600">Daftar Berita</h1>
        <!-- <p class="text-gray-600 dark:text-gray-300 mt-2">Telusuri berita terbaru</p> -->
      </header>

      <!-- Filter bar -->
      <section class="max-w-7xl mx-auto px-4">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 shadow-sm">
          <div class="flex flex-col md:flex-row md:items-end gap-3">
            <div class="flex-1">
              <label class="block text-sm mb-1 text-gray-600 dark:text-gray-300">Cari Judul</label>
              <input
                v-model="search"
                type="text"
                placeholder="Cari judul berita…"
                class="w-full px-3 py-2 border rounded-lg text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                @keyup.enter="applyFilters"
              />
            </div>

            <div>
              <label class="block text-sm mb-1 text-gray-600 dark:text-gray-300">Tanggal</label>
              <input
                v-model="tanggal"
                type="date"
                class="px-3 py-2 border rounded-lg text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                @change="applyFilters"
              />
            </div>

            <div>
              <label class="block text-sm mb-1 text-gray-600 dark:text-gray-300">Bulan</label>
              <select
                v-model="bulan"
                class="px-3 py-2 border rounded-lg text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                @change="applyFilters"
              >
                <option value="">Semua</option>
                <option v-for="m in 12" :key="m" :value="String(m).padStart(2,'0')">
                  {{ monthName(m) }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm mb-1 text-gray-600 dark:text-gray-300">Tahun</label>
              <select
                v-model="tahun"
                class="px-3 py-2 border rounded-lg text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
                @change="applyFilters"
              >
                <option value="">Semua</option>
                <option v-for="y in years" :key="y" :value="String(y)">{{ y }}</option>
              </select>
            </div>

            <!-- Hanya tombol Reset -->
            <div class="flex gap-2">
              <button
                @click="resetFilters"
                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white rounded-lg text-sm"
              >Reset</button>
            </div>
          </div>
        </div>
      </section>

      <!-- Grid Berita -->
      <section class="max-w-7xl mx-auto px-4 py-6">
        <div v-if="Array.isArray(berita.data) && berita.data.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
          <article
            v-for="b in berita.data"
            :key="b.id"
            class="bg-white dark:bg-gray-800 rounded-2xl shadow hover:shadow-lg transition border border-transparent dark:border-gray-700 overflow-hidden"
          >
            <a :href="route('landing.beritashow', b.slug)" class="block">
              <img
                :src="b.thumbnail_url"
                alt="Thumbnail Berita"
                class="w-full h-40 object-cover"
                @error="$event.target.src='/img/placeholder-news.jpg'"
              />
            </a>

            <div class="p-4">
              <h3 class="font-bold text-base mb-2 leading-snug">
                <a
                  :href="route('landing.beritashow', b.slug)"
                  class="text-gray-900 dark:text-white hover:text-orange-600 dark:hover:text-orange-400 line-clamp-2"
                >
                  {{ b.judul }}
                </a>
              </h3>

              <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
                🗓 {{ formatTanggal(b.tanggal_terbit) }} &nbsp;&nbsp;&nbsp; 👁️ {{ b.jumlah_view }}x
              </p>
              <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">👤 {{ b.penulis_nama || 'Redaksi' }}</p>

              <a :href="route('landing.beritashow', b.slug)" class="inline-block text-sm text-orange-600 hover:underline">
                Baca selengkapnya →
              </a>
            </div>
          </article>
        </div>

        <div v-else class="text-gray-600 dark:text-gray-300 text-sm">
          Belum ada berita sesuai filter.
        </div>
      </section>

      <!-- Pagination -->
      <section class="max-w-7xl mx-auto px-4 pb-10" v-if="Array.isArray(berita.links) && berita.links.length > 3">
        <nav class="flex flex-wrap gap-1 justify-center">
          <Link
            v-for="(link, i) in berita.links"
            :key="i"
            :href="link.url || '#'"
            v-html="link.label"
            :class="[
              'px-3 py-1 rounded text-sm',
              link.active
                ? 'bg-orange-600 text-white'
                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600',
              !link.url && 'opacity-50 cursor-not-allowed'
            ]"
            preserve-state
            preserve-scroll
          />
        </nav>
      </section>

      <!-- Footer -->
      <footer class="bg-gray-100 dark:bg-gray-800 text-center py-6 text-sm text-gray-600 dark:text-gray-300 mt-12 px-4">
        &copy; 2025 Layanan Komunikasi dan Dokumentasi  Pimpinan by Biro Administrasi Pimpinan Kabupaten Mahakam Ulu
      </footer>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, onMounted, computed, watch } from 'vue'

const props = defineProps({
  berita: Object,     // paginator
  filters: Object,    // { search, tahun, bulan, tanggal }
})

const isDarkMode = ref(false)
onMounted(() => {
  isDarkMode.value = localStorage.getItem('theme') === 'dark'
  document.documentElement.classList.toggle('dark', isDarkMode.value)
})
const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light')
  document.documentElement.classList.toggle('dark', isDarkMode.value)
}

// state filter
const search  = ref(props.filters?.search  ?? '')
const tahun   = ref(props.filters?.tahun   ?? '')
const bulan   = ref(props.filters?.bulan   ?? '')
const tanggal = ref(props.filters?.tanggal ?? '')

// daftar tahun (5 tahun ke belakang)
const years = computed(() => {
  const now = new Date().getFullYear()
  return Array.from({ length: 5 }, (_, i) => now - i)
})

// helpers
function monthName(m) {
  const idx = Number(m) - 1
  const nama = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']
  return nama[idx] || '-'
}

function formatTanggal(t) {
  if (!t) return '-'
  const d = new Date(t)
  const nama = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']
  return `${String(d.getDate()).padStart(2,'0')} ${nama[d.getMonth()]} ${d.getFullYear()}`
}

// actions
function applyFilters() {
  router.get(route('landing.listBerita'), {
    search: search.value,
    tahun: tahun.value,
    bulan: bulan.value,
    tanggal: tanggal.value,
    page: 1,         // reset ke halaman pertama
    per_page: 100,   // kirim preferensi 100/item (controller boleh abaikan/ikuti)
  }, {
    preserveState: true,
    replace: true,
    preserveScroll: true,
  })
}

function resetFilters() {
  search.value = ''
  tahun.value = ''
  bulan.value = ''
  tanggal.value = ''
  applyFilters()
}

// === Realtime search (debounce 400ms) ===
function debounce(fn, wait = 400) {
  let t
  return (...args) => {
    clearTimeout(t)
    t = setTimeout(() => fn(...args), wait)
  }
}
const debouncedApply = debounce(applyFilters, 400)
watch(search, () => {
  debouncedApply()
})

// expose data untuk template
const berita = computed(() => props.berita || { data: [], links: [] })
</script>
