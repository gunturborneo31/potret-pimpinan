<template>
  <Head :title="`Sambutan: ${sambutan.judul}`" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">Sambutan</h2>
      </div>
    </template>

    <div class="max-w-7xl mx-auto px-4 py-6">
      <div class="mb-5">
        <!-- ⬇️ Tambahkan tahun terbit di sini -->
        <h2 class="text-xl font-bold text-gray-800 dark:text-white">
          Sambutan / Bulan {{ sambutan.judul }} — Tahun {{ formatYear(sambutan.tanggal_terbit) }}
        </h2>
        <a href="/sambutan" class="text-sm text-gray-600 hover:underline">← Kembali</a>
      </div>

      <!-- <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
        <p class="text-sm text-gray-700 dark:text-gray-300">
          🗓 Tanggal Terbit: <strong>{{ formatDate(sambutan.tanggal_terbit) }}</strong>
        </p>
        <p v-if="sambutan.deskripsi" class="text-sm text-gray-600 dark:text-gray-400 mt-1" v-html="sambutan.deskripsi"></p>
      </div> -->

      <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <UploadBucket
          title="File Naskah"
          type="naskah"
          :sambutan-id="sambutan.id"
          :items="state.naskah"
          @uploaded="onUploaded"
          @deleted="onDeleted"
        />
        <UploadBucket
          title="File Tapping"
          type="tapping"
          :sambutan-id="sambutan.id"
          :items="state.tapping"
          @uploaded="onUploaded"
          @deleted="onDeleted"
        />
        <UploadBucket
          title="File Presentasi"
          type="presentasi"
          :sambutan-id="sambutan.id"
          :items="state.presentasi"
          @uploaded="onUploaded"
          @deleted="onDeleted"
        />
        <UploadBucket
          title="File Terjemahan"
          type="terjemahan"
          :sambutan-id="sambutan.id"
          :items="state.terjemahan"
          @uploaded="onUploaded"
          @deleted="onDeleted"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import UploadBucket from './Upload.vue'
import { Head } from '@inertiajs/vue3'
import { reactive } from 'vue'

const props = defineProps({
  sambutan: Object,
  files: Object, // { naskah:[], tapping:[], presentasi:[], terjemahan:[] }
})

const state = reactive({
  naskah: props.files?.naskah ?? [],
  tapping: props.files?.tapping ?? [],
  presentasi: props.files?.presentasi ?? [],
  terjemahan: props.files?.terjemahan ?? [],
})

// format tahun untuk header
function formatYear(t) {
  if (!t) return '-'
  return new Date(t).getFullYear()
}

// (opsional) masih disimpan kalau sewaktu-waktu dipakai lagi
function formatDate(t) {
  if (!t) return '-'
  const d = new Date(t)
  const m = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']
  return `${String(d.getDate()).padStart(2,'0')} ${m[d.getMonth()]} ${d.getFullYear()}`
}

function onUploaded({ type, files }) {
  state[type] = [...state[type], ...files]
}
function onDeleted({ type, id }) {
  state[type] = state[type].filter(it => it.id !== id)
}
</script>
