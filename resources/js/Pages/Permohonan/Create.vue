<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'

defineProps({
  staffList: Array,
  user: {
    type: Object,
    default: () => ({}),
  },
})

const form = useForm({
  kategori: '',
  title: '',
  description: '',
  priority: '',
  file: null,
  disposisi: '',
})

const trixInput = ref(null)

onMounted(() => {
  const editor = document.querySelector('trix-editor')
  editor?.addEventListener('trix-change', () => {
    form.description = trixInput.value.value
  })
})

function handleFileChange(e) {
  const file = e.target.files[0]
  const allowedTypes = [
    'application/pdf',
    'application/msword', // .doc
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
    'text/plain' // .txt
  ]

  if (file && !allowedTypes.includes(file.type)) {
    alert('Hanya file PDF, Word (.doc, .docx), dan TXT yang diperbolehkan.')
    e.target.value = '' // reset input
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

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Ajukan Permohonan</h2>
    </template>

    
    <div class="py-6 max-w-4xl mx-auto">
      <!-- Tombol Kembali -->
<div class="mt-6 mb-6 text-right">
  <a href="/permohonan"
     class="inline-block bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 px-4 py-2 rounded hover:bg-gray-400 dark:hover:bg-gray-600 transition">
    ← Kembali ke Daftar Permohonan
  </a>
</div>

      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-5">

          <!-- Title -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Judul Permohonan</label>
            <input type="text" v-model="form.title"
              class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />
          </div>

          <!-- Kategori -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Kategori</label>
            <select v-model="form.kategori"
              class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
              <option value="" disabled>Pilih Kategori Permohonan</option>
              <option value="Sambutan">Sambutan</option>
              <option value="Dokumentasi">Dokumentasi</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>

          <!-- Prioritas -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Prioritas</label>
            <select v-model="form.priority"
              class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required>
              <option value="" disabled>Pilih Prioritas</option>
              <option value="Critical/Urgent">Critical/Urgent</option>
              <option value="Medium">Medium</option>
              <option value="Low">Low</option>
            </select>
          </div>

          <!-- Disposisi -->
          <div v-if="user && user.role === 'SUPERADMIN'">
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Disposisikan ke Staff</label>
            <select v-model="form.disposisi"
              class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100">
              <option value="" disabled>Pilih Staff</option>
              <option v-for="staff in staffList" :key="staff.id" :value="staff.id">{{ staff.name }}</option>
            </select>
          </div>

          <!-- Deskripsi -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Deskripsi</label>
            <input id="x" type="hidden" v-model="form.description" ref="trixInput" />
            <trix-editor input="x"
              class="trix-content bg-white dark:bg-gray-900 border dark:border-gray-700 rounded p-2 dark:text-gray-100"></trix-editor>
          </div>

          <!-- Upload File -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Upload Pointer/File (Opsional, Max 10MB)</label>
            <input type="file" accept=".pdf,.doc,.docx,.txt" @change="handleFileChange"
              class="w-full text-gray-700 dark:text-gray-200 file:bg-orange-500 file:text-white file:rounded file:px-3 file:py-1 file:border-none file:hover:bg-orange-600" />
          </div>

          <!-- Submit -->
          <div>
           <button type="submit"
            class="block w-full bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 dark:hover:bg-orange-400 transition-colors text-center">
            Kirim Permohonan
          </button>

          </div>

        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.trix-content {
  min-height: 150px;
}
</style>
