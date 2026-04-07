<template>
    <DashboardLayout>
      <div class="max-w-3xl bg-white rounded-2xl shadow p-6">
        <h2 class="text-2xl font-bold mb-2">Créer un projet</h2>
        <p class="text-slate-500 mb-6">Ajoute un nouveau projet</p>
  
        <div v-if="errorMessage" class="text-red-600 mb-4">{{ errorMessage }}</div>
  
        <ProjectForm :form="form" @submit="submitForm" />
      </div>
    </DashboardLayout>
  </template>
  
  <script setup>
  import { reactive, ref } from 'vue'
  import { useRouter } from 'vue-router'
  import DashboardLayout from '../../layouts/DashboardLayout.vue'
  import ProjectForm from '../../components/projects/ProjectForm.vue'
  import { createProject } from '../../api/projects'
  
  const router = useRouter()
  const errorMessage = ref('')
  
  const form = reactive({
    name: '',
    description: '',
    status: 'active',
    start_date: '',
    end_date: '',
  })
  
  const submitForm = async () => {
    errorMessage.value = ''
  
    try {
      await createProject(form)
      router.push('/projects')
    } catch (error) {
      errorMessage.value = error.response?.data?.message || 'Erreur lors de la création.'
    }
  }
  </script>