<template>
  <DashboardLayout>
    <div class="max-w-2xl bg-white rounded-2xl shadow p-6">
      <h2 class="text-2xl font-bold mb-2">Créer une organisation</h2>
      <p class="text-slate-500 mb-6">Crée ton espace startup</p>

      <form class="space-y-4" @submit.prevent="submitForm">
        <div>
          <label class="block mb-1">Nom</label>
          <input v-model="form.name" type="text" class="w-full border rounded-lg px-4 py-3" />
        </div>

        <div>
          <label class="block mb-1">Description</label>
          <textarea v-model="form.description" class="w-full border rounded-lg px-4 py-3"></textarea>
        </div>

        <div v-if="message" class="text-green-600">{{ message }}</div>
        <div v-if="errorMessage" class="text-red-600">{{ errorMessage }}</div>

        <button class="px-5 py-3 rounded-lg bg-slate-900 text-white">
          Créer
        </button>
      </form>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { reactive, ref } from 'vue'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import { createOrganization } from '../../api/organizations'

const form = reactive({
  name: '',
  description: '',
})

const message = ref('')
const errorMessage = ref('')

const submitForm = async () => {
  message.value = ''
  errorMessage.value = ''

  try {
    await createOrganization(form)
    message.value = 'Organisation créée avec succès.'
    form.name = ''
    form.description = ''
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Erreur'
  }
}
</script>