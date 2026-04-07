<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { toast } from 'vue3-toastify'

const props = defineProps({
  post: Object,      // data post social media yang diedit
  users: Array       // daftar user untuk select user_id (kalau perlu)
})

const optionList = [
  'Instagram Reels',
  'Instagram Post',
  'Youtube Video',
  'Youtube Short',
  'Tiktok',
  'Facebook Post'
]

const form = useForm({
  judul: props.post.judul,
  link: props.post.link,
  option: props.post.option,
  user_id: props.post.user_id
})

const submit = () => {
  form.put(route('post-sosmed.update', props.post.id), {
    onSuccess: () => {
      toast.success('Data berhasil diperbarui!')
    },
    onError: () => {
      toast.error('Terjadi kesalahan saat memperbarui data.')
    }
  })
}
</script>

<template>
  <Head title="Edit Post Social Media" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Edit Post Social Media
      </h2>
    </template>

    <div class="py-6">
      

      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
           <div class="mt-6 mb-6 text-right">
        <a href="/post-sosmed"
          class="inline-block bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 px-4 py-2 rounded hover:bg-gray-400 dark:hover:bg-gray-600 transition">
          ← Kembali ke Daftar Post Link
        </a>
      </div>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900 dark:text-gray-100">
            <form @submit.prevent="submit" class="space-y-6">

              <!-- Judul -->
              <div>
                <label for="judul" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Judul
                </label>
                <input
                  type="text"
                  id="judul"
                  v-model="form.judul"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                />
                <div v-if="form.errors.judul" class="text-sm text-red-500 mt-1">
                  {{ form.errors.judul }}
                </div>
              </div>

              <!-- Link -->
              <div>
                <label for="link" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Link
                </label>
                <input
                  type="url"
                  id="link"
                  v-model="form.link"
                  placeholder="https://..."
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                />
                <div v-if="form.errors.link" class="text-sm text-red-500 mt-1">
                  {{ form.errors.link }}
                </div>
              </div>

              <!-- Option -->
              <div>
                <label for="option" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Platform
                </label>
                <select
                  id="option"
                  v-model="form.option"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                >
                  <option value="">-- Pilih Platform --</option>
                  <option v-for="opt in optionList" :key="opt" :value="opt">{{ opt }}</option>
                </select>
                <div v-if="form.errors.option" class="text-sm text-red-500 mt-1">
                  {{ form.errors.option }}
                </div>
              </div>

              <!-- Tombol Submit -->
              <div class="flex justify-end">
                <button
                  type="submit"
                  class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150"
                  :disabled="form.processing"
                >
                  Simpan Perubahan
                </button>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
