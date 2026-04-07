<template>
  <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Workspace</h1>
        <p class="text-sm text-slate-500">
          Bienvenue {{ authStore.user?.name || 'Utilisateur' }}
        </p>
      </div>

      <div class="flex items-center gap-3">
        <div class="hidden sm:flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2">
          <div class="h-9 w-9 rounded-full bg-slate-900 text-white flex items-center justify-center font-semibold">
            {{ initials }}
          </div>
          <div class="text-sm">
            <p class="font-medium text-slate-800">{{ authStore.user?.name || 'Utilisateur' }}</p>
            <p class="text-slate-500">{{ authStore.user?.email || '-' }}</p>
          </div>
        </div>

        <button
          class="rounded-xl bg-slate-900 px-4 py-2.5 text-white font-medium hover:bg-slate-700 transition"
          @click="handleLogout"
        >
          Logout
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const initials = computed(() => {
  const name = authStore.user?.name || 'U'
  return name
    .split(' ')
    .map((part) => part.charAt(0).toUpperCase())
    .slice(0, 2)
    .join('')
})

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>