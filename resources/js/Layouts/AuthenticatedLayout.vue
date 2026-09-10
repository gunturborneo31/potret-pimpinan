<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { toast } from 'vue3-toastify';
import {
  Home,
  FileText,
  Megaphone,
  MessageCircle,
  Share2,
  Calendar,
  Users,
  Bell,
  Moon,
  Globe,
  FolderOpen,
  Sun,
  User,
  MoreHorizontal
} from 'lucide-vue-next';

import dayjs from 'dayjs'
import 'dayjs/locale/id'
dayjs.locale('id')

const formatDate = (date) => dayjs(date).format('D MMMM HH:mm')

// ===== Notifikasi =====
const showDropdownNotif = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
const hasNewNotif = ref(false);
const notifAudio = ref(null);
const userInteractedNotif = ref(false);

// ===== Sidebar & Theme =====
const isCollapsed = ref(false);
const toggleSidebar = () => (isCollapsed.value = !isCollapsed.value);
const isDark = ref(false);

// ===== Restore/Save state =====
const SIDEBAR_KEY = 'pp_sidebar_collapsed';

// ===== Bottom Nav (mobile/tablet) =====
const showMore = ref(false); // sheet “Lainnya / Titik tiga”
const page = usePage();
const role = computed(() => page?.props?.auth?.user?.role || 'BIASA');

// Helper: aktifkan state pada nav item
const isActivePath = (start) => {
  try {
    return window.location.pathname.startsWith(start);
  } catch {
    return false;
  }
};

// Semua menu per role (pakai path sama seperti sidebar di file-mu)
const allMenusByRole = computed(() => {
  const map = {
    'SUPERADMIN': [
      { name: 'Dashboard', href: route('dashboard'), icon: Home },
      { name: 'Permohonan', href: '/permohonan', icon: FileText },
      { name: 'Sambutan', href: '/sambutan', icon: MessageCircle },
      { name: 'Post Social Media', href: '/post-sosmed', icon: Share2 },
      { name: 'Kegiatan', href: '/kegiatan', icon: FolderOpen },
      { name: 'Pengguna', href: '/users', icon: Users },
    ],
    'STAFF': [
      { name: 'Dashboard', href: route('dashboard'), icon: Home },
      { name: 'Permohonan', href: '/permohonan', icon: FileText },
      { name: 'Sambutan', href: '/sambutan', icon: MessageCircle },
      { name: 'Post Social Media', href: '/post-sosmed', icon: Share2 },
      { name: 'Kegiatan', href: '/kegiatan', icon: FolderOpen },
    ],
    'BIASA': [
      { name: 'Permohonan', href: '/permohonan', icon: FileText },
    ],
  };
  return map[role.value] || [];
});

// 4 menu utama (untuk role selain USERB)
const PRIMARY_NAMES = ['Dashboard', 'Permohonan', 'Kegiatan'];

const primaryMenus = computed(() => {
  const list = allMenusByRole.value.filter(i => PRIMARY_NAMES.includes(i.name));
  const order = new Map(PRIMARY_NAMES.map((n, i) => [n, i]));
  return list.sort((a, b) => order.get(a.name) - order.get(b.name)).slice(0, 4);
});

const moreMenus = computed(() =>
  allMenusByRole.value.filter(i => !PRIMARY_NAMES.includes(i.name))
);

// ===== Notifikasi logic =====
const toggleDropdownNotif = () => {
  showDropdownNotif.value = !showDropdownNotif.value;
  if (showDropdownNotif.value) hasNewNotif.value = false;
};

const loadNotifications = async () => {
  try {
    const res = await axios.get('/notifications');
    const dataArray = Array.isArray(res.data.notifications) ? res.data.notifications : [];
    const unread = res.data.unread_count ?? dataArray.filter(n => !n.read_at).length;

    if (unread > unreadCount.value) {
      hasNewNotif.value = true;
      if (notifAudio.value && userInteractedNotif.value) {
        notifAudio.value.currentTime = 0;
        notifAudio.value.play().catch(() => {});
      }
    }

    notifications.value = dataArray;
    unreadCount.value = unread;
  } catch (err) {
    console.error('Error loading notifications:', err);
  }
};

const markAllReadNotif = async () => {
  try {
    await router.post('/notifications/mark-all-read');
    notifications.value = notifications.value.map(n => ({ ...n, read_at: new Date() }));
    unreadCount.value = 0;
    hasNewNotif.value = false;
    if (notifAudio.value) {
      notifAudio.value.pause();
      notifAudio.value.currentTime = 0;
    }
    toast.success('Semua notifikasi telah dibaca!', { position: 'top-right', autoClose: 3000 });
  } catch (err) {
    console.error('Error marking notifications as read:', err);
    toast.error('Gagal menandai notifikasi!', { position: 'top-right', autoClose: 3000 });
  }
};

const enableNotifSound = () => {
  userInteractedNotif.value = true;
  localStorage.setItem('notifSoundEnabled', 'true');
  notifAudio.value?.play().catch(()=>{});
};

// ===== Mount / Watch =====
const interactionHandler = () => {
  userInteractedNotif.value = true;
  document.removeEventListener('click', interactionHandler);
  document.removeEventListener('keydown', interactionHandler);
  document.removeEventListener('touchstart', interactionHandler);
};
document.addEventListener('click', interactionHandler);
document.addEventListener('keydown', interactionHandler);
document.addEventListener('touchstart', interactionHandler);

onMounted(() => {
  isDark.value = localStorage.getItem('darkMode') === 'true';
  document.documentElement.classList.toggle('dark', isDark.value);

  const savedCollapsed = localStorage.getItem(SIDEBAR_KEY);
  if (savedCollapsed !== null) {
    isCollapsed.value = savedCollapsed === 'true';
  }

  const saved = localStorage.getItem('notifSoundEnabled') === 'true';
  if (saved) userInteractedNotif.value = true;

  const ih = () => {
    userInteractedNotif.value = true;
    document.removeEventListener('click', ih);
    document.removeEventListener('keydown', ih);
    document.removeEventListener('touchstart', ih);
  };
  document.addEventListener('click', ih);
  document.addEventListener('keydown', ih);
  document.addEventListener('touchstart', ih);

  loadNotifications();
  setInterval(loadNotifications, 5000);
});

watch(isDark, (val) => {
  localStorage.setItem('darkMode', val);
  document.documentElement.classList.toggle('dark', val);
});

watch(isCollapsed, (val) => {
  localStorage.setItem(SIDEBAR_KEY, val ? 'true' : 'false');
});
</script>

<template>
  <!-- App shell: tambahkan class 'app-shell' agar bisa dioverride margin-left di mobile -->
  <div
    class="app-shell min-h-screen flex flex-col transition-all duration-300 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100"
    :class="isCollapsed ? 'ml-16' : 'ml-64'"
  >
    <!-- Sidebar (sembunyikan di < md) -->
    <aside
      class="sidebar-desktop fixed top-0 left-0 h-screen bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-sm transition-all duration-300 hidden md:block"
      :class="isCollapsed ? 'w-16' : 'w-64'"
    >
      <!-- Header -->
      <div class="h-16 flex items-center justify-between px-4 border-b dark:border-gray-700">
        <Link :href="route('dashboard')">
          <img
            v-if="!isCollapsed"
            class="h-7 sm:h-8 md:h-9"
            src="/img/logo-only.png"
            alt="Logo"
          />
          <img
            v-else
            class="h-6 sm:h-7 md:h-8 mx-auto"
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

      <!-- Navigation (desktop) -->
      <nav class="px-2 py-4 space-y-2 overflow-y-auto h-[calc(100%-4rem)]">
        <template v-for="item in allMenusByRole" :key="item.name">
          <Link :href="item.href" class="flex items-center gap-2 px-3 py-4 rounded hover:bg-orange-100 dark:hover:bg-gray-700 transition-colors" :class="{ 'justify-center': isCollapsed }">
            <component :is="item.icon" class="h-5 w-5 text-orange-500 flex-shrink-0" />
            <span v-show="!isCollapsed" class="truncate">{{ item.name }}</span>
          </Link>
        </template>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
      <header class="bg-white dark:bg-gray-800 shadow px-4 py-3 flex items-center justify-between">
        <!-- App Title -->
        <div class="hidden md:flex items-center gap-3">
          <img src="/img/logo-only.png" alt="Logo" class="h-8" />
          <h1 class="text-lg font-bold text-orange-600 dark:text-orange-400">Layanan Komunikasi dan Dokumentasi  Pimpinan</h1>
        </div>

        <div class="ml-auto flex items-center gap-4">
          <!-- Notif -->
          <div class="relative">
            <audio ref="notifAudio" src="/sounds/bell.mp3" loop></audio>
            <button @click="toggleDropdownNotif" class="relative p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full">
              <Bell class="w-6 h-6 text-gray-700 dark:text-gray-100" :class="{ 'animate-bell': hasNewNotif }" />
              <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">
                {{ unreadCount }}
              </span>
            </button>

            <!-- ====== DROPDOWN NOTIFIKASI (DESKTOP) ====== -->
            <div
              v-if="showDropdownNotif"
              class="hidden lg:block absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border z-50 max-h-96 overflow-auto"
            >
              <div class="flex justify-between items-center p-2 border-b dark:border-gray-700">
                <span class="font-semibold text-gray-700 dark:text-gray-100 text-sm">Notifikasi</span>
                <button @click="markAllReadNotif" class="text-xs text-blue-500 hover:underline">Mark all as read</button>
              </div>

              <div v-if="notifications.length > 0">
                <div v-for="n in notifications" :key="n.id" class="p-3 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                  <Link :href="route('permohonan.show', n.data?.permohonan_id)">
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ n.data?.title || n.title }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ n.data?.message || n.message }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ n.data?.status || n.status }}</p>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ formatDate(n.created_at) }}</span>
                  </Link>
                </div>
              </div>
              <div v-else class="p-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                Tidak ada notifikasi
              </div>
            </div>

            <!-- ====== DROPDOWN NOTIFIKASI (MOBILE/TABLET – CENTER) ====== -->
            <div
              v-if="showDropdownNotif"
              class="lg:hidden fixed left-1/2 -translate-x-1/2 top-16 w-[92vw] max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-2xl border z-50 max-h-[60vh] overflow-auto"
            >
              <div class="flex justify-between items-center p-2 border-b dark:border-gray-700">
                <span class="font-semibold text-gray-700 dark:text-gray-100 text-sm">Notifikasi</span>
                <button @click="markAllReadNotif" class="text-xs text-blue-500 hover:underline">Mark all as read</button>
              </div>

              <div v-if="notifications.length > 0">
                <div v-for="n in notifications" :key="n.id" class="p-3 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                  <Link :href="route('permohonan.show', n.data?.permohonan_id)">
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ n.data?.title || n.title }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ n.data?.message || n.message }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ n.data?.status || n.status }}</p>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ formatDate(n.created_at) }}</span>
                  </Link>
                </div>
              </div>
              <div v-else class="p-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                Tidak ada notifikasi
              </div>
            </div>
          </div>

          <!-- Dark Mode -->
          <button
            @click="isDark = !isDark"
            class="flex items-center gap-1 text-sm px-3 py-2 text-gray-700 dark:text-gray-100 rounded hover:bg-orange-100 dark:hover:bg-gray-700"
          >
            <svg v-if="isDark" class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 3v1m0 16v1m8.5-8.5l-.7.7M4.2 4.2l.7.7M21 12h-1M4 12H3m16.2 4.2l-.7-.7M4.2 19.8l.7-.7M12 5a7 7 0 100 14a7 7 0 000-14z" />
            </svg>
            <svg v-else class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M17.293 14.707A8 8 0 019.293 6.707a8.003 8.003 0 0010.586 10.586z" />
            </svg>
            <span class="hidden sm:inline">Dark Mode</span>
          </button>

          <!-- Website -->
          <Link href="/" target="_blank" class="flex items-center gap-1 text-sm px-3 py-2 text-gray-700 dark:text-gray-100 rounded hover:bg-orange-100 dark:hover:bg-gray-700">
            <Globe class="h-5 w-5 text-orange-500" />
            <span class="hidden sm:inline">Website</span>
          </Link>

          <!-- Profile -->
          <Dropdown align="right" width="56">
            <template #trigger>
              <button class="flex items-center gap-2 text-sm px-3 py-2 text-gray-700 dark:text-gray-100 rounded hover:bg-orange-100 dark:hover:bg-gray-700">
                <svg class="h-5 w-5 text-gray-500 dark:text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M5.121 17.804A3.001 3.001 0 0112 15h0a3.001 3.001 0 016.879 2.804M15 11a3 3 0 00-6 0" />
                </svg>
                <span class="font-medium"
                    :title="$page.props.auth.user.name">
                {{ ($page.props.auth.user.name || '').slice(0, 6) + (($page.props.auth.user.name || '').length > 6 ? '...' : '') }}
              </span>

              </button>
            </template>
            <template #content>
              <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
              <DropdownLink :href="route('logout')" method="post" as="button">Logout</DropdownLink>
            </template>
          </Dropdown>
        </div>
      </header>

      <main class="flex-1 p-0 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-100">
        <div v-if="$page.props.flash?.message" class="mb-4 text-green-600 dark:text-green-400">
          {{ $page.props.flash.message }}
        </div>
        <slot />
      </main>
    </div>

    <!-- ====== Bottom Navigation (mobile & tablet) ====== -->
    <nav
      class="md:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 z-40"
    >
      <!-- USERB: Website | Permohonan | Lainnya — centered (tetap) -->
      <template v-if="role === 'USERB'">
        <div class="h-16 flex items-center justify-center">
          <div class="flex items-center justify-center gap-8">
            <Link
              href="/"
              target="_blank"
              class="flex flex-col items-center justify-center gap-1 text-xs text-gray-700 dark:text-gray-200"
            >
              <Globe class="w-6 h-6" />
              <span>Website</span>
            </Link>

            <Link
              href="/permohonan"
              class="flex flex-col items-center justify-center gap-1 text-xs"
              :class="isActivePath('/permohonan') ? 'text-orange-600' : 'text-gray-700 dark:text-gray-200'"
            >
              <FileText class="w-6 h-6" />
              <span>Permohonan</span>
            </Link>

            <button
              class="flex flex-col items-center justify-center gap-1 text-xs text-gray-700 dark:text-gray-200"
              @click="showMore = true"
            >
              <MoreHorizontal class="w-6 h-6" />
              <span>Lainnya</span>
            </button>
          </div>
        </div>
      </template>

      <!-- Staf Sambutan: Dashboard | Sambutan | Lainnya -->
      <template v-else-if="role === 'Staf Sambutan'">
        <div class="h-16 flex items-center justify-center">
          <div class="flex items-center justify-center gap-8">
            <Link
              :href="route('dashboard')"
              class="flex flex-col items-center justify-center gap-1 text-xs"
              :class="isActivePath('/dashboard') ? 'text-orange-600' : 'text-gray-700 dark:text-gray-200'"
            >
              <Home class="w-6 h-6" />
              <span>Dashboard</span>
            </Link>

            <Link
              href="/sambutan"
              class="flex flex-col items-center justify-center gap-1 text-xs"
              :class="isActivePath('/sambutan') ? 'text-orange-600' : 'text-gray-700 dark:text-gray-200'"
            >
              <MessageCircle class="w-6 h-6" />
              <span>Sambutan</span>
            </Link>

            <button
              class="flex flex-col items-center justify-center gap-1 text-xs text-gray-700 dark:text-gray-200"
              @click="showMore = true"
            >
              <MoreHorizontal class="w-6 h-6" />
              <span>Lainnya</span>
            </button>
          </div>
        </div>
      </template>

      <!-- Staf Berita: Dashboard | Berita | Post Sosmed | Lainnya -->
      <template v-else-if="role === 'Staf Berita'">
        <div class="h-16 flex items-center justify-center">
          <div class="flex items-center justify-center gap-8">
            <Link
              :href="route('dashboard')"
              class="flex flex-col items-center justify-center gap-1 text-xs"
              :class="isActivePath('/dashboard') ? 'text-orange-600' : 'text-gray-700 dark:text-gray-200'"
            >
              <Home class="w-6 h-6" />
              <span>Dashboard</span>
            </Link>

            <Link
              href="/berita"
              class="flex flex-col items-center justify-center gap-1 text-xs"
              :class="isActivePath('/berita') ? 'text-orange-600' : 'text-gray-700 dark:text-gray-200'"
            >
              <Megaphone class="w-6 h-6" />
              <span>Berita</span>
            </Link>

            <Link
              href="/post-sosmed"
              class="flex flex-col items-center justify-center gap-1 text-xs"
              :class="isActivePath('/post-sosmed') ? 'text-orange-600' : 'text-gray-700 dark:text-gray-200'"
            >
              <Share2 class="w-6 h-6" />
              <span>Post Social Media</span>
            </Link>

            <button
              class="flex flex-col items-center justify-center gap-1 text-xs text-gray-700 dark:text-gray-200"
              @click="showMore = true"
            >
              <MoreHorizontal class="w-6 h-6" />
              <span>Lainnya</span>
            </button>
          </div>
        </div>
      </template>

      <!-- Staf Dokumentasi: Dashboard | Kegiatan | Post Sosmed | Lainnya -->
      <template v-else-if="role === 'Staf Dokumentasi'">
        <div class="h-16 flex items-center justify-center">
          <div class="flex items-center justify-center gap-8">
            <Link
              :href="route('dashboard')"
              class="flex flex-col items-center justify-center gap-1 text-xs"
              :class="isActivePath('/dashboard') ? 'text-orange-600' : 'text-gray-700 dark:text-gray-200'"
            >
              <Home class="w-6 h-6" />
              <span>Dashboard</span>
            </Link>

            <Link
              href="/kegiatan"
              class="flex flex-col items-center justify-center gap-1 text-xs"
              :class="isActivePath('/kegiatan') ? 'text-orange-600' : 'text-gray-700 dark:text-gray-200'"
            >
              <FolderOpen class="w-6 h-6" />
              <span>Kegiatan</span>
            </Link>

            <Link
              href="/post-sosmed"
              class="flex flex-col items-center justify-center gap-1 text-xs"
              :class="isActivePath('/post-sosmed') ? 'text-orange-600' : 'text-gray-700 dark:text-gray-200'"
            >
              <Share2 class="w-6 h-6" />
              <span>Post Social Media</span>
            </Link>

            <button
              class="flex flex-col items-center justify-center gap-1 text-xs text-gray-700 dark:text-gray-200"
              @click="showMore = true"
            >
              <MoreHorizontal class="w-6 h-6" />
              <span>Lainnya</span>
            </button>
          </div>
        </div>
      </template>

      <!-- Role lain: fallback (tetap centered seperti sebelumnya) -->
      <template v-else>
        <div class="h-16 flex items-center justify-center">
          <div class="flex items-center justify-center gap-8">
            <template v-for="item in primaryMenus" :key="item.name">
              <Link
                :href="item.href"
                class="flex flex-col items-center justify-center gap-1 text-xs"
                :class="isActivePath(typeof item.href === 'string' ? item.href : '/dashboard') ? 'text-orange-600' : 'text-gray-700 dark:text-gray-200'"
              >
                <component :is="item.icon" class="w-6 h-6" />
                <span class="truncate">{{ item.name }}</span>
              </Link>
            </template>

            <button
              class="flex flex-col items-center justify-center gap-1 text-xs text-gray-700 dark:text-gray-200"
              @click="showMore = true"
            >
              <MoreHorizontal class="w-6 h-6" />
              <span>Lainnya</span>
            </button>
          </div>
        </div>
      </template>
    </nav>

    <!-- Sheet "Lainnya" -->
    <transition name="fade">
      <div v-if="showMore" class="md:hidden fixed inset-0 z-50">
        <!-- overlay -->
        <div class="absolute inset-0 bg-black/40" @click="showMore = false"></div>

        <!-- === Sheet untuk USERB (tetap) === -->
        <template v-if="role === 'USERB'">
          <div class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-800 rounded-t-2xl shadow-2xl p-4">
            <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded mx-auto mb-3"></div>

            <div class="space-y-2">
              <button
                @click="isDark = !isDark; showMore = false"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
              >
                <component :is="isDark ? Sun : Moon" class="w-5 h-5 text-orange-500" />
                <span class="text-sm">{{ isDark ? 'Light Mode' : 'Dark Mode' }}</span>
              </button>

              <Link
                :href="route('profile.edit')"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
                @click="showMore = false"
              >
                <User class="w-5 h-5 text-orange-500" />
                <span class="text-sm">Profile</span>
              </Link>

              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-left"
                @click="showMore = false"
              >
                <svg class="w-5 h-5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 17l5-5m0 0l-5-5m5 5H9" />
                </svg>
                <span class="text-sm">Logout</span>
              </Link>
            </div>

            <button
              class="mt-4 w-full py-2 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600"
              @click="showMore = false"
            >
              Tutup
            </button>
          </div>
        </template>

        <!-- === Sheet untuk Staf Sambutan === -->
        <template v-else-if="role === 'Staf Sambutan'">
          <div class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-800 rounded-t-2xl shadow-2xl p-4">
            <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded mx-auto mb-3"></div>
            <div class="space-y-2">
              <button
                @click="isDark = !isDark; showMore = false"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
              >
                <component :is="isDark ? Sun : Moon" class="w-5 h-5 text-orange-500" />
                <span class="text-sm">{{ isDark ? 'Light Mode' : 'Dark Mode' }}</span>
              </button>

              <Link
                :href="route('profile.edit')"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
                @click="showMore = false"
              >
                <User class="w-5 h-5 text-orange-500" />
                <span class="text-sm">Profile</span>
              </Link>

              <Link
                href="/"
                target="_blank"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
                @click="showMore = false"
              >
                <Globe class="w-5 h-5 text-orange-500" />
                <span class="text-sm">Website</span>
              </Link>

              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-left"
                @click="showMore = false"
              >
                <svg class="w-5 h-5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 17l5-5m0 0l-5-5m5 5H9" />
                </svg>
                <span class="text-sm">Logout</span>
              </Link>
            </div>
            <button
              class="mt-4 w-full py-2 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600"
              @click="showMore = false"
            >
              Tutup
            </button>
          </div>
        </template>

        <!-- === Sheet untuk Staf Berita === -->
        <template v-else-if="role === 'Staf Berita'">
          <div class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-800 rounded-t-2xl shadow-2xl p-4">
            <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded mx-auto mb-3"></div>
            <div class="space-y-2">
              <button
                @click="isDark = !isDark; showMore = false"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
              >
                <component :is="isDark ? Sun : Moon" class="w-5 h-5 text-orange-500" />
                <span class="text-sm">{{ isDark ? 'Light Mode' : 'Dark Mode' }}</span>
              </button>

              <Link
                :href="route('profile.edit')"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
                @click="showMore = false"
              >
                <User class="w-5 h-5 text-orange-500" />
                <span class="text-sm">Profile</span>
              </Link>

              <Link
                href="/"
                target="_blank"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
                @click="showMore = false"
              >
                <Globe class="w-5 h-5 text-orange-500" />
                <span class="text-sm">Website</span>
              </Link>

              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-left"
                @click="showMore = false"
              >
                <svg class="w-5 h-5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 17l5-5m0 0l-5-5m5 5H9" />
                </svg>
                <span class="text-sm">Logout</span>
              </Link>
            </div>
            <button
              class="mt-4 w-full py-2 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600"
              @click="showMore = false"
            >
              Tutup
            </button>
          </div>
        </template>

        <!-- === Sheet untuk Staf Dokumentasi === -->
        <template v-else-if="role === 'Staf Dokumentasi'">
          <div class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-800 rounded-t-2xl shadow-2xl p-4">
            <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded mx-auto mb-3"></div>
            <div class="space-y-2">
              <button
                @click="isDark = !isDark; showMore = false"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
              >
                <component :is="isDark ? Sun : Moon" class="w-5 h-5 text-orange-500" />
                <span class="text-sm">{{ isDark ? 'Light Mode' : 'Dark Mode' }}</span>
              </button>

              <Link
                :href="route('profile.edit')"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
                @click="showMore = false"
              >
                <User class="w-5 h-5 text-orange-500" />
                <span class="text-sm">Profile</span>
              </Link>

              <Link
                href="/"
                target="_blank"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
                @click="showMore = false"
              >
                <Globe class="w-5 h-5 text-orange-500" />
                <span class="text-sm">Website</span>
              </Link>

              <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="w-full flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-left"
                @click="showMore = false"
              >
                <svg class="w-5 h-5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 17l5-5m0 0l-5-5m5 5H9" />
                </svg>
                <span class="text-sm">Logout</span>
              </Link>
            </div>
            <button
              class="mt-4 w-full py-2 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600"
              @click="showMore = false"
            >
              Tutup
            </button>
          </div>
        </template>

        <!-- === Sheet default (role lainnya) === -->
        <template v-else>
          <div class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-800 rounded-t-2xl shadow-2xl p-4">
            <div class="w-12 h-1.5 bg-gray-300 dark:bg-gray-600 rounded mx-auto mb-3"></div>
            <div class="grid grid-cols-2 gap-3">
              <template v-for="item in moreMenus" :key="item.name">
                <Link
                  :href="item.href"
                  class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700"
                  @click="showMore = false"
                >
                  <component :is="item.icon" class="w-5 h-5 text-orange-500" />
                  <span class="text-sm">{{ item.name }}</span>
                </Link>
              </template>
              <template v-if="moreMenus.length === 0">
                <div class="col-span-2 text-center text-sm text-gray-500 dark:text-gray-400 py-2">
                  Tidak ada menu lainnya.
                </div>
              </template>
            </div>
            <button
              class="mt-4 w-full py-2 text-sm rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600"
              @click="showMore = false"
            >
              Tutup
            </button>
          </div>
        </template>
      </div>
    </transition>
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
.animate-bell { animation: bellShake 1s ease-in-out infinite; }

/* Fade untuk sheet overlay */
.fade-enter-active, .fade-leave-active { transition: opacity .2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Mobile/tablet overrides */
@media (max-width: 767px) {
  /* hilangkan margin-left yang berasal dari sidebar */
  .app-shell { margin-left: 0 !important; }
  /* beri ruang untuk bottom-nav */
  main { padding-bottom: 4.5rem; }
}
</style>
