<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-3xl font-bold text-slate-900">Projets</h2>
          <p class="mt-2 text-slate-500">Liste des projets de ton organisation.</p>
        </div>

        <RouterLink
          to="/projects/create"
          class="rounded-2xl bg-slate-900 px-5 py-3 text-white font-semibold hover:bg-slate-700 transition"
        >
          Nouveau projet
        </RouterLink>
      </div>

      <div v-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-600">
        {{ errorMessage }}
      </div>

      <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
        <div v-if="projects.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center text-slate-500">
          Aucun projet pour le moment.
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[900px]">
            <thead>
              <tr class="border-b border-slate-200 text-left">
                <th class="px-4 py-3">Nom</th>
                <th class="px-4 py-3">Statut</th>
                <th class="px-4 py-3">Début</th>
                <th class="px-4 py-3">Fin</th>
                <th class="px-4 py-3">Action</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="project in projects" :key="project.id" class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-4 py-4 font-medium text-slate-800">{{ project.name }}</td>
                <td class="px-4 py-4">
                  <span class="inline-flex rounded-full px-3 py-1 text-sm font-medium" :class="statusClass(project.status)">
                    {{ formatStatus(project.status) }}
                  </span>
                </td>
                <td class="px-4 py-4 text-slate-600">{{ project.start_date || '-' }}</td>
                <td class="px-4 py-4 text-slate-600">{{ project.end_date || '-' }}</td>
                <td class="px-4 py-4">
                  <RouterLink
                    :to="`/projects/${project.id}/kanban`"
                    class="inline-flex rounded-xl bg-sky-50 px-3 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-100"
                  >
                    Ouvrir Kanban
                  </RouterLink>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import { getProjects } from '../../api/projects'

const projects = ref([])
const errorMessage = ref('')

const loadProjects = async () => {
  try {
    const response = await getProjects()
    projects.value = response.data.projects || []
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Erreur de chargement.'
  }
}

const formatStatus = (status) => {
  if (status === 'active') return 'Active'
  if (status === 'completed') return 'Terminée'
  if (status === 'on_hold') return 'En pause'
  return status
}

const statusClass = (status) => {
  if (status === 'active') return 'bg-emerald-50 text-emerald-700'
  if (status === 'completed') return 'bg-sky-50 text-sky-700'
  if (status === 'on_hold') return 'bg-amber-50 text-amber-700'
  return 'bg-slate-100 text-slate-700'
}

onMounted(loadProjects)
</script>