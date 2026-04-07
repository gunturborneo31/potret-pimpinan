<script setup>
import { Folder, EllipsisVertical } from 'lucide-vue-next'
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { FolderOpen, FolderLock } from 'lucide-vue-next'
import { Calendar } from 'lucide-vue-next' // ⬅️ tambahkan icon kalender

const emit = defineEmits(['rename', 'delete', 'shareSettings', 'toggleFavorite'])
const props = defineProps({ folder: Object, auth: Object})

const showMenu = ref(false)

function toggleMenu(e) {
  e.preventDefault()
  showMenu.value = !showMenu.value
}

function handleClick(action) {
  showMenu.value = false
  emit(action, props.folder)
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))

function handleClickOutside(e) {
  if (!e.target.closest('.dropdown-menu') && !e.target.closest('button')) {
    showMenu.value = false
  }
}

function truncate(text, length = 25) {
  return text.length > length ? text.slice(0, length) + '...' : text
}
</script>

<template>
  <div
    class="relative bg-white dark:bg-gray-800 rounded-2xl shadow hover:shadow-lg p-4 border border-gray-200 dark:border-gray-700 transition group"
  >
    <!-- Dropdown -->
    <div class="absolute top-2 right-2 left-2 flex items-center gap-2">
      <div class="absolute top-2 right-0">
        <button @click="toggleMenu" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
          <EllipsisVertical class="w-5 h-5" />
        </button>

        <div
          v-if="showMenu"
          class="dropdown-menu absolute right-0 mt-2 w-40 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded shadow z-10"
        >
          <ul class="text-sm text-gray-800 dark:text-white">
            <!-- Tombol Rename hanya jika user adalah pemilik -->
            <li v-if="auth?.user && folder.user_id === auth.user.id">
              <button @click="$emit('rename', folder)" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">
                ✏️ Rename
              </button>
            </li>

            <!-- Tombol Share tetap bisa -->
            <li>
              <button @click="handleClick('shareSettings')" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">
                🔗 Share Link
              </button>
            </li>

            <!-- Tombol Hapus hanya jika user adalah pemilik -->
            <li v-if="auth?.user && folder.user_id === auth.user.id">
              <button @click="handleClick('delete')" class="w-full text-left text-red-600 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">
                🗑️ Hapus Folder
              </button>
            </li>
          </ul>
        </div>
      </div>

      <!-- Tambahkan modifier .prevent untuk mencegah default GET -->
      <div
        class="cursor-pointer w-8 h-8 flex items-center justify-center"
        @click.prevent="$emit('toggleFavorite', folder)"
        v-if="auth?.user && folder.user_id === auth.user.id"
      >
        <span :class="[folder.is_favorite ? 'text-yellow-400' : 'text-gray-400', 'text-2xl leading-none']">★</span>
      </div>
    </div>

    <a :href="route('kegiatan.folders.show', props.folder.slug)" class="block mt-4">
      <div class="flex flex-col items-center justify-center text-center">
        <FolderOpen
          v-if="folder.is_public"
          class="w-12 h-12 text-green-500 mb-3 text-green-500"
        />
        <FolderLock
          v-else
          class="w-12 h-12 text-orange-500 mb-3 text-gray-500"
        />

        <div class="text-sm font-medium text-gray-800 dark:text-white truncate">
          {{ truncate(props.folder.judul) }}
        </div>

        <!-- Nama user -->
        <div v-if="props.folder.user" class="flex items-center justify-center mt-1 text-xs text-gray-500 dark:text-gray-400 truncate">
          <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8V22h19.2v-2.8c0-3.2-6.4-4.8-9.6-4.8z"/>
          </svg>
          <span class="truncate">{{ props.folder.user.name }}</span>
        </div>

        <!-- Informasi tambahan -->
        <div class="flex justify-center gap-3 mt-2 text-xs text-gray-500 dark:text-gray-400">
          <!-- Jumlah file -->
          <div title="Jumlah file">
            📄 Total File : {{ props.folder.files_count ?? 0 }}
          </div>
        </div>

        <!-- ⬇️ Tanggal kegiatan (dibawah Total File) -->
        <div class="flex items-center justify-center mt-1 text-xs text-gray-500 dark:text-gray-400">
          <span class="truncate">
           📅
            {{
              props.folder.tanggal_kegiatan
                ? new Date(props.folder.tanggal_kegiatan).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                  })
                : '-'
            }}
          </span>
        </div>
      </div>
    </a>
  </div>
</template>
