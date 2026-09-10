<template>
  <Head :title="berita.judul + ' - Berita'" />

  <div :class="{ 'dark': isDarkMode }">
    <div class="min-h-screen bg-white dark:bg-gray-900 text-gray-800 dark:text-white">
      <!-- Navbar sangat ringkas; sesuaikan dengan navbar landing-mu bila perlu -->
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
      <!-- Content -->
       
      <main class="max-w-3xl mx-auto px-4 py-8">
         <div class="mt-5 mb-10">
          <a href="/" class="text-orange-600 hover:underline">← Kembali ke Beranda</a>
          <hr class="mt-2">
        </div>
        
        <h1 class="text-2xl sm:text-3xl font-bold mb-3 leading-snug">{{ berita.judul }}</h1>

        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500 dark:text-gray-400 mb-6">
          <span>🗓 {{ formatTanggal(berita.tanggal_terbit) }}</span>
          <span>•</span>
          <span>👤 {{ berita.penulis_nama || 'Redaksi' }}</span>
          <span>•</span>
          <span>👁️ {{ berita.jumlah_view }}x dibaca</span>
        </div>

        <img
          :src="berita.thumbnail_url"
          alt="Thumbnail"
          class="w-full rounded-xl shadow mb-6 object-cover max-h-[420px]"
          @error="$event.target.src='/img/placeholder-news.jpg'"
        />

        <!-- Isi berita -->
        <article
          class="prose dark:prose-invert max-w-none prose-img:rounded-xl prose-a:text-orange-600"
          v-html="berita.isi_berita"
        ></article>

        <div class="mt-10">
          <a href="/" class="text-orange-600 hover:underline">← Kembali ke Beranda</a>
        </div>
      </main>

      <footer class="bg-gray-100 dark:bg-gray-800 text-center py-6 text-sm text-gray-600 dark:text-gray-300 mt-12 px-4">
        &copy; 2025 Layanan Komunikasi dan Dokumentasi  Pimpinan by Biro Administrasi Pimpinan Kabupaten Mahakam Ulu
      </footer>
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'

const props = defineProps({
  berita: Object,
})

const isDarkMode = ref(false)
const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light')
  document.documentElement.classList.toggle('dark', isDarkMode.value)
}

onMounted(() => {
  isDarkMode.value = localStorage.getItem('theme') === 'dark'
  document.documentElement.classList.toggle('dark', isDarkMode.value)
})

function formatTanggal(tanggal) {
  if (!tanggal) return '-'
  const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']
  const d = new Date(tanggal)
  return `${String(d.getDate()).padStart(2,'0')} ${bulan[d.getMonth()]} ${d.getFullYear()}`
}
</script>

<style>
/* Tip: aktifkan @tailwind/typography untuk styling isi_berita */
.prose :where(img):not(:where([class~="not-prose"] *)) {
  margin-top: 1rem;
  margin-bottom: 1rem;
}
</style>
