<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { onMounted } from 'vue'

const props = defineProps({
  user: Object,
})

const form = useForm({
  name: props.user.name,
  no_hp: props.user.no_hp,
  email: props.user.email,
  role: props.user.role,
  password: '',
})

function submit() {
  form.put(route('users.update', props.user.id))
}
</script>

<template>
  <Head title="Edit User" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-bold text-gray-800 dark:text-white">Edit User</h2>
    </template>

       <div class="flex justify-center py-5 px-4">

<div class="text-center">
  <a href="/users"
     class="inline-block bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-100 px-4 py-2 rounded hover:bg-gray-400 dark:hover:bg-gray-600 transition">
    ← Kembali ke Daftar User
  </a>
</div>
</div>

    <div class="py-6 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <form @submit.prevent="submit" class="space-y-4 bg-white dark:bg-gray-800 p-6 rounded shadow">
        <div>
          <label class="block font-medium text-sm text-gray-700 dark:text-gray-200">Nama</label>
          <input v-model="form.name" type="text" class="mt-1 input w-full" />
          <span class="text-red-500 text-sm" v-if="form.errors.name">{{ form.errors.name }}</span>
        </div>

        <div>
          <label class="block font-medium text-sm text-gray-700 dark:text-gray-200">No HP</label>
          <input v-model="form.no_hp" type="text" class="mt-1 input w-full" />
          <span class="text-red-500 text-sm" v-if="form.errors.no_hp">{{ form.errors.no_hp }}</span>
        </div>

        <div>
          <label class="block font-medium text-sm text-gray-700 dark:text-gray-200">Email</label>
          <input v-model="form.email" type="email" class="mt-1 input w-full" />
          <span class="text-red-500 text-sm" v-if="form.errors.email">{{ form.errors.email }}</span>
        </div>

        <div>
          <label class="block font-medium text-sm text-gray-700 dark:text-gray-200">Role</label>
          <select v-model="form.role" class="mt-1 input w-full">
            <option value="SUPERADMIN">SUPERADMIN</option>
            <option value="STAFF">STAFF</option>
            <option value="BIASA">USER</option>
          </select>
          <span class="text-red-500 text-sm" v-if="form.errors.role">{{ form.errors.role }}</span>
        </div>

        <div>
          <label class="block font-medium text-sm text-gray-700 dark:text-gray-200">Password (kosongkan jika tidak diubah)</label>
          <input v-model="form.password" type="password" class="mt-1 input w-full" />
          <span class="text-red-500 text-sm" v-if="form.errors.password">{{ form.errors.password }}</span>
        </div>

        <div class="flex justify-end">
          <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded">
            Perbarui
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
.input {
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  padding: 0.5rem;
  background-color: white;
}
.dark .input {
  background-color: #1f2937;
  border-color: #4b5563;
  color: #f9fafb;
}
</style>
