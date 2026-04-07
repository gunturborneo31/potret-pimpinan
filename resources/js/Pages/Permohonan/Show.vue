<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, onMounted, nextTick, onUpdated } from 'vue'

const props = defineProps({
  permohonan: Object,
  user: Object,
})

const form = useForm({
  komentar: '',
  file: null,
  permohonan_id: props.permohonan.id,
})

function handleFile(e) {
  form.file = e.target.files[0]
}

function submit() {
  form.post('/komentar', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('komentar', 'file')
    }
  })
}

/* ====== Linkify & enhance anchors (untuk v-html) ====== */
// Jika teks tidak punya <a ...>, deteksi URL dan ubah menjadi tautan.
// Kalau sudah ada <a>, biarkan apa adanya (hanya akan di-enhance di DOM).
function linkify(html) {
  if (!html) return ''
  const hasAnchor = /<a\s/i.test(html)
  if (hasAnchor) return html
  // Deteksi URL sederhana (http(s):// atau www.)
  const urlRegex = /((https?:\/\/|www\.)[^\s<]+)/gi
  return html.replace(urlRegex, (m) => {
    const href = /^https?:\/\//i.test(m) ? m : `http://${m}`
    return `<a href="${href}" target="_blank" rel="noopener noreferrer">${m}</a>`
  })
}

// refs untuk konten yang dirender via v-html
const descRef = ref(null)
const commentRefs = ref([])
// setter ref untuk elemen komentar per item
function setCommentRef(el, _idx) {
  if (el) commentRefs.value.push(el)
}

// Tambahkan atribut & kelas ke semua <a> di dalam kontainer
function enhanceAnchors(rootEl) {
  if (!rootEl) return
  const anchors = rootEl.querySelectorAll('a')
  anchors.forEach(a => {
    if (!a.getAttribute('target')) a.setAttribute('target', '_blank')
    a.setAttribute('rel', 'noopener noreferrer')
    a.classList.add('inline-link') // untuk styling underline + ikon
  })
}

async function enhanceAllLinks() {
  await nextTick()
  enhanceAnchors(descRef.value)
  commentRefs.value.forEach(enhanceAnchors)
}

onMounted(() => {
  enhanceAllLinks()
})
onUpdated(() => {
  // kalau ada update DOM (mis. komentar baru), pastikan link tetap di-enhance
  enhanceAllLinks()
})
</script>

<template>
  <Head :title="`Detail Permohonan: ${permohonan.title}`" />

  <AuthenticatedLayout>
    <!-- Header -->
    <template #header>
      <h2 class="text-lg sm:text-xl font-bold text-gray-800 dark:text-white">Detail Permohonan</h2>
    </template>

    <!-- Tombol Kembali -->
    <div class="px-3 sm:px-4 py-4">
      <div class="max-w-5xl mx-auto">
        <a
          href="/permohonan"
          class="inline-flex items-center gap-2 w-full sm:w-auto justify-center bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100 px-4 py-2 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition text-sm sm:text-base"
        >
          <span class="hidden sm:inline">←</span> Kembali ke Daftar Permohonan
        </a>
      </div>
    </div>

    <!-- Card Konten -->
    <div class="px-3 sm:px-4 pb-8">
      <div class="max-w-5xl mx-auto">
        <div class="bg-yellow-50 dark:bg-[#2b2a1f] border border-yellow-200 dark:border-yellow-700/40 shadow-md sm:shadow-lg rounded-lg sm:rounded-xl w-full p-4 sm:p-6">

          <!-- Judul -->
          <h3 class="text-xl sm:text-2xl font-bold text-orange-700 dark:text-amber-300 mb-3 sm:mb-4 leading-snug">
            {{ permohonan.title }}
          </h3>

          <!-- Meta: kategori, prioritas, status (grid responsif) -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3 mb-4 sm:mb-6">
            <div class="flex items-center justify-between sm:block">
              <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Kategori</p>
              <p class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-100">{{ permohonan.kategori }}</p>
            </div>
            <div class="flex items-center justify-between sm:block">
              <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Prioritas</p>
              <p class="text-sm sm:text-base font-semibold text-gray-800 dark:text-gray-100">{{ permohonan.priority }}</p>
            </div>
            <div class="flex items-center justify-between sm:block">
              <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</p>
              <p class="inline-flex items-center gap-2 text-sm sm:text-base font-semibold text-orange-800 dark:text-amber-300">
                <span class="inline-block w-2 h-2 rounded-full bg-orange-500"></span>
                {{ permohonan.status }}
              </p>
            </div>
          </div>

          <!-- Deskripsi -->
          <div class="mt-3 sm:mt-4">
            <p class="font-semibold text-gray-800 dark:text-gray-100 mb-2">Deskripsi</p>
            <div
              ref="descRef"
              class="content-html prose prose-sm sm:prose-base max-w-none text-gray-800 dark:text-gray-100 dark:prose-invert"
              v-html="linkify(permohonan.description)"
            />
          </div>

          <!-- Lampiran -->
          <div v-if="permohonan.file" class="mt-4">
            <a
              :href="`/storage/${permohonan.file}`"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-link inline-flex items-center gap-2 text-blue-700 dark:text-blue-400 underline text-sm sm:text-base"
            >
              📎 Lihat File Lampiran
            </a>
          </div>

          <!-- Garis -->
          <div class="mt-5 sm:mt-6 pt-4 border-t border-yellow-200 dark:border-yellow-700/40"></div>

          <!-- Komentar -->
          <div class="mt-2">
            <h4 class="text-lg font-bold text-orange-800 dark:text-amber-300 mb-3 sm:mb-4">Komentar</h4>

            <div
              v-for="(komentar, idx) in permohonan.komentars"
              :key="komentar.id"
              :ref="el => setCommentRef(el, idx)"
              class="bg-white dark:bg-gray-800 border-l-4 border-orange-400 dark:border-orange-500/80 shadow-sm mb-4 p-3 sm:p-4 rounded-md"
            >
              <p class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-200">
                {{ komentar.user.name }}:
              </p>

              <div
                class="content-html prose prose-sm max-w-none mt-1 text-gray-800 dark:text-gray-100 dark:prose-invert"
                v-html="linkify(komentar.komentar)"
              />

              <div v-if="komentar.file" class="mt-2">
                <a
                  :href="`/storage/${komentar.file}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-link text-blue-600 dark:text-blue-400 underline text-sm"
                >📎 Lihat File</a>
              </div>
            </div>
          </div>

          <!-- (Opsional) Form komentar -->
          <!--
          <form @submit.prevent="submit" class="mt-6">
            <label class="block text-sm mb-1">Tambah Komentar</label>
            <textarea v-model="form.komentar" class="w-full border rounded-md p-2 text-sm dark:bg-gray-900 dark:border-gray-700 dark:text-white" rows="3"></textarea>

            <div class="mt-3 flex flex-col sm:flex-row items-start sm:items-center gap-3">
              <input type="file" @change="handleFile" class="text-sm" />
              <button
                type="submit"
                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md text-sm"
                :disabled="form.processing"
              >
                Kirim
              </button>
            </div>
          </form>
          -->
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Styling link di dalam konten v-html */
.content-html a {
  text-decoration: underline;
  word-break: break-word;
}

/* Kelas yang kita suntikkan ke semua <a> melalui enhanceAnchors() */
.inline-link {
  text-decoration: underline;
  position: relative;
}

/* Ikon kecil penanda link */
.inline-link::after {
  content: ' 🔗';
  font-size: 0.9em;
  opacity: 0.85;
}

/* Perapihan tampilan prose pada dark mode (opsional, jika pakai @tailwind/typography) */
:deep(.prose img) {
  border-radius: 0.5rem;
}

/* Responsif kecil-kecilan tambahan */
@media (max-width: 640px) {
  .prose {
    font-size: 0.95rem;
    line-height: 1.55;
  }
}
</style>
