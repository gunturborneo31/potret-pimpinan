<template>
  <Head :title="`${folder.judul} - Layanan Dokumentasi Pimpinan`" />

  <div :class="{ 'dark': isDarkMode }">
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
          <a :href="`/kegiatan/${folder.slug}/download-zip`" class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded text-sm shadow whitespace-nowrap">
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
  File yang dapat diunduh ({{ checkedCount }})<br>
  <template v-if="jumlah_unchecked > 0">
    Total ada ({{ totalFiles }}). Silakan ajukan
    <a href="/login" style="text-decoration: underline;">permohonan</a>
    untuk mendapatkan semua filenya.
  </template>
</h2>

        <p class="text-sm text-gray-500 dark:text-gray-300">
          📅 {{ formatTanggal(folder.tanggal_kegiatan) }} | 👤 Dihadiri oleh: {{ folder.pejabat_hadir }}
        </p>

        <div v-if="filteredFiles.length === 0" class="text-gray-600 dark:text-gray-300 mt-4">
          Tidak ada file ditemukan.
        </div>

        <div
          v-else
          :class="isGridView ? 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6 mt-6' : 'space-y-4 mt-6'"
        >
          <div
            v-for="file in filteredFiles"
            :key="file.id"
            :class="[
              'relative border rounded-lg p-4 shadow-sm bg-gray-50 dark:bg-gray-800 transition',
              !isGridView && 'flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3'
            ]"
          >
            <!-- Thumbnail -->
            <div class="w-full cursor-zoom-in" @click="handleFileClick(file)">
              <div class="relative">
                <img
                  v-if="isImage(file.nama_file)"
                  :src="fileThumbSrc(file)"
                  :alt="file.nama_file"
                  class="w-full h-32 object-cover rounded shadow select-none"
                  loading="lazy"
                  draggable="false"
                />
                <img
                  v-else-if="isPDF(file.nama_file)"
                  :src="fileThumbSrc(file)"
                  :alt="file.nama_file"
                  class="w-full h-32 object-cover rounded shadow select-none"
                  loading="lazy"
                  draggable="false"
                />
                <div
                  v-else
                  :alt="file.nama_file"
                  class="w-full h-32 object-cover rounded shadow grid place-items-center text-3xl bg-white dark:bg-gray-700"
                >
                  📄
                </div>

                <!-- Badge status -->
                <div class="absolute top-2 left-2">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded text-[10px] font-semibold',
                      isChecked(file.checked) ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-800'
                    ]"
                  >
                    {{ isChecked(file.checked) ? 'Download Tersedia' : 'Pratinjau' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Info -->
            <div class="flex-1 cursor-pointer" @click="handleFileClick(file)">
              <div class="font-semibold text-base sm:text-lg truncate mt-2">{{ file.nama_file }}</div>
              <div class="text-sm text-gray-500 dark:text-gray-300 mt-1">Size: {{ formatSize(file.size) }}</div>
            </div>

            <!-- Tombol Unduh: sembunyikan untuk unchecked -->
            <div class="mt-2 sm:mt-0">
              <button
                v-if="isChecked(file.checked)"
                @click="downloadFile(file)"
                class="mt-2 sm:mt-0 inline-block text-sm text-orange-600 hover:underline whitespace-nowrap"
              >
                📥 Unduh ({{ file.jumlah_download }}x)
              </button>
              <span v-else class="mt-2 sm:mt-0 inline-block text-[11px] text-gray-500 dark:text-gray-400">
                🔒 Unduh tidak tersedia
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- NOTE: Teleport modal ke body agar lepas dari konteks parent -->
      <teleport to="body">
        <!-- Modal Preview -->
        <div
          v-if="previewFile"
          class="fixed inset-0 z-[9999] bg-black/80 md:bg-black/70 flex items-center justify-center p-2 md:p-4"
          @click.self="closePreview"
        >
          <!-- Tombol X mengambang -->
          <button
            @click="closePreview"
            class="absolute top-3 right-3 md:top-4 md:right-4
                   w-10 h-10 rounded-full
                   bg-black/70 text-white hover:bg-black/85
                   border border-white/20 shadow-lg backdrop-blur-sm
                   focus:outline-none focus:ring-2 focus:ring-white/80
                   flex items-center justify-center"
            aria-label="Tutup pratinjau"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6l-12 12" />
            </svg>
          </button>

          <!-- Konten (klik dalam konten tidak menutup) -->
          <div
            class="bg-white/95 dark:bg-gray-900/95 rounded-lg overflow-hidden
                   w-full max-w-5xl max-h-[90vh] relative"
            @click.stop
          >
            <div class="p-0 md:p-3">
              <template v-if="isImage(previewFile.nama_file)">
                <div class="relative">
                  <img
                    :src="isChecked(previewFile.checked)
                          ? `/storage/${previewFile.path}`
                          : `/kegiatan/file/${previewFile.id}/wm`"
                    :alt="previewFile.nama_file"
                    class="block w-auto max-w-full max-h-[85vh] mx-auto object-contain select-none"
                  />
                  <div v-if="!isChecked(previewFile.checked)"
                       class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <div
                      class="text-white/70 font-extrabold text-3xl md:text-5xl
                             tracking-wider rotate-[-20deg] text-center
                             drop-shadow-[0_2px_8px_rgba(0,0,0,0.6)] px-3">
                      <!-- Biro Adpim - Seetda Kab. Mahulu -->
                    </div>
                  </div>
                </div>
              </template>

              <template v-else-if="isPDF(previewFile.nama_file)">
                <iframe
                  :src="`/storage/${previewFile.path}`"
                  class="w-full h-[80vh]" frameborder="0"
                />
              </template>

              <template v-else>
                <div class="p-8 text-center text-gray-700 dark:text-gray-200">
                  Pratinjau tidak tersedia
                </div>
              </template>
            </div>
          </div>
        </div>
      </teleport>

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

    </div>
  </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { LayoutGrid, List } from 'lucide-vue-next'
import axios from 'axios'
import { toast } from 'vue3-toastify'

const props = defineProps({
  folder: Object,
  files: Array,
  filessatu: Array, 
  jumlah_unchecked: Number,
})

const showFeedbackModal = ref(false)
const selectedEmote = ref(null)
const searchQuery = ref('')
const isDarkMode = ref(false)
const isGridView = ref(true)
const previewFile = ref(null)

const closePreview = () => { previewFile.value = null }

const onEsc = (e) => {
  if (e.key === 'Escape') closePreview()
}

// total semua file (checked + unchecked)
const totalFiles = computed(() => Array.isArray(props.files) ? props.files.length : 0)

// jumlah file yang bisa diunduh (checked = true)
// pakai data dari server biar hemat, fallback ke filter jika perlu
const checkedCount = computed(() => {
  if (Array.isArray(props.filessatu)) return props.filessatu.length
  return Array.isArray(props.files) ? props.files.filter(f => isChecked(f.checked)).length : 0
})

onMounted(() => {
  isDarkMode.value = localStorage.getItem('theme') === 'dark'
  document.documentElement.classList.toggle('dark', isDarkMode.value)
  document.addEventListener('keydown', onEsc)
})

onBeforeUnmount(() => {
  document.removeEventListener('keydown', onEsc)
})

const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value
  localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light')
  document.documentElement.classList.toggle('dark', isDarkMode.value)
}

// helper untuk nilai checked ("0"/"1" atau boolean)
const isChecked = (v) => Number(v) === 1 || v === true

// urutkan: checked=1 dulu, lalu terbaru
const allFilesSorted = computed(() => {
  const arr = Array.isArray(props.files) ? [...props.files] : []
  arr.sort((a, b) => {
    const byChecked = (isChecked(b.checked) ? 1 : 0) - (isChecked(a.checked) ? 1 : 0)
    if (byChecked !== 0) return byChecked
    return new Date(b.created_at || 0) - new Date(a.created_at || 0)
  })
  return arr
})

// Filter pencarian di atas hasil sort
const filteredFiles = computed(() => {
  if (!searchQuery.value) return allFilesSorted.value
  const q = (searchQuery.value || '').toLowerCase()
  return allFilesSorted.value.filter(f => (f.nama_file || '').toLowerCase().includes(q))
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
    if (!isChecked(file.checked)) return
    await axios.post(`/kegiatan/file/${file.id}/download`)
    file.jumlah_download = (file.jumlah_download || 0) + 1

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
  if (bytes == null || bytes === 0) return '0 Byte'
  const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))
  return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i]
}

// dukungan HEIC/HEIF untuk preview
const isImage = (filename) => /\.(jpe?g|png|gif|webp|heic|heif)$/i.test(filename || '')
const isPDF   = (filename) => /\.pdf$/i.test(filename || '')
const isPreviewable = (filename) => isImage(filename) || isPDF(filename)

// sumber thumbnail/preview
const fileThumbSrc = (file) => {
  const isImg = /\.(jpg|jpeg|png|gif|webp|heic|heif)$/i.test(file.nama_file || '')
  if (isImg) {
    return isChecked(file.checked) ? `/storage/${file.path}` : `/kegiatan/file/${file.id}/wm`
  }
  if (/\.pdf$/i.test(file.nama_file || '')) return '/img/pdf-icon.png'
  return '/img/file-icon.png'
}

const handleFileClick = (file) => {
  if (isPreviewable(file.nama_file)) {
    previewFile.value = file
  } else if (isChecked(file.checked)) {
    downloadFile(file)
  }
}
</script>
