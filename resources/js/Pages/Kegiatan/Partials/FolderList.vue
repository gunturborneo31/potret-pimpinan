<script setup>
import { Folder } from 'lucide-vue-next'

const props = defineProps({
  folder: Object,
})

function truncate(text, length = 25) {
  return text.length > length ? text.slice(0, length) + '...' : text
}
</script>

<template>
  <div
    class="w-full bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 px-4 py-3 flex items-center justify-between hover:shadow-md transition"
  >
    <div class="flex items-center gap-4">
      <!-- Icon -->
      <Folder class="w-6 h-6 text-orange-500 shrink-0" />

      <!-- Folder Info -->
      <div class="flex flex-col">
        <router-link
          :to="{ name: 'kegiatansub.folders.show', params: { slug: folder.slug } }"
          class="text-sm font-medium text-gray-800 dark:text-white hover:underline"
        >
          {{ truncate(folder.judul) }}
        </router-link>
      </div>
    </div>
  </div>
</template>
