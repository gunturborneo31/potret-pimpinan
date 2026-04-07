<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Manajemen File</h2>

    <!-- Buat Folder -->
    <div>
      <input v-model="folderName" type="text" placeholder="Nama Folder" class="border p-2 rounded" />
      <button @click="createFolder" class="bg-orange-500 text-white px-4 py-2 rounded ml-2">Buat</button>
    </div>

    <!-- Upload File -->
    <div v-if="selectedFolder" class="mt-4">
      <p class="text-sm text-gray-600">Folder Aktif: <strong class="text-orange-600">{{ selectedFolder.nama }}</strong></p>
      <input type="file" @change="handleFileUpload" multiple class="mt-2" />
      
      <!-- Progress Bar -->
      <div v-if="uploading" class="mt-2 bg-gray-200 rounded">
        <div :style="{ width: uploadProgress + '%' }" class="bg-orange-500 text-white text-sm text-center py-1 rounded" :class="uploadProgress === 100 ? 'bg-green-500' : ''">
          {{ uploadProgress }}%
        </div>
      </div>
    </div>

    <!-- Daftar Folder & File -->
    <div v-if="folders.length" class="mt-6">
      <h3 class="text-lg font-semibold">Daftar Folder</h3>
      <ul>
        <li v-for="folder in folders" :key="folder.id" class="mb-4">
          <strong @click="selectFolder(folder)" class="cursor-pointer text-orange-600">{{ folder.nama }}</strong>
          <ul class="ml-4 text-sm">
            <li v-for="file in folder.files" :key="file.id">
              📄 {{ file.original_name }}
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const folderName = ref('')
const folders = ref([])
const selectedFolder = ref(null)
const uploading = ref(false)
const uploadProgress = ref(0)

const createFolder = async () => {
  if (!folderName.value) return
  const res = await axios.post('/upload-folder', { nama: folderName.value })
  folders.value.push({ ...res.data, files: [] })
  folderName.value = ''
}

const handleFileUpload = async (event) => {
  const files = event.target.files
  if (!files.length || !selectedFolder.value) return

  uploading.value = true
  uploadProgress.value = 0

  const total = files.length
  let uploaded = 0

  for (let file of files) {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('folder_id', selectedFolder.value.id)

    await axios.post('/upload-file', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        const percent = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        uploadProgress.value = Math.round(((uploaded + percent / 100) / total) * 100)
      }
    }).then(res => {
      selectedFolder.value.files.push(res.data)
    }).catch(err => {
      console.error('Upload error:', err)
    })

    uploaded += 1
  }

  uploadProgress.value = 100
  setTimeout(() => {
    uploading.value = false
    uploadProgress.value = 0
  }, 1000)
}

const selectFolder = (folder) => {
  selectedFolder.value = folder
}

const loadFolders = async () => {
  const res = await axios.get('/uploaded-files')
  folders.value = res.data
}

onMounted(() => {
  loadFolders()
})
</script>
