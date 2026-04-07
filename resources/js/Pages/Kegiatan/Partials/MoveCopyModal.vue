<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  show: Boolean,
  file: Object,
  mode: {
    type: String,
    default: 'move',
  }
})

const emit = defineEmits(['close', 'confirmed'])

const selectedFolder = ref(null)
const folders = ref([])
const searchQuery = ref('')

onMounted(async () => {
  try {
    const res = await axios.get('/kegiatan/folders/pindah')
    folders.value = res.data.data || res.data
  } catch (error) {
    console.error('Gagal memuat folder:', error)
  }
})

const filteredFolders = computed(() => {
  if (!searchQuery.value) return folders.value
  return folders.value.filter(folder =>
    folder.judul.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})
</script>

<template>
  <div v-if="show" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg w-[90%] max-w-md space-y-4">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
        {{ mode === 'move' ? 'Pindahkan' : 'Salin' }} File ke Folder
      </h2>

      <!-- Pencarian -->
      <input
        type="text"
        v-model="searchQuery"
        class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-white"
        placeholder="Cari folder..."
      />

      <!-- Select manual -->
      <select
        v-model="selectedFolder"
        class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 mt-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-white"
      >
        <option disabled value="">Pilih folder tujuan</option>
        <option
          v-for="folder in filteredFolders"
          :key="folder.id"
          :value="folder.id"
        >
          📁 {{ folder.judul }}
        </option>
      </select>

      <!-- Tombol aksi -->
      <div class="flex justify-end gap-2 mt-4">
        <button
          @click="$emit('close')"
          class="px-3 py-1 text-sm bg-gray-100 dark:bg-gray-600 dark:text-white rounded hover:bg-gray-200 dark:hover:bg-gray-500"
        >
          Batal
        </button>
        <button
          :disabled="!selectedFolder"
          @click="$emit('confirmed', { fileId: file.id, targetFolderId: selectedFolder, mode })"
          class="px-3 py-1 text-sm bg-orange-600 text-white rounded hover:bg-orange-700 disabled:opacity-50"
        >
          {{ mode === 'move' ? 'Pindahkan' : 'Salin' }}
        </button>
      </div>
    </div>
  </div>
</template>
