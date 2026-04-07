<script setup>
import { onMounted, onUnmounted } from 'vue'

const loadedFiles = ref([])
const page = ref(1)
const hasMore = ref(true)

async function loadMoreFiles() {
  if (!hasMore.value) return

  try {
    const res = await axios.get(`/kegiatan/${props.folder.slug}/files?page=${page.value}`)
    if (res.data.data.length) {
      loadedFiles.value.push(...res.data.data)
      page.value++
    } else {
      hasMore.value = false
    }
  } catch (err) {
    toast.error('Gagal memuat file.')
  }
}

function handleScroll() {
  const scrollPosition = window.innerHeight + window.scrollY
  const threshold = document.body.offsetHeight - 100

  if (scrollPosition >= threshold) {
    loadMoreFiles()
  }
}

onMounted(() => {
  loadMoreFiles()
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <!-- Upload UI -->
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-bold text-gray-800 dark:text-white">Upload File ke: {{ folder.judul }}</h2>
    </template>

    <div class="max-w-4xl mx-auto p-4">
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <input type="file" multiple @change="handleFiles" class="mb-4" />

        <div v-if="uploading" class="w-full bg-gray-200 rounded h-4 mb-4 overflow-hidden">
          <div class="h-full bg-orange-500" :style="{ width: progress + '%' }"></div>
        </div>

        <button @click="uploadFiles" class="bg-orange-500 text-white px-6 py-2 rounded hover:bg-orange-600">
          Upload
        </button>
      </div>

      <!-- List file -->
      <div class="mt-6">
        <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-white">File di Folder</h3>
        <ul class="space-y-2">
          <li v-for="file in loadedFiles" :key="file.id" class="bg-gray-50 dark:bg-gray-700 p-4 rounded">
            <span class="text-gray-700 dark:text-gray-100">{{ file.nama_file }}</span>
          </li>
        </ul>
        <div v-if="!hasMore" class="text-sm text-gray-500 mt-4">Semua file sudah dimuat.</div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
