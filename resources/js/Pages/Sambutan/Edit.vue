<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import { ref, watch, computed, onMounted } from 'vue'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

const props = defineProps({
  sambutan: Object,
  users: Array,
})

const userOptions = computed(() =>
  props.users.map(user => ({
    value: user.id,
    label: user.name,
  }))
)

const form = useForm({
  judul: props.sambutan.judul,
  tanggal_dibuat: props.sambutan.tanggal_dibuat,
  isi_sambutan: props.sambutan.isi_sambutan,
  kontributor_id: props.sambutan.kontributor_id || [],
  file: null,
  is_public: !!props.sambutan.is_public,
})

const trixInput = ref(null)
const existingFile = ref(props.sambutan.file || null)

const page = usePage()
watch(() => page.props?.flash?.success, (message) => {
  if (message) toast(message)
})

// Sync isi_sambutan dengan Trix
onMounted(() => {
  const editor = document.querySelector('trix-editor')
  editor?.addEventListener('trix-change', () => {
    form.isi_sambutan = trixInput.value.value
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

const submitted = ref(false)

const submit = () => {
  submitted.value = true

  const editor = document.querySelector('trix-editor')
  if (editor) {
    form.isi_sambutan = editor.editor.getDocument().toString()
  }

  const formData = new FormData()
  formData.append('_method', 'PUT') // <== Penting banget
  formData.append('judul', form.judul || '')
  formData.append('tanggal_dibuat', form.tanggal_dibuat || '')
  formData.append('isi_sambutan', form.isi_sambutan || '')
  formData.append('is_public', form.is_public ? 1 : 0)

  if (form.kontributor_id && form.kontributor_id.length) {
    form.kontributor_id.forEach((id, i) => {
      formData.append(`kontributor_id[${i}]`, id)
    })
  }

  if (form.file) {
    formData.append('file', form.file)
  }

router.post(route('sambutan.update', props.sambutan.id), formData, {
  method: 'put', // ✅ PAKAI POST + _method = PUT → Laravel akan update
  forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      submitted.value = false
    },
    onError: (errors) => {
      console.error('Validation errors:', errors)
    }
  })
}

function toast(msg) {
  const toast = document.createElement('div')
  toast.textContent = msg
  toast.className = 'fixed top-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow z-50'
  document.body.appendChild(toast)
  setTimeout(() => document.body.removeChild(toast), 3000)
}
</script>

<template>
  <Head title="Edit Sambutan" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Edit Sambutan</h2>
    </template>

       <!-- Tombol Kembali -->
   

    <div class="py-6 max-w-4xl mx-auto">
         <a href="/sambutan"
        class="inline-flex items-center mb-4 text-sm text-orange-600 hover:underline"
      >
        <ArrowLeft class="w-4 h-4 mr-1" />  ← Kembali ke Daftar Sambutan
    </a>
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <form @submit.prevent="submit" class="space-y-5">
          <!-- Judul -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Judul</label>
            <input type="text" v-model="form.judul"
              class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" />
            <div v-if="form.errors.judul" class="text-red-500 text-sm mt-1">{{ form.errors.judul }}</div>
          </div>

          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Tanggal Dibuat</label>
            <input type="date" v-model="form.tanggal_dibuat"
              class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />
          </div>

          <!-- Isi Sambutan -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Isi Sambutan</label>
              <input
  id="isisambutanTrix"
  type="hidden"
  name="isi_sambutan"
  ref="trixInput"
  :value="form.isi_sambutan"
/>
            <trix-editor
                input="isisambutanTrix"
                class="trix-content border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
                @trix-change="updateSore"
            />
            <div v-if="form.errors.isi_sambutan" class="text-red-500 text-sm mt-1">{{ form.errors.isi_sambutan }}</div>
          </div>

          <!-- Kontributor Multiselect -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Kontributor</label>
            <Multiselect
              v-model="form.kontributor_id"
              :options="userOptions"
              mode="tags"
              placeholder="Pilih Kontributor Lainnya..."
              :searchable="true"
              :clearable="true"
              class="w-full dark:text-gray-700"
            />
            <div v-if="form.errors.kontributor_id" class="text-red-500 text-sm mt-1">{{ form.errors.kontributor_id }}</div>
          </div>

          <!-- File Upload -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Upload File (Opsional)</label>
            <input type="file" accept=".pdf,.doc,.docx,.txt" @change="handleFileChange"
              class="w-full text-gray-700 dark:text-gray-200 file:bg-orange-500 file:text-white file:rounded file:px-3 file:py-1 file:border-none file:hover:bg-orange-600" />
            <div v-if="form.errors.file" class="text-red-500 text-sm mt-1">{{ form.errors.file }}</div>
            <div v-if="existingFile" class="text-sm text-gray-600 dark:text-gray-300 mt-2">
              File saat ini: <a :href="`/storage/${existingFile}`" target="_blank" class="underline text-blue-500">{{ existingFile }}</a>
            </div>
          </div>

          <!-- Is Public -->
          <div class="flex items-center gap-2">
            <input type="checkbox" v-model="form.is_public" />
            <label class="text-gray-700 dark:text-gray-200">Tampilkan di Halaman Publik</label>
          </div>

          <!-- Submit -->
          <div>
            <button type="submit"
              class="block w-full bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 dark:hover:bg-orange-400 transition-colors text-center">
              Perbarui Sambutan
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
