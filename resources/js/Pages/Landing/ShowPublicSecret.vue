<template>
  <Head :title="`${folder.judul} - Layanan Dokumentasi Pimpinan`" />

  <div :class="{ 'dark': isDarkMode }">
     <div class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 px-4 py-3 text-center font-semibold">
        ⚠️ Halaman secret didapatkan setelah mengajukan permohonan
      </div>

    <div class="min-h-screen bg-white dark:bg-gray-900 text-gray-800 dark:text-white transition-colors duration-300">

      <!-- Navbar -->
      <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 sm:px-6 py-3 sm:py-4 flex flex-wrap gap-4 justify-between items-center sticky top-0 z-50">
        <h1 class="text-lg sm:text-xl font-bold truncate max-w-full">📁 {{ folder.judul }}</h1>

        <div class="flex flex-wrap gap-2 sm:gap-3">
          <button @click="toggleDarkMode" class="border px-3 py-1 rounded text-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            {{ isDarkMode ? '☀️' : '🌙' }}
          </button>
          <button @click="showFeedbackModal = true" class="bg-orange-500 text-white px-3 py-1 rounded hover:bg-orange-600 text-sm shadow">
        📝 Feedback/Survei
        </button>

          <button @click="copyLink" class="bg-orange-500 text-white px-3 py-1 rounded hover:bg-orange-600 text-sm shadow">
            🔗 Salin Link
          </button>
          <a :href="`/kegiatan/${folder.slug_secret}/secret-download-zip`" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded text-sm shadow whitespace-nowrap">
            📥 Unduh ZIP
          </a>
        </div>
      </nav>

      <!-- Breadcrumb -->
      <div class="max-w-7xl mx-auto px-4 mt-4 sm:mt-6 text-sm text-gray-500 dark:text-gray-400">
        <span class="hover:underline"><a href="/">🏠 Beranda</a></span> &raquo;
        <span class="text-orange-600 font-medium">📁 {{ folder.judul }}</span>
      </div>

      <!-- Search + Toggle View -->
      <div class="max-w-7xl mx-auto px-4 mt-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <!-- Search Input -->
        <div class="relative w-full md:w-1/2">
          <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">🔍</span>
          <input
            type="text"
            v-model="searchQuery"
            class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500"
            placeholder="Cari nama file..."
          />
        </div>

        <!-- View Toggle -->
        <div class="flex gap-2 items-center">
          <button @click="isGridView = true" :class="{ 'text-orange-600': isGridView }" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
            <LayoutGrid class="w-5 h-5" />
          </button>
          <button @click="isGridView = false" :class="{ 'text-orange-600': !isGridView }" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
            <List class="w-5 h-5" />
          </button>
        </div>
      </div>

      <!-- File List -->
      <div class="max-w-7xl mx-auto px-4 py-10">
        
    <h2 class="text-lg sm:text-xl font-semibold text-orange-600 mb-6">
   Total File ({{ files.length }})
        
</h2>
  <p class="text-sm text-gray-500 dark:text-gray-300">
        📅 {{ formatTanggal(folder.tanggal_kegiatan) }} | 👤 Dihadiri oleh: {{ folder.pejabat_hadir }}
        </p>
<div v-if="jumlah_unchecked > 0" class="text-sm text-gray-600 dark:text-gray-300 mb-4">
</div>


        <div v-if="filteredFiles.length === 0" class="text-gray-600 dark:text-gray-300">
          Tidak ada file ditemukan.
        </div>

        <div v-else :class="isGridView ? 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6' : 'space-y-4'">

        <div
  v-for="file in filteredFiles"
  :key="file.id"
  :class="[
    'border rounded-lg p-4 shadow-sm bg-gray-50 dark:bg-gray-800 transition',
    !isGridView && 'flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3'
  ]"
>
<div class="mb-3">
            <div class="cursor-pointer flex-1" @click="handleFileClick(file)">

  <img
    v-if="isImage(file.nama_file)"
    :src="`/storage/${file.path}`"
    alt="{{ file.nama_file }}"
    class="w-full h-32 object-cover rounded shadow"
  />
  <img
    v-else-if="isPDF(file.nama_file)"
    src="/img/pdf-icon.png"
    alt="{{ file.nama_file }}"
    class="w-full h-32 object-cover rounded shadow"
  />
  <div
    v-else
    alt="{{ file.nama_file }}"
    class="w-full h-32 object-cover rounded shadow"
  >
    📄
  </div>
  </div>
</div>
            <div class="cursor-pointer flex-1" @click="handleFileClick(file)">
                 <!-- <div v-if="isImage(file.nama_file)" class="mb-2">
    <img
      :src="`/storage/${file.path}`"
      alt="Thumbnail"
      class="w-full h-32 object-cover rounded shadow"
    />
  </div> -->
              <div class="font-semibold text-base sm:text-lg truncate">{{ file.nama_file }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-300 mt-1">Size: {{ formatSize(file.size) }}</div>
            </div>

            <button
              @click="downloadFile(file)"
              class="mt-2 sm:mt-0 inline-block text-sm text-orange-600 hover:underline whitespace-nowrap"
            >
              📥 Unduh ({{ file.jumlah_download }}x)
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Preview -->
      <div v-if="previewFile" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-900 p-4 rounded-lg w-full max-w-6xl max-h-[90vh] overflow-auto relative">
          <button @click="previewFile = null" class="absolute top-3 right-4 text-gray-700 dark:text-white text-xl">✕</button>

          <template v-if="isImage(previewFile.nama_file)">
            <img :src="`/storage/${previewFile.path}`" alt="Preview" class="w-full max-h-[80vh] object-contain rounded" />
          </template>

          <template v-else-if="isPDF(previewFile.nama_file)">
            <iframe :src="`/storage/${previewFile.path}`" class="w-full h-[80vh] rounded" frameborder="0" />
          </template>
        </div>
      </div>

      <!-- Footer -->
      <footer class="bg-gray-100 dark:bg-gray-800 text-center py-6 text-sm text-gray-600 dark:text-gray-300 mt-12">
        &copy; 2025 Layanan Dokumentasi Pimpinan by Biro Administrasi Pimpinan Kabupaten Mahakam Ulu
      </footer>

      <!-- Modal Feedback -->
<div v-if="showFeedbackModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4">
  <div class="bg-white dark:bg-gray-900 p-6 rounded-lg w-full max-w-md text-center">
    <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Bagaimana menurut Anda <br>Pelayanan Dokumentasi Pimpinan?</h2>

    <div class="flex justify-center gap-6 text-3xl mb-4">
      <span @click="selectedEmote = 'senyum-lebar'" :class="{ 'scale-110': selectedEmote === 'senyum-lebar' }" class="cursor-pointer">😁</span>
      <span @click="selectedEmote = 'senyum-tipis'" :class="{ 'scale-110': selectedEmote === 'senyum-tipis' }" class="cursor-pointer">🙂</span>
      <span @click="selectedEmote = 'merengut'" :class="{ 'scale-110': selectedEmote === 'merengut' }" class="cursor-pointer">😕</span>
    </div>

    <button
      :disabled="!selectedEmote"
      @click="submitFeedback"
      class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded disabled:opacity-50"
    >
      Kirim Feedback
    </button>
    <div class="mt-4">
      <button @click="showFeedbackModal = false" class="text-sm text-gray-500 hover:underline">Tutup</button>
    </div>
  </div>
</div>

      <!-- Modal kalau belum login -->
      <div v-if="!auth_user" class="fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg text-center max-w-sm w-full">
          <h2 class="text-lg font-bold mb-4">Anda harus login ketika akses link ini</h2>
          <a href="/login" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded shadow">
            🔑 Login Sekarang
          </a>
        </div>
      </div>

      <!-- Konten utama (copy dari ShowPublic.vue) -->
      <!-- ... -->
    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import { LayoutGrid, List } from 'lucide-vue-next'
import axios from 'axios'
import { toast } from 'vue3-toastify'

const props = defineProps({
  folder: Object,
  files: Array,
  jumlah_unchecked: Number,
  auth_user: Object, // ✅ tambahan props dari controller
})


const showFeedbackModal = ref(false)
const selectedEmote = ref(null)
const searchQuery = ref('')
const isDarkMode = ref(false)
const isGridView = ref(true)
const previewFile = ref(null)
const checkedFiles = computed(() => props.files.filter(file => file.checked))
const totalFiles = computed(() => props.files.length + props.jumlah_unchecked)


onMounted(() => {
  isDarkMode.value = localStorage.getItem('theme') === 'dark'
  document.documentElement.classList.toggle('dark', isDarkMode.value)
})

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light')
  document.documentElement.classList.toggle('dark', isDarkMode.value)
}

const filteredFiles = computed(() => {
  if (!searchQuery.value) return props.files
  return props.files.filter(file =>
    file.nama_file.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const copyLink = () => {
  const link = window.location.href
  navigator.clipboard.writeText(link).then(() => {
    toast.success('🔗 Link berhasil disalin!')
  }).catch(() => {
    toast.error('❌ Gagal menyalin link.')
  })
}
const downloadFile = async (file) => {
  try {
    await axios.post(`/kegiatan/file/${file.id}/download`)
    file.jumlah_download += 1

    const a = document.createElement('a')
    a.href = `/storage/${file.path}`
    a.download = file.nama_file
    a.target = '_blank'
    a.click()
  } catch (error) {
    alert('Gagal mengunduh file.')
    console.error(error)
  }
}

const submitFeedback = async () => {
  try {
    await axios.post('/feedback', {
      emote: selectedEmote.value,
      slug: props.folder.id
    })
    showFeedbackModal.value = false
    selectedEmote.value = null
    toast.success('Terima kasih telah melakukan feedback terhadap layanan kami - Regard Biro Administrasi Pimpinan')
  } catch (error) {
    toast.error('❌ Gagal mengirim feedback')
  }
}


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

function formatSize(bytes) {
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  if (bytes === 0) return '0 Byte'
  const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))
  return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i]
}

const isImage = (filename) => /\.(jpg|jpeg|png|gif|webp)$/i.test(filename)
const isPDF = (filename) => /\.pdf$/i.test(filename)
const isPreviewable = (filename) => isImage(filename) || isPDF(filename)

const handleFileClick = (file) => {
  if (isPreviewable(file.nama_file)) {
    previewFile.value = file
  } else {
    downloadFile(file)
  }
}
</script>
