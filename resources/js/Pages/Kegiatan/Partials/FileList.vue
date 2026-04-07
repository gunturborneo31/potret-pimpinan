<script setup>
import { FileText, EllipsisVertical } from 'lucide-vue-next'
import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'
import { toast } from 'vue3-toastify'
import { usePage } from '@inertiajs/vue3'

const { props: pageProps } = usePage()
const auth = pageProps.auth

const props = defineProps({
  file: Object,
})
const emit = defineEmits(['rename', 'delete', 'shareSettings', 'move', 'copy'])

const showMenu = ref(false)

/* ====== Tambahan: state modal preview ====== */
const previewModal = ref(false)

/* ====== Utility yang sudah ada ====== */
function truncate(text, length = 25) {
  return text.length > length ? text.slice(0, length) + '...' : text
}

/* ====== Tambahan: helper aman baca ekstensi & cek previewable ====== */
function getExt(name) {
  return String(name || '').split('.').pop().toLowerCase()
}
function isImageExt(ext) {
  return ['jpg','jpeg','png','gif','webp','bmp'].includes(ext)
}
function isPDFExt(ext) {
  return ext === 'pdf'
}
function isPreviewable(file) {
  const ext = getExt(file?.nama_file)
  return isImageExt(ext) || isPDFExt(ext)
}

/* ====== Tambahan: open/close preview ====== */
function openPreview() {
  if (isPreviewable(props.file)) {
    previewModal.value = true
  } else {
    // Jika bukan image/pdf, buka tab baru
    window.open(`/storage/${props.file.path}`, '_blank', 'noopener')
  }
}
function closePreview() {
  previewModal.value = false
}

/* ====== Tambahan: ESC untuk menutup modal ====== */
function onEsc(e) {
  if (e.key === 'Escape' && previewModal.value) closePreview()
}
onMounted(() => document.addEventListener('keydown', onEsc))
onBeforeUnmount(() => document.removeEventListener('keydown', onEsc))

/* ====== Logika lama (tetap) ====== */
async function toggleChecked() {
  try {
    const response = await axios.patch(`/kegiatan/files/${props.file.id}/toggle-checked`)
    props.file.checked = response.data.checked
    toast.success(props.file.checked ? 'File ditampilkan!' : 'File disembunyikan!')
  } catch (error) {
    toast.error('Gagal mengubah status file')
    console.error(error)
  }
}

function toggleMenu() {
  showMenu.value = !showMenu.value
}

function handleClick(action) {
  showMenu.value = false
  emit(action, props.file)
}
</script>

<template>
  <div
    class="w-full bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center justify-between hover:shadow-md transition relative"
  >
    <!-- Area kiri (ikon + info) dibuat bisa diklik untuk preview -->
    <div
      class="flex items-center gap-4 cursor-pointer"
      role="button" tabindex="0"
      @click="openPreview"
      @keyup.enter.prevent="openPreview"
      @keyup.space.prevent="openPreview"
    >
      <!-- Icon -->
      <FileText class="w-6 h-6 text-orange-500 shrink-0" />

      <!-- File Info -->
      <div class="flex flex-col">
        <p class="text-sm font-medium text-gray-800 dark:text-white truncate">
          {{ truncate(file.nama_file) }}
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-300">
          {{ (file.size / 1024 / 1024).toFixed(2) }} MB •
          <span>{{ file.jumlah_download }}x diunduh</span>
        </p>
      </div>
    </div>

    <!-- Action Buttons (kanan) -->
    <div class="ml-4 flex flex-col items-end gap-1">
      <button v-if="auth?.user && file.folder.user_id === auth.user.id"
        @click.stop="toggleChecked"
        class="text-xs px-2 py-1 rounded font-medium border transition"
        :class="file.checked
          ? 'bg-green-100 text-green-700 border-green-300 hover:bg-green-200'
          : 'bg-gray-100 text-gray-600 border-gray-300 hover:bg-gray-200'"
        style="margin-right: 20px;"
      >
        {{ file.checked ? '✔ Unduh Tersedia' : '❌ Tidak Tersedia Unduh' }}
      </button>
    </div>

    <!-- Toggle menu -->
    <div class="absolute top-2 right-2" v-if="auth?.user && file.folder.user_id === auth.user.id">
      <button @click.stop="toggleMenu" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
        <EllipsisVertical class="w-4 h-4 text-gray-500 dark:text-white" />
      </button>
      <div
        v-if="showMenu"
        class="absolute right-0 mt-2 w-36 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded shadow-md z-20"
      >
        <button
          class="w-full px-4 py-2 text-sm text-left hover:bg-gray-100 dark:hover:bg-gray-800 text-white-600"
          @click="handleClick('move')"
        >
          📂 Pindahkan
        </button>

        <button
          class="w-full px-4 py-2 text-sm text-left hover:bg-gray-100 dark:hover:bg-gray-800 text-white-600"
          @click="handleClick('copy')"
        >
          📄 Salin
        </button>

        <button
          class="w-full px-4 py-2 text-sm text-left hover:bg-gray-100 dark:hover:bg-gray-800 text-red-600"
          @click="handleClick('delete')"
        >
          🗑 Hapus File
        </button>
      </div>
    </div>
  </div>

  <!-- Modal Preview (responsif + klik luar + Esc) -->
  <div
    v-if="previewModal"
    class="fixed inset-0 z-[999] flex items-center justify-center bg-black/80 p-2 sm:p-3 md:p-4"
    role="dialog" aria-modal="true"
    @click.self="closePreview"
  >
    <!-- Tombol X mengambang (selalu kontras) -->
    <button
      @click="closePreview"
      class="absolute top-3 right-3 md:top-4 md:right-4
             w-10 h-10 md:w-11 md:h-11 rounded-full
             bg-black/70 text-white hover:bg-black/85
             border border-white/30 shadow-lg backdrop-blur-sm
             focus:outline-none focus:ring-2 focus:ring-white/80
             flex items-center justify-center z-[1000]"
      aria-label="Tutup pratinjau"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 md:w-7 md:h-7" fill="none"
           viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6l-12 12" />
      </svg>
    </button>

    <!-- Konten modal -->
    <div
      class="relative bg-white/95 dark:bg-gray-900/95 rounded-lg overflow-hidden
             w-full max-w-5xl max-h-[90vh]"
      @click.stop
    >
      <div class="p-0 sm:p-2 md:p-3">
        <!-- Gambar -->
        <img
          v-if="isImageExt(getExt(file.nama_file))"
          :src="`/storage/${file.path}`"
          :alt="file.nama_file"
          class="block mx-auto w-auto max-w-full max-h-[85vh] object-contain select-none"
          draggable="false"
        />
        <!-- PDF -->
        <iframe
          v-else-if="isPDFExt(getExt(file.nama_file))"
          :src="`/storage/${file.path}`"
          class="w-[95vw] sm:w-full h-[70vh] sm:h-[80vh]"
          frameborder="0"
        />
        <!-- Lainnya -->
        <div v-else class="p-8 text-center text-gray-700 dark:text-gray-200">
          Pratinjau tidak tersedia
        </div>
      </div>
    </div>
  </div>
</template>
