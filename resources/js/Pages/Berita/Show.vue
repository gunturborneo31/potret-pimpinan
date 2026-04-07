<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'
import { ArrowLeft, Eye, Pencil, User, Users, Globe, Paperclip, BookOpen } from 'lucide-vue-next'
import dayjs from 'dayjs'
import 'dayjs/locale/id'
dayjs.locale('id')

const props = defineProps({
    berita: Object,
    kontributors: Object,
})
</script>

<template>
  <AuthenticatedLayout>
    <div class="max-w-4xl mx-auto py-8 px-4">
      
      <!-- Tombol Kembali -->
      <Link
        class="inline-flex items-center mb-4 text-sm text-orange-600 hover:underline"
        :href="route('berita.index')"
      >
        <ArrowLeft class="w-4 h-4 mr-1" /> Kembali ke Daftar Berita
      </Link>

      <!-- Card Berita -->
      <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
        <!-- Thumbnail -->
        <img
          :src="berita.thumbnail ? `/storage/${berita.thumbnail}` : '/storage/default.jpg'"
          alt="Thumbnail Berita"
          class="w-full h-64 object-cover"
        />

        <!-- Konten -->
        <div class="p-6">
          <!-- Judul dan Tanggal -->
          <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-1">{{ berita.judul }}</h1>

          <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">📅 Terbit: {{ dayjs(berita.tanggal_terbit).format('D MMMM YYYY') }} </p>

          <!-- Informasi Tambahan -->
          <div class="text-sm text-gray-700 dark:text-gray-300 mb-4 space-y-1">
            <p class="flex items-center gap-2">
              <Eye class="w-4 h-4" /> Views: {{ berita.jumlah_view || 0 }}
            </p>
            <p class="flex items-center gap-2">
              <Pencil class="w-4 h-4" /> Editor: {{ berita.editor?.name || '-' }}
              <span class="ml-2">| 🖋️ Penulis: {{ berita.penulis?.name || '-' }}</span>
            </p>
          <p class="flex items-center gap-2">
  <Users class="w-4 h-4" /> Kontributor:
  <span v-if="berita.lainnya_id?.length">
    <span
      v-for="id in berita.lainnya_id"
      :key="id"
      class="ml-1 px-2 py-0.5 bg-orange-100 text-orange-800 rounded text-xs dark:bg-orange-900 dark:text-orange-200"
    >
      {{ kontributors[id]?.name || 'User #' + id }}
    </span>
  </span>
  <span v-else>-</span>
</p>

            <p class="flex items-center gap-2">
              <BookOpen class="w-4 h-4" /> Kegiatan: {{ berita.kegiatan?.judul || '-' }}
            </p>
            <p class="flex items-center gap-2">
              <Globe class="w-4 h-4" />
              Status:
              <span
                :class="berita.is_public
                  ? 'text-green-600 dark:text-green-400'
                  : 'text-red-600 dark:text-red-400'"
              >
                {{ berita.is_public ? 'Public' : 'Private' }}
              </span>
            </p>
          </div>

          <!-- Isi Berita -->
          <div class="prose dark:prose-invert max-w-none" v-html="berita.isi_berita"></div>

          <!-- Lampiran -->
          <div v-if="berita.file" class="mt-0">
            <a
              :href="`/storage/${berita.file}`"
              class="inline-flex items-center gap-2 text-blue-600 hover:underline dark:text-blue-400"
              target="_blank"
              download
            >
              <Paperclip class="w-4 h-4" /> Unduh Lampiran
            </a>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
img {
  border-radius: 0.25rem;
}
</style>
