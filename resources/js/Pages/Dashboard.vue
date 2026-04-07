<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Pie } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  CategoryScale,
  LinearScale,
  BarElement,
} from 'chart.js'
import { FileText, Users, AlertTriangle, Hourglass } from 'lucide-vue-next'
import { Doughnut } from 'vue-chartjs'
import { Bar } from 'vue-chartjs'

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale, BarElement)

const props = defineProps({
  totalPermohonan: Number,
  kategoriCounts: Object,
  statusCounts: Object,
  prioritasCounts: Object,
  jumlahUserBiasa: Number,
  staffStats: Array,

  // ====== Berita ======
  beritaSummary:  { type: Object, default: () => ({ total: 0, public: 0, private: 0 }) },
  penulisStats:   { type: Array,  default: () => [] }, // [{id,name,jumlah}]
  kontributorStats:{ type: Array, default: () => [] }, // [{id,name,jumlah}]
  kontributorUnique: { type: Number, default: 0 },

  // ====== Sambutan ======
  sambutanTotal: Number,
  sambutanPerUser: Array,       // [{ user_id, name, total }]
  sambutanFilesTotal: Number,
  sambutanFilesByType: Object,  // { naskah, tapping, presentasi, terjemahan }

  // ====== Kegiatan ======
  kegiatanTotal: Number,
  kegiatanFilesTotal: Number,
  kegiatanByStatus: Object,      // { public, private }
  kegiatanCreatorUnique: Number,
  kegiatanCreators: Array,       // [{ user_id, name, total }]
  kegiatanMonthly: Object,       // { labels:[], total:[], publik:[], privat:[] }
  kegiatanDownloadsTotal: Number,

  // ====== Sosial Media (BARU) ======
  sosmedTotal: { type: Number, default: 0 },
  sosmedByPlatform: { type: Object, default: () => ({}) }, // { Instagram: 10, Facebook: 5, ... }
  sosmedCreators: { type: Array, default: () => [] },      // [{ user_id, name, total }]
  sosmedCreatorUnique: { type: Number, default: 0 },
})

// ========== KEGIATAN ==========
const pieKegiatanStatus = computed(() => ({
  labels: ['Publik', 'Private'],
  datasets: [{
    backgroundColor: ['#10b981', '#ef4444'],
    data: [
      props.kegiatanByStatus?.public  ?? 0,
      props.kegiatanByStatus?.private ?? 0
    ],
  }]
}))
const barKegiatanMonthly = computed(() => ({
  labels: props.kegiatanMonthly?.labels ?? [],
  datasets: [
    { label: 'Total',  data: props.kegiatanMonthly?.total  ?? [], backgroundColor: '#f59e0b' },
    { label: 'Publik', data: props.kegiatanMonthly?.publik ?? [], backgroundColor: '#10b981' },
    { label: 'Private',data: props.kegiatanMonthly?.privat ?? [], backgroundColor: '#ef4444' },
  ]
}))
const topMakers = computed(() => {
  const arr = Array.isArray(props.kegiatanCreators) ? [...props.kegiatanCreators] : []
  const top = arr.slice(0, 8)
  const rest = arr.slice(8)
  const others = rest.reduce((a,b)=> a + (b.total||0), 0)
  if (others > 0) top.push({ name: 'Lainnya', total: others })
  return top
})
const doughnutKegiatanMakers = computed(() => ({
  labels: topMakers.value.map(x => x.name || '—'),
  datasets: [{
    backgroundColor: ['#6366f1','#10b981','#f59e0b','#ef4444','#14b8a6','#8b5cf6','#22c55e','#64748b','#fb923c'],
    data: topMakers.value.map(x => x.total || 0)
  }]
}))

// ========== SAMBUTAN ==========
const pieSambutanFiles = computed(() => ({
  labels: ['Naskah','Tapping','Presentasi','Terjemahan'],
  datasets: [{
    backgroundColor: ['#f59e0b','#10b981','#6366f1','#ef4444'],
    data: [
      props.sambutanFilesByType?.naskah ?? 0,
      props.sambutanFilesByType?.tapping ?? 0,
      props.sambutanFilesByType?.presentasi ?? 0,
      props.sambutanFilesByType?.terjemahan ?? 0,
    ],
  }]
}))
const topUsers = computed(() => {
  const arr = Array.isArray(props.sambutanPerUser) ? [...props.sambutanPerUser] : []
  const top = arr.slice(0, 8)
  const sisa = arr.slice(8)
  const othersCount = sisa.reduce((a,b)=> a + (b.total||0), 0)
  if (othersCount > 0) top.push({ name: 'Lainnya', total: othersCount })
  return top
})
const pieSambutanPerUser = computed(() => ({
  labels: topUsers.value.map(x => x.name || '—'),
  datasets: [{
    backgroundColor: [
      '#f59e0b','#10b981','#6366f1','#ef4444',
      '#14b8a6','#8b5cf6','#ef7c00','#22c55e','#64748b'
    ],
    data: topUsers.value.map(x => x.total || 0),
  }]
}))

// ========== Permohonan (eksisting) ==========
const nf = new Intl.NumberFormat('id-ID')
const pieDataKategori = {
  labels: Object.keys(props.kategoriCounts),
  datasets: [{
    backgroundColor: ['#f59e0b', '#10b981', '#6366f1'],
    data: Object.values(props.kategoriCounts),
  }]
}
const pieDataStatus = {
  labels: Object.keys(props.statusCounts),
  datasets: [{
    backgroundColor: ['#facc15', '#3b82f6', '#22c55e', '#ef4444'],
    data: Object.values(props.statusCounts),
  }]
}
const doughnutDataPrioritas = {
  labels: Object.keys(props.prioritasCounts),
  datasets: [{
    backgroundColor: ['#dc2626', '#f59e0b', '#10b981'],
    data: Object.values(props.prioritasCounts),
  }]
}

// Palet & opsi chart
const palette = ['#22c55e', '#ef4444', '#3b82f6', '#f59e0b', '#10b981', '#6366f1']
const chartOptions = {
  responsive: true, maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom', labels: { usePointStyle: true } },
    tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${nf.format(ctx.parsed)}` } }
  }
}

// ===== Berita =====
const pieBeritaStatus = computed(() => ({
  labels: ['Publik', 'Private'],
  datasets: [{
    data: [props.beritaSummary.public || 0, props.beritaSummary.private || 0],
    backgroundColor: ['#22c55e', '#ef4444'],
  }]
}))
const pieTopPenulis = computed(() => {
  const top = (props.penulisStats || []).slice(0, 6)
  return {
    labels: top.map(p => p.name),
    datasets: [{
      data: top.map(p => p.jumlah),
      backgroundColor: palette.slice(0, top.length),
    }]
  }
})

// ========== SOSMED (BARU) ==========
const platformColors = ['#3b82f6','#22c55e','#ef4444','#f59e0b','#10b981','#6366f1','#8b5cf6','#14b8a6','#64748b']
const pieSosmedPlatform = computed(() => {
  const labels = Object.keys(props.sosmedByPlatform || {})
  const values = labels.map(l => props.sosmedByPlatform[l] || 0)
  return {
    labels,
    datasets: [{
      backgroundColor: platformColors.slice(0, labels.length),
      data: values
    }]
  }
})
const topSosmedCreators = computed(() => {
  const arr = Array.isArray(props.sosmedCreators) ? [...props.sosmedCreators] : []
  const top = arr.slice(0, 8)
  const rest = arr.slice(8)
  const others = rest.reduce((a,b)=> a + (b.total||0), 0)
  if (others > 0) top.push({ name: 'Lainnya', total: others })
  return top
})
const doughnutSosmedCreators = computed(() => ({
  labels: topSosmedCreators.value.map(x => x.name || '—'),
  datasets: [{
    backgroundColor: ['#6366f1','#10b981','#f59e0b','#ef4444','#14b8a6','#8b5cf6','#22c55e','#64748b','#fb923c'],
    data: topSosmedCreators.value.map(x => x.total || 0)
  }]
}))
</script>

<template>
  <Head title="Dashboard" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Dashboard</h2>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- 🔢 Highlight Card -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Total Permohonan -->
          <div class="flex items-center bg-orange-50 dark:bg-orange-900 border-l-4 border-orange-500 p-4 rounded shadow space-x-4">
            <FileText class="w-10 h-10 text-orange-500" />
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-300">Total Permohonan</p>
              <p class="text-2xl font-bold text-orange-600 dark:text-orange-300">{{ totalPermohonan }}</p>
            </div>
          </div>

          <!-- User Biasa -->
          <div class="flex items-center bg-blue-50 dark:bg-blue-900 border-l-4 border-blue-500 p-4 rounded shadow space-x-4">
            <Users class="w-10 h-10 text-blue-500" />
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-300">User Terdaftar</p>
              <p class="text-2xl font-bold text-blue-600 dark:text-blue-300">{{ jumlahUserBiasa }}</p>
            </div>
          </div>

          <!-- Prioritas Urgent -->
          <div class="flex items-center bg-red-50 dark:bg-red-900 border-l-4 border-red-500 p-4 rounded shadow space-x-4">
            <AlertTriangle class="w-10 h-10 text-red-500" />
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-300">Prioritas - Urgent</p>
              <p class="text-xl font-semibold text-red-600 dark:text-red-300">{{ prioritasCounts['Critical/Urgent'] ?? 0 }}</p>
            </div>
          </div>

          <!-- Prioritas Medium -->
          <div class="flex items-center bg-yellow-50 dark:bg-yellow-900 border-l-4 border-yellow-500 p-4 rounded shadow space-x-4">
            <Hourglass class="w-10 h-10 text-yellow-500" />
            <div>
              <p class="text-sm text-gray-500 dark:text-gray-300">Prioritas - Medium</p>
              <p class="text-xl font-semibold text-yellow-600 dark:text-yellow-300">{{ prioritasCounts['Medium'] ?? 0 }}</p>
            </div>
          </div>
        </div>

        <!-- 📊 Pie Charts -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div class="bg-white dark:bg-gray-800 p-6 shadow rounded">
            <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">Permohonan per Kategori</h3>
            <Pie :data="pieDataKategori" />
          </div>

          <div class="bg-white dark:bg-gray-800 p-6 shadow rounded">
            <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">Permohonan per Status</h3>
            <Pie :data="pieDataStatus" />
          </div>

          <div class="bg-white dark:bg-gray-800 p-6 shadow rounded">
            <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">Permohonan Berdasarkan Prioritas</h3>
            <Doughnut :data="doughnutDataPrioritas" />
          </div>
        </div>

        <!-- 👥 Staff Statistics -->
        <div class="bg-white dark:bg-gray-800 p-6 shadow rounded">
          <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">Statistik Staff</h3>
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-700 dark:text-gray-200">
              <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                <tr>
                  <th class="py-2 px-4">Nama Staff</th>
                  <th class="py-2 px-4 text-center">Disposisi</th>
                  <th class="py-2 px-4 text-center">Diajukan</th>
                  <th class="py-2 px-4 text-center">Diproses</th>
                  <th class="py-2 px-4 text-center">Selesai</th>
                  <th class="py-2 px-4 text-center">Ditolak</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="staff in staffStats" :key="staff.id" class="border-b border-gray-200 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-center">
                  <td class="py-2 px-4 text-left">{{ staff.name }}</td>
                  <td class="py-2 px-4">{{ staff.total_disposisi }}</td>
                  <td class="py-2 px-4">{{ staff.diajukan_count }}</td>
                  <td class="py-2 px-4">{{ staff.diproses_count }}</td>
                  <td class="py-2 px-4">{{ staff.selesai_count }}</td>
                  <td class="py-2 px-4">{{ staff.ditolak_count }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- 📚 Statistik Berita -->
        <div class="space-y-6">
          <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Statistik Berita</h3>

          <!-- KPI Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-indigo-500">
              <p class="text-sm text-gray-500 dark:text-gray-300">Total Berita</p>
              <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-300">{{ nf.format(beritaSummary.total) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-emerald-500">
              <p class="text-sm text-gray-500 dark:text-gray-300">Berita Publik</p>
              <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-300">{{ nf.format(beritaSummary.public) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-rose-500">
              <p class="text-sm text-gray-500 dark:text-gray-300">Berita Private</p>
              <p class="text-2xl font-bold text-rose-600 dark:text-rose-300">{{ nf.format(beritaSummary.private) }}</p>
            </div>
          </div>

          <!-- Charts + Tables -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Pie status -->
            <div class="bg-white dark:bg-gray-800 p-6 shadow rounded">
              <h4 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Status Berita</h4>
              <div class="relative h-64">
                <Pie :data="pieBeritaStatus" :options="chartOptions" />
              </div>
            </div>

            <!-- Pie top penulis -->
            <div class="bg-white dark:bg-gray-800 p-6 shadow rounded">
              <h4 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Top Penulis (6 Teratas)</h4>
              <div class="relative h-64">
                <Pie :data="pieTopPenulis" :options="chartOptions" />
              </div>
            </div>

            <!-- Ringkasan kontributor -->
            <div class="bg-white dark:bg-gray-800 p-6 shadow rounded">
              <h4 class="text-lg font-semibold mb-1 text-gray-800 dark:text-gray-100">Kontributor</h4>
              <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">
                Total kontributor : <span class="font-semibold">{{ nf.format(kontributorUnique) }}</span>
              </p>
              <div class="max-h-64 overflow-auto border border-gray-200 dark:border-gray-700 rounded">
                <table class="w-full text-sm">
                  <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                    <tr>
                      <th class="py-2 px-3 text-left">Nama</th>
                      <th class="py-2 px-3 text-right">Terlibat</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="k in kontributorStats" :key="k.id" class="border-b border-gray-200 dark:border-gray-700">
                      <td class="py-2 px-3">{{ k.name }}</td>
                      <td class="py-2 px-3 text-right">{{ nf.format(k.jumlah) }}</td>
                    </tr>
                    <tr v-if="!kontributorStats?.length">
                      <td colspan="2" class="py-3 px-3 text-center text-gray-500 dark:text-gray-400">Belum ada data.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Tabel Penulis -->
          <div class="bg-white dark:bg-gray-800 p-6 shadow rounded">
            <h4 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Daftar Penulis & Jumlah Berita</h4>
            <div class="overflow-x-auto">
              <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                  <tr>
                    <th class="py-2 px-3">Penulis</th>
                    <th class="py-2 px-3 text-right">Jumlah</th>
                    <th class="py-2 px-3 text-right">Persentase</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="p in penulisStats" :key="p.id" class="border-b border-gray-200 dark:border-gray-700">
                    <td class="py-2 px-3">{{ p.name }}</td>
                    <td class="py-2 px-3 text-right">{{ nf.format(p.jumlah) }}</td>
                    <td class="py-2 px-3 text-right">
                      {{ beritaSummary.total ? ((p.jumlah / beritaSummary.total) * 100).toFixed(1) : '0.0' }}%
                    </td>
                  </tr>
                  <tr v-if="!penulisStats?.length">
                    <td colspan="3" class="py-4 text-center text-gray-500 dark:text-gray-400">Belum ada data.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ======== SAMBUTAN ======== -->
        <div class="space-y-6">
          <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Statistik Sambutan</h3>
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Kartu ringkasan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:col-span-1">
              <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border border-gray-100 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-300">Total Sambutan</p>
                <p class="text-2xl font-bold text-orange-600 dark:text-orange-300">{{ props.sambutanTotal ?? 0 }}</p>
              </div>
              <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border border-gray-100 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-300">Total File Sambutan</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-300">{{ props.sambutanFilesTotal ?? 0 }}</p>
              </div>
              <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border border-gray-100 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-300">Naskah</p>
                <p class="text-xl font-semibold text-gray-800 dark:text-gray-100">{{ props.sambutanFilesByType?.naskah ?? 0 }}</p>
              </div>
              <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border border-gray-100 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-300">Tapping</p>
                <p class="text-xl font-semibold text-gray-800 dark:text-gray-100">{{ props.sambutanFilesByType?.tapping ?? 0 }}</p>
              </div>
              <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border border-gray-100 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-300">Presentasi</p>
                <p class="text-xl font-semibold text-gray-800 dark:text-gray-100">{{ props.sambutanFilesByType?.presentasi ?? 0 }}</p>
              </div>
              <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border border-gray-100 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-300">Terjemahan</p>
                <p class="text-xl font-semibold text-gray-800 dark:text-gray-100">{{ props.sambutanFilesByType?.terjemahan ?? 0 }}</p>
              </div>
            </div>

            <!-- Pie: Files per type -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded shadow border border-gray-100 dark:border-gray-700">
              <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">File Sambutan per Jenis</h3>
              <Pie :data="pieSambutanFiles" />
            </div>

            <!-- Pie: Sambutan per User -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded shadow border border-gray-100 dark:border-gray-700">
              <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">Sambutan per Pembuat (Top)</h3>
              <Doughnut :data="pieSambutanPerUser" />
            </div>
          </div>

          <!-- Tabel Pembuat -->
          <div class="bg-white dark:bg-gray-800 p-6 shadow rounded border border-gray-100 dark:border-gray-700 mt-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">Pembuat Sambutan & Jumlah yang Dibuat</h3>
            <div class="overflow-x-auto">
              <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                  <tr>
                    <th class="py-2 px-4 w-16">No</th>
                    <th class="py-2 px-4">Nama</th>
                    <th class="py-2 px-4 text-right">Total Sambutan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(row, i) in (props.sambutanPerUser ?? [])"
                    :key="row.user_id ?? i"
                    class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
                  >
                    <td class="py-2 px-4">{{ i + 1 }}</td>
                    <td class="py-2 px-4">{{ row.name || '—' }}</td>
                    <td class="py-2 px-4 text-right font-semibold">{{ row.total }}</td>
                  </tr>
                  <tr v-if="!props.sambutanPerUser || props.sambutanPerUser.length === 0">
                    <td colspan="3" class="py-4 text-center text-gray-500 dark:text-gray-400">Belum ada data.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ======== KEGIATAN ======== -->
     <div class="space-y-6">
  <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Statistik Kegiatan</h3>

  <!-- Ringkasan -->
  <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
    <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-indigo-500">
      <p class="text-sm text-gray-500 dark:text-gray-300">Total Kegiatan</p>
      <p class="text-2xl font-bold text-orange-600 dark:text-orange-300">{{ props.kegiatanTotal ?? 0 }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-emerald-500">
      <p class="text-sm text-gray-500 dark:text-gray-300">Total File Terunggah</p>
      <p class="text-2xl font-bold text-blue-600 dark:text-blue-300">{{ props.kegiatanFilesTotal ?? 0 }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-amber-500">
      <p class="text-sm text-gray-500 dark:text-gray-300">Publik / Private</p>
      <p class="text-xl font-semibold text-gray-800 dark:text-gray-100">
        {{ (props.kegiatanByStatus?.public ?? 0) }} / {{ (props.kegiatanByStatus?.private ?? 0) }}
      </p>
    </div>

    <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-purple-500">
      <p class="text-sm text-gray-500 dark:text-gray-300">Total File Terunduh</p>

      <p class="text-xl font-semibold text-gray-800 dark:text-gray-100">
        {{ nf.format(props.kegiatanDownloadsTotal ?? 0) }}
      </p>
    </div>
  </div>

  <!-- Satu baris / dua kolom: Tabel + Doughnut -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
    <!-- Tabel Pembuat -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded shadow border border-gray-100 dark:border-gray-700">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Pembuat Kegiatan & Jumlah</h3>
        <span class="text-xs text-gray-500 dark:text-gray-400">Total pembuat: {{ props.kegiatanCreatorUnique ?? 0 }}</span>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
            <tr>
              <th class="py-2 px-4 w-16">No</th>
              <th class="py-2 px-4">Nama</th>
              <th class="py-2 px-4 text-right">Total Kegiatan</th>
              <th class="py-2 px-4 text-right">Jumlah Download</th>
              <th class="py-2 px-4 text-right">Persentase</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(row, i) in (props.kegiatanCreators ?? [])"
              :key="row.user_id ?? i"
              class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <td class="py-2 px-4">{{ i + 1 }}</td>
              <td class="py-2 px-4">{{ row.name || '—' }}</td>
              <td class="py-2 px-4 text-right font-semibold">{{ nf.format(row.total) }}</td>
              <td class="py-2 px-4 text-right">{{ nf.format(row.jumlah_download || 0) }}</td>
              <td class="py-2 px-4 text-right">
                {{
                  (props.kegiatanTotal && props.kegiatanTotal > 0)
                    ? ((row.total / props.kegiatanTotal) * 100).toFixed(1)
                    : '0.0'
                }}%
              </td>
            </tr>
            <tr v-if="!props.kegiatanCreators || props.kegiatanCreators.length === 0">
              <td colspan="5" class="py-4 text-center text-gray-500 dark:text-gray-400">Belum ada data.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Doughnut Top Pembuat -->
<div class="bg-white dark:bg-gray-800 p-6 rounded shadow border border-gray-100 dark:border-gray-700">
  <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">Top Pembuat Kegiatan</h3>

  <!-- kontainer tengah -->
  <div class="h-72 grid place-items-center">
    <!-- kotak responsif untuk kanvas -->
    <div class="w-56 sm:w-64 md:w-72 aspect-square">
      <Doughnut
        :data="doughnutKegiatanMakers"
        :options="{
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { position: 'bottom' } }
        }"
      />
    </div>
  </div>
</div>

    
  </div>
</div>


        <!-- ======== POST SOSIAL MEDIA (BARU) ======== -->
        <div class="space-y-6">
          <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Statistik Post Sosial Media</h3>

          <!-- KPI Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-indigo-500">
              <p class="text-sm text-gray-500 dark:text-gray-300">Total Post</p>
              <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-300">{{ nf.format(sosmedTotal || 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-emerald-500">
              <p class="text-sm text-gray-500 dark:text-gray-300">Total Pembuat</p>
              <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-300">{{ nf.format(sosmedCreatorUnique || 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 p-4 rounded shadow border-l-4 border-amber-500">
              <p class="text-sm text-gray-500 dark:text-gray-300">Jumlah Platform</p>
              <p class="text-2xl font-bold text-amber-600 dark:text-amber-300">
                {{ Object.keys(sosmedByPlatform || {}).length }}
              </p>
            </div>
          </div>

          <!-- Charts + Table -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Pie Platform -->
            <div class="bg-white dark:bg-gray-800 p-6 shadow rounded">
              <h4 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Distribusi Platform</h4>
              <div class="relative h-64">
                <Pie :data="pieSosmedPlatform" :options="chartOptions" />
              </div>
            </div>

            <!-- Doughnut Top Pembuat -->
            <div class="bg-white dark:bg-gray-800 p-6 shadow rounded">
              <h4 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Top Pembuat (8 Teratas)</h4>
              <div class="relative h-64">
                <Doughnut :data="doughnutSosmedCreators" :options="chartOptions" />
              </div>
            </div>

            <!-- Tabel Pembuat -->
            <div class="bg-white dark:bg-gray-800 p-6 shadow rounded">
              <h4 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-100">Daftar Pembuat</h4>
              <div class="max-h-64 overflow-auto border border-gray-200 dark:border-gray-700 rounded">
                <table class="w-full text-sm">
                  <thead class="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200">
                    <tr>
                      <th class="py-2 px-3 text-left">Nama</th>
                      <th class="py-2 px-3 text-right">Total</th>
                      <th class="py-2 px-3 text-right">Persentase</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="c in sosmedCreators" :key="c.user_id" class="border-b border-gray-200 dark:border-gray-700">
                      <td class="py-2 px-3">{{ c.name || '—' }}</td>
                      <td class="py-2 px-3 text-right">{{ nf.format(c.total || 0) }}</td>
                      <td class="py-2 px-3 text-right">
                        {{
                          (sosmedTotal && sosmedTotal > 0)
                            ? ((c.total / sosmedTotal) * 100).toFixed(1)
                            : '0.0'
                        }}%
                      </td>
                    </tr>
                    <tr v-if="!sosmedCreators?.length">
                      <td colspan="3" class="py-3 px-3 text-center text-gray-500 dark:text-gray-400">Belum ada data.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <!-- ======== /POST SOSMED ======== -->

      </div>
    </div>
  </AuthenticatedLayout>
</template>
