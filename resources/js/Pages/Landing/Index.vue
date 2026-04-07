<template>
  <Head title="Potret Pimpinan - Pemerintah Provinsi Kalimantan Timur" />
  <div :class="{ 'dark': isDarkMode }">

  <div class="min-h-screen bg-white dark:bg-gray-900 text-gray-800 dark:text-white">

    <!-- Navbar -->
     <nav class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white px-4 py-4 shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-3">
          <a href="/" class="flex items-center gap-2">
            <img src="/img/logo-only.png" alt="Logo" class="h-8 w-8 object-contain" />
            <span class="font-bold text-xl">Potret Pimpinan</span>
          </a>
          <div class="flex items-center gap-4 text-sm">
            <a href="/" class="text-orange-600 hover:underline">Beranda</a>
            <a href="/list-kegiatan" class="hover:underline">Kegiatan</a>
            <!-- <a href="/list-berita" class="hover:underline">Berita</a> -->
                <a href="login" class="hover:underline text-sm">Login</a>
    <a href="register" class="hover:underline text-sm">Register</a>
            <button @click="toggleDarkMode" class="border px-3 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
              {{ isDarkMode ? '☀️' : '🌙' }}
            </button>
          </div>
        </div>
      </nav>

    <!-- Slider -->
<section
  id="beranda"
  class="relative w-full"
>
  <div class="aspect-[32/10] w-full">
    <img
      src="/img/slider-pimpinan-fix.jpg"
      alt="Slider Potret Pimpinan"
      class="w-full h-full object-cover object-center"
    />
  </div>
</section>


    <!-- Sambutan -->
<!-- Sambutan -->
<section id="sambutan" class="max-w-7xl mx-auto px-4 py-12">
  <!-- Judul tetap di atas -->
  <h3 class="text-2xl font-semibold text-orange-600 mb-6 text-center">
    KERJA NYATA, TERSAJI JELAS
  </h3>

  <!-- Konten: kiri gambar, kanan teks (stack di mobile) -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
    <!-- Kiri: Gambar -->
    <div class="flex justify-center">
      <img
        src="/img/pimpinan-min.png"
        alt="Pimpinan Provinsi Kalimantan Timur"
        class="w-full max-w-2xl h-auto object-contain"
      />
    </div>

    <!-- Kanan: Teks -->
    <div class="text-gray-700 dark:text-gray-300 leading-relaxed text-base sm:text-lg md:text-left text-center">
      <p>
        Selamat datang di platform <strong>Potret Pimpinan</strong>. Platform ini menyajikan dokumentasi,
        sambutan dan informasi kegiatan pimpinan. Kami berkomitmen memberikan akses publik terhadap dokumen
        sambutan/materi pimpinan, dokumentasi dan komunikasi pimpinan serta permohonan.
      </p>
    </div>
  </div>
</section>


    <!-- Section Kegiatan Terbaru -->
<section id="kegiatan" class="max-w-7xl mx-auto px-4 py-12">
  <div class="text-center mb-12">
    <h2 class="text-2xl font-bold text-orange-600">Kegiatan Terbaru</h2>
    <p class="text-gray-600 dark:text-gray-300 mt-2">
      Daftar folder kegiatan terbaru yang tersedia untuk publik
    </p>
  </div>

  <!-- Grid Card Folder -->
  <div
    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6"
  >
   <div
  v-for="folder in folderPublik"
  :key="folder.id"
  class="bg-white dark:bg-gray-800 rounded-2xl shadow hover:shadow-lg transition p-6 text-left border border-transparent dark:border-gray-700"
>
  <!-- Judul Folder -->
  <h3 class="font-bold text-lg mb-2">
    <a
      :href="`/kegiatan/${folder.slug}`"
      class="text-orange-600 hover:underline break-words"
    >
      📁 {{ truncate(folder.judul) }}
    </a>
  </h3>

  <!-- Detail kegiatan -->
  <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
    📅 {{ formatTanggal(folder.tanggal_kegiatan) }}
  </p>
  <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
    👤 Dihadiri: {{ folder.pejabat_hadir }}
  </p>
  <p class="text-sm text-gray-600 dark:text-gray-300">
    📄 Total File: {{ folder.files.length }}
  </p>
</div>

  </div>

  <!-- Button Lihat Semua -->
  <div class="text-center mt-10">
    <a
      href="/list-kegiatan"
      class="inline-block bg-orange-600 text-white font-semibold px-6 py-3 rounded-xl shadow hover:bg-orange-700 transition"
    >
      Lihat Semua Kegiatan →
    </a>
  </div>
</section>

<!-- ===== Section Sambutan ===== -->
<section id="sambutan" class="max-w-7xl mx-auto px-4 py-12">
  <div class="text-center mb-5">
    <h2 class="text-2xl font-bold text-orange-600">Sambutan</h2>
    <!-- <p class="text-gray-600 dark:text-gray-300 mt-2">
      Sambutan tersedia dengan mengajukan permohonan, Silakan ajukan permohonan
      untuk mendapatkan file naskah lengkap sambutan.
    </p> -->
  </div>

  <!-- Kartu info + CTA -->
  <div class="max-w-3xl mx-auto">
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl shadow hover:shadow-lg transition p-6 border border-transparent dark:border-gray-700"
    >
      <div class="flex items-start gap-4">
        <div
          class="shrink-0 grid place-items-center w-12 h-12 rounded-full bg-orange-50 text-orange-600 dark:bg-gray-900 dark:text-orange-400"
          aria-hidden="true"
        >
          📣
        </div>

        <div class="flex-1">
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
            Informasi Sambutan
          </h3>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
            Sambutan tersedia dengan <span class="font-medium">mengajukan permohonan</span>.
            Silakan ajukan permohonan untuk mendapatkan
            <span class="font-medium">file naskah lengkap sambutan</span>.
          </p>

          <div class="mt-4">
            <a
              href="/permohonan/create"
              class="inline-block bg-orange-600 text-white font-semibold px-5 py-2 rounded-xl shadow hover:bg-orange-700 transition"
            >
              Ajukan Permohonan →
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>



<!-- Section Berita Terbaru -->
<section id="berita-terbaru" class="max-w-7xl mx-auto px-4 py-12">
  <div class="text-center mb-12">
    <h2 class="text-2xl font-bold text-orange-600">Berita Terbaru</h2>
    <p class="text-gray-600 dark:text-gray-300 mt-2">
      Kumpulan berita terbaru untuk Anda
    </p>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-6">
    <article
      v-for="b in props.beritaTerbaru"
      :key="b.id"
      class="bg-white dark:bg-gray-800 rounded-2xl shadow hover:shadow-lg transition border border-transparent dark:border-gray-700 overflow-hidden"
    >
      <!-- Thumbnail -->
      <a :href="route('landing.beritashow', b.slug)" class="block">
        <img
          :src="b.thumbnail_url"
          alt="Thumbnail Berita"
          class="w-full h-40 object-cover"
          @error="$event.target.src='/img/placeholder-news.jpg'"
        />
      </a>

      <!-- Body -->
      <div class="p-4">
        <h3 class="font-bold text-base mb-2 leading-snug">
          <a
            :href="route('landing.beritashow', b.slug)"
            class="text-gray-900 dark:text-white hover:text-orange-600 dark:hover:text-orange-400 line-clamp-2"
          >
            {{ b.judul }}
          </a>
        </h3>

          <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">🗓 {{ formatTanggal(b.tanggal_terbit) }} &nbsp;&nbsp;&nbsp; 👁️ {{ b.jumlah_view }}x</p>
          <p class="text-sm text-gray-600 dark:text-gray-300 mb-1"></p>
          <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">👤 {{ b.penulis_nama || 'Redaksi' }}</p>


        <a
          :href="route('landing.beritashow', b.slug)"
          class="inline-block text-sm text-orange-600 hover:underline"
        >
          Baca selengkapnya →
        </a>
      </div>
    </article>
  </div>
    <!-- Button Lihat Semua -->
  <div class="text-center mt-10">
    <a
      href="/list-berita"
      class="inline-block bg-orange-600 text-white font-semibold px-6 py-3 rounded-xl shadow hover:bg-orange-700 transition"
    >
      Lihat Semua Berita →
    </a>
  </div>
</section>

<!-- Section Permohonan -->
<section class="max-w-7xl mx-auto px-4 py-12" id="permohonan">
  <div class="text-center mb-12">
    <h2 class="text-2xl font-bold text-orange-600">Permohonan Terbaru</h2>
  </div>

  <!-- List Permohonan -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
    <div
      v-for="p in permohonanTerbaru"
      :key="p.id"
      class="mb-4 p-4 bg-gray-100 dark:bg-gray-800 rounded shadow"
    >
      <p class="text-sm text-gray-500 dark:text-gray-400">
        Dari: {{ maskName(p.user.name) }}
      </p>

      <p class="font-bold text-lg">{{ p.judul }}</p>
      <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
        Permohonan untuk {{ p.kategori }}
      </p>
    </div>
  </div>

  <!-- Button Ajukan -->
  <div class="text-center">
   <a
      href="/login"
      class="inline-block bg-orange-600 text-white font-semibold px-6 py-3 rounded-xl shadow hover:bg-orange-700 transition"
    >
      Ajukan Permohonan →
    </a>
  </div>
</section>



<!-- Section Statistik -->
<section id="statistik" class="max-w-7xl mx-auto px-4 py-12">
  <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow p-6">
  <!-- Title -->
  <div class="flex items-center gap-2 mb-8">
          <svg class="w-8 h-8 text-orange-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round"
    d="M3 3v18h18M8 17v-6M13 17V9M18 17v-3" />
</svg>

      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Statistik Kegiatan</h3>
  </div>

  <!-- Grid Items -->
  <div
    ref="statSection"
    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 text-center"
  >
    <div
      v-for="item in statistikItems"
      :key="item.label"
      class="p-6 bg-gray-100 dark:bg-gray-800 rounded shadow hover:shadow-md transition transform hover:-translate-y-1 duration-300"
    >
      <div class="text-4xl mb-2 text-orange-600">
        <component :is="item.icon" class="inline-block w-10 h-10" />
      </div>
      <div class="text-2xl font-bold text-gray-900 dark:text-white">
        {{ animatedCount[item.key] }}
      </div>
      <div class="text-sm text-gray-500 dark:text-gray-400">{{ item.label }}</div>
    </div>
  </div>
  </div>
</section>

<!-- Statistik Permohonan -->
<section class="max-w-7xl mx-auto px-4 pt-0 pb-12">
  <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow p-6">
    <div class="flex items-center mb-4">
      <svg class="w-8 h-8 text-orange-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
  <path stroke-linecap="round" stroke-linejoin="round"
    d="M3 3v18h18M8 17v-6M13 17V9M18 17v-3" />
</svg>

      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Statistik Permohonan</h3>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 text-center">
      <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded shadow hover:shadow-md transition transform hover:-translate-y-1 duration-300">
        <div class="text-sm text-gray-500 dark:text-gray-400">Total Permohonan</div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ permohonanCount.total }}
        </div>
      </div>
      <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded shadow">
        <div class="text-sm text-gray-500 dark:text-gray-400">Sambutan</div>
        <div class="text-xl font-semibold text-orange-600">
          {{ permohonanCount.sambutan }}
        </div>
      </div>
      <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded shadow">
        <div class="text-sm text-gray-500 dark:text-gray-400">Dokumentasi</div>
        <div class="text-xl font-semibold text-orange-600">
          {{ permohonanCount.dokumentasi }}
        </div>
      </div>
      <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded shadow">
        <div class="text-sm text-gray-500 dark:text-gray-400">Lainnya</div>
        <div class="text-xl font-semibold text-orange-600">
          {{ permohonanCount.lainnya }}
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Statistik Feedback -->
<section class="max-w-7xl mx-auto px-4 pt-0 pb-12">
  <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow p-6">
    <div class="flex items-center mb-4">
      <svg class="w-8 h-8 text-orange-600 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 0 1 1.789 2.894l-1.383 2.765A2 2 0 0 1 17.41 17H14v-7zM10 17H6.59a2 2 0 0 1-1.76-1.341L3.447 12.89A2 2 0 0 1 5.236 10H10v7z" />
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Statistik Feedback</h3>
    </div>
    <div class="grid grid-cols-3 gap-6 text-center">
      <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded shadow hover:shadow-md transition transform hover:-translate-y-1 duration-300">
        <div class="text-3xl mb-2">😄</div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ feedbackStats.happy }}
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">Senang</div>
      </div>
      <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded shadow hover:shadow-md transition transform hover:-translate-y-1 duration-300">
        <div class="text-3xl mb-2">😐</div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ feedbackStats.neutral }}
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">Biasa Saja</div>
      </div>
      <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded shadow hover:shadow-md transition transform hover:-translate-y-1 duration-300">
        <div class="text-3xl mb-2">😞</div>
        <div class="text-2xl font-bold text-gray-900 dark:text-white">
          {{ feedbackStats.sad }}
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">Kurang Puas</div>
      </div>
    </div>
  </div>
</section>




    <!-- Footer -->
    <footer class="bg-gray-100 dark:bg-gray-800 text-center py-6 text-sm text-gray-600 dark:text-gray-300 mt-12 px-4">
      &copy; 2025 Potret Pimpinan by Biro Administrasi Pimpinan Provinsi Kalimantan Timur
    </footer>
  </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import useDarkMode from '@/Composables/useDarkMode'
import { ref, reactive, onMounted } from 'vue'
import { FolderKanban, User, FileText, Download, BarChart3 } from 'lucide-vue-next'

const isDarkMode = ref(false)


const props = defineProps({
  permohonanTerbaru: Array,
  folderPublik: Array,
  statistikPermohonan: Object,
  statistik: Object, 
  feedbackStats: Object,
  beritaTerbaru: Array,
})

const statSection = ref(null)
const hasAnimated = ref(false)

const statistikItems = [
  { label: 'Total Kegiatan', key: 'folder', icon: FolderKanban },
  { label: 'Total File', key: 'file', icon: FileText },
  { label: 'Total Unduhan', key: 'download', icon: Download },
  { label: 'Total User Terdaftar', key: 'user', icon: User },

]

const animatedCount = reactive({
  folder: 0,
  user: 0,
  file: 0,
  download: 0,
})

const permohonanCount = reactive({
  total: 0,
  sambutan: 0,
  dokumentasi: 0,
  lainnya: 0,
})

const animatePermohonanCount = (key, target) => {
  let start = 0
  const duration = 1500
  const step = Math.ceil(target / (duration / 16))
  const interval = setInterval(() => {
    start += step
    if (start >= target) {
      permohonanCount[key] = target
      clearInterval(interval)
    } else {
      permohonanCount[key] = start
    }
  }, 16)
}

const animateCount = (key, target) => {
  let start = 0
  const duration = 1500
  const step = Math.ceil(target / (duration / 16))
  const interval = setInterval(() => {
    start += step
    if (start >= target) {
      animatedCount[key] = target
      clearInterval(interval)
    } else {
      animatedCount[key] = start
    }
  }, 16)
}

const startCountAnimation = () => {
  if (hasAnimated.value) return
  hasAnimated.value = true

  animateCount('folder', props.statistik.totalFolder)
  animateCount('user', props.statistik.totalUser)
  animateCount('file', props.statistik.totalFile)
  animateCount('download', props.statistik.totalDownload)
  animatePermohonanCount('total', props.statistikPermohonan.total)
animatePermohonanCount('sambutan', props.statistikPermohonan.sambutan)
animatePermohonanCount('dokumentasi', props.statistikPermohonan.dokumentasi)
animatePermohonanCount('lainnya', props.statistikPermohonan.lainnya)
}

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light')
  document.documentElement.classList.toggle('dark', isDarkMode.value)
}


onMounted(() => {
    isDarkMode.value = localStorage.getItem('theme') === 'dark'
  document.documentElement.classList.toggle('dark', isDarkMode.value)
  const observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) {
        startCountAnimation()
        observer.disconnect()
      }
    },
    { threshold: 0.3 }
  )
  if (statSection.value) {
    observer.observe(statSection.value)
  }
})

function formatTanggal(tanggal) {
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
  return text.length > length ? text.slice(0, length) + '...' : text
}

const maskName = (name) => {
  if (!name) return ''
  if (name.length <= 2) return name // kalau terlalu pendek biarkan apa adanya

  const start = name.slice(0, 2)
  const end = name.slice(-2)
  const middle = '*'.repeat(name.length - 4)

  return start + middle + end
}
</script>
