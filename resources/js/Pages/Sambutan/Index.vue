<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Search, Edit, Trash2, Eye, Globe } from 'lucide-vue-next'
import { toast } from 'vue3-toastify'


const confirmDelete = async (id) => {
  if (confirm('Yakin ingin menghapus sambutan ini?')) {
    try {
      await router.delete(route('sambutan.destroy', id), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => toast.success('Sambutan berhasil dihapus!'),
        onError: () => toast.error('Gagal menghapus sambutan.'),
      })
    } catch (error) {
      console.error(error)
      toast.error('Terjadi kesalahan saat menghapus sambutan.')
    }
  }
}


const props = defineProps({
  sambutans: Array,
  kontributors: Object, // ✅ Tambahkan ini
})

const search = ref('')
const filteredSambutans = computed(() => {
  if (!search.value) return props.sambutans
  return props.sambutans.filter(s =>
    s.judul.toLowerCase().includes(search.value.toLowerCase())
  )
})

function showPublic(slug) {
  window.open(`/sambutan/${slug}`, '_blank')
}
</script>


<template>
  <Head title="Daftar Sambutan" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Daftar Sambutan</h2>
      </div>
    </template>
<div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
  <div class="flex justify-end">
    <Link
      href="/sambutan/create"
      class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded shadow text-sm"
    >
      + Tambah Sambutan
    </Link>
  </div>
</div>

    <div class="max-w-7xl mx-auto py-1 px-4 sm:px-6 lg:px-8">
        
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <!-- Search -->
        <div class="mb-4">
          <div class="flex items-center gap-2">
            <Search class="w-4 h-4 text-gray-500 dark:text-gray-300" />
            <input
              v-model="search"
              type="text"
              placeholder="Cari judul sambutan..."
              class="w-full px-3 py-2 rounded border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-orange-500 focus:border-orange-500 text-sm"
            />
          </div>
        </div>

        <!-- Table -->
      <div class="w-full overflow-visible">

          <table class="w-full text-sm">
            <thead>
              <tr class="bg-gray-100 dark:bg-gray-700 text-left text-gray-700 dark:text-gray-200">
                <th class="px-4 py-3 font-semibold">Judul</th>
                <th class="px-4 py-3 font-semibold">Pembuat</th>
                <th class="px-4 py-3 font-semibold">Kontributor</th>
                <th class="px-4 py-3 font-semibold">Status</th>
                <th class="px-4 py-3 text-right font-semibold">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="item in filteredSambutans"
                :key="item.id"
                class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900 transition"
              >
                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 font-medium">{{ item.judul }}</td>
                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 font-medium">{{ item.user?.name || '-' }}</td>
                

                <td class="py-3 px-4">
  <div class="flex flex-wrap gap-1 items-start">
    <!-- Tampilkan kontributor pertama -->
    <span
      v-if="item.kontributor_id?.[0]"
      class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs dark:bg-orange-900 dark:text-orange-200"
    >
      {{ props.kontributors[item.kontributor_id[0]]?.name || 'User #' + item.kontributor_id[0] }}
    </span>

    <!-- Tampilkan +n lainnya dengan tooltip -->
    <span
      v-if="item.kontributor_id?.length > 1"
      class="relative group bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs dark:bg-gray-700 dark:text-gray-200 cursor-default"
    >
      +{{ item.kontributor_id.length - 1 }} lainnya

      <div
        class="absolute z-10 hidden group-hover:block bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs rounded p-2 w-52 mt-1 left-0"
      >
        <ul class="list-disc list-inside">
          <li
            v-for="id in item.kontributor_id.slice(1)"
            :key="id"
          >
            {{ props.kontributors[id]?.name || 'User #' + id }}
          </li>
        </ul>
      </div>
    </span>
  </div>
</td>


                <td class="px-4 py-3">
                   <span
    class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full"
    :class="item.is_public
      ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
      : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
  >
    <Globe class="w-3 h-3" />
    {{ item.is_public ? 'Public' : 'Private' }}
  </span>
                </td>
                <td class="px-4 py-3 text-right space-x-2">
                  <Link
                    :href="`/sambutan/${item.id}/edit`"
                    class="inline-flex items-center text-blue-600 hover:text-blue-800"
                    title="Edit"
                  >
                    <Edit class="w-4 h-4" />
                  </Link>
                  <button
                    @click="showPublic(item.slug)"
                    class="inline-flex items-center text-green-600 hover:text-green-800"
                    title="Lihat Publik"
                  >
                    <Eye class="w-4 h-4" />
                  </button>
      <button
  @click="confirmDelete(item.id)"
  class="inline-flex items-center text-red-600 hover:text-red-800"
>
  <Trash2 class="w-4 h-4" />
</button>


                </td>
              </tr>

              <tr v-if="filteredSambutans.length === 0">
                <td colspan="4" class="text-center py-6 text-gray-500 dark:text-gray-400">
                  Tidak ada data sambutan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
