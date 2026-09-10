<template>
  <Head title="Layanan Komunikasi dan Dokumentasi  Pimpinan - Pemerintah Kabupaten Mahakam Ulu" />
  <div :class="{ 'dark': isDarkMode }">

  <div class="min-h-screen bg-white dark:bg-gray-900 text-gray-800 dark:text-white">

    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white px-4 py-4 flex flex-wrap justify-between items-center sticky top-0 z-50 shadow">
      <div class="font-bold text-xl mb-2 md:mb-0 w-full md:w-auto text-center md:text-left">
        Layanan Komunikasi dan Dokumentasi  Pimpinan
      </div>
      <div class="flex flex-wrap gap-4 justify-center md:justify-end items-center w-full md:w-auto">
        <a href="#beranda" class="hover:underline text-sm">Beranda</a>
        <a href="login" class="hover:underline text-sm">Kegiatan</a>
        <a href="login" class="hover:underline text-sm">Login</a>
        <a href="register" class="hover:underline text-sm">Register</a>
    
       <button @click="toggleDarkMode" class="border px-3 py-1 rounded text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            {{ isDarkMode ? '☀️' : '🌙' }}
          </button>

      </div>
    </nav>

    <!-- Slider -->
<section
  id="beranda"
  class="relative w-full"
>
  <div class="aspect-[32/10] w-full">
    <img
      src="/img/slider-pimpinan.jpg"
      alt="Slider Layanan Komunikasi dan Dokumentasi  Pimpinan"
      class="w-full h-full object-cover object-center"
    />
  </div>
</section>


    <!-- Sambutan -->
    <section id="sambutan" class="max-w-4xl mx-auto px-4 py-12 text-center">
      <H3 class="text-2xl font-semibold text-orange-600 mb-4">KERJA NYATA, TERSAJI JELAS</H3>
      <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-base sm:text-lg">
        Selamat datang di Layanan Komunikasi dan Dokumentasi  Pimpinan. Layanan yang dikelola Bagian Protokol dan Komunikasi Pimpinan menyajikan dokumentasi, sambutan dan informasi kegiatan pimpinan. Kami berkomitmen memberikan akses publik terhadap dokumen sambutan/materi pimpinan, dokumentasi kegiatan pimpinan serta permohonan berkaitan dengan dokumentasi kegiatan pimpinan lainnya.
      </p>
    </section>

        <!-- Section Permohonan dan Kegiatan -->
    <section class="max-w-7xl mx-auto px-4 py-12" id="kegiatan">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Permohonan -->
        <div>
          <h2 class="text-xl font-semibold text-orange-600 mb-4">Permohonan Terbaru</h2>
          <div
            v-for="p in permohonanTerbaru"
            :key="p.id"
            class="mb-4 p-4 bg-gray-100 dark:bg-gray-800 rounded shadow"
          >
            <p class="text-sm text-gray-500 dark:text-gray-400">Dari: {{ p.user.name }}</p>
            <p class="font-bold text-lg">{{ p.judul }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
              Kategori: {{ p.kategori }} | Prioritas: {{ p.prioritas }}
            </p>
          </div>
        </div>

        <!-- Folder Publik -->
        <div>
          <h2 class="text-xl font-semibold text-orange-600 mb-4">Kegiatan Terbaru</h2>
          <div
            v-for="folder in folderPublik"
            :key="folder.id"
            class="mb-4 p-4 bg-gray-100 dark:bg-gray-800 rounded shadow overflow-hidden"
          >
            <h3 class="font-bold text-lg mb-2">
              <a
                :href="`/kegiatan/${folder.slug}`"
                class="text-orange-600 hover:underline break-words"
              >
                📁 {{ folder.judul }}
              </a>
            </h3>
            <!-- <ul class="list-disc pl-5 text-sm text-gray-800 dark:text-gray-200 space-y-1">
              <li
                v-for="file in folder.files"
                :key="file.id"
              >
                <a
                  :href="`/storage/${file.path}`"
                  class="text-blue-600 hover:underline break-all"
                  target="_blank"
                >
                  {{ file.nama_file }}
                </a>
              </li>
            </ul> -->
          </div>
        </div>
      </div>
    </section>

<!-- Section Statistik -->
<section id="statistik" class="max-w-7xl mx-auto px-4 py-12">
  <div ref="statSection" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 text-center">
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
      &copy; 2025 Layanan Komunikasi dan Dokumentasi  Pimpinan by Biro Administrasi Pimpinan Kabupaten Mahakam Ulu
    </footer>
  </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import useDarkMode from '@/Composables/useDarkMode'
import { ref, reactive, onMounted } from 'vue'
import { FolderKanban, User, FileText, Download } from 'lucide-vue-next'

const isDarkMode = ref(false)


const props = defineProps({
  permohonanTerbaru: Array,
  folderPublik: Array,
  statistikPermohonan: Object,
  statistik: Object, 
  feedbackStats: Object,
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

</script>
