<script setup>
import { ref, onMounted } from 'vue'
import { Bell } from 'lucide-vue-next'

const notifications = ref([])
const unreadCount = ref(0)

onMounted(() => {
    const userIdMeta = document.querySelector('meta[name="user-id"]')
    const userId = userIdMeta?.content

    if (!userId || !window.Echo) {
        return
    }

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
