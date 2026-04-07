<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Eye, Pencil, Trash } from 'lucide-vue-next'
import { ref, watch } from 'vue'
import debounce from 'lodash.debounce'

const props = defineProps({
  permohonans: Object,
  filters: Object,
  user: Object,
})

// Pastikan semua key tersedia meskipun props.filters null
const form = ref({
  search: props.filters?.search ?? '',
  user: props.filters?.user ?? '',
  kategori: props.filters?.kategori ?? '',
  priority: props.filters?.priority ?? '',
  status: props.filters?.status ?? '',
})

// Fungsi pencarian dengan debounce 500ms
const performSearch = debounce(() => {
  router.get('/permohonan', form.value, {
    preserveState: true,
    replace: true,
  })
}, 500)

// Pantau perubahan form dan jalankan debounced fetch
watch(form, performSearch, { deep: true })

const kategoriOptions = ['Sambutan', 'Dokumentasi', 'Lainnya']
const priorityOptions = ['Critical/Urgent', 'Medium', 'Low']
const statusOptions = ['Diajukan', 'Diproses', 'Selesai', 'Ditolak']

const statusClass = {
  Diajukan: 'bg-gray-200 text-gray-800',
  Diproses: 'bg-yellow-200 text-yellow-800',
  Selesai: 'bg-green-200 text-green-800',
  Ditolak: 'bg-red-200 text-red-800',
}

const statusIcon = {
  Diajukan: '📝',
  Diproses: '⏳',
  Selesai: '✅',
  Ditolak: '❌',
}

function deletePermohonan(id) {
  if (confirm('Apakah Anda yakin ingin menghapus permohonan ini?')) {
    router.delete(`/permohonan/${id}`, {
      preserveScroll: true,
      onSuccess: () => {
        alert('Permohonan berhasil dihapus.')
      },
      onError: () => {
        alert('Gagal menghapus permohonan.')
      }
    })
  }
}

function resetFilters() {
  form.value = {
    search: '',
    user: '',
    kategori: '',
    priority: '',
    status: '',
  }

  router.get('/permohonan', {}, {
    preserveState: true,
    replace: true,
  })
}
</script>

<template>
  <Head title="Daftar Permohonan" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-bold text-gray-800 dark:text-white">Daftar Permohonan</h2>
    </template>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-end mb-4">
        <Link
          href="/permohonan/create"
          class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded shadow dark:shadow-none">
          + Buat Permohonan
        </Link>
      </div>

      <!-- Filter -->
      <div class="mb-4 bg-white dark:bg-gray-800 p-4 rounded shadow-sm grid md:grid-cols-5 gap-2 text-sm">
        <input
          v-model="form.search"
          type="text"
          placeholder="Cari Judul..."
          class="border dark:border-gray-600 dark:bg-gray-700 dark:text-white px-2 py-1 rounded" />

        <input
          v-if="$page.props.auth.user.role === 'SUPERADMIN'"
          v-model="form.user"
          type="text"
          placeholder="Cari User..."
          class="border dark:border-gray-600 dark:bg-gray-700 dark:text-white px-2 py-1 rounded" />

        <select
          v-model="form.kategori"
          class="border dark:border-gray-600 dark:bg-gray-700 dark:text-white px-2 py-1 rounded">
          <option value="">Semua Kategori</option>
          <option v-for="k in kategoriOptions" :key="k" :value="k">{{ k }}</option>
        </select>

        <select
          v-model="form.priority"
          class="border dark:border-gray-600 dark:bg-gray-700 dark:text-white px-2 py-1 rounded">
          <option value="">Semua Prioritas</option>
          <option v-for="p in priorityOptions" :key="p" :value="p">{{ p }}</option>
        </select>

        <select
          v-model="form.status"
          class="border dark:border-gray-600 dark:bg-gray-700 dark:text-white px-2 py-1 rounded">
          <option value="">Semua Status</option>
          <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
        </select>

        <div class="col-span-full flex justify-end">
          <button
            @click="resetFilters"
            class="text-sm px-4 py-1.5 border rounded text-gray-600 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
            🔄 Reset
          </button>
        </div>
      </div>

      <!-- ======================= -->
      <!--  MOBILE & SMALL TABLET  -->
      <!-- ======================= -->
      <div class="sm:hidden space-y-3">
        <div
          v-for="(p, index) in permohonans.data"
          :key="p.id"
          class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border dark:border-gray-700 p-3">
          <!-- Header card -->
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">
                {{ p.title }}
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                #{{ index + 1 }} ·
                {{
                  new Date(p.created_at).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                  })
                }}
              </p>
            </div>
            <span
              class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold"
              :class="statusClass[p.status ?? 'Diajukan']">
              {{ statusIcon[p.status ?? 'Diajukan'] }} {{ p.status ?? 'Diajukan' }}
            </span>
          </div>

          <!-- Body card -->
          <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
            <div>
              <span class="text-gray-500">Kategori:</span>
              <span class="font-medium"> {{ p.kategori }} </span>
            </div>
            <div>
              <span class="text-gray-500">Prioritas:</span>
              <span class="font-medium"> {{ p.priority }} </span>
            </div>
            <div>
              <span class="text-gray-500">Disposisi:</span>
              <span class="font-medium" v-if="p.disposisi">{{ p.disposisi.name }}</span>
              <span v-else class="italic text-red-500">Belum Disposisi</span>
            </div>
            <div>
              <span class="text-gray-500">Pemohon:</span>
              <span class="font-medium"> {{ p.user?.name ?? '-' }} </span>
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-3 flex items-center gap-3">
            <Link
              :href="`/permohonan/${p.id}`"
              class="text-orange-500 hover:text-orange-600"
              aria-label="Lihat">
              <Eye class="w-5 h-5" />
            </Link>

            <Link
              v-if="['SUPERADMIN', 'STAFF', 'BIASA'].includes(props.user.role)"
              :href="`/permohonan/${p.id}/edit`"
              class="text-blue-500 hover:text-blue-700"
              aria-label="Ubah">
              <Pencil class="w-5 h-5" />
            </Link>

            <button
              v-if="['SUPERADMIN', 'STAFF'].includes(props.user.role)"
              @click="deletePermohonan(p.id)"
              class="text-red-500 hover:text-red-700"
              title="Hapus permohonan"
              aria-label="Hapus">
              <Trash class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>

      <!-- ======================= -->
      <!--   DESKTOP & ≥SM TABLET  -->
      <!-- ======================= -->
      <div class="hidden sm:block bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
        <div class="overflow-x-auto">
          <table class="min-w-[900px] w-full border text-sm">
            <thead class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
              <tr>
                <th class="px-3 py-2 border text-left hidden lg:table-cell">#</th>
                <th class="px-3 py-2 border text-left">Judul</th>
                <th class="px-3 py-2 border text-left hidden md:table-cell">Kategori</th>
                <th class="px-3 py-2 border text-left hidden md:table-cell">Prioritas</th>
                <th class="px-3 py-2 border text-left">Status</th>
                <th class="px-3 py-2 border text-left hidden lg:table-cell">Disposisi</th>
                <th class="px-3 py-2 border text-left hidden md:table-cell">Dibuat</th>
                <th class="px-3 py-2 border text-left hidden lg:table-cell">Pemohon</th>
                <th class="px-3 py-2 border text-left">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(p, index) in permohonans.data"
                :key="p.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-100">
                <td class="px-3 py-2 border hidden lg:table-cell">{{ index + 1 }}</td>
                <td class="px-3 py-2 border max-w-[280px] truncate">{{ p.title }}</td>
                <td class="px-3 py-2 border hidden md:table-cell">{{ p.kategori }}</td>
                <td class="px-3 py-2 border hidden md:table-cell">{{ p.priority }}</td>

                <td class="px-3 py-2 border">
                  <span
                    class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold"
                    :class="statusClass[p.status ?? 'Diajukan']">
                    {{ statusIcon[p.status ?? 'Diajukan'] }} {{ p.status ?? 'Diajukan' }}
                  </span>
                </td>

                <td class="px-3 py-2 border hidden lg:table-cell">
                  <span v-if="p.disposisi" class="text-gray-800 dark:text-gray-200">
                    {{ p.disposisi.name }}
                  </span>
                  <span v-else class="italic text-red-500">Belum Disposisi</span>
                </td>

                <td
                  class="px-3 py-2 border text-gray-500 dark:text-gray-300 text-xs whitespace-nowrap hidden md:table-cell">
                  {{
                    new Date(p.created_at).toLocaleDateString('id-ID', {
                      year: 'numeric',
                      month: 'short',
                      day: 'numeric',
                    })
                  }}
                </td>

                <td class="px-3 py-2 border hidden lg:table-cell">
                  {{ p.user?.name ?? '-' }}
                </td>

                <td class="px-3 py-2 border">
                  <div class="flex items-center gap-2">
                    <Link
                      :href="`/permohonan/${p.id}`"
                      class="text-orange-500 hover:text-orange-600"
                      aria-label="Lihat">
                      <Eye class="w-5 h-5" />
                    </Link>

                    <Link
                      v-if="['SUPERADMIN', 'STAFF', 'BIASA'].includes(props.user.role)"
                      :href="`/permohonan/${p.id}/edit`"
                      class="text-blue-500 hover:text-blue-700"
                      aria-label="Ubah">
                      <Pencil class="w-5 h-5" />
                    </Link>

                    <button
                      v-if="['SUPERADMIN', 'STAFF'].includes(props.user.role)"
                      @click="deletePermohonan(p.id)"
                      class="text-red-500 hover:text-red-700"
                      title="Hapus permohonan"
                      aria-label="Hapus">
                      <Trash class="w-5 h-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-4 flex justify-end space-x-1">
        <template v-for="link in permohonans.links" :key="link.label">
          <Link
            v-if="link.url"
            :href="link.url"
            v-html="link.label"
            class="px-3 py-1 border rounded text-sm"
            :class="{
              'bg-orange-500 text-white': link.active,
              'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700': !link.active
            }" />
          <span
            v-else
            v-html="link.label"
            class="px-3 py-1 border rounded text-sm text-gray-400 opacity-50 cursor-not-allowed" />
        </template>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
