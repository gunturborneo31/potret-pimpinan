<script setup>
import { Folder, EllipsisVertical } from 'lucide-vue-next'
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { FolderOpen, FolderLock } from 'lucide-vue-next'

const emit = defineEmits(['rename', 'delete', 'shareSettings'])
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

function truncate(text, length = 25) {
  return text.length > length ? text.slice(0, length) + '...' : text
}
</script>

<template>
  <div
    class="relative p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow hover:shadow-md transition group"
  >
    <!-- Titik tiga -->
    <div class="absolute top-2 right-2">
      <button @click="toggleMenu" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
        <EllipsisVertical class="w-5 h-5" />
      </button>
      <div
        v-if="showMenu"
        class="dropdown-menu absolute right-0 mt-2 w-40 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded shadow z-10"
      >
        <ul class="text-sm text-gray-800 dark:text-white">
        <li
  v-if="auth?.user && folder.user_id === auth.user.id"
>
            <button  @click="$emit('rename', folder)" class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">
              ✏️ Rename
            </button>
          </li>
          <li>
            <button @click="handleClick('shareSettings')"  class="w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">
              🔗 Share Link
            </button>
          </li>
      <li
  v-if="auth?.user && folder.user_id === auth.user.id"
>
            <button @click="handleClick('delete')" class="w-full text-left text-red-600 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">
              🗑️ Hapus Folder
            </button>
          </li>
        </ul>
      </div>
    </div>

    <!-- Link Folder -->
    <a
      :href="route('kegiatan.folders.show', props.folder.slug)"
      class="flex items-center justify-between mt-3"
    >
      <div class="flex items-center gap-3">
             <FolderOpen
      v-if="folder.is_public"
      class="w-6 h-6 text-green-500 group-hover:text-green-600"
    />
    <FolderLock
      v-else
      class="w-6 h-6 text-orange-500 group-hover:text-orange-600"
    />
        <div class="font-medium text-gray-800 dark:text-white truncate">
          {{ truncate(props.folder.judul) }}
        </div>
        

        <div class="flex justify-center gap-3 mt-2 text-xs text-gray-500 dark:text-gray-400">
          <!-- Jumlah file -->
          <div title="Jumlah file">
          <span class="truncate">{{ props.folder.user.name }}</span>

            📄 {{ props.folder.files_count ?? 0 }} 📅
            {{
              props.folder.tanggal_kegiatan
                ? new Date(props.folder.tanggal_kegiatan).toLocaleDateString('id-ID', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                  })
                : '-'
            }}
          </div>
          <!-- ibttus public/private -->
          <div title="Status Folder">
            {{ props.folder.is_public ? '🔓 Public' : '🔒 Private' }}
          </div>
        </div>
      </div>
       <div class="cursor-pointer w-8 h-8 flex items-center justify-center" @click="$emit('toggleFavorite', folder)"  v-if="auth?.user && folder.user_id === auth.user.id">
    <span
      :class="[
        folder.is_favorite ? 'text-yellow-400' : 'text-gray-400',
        'text-2xl leading-none'
      ]"
    >★</span>
  </div>


    </a>
  </div>
</template>
