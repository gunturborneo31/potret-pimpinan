<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'

const form = useForm({
  judul: '',
})

function submit() {
  form.post(route('kegiatan.store'))
}
</script>

<template>
  <Head title="Buat Folder Kegiatan" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-bold text-gray-800 dark:text-white">Tambah Folder Kegiatan</h2>
    </template>

    <div class="py-6 max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
      <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow space-y-4">
        <div>
          <label class="block mb-1 font-semibold text-gray-700 dark:text-gray-200">Judul Folder</label>
          <input
            v-model="form.judul"
            type="text"
            class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg shadow-sm focus:ring-orange-500"
            placeholder="Contoh: Kegiatan 2025"
          />
          <div v-if="form.errors.judul" class="text-red-500 text-sm mt-1">
            {{ form.errors.judul }}
          </div>
        </div>

        <div class="flex justify-end">
          <button
            type="submit"
            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded"
            :disabled="form.processing"
          >
            Simpan
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
