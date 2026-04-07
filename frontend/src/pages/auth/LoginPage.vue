<template>
    <AuthLayout>
      <div>
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-slate-900">Connexion</h1>
          <p class="mt-2 text-slate-500">Accède à ton espace de gestion SaaS.</p>
        </div>
  
        <form class="space-y-5" @submit.prevent="submitForm">
          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
            <input
              v-model="form.email"
              type="email"
              class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
              placeholder="ton@email.com"
            />
          </div>
  
          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Mot de passe</label>
            <input
              v-model="form.password"
              type="password"
              class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
              placeholder="********"
            />
          </div>
  
          <div v-if="errorMessage" class="rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-600">
            {{ errorMessage }}
          </div>
  
          <button
            type="submit"
            class="w-full rounded-2xl bg-slate-900 py-3.5 text-white font-semibold transition hover:bg-slate-700"
            :disabled="authStore.loading"
          >
            {{ authStore.loading ? 'Connexion...' : 'Se connecter' }}
          </button>
        </form>
  
        <p class="mt-6 text-sm text-slate-600">
          Pas encore de compte ?
          <RouterLink to="/register" class="font-semibold text-sky-600 hover:text-sky-700">
            Créer un compte
          </RouterLink>
        </p>
      </div>
    </AuthLayout>
  </template>
  
  <script setup>
  import { reactive, ref } from 'vue'
  import { RouterLink, useRouter } from 'vue-router'
  import AuthLayout from '../../layouts/AuthLayout.vue'
  import { useAuthStore } from '../../stores/auth'
  
  const authStore = useAuthStore()
  const router = useRouter()
  
  const errorMessage = ref('')
  
  const form = reactive({
    email: '',
    password: '',
  })
  
  const submitForm = async () => {
    errorMessage.value = ''
  
    try {
      await authStore.login(form)
      router.push('/dashboard')
    } catch (error) {
      errorMessage.value =
        error.response?.data?.message ||
        error.response?.data?.errors?.email?.[0] ||
        'Échec de la connexion.'
    }
  }
  </script>