<script setup>
import { ref, watch, nextTick, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { LayoutGrid, List } from 'lucide-vue-next'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import axios from 'axios'
import { toast } from 'vue3-toastify'
import Card from './Partials/Card.vue'
import FileCard from './Partials/FileCard.vue'
import FileList from './Partials/FileList.vue'
import FolderList from './Partials/FolderList.vue'

import MoveCopyModal from './Partials/MoveCopyModal.vue'

const isMoveCopyModalOpen = ref(false)
const modalOpen = ref(false)
const selectedFile = ref(null)
const actionMode = ref('move') // or 'copy'
const folders = ref([])
const props = defineProps({
  folder: Object,           // folder utama (slug)
  subfolders: Array,        // folder.children
  files: Object,             // paginated files (data, links)
  auth: Object,
})

const showMoveModal = ref(false)
const showMoveCopyModal = ref(false)
const mode = ref('move')

function openMoveModal(file) {
  selectedFile.value = file
  mode.value = 'move'
  showMoveModal.value = true
}
function openCopyModal(file) {
  selectedFile.value = file
  mode.value = 'copy'
  showMoveModal.value = true
}


async function handleMoveOrCopy({ fileId, targetFolderId, mode }) {
  try {
    const url =
      mode === 'move'
        ? `/kegiatan/files/${fileId}/move`
        : `/kegiatan/files/${fileId}/copy`

    const response = await axios.post(url, {
      target_folder_id: targetFolderId,
    })

    const slug = response.data.slug

    toast.success(mode === 'move' ? 'File berhasil dipindahkan!' : 'File berhasil disalin!')

    if (slug) {
      router.visit(`/kegiatan/folders/${slug}`)
    }
  } catch (error) {
    toast.error('Gagal memproses file')
    console.error(error)
  }
}

const parentId = computed(() => props.folder?.id ?? null)

const breadcrumbs = computed(() => {
  const path = []
  let current = props.folder

  while (current) {
    path.unshift(current)
    current = current.parent
  }

  return path
})

const dropzoneRef = ref(null)

const formSubfolder = useForm({
  judul: '',
  parent_id: props.folder?.id || null,
})

const subfolders = ref([...props.subfolders])
const showModal = ref(false)
const inputRef = ref(null)

const fileInput = ref(null)
const selectedFiles = ref([])
const uploading = ref(false)
const uploadProgress = ref({})

const files = ref([...props.files.data])
const nextPageUrl = ref(props.files.links.next)

const searchQuery = ref('')
const isGrid = ref(true)

const filteredSubfolders = computed(() =>
  subfolders.value.filter(f => f.judul.toLowerCase().includes(searchQuery.value.toLowerCase()))
)

const filteredFiles = computed(() =>
  files.value.filter(f => f.nama_file.toLowerCase().includes(searchQuery.value.toLowerCase()))
)

const showRenameModal = ref(false)
const renameForm = useForm({
  id: null,
  nama_file: '',
})

const showShareModal = ref(false)
const shareFile = ref(null)

function openRenameModal(file) {
  renameForm.id = file.id
  renameForm.nama_file = file.nama_file
  showRenameModal.value = true
}

function renameFile() {
  axios.put(route('kegiatan.files.update', renameForm.id), {
    nama_file: renameForm.nama_file
  })
  .then(res => {
    const updated = res.data.file
    const index = files.value.findIndex(f => f.id === updated.id)
    if (index !== -1) files.value[index] = updated
    toast.success('Nama file berhasil diubah')
    showRenameModal.value = false
  })
  .catch(() => toast.error('Gagal mengganti nama file'))
}

function openShareSettings(file) {
  shareFile.value = file
  showShareModal.value = true
}

function updateShareSetting(isPublic) {
  axios.put(route('kegiatan.files.update', shareFile.value.id), {
    public: isPublic
  }).then(res => {
    const updated = res.data.file
    const index = files.value.findIndex(f => f.id === updated.id)
    if (index !== -1) files.value[index] = updated
    toast.success('Pengaturan link diperbarui')
    showShareModal.value = false
  }).catch(() => {
    toast.error('Gagal menyimpan pengaturan')
  })
}


formSubfolder.parent_id = props.folder.id

function createSubfolder() {
  axios.post(route('kegiatansub.folders.store', props.folder.slug), {
    judul: formSubfolder.judul,
    parent_id: props.folder.id
  })
  .then(res => {
    toast.success('Sub-folder dibuat')
    subfolders.value.unshift(res.data.subfolder)
    showModal.value = false
    formSubfolder.reset()
  })
  .catch(err => {
    if (err.response?.status === 422) {
      toast.error('Judul wajib diisi')
    } else {
      toast.error('Gagal membuat folder')
    }
  })
}

function onFileChange(e) {
  selectedFiles.value = [...e.target.files]
}

function uploadFiles() {
  if (!selectedFiles.value.length) return

  uploading.value = true

  const uploads = selectedFiles.value.map(file => {
    if (file.size > 2 * 1024 * 1024 * 1024) {
      toast.error(`${file.name}: Melebihi 2GB`)
      return Promise.resolve()
    }

    const formData = new FormData()
    formData.append('file', file)
    formData.append('folder_id', props.folder.id)

  return axios.post(route('kegiatan.files.store', props.folder.slug), formData, {
  headers: { 'Content-Type': 'multipart/form-data' },
  onUploadProgress: (e) => {
    uploadProgress.value[file.name] = Math.round((e.loaded * 100) / e.total)
  }
}).then(res => {
      if (res.data.file) {
        files.value.unshift(res.data.file)
        toast.success(`${file.name}: berhasil terupload`)
      }
    }).catch(() => {
      toast.error(`${file.name}: gagal diupload`)
    })
  })

  Promise.all(uploads).finally(() => {
    uploading.value = false
    selectedFiles.value = []
    fileInput.value.value = null
    uploadProgress.value = {}
  })
}

function handleClick(action) {
  showMenu.value = false
  emit(action, props.file)
}

const handleDeleteFile = async (file) => {
  if (confirm(`Yakin ingin menghapus file "${file.nama_file}"?`)) {
    try {
      await axios.delete(route('kegiatan.files.destroy', file.id))
      files.value = files.value.filter(f => f.id !== file.id)
      toast.success('File berhasil dihapus')
    } catch (error) {
      toast.error('Gagal menghapus file')
    }
  }
}

function loadMoreFiles() {
  if (!nextPageUrl.value) return

  axios.get(nextPageUrl.value)
    .then(res => {
      files.value.push(...res.data.files.data)
      nextPageUrl.value = res.data.files.links.next
    })
}

function handleDrop(e) {
  e.preventDefault()
  const droppedFiles = [...e.dataTransfer.files].filter(f => f.size <= 2 * 1024 * 1024 * 1024)
  if (!droppedFiles.length) {
    toast.error('File melebihi 2GB atau tidak valid.')
    return
  }
  selectedFiles.value = droppedFiles
  uploadFiles()
}

function handleDragOver(e) {
  e.preventDefault()
}

watch(showModal, async (val) => {
  if (val) {
    await nextTick()
    inputRef.value?.focus()
  }
})


function handleMove(file) {
  selectedFile.value = file
  actionMode.value = 'move'
  modalOpen.value = true
}

function handleCopy(file) {
  selectedFile.value = file
  actionMode.value = 'copy'
  modalOpen.value = true
}

function handleMoveCopy({ fileId, targetFolderId, mode }) {
  axios.post(`/kegiatan/files/${fileId}/${mode}`, {
    target_folder_id: targetFolderId,
  }).then(() => {
    toast.success(`File berhasil di${mode === 'move' ? 'pindahkan' : 'salin'}`)
    // refresh daftar file atau update state
  }).catch(() => {
    toast.error(`Gagal ${mode === 'move' ? 'memindahkan' : 'menyalin'} file`)
  }).finally(() => modalOpen.value = false)
}

</script>

<template>
  <Head :title="folder.judul" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-bold text-gray-800 dark:text-white">
        Folder: {{ folder.judul }}
      </h2>
    </template>

    <!-- Modal Rename File -->
<div v-if="showRenameModal" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
  <div class="bg-white dark:bg-gray-800 p-6 rounded shadow w-full max-w-md">
    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Ganti Nama File</h3>
    <input
      v-model="renameForm.nama_file"
      @keyup.enter="renameFile"
      class="w-full border border-gray-300 dark:border-gray-600 px-4 py-2 rounded dark:bg-gray-900 dark:text-white mb-4"
      placeholder="Nama file baru"
    />
    <div class="flex justify-end gap-2">
      <button @click="showRenameModal = false" class="px-4 py-2 text-gray-600 dark:text-gray-300">Batal</button>
      <button @click="renameFile" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded">
        Simpan
      </button>
    </div>
  </div>
</div>

<!-- Modal Share Settings -->
<div v-if="showShareModal" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
  <div class="bg-white dark:bg-gray-800 p-6 rounded shadow w-full max-w-md">
    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Pengaturan Link</h3>
    <div class="space-y-2">
      <p class="text-sm text-gray-700 dark:text-gray-300">
        Tautan:
      </p>
      <div v-if="shareFile?.public" class="bg-gray-100 dark:bg-gray-900 p-2 rounded flex justify-between items-center">
        <input type="text" :value="route('kegiatan.files.share', shareFile?.id)" readonly class="bg-transparent w-full text-sm dark:text-white" />
        <button @click="() => navigator.clipboard.writeText(route('kegiatan.files.share', shareFile?.id))" class="ml-2 text-sm text-blue-500">Copy</button>
      </div>
      <div v-else class="text-sm text-red-500">File ini bersifat privat</div>

      <div class="flex justify-end gap-2 mt-4">
        <button @click="showShareModal = false" class="px-4 py-2 text-gray-600 dark:text-gray-300">Tutup</button>
        <button @click="updateShareSetting(!shareFile?.public)" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded">
          {{ shareFile?.public ? 'Jadikan Privat' : 'Jadikan Publik' }}
        </button>
      </div>
    </div>
  </div>
</div>

    <!-- Modal buat subfolder -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
      <div class="bg-white dark:bg-gray-800 p-6 rounded shadow w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-white">Tambah Sub-Folder</h3>
        <input type="hidden" v-model="formSubfolder.parent_id">
        <input
          ref="inputRef"
          v-model="formSubfolder.judul"
          @keyup.enter="createSubfolder"
          placeholder="Judul sub-folder"
          class="w-full border border-gray-300 dark:border-gray-600 px-4 py-2 rounded dark:bg-gray-900 dark:text-white mb-4"
        />
        <div class="flex justify-end gap-2">
          <button @click="showModal = false" class="px-4 py-2 text-gray-600 dark:text-gray-300">Batal</button>
          <button @click="createSubfolder" class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded">
            Simpan
          </button>
        </div>
      </div>
    </div>

    <!-- Breadcrumbs -->
<!-- Breadcrumbs dengan border bawah -->
<nav class="px-4 mb-4 pb-2 border-b border-gray-300 dark:border-gray-600 text-sm text-gray-600 dark:text-gray-300" style="margin-top:10px;">
  <ol class="flex flex-wrap gap-1 items-center">
    <li>
      <a href="/kegiatan" class="hover:underline text-orange-600 dark:text-orange-400">📁 Semua Kegiatan</a>
    </li>
    <li v-for="(crumb, index) in breadcrumbs" :key="crumb.id" class="flex items-center">
      <span class="mx-1">/</span>
      <a
        v-if="index < breadcrumbs.length - 1"
        :href="route('kegiatan.folders.show', crumb.slug)"
        class="hover:underline text-orange-600 dark:text-orange-400"
      >
        {{ crumb.judul }}
      </a>
      <span v-else class="text-gray-800 dark:text-white font-semibold">
        {{ crumb.judul }}
      </span>
    </li>
  </ol>
</nav>


<div class="p-4 max-w-7xl mx-auto">
   <div class="flex justify-first mb-2">
    <a href="/kegiatan"
          class="inline-block bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 px-4 py-2 rounded hover:bg-gray-400 dark:hover:bg-gray-600 transition">
          ← Kembali
        </a>
  </div>

      <!-- Subfolder -->
      <!-- <div class="mb-6">
        <div class="flex justify-between items-center mb-2">
          <button @click="showModal = true" class="text-sm px-3 py-1 bg-orange-500 text-white rounded">
            + Folder Baru
          </button>
        </div>
      </div> -->

      <!-- Filter dan Toggle View -->
<div class="flex items-center justify-between mb-4 gap-4 flex-wrap">
  <!-- Search -->
  <input
    type="text"
    v-model="searchQuery"
    placeholder="Cari nama file..."
    class="w-full sm:w-1/2 px-4 py-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
  />

  <!-- Toggle View -->
  <div class="flex items-center gap-2">
    <button
      @click="isGrid = true"
      :class="isGrid ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-white'"
      class="px-3 py-1 rounded text-sm"
    >
    <LayoutGrid class="w-5 h-5" />

    </button>
    <button
      @click="isGrid = false"
      :class="!isGrid ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-white'"
      class="px-3 py-1 rounded text-sm"
    >
    <List class="w-5 h-5" />

    </button>
  </div>
</div>

   <!-- Upload -->
<div class="mb-6"  v-if="auth?.user && folder.user_id === auth.user.id">
  <h3 class="text-lg font-bold text-gray-700 dark:text-white mb-2">Upload File</h3>

  <div
    ref="dropzoneRef"
    @drop="handleDrop"
    @dragover="handleDragOver"
    class="w-full p-6 mb-2 border-2 border-dashed rounded text-center cursor-pointer transition 
           text-gray-500 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:border-orange-500"
  >
    <p>Drag & drop file di sini atau pilih manual</p>
    <input
      ref="fileInput"
      type="file"
      multiple
      @change="onFileChange"
      class="mt-2 block mx-auto text-sm text-gray-600 dark:text-gray-300"
    />
  </div>

  <button
    @click="uploadFiles"
    :disabled="!selectedFiles.length || uploading"
    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded disabled:opacity-50"
  >
    Upload
  </button>

  <!-- Progress -->
  <div v-if="uploading" class="mt-4 space-y-2">
    <div v-for="(progress, name) in uploadProgress" :key="name">
      <div class="text-sm text-gray-700 dark:text-white">{{ name }}</div>
      <div class="w-full bg-gray-300 dark:bg-gray-600 h-2 rounded">
        <div class="bg-orange-500 h-2 rounded" :style="{ width: progress + '%' }"></div>
      </div>
    </div>
  </div>
</div>

      <!-- File List -->
      <div>
        <h3 class="text-lg font-bold text-gray-700 dark:text-white mb-2">File</h3>
<div v-if="filteredFiles.length || filteredSubfolders.length">
  <div v-if="isGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
    <!-- Subfolder sebagai Card -->
    <Card v-for="sf in filteredSubfolders" :key="sf.id" :folder="sf" />
    <!-- File sebagai Card -->
    <!-- <FileCard v-for="file in filteredFiles" :key="file.id" :file="file" /> -->
    <FileCard
  v-for="file in filteredFiles"
  :key="file.id"
  :file="file"
  @delete="handleDeleteFile"
  @rename="openRenameModal"
  @shareSettings="openShareSettings"
  @move="openMoveModal" 
  @copy="openCopyModal"
/>

  <!-- Modal -->
  <MoveCopyModal
    v-if="showMoveModal"
    :show="showMoveModal"
    :file="selectedFile"
    @close="showMoveModal = false"
    @confirmed="handleMoveOrCopy"
   
/>
  </div>

  <div v-else class="space-y-3">
    <!-- Subfolder sebagai List -->
    <FolderList v-for="sf in filteredSubfolders" :key="sf.id" :folder="sf" />
    <!-- File sebagai List -->
   <FileList
  v-for="file in files"
  :key="file.id"
  :file="file"
  @delete="handleDeleteFile"
  @rename="handleRename"
  @shareSettings="handleShareSettings"
/>
  </div>
</div>
<div v-else class="text-sm text-gray-500 dark:text-gray-300">Tidak ditemukan hasil untuk "{{ searchQuery }}"</div>


        <!-- Muat Lagi -->
        <div v-if="nextPageUrl" class="mt-4 text-center">
          <button
            @click="loadMoreFiles"
            class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm rounded">
            Muat Lagi
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
