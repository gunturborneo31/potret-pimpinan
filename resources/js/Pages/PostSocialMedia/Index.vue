<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed, defineProps, watch } from 'vue'
import { Search, Edit, Trash2 } from 'lucide-vue-next'
import { toast } from 'vue3-toastify'

import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

const props = defineProps({
  posts: Object,
  users: Object,
  userselect: Object,
  filters: Object,
})

/**
 * Opsi select penulis
 * (kita pakai value string biar konsisten saat bandingkan dan saat kirim ke server)
 */
const userOptions = computed(() =>
  Object.values(props.userselect ?? {}).map(user => ({
    value: String(user.id),
    label: user.name
  }))
)

/**
 * Sinkronisasi filter awal dari server:
 * Pastikan selectedUsers selalu berupa array of values (ID string).
 */
const selectedUsers = ref(
  Array.isArray(props.filters?.penulis)
    ? props.filters.penulis.map(id => String(id))
    : []
)

const search = ref(props.filters?.search || '')

/**
 * Hasil filter untuk tampilan di client
 */
const filteredPosts = computed(() => {
  let filtered = props.posts.data || []

  if (search.value) {
    filtered = filtered.filter(post =>
      (post.judul || '').toLowerCase().includes(search.value.toLowerCase())
    )
  }

  if (selectedUsers.value.length > 0) {
    const selectedIds = selectedUsers.value.map(String)
    filtered = filtered.filter(post => selectedIds.includes(String(post.user_id)))
  }

  return filtered
})

/**
 * Jaga supaya selectedUsers selalu array
 */
watch(selectedUsers, (newVal) => {
  if (!Array.isArray(newVal)) selectedUsers.value = []
})

/**
 * Sinkron ke URL/server saat filter berubah
 */
watch([search, selectedUsers], () => {
  router.get(route('post-sosmed.index'), {
    search: search.value,
    penulis: selectedUsers.value, // kirim array of IDs
  }, {
    preserveState: true,
    replace: true,
  })
})

const confirmDelete = async (id) => {
  if (confirm('Yakin ingin menghapus post ini?')) {
    try {
      await router.delete(route('post-sosmed.destroy', id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => toast.success('Post berhasil dihapus!'),
        onError: () => toast.error('Gagal menghapus post.'),
      })
    } catch (error) {
      console.error(error)
      toast.error('Terjadi kesalahan saat menghapus post.')
    }
  }
}

function openLink(link) {
  if (!link) return
  window.open(link, '_blank', 'noopener')
}
</script>

<template>
  <Head title="Post Social Media" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Post Social Media</h2>
      </div>
    </template>

    <!-- Tombol tambah -->
    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-end">
        <Link
          href="/post-sosmed/create"
          class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded shadow text-sm"
        >
          + Tambah Post
        </Link>
      </div>
    </div>

    <div class="max-w-7xl mx-auto py-1 px-4 sm:px-6 lg:px-8">
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4 sm:p-6">

        <!-- Filter bar (responsif & rapi) -->
        <div class="mb-4 flex flex-col sm:flex-row gap-3 sm:items-center">
          <!-- Search -->
          <div class="order-1 sm:order-none flex items-center gap-2 flex-1 min-w-0">
            <Search class="w-4 h-4 text-gray-500 dark:text-gray-300 flex-shrink-0" />
            <input
              v-model="search"
              type="text"
              placeholder="Cari judul post..."
              class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500 text-sm min-w-0"
            />
          </div>

          <!-- Multiselect Penulis (dibatasi lebarnya agar input tetap terlihat) -->
          <!-- <div class="flex-shrink-0 w-full sm:w-64 md:w-72 lg:w-80">
            <Multiselect
              v-model="selectedUsers"
              :options="userOptions"
              placeholder="Pilih Pembuat"
              multiple
              :searchable="true"
              :clearable="true"
              :close-on-select="false"
              label="label"
              value-prop="value"
              track-by="value"
              class="text-sm dark:text-gray-800 w-full"
            />
          </div> -->
        </div>

        <!-- =================== TABEL (md+) =================== -->
        <div class="hidden md:block w-full overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-100 dark:bg-gray-700 text-left text-gray-700 dark:text-gray-200">
                <th class="px-4 py-3 font-semibold">Judul</th>
                <th class="px-4 py-3 font-semibold">Link</th>
                <th class="px-4 py-3 font-semibold">Platform</th>
                <th class="px-4 py-3 font-semibold">Pembuat</th>
                <th class="px-4 py-3 text-right font-semibold">Aksi</th>
              </tr>
            </thead>

            <tbody>
              <template v-if="filteredPosts.length > 0">
                <tr
                  v-for="item in filteredPosts"
                  :key="item.id"
                  class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900 transition"
                >
                  <td class="px-4 py-3 text-gray-900 dark:text-gray-100 font-medium">
                    {{ item.judul }}
                  </td>

                  <td class="px-4 py-3">
                    <button
                      class="text-blue-600 hover:text-blue-800 underline max-w-[28rem] truncate"
                      @click="openLink(item.link)"
                      :title="item.link"
                    >
                      {{ item.link }}
                    </button>
                  </td>

                  <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                    {{ item.option }}
                  </td>

                  <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                    {{ props.users[item.user_id]?.name || '-' }}
                  </td>

                  <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-3">
                      <Link
                        :href="`/post-sosmed/${item.id}/edit`"
                        class="flex items-center text-blue-600 hover:text-blue-800"
                        title="Edit"
                      >
                        <Edit class="w-5 h-5" />
                      </Link>
                      <button
                        @click="confirmDelete(item.id)"
                        class="flex items-center text-red-600 hover:text-red-800"
                        title="Hapus"
                      >
                        <Trash2 class="w-5 h-5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </template>

              <tr v-else>
                <td colspan="5" class="text-center py-6 text-gray-500 dark:text-gray-400">
                  Tidak ada data post
                  <template v-if="selectedUsers.length > 0"> untuk pembuat yang dipilih</template>.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- =================== KARTU (mobile / < md) =================== -->
        <div class="md:hidden space-y-3">
          <template v-if="filteredPosts.length > 0">
            <div
              v-for="item in filteredPosts"
              :key="item.id"
              class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-4"
            >
              <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                  <h3 class="font-semibold text-gray-900 dark:text-white">
                    {{ item.judul }}
                  </h3>

                  <p class="text-xs mt-1">
                    <button
                      class="text-blue-600 hover:text-blue-800 underline truncate"
                      @click="openLink(item.link)"
                      :title="item.link"
                    >
                      {{ item.link }}
                    </button>
                  </p>

                  <div class="mt-2 text-xs text-gray-600 dark:text-gray-300 space-y-0.5">
                    <p>📱 Platform: <span class="font-medium text-gray-800 dark:text-gray-100">{{ item.option }}</span></p>
                    <p>👤 Pembuat: <span class="font-medium text-gray-800 dark:text-gray-100">{{ props.users[item.user_id]?.name || '-' }}</span></p>
                  </div>
                </div>

                <div class="flex flex-col gap-2 shrink-0">
                  <Link
                    :href="`/post-sosmed/${item.id}/edit`"
                    class="inline-flex items-center justify-center px-3 py-1.5 text-xs rounded border border-blue-200 text-blue-600 hover:bg-blue-50 dark:border-blue-900 dark:text-blue-300 dark:hover:bg-blue-950"
                    title="Edit"
                  >
                    <Edit class="w-4 h-4 mr-1" /> Edit
                  </Link>
                  <button
                    @click="confirmDelete(item.id)"
                    class="inline-flex items-center justify-center px-3 py-1.5 text-xs rounded border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950"
                    title="Hapus"
                  >
                    <Trash2 class="w-4 h-4 mr-1" /> Hapus
                  </button>
                </div>
              </div>
            </div>
          </template>

          <div v-else class="text-center text-sm text-gray-500 dark:text-gray-400 py-6">
            Tidak ada data post
            <template v-if="selectedUsers.length > 0"> untuk pembuat yang dipilih</template>.
          </div>
        </div>

        <!-- Pagination -->
        <div class="mt-5 flex justify-center gap-1">
          <template v-for="link in props.posts.links" :key="link.url || link.label">
            <button
              v-if="link.url"
              @click.prevent="router.get(link.url, { preserveState: true, preserveScroll: true })"
              class="px-3 py-1 border rounded text-sm"
              :class="{
                'bg-orange-500 text-white border-orange-500': link.active,
                'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600/80': !link.active
              }"
              v-html="link.label"
            />
            <span v-else v-html="link.label" class="px-3 py-1 text-gray-400 text-sm"></span>
          </template>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Pastikan tag Multiselect tidak tinggi berlebihan walau banyak pilihan */
:deep(.multiselect-tags) {
  max-height: 36px; /* ~ h-9 */
  overflow: hidden;
}
:deep(.multiselect-tag) {
  max-width: 7rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
/* Dropdown biar di atas elemen lain saat terbuka */
:deep(.multiselect) {
  z-index: 10;
}
</style>
