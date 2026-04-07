<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-white">
          Detail Sambutan
        </h2>
      
      </div>
    </template>

    <div class="py-6">
      
      <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
          <Link
          :href="route('sambutan.index')"
          class="text-sm text-orange-600 hover:text-orange-800 transition py-8"
        >
          ← Kembali ke Daftar Sambutan
        </Link>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg p-6 space-y-6">
          
          <!-- Judul -->
          <div class="flex items-center gap-2">
            <span class="text-2xl">📌</span>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ sambutan.judul }}</h1>
          </div>

          <!-- Metadata -->
          <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
            <div class="flex items-center gap-2">
              👤 <span>Ditulis oleh: <strong>{{ sambutan.user?.name || '-' }}</strong></span>
            </div>
            <div class="flex items-center gap-2" v-if="sambutan.created_at">
              🕒 <span>Diterbitkan: {{ formatDate(sambutan.created_at) }}</span>
            </div>
            <div class="flex items-center gap-2">
              🌍 <span>Status: 
                <span
                  :class="sambutan.is_public 
                    ? 'text-green-600 dark:text-green-300' 
                    : 'text-red-600 dark:text-red-300'"
                >
                  {{ sambutan.is_public ? 'Publik' : 'Privat' }}
                </span>
              </span>
            </div>
          </div>

          <!-- Isi Sambutan -->
          <div class="prose dark:prose-invert max-w-none" v-html="sambutan.isi_sambutan" />

          <!-- File Sambutan -->
          <div v-if="sambutan.file" class="mt-4">
            <a
              :href="`/storage/${sambutan.file}`"
              target="_blank"
              class="inline-flex items-center gap-2 text-orange-600 hover:underline text-sm"
            >
              📎 Lihat Lampiran Sambutan
            </a>
          </div>

          <!-- Kontributor -->
          <div v-if="sambutan.kontributor?.length" class="mt-6">
            <p class="font-semibold mb-2 flex items-center gap-2 text-sm text-gray-800 dark:text-white">
              🤝 Kontributor:
            </p>
            <ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300">
              <li v-for="user in sambutan.kontributor" :key="user.id">
                {{ user.name }}
              </li>
            </ul>
          </div>

        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'

// Props dari controller
const props = defineProps({
  sambutan: Object,
})

// Format tanggal
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}
</script>
