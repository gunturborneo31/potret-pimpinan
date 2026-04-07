<script setup>
import { ref, onMounted } from 'vue'
import { Bell } from 'lucide-vue-next'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Setup Pusher
window.Pusher = Pusher
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
})

const notifications = ref([])
const unreadCount = ref(0)

onMounted(() => {
    // Ganti "user-id" dengan ID user yang login
    const userId = document.querySelector('meta[name="user-id"]').content

    // Subscribe ke private channel notifikasi user
    window.Echo.private(`App.Models.User.${userId}`)
        .notification((notification) => {
            notifications.value.unshift(notification)
            unreadCount.value++
        })
})
</script>

<template>
    <div class="relative cursor-pointer">
        <Bell class="w-6 h-6" />
        <span
            v-if="unreadCount > 0"
            class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full text-xs px-1"
        >
            {{ unreadCount }}
        </span>
    </div>
</template>

<style scoped>
/* Optional: hover effect */
div:hover svg {
    color: orange;
}
</style>
