<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Eye, Pencil, Trash, User } from 'lucide-vue-next' // ⬅️ tambah User
import { ref, watch } from 'vue'
import debounce from 'lodash.debounce'

const props = defineProps({
  users: Object,
  filters: Object
})

const search = ref(props.filters.search || '')

watch(search, debounce((value) => {
  router.get(route('users.index'), { search: value }, {
    preserveState: true,
    replace: true
  })
}, 300))

function destroy(id) {
  if (confirm('Yakin hapus user?')) {
    router.delete(route('users.destroy', id))
  }
}
</script>

<template>
  <Head title="Manajemen User" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-bold text-gray-800 dark:text-white">Manajemen User</h2>
    </template>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header aksi + search -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <input
          type="text"
          v-model="search"
          placeholder="Cari nama, email, atau no hp/whatsapp..."
          class="border rounded px-3 py-2 w-full md:w-1/3 dark:bg-gray-700 dark:text-white"
        />
        <Link :href="route('users.create')" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded text-center">
          Tambah User
        </Link>
      </div>

      <!-- =================== TABEL (md+) =================== -->
      <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg hidden md:block">
        <div class="overflow-x-auto">
          <table class="min-w-full border text-sm">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
              <tr>
                <th class="p-3 text-left">Nama</th>
                <th class="p-3 text-left">Email</th>
                <th class="p-3 text-left">No Hp/Whatsapp</th>
                <th class="p-3 text-left">Role</th>
                <th class="p-3 text-left">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="user in users.data"
                :key="user.id"
                class="border-t dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700"
              >
                <td class="p-3">{{ user.name }}</td>
                <td class="p-3">{{ user.email }}</td>
                <td class="p-3">{{ user.no_hp }}</td>
                <td class="p-3 flex items-center gap-2">
                  <User
                    class="w-4 h-4"
                    :class="{
                      'text-blue-500': user.role === 'SUPERADMIN',
                      'text-orange-500': user.role === 'STAFF',
                      'text-gray-500': user.role === 'BIASA',
                    }"
                  />
                  {{ user.role === 'BIASA' ? 'USER' : user.role }}
                </td>
                <td class="p-3 space-x-2 whitespace-nowrap">
                  <Link
                    :href="route('users.edit', user.id)"
                    class="inline-flex items-center text-blue-500 hover:underline"
                  >
                    <Pencil class="w-4 h-4 mr-1" /> Edit
                  </Link>
                  <button
                    @click="destroy(user.id)"
                    class="inline-flex items-center text-red-500 hover:underline"
                  >
                    <Trash class="w-4 h-4 mr-1" /> Hapus
                  </button>
                </td>
              </tr>
              <tr v-if="users.data.length === 0">
                <td colspan="5" class="text-center py-4 text-gray-500 dark:text-gray-400">
                  Tidak ada data user.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- =================== KARTU (mobile / < md) =================== -->
      <div class="md:hidden space-y-3">
        <div
          v-for="user in users.data"
          :key="user.id"
          class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-4"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <h3 class="font-semibold text-gray-800 dark:text-white truncate">
                  {{ user.name }}
                </h3>
                <span
                  class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full"
                  :class="user.role === 'SUPERADMIN'
                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200'
                    : user.role === 'STAFF'
                      ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-200'
                      : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'"
                  title="Role"
                >
                  <User class="w-3.5 h-3.5" />
                  {{ user.role === 'BIASA' ? 'USER' : user.role }}
                </span>
              </div>

              <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 break-all">
                📧 {{ user.email }}
              </p>
              <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5 break-all">
                📱 {{ user.no_hp || '-' }}
              </p>
            </div>

            <div class="flex flex-col gap-2 shrink-0">
              <Link
                :href="route('users.edit', user.id)"
                class="inline-flex items-center justify-center px-3 py-1.5 text-xs rounded border border-blue-200 text-blue-600 hover:bg-blue-50 dark:border-blue-900 dark:text-blue-300 dark:hover:bg-blue-950"
              >
                <Pencil class="w-3.5 h-3.5 mr-1" /> Edit
              </Link>
              <button
                @click="destroy(user.id)"
                class="inline-flex items-center justify-center px-3 py-1.5 text-xs rounded border border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950"
              >
                <Trash class="w-3.5 h-3.5 mr-1" /> Hapus
              </button>
            </div>
          </div>
        </div>

        <div v-if="users.data.length === 0" class="text-center text-sm text-gray-500 dark:text-gray-400 py-6">
          Tidak ada data user.
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-4 flex justify-center">
        <div v-if="users.links.length > 3" class="flex flex-wrap gap-2">
          <template v-for="(link, i) in users.links" :key="i">
            <button
              v-if="link.url"
              @click="router.get(link.url)"
              class="px-3 py-1 rounded border text-sm"
              :class="{
                'bg-orange-500 text-white border-orange-500': link.active,
                'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 border-gray-200 dark:border-gray-600': !link.active
              }"
              v-html="link.label"
            />
          </template>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
