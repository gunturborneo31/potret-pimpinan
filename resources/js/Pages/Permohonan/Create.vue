<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'

const props = defineProps({
  staffList: Array,
  user: {
    type: Object,
    default: null,
  },
})

const isAuthenticated = computed(() => Boolean(props.user?.id))
const backLink = computed(() => (isAuthenticated.value ? '/permohonan' : '/'))
const backText = computed(() => (isAuthenticated.value ? '← Kembali ke Daftar Permohonan' : '← Kembali ke Beranda'))
const isDarkMode = ref(false)

const form = useForm({
  nama_pemohon: props.user?.name ?? '',
  email_pemohon: props.user?.email ?? '',
  no_hp_pemohon: props.user?.phone ?? '',
  instansi_pemohon: '',
  kategori: '',
  title: '',
  description: '',
  priority: '',
  file: null,
  disposisi: '',
})

const trixInput = ref(null)

onMounted(() => {
  isDarkMode.value = localStorage.getItem('theme') === 'dark'
  document.documentElement.classList.toggle('dark', isDarkMode.value)

  const editor = document.querySelector('trix-editor')
  editor?.addEventListener('trix-change', () => {
    form.description = trixInput.value.value
  })
})

function toggleDarkMode() {
  isDarkMode.value = !isDarkMode.value
  localStorage.setItem('theme', isDarkMode.value ? 'dark' : 'light')
  document.documentElement.classList.toggle('dark', isDarkMode.value)
}

function handleFileChange(e) {
  const file = e.target.files[0]
  const allowedTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'text/plain',
  ]

  if (file && !allowedTypes.includes(file.type)) {
    alert('Hanya file PDF, Word (.doc, .docx), dan TXT yang diperbolehkan.')
    e.target.value = ''
    form.file = null
    return
  }

  form.file = file
}

function submit() {
  form.post('/permohonan', { forceFormData: true })
}
</script>

<template>
  <Head title="Ajukan Permohonan" />

  <div :class="{ dark: isDarkMode }">
    <div class="min-h-screen bg-white dark:bg-gray-900 text-gray-800 dark:text-white">
      <nav class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white px-4 py-4 shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-3">
          <a href="/" class="flex items-center gap-2">
            <img src="/img/logo-only.png" alt="Logo" class="h-8 w-8 object-contain" />
            <span class="font-bold text-xl">Layanan Komunikasi dan Dokumentasi  Pimpinan</span>
          </a>
          <div class="flex items-center gap-4 text-sm">
            <a href="/" class="hover:underline">Beranda</a>
            <a href="/list-kegiatan" class="hover:underline">Kegiatan</a>
            <a href="/permohonan/create" class="text-orange-600 hover:underline">Permohonan</a>
            <a v-if="!isAuthenticated" href="/login" class="hover:underline">Login</a>
            <a v-if="!isAuthenticated" href="/register" class="hover:underline">Register</a>
            <a v-else href="/dashboard" class="hover:underline">Dashboard</a>
            <button @click="toggleDarkMode" class="border px-3 py-1 rounded hover:bg-gray-100 dark:hover:bg-gray-700">
              {{ isDarkMode ? '☀️' : '🌙' }}
            </button>
          </div>
        </div>
      </nav>

      <div class="py-6 max-w-4xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-center text-gray-800 dark:text-white mb-6">Ajukan Permohonan</h2>

        <div class="mt-6 mb-6 text-right">
          <a
            :href="backLink"
            class="inline-block bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 px-4 py-2 rounded hover:bg-gray-400 dark:hover:bg-gray-600 transition"
          >
            {{ backText }}
          </a>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Nama Pemohon</label>
              <input
                v-model="form.nama_pemohon"
                type="text"
                class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                required
              />
              <p v-if="form.errors.nama_pemohon" class="mt-1 text-sm text-red-500">{{ form.errors.nama_pemohon }}</p>
            </div>

            <div>
              <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Email Pemohon</label>
              <input
                v-model="form.email_pemohon"
                type="email"
                class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                required
              />
              <p v-if="form.errors.email_pemohon" class="mt-1 text-sm text-red-500">{{ form.errors.email_pemohon }}</p>
            </div>

            <div>
              <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">No. HP/WhatsApp</label>
              <input
                v-model="form.no_hp_pemohon"
                type="text"
                class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                required
              />
              <p v-if="form.errors.no_hp_pemohon" class="mt-1 text-sm text-red-500">{{ form.errors.no_hp_pemohon }}</p>
            </div>

            <div>
              <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Instansi/Asal</label>
              <input
                v-model="form.instansi_pemohon"
                type="text"
                class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                required
              />
              <p v-if="form.errors.instansi_pemohon" class="mt-1 text-sm text-red-500">{{ form.errors.instansi_pemohon }}</p>
            </div>
          </div>

          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Judul Permohonan</label>
            <input
              type="text"
              v-model="form.title"
              class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
              required
            />
            <p v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</p>
          </div>

          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Kategori</label>
            <select
              v-model="form.kategori"
              class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
              required
            >
              <option value="" disabled>Pilih Kategori Permohonan</option>
              <option value="Sambutan">Sambutan</option>
              <option value="Dokumentasi">Dokumentasi</option>
              <option value="Lainnya">Lainnya</option>
            </select>
            <p v-if="form.errors.kategori" class="mt-1 text-sm text-red-500">{{ form.errors.kategori }}</p>
          </div>

          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Prioritas</label>
            <select
              v-model="form.priority"
              class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
              required
            >
              <option value="" disabled>Pilih Prioritas</option>
              <option value="Critical/Urgent">Critical/Urgent</option>
              <option value="Medium">Medium</option>
              <option value="Low">Low</option>
            </select>
            <p v-if="form.errors.priority" class="mt-1 text-sm text-red-500">{{ form.errors.priority }}</p>
          </div>

          <div v-if="user && user.role === 'SUPERADMIN'">
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Disposisikan ke Staff</label>
            <select
              v-model="form.disposisi"
              class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
            >
              <option value="" disabled>Pilih Staff</option>
              <option v-for="staff in staffList" :key="staff.id" :value="staff.id">{{ staff.name }}</option>
            </select>
          </div>

          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Deskripsi</label>
            <input id="x" type="hidden" v-model="form.description" ref="trixInput" />
            <trix-editor
              input="x"
              class="trix-content bg-white dark:bg-gray-900 border dark:border-gray-700 rounded p-2 dark:text-gray-100"
            />
            <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
          </div>

          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Upload Pointer/File (Opsional, Max 10MB)</label>
            <input
              type="file"
              accept=".pdf,.doc,.docx,.txt"
              @change="handleFileChange"
              class="w-full text-gray-700 dark:text-gray-200 file:bg-orange-500 file:text-white file:rounded file:px-3 file:py-1 file:border-none file:hover:bg-orange-600"
            />
            <p v-if="form.errors.file" class="mt-1 text-sm text-red-500">{{ form.errors.file }}</p>
          </div>

          <div>
            <button
              type="submit"
              class="block w-full bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 dark:hover:bg-orange-400 transition-colors text-center"
              :disabled="form.processing"
            >
              Kirim Permohonan
            </button>
          </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.trix-content {
  min-height: 150px;
}
</style>
