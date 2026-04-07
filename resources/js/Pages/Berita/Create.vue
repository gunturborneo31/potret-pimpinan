<template>
  <Head title="Tambah Berita" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Tambah Berita</h2>
    </template>

    <div class="py-6 max-w-4xl mx-auto">
      <div class="mt-6 mb-6 text-right">
        <a href="/berita"
          class="inline-block bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 px-4 py-2 rounded hover:bg-gray-400 dark:hover:bg-gray-600 transition">
          ← Kembali ke Daftar Berita
        </a>
      </div>

      <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
        <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-5">

          <!-- Judul -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Judul</label>
            <input type="text" v-model="form.judul"
              class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />
          </div>

          <!-- Isi Berita -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Isi Berita</label>
            <input id="x" type="hidden" v-model="form.isi_berita" ref="trixInput" />
            <trix-editor input="x"
              class="trix-content bg-white dark:bg-gray-900 border dark:border-gray-700 rounded p-2 dark:text-gray-100"></trix-editor>
          </div>

          <!-- Tanggal Terbit -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Tanggal Terbit</label>
            <input type="date" v-model="form.tanggal_terbit"
              class="w-full border rounded p-2 bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100" required />
          </div>

          <!-- Penulis -->
        <div>
  <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Penulis</label>
  <Multiselect
    v-model="form.penulis_id"
    :options="penulisidOptions"
    placeholder="Pilih Penulis"
    :searchable="true"
    :clearable="true"
    class="w-full dark:text-gray-700"
    :class="{ 'border-red-500': !form.penulis_id && submitted }"
  />
  <p v-if="!form.penulis_id && submitted" class="text-red-500 text-sm mt-1">Penulis wajib dipilih</p>
</div>


          <!-- Editor -->
        <div>
  <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Editor</label>
  <Multiselect
    v-model="form.editor_id"
    :options="penulisidOptions"
    placeholder="Pilih Editor"
    :searchable="true"
    :clearable="true"
    class="w-full dark:text-gray-700"
    :class="{ 'border-red-500': !form.editor_id && submitted }"
  />
  <p v-if="!form.editor_id && submitted" class="text-red-500 text-sm mt-1">Penulis wajib dipilih</p>
</div>

          <!-- Lainnya -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Lainnya Opsional</label>
            <Multiselect
              v-model="form.lainnya_id"
              :options="userOptions"
              mode="tags"
            placeholder="Pilih Kontributor"
            :searchable="true"
            :clearable="true"
            class="w-full dark:text-gray-700"

              
            />
          </div>

          <!-- Kegiatan -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-7">Terkait Kegiatan (Opsional)</label>
            <Multiselect
              v-model="form.kegiatan_id"
              :options="kegiatanOptions"
             placeholder="Pilih Kegiatan..."
            :searchable="true"
            :clearable="true"
            class="w-full dark:text-gray-700"
            />
          </div>

          <!-- Thumbnail -->
        <div>
        <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">Thumbnail (Opsional)</label>
        <input
            type="file"
            accept="image/*"
            @change="handleThumbnailChange"
            class="w-full text-gray-700 dark:text-gray-200 file:bg-orange-500 file:text-white file:rounded file:px-3 file:py-1 file:border-none file:hover:bg-orange-600"
        />
        </div>


          <!-- File -->
          <div>
            <label class="block font-semibold mb-1 text-gray-700 dark:text-gray-200">File (Opsional)</label>
            <input type="file" @change="handleFileChange"
              class="w-full text-gray-700 dark:text-gray-200 file:bg-orange-500 file:text-white file:rounded file:px-3 file:py-1 file:border-none file:hover:bg-orange-600" />
          </div>

          <!-- is_public -->
          <div>
            <label class="inline-flex items-center space-x-2">
              <input type="checkbox" v-model="form.is_public" />
              <span class="text-gray-700 dark:text-gray-200">Publikasikan Berita</span>
            </label>
          </div>

          <!-- Submit -->
          <div>
            <button type="submit"
              class="block w-full bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 dark:hover:bg-orange-400 transition-colors text-center">
              Simpan Berita
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Multiselect from '@vueform/multiselect'
import '@vueform/multiselect/themes/default.css'

const props = defineProps({
  users: Array,
  penulisid: Array,
  kegiatans: Array,
})

const form = useForm({
  judul: '',
  slug: '',
  isi_berita: '',
  tanggal_terbit: '',
  penulis_id: null,
  editor_id: null,
  lainnya_id: [],
  kegiatan_id: null,
  thumbnail: null,
  file: null,
  is_public: false,
})

const trixInput = ref(null)
onMounted(() => {
  const editor = document.querySelector('trix-editor')
  if (editor) {
    editor.addEventListener('trix-change', () => {
      form.isi_berita = trixInput.value?.value || ''
    })
  }
})

// Options for select
const userOptions = computed(() =>
  props.users.map(user => ({ label: user.name, value: user.id }))
)

const penulisidOptions = computed(() =>
  Object.values(props.penulisid).map(user => ({
    label: user.name,
    value: user.id
  }))
)

const kegiatanOptions = computed(() =>
  props.kegiatans.map(k => ({ label: k.judul, value: k.id }))
)

const handleFileChange = (e) => {
  form.file = e.target.files[0]
}

const handleThumbnailChange = (e) => {
  form.thumbnail = e.target.files[0]
}

const submitted = ref(false);

const submit = () => {
  submitted.value = true;

  if (!form.penulis_id || !form.editor_id) {
    // Validasi gagal, tidak kirim
    return;
  }

  form.post(route('berita.store'));
};

</script>