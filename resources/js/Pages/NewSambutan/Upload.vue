<template>
  <div class="border border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-3 sm:p-4 bg-white/70 dark:bg-gray-800/60">
    <!-- Header -->
    <div class="mb-3 sm:mb-4">
      <div class="flex items-center justify-between gap-2">
        <h3 class="text-base sm:text-lg font-semibold text-gray-800 dark:text-white">{{ title }}</h3>
        <span class="text-xs sm:text-sm text-gray-500 whitespace-nowrap">
          {{ items.length }} file
        </span>
      </div>

      <!-- Search: selalu di bawah judul -->
      <div class="mt-2">
        <label class="sr-only">Cari nama file</label>
        <div class="flex items-center gap-2">
          <input
            v-model="query"
            type="text"
            placeholder="Cari nama file..."
            class="w-full px-3 py-2 rounded text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-400"
          />
          <button
            v-if="query"
            @click="query=''"
            class="shrink-0 text-xs sm:text-sm px-2.5 py-2 rounded bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600"
          >
            Clear
          </button>
        </div>
      </div>
    </div>

    <!-- Dropzone -->
    <div
      class="rounded-md p-4 text-center text-sm transition"
      :class="isOver ? 'bg-orange-50 border border-orange-300' : 'bg-gray-50 dark:bg-gray-900 border border-transparent'"
      @dragover.prevent="isOver = true"
      @dragleave.prevent="isOver = false"
      @drop.prevent="handleDrop"
    >
      <p class="text-gray-600 dark:text-gray-300">
        Seret & lepaskan file di sini, atau
        <button type="button" class="text-orange-600 hover:underline" @click="pick">
          Pilih File
        </button>
      </p>
      <p class="text-xs text-gray-500 mt-1">
        Hanya PDF, Word (doc, docx), Excel (xls, xlsx)
      </p>
      <input
        ref="inp"
        type="file"
        class="hidden"
        multiple
        :accept="accepts"
        @change="handleChoose"
      />
    </div>

    <!-- Progress -->
    <div v-if="progress > 0 && progress < 100" class="mt-3 sm:mt-4">
      <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded">
        <div class="h-2 bg-orange-500 rounded" :style="{ width: progress+'%' }"></div>
      </div>
      <p class="text-xs text-gray-500 mt-1">{{ progress }}%</p>
    </div>

    <!-- List files (filtered) -->
    <ul class="mt-3 sm:mt-4 space-y-2 max-h-60 sm:max-h-72 overflow-auto pr-1">
      <li
        v-for="it in filteredItems"
        :key="it.id"
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded p-2"
      >
        <a
          :href="it.url"
          target="_blank"
          rel="noopener"
          class="truncate text-sm text-blue-600 dark:text-blue-400 hover:underline"
          :title="it.original_name"
          v-html="highlight(it.original_name, query)"
        ></a>

        <div class="flex items-center gap-2 sm:gap-3">
          <button
            @click="remove(it.id)"
            class="text-xs px-2 py-1 sm:px-3 sm:py-1.5 bg-red-500 hover:bg-red-600 text-white rounded"
          >
            Hapus
          </button>
        </div>
      </li>

      <li v-if="!filteredItems.length" class="text-xs text-gray-500">
        {{ items.length ? 'Tidak ada yang cocok dengan pencarian.' : 'Belum ada file.' }}
      </li>
    </ul>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref, computed } from 'vue'
import { toast } from 'vue3-toastify'

function makeUrl(name, params) {
  try {
    if (typeof route === 'function') return route(name, params)
  } catch (_) {}
  if (name === 'sambutan.upload') return `/sambutan/${params.sambutan}/upload`
  if (name === 'sambutan.file.destroy') return `/sambutan/${params.sambutan}/file/${params.file}`
  return '/'
}

const props = defineProps({
  title: String,
  type: { type: String, required: true },       // 'naskah'|'tapping'|'presentasi'|'terjemahan'
  sambutanId: { type: String, required: true }, // UUID
  items: { type: Array, default: () => [] },
})
const emit = defineEmits(['uploaded','deleted'])

const inp = ref(null)
const isOver = ref(false)
const progress = ref(0)
const query = ref('')

// Accept: PDF, Word, Excel
const accepts =
  'application/pdf,.pdf,application/msword,.doc,application/vnd.openxmlformats-officedocument.wordprocessingml.document,.docx,application/vnd.ms-excel,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,.xlsx'

// Filter realtime berdasarkan nama file
const filteredItems = computed(() => {
  const q = query.value.trim().toLowerCase()
  if (!q) return props.items || []
  return (props.items || []).filter(it =>
    (it.original_name || '').toLowerCase().includes(q)
  )
})

// Highlight pencarian
function escapeHtml(s) {
  return s.replace(/[&<>"']/g, (m) => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[m]))
}
function highlight(text, q) {
  const t = String(text || '')
  const query = String(q || '').trim()
  if (!query) return escapeHtml(t)
  const re = new RegExp(`(${query.replace(/[.*+?^${}()|[\\]\\\\]/g, '\\$&')})`, 'ig')
  return escapeHtml(t).replace(re, '<mark>$1</mark>')
}

function pick() { inp.value?.click() }

function validateFiles(list) {
  const allowedExt = ['pdf','doc','docx','xls','xlsx']
  const allowedMime = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  ]
  return list.filter(f => {
    const ext = (f.name.split('.').pop() || '').toLowerCase()
    return allowedExt.includes(ext) || allowedMime.includes(f.type)
  })
}

function handleChoose(e) {
  let files = Array.from(e.target.files || [])
  handleIncoming(files)
  e.target.value = ''
}

function handleDrop(e) {
  isOver.value = false
  let files = Array.from(e.dataTransfer.files || [])
  handleIncoming(files)
}

function handleIncoming(files) {
  files = validateFiles(files)
  if (!files.length) {
    toast.error('Format file tidak didukung. Hanya PDF, DOC, DOCX, XLS, XLSX.')
    return
  }
  // tanpa batas jumlah file
  upload(files)
}

async function upload(fileList) {
  try {
    progress.value = 5
    const form = new FormData()
    form.append('type', props.type)
    fileList.forEach(f => form.append('files[]', f))

    const url = makeUrl('sambutan.upload', { sambutan: props.sambutanId })
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

    const res = await axios.post(url, form, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json',
        'Content-Type': 'multipart/form-data',
      },
      onUploadProgress: (e) => { if (e.total) progress.value = Math.round((e.loaded / e.total) * 100) }
    })

    progress.value = 100

    // Toast sukses per file
    const uploaded = res.data?.files || []
    uploaded.forEach(f => {
      const nm = f?.original_name || 'File'
      toast.success(`${nm} Berhasil terupload`)
    })

    emit('uploaded', { type: props.type, files: uploaded })
    setTimeout(() => (progress.value = 0), 400)
  } catch (err) {
    console.error('[upload] failed', err)
    progress.value = 0
    const msg = err?.response?.data?.message || 'Gagal mengunggah file.'
    toast.error(msg)
  }
}

async function remove(id) {
  if (!confirm('Hapus file ini?')) return
  try {
    // ambil nama file sebelum dihapus
    const current = (props.items || []).find(it => it.id === id)
    const nm = current?.original_name || 'File'

    const url = makeUrl('sambutan.file.destroy', { sambutan: props.sambutanId, file: id })
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

    await axios.delete(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json',
      }
    })

    emit('deleted', { type: props.type, id })
    toast.success(`${nm} terhapus`)
  } catch (err) {
    console.error('[delete] failed', err)
    toast.error('Gagal menghapus file.')
  }
}
</script>
