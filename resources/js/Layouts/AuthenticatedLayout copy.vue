<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import {
  Home,
  FileText,
  Megaphone,
  MessageCircle,
  Share2,
  Calendar,
  Users,
  Moon,
  Bell,
  Sun,
  User
} from 'lucide-vue-next';

const showDropdown = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const hasNew = ref(false);
const notifSound = ref(null);
const userInteracted = ref(false);

// Toggle dropdown bell
const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
    if (showDropdown.value) {
        hasNew.value = false; // hentikan animasi bell
    }
};

// Load notifications
const loadNotifications = async () => {
    try {
        const res = await axios.get('/notifications');
        const dataArray = Array.isArray(res.data.notifications) ? res.data.notifications : [];
        const unread = res.data.unread_count ?? dataArray.filter(n => !n.read_at).length;

        // Jika ada notifikasi baru
        if (unread > unreadCount.value) {
            hasNew.value = true;
            if (notifSound.value && userInteracted.value) {
                // hanya play jika audio sedang pause
                if (notifSound.value.paused) {
                    notifSound.value.currentTime = 0;
                    notifSound.value.play().catch(e => console.log('Audio play error:', e));
                }
            }
        }

        notifications.value = dataArray;
        unreadCount.value = unread;
    } catch (err) {
        console.error('Error loading notifications:', err);
    }
};

// Mark all notifications as read
const markAllRead = async () => {
    try {
        await axios.post('/notifications/mark-all-read');
        notifications.value = notifications.value.map(n => ({ ...n, read_at: new Date() }));
        unreadCount.value = 0;
        hasNew.value = false;

        // hentikan audio
        if (notifSound.value) {
            notifSound.value.pause();
            notifSound.value.currentTime = 0;
        }
    } catch (err) {
        console.error('Error marking notifications as read:', err);
    }
};

onMounted(() => {
    notifSound.value = document.getElementById('notifSound');

    // Tunggu interaksi user agar audio bisa play
    const interactionHandler = () => {
        userInteracted.value = true;
        document.removeEventListener('click', interactionHandler);
        document.removeEventListener('keydown', interactionHandler);
        document.removeEventListener('touchstart', interactionHandler);
    };
    document.addEventListener('click', interactionHandler);
    document.addEventListener('keydown', interactionHandler);
    document.addEventListener('touchstart', interactionHandler);

    loadNotifications();
    setInterval(loadNotifications, 5000);
});
</script>


<template>
  <div class="flex min-h-screen bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 transition-colors">
    <!-- Sidebar -->
    <aside
      :class="[
        'bg-white dark:bg-gray-800 border-r shadow-sm transition-all duration-300',
        isCollapsed ? 'w-16' : 'w-64'
      ]"
      class="relative"
    >
      <!-- Header -->
      <div class="h-16 flex items-center justify-between px-4 border-b dark:border-gray-700">
        <Link :href="route('dashboard')">
          <img
            v-if="!isCollapsed"
            class="h-9"
            src="/img/logo-only.png"
            alt="Logo"
          />
          <img
            v-else
            class="h-8 mx-auto"
            src="/img/logo-only.png"
            alt="Logo"
          />
        </Link>
        <button @click="toggleSidebar" class="text-gray-500 hover:text-orange-500">
          <svg v-if="!isCollapsed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 19l-7-7 7-7" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mx-auto" fill="none"
            viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

  
    <!-- Navigation -->
<nav class="mt-4 px-2 space-y-2">

  <div v-if="$page.props.auth.user.tipe === 'All Role'">
  <!-- Dashboard -->
  <Link
    :href="route('dashboard')"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <Home class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Dashboard</span>
  </Link>

  <!-- Permohonan -->
  <Link
    href="/permohonan"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <FileText class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Permohonan</span>
  </Link>

  <!-- Berita -->
  <Link
    href="/berita"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <Megaphone class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Berita</span>
  </Link>

  <!-- Sambutan -->
  <Link
    href="/sambutan"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <MessageCircle class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Sambutan</span>
  </Link>

  <!-- Post Social Media -->
  <Link
    href="/post-sosmed"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <Share2 class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Post Social Media</span>
  </Link>

  <!-- Kegiatan -->
  <Link
    href="/kegiatan"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <Calendar class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Kegiatan</span>
  </Link>

  <!-- Pengguna -->
  <Link
    href="/users"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <Users class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Pengguna</span>
  </Link>
  </div>

  <div v-if="$page.props.auth.user.tipe === 'Staf Sambutan'">
  <!-- Dashboard -->
  <Link
    :href="route('dashboard')"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <Home class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Dashboard</span>
  </Link>

  <!-- Sambutan -->
  <Link
    href="/sambutan"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <MessageCircle class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Sambutan</span>
  </Link>

  </div>

  <div v-if="$page.props.auth.user.tipe === 'Staf Berita'">
  <!-- Dashboard -->
  <Link
    :href="route('dashboard')"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <Home class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Dashboard</span>
  </Link>
  
   <!-- Berita -->
  <Link
    href="/berita"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <Megaphone class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Berita</span>
  </Link>

  <!-- Post Social Media -->
  <Link
    href="/post-sosmed"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <Share2 class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Post Social Media</span>
  </Link>


  </div>

   <div v-if="$page.props.auth.user.tipe === 'Staf Dokumentasi'">
  <!-- Dashboard -->
  <Link
    :href="route('dashboard')"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <Home class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Dashboard</span>
  </Link>
  
   <!-- Berita -->
  <Link
    href="/kegiatan"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <Calendar class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Kegiatan</span>
  </Link>

  <!-- Post Social Media -->
  <Link
    href="/post-sosmed"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <Share2 class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Post Social Media</span>
  </Link>


  </div>

  <div v-if="$page.props.auth.user.tipe === 'USERB'">

  <!-- Permohonan -->
  <Link
    href="/permohonan"
    class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
    :class="{ 'justify-center': isCollapsed }"
  >
    <FileText class="h-5 w-5 text-orange-500" />
    <span v-show="!isCollapsed">Permohonan</span>
  </Link>

  </div>

</nav>

    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
    <header class="bg-white dark:bg-gray-800 shadow px-4 py-3 flex items-center justify-between">
  <h1 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
    <slot name="header" />
  </h1>

  <!-- User Profile & Dark Mode Toggle -->
<div class="ml-auto flex items-center gap-4">

   <!-- Bell + dropdown -->
 <div class="relative">
    <!-- Audio bell -->
    <audio id="notifSound" src="/sounds/bell.mp3" loop></audio>

    <!-- Bell button -->
    <button @click="toggleDropdown" class="relative p-2 hover:bg-gray-100 rounded-full">
      <Bell class="w-6 h-6 text-gray-700" :class="{ 'animate-bell': hasNew }" />
      <span v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">
        {{ unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <div v-if="showDropdown" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border z-50 max-h-96 overflow-auto">
      <div class="flex justify-between items-center p-2 border-b">
        <span class="font-semibold text-gray-700 text-sm">Notifikasi</span>
        <button @click="markAllRead" class="text-xs text-blue-500 hover:underline">
          Mark all as read
        </button>
      </div>

      <div v-if="notifications.length > 0">
        <div v-for="n in notifications" :key="n.id" class="p-3 border-b hover:bg-gray-50">
          <p class="text-sm font-medium">
            <Link v-if="n.data?.permohonan_id" :href="`/permohonan/${n.data.permohonan_id}`" class="text-blue-600 hover:underline">
              {{ n.data?.title || n.title }}
            </Link>
            <span v-else>{{ n.data?.title || n.title }}</span>
          </p>
          <p class="text-xs text-gray-500">Message: {{ n.data?.message || n.message }}</p>
          <span class="text-xs text-gray-400">{{ n.created_at }}</span>
        </div>
      </div>
      <div v-else class="p-3 text-sm text-gray-500 text-center">
        Tidak ada notifikasi
      </div>
    </div>
  </div>
  <!-- Dark Mode Toggle Button -->
  <button
    @click="isDark = !isDark"
    class="flex items-center gap-1 text-sm px-3 py-2 text-gray-700 dark:text-gray-100 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
  >
    <svg v-if="isDark" class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2"
      viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M12 3v1m0 16v1m8.5-8.5l-.7.7M4.2 4.2l.7.7M21 12h-1M4 12H3m16.2 4.2l-.7-.7M4.2 19.8l.7-.7M12 5a7 7 0 100 14a7 7 0 000-14z" />
    </svg>
    <svg v-else class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
      viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M17.293 14.707A8 8 0 019.293 6.707a8.003 8.003 0 0010.586 10.586z" />
    </svg>
    <span class="hidden sm:inline">Dark Mode</span>
  </button>

  <!-- User Profile Dropdown -->
  <Dropdown align="right" width="56">
    <template #trigger>
      <button
        class="flex items-center gap-2 text-sm px-3 py-2 text-gray-700 dark:text-gray-100 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
      >
        <svg class="h-5 w-5 text-gray-500 dark:text-gray-300" fill="none" stroke="currentColor" stroke-width="2"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M5.121 17.804A3.001 3.001 0 0112 15h0a3.001 3.001 0 016.879 2.804M15 11a3 3 0 00-6 0" />
        </svg>
        <span class="font-medium truncate">{{ $page.props.auth.user.name }}</span>
      </button>
    </template>

    <template #content>
      <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
      <DropdownLink :href="route('logout')" method="post" as="button">Logout</DropdownLink>
    </template>
  </Dropdown>
</div>

</header>

      <main class="p-0">
        <div v-if="$page.props.flash?.message" class="mb-4 text-green-600 dark:text-green-400">
          {{ $page.props.flash.message }}
        </div>
        <slot />
      </main>

    </div>
  </div>
</template>


<style scoped>
@keyframes bellShake {
  0% { transform: rotate(0deg); }
  10% { transform: rotate(15deg); }
  20% { transform: rotate(-15deg); }
  30% { transform: rotate(10deg); }
  40% { transform: rotate(-10deg); }
  50% { transform: rotate(5deg); }
  60% { transform: rotate(-5deg); }
  70% { transform: rotate(2deg); }
  80% { transform: rotate(-2deg); }
  100% { transform: rotate(0deg); }
}

.animate-bell {
  animation: bellShake 1s ease-in-out infinite;
}
</style>