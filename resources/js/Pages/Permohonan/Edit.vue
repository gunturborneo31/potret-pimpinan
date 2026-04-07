<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import Dropzone from 'dropzone'

// Props
const props = defineProps({
  permohonan: Object,
  staffList: Array,
  user: Object,
})

// const permohonan = ref({
//   ...props.permohonan,
//   status: props.permohonan.status
// })

const permohonan = computed(() => props.permohonan)

// Form edit permohonan
const form = useForm({
  id: '',
  title: '',
  kategori: '',
  priority: '',
  description: '',
  disposisi: '',
})

// Form komentar
const komentar = useForm({
  komentar: '',
  file: null,
})

const toastMessage = ref(null)

const trixInput = ref(null)
const dropzoneElement = ref(null)
let dz = null

function showToast(msg) {
  toastMessage.value = msg
  setTimeout(() => {
    toastMessage.value = null
  }, 3000)
}

// Load data dan init editor
onMounted(() => {
  form.id = props.permohonan.id
  form.title = props.permohonan.title
  form.kategori = props.permohonan.kategori
  form.priority = props.permohonan.priority
  form.description = props.permohonan.description
  form.disposisi = props.permohonan.disposisi

  setTimeout(() => {
    trixInput.value.value = form.description
    const editor = document.querySelector('trix-editor')
    editor?.editor.loadHTML(form.description)
    editor?.addEventListener('trix-change', () => {
      form.description = trixInput.value.value
    })
  }, 100)

  // ✅ Init Dropzone
  dz = new Dropzone(dropzoneElement.value, {
    url: '#', // Tidak digunakan karena kita pakai Inertia
    autoProcessQueue: false,
    maxFilesize: 10,
    maxFiles: 1,
    acceptedFiles: '.pdf,.doc,.docx,.jpg,.jpeg,.png',
    addRemoveLinks: true,
    dictDefaultMessage: 'Seret dan lepas berkas di sini atau klik untuk memilih',
  })

  dz.on('addedfile', file => {
    komentar.file = file
  })

  dz.on('removedfile', () => {
    komentar.file = null
  })

    document.addEventListener('trix-file-accept', function (event) {
    event.preventDefault(); // ini akan men-disable fitur file upload di trix editor
    showToast('Upload file dinonaktifkan. Silahkan upload file dikomentar.');
  })
})

onBeforeUnmount(() => {
  dz?.destroy?.()
})

function submit() {
  form.put(`/permohonan/${form.id}`, {
    onSuccess: () => {
      console.log('Permohonan berhasil diperbarui')
    },
    onError: err => console.error(err),
  })
}

function submitKomentar() {
  const formData = new FormData()
  formData.append('komentar', komentar.komentar)
  if (komentar.file) formData.append('file', komentar.file)

 router.post(`/permohonan/${props.permohonan.id}/komentar`, formData, {
  forceFormData: true,
  preserveScroll: true,
  onSuccess: () => {
    komentar.reset('komentar', 'file')
    dz.removeAllFiles()
    document.querySelector('trix-editor')?.editor.loadHTML('')
    showToast('Komentar berhasil dikirim!')
  },
  onError: err => {
    console.error(err)
    showToast('Komentar gagal dikirim!')
  }
})
}


function deleteKomentar(id) {
  if (confirm('Yakin ingin menghapus komentar ini?')) {
    router.delete(`/komentar/${id}`, {
      onSuccess: () => {
        props.permohonan.komentars = props.permohonan.komentars.filter(k => k.id !== id)
        showToast("Komentar terhapus!")

      },
      onError: () => {
        alert('Gagal menghapus komentar.')
      }
    })
  }
}

function updateStatus(status) {
  router.put(`/permohonan/${props.permohonan.id}/update-status`, {
    status,
  }, {
    onSuccess: () => {
      props.permohonan.status = status
      showToast(`Status diubah menjadi ${status}`)
    },
    onError: () => {
      showToast('Gagal mengubah status.')
    }
  })
}

function ubahStatus(statusBaru) {
  router.put(`/permohonan/${props.permohonan.id}/update-status`, {
    status: statusBaru
  }, {
    preserveScroll: true,
    onSuccess: () => {
      showToast(`Status diubah menjadi ${statusBaru}`)
      // Optional: reload agar status dari backend terbaru
      router.reload({ only: ['permohonan'] })
    },
    onError: () => {
      showToast('Gagal mengubah status')
    }
  })
}

function updateKomentar(event) {
  komentar.komentar = event.target.innerHTML
}


</script>


<template>
<transition name="fade">
  <div
    v-if="toastMessage"
    class="fixed bottom-5 right-5 bg-green-500 text-white px-4 py-2 rounded shadow z-50"
  >
    {{ toastMessage }}
  </div>
</transition>

  <Head title="Edit Permohonan" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-bold text-gray-800">Edit Permohonan</h2>
    </template>

    <div class="py-6">
      
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
              <!-- Tombol Kembali -->
<div class="mt-6 mb-6 text-right">
  <a href="/permohonan"
     class="inline-block bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 px-4 py-2 rounded hover:bg-gray-400 dark:hover:bg-gray-600 transition">
    ← Kembali ke Daftar Permohonan
  </a>
</div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

          <!-- 📝 Kolom Kiri: Form Edit -->
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <!-- Status Buttons -->
      
    <div class="flex flex-wrap gap-2 mb-6" v-if="props.user.role === 'SUPERADMIN' || props.user.role === 'STAFF'">
      
      <button
        v-for="(icon, status) in {
          Diajukan: '📤',
          Diproses: '⚙️',
          Selesai: '✅',
          Ditolak: '❌'
        }"
        :key="status"
        @click="ubahStatus(status)"
        :class="[
          'flex items-center gap-2 px-2 py-1 rounded-full text-sm font-semibold border transition',
          props.permohonan?.status === status
            ? {
                Diajukan: 'bg-yellow-100 text-yellow-800 border-yellow-500 dark:bg-yellow-900 dark:text-yellow-200 dark:border-yellow-400',
                Diproses: 'bg-blue-100 text-blue-800 border-blue-500 dark:bg-blue-900 dark:text-blue-200 dark:border-blue-400',
                Selesai: 'bg-green-100 text-green-800 border-green-500 dark:bg-green-900 dark:text-green-200 dark:border-green-400',
                Ditolak: 'bg-red-100 text-red-800 border-red-500 dark:bg-red-900 dark:text-red-200 dark:border-red-400',
              }[status]
            : 'bg-gray-50 text-gray-600 border-gray-300 hover:bg-gray-100 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600'
        ]"
      >
        <span>{{ icon }}</span>
        <span>{{ status }}</span>
      </button>
    </div>

    <!-- Current Status -->
    <p class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-300">
      Status saat ini:
      <span
        :class="{
          'text-blue-600 dark:text-blue-300': props.permohonan?.status === 'Diajukan',
          'text-yellow-600 dark:text-yellow-300': props.permohonan?.status === 'Diproses',
          'text-green-600 dark:text-green-300': props.permohonan?.status === 'Selesai',
          'text-red-600 dark:text-red-300': props.permohonan?.status === 'Ditolak'
        }"
      >
        {{ props.permohonan?.status || 'Tidak Diketahui' }}
      </span>
    </p>

    <!-- Form -->
    <form @submit.prevent="submit" class="space-y-5">
      <!-- Judul -->
      <div>
        <label class="block font-semibold mb-1 text-gray-800 dark:text-gray-200">Judul Permohonan</label>
        <input
          type="text"
          v-model="form.title"
          class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
          required
        />
      </div>

      <!-- Kategori -->
      <div>
        <label class="block font-semibold mb-1 text-gray-800 dark:text-gray-200">Kategori</label>
        <select
          v-model="form.kategori"
          class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
          required
        >
          <option value="Sambutan">Sambutan</option>
          <option value="Dokumentasi">Dokumentasi</option>
          <option value="Lainnya">Lainnya</option>
        </select>
      </div>

      <!-- Prioritas -->
      <div>
        <label class="block font-semibold mb-1 text-gray-800 dark:text-gray-200">Prioritas</label>
        <select
          v-model="form.priority"
          class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
          required
        >
          <option value="Critical/Urgent">Critical/Urgent</option>
          <option value="Medium">Medium</option>
          <option value="Low">Low</option>
        </select>
      </div>

      <!-- Disposisi -->
      <div v-if="props.user.role === 'SUPERADMIN'">
        <label class="block font-semibold mb-1 text-gray-800 dark:text-gray-200">Disposisikan ke Staff</label>
        <select
          v-model="form.disposisi"
          class="w-full border border-gray-300 dark:border-gray-600 rounded p-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
        >
          <option value="" disabled>Pilih Staff</option>
          <option v-for="staff in props.staffList" :key="staff.id" :value="staff.id">
            {{ staff.name }}
          </option>
        </select>
      </div>

      <!-- Deskripsi -->
      <div>
        <label class="block font-semibold mb-1 text-gray-800 dark:text-gray-200">Deskripsi</label>
        <input id="x" type="hidden" ref="trixInput" />
        <trix-editor
          input="x"
          class="trix-content border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded p-2"
        ></trix-editor>
      </div>

      <!-- Submit Button -->
      <div>
        <button
          type="submit"
          class="bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white px-4 py-2 rounded"
        >
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>

          
          <!-- 💬 Kolom Kanan: Komentar + Dropzone -->
  <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <!-- Komentar sebelumnya -->
    <div class="mt-8 border-t border-gray-200 dark:border-gray-600 pt-4">
      <h4 class="text-md font-semibold mb-3 text-gray-800 dark:text-gray-100">Komentar Terbaru</h4>

      <div v-if="permohonan.komentars.length === 0" class="text-gray-500 dark:text-gray-400">
        Belum ada komentar.
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="k in permohonan.komentars"
          :key="k.id"
          class="p-4 bg-gray-50 dark:bg-gray-700 border dark:border-gray-600 rounded-lg shadow-sm"
        >
          <!-- Header Komentar -->
          <div class="flex justify-between items-center text-sm text-gray-600 dark:text-gray-300">
            <div class="flex items-center gap-2">
              <span v-if="k.user.role === 'SUPERADMIN'" title="SUPERADMIN">🛡️</span>
              <span v-else-if="k.user.role === 'STAFF'" title="STAFF">👨‍💼</span>
              <span v-else title="BIASA">🙋‍♂️</span>

              <strong>{{ k.user.name }}</strong>
              <span class="text-xs text-gray-400 dark:text-gray-300">
                (<span v-if="k.user.role === 'SUPERADMIN'">ADMIN</span>
                <span v-else-if="k.user.role === 'STAFF'">STAFF</span>
                <span v-else>USER</span>)
              </span>
            </div>
            <div class="text-xs text-gray-400 dark:text-gray-300">
              {{ new Date(k.created_at).toLocaleString() }}
            </div>
          </div>

          <!-- Isi Komentar -->
          <div class="mt-2 prose prose-sm max-w-none text-gray-800 dark:text-gray-100" v-html="k.komentar"></div>

          <!-- Lampiran -->
          <div v-if="k.file" class="mt-3 flex items-center space-x-2">
            <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15.172 7l-6.586 6.586a2 2 0 002.828 2.828l6.586-6.586a4 4 0 00-5.656-5.656L7.05 9.05a6 6 0 008.486 8.486l6.586-6.586" />
            </svg>
            <a
              :href="`/storage/${k.file}`"
              target="_blank"
              class="text-blue-600 dark:text-blue-400 hover:underline"
              download
            >
              {{ k.file_original_name ?? 'Unduh File' }}
            </a>
          </div>

          <!-- Tombol Hapus -->
          <div class="flex justify-end mt-2">
            <button
              v-if="user.role === 'SUPERADMIN' || user.id === k.user.id"
              @click="deleteKomentar(k.id)"
              class="flex items-center gap-1 text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:underline"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3m5 0H6" />
              </svg>
              <span>Hapus</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Tambah Komentar -->
    <h3 class="text-lg font-semibold mb-4 mt-8 pt-4 border-t border-gray-300 dark:border-gray-600 flex items-center gap-2 text-gray-800 dark:text-gray-100">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 5.523-4.477 10-10 10S1 17.523 1 12 5.477 2 11 2s10 4.477 10 10z" />
      </svg>
      Tambahkan Komentar
    </h3>

    <form @submit.prevent="submitKomentar" class="space-y-4 text-sm">
      <!-- Editor Komentar -->
      <input id="komentarTrix" type="hidden" name="komentar" :value="komentar.komentar" />
      <trix-editor
        input="komentarTrix"
        class="trix-content border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
        @trix-change="updateKomentar"
      />

      <!-- Dropzone -->
      <div
        ref="dropzoneElement"
        class="dropzone border-2 border-dashed rounded p-4 text-center bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-500 text-gray-600 dark:text-gray-300"
      ></div>

      <button
        type="submit"
        class="bg-orange-500 hover:bg-orange-600 dark:bg-orange-600 dark:hover:bg-orange-700 text-white px-4 py-2 rounded"
      >
        Kirim Komentar
      </button>
    </form>
  </div>

        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>


<style scoped>
.trix-content {
  min-height: 150px;
}
a.download-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: #2563eb;
  text-decoration: underline;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
.status-text {
  transition: color 0.3s ease;
}
:deep(.dark .trix-content) {
  background-color: #1f2937 !important; /* Tailwind gray-800 */
  color: #f9fafb !important;           /* Tailwind gray-50 */
  border-color: #4b5563 !important;    /* Tailwind gray-600 */
}

:deep(.trix-content) {
  min-height: 150px;
}

</style>
