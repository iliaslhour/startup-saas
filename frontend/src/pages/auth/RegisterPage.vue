<template>
    <AuthLayout>
      <div>
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-slate-900">Inscription</h1>
          <p class="mt-2 text-slate-500">Crée ton compte pour commencer.</p>
        </div>
  
        <form class="space-y-5" @submit.prevent="submitForm">
          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Nom</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full rounded-2xl border border-slate-300 px-4 py-3.5 outline-none transition focus:border-sky-500 focus:ring-2 focus:ring-sky-100"
              placeholder="Ton nom"
            />
          </div>
  
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
  
          <div>
            <label class="mb-2 block text-sm font-medium text-slate-700">Confirmation</label>
            <input
              v-model="form.password_confirmation"
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
            {{ authStore.loading ? 'Création...' : 'Créer un compte' }}
          </button>
        </form>
  
        <p class="mt-6 text-sm text-slate-600">
          Déjà un compte ?
          <RouterLink to="/login" class="font-semibold text-sky-600 hover:text-sky-700">
            Se connecter
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
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  })
  
  const submitForm = async () => {
    errorMessage.value = ''
  
    try {
      await authStore.register(form)
      router.push('/dashboard')
    } catch (error) {
      const errors = error.response?.data?.errors
      errorMessage.value =
        errors?.email?.[0] ||
        errors?.password?.[0] ||
        errors?.name?.[0] ||
        error.response?.data?.message ||
        'Échec de l’inscription.'
    }
  }
  </script>