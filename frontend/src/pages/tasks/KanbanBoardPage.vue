<template>
  <DashboardLayout>
    <div class="space-y-6">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
          <h2 class="text-3xl font-bold text-slate-900">Kanban du projet</h2>
          <p class="mt-2 text-slate-500">Visualise les tâches par statut.</p>
        </div>

        <RouterLink
          to="/tasks"
          class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-white font-semibold hover:bg-slate-700 transition"
        >
          Gérer toutes les tâches
        </RouterLink>
      </div>

      <div v-if="errorMessage" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
        {{ errorMessage }}
      </div>

      <div v-if="loading" class="rounded-2xl bg-white border border-slate-200 p-6 text-slate-500">
        Chargement du kanban...
      </div>

      <div v-else class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <KanbanColumn
          v-for="column in kanban"
          :key="column.key"
          :column="column"
          @change-status="changeStatus"
        />
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import DashboardLayout from '../../layouts/DashboardLayout.vue'
import KanbanColumn from '../../components/tasks/KanbanColumn.vue'
import { getProjectKanban, updateTaskStatus } from '../../api/tasks'

const route = useRoute()
const kanban = ref([])
const loading = ref(true)
const errorMessage = ref('')

const loadKanban = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await getProjectKanban(route.params.projectId)
    kanban.value = response.data.kanban || []
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      'Erreur de chargement du kanban.'
  } finally {
    loading.value = false
  }
}

const changeStatus = async (taskId, status) => {
  errorMessage.value = ''

  try {
    await updateTaskStatus(taskId, { status })
    await loadKanban()
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      'Erreur lors de la mise à jour du statut.'
  }
}

onMounted(loadKanban)
</script>