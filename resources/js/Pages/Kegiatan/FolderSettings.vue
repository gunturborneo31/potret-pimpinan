<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { toast } from 'vue3-toastify'

const props = defineProps({
  folder: Object,
})

const shareUrl = `${window.location.origin}/kegiatan/${props.folder.slug}`

const form = useForm({
  is_public: props.folder.is_public || 'PRIVATE'
})

function update() {
  form.put(route('kegiatan.folders.update', props.folder.id), {
    preserveScroll: true,
    onSuccess: () => toast.success('Folder berhasil diperbarui!')
  })
}

function copyLink() {
  navigator.clipboard.writeText(shareUrl)
  toast.info('Link disalin ke clipboard!')
}
</script>

<template>
  <Head :title="`Pengaturan Folder - ${folder.judul}`" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-bold text-gray-800 dark:text-white">Pengaturan Folder</h2>
    </template>

    <div class="max-w-3xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 space-y-6">
        <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Pengaturan Folder: {{ folder.judul }}</h1>

        <!-- Pilih status publik -->
        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Status Akses</label>
          <select v-model="form.is_public" class="w-full rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
            <option value="PRIVATE">PRIVATE (Hanya bisa diakses internal)</option>
            <option value="PUBLIC">PUBLIC (Bisa dibagikan ke siapa saja)</option>
          </select>
        </div>

        <!-- Share Link -->
        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-200">Share Link</label>
          <div class="flex gap-2">
            <input :value="shareUrl" type="text" readonly
              class="flex-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
            <button @click="copyLink" type="button"
              class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">Salin</button>
          </div>
        </div>

        <div class="text-right">
          <button @click="update"
            class="bg-orange-500 text-white px-6 py-2 rounded hover:bg-orange-600">
            Simpan Pengaturan
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
