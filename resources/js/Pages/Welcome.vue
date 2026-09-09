<template>
  <div class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white min-h-screen">
    
    <!-- Navbar -->
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow">
      <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold text-orange-600">Layanan Dokumentasi Pimpinan</h1>
        <div class="space-x-6 text-sm font-medium">
          <a href="#beranda" class="hover:text-orange-600">Beranda</a>
          <a href="#sambutan" class="hover:text-orange-600">Sambutan</a>
          <a href="#kegiatan" class="hover:text-orange-600">Kegiatan</a>
          <a href="#tentang" class="hover:text-orange-600">Tentang</a>
        </div>
      </div>
    </nav>

    <!-- Hero Slider -->
    <section id="beranda" class="relative h-[400px] overflow-hidden bg-gray-100 dark:bg-gray-800">
      <img src="/storage/illustrasi/pimpinan-3d.jpg" alt="Slider" class="w-full h-full object-cover opacity-90">
      <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
        <h2 class="text-white text-3xl md:text-5xl font-bold text-center">Layanan Dokumentasi Pimpinan Kabupaten Mahakam Ulu</h2>
      </div>
    </section>

    <!-- Section Sambutan -->
    <section id="sambutan" class="py-16 bg-white dark:bg-gray-900 px-4 md:px-10">
      <div class="max-w-4xl mx-auto text-center space-y-4">
        <h2 class="text-2xl font-bold text-orange-600">Sambutan</h2>
        <p class="text-md md:text-lg leading-relaxed">
          Selamat datang di halaman resmi dokumentasi kegiatan pimpinan. Halaman ini dibuat untuk memudahkan publik melihat potret kegiatan pimpinan secara transparan dan akuntabel.
        </p>
      </div>
    </section>

    <!-- Section Kegiatan + Permohonan -->
    <section id="kegiatan" class="py-16 bg-gray-50 dark:bg-gray-800 px-4 md:px-10">
      <div class="max-w-7xl mx-auto">
        <div class="grid md:grid-cols-2 gap-8">
          
          <!-- Permohonan Terbaru -->
          <div>
            <h3 class="text-xl font-bold mb-4 text-orange-600">Permohonan Terbaru</h3>
            <ul class="space-y-4">
              <li v-for="perm in permohonan" :key="perm.id" class="bg-white dark:bg-gray-700 p-4 rounded shadow">
                <h4 class="font-semibold text-md">{{ perm.judul }}</h4>
                <p class="text-sm text-gray-600 dark:text-gray-300">Diajukan oleh: {{ perm.user.name }}</p>
              </li>
            </ul>
          </div>

          <!-- Kegiatan Publik -->
          <div>
            <h3 class="text-xl font-bold mb-4 text-orange-600">Folder Kegiatan Publik</h3>
            <ul class="space-y-4">
              <li v-for="folder in folders" :key="folder.id" class="bg-white dark:bg-gray-700 p-4 rounded shadow hover:bg-orange-50 dark:hover:bg-gray-600 cursor-pointer">
                <a :href="route('kegiatan.folders.show', folder.slug)">
                  <h4 class="font-semibold text-md">📁 {{ folder.judul }}</h4>
                  <p class="text-sm text-gray-600 dark:text-gray-300">Jumlah file: {{ folder.files.length }}</p>
                </a>
              </li>
            </ul>
          </div>

        </div>
      </div>
    </section>

    <!-- Tentang -->
    <section id="tentang" class="py-16 bg-white dark:bg-gray-900 px-4 md:px-10">
      <div class="max-w-4xl mx-auto text-center space-y-4">
        <h2 class="text-2xl font-bold text-orange-600">Tentang</h2>
        <p class="text-md md:text-lg leading-relaxed">
          Aplikasi ini dibangun oleh Biro Administrasi Pimpinan Kabupaten Mahakam Ulu sebagai dokumentasi dan transparansi kegiatan pimpinan daerah. Dibangun dengan Laravel + Vue SPA.
        </p>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-100 dark:bg-gray-800 py-6 border-t border-gray-300 dark:border-gray-700 text-center">
      <p class="text-sm text-gray-700 dark:text-gray-300">
        &copy; 2025 Layanan Dokumentasi Pimpinan by Biro Administrasi Pimpinan Kabupaten Mahakam Ulu
      </p>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'

const permohonan = ref([])
const folders = ref([])

onMounted(async () => {
  try {
    const permohonanRes = await axios.get('/api/permohonan/terbaru')
    permohonan.value = permohonanRes.data

    const folderRes = await axios.get('/api/kegiatan/folder-publik')
    folders.value = folderRes.data
  } catch (e) {
    console.error(e)
  }
})
</script>
