<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'
import { Plus, Eye, Pencil, Trash, Globe } from 'lucide-vue-next'
import { toast } from 'vue3-toastify'
import axios from 'axios'
import dayjs from 'dayjs'
import 'dayjs/locale/id'
dayjs.locale('id')
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

import { usePage } from '@inertiajs/vue3'

const page = usePage()

watch(() => page.props.flash, (flash) => {
  if (flash?.success) {
    toast.success(flash.success)
  } else if (flash?.error) {
    toast.error(flash.error)
  }
})

const props = defineProps({
  beritas: Object,
  filters: Object,
  kontributors: Object,
  penulisList: Object,
})

const userOptions = computed(() =>
  Object.values(props.penulisList ?? {}).map(user => ({
    value: user.id,
    label: user.name
  }))
)

const search = ref(props.filters.search || '')
const tanggal_terbit = ref(props.filters.tanggal_terbit || '')
const status = ref(props.filters.status || '')
const penulis = ref(props.filters.penulis || [])

const bulan = ref(props.filters.bulan || '')
const tahun = ref(props.filters.tahun || '')

const bulanList = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]

const tahunList = Array.from({ length: 10 }, (_, i) => new Date().getFullYear() - i)

watch([search, status, bulan, tahun, penulis], () => {
  router.get(route('berita.index'), {
    search: search.value,
    status: status.value,
    bulan: bulan.value,
    tahun: tahun.value,
    penulis: penulis.value,
  }, {
    preserveState: true,
    replace: true
  })
})

const resetFilter = () => {
  search.value = ''
  status.value = ''
  bulan.value = ''
  tahun.value = ''
  penulis.value = []

  router.get(route('berita.index'), {
    search: '',
    status: '',
    bulan: '',
    tahun: '',
    penulis: [],
  }, {
    preserveState: false,
    replace: true,
  })
}

const tampilkan = async (id) => {
  if (confirm('Publish berita ini?')) {
    try {
      await axios.put(route('berita.tampilkan', id)) // GANTI delete jadi put
      toast.success('Berita berhasil dipublish!')

      router.reload({
        preserveScroll: true,
        preserveState: true,
      })
    } catch (error) {
      console.error(error)
      toast.error('Terjadi kesalahan saat publish berita.')
    }
  }
}

const sembunyikan = async (id) => {
  if (confirm('Draft berita ini?')) {
    try {
      await axios.put(route('berita.sembunyikan', id)) // GANTI delete jadi put
      toast.success('Berita berhasil dipublish!')

      router.reload({
        preserveScroll: true,
        preserveState: true,
      })
    } catch (error) {
      console.error(error)
      toast.error('Terjadi kesalahan saat publish berita.')
    }
  }
}

const hapus = async (id) => {
  if (confirm('Yakin ingin menghapus berita ini?')) {
    try {
      await axios.delete(route('berita.destroy', id))
      toast.success('Berita berhasil dihapus!')

      router.reload({
        preserveScroll: true,
        preserveState: true,
      })
    } catch (error) {
      console.error(error)
      toast.error('Terjadi kesalahan saat menghapus berita.')
    }
  }
}

const goToPage = (url) => {
  if (url) {
    router.visit(url, {
      preserveScroll: true,
      preserveState: true,
      data: {
        search: search.value,
        status: status.value,
        bulan: bulan.value,
        tahun: tahun.value,
      },
    })
  }
}

function truncate(text, length = 25) {
  return text.length > length ? text.slice(0, length) + '...' : text
}

const showDetailId = ref(null)

function toggleDetail(id) {
  showDetailId.value = showDetailId.value === id ? null : id
}

function openBerita(slug) {
  window.open(route('landing.beritashow', slug), '_blank', 'noopener');
}
</script>

<template>
  <Head title="Berita" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Daftar Berita</h1>
      </div>
    </template>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- CTA Tambah -->
      <div class="flex items-center mb-6">
        <Link
          :href="route('berita.create')"
          class="ml-auto inline-flex items-center px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm rounded shadow"
        >
          <Plus class="w-4 h-4 mr-2" /> Tambah Berita
        </Link>
      </div>

      <!-- Filter -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 items-center">
        <input
          v-model="search"
          type="text"
          placeholder="Cari judul berita..."
          class="w-full px-4 py-2 border rounded-md text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
        />

        <select
          v-model="bulan"
          class="w-full px-4 py-2 border rounded-md text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
        >
          <option value="">Pilih Bulan</option>
          <option v-for="(nama, index) in bulanList" :key="index" :value="String(index + 1).padStart(2, '0')">
            {{ nama }}
          </option>
        </select>

        <select
          v-model="tahun"
          class="w-full px-4 py-2 border rounded-md text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white"
        >
          <option value="">Pilih Tahun</option>
          <option v-for="year in tahunList" :key="year" :value="year">{{ year }}</option>
        </select>

        <Multiselect
          v-model="penulis"
          :options="userOptions"
          placeholder="Pilih Penulis"
          :searchable="true"
          :clearable="true"
          class="w-full text-sm dark:text-gray-800"
        />

        <label class="inline-flex items-center space-x-2 text-sm text-gray-700 dark:text-gray-200 md:col-span-2">
          <input
            type="checkbox"
            v-model="status"
            true-value="1"
            false-value="0"
            class="rounded border-gray-300 dark:border-gray-600 text-orange-500 focus:ring-orange-400"
          />
          <span>Tampilkan saja yang publik</span>
        </label>

        <div class="md:col-span-2 md:text-right">
          <a
            href="/berita"
            class="inline-flex items-center px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-sm text-gray-800 dark:text-white rounded-md"
          >
            Reset Filter
          </a>
        </div>
      </div>

      <!-- ===== MOBILE/TABLET: Card List ===== -->
      <div class="md:hidden space-y-3">
        <div
          v-for="berita in props.beritas.data"
          :key="berita.id"
          class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-100 dark:border-gray-700 p-4"
        >
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <h3 class="font-semibold text-gray-900 dark:text-white leading-snug">
                {{ truncate(berita.judul, 70) }}
              </h3>

              <div class="mt-2 space-y-1 text-xs text-gray-600 dark:text-gray-300">
                <div>✍️ Penulis: <span class="font-medium">{{ berita.penulis?.name || '-' }}</span></div>
                <div>🛠 Editor: <span class="font-medium">{{ berita.editor?.name || '-' }}</span></div>
                <div class="flex flex-wrap gap-1 items-start">
                  🤝 Kontributor:
                  <template v-if="berita.lainnya_id?.length">
                    <span
                      class="bg-orange-100 text-orange-800 px-2 py-0.5 rounded text-[11px] dark:bg-orange-900 dark:text-orange-200"
                    >
                      {{ props.kontributors[berita.lainnya_id[0]]?.name || ('User #' + berita.lainnya_id[0]) }}
                    </span>
                    <span
                      v-if="berita.lainnya_id.length > 1"
                      class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded text-[11px] dark:bg-gray-700 dark:text-gray-200"
                    >
                      +{{ berita.lainnya_id.length - 1 }} lainnya
                    </span>
                  </template>
                  <template v-else>-</template>
                </div>
                <div>📅 {{ dayjs(berita.tanggal_terbit).format('D MMMM YYYY') }}</div>
                <div>
                  <span
                    class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full"
                    :class="berita.is_public
                      ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                      : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                  >
                    <Globe class="w-3 h-3" />
                    {{ berita.is_public ? 'Public' : 'Private' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Aksi cepat (ikon) -->
            <div class="shrink-0 flex flex-col items-end gap-2">
              <button
                @click="openBerita(berita.slug)"
                class="text-green-600 hover:text-green-700"
                title="Lihat"
              >
                <Eye class="w-5 h-5" />
              </button>

              <Link
                :href="route('berita.edit', berita.id)"
                class="text-blue-600 hover:text-blue-700"
                title="Edit"
              >
                <Pencil class="w-5 h-5" />
              </Link>

              <button
                v-if="berita.is_public == 0"
                @click="tampilkan(berita.id)"
                class="text-orange-500 hover:text-orange-600"
                title="Tampilkan (Public)"
              >
                <Globe class="w-5 h-5" />
              </button>
              <button
                v-else
                @click="sembunyikan(berita.id)"
                class="text-gray-500 hover:text-orange-600"
                title="Jadikan Private"
              >
                <Globe class="w-5 h-5" />
              </button>

              <button
                @click="hapus(berita.id)"
                class="text-red-600 hover:text-red-700"
                title="Hapus"
              >
                <Trash class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- Aksi tombol teks (opsional) -->
          <div class="mt-3 flex flex-wrap gap-3">
            <button @click="openBerita(berita.slug)" class="text-green-600 hover:underline text-sm flex items-center gap-1">
              <Eye class="w-4 h-4" /> Lihat
            </button>

            <Link :href="route('berita.edit', berita.id)" class="text-blue-600 hover:underline text-sm flex items-center gap-1">
              <Pencil class="w-4 h-4" /> Edit
            </Link>

            <button
              v-if="berita.is_public == 0"
              @click="tampilkan(berita.id)"
              class="text-orange-500 hover:underline text-sm flex items-center gap-1"
            >
              <Globe class="w-4 h-4" /> Tampilkan
            </button>
            <button
              v-else
              @click="sembunyikan(berita.id)"
              class="text-gray-500 hover:underline text-sm flex items-center gap-1"
            >
              <Globe class="w-4 h-4" /> Private
            </button>

            <button
              @click="hapus(berita.id)"
              class="text-red-600 hover:underline text-sm flex items-center gap-1"
            >
              <Trash class="w-4 h-4" /> Hapus
            </button>
          </div>
        </div>

        <!-- Empty state mobile -->
        <div
          v-if="props.beritas.data.length === 0"
          class="text-center py-8 text-gray-500 dark:text-gray-400"
        >
          Data tidak ditemukan.
        </div>
      </div>

      <!-- ===== DESKTOP: Table ===== -->
      <div class="hidden md:block bg-white dark:bg-gray-800 shadow rounded-xl overflow-x-auto">
        <table class="min-w-full text-sm text-left text-gray-700 dark:text-gray-200">
          <thead>
            <tr class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-white text-sm">
              <th class="py-3 px-4">Judul</th>
              <th class="py-3 px-4">Penulis</th>
              <th class="py-3 px-4">Editor</th>
              <th class="py-3 px-4">Kontributor</th>
              <th class="py-3 px-4">Tanggal Terbit</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="berita in props.beritas.data"
              :key="berita.id"
              class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
              <td class="py-3 px-4 font-medium truncate max-w-[18rem]">{{ truncate(berita.judul, 120) }}</td>
              <td class="py-3 px-4">{{ berita.penulis?.name || '-' }}</td>
              <td class="py-3 px-4">{{ berita.editor?.name || '-' }}</td>

              <td class="py-3 px-4">
                <div class="flex flex-wrap gap-1 items-start">
                  <span
                    v-if="berita.lainnya_id?.[0]"
                    class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs dark:bg-orange-900 dark:text-orange-200"
                  >
                    {{ props.kontributors[berita.lainnya_id[0]]?.name || 'User #' + berita.lainnya_id[0] }}
                  </span>
                  <span
                    v-if="berita.lainnya_id.length > 1"
                    class="relative group bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs dark:bg-gray-700 dark:text-gray-200 cursor-default"
                  >
                    +{{ berita.lainnya_id.length - 1 }} lainnya
                    <div
                      class="absolute z-10 hidden group-hover:block bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs rounded p-2 w-52 mt-1 left-0"
                    >
                      <ul class="list-disc list-inside">
                        <li v-for="id in berita.lainnya_id.slice(1)" :key="id">
                          {{ props.kontributors[id]?.name || 'User #' + id }}
                        </li>
                      </ul>
                    </div>
                  </span>
                </div>
              </td>

              <td class="py-3 px-4">
                {{ dayjs(berita.tanggal_terbit).format('D MMMM YYYY') }}
              </td>

              <td class="py-3 px-4">
                <span
                  class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full"
                  :class="berita.is_public
                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                >
                  <Globe class="w-3 h-3" />
                  {{ berita.is_public ? 'Public' : 'Private' }}
                </span>
              </td>

              <td class="py-3 px-4">
                <div class="flex gap-3 justify-center items-center">
                  <Link :href="route('berita.edit', berita.id)" class="text-blue-600 hover:underline flex items-center gap-1">
                    <Pencil class="w-4 h-4" /> Edit
                  </Link>

                  <button
                    @click="openBerita(berita.slug)"
                    class="text-green-600 hover:underline flex items-center gap-1"
                  >
                    <Eye class="w-4 h-4" /> Lihat
                  </button>

                  <button
                    v-if="berita.is_public == 0"
                    @click="tampilkan(berita.id)"
                    class="text-orange-500 hover:text-orange-600 flex items-center gap-1"
                  >
                    <Globe class="w-4 h-4" /> Tampilkan
                  </button>
                  <button
                    v-else
                    @click="sembunyikan(berita.id)"
                    class="text-gray-500 hover:text-orange-600 flex items-center gap-1"
                  >
                    <Globe class="w-4 h-4" /> Private
                  </button>

                  <button
                    @click="hapus(berita.id)"
                    class="text-red-600 hover:text-red-700 flex items-center gap-1"
                  >
                    <Trash class="w-4 h-4" /> Hapus
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="props.beritas.data.length === 0">
              <td colspan="7" class="text-center py-4 text-gray-500 dark:text-gray-400">Data tidak ditemukan.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="flex justify-center mt-6" v-if="props.beritas.links.length > 3">
        <nav class="inline-flex space-x-1">
          <button
            v-for="(link, index) in props.beritas.links"
            :key="index"
            @click="goToPage(link.url)"
            v-html="link.label"
            :class="[
              'px-3 py-1 rounded text-sm',
              link.active
                ? 'bg-orange-500 text-white'
                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600',
              !link.url && 'opacity-50 cursor-not-allowed'
            ]"
            :disabled="!link.url"
          />
        </nav>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}
.animate-fade-in { animation: fade-in 0.3s ease-out; }
</style>
